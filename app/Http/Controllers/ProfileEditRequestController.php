<?php

namespace App\Http\Controllers;

use App\Models\ProfileEditRequest;
use App\Notifications\ProfileEditRequestStatus;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class ProfileEditRequestController extends Controller
{
    public function approve(ProfileEditRequest $profileEditRequest): JsonResponse
    {
        if ($profileEditRequest->status !== 'pending') {
            return response()->json(['message' => 'Request already processed.'], 422);
        }

        $profileEditRequest->update([
            'status'      => 'approved',
            'reviewed_by' => Auth::guard('admin')->id(),
            'reviewed_at' => now(),
        ]);

        $this->notifyAndLog($profileEditRequest, 'profile_edit_approved', 'Approved');

        return response()->json(['message' => 'Request approved.']);
    }

    public function reject(Request $request, ProfileEditRequest $profileEditRequest): JsonResponse
    {
        if ($profileEditRequest->status !== 'pending') {
            return response()->json(['message' => 'Request already processed.'], 422);
        }

        $remarks = $request->input('remarks');

        $profileEditRequest->update([
            'status'      => 'rejected',
            'remarks'     => $remarks,
            'reviewed_by' => Auth::guard('admin')->id(),
            'reviewed_at' => now(),
        ]);

        $suffix = $remarks ? " Reason: {$remarks}" : '';
        $this->notifyAndLog($profileEditRequest, 'profile_edit_rejected', 'Rejected', $suffix);

        return response()->json(['message' => 'Request rejected.']);
    }

    private function notifyAndLog(ProfileEditRequest $editRequest, string $actionType, string $verb, string $suffix = ''): void
    {
        $employee = $editRequest->user;

        if ($employee) {
            Notification::send($employee, new ProfileEditRequestStatus($editRequest));
        }

        ActivityLogger::log(
            $actionType,
            "{$verb} the profile edit request of {$employee?->name}.{$suffix}",
            ['id' => $employee?->id, 'name' => $employee?->name, 'email' => $employee?->email, 'type' => $employee?->type, 'unit' => $employee?->unit]
        );
    }
}
