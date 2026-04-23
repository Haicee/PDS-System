<?php

namespace App\Http\Controllers;

use App\Models\AdminActivityLog;
use App\Models\PdsSubmission;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        if (auth('web')->check()) {
            return redirect()->route('employee.dashboard');
        }

        $typeCounts = User::selectRaw("type, COUNT(*) as total")
            ->whereIn('type', ['Permanent Employee', 'Contract of Service', 'Job Order'])
            ->groupBy('type')
            ->pluck('total', 'type');

        $permanentCount = $typeCounts['Permanent Employee'] ?? 0;
        $contractCount  = $typeCounts['Contract of Service'] ?? 0;
        $jobOrderCount  = $typeCounts['Job Order'] ?? 0;

        $statusCounts  = PdsSubmission::selectRaw("status, COUNT(*) as total")
            ->whereIn('status', ['Pending', 'Approved', 'Rejected'])
            ->groupBy('status')
            ->pluck('total', 'status');

        $pendingCount  = $statusCounts['Pending']  ?? 0;
        $approvedCount = $statusCounts['Approved'] ?? 0;
        $rejectedCount = $statusCounts['Rejected'] ?? 0;

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

        $recentActivityLogs = AdminActivityLog::orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($log) {
                return [
                    'id'                => $log->id,
                    'admin_name'        => $log->admin_name,
                    'admin_role'        => ucwords($log->admin_role),
                    'action_type'       => $log->action_type,
                    'activity'          => $log->activity,
                    'target_user_name'  => $log->target_user_name ?? '—',
                    'target_user_email' => $log->target_user_email ?? '—',
                    'target_user_type'  => $log->target_user_type ?? '—',
                    'target_user_unit'  => $log->target_user_unit ?? '—',
                    'date_time'         => $log->created_at->setTimezone('Asia/Manila')->format('M d, Y • g:i A'),
                ];
            });

        $stats = [
            'totalEmployees' => $permanentCount,
            'verifiedEmployees' => $contractCount,
            'jobOrderEmployees' => $jobOrderCount,
            'pendingPds' => $pendingCount,
            'approvedPds' => $approvedCount,
            'rejectedPds' => $rejectedCount,
            'recentHires' => 6,
            'recentSubmissions' => $recentSubmissions,
            'recentActivityLogs' => $recentActivityLogs,
        ];

        return view('dashboard', compact('stats'));
    }
}
