<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\RegistrationUser;
use App\Models\UserProfile;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:Male,Female'],
            'unit' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'digits:11'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'type' => ['required', 'in:Permanent Employee,Job Order'],
            'location_assigned' => ['required', 'string', 'max:255'],
            'profile_photo' => ['required', 'image', 'max:3072'],
        ]);
        
    $role = str_starts_with($request->email, 'admin1@gmail.com') ? 'admin' : 'employee';

        $approved = RegistrationUser::whereRaw('LOWER(full_name) = ?', [mb_strtolower($request->name)])->first();

        if (! $approved) {
            return back()
                ->withInput()
                ->withErrors(['name' => 'Name not found in the official employee list.']);
        }

        $role = 'employee';
        $status = 'Active';

        $user = User::create([
            'name' => $request->name,
            'gender' => $request->gender,
            'unit' => $request->unit,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => $request->type,
            'status' => $status,
            'location_assigned' => $request->location_assigned,
            'role' => $role,
        ]);

        // Store latest captured/uploaded photo
        $path = $request->file('profile_photo')->store('profiles', 'public');

        UserProfile::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'profile' => $path,
        ]);

        event(new Registered($user));

        Auth::login($user);

        // ensure fresh pds session cache for new account
        session()->forget(['pds', 'pds_owner']);
        session(['pds_owner' => $user->id]);

        return $user->role === 'employee'
            ? redirect('/employee')
            : redirect(route('dashboard', absolute: false));
    }
}