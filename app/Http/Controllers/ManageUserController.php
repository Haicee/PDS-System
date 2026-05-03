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
use App\Services\ActivityLogger;
use App\Services\ExportService;

class ManageUserController extends Controller
{
    public function __construct(private ExportService $exportService) {}

    public function index(Request $request)
    {
        $units = config('units.list', []);
        $status = $request->query('status');

        $employees = User::select('id', 'name', 'gender', 'unit', 'email', 'phone', 'type', 'status', 'location_assigned', 'created_at')
            ->where('is_archive', false)
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

        return view('manage-user', compact('employees', 'units', 'status'));
    }

    public function archive(Request $request)
    {
        $units = config('units.list', []);
        $status = $request->query('status');

        $employees = User::select('id', 'name', 'gender', 'unit', 'email', 'phone', 'type', 'status', 'location_assigned', 'updated_at')
            ->where('is_archive', true)
            ->latest('updated_at')
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
                    'archived_at' => $user->updated_at?->toIso8601String(),
                    'avatar' => $avatar,
                    'edit_request' => $latestEditRequest ? [
                        'id' => $latestEditRequest->id,
                        'status' => $latestEditRequest->status,
                        'remarks' => $latestEditRequest->remarks,
                    ] : null,
                ];
            })
            ->values();

        return view('archive', compact('employees', 'units', 'status'));
    }

    public function archiveUser(User $user)
    {
        $user->update([
            'is_archive' => true,
            'archived_at' => now(),
            'archived_by' => auth()->user()->name,
            'status' => 'Inactive',
        ]);

        ActivityLogger::log(
            'archive',
            "Archived the employee account.",
            ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'type' => $user->type, 'unit' => $user->unit]
        );

        return response()->json([
            'message' => 'User archived successfully',
            'user' => $user->only(['id','name','gender','unit','email','phone','type','status','location_assigned', 'is_archive']),
        ]);
    }

    public function unarchiveUser(User $user)
    {
        $user->update([
            'is_archive' => false,
            'archived_at' => null,
            'archived_by' => null,
            'status' => 'Active',
        ]);

        ActivityLogger::log(
            'unarchive',
            "Restored (unarchived) the employee account.",
            ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'type' => $user->type, 'unit' => $user->unit]
        );

        return response()->json([
            'message' => 'User unarchived successfully',
            'user' => $user->only(['id','name','gender','unit','email','phone','type','status','location_assigned', 'is_archive']),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $units = config('units.list', []);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', Rule::in($units)],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['required', 'digits:11'],
            'type' => ['required', 'in:Permanent Employee,Contract of Service,Job Order'],
            'status' => ['required', 'in:Active,Inactive'],
            'location_assigned' => ['required', 'string', 'max:255'],
        ]);

        $original = $user->only(['name','unit','email','phone','type','status','location_assigned']);

        $user->fill($data);
        $user->save();

        // Keep employment status in sync with masterlist by full name (id not used there)
        if (($original['type'] ?? null) !== $data['type']) {
            RegistrationUser::whereRaw('LOWER(full_name) = LOWER(?)', [$user->name])
                ->update(['type' => $data['type']]);
        }

        $shouldArchive = false;
        if (isset($data['status']) && $data['status'] === 'Inactive' && $original['status'] !== 'Inactive') {
            $user->update([
                'is_archive' => true,
                'archived_at' => now(),
                'archived_by' => auth()->user()->name,
            ]);
            $shouldArchive = true;
        }
        elseif (isset($data['status']) && $data['status'] === 'Active' && $original['status'] === 'Inactive' && $user->is_archive) {
            $user->update([
                'is_archive' => false,
                'archived_at' => null,
                'archived_by' => null,
            ]);
        }

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

            $changedList = implode(', ', $changed);
            ActivityLogger::log(
                'update',
                "Updated the information. Changed fields: {$changedList}.",
                ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'type' => $user->type, 'unit' => $user->unit],
                ['changed_fields' => $changed]
            );
        }

        // Prepare response message based on what happened
        $message = 'User updated';
        if ($shouldArchive) {
            $message = 'User status changed to Inactive and automatically archived';
        } elseif (isset($data['status']) && $data['status'] === 'Active' && $original['status'] === 'Inactive' && isset($original['is_archive']) && $original['is_archive']) {
            $message = 'User status changed to Active and automatically unarchived';
        } elseif (isset($data['status']) && $data['status'] === 'Inactive' && $original['status'] !== 'Inactive' && !isset($data['is_archive'])) {
            $message = 'User status changed to Inactive and automatically archived';
        } elseif (isset($data['status']) && $data['status'] === 'Active' && $original['status'] === 'Inactive' && !isset($data['is_archive'])) {
            $message = 'User status changed to Active and automatically unarchived';
        }

        return response()->json([
            'message' => $message,
            'user' => $user->only(['id','name','gender','unit','email','phone','type','status','location_assigned', 'is_archive']),
        ]);
    }

    public function destroy(User $user)
    {
        $deletedName  = $user->name;
        $deletedEmail = $user->email;
        $deletedType  = $user->type;
        $deletedUnit  = $user->unit;

        DB::transaction(function () use ($user) {
            $sigFiles = DB::table('pds_signature_files')->where('user_id', $user->id)->get();
            $filePaths = $sigFiles->flatMap(fn ($r) => [
                $r->photo_file_path,
                $r->signature_file_path,
                $r->thumbmark_file_path,
            ])->merge(
                DB::table('users_profile')->where('user_id', $user->id)->pluck('profile')
            )->filter();

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

            $driver = DB::connection()->getDriverName();
            if ($driver === 'pgsql') {
                DB::table('notifications')
                    ->whereRaw("(data::jsonb)->>'user_id' = ?", [$user->id])
                    ->delete();
            } else {
                DB::table('notifications')
                    ->whereRaw("JSON_EXTRACT(data, '$.user_id') = ?", [$user->id])
                    ->delete();
            }

            foreach ($filePaths as $path) {
                Storage::disk('public')->delete($path);
            }

            $user->delete();
        });

        ActivityLogger::log(
            'delete',
            "Permanently deleted the employee account.",
            ['id' => null, 'name' => $deletedName, 'email' => $deletedEmail, 'type' => $deletedType, 'unit' => $deletedUnit]
        );

        return response()->json([
            'message' => 'User deleted',
        ]);
    }

    public function exportActive(): \Illuminate\Http\Response
    {
        if (! class_exists(\ZipArchive::class)) {
            abort(500, 'ZipArchive PHP extension is required to export XLSX. Please enable php_zip.');
        }

        $employees = User::select('name', 'unit', 'email', 'phone', 'type', 'status', 'location_assigned')
            ->where('is_archive', false)
            ->orderBy('name')
            ->get()
            ->map(fn ($e) => [
                'name'       => $e->name ?? '',
                'department' => $e->unit ?? '',
                'email'      => $e->email ?? '',
                'phone'      => $e->phone ?? '',
                'type'       => $e->type ?? '',
                'status'     => $e->status ?? '',
                'location'   => $e->location_assigned ?? '',
            ])
            ->toArray();

        $columns   = ['Name', 'Department', 'Email', 'Phone', 'Employee Status', 'Status', 'Place of Assignment'];
        $colWidths = [30, 18, 32, 18, 18, 14, 36];
        $xlsx      = $this->exportService->buildEmployeesXlsx($columns, $employees, $colWidths);

        return response($xlsx, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="BFAR_Employees_' . date('Y-m-d') . '.xlsx"',
        ]);
    }

    public function exportArchived(): \Illuminate\Http\Response
    {
        if (! class_exists(\ZipArchive::class)) {
            abort(500, 'ZipArchive PHP extension is required to export XLSX. Please enable php_zip.');
        }

        $employees = User::select('name', 'unit', 'email', 'phone', 'type', 'status', 'location_assigned', 'archived_at', 'archived_by')
            ->where('is_archive', true)
            ->latest('archived_at')
            ->get()
            ->map(fn ($e) => [
                'name'        => $e->name,
                'department'  => $e->unit,
                'email'       => $e->email,
                'phone'       => $e->phone,
                'type'        => $e->type,
                'status'      => $e->status,
                'location'    => $e->location_assigned,
                'archived_at' => $e->archived_at?->format('Y-m-d H:i:s'),
                'archived_by' => $e->archived_by,
            ])
            ->toArray();

        $columns   = ['Name', 'Department', 'Email', 'Phone', 'Employee Status', 'Status', 'Place of Assignment', 'Archived At', 'Archived By'];
        $colWidths = [25, 18, 32, 18, 18, 14, 30, 20, 20];
        $xlsx      = $this->exportService->buildEmployeesXlsx($columns, $employees, $colWidths);

        return response($xlsx, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="BFAR_Archived_Employees_' . date('Y-m-d') . '.xlsx"',
        ]);
    }
}
