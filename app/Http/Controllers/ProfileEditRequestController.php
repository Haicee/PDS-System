<?php

namespace App\Http\Controllers;

use App\Models\ProfileEditRequest;
use App\Notifications\ProfileEditRequestStatus;
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
            'status' => 'approved',
            'reviewed_by' => Auth::guard('admin')->id(),
            'reviewed_at' => now(),
        ]);

        if ($profileEditRequest->user) {
            Notification::send($profileEditRequest->user, new ProfileEditRequestStatus($profileEditRequest));
        }

        return response()->json(['message' => 'Request approved.']);
    }

    public function reject(Request $request, ProfileEditRequest $profileEditRequest): JsonResponse
    {
        if ($profileEditRequest->status !== 'pending') {
            return response()->json(['message' => 'Request already processed.'], 422);
        }

        $profileEditRequest->update([
            'status' => 'rejected',
            'remarks' => $request->input('remarks'),
            'reviewed_by' => Auth::guard('admin')->id(),
            'reviewed_at' => now(),
        ]);

        if ($profileEditRequest->user) {
            Notification::send($profileEditRequest->user, new ProfileEditRequestStatus($profileEditRequest));
        }

        return response()->json(['message' => 'Request rejected.']);
    }
}
