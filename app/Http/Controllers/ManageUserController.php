<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\RegistrationUser;
use Illuminate\Http\Request;

class ManageUserController extends Controller
{
    public function index(Request $request)
    {
        $employees = User::select('id', 'name', 'gender', 'unit', 'email', 'phone', 'type', 'status', 'location_assigned')
            ->get()
            ->map(function (User $user) {
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
                    'avatar' => asset('images/avatar.jpg'),
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
        RegistrationUser::whereRaw('LOWER(full_name) = ?', [mb_strtolower($user->name)])
            ->orWhere('email', $user->email)
            ->delete();

        $user->delete();

        return response()->json([
            'message' => 'User deleted',
        ]);
    }
}
