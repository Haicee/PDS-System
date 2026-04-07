<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ProfileEditRequest;
use App\Models\RegistrationUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use App\Notifications\EmployeeInfoUpdated;

class ManageUserController extends Controller
{
    // profile
    public function index(Request $request)
        {
            $units = config('units.list', []);

            $employees = User::select('id', 'name', 'gender', 'unit', 'email', 'phone', 'type', 'status', 'location_assigned', 'created_at')
                ->with('profile')
                ->latest('created_at')
                ->get()
                ->map(function (User $user) {
                    $avatar = $this->avatarUrl($user->profile?->profile);
                    $latestEditRequest = ProfileEditRequest::where('user_id', $user->id)
                        ->latest()
                        ->first();

                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'gender' => $user->gender,
                        'unit' => $user->unit,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'type' => $user->type,
                        'status' => $user->status,
                        'location' => $user->location_assigned,
                        'created_at' => $user->created_at?->toIso8601String(),
                        'avatar' => $avatar,
                        'edit_request' => $latestEditRequest ? [
                            'id' => $latestEditRequest->id,
                            'status' => $latestEditRequest->status,
                            'remarks' => $latestEditRequest->remarks,
                        ] : null,
                    ];
                })
                ->values();

            return view('manage-user', compact('employees', 'units'));
        }

    
        // update
    public function update(Request $request, User $user)
    {
        $units = config('units.list', []);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', Rule::in($units)],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['required', 'digits:11'],
            'type' => ['required', 'in:Permanent Employee,Contract of Service'],
            'status' => ['required', 'in:Active,Inactive'],
            'location_assigned' => ['required', 'string', 'max:255'],
        ]);

        $original = $user->only(['name','unit','email','phone','type','status','location_assigned']);

        $user->fill($data);
        $user->save();

        $changed = [];
        foreach ($data as $key => $value) {
            $origVal = $original[$key] ?? null;
            if ($origVal !== $value) {
                $changed[] = match ($key) {
                    'name' => 'Name',
                    'unit' => 'Division/Section/Unit/Office',
                    'email' => 'Email',
                    'phone' => 'Phone',
                    'type' => 'Employee Status',
                    'status' => 'Status',
                    'location_assigned' => 'Place of Assignment',
                    default => $key,
                };
            }
        }

        if (!empty($changed)) {
            Notification::send($user, new EmployeeInfoUpdated($user, $changed));
            $this->trimNotificationHistory($user);
        }

        return response()->json([
            'message' => 'User updated',
            'user' => $user->only(['id','name','gender','unit','email','phone','type','status','location_assigned']),
        ]);
    }

    private function trimNotificationHistory($notifiable, int $limit = 20): void
    {
        $query = $notifiable->notifications()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->skip($limit);

        do {
            $excessIds = $query->take(500)->pluck('id');
            if ($excessIds->isEmpty()) {
                break;
            }
            $notifiable->notifications()->whereIn('id', $excessIds)->delete();
        } while ($excessIds->count() === 500);
    }

    // delete all account info
    public function destroy(User $user)
    {
        DB::transaction(function () use ($user) {
            // Collect file paths before deleting rows
            $photoPaths = DB::table('pds_signature_files')
                ->where('user_id', $user->id)
                ->pluck('photo_file_path')
                ->filter();

            $signaturePaths = DB::table('pds_signature_files')
                ->where('user_id', $user->id)
                ->pluck('signature_file_path')
                ->filter();

            $thumbmarkPaths = DB::table('pds_signature_files')
                ->where('user_id', $user->id)
                ->pluck('thumbmark_file_path')
                ->filter();

            $profilePaths = DB::table('users_profile')
                ->where('user_id', $user->id)
                ->pluck('profile')
                ->filter();

            $tables = [
                'pds_addresses',
                'pds_contact_infos',
                'pds_declarations',
                'pds_drafts',
                'pds_education_records',
                'pds_eligibilities',
                'pds_family_members',
                'pds_form5_remarks',
                'pds_id_infos',
                'pds_other_info',
                'pds_personal_infos',
                'pds_references',
                'pds_rejections',
                'pds_signature_files',
                'pds_submissions',
                'pds_training_programs',
                'pds_voluntary_work',
                'pds_work_experiences',
                'profile_edit_requests',
                'users_profile',
                'otps',
                'notifications',
            ];

            foreach ($tables as $table) {
                $query = DB::table($table);

                if ($table === 'notifications') {
                    $query->where('notifiable_id', $user->id);
                } elseif ($table === 'otps') {
                    $query->where('email', $user->email);
                } else {
                    $query->where('user_id', $user->id);
                }

                $query->delete();
            }

            // Also remove notifications that reference this user in payload (e.g., sent to admins)
            $connection = DB::connection();
            $driver = $connection->getDriverName();
            
            if ($driver === 'pgsql') {
                // PostgreSQL syntax
                DB::table('notifications')
                    ->whereRaw("(data::jsonb)->>'user_id' = ?", [$user->id])
                    ->delete();
            } else {
                // MySQL syntax
                DB::table('notifications')
                    ->whereRaw("JSON_EXTRACT(data, '$.user_id') = ?", [$user->id])
                    ->delete();
            }

            // Delete stored files tied to this user (passport photos, signatures, profiles)
            foreach ($photoPaths as $path) {
                Storage::disk('public')->delete($path);
            }
            foreach ($signaturePaths as $path) {
                Storage::disk('public')->delete($path);
            }
            foreach ($thumbmarkPaths as $path) {
                Storage::disk('public')->delete($path);
            }
            foreach ($profilePaths as $path) {
                Storage::disk('public')->delete($path);
            }

            $user->delete();
        });

        return response()->json([
            'message' => 'User deleted',
        ]);
    }
}
