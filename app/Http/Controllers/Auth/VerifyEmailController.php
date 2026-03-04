<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AdminUser;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();
        $target = ($user->role ?? null) === 'employee'
            ? '/employee'
            : route('dashboard', absolute: false);

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended($target.'?verified=1');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->intended($target.'?verified=1');
    }

    /**
     * Handle verification link for guests (e.g., opening from email while logged out).
     */
    public function guestVerify(Request $request): \Illuminate\Contracts\View\View|RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            return redirect()->route('login')->withErrors(['email' => 'Verification link is invalid or expired.']);
        }

        $user = $this->resolveUserFromRequest($request);

        if (! $user) {
            return redirect()->route('login')->withErrors(['email' => 'Verification link is invalid or expired.']);
        }

        // Validate hash from signed URL matches user's email
        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            return redirect()->route('login')->withErrors(['email' => 'Verification link is invalid or expired.']);
        }

        $target = ($user->role ?? null) === 'employee'
            ? '/employee'
            : route('dashboard', absolute: false);

        if ($user->hasVerifiedEmail()) {
            return view('auth.verify-success', ['redirect' => $target]);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // Do not log in this browser; let the original session poll and redirect
        return view('auth.verify-success', ['redirect' => $target]);
    }

    private function resolveUserFromRequest(Request $request): ?\Illuminate\Contracts\Auth\Authenticatable
    {
        $id = $request->route('id');

        // Try web users first
        $user = User::find($id);
        if ($user) {
            return $user;
        }

        // Fallback to admin users
        return AdminUser::find($id);
    }
}
