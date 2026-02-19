<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\RegistrationUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ManageUserController extends Controller
{
    public function index(Request $request)
    {
        $employees = User::select('id', 'name', 'gender', 'unit', 'email', 'phone', 'type', 'status', 'location_assigned')
            ->with('profile')
            ->get()
            ->map(function (User $user) {
                $rawPath = $user->profile?->profile;
                $sanitizedPath = $rawPath ? ltrim(str_replace('storage/', '', $rawPath), '/') : null;
                $avatar = ($sanitizedPath && Storage::disk('public')->exists($sanitizedPath))
                    ? Storage::disk('public')->url($sanitizedPath)
                    : asset('images/avatar.jpg');

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
                    'avatar' => $avatar,
                ];
            })
            ->values();

        return view('manage-user', compact('employees'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['required', 'digits:11'],
            'type' => ['required', 'in:Permanent Employee,Job Order'],
            'status' => ['required', 'in:Active,Inactive'],
            'location_assigned' => ['required', 'string', 'max:255'],
        ]);

        $user->fill($data);
        $user->save();

        return response()->json([
            'message' => 'User updated',
            'user' => $user->only(['id','name','gender','unit','email','phone','type','status','location_assigned']),
        ]);
    }

    public function destroy(User $user)
    {
        DB::transaction(function () use ($user) {
            $tables = [
                'pds_addresses',
                'pds_contact_infos',
                'pds_declarations',
                'pds_education_records',
                'pds_eligibilities',
                'pds_family_members',
                'pds_form5_remarks',
                'pds_id_infos',
                'pds_other_info',
                'pds_personal_infos',
                'pds_references',
                'pds_signature_files',
                'pds_submissions',
                'pds_training_programs',
                'pds_voluntary_work',
                'pds_work_experiences',
            ];

            foreach ($tables as $table) {
                DB::table($table)->where('user_id', $user->id)->delete();
            }

            RegistrationUser::whereRaw('LOWER(full_name) = ?', [mb_strtolower($user->name)])
                ->orWhere('email', $user->email)
                ->delete();

            $user->delete();
        });

        return response()->json([
            'message' => 'User deleted',
        ]);
    }
}
