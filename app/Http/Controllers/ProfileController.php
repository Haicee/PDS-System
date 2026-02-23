<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->hasFile('profile_photo')) {
            $oldPath = $user->profile?->profile;
            $path = $request->file('profile_photo')->store('profiles', 'public');
            $user->profile()->updateOrCreate([], [
                'name' => $user->name,
                'profile' => $path,
            ]);

            if ($oldPath) {
                $oldFilename = basename($oldPath);
                $oldSanitized = $oldFilename ? 'profiles/' . $oldFilename : null;
                if ($oldSanitized && Storage::disk('public')->exists($oldSanitized)) {
                    Storage::disk('public')->delete($oldSanitized);
                }
            }
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
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
