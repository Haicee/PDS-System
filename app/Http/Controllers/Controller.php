<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Support\Facades\Storage;

abstract class Controller
{
    /**
     * Ensure a path or URL uses the current request host (e.g., 127.0.0.1:8000 instead of localhost).
     */
    protected function withAppHost(string $urlOrPath): string
    {
        $root = request()->getSchemeAndHttpHost();
        $path = ltrim(parse_url($urlOrPath, PHP_URL_PATH) ?? $urlOrPath, '/');

        return rtrim($root, '/') . '/' . $path;
    }

    /**
     * Build an avatar URL from a stored profile path with existence check and fallback.
     */
    protected function avatarUrl(?string $rawPath): string
    {
        $filename = $rawPath ? basename($rawPath) : null;
        $sanitizedPath = $filename ? 'profiles/' . $filename : null;

        if ($sanitizedPath && Storage::disk('public')->exists($sanitizedPath)) {
            $storagePath = Storage::disk('public')->url($sanitizedPath);

            return $this->withAppHost($storagePath);
        }

        return $this->withAppHost(asset('images/avatar.jpg'));
    }

    /**
     * Build a URL from a public-disk path, normalized to current host.
     */
    protected function assetFromPublicDisk(string $path): string
    {
        return $this->withAppHost(Storage::disk('public')->url($path));
    }

    /**
     * Trim notification history for a notifiable, keeping only the most recent $limit entries.
     */
    protected function trimNotificationHistory($notifiable, int $limit = 20): void
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

    protected function notifyAdmins(object $notification): void
    {
        $adminUsers    = AdminUser::all();
        $roleAdmins    = User::where('role', 'admin')->get();
        $recipients    = $adminUsers->concat($roleAdmins);

        if ($recipients->isNotEmpty()) {
            NotificationFacade::send($recipients, $notification);
            foreach ($recipients as $recipient) {
                $this->trimNotificationHistory($recipient);
            }
        }
    }
}
