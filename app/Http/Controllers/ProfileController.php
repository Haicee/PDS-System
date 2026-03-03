<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\AdminUser;
use App\Notifications\EmployeeProfileUpdated;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
            'avatar' => $this->avatarUrl($request->user()?->profile?->profile),
            'units' => config('units.list', []),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $original = $user->only(['name','gender','unit','phone','email','type','location_assigned']);
        $originalPhoto = $user->profile?->profile;

        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $photoChanged = false;
        if ($request->hasFile('profile_photo')) {
            $oldPath = $user->profile?->profile;
            $path = $request->file('profile_photo')->store('profiles', 'public');
            $user->profile()->updateOrCreate([], [
                'name' => $user->name,
                'profile' => $path,
            ]);
            $photoChanged = $photoChanged || $oldPath !== $path;

            if ($oldPath) {
                $oldFilename = basename($oldPath);
                $oldSanitized = $oldFilename ? 'profiles/' . $oldFilename : null;
                if ($oldSanitized && Storage::disk('public')->exists($oldSanitized)) {
                    Storage::disk('public')->delete($oldSanitized);
                }
            }
        }

        $user->save();

        $changed = [];
        foreach ($original as $key => $value) {
            if ($user->{$key} !== $value) {
                $changed[] = match ($key) {
                    'name' => 'Name',
                    'gender' => 'Gender',
                    'unit' => 'Unit/Division/Section',
                    'phone' => 'Phone',
                    'email' => 'Email',
                    'type' => 'Type',
                    'location_assigned' => 'Location Assigned',
                    default => $key,
                };
            }
        }

        if ($photoChanged) {
            $changed[] = 'Profile Photo';
        }

        if (!empty($changed)) {
            $notification = new EmployeeProfileUpdated($user, $changed);
            $admins = AdminUser::all();
            $adminUsers = \App\Models\User::where('role', 'admin')->get();

            if ($admins->isNotEmpty()) {
                Notification::send($admins, $notification);
                foreach ($admins as $admin) {
                    $this->trimNotificationHistory($admin);
                }
            }

            if ($adminUsers->isNotEmpty()) {
                Notification::send($adminUsers, $notification);
                foreach ($adminUsers as $adminUser) {
                    $this->trimNotificationHistory($adminUser);
                }
            }
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
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

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'digits:11'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'type' => ['required', 'in:Permanent Employee,Job Order'],
            'location_assigned' => ['required', 'string', 'max:255'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
