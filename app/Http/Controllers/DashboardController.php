<?php

namespace App\Http\Controllers;

use App\Models\PdsSubmission;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        // If an employee (web guard) hits /dashboard, redirect to employee dashboard
        if (auth('web')->check()) {
            return redirect()->route('employee.dashboard');
        }

        $permanentCount = User::where('type', 'Permanent Employee')->count();
        $jobOrderCount = User::where('type', 'Job Order')->count();

        $pendingCount = PdsSubmission::where('status', 'Pending')->count();
        $approvedCount = PdsSubmission::where('status', 'Approved')->count();
        $rejectedCount = PdsSubmission::where('status', 'Rejected')->count();

        $recentSubmissions = PdsSubmission::with(['user.profile'])
            ->orderBy('submitted', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($submission) {
                $user = $submission->user;
                $avatar = $this->avatarUrl($user?->profile?->profile);

                return [
                    'id' => $submission->id,
                    'user_id' => $submission->user_id,
                    'name' => $submission->name ?? $user->name ?? '—',
                    'avatar' => $avatar,
                    'unit' => $submission->unit ?? $user->unit ?? '—',
                    'type' => $submission->type ?? $user->type ?? '—',
                    'email' => $submission->email ?? $user->email ?? '—',
                    'phone' => $user->phone ?? '—',
                    'location' => $user->location_assigned ?? '—',
                    'status' => $submission->status ? ucfirst($submission->status) : 'Pending',
                    'status_key' => $submission->status ? strtolower($submission->status) : 'pending',
                    'submitted_at' => $submission->submitted
                        ? $submission->submitted->format('M d, Y • g:i A')
                        : '—',
                ];
            });

        $stats = [
            'totalEmployees' => $permanentCount,
            'verifiedEmployees' => $jobOrderCount,
            'pendingPds' => $pendingCount,
            'approvedPds' => $approvedCount,
            'rejectedPds' => $rejectedCount,
            'recentHires' => 6,
            'recentSubmissions' => $recentSubmissions,
        ];

        return view('dashboard', compact('stats'));
    }
}
