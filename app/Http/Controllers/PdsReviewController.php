<?php

namespace App\Http\Controllers;

use App\Models\PdsSubmission;
use App\Notifications\PdsStatusUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class PdsReviewController extends Controller
{
    public function index()
    {
        $submissions = PdsSubmission::with('user')
            ->orderBy('submitted', 'desc')
            ->get()
            ->map(function ($submission) {
                $avatar = $this->avatarUrl($submission->user?->profile?->profile);

                return [
                    'id' => $submission->id,
                    'key' => 'pds-' . $submission->id,
                    'user_id' => $submission->user_id,
                    'name' => $submission->name ?? 'Unknown',
                    'avatar' => $avatar,
                    'unit' => $submission->unit ?? '—',
                    'email' => $submission->email ?? '—',
                    'type' => $submission->type ?? 'Permanent Employee',
                    'status' => $submission->status ?? 'Pending',
                    'status_key' => strtolower($submission->status ?? 'pending'),
                    'submitted_at' => $submission->submitted ? $submission->submitted->format('M d, Y • g:i A') : '—',
                ];
            })
            ->toArray();

        return view('pds-form', compact('submissions'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Pending,Approved,Rejected',
        ]);

        $submission = PdsSubmission::findOrFail($id);
        $submission->status = $request->status;
        $submission->save();

        if ($submission->user) {
            Notification::send($submission->user, new PdsStatusUpdated($submission));
            $this->trimNotificationHistory($submission->user);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'submission' => [
                'id' => $submission->id,
                'status' => $submission->status,
                'status_key' => strtolower($submission->status),
            ],
        ]);
    }

    private function trimNotificationHistory($notifiable, int $limit = 20): void
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
}
