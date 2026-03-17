<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class PdsResubmitted extends Notification implements ShouldBroadcast
{
    use Queueable;

    public function __construct(public User $user)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'PDS Re-submitted',
            'message' => sprintf('%s updated and resubmitted their PDS.', $this->user->name),
            'kind' => 'pds_resubmitted',
            'name' => $this->user->name,
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'role' => $this->user->role,
            'type' => $this->user->type,
            'link' => route('pds.preview', ['view_user' => $this->user?->id]),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
