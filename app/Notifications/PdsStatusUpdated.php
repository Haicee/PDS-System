<?php

namespace App\Notifications;

use App\Models\PdsSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class PdsStatusUpdated extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public function __construct(public PdsSubmission $submission)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        $status = $this->submission->status ?? 'Pending';

        return [
            'title' => 'PDS Submission Updates',
            'message' => sprintf('Your PDS was marked %s.', $status),
            'status' => $status,
            'submission_id' => $this->submission->id,
            'link' => route('employee.dashboard'),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
