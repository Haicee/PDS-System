<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index()
    {
        $loggedInAdmin = auth('admin')->user();
        $loggedInRole = $loggedInAdmin->role ?? null;
        $loggedInId = $loggedInAdmin->id ?? null;

        $admins = AdminUser::select('id', 'name', 'email', 'role', 'status', 'created_at')
            ->when($loggedInId, fn($query) => $query->where('id', '!=', $loggedInId))
            ->get()
            ->map(fn (AdminUser $admin) => $this->normalize($admin))
            ->values();

        return view('admin-users', compact('admins', 'loggedInRole'));
    }

    public function show(AdminUser $adminUser)
    {
        return response()->json($this->normalize($adminUser));
    }

    public function update(Request $request, AdminUser $adminUser)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:admin_users,email,' . $adminUser->id],
            'role' => ['required', 'in:main admin,admin user'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $adminUser->fill($data);
        $adminUser->save();

        return response()->json([
            'message' => 'Admin updated successfully.',
            'admin' => $this->normalize($adminUser),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:admin_users,name'],
            'email' => ['required', 'email', 'max:255', 'unique:admin_users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $admin = AdminUser::create([
            'name' => mb_strtoupper($validated['name']),
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'admin user',
            'status' => 'active',
        ]);

        return response()->json([
            'message' => 'Admin created successfully.',
            'admin' => $this->normalize($admin),
        ], 201);
    }

    public function destroy(AdminUser $adminUser)
    {
        $adminUser->delete();

        return response()->json([
            'message' => 'Admin deleted successfully.',
        ]);
    }

    private function normalize(AdminUser $admin): array
    {
        return [
            'id' => $admin->id,
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => mb_convert_case($admin->role, MB_CASE_TITLE, 'UTF-8'),
            'status' => $admin->status ? mb_convert_case($admin->status, MB_CASE_TITLE, 'UTF-8') : 'Active',
            'created_at' => $admin->created_at
                ? $admin->created_at->setTimezone('Asia/Manila')->format('Y-m-d H:i')
                : '—',
            'avatar' => asset('images/avatar.jpg'),
        ];
    }
}
