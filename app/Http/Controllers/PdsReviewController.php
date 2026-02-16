<?php

namespace App\Http\Controllers;

use App\Models\PdsSubmission;
use Illuminate\Http\Request;

class PdsReviewController extends Controller
{
    public function index()
    {
        $submissions = PdsSubmission::with('user')
            ->orderBy('submitted', 'desc')
            ->get()
            ->map(function ($submission) {
                return [
                    'id' => $submission->id,
                    'key' => 'pds-' . $submission->id,
                    'user_id' => $submission->user_id,
                    'name' => $submission->name ?? 'Unknown',
                    'avatar' => $submission->user && $submission->user->gender === 'Female' 
                        ? 'https://i.pravatar.cc/96?img=47' 
                        : 'https://i.pravatar.cc/96?img=12',
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
}
