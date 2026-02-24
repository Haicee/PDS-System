<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\PdsDraft;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Prefer admin guard if authenticated there
        if (Auth::guard('admin')->check()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        // Reset any stale pds session data before hydrating for this user
        session()->forget(['pds', 'pds_owner']);

        $user = Auth::guard('web')->user();

        // hydrate per-user pds session cache from persisted draft
        if ($user) {
            $draft = PdsDraft::where('user_id', $user->id)->first();
            if ($draft && $draft->data) {
                session(['pds' => $draft->data, 'pds_owner' => $user->id]);
            } else {
                session()->forget('pds');
            }
        }

        if ($user?->role === 'employee') {
            return redirect('/employee');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Logout both guards to avoid lingering admin sessions
        $adminGuard = Auth::guard('admin');
        $webGuard = Auth::guard('web');

        // persist cached pds data to draft before clearing session, only if owned by this user
        $webUserId = $webGuard->id();
        $sessionPds = session('pds');
        $sessionOwner = session('pds_owner');
        if ($webUserId && is_array($sessionPds) && $sessionOwner === $webUserId) {
            $draft = PdsDraft::firstOrCreate(['user_id' => $webUserId]);
            $existing = $draft->data ?? [];
            $draft->data = array_replace_recursive($existing, $sessionPds);
            $draft->save();
        }

        session()->forget(['pds', 'pds_owner']);

        $adminGuard->logout();
        $webGuard->logout();

        // Clear remember-me cookies for both guards if present
        if (method_exists($adminGuard, 'getRecallerName')) {
            Cookie::queue(Cookie::forget($adminGuard->getRecallerName()));
        }
        if (method_exists($webGuard, 'getRecallerName')) {
            Cookie::queue(Cookie::forget($webGuard->getRecallerName()));
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
