<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $remember = $this->boolean('remember');
        $login = trim($this->input('login'));
        $normalizedLogin = $login;
        $password = $this->input('password');
        $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL) !== false;

        // Build possible credential sets
        $adminCredentials = $isEmail
            ? ['email' => strtolower($login), 'password' => $password]
            : null;

        $userCredentials = ['name' => $login, 'password' => $password];

        // Validate existence: allow admin by email or name; user by name
        $loginExists = $this->checkLoginExists($normalizedLogin, $isEmail);

        if (!$loginExists) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'login' => 'This account is not registered in our system.',
            ]);
        }

        // Try admin first and ensure role is allowed
        if ($adminCredentials && Auth::guard('admin')->attempt($adminCredentials, $remember)) {
            $admin = Auth::guard('admin')->user();
            $allowedAdminRoles = ['main admin', 'admin user'];

            // Check if account is active
            if (strtolower($admin->status ?? '') !== 'active') {
                Auth::guard('admin')->logout();
                RateLimiter::hit($this->throttleKey());
                throw ValidationException::withMessages([
                    'login' => 'Your account is inactive. Please contact the administrator.',
                ]);
            }

            if (in_array($admin->role, $allowedAdminRoles, true)) {
                Auth::shouldUse('admin');
                RateLimiter::clear($this->throttleKey());
                return;
            }

            Auth::guard('admin')->logout();
        }

        // Then fallback to normal users and ensure role is employee
        if (Auth::guard('web')->attempt($userCredentials, $remember)) {
            $user = Auth::guard('web')->user();
            
            // Check if account is active
            if (strtolower($user->status ?? '') !== 'active') {
                Auth::guard('web')->logout();
                RateLimiter::hit($this->throttleKey());
                throw ValidationException::withMessages([
                    'login' => 'Your account is inactive. Please contact the administrator.',
                ]);
            }
            
            if ($user?->role === 'employee') {
                Auth::shouldUse('web');
                RateLimiter::clear($this->throttleKey());
                return;
            }

            Auth::guard('web')->logout();
        }

        RateLimiter::hit($this->throttleKey());

        // Account exists but authentication failed - must be wrong password or wrong role
        throw ValidationException::withMessages([
            'password' => 'The password you entered is incorrect.',
        ]);
    }

    /**
     * Check if login (name or email for admin; name for user) exists in either admin_users or users table
     */
    private function checkLoginExists($login, bool $isEmail): bool
    {
        $adminExists = false;
        if ($isEmail) {
            $adminExists = \DB::table('admin_users')
                ->whereRaw('LOWER(email) = LOWER(?)', [$login])
                ->exists();
        }

        $userExists = \DB::table('users')
            ->whereRaw('LOWER(name) = LOWER(?)', [$login])
            ->exists();

        return $adminExists || $userExists;
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => 'Too many login attempts. Please try again in ' . $seconds . ' seconds.',
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('login')).'|'.$this->ip());
    }
}
