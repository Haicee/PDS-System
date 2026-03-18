<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function markAsRead(string $id): RedirectResponse
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->back();
        }

        $notification = $user->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
        }

        return redirect()->back();
    }

    public function markAllAsRead(): RedirectResponse
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->back();
        }

        $user->unreadNotifications->markAsRead();

        return redirect()->back();
    }
}
