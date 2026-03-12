<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

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

    public function latest(): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $unreadCount = $user->unreadNotifications()->count();
        $recent = $user->notifications()
            ->latest()
            ->take(20)
            ->get()
            ->map(function ($notification) {
                $data = $notification->data ?? [];

                return [
                    'id' => $notification->id,
                    'title' => $data['title'] ?? 'Notification',
                    'message' => $data['message'] ?? '',
                    'link' => $data['link'] ?? null,
                    'created_at_human' => $notification->created_at?->diffForHumans(),
                    'read_at' => $notification->read_at,
                ];
            });

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $recent,
        ]);
    }
}
