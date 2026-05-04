<?php

// employee 'auth:web'
// admin 'auth:admin'


use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PdsSubmissionController;
use App\Http\Controllers\PdsStepController;
use App\Http\Controllers\PdsPdfController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ManageUserController;
use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\RegistrationUser;
use App\Http\Controllers\PdsController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileEditRequestController;
use App\Http\Controllers\Auth\OtpController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Employee dashboard
Route::get('/employee', [EmployeeController::class, 'dashboard'])
    ->middleware(['auth:web'])
    ->name('employee.dashboard');

Route::post('/employee/rejection/dismiss', [EmployeeController::class, 'dismissRejection'])
    ->middleware(['auth:web'])
    ->name('employee.rejection.dismiss');

Route::post('/employee/approval/dismiss', [EmployeeController::class, 'dismissApproval'])
    ->middleware(['auth:web'])
    ->name('employee.approval.dismiss');

Route::get('/employee/pds/status', [EmployeeController::class, 'latestPdsStatus'])
    ->middleware(['auth:web'])
    ->name('employee.pds.status');

// Notifications (admin/web authenticated)
Route::middleware(['auth:admin,web'])->group(function () {
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.readAll');
    Route::get('/notifications/latest', [NotificationController::class, 'latest'])
        ->name('notifications.latest');
});

// Dashboard Route
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth:admin,web'])
    ->name('dashboard');








//PDS Review routes
Route::middleware(['auth:admin', 'verified'])->group(function () {
    Route::get('/pds-form', [App\Http\Controllers\PdsReviewController::class, 'index'])->name('pds.form');
    Route::get('/pds-form/latest', [App\Http\Controllers\PdsReviewController::class, 'latest'])->name('pds.form.latest');
    Route::post('/pds-form/{id}/status', [App\Http\Controllers\PdsReviewController::class, 'updateStatus'])->name('pds.updateStatus');
    Route::get('/pds-form/export', [App\Http\Controllers\PdsReviewController::class, 'export'])->name('pds.export');
    Route::get('/pds-form/export-details', [App\Http\Controllers\PdsReviewController::class, 'exportDetails'])->name('pds.export.details');
    Route::get('/pds-form/{key}/download', [App\Http\Controllers\PdsReviewController::class, 'downloadDocx'])->name('pds.download');
});

Route::get('/pds-preview/{user}', [App\Http\Controllers\PdsPdfController::class, 'previewForAdmin'])
    ->middleware(['auth:admin', 'verified'])
    ->name('pds.preview.admin');

Route::get('/pds-preview/{user}/download', [App\Http\Controllers\PdsPdfController::class, 'downloadForAdmin'])
    ->middleware(['auth:admin', 'verified'])
    ->name('pds.preview.admin.download');

Route::get('/manage-user', [ManageUserController::class, 'index'])
    ->middleware(['auth:admin'])
    ->name('manage-user');

Route::patch('/manage-user/{user}', [ManageUserController::class, 'update'])
    ->middleware(['auth:admin'])
    ->name('manage-user.update');

Route::delete('/manage-user/{user}', [ManageUserController::class, 'destroy'])
    ->middleware(['auth:admin'])
    ->name('manage-user.destroy');

Route::post('/manage-user/{user}/archive', [ManageUserController::class, 'archiveUser'])
    ->middleware(['auth:admin'])
    ->name('manage-user.archive');

// Archive routes
Route::get('/archive', [ManageUserController::class, 'archive'])
    ->middleware(['auth:admin'])
    ->name('archive');

Route::post('/archive/{user}/unarchive', [ManageUserController::class, 'unarchiveUser'])
    ->middleware(['auth:admin'])
    ->name('archive.unarchive');

Route::delete('/archive/{user}', [ManageUserController::class, 'destroy'])
    ->middleware(['auth:admin'])
    ->name('archive.delete');

Route::get('/archive/export', [ManageUserController::class, 'exportArchived'])
    ->middleware(['auth:admin'])
    ->name('archive.export');


Route::get('/manage-user/export', [ManageUserController::class, 'exportActive'])
    ->middleware(['auth:admin'])
    ->name('manage-user.export');

// Admin Users
Route::get('/admin-users', [AdminUserController::class, 'index'])
    ->middleware(['auth:admin'])
    ->name('admin.users');

Route::get('/admin-users/{adminUser}', [AdminUserController::class, 'show'])
    ->middleware(['auth:admin'])
    ->name('admin.users.show');

Route::patch('/admin-users/{adminUser}', [AdminUserController::class, 'update'])
    ->middleware(['auth:admin'])
    ->name('admin.users.update');

Route::delete('/admin-users/{adminUser}', [AdminUserController::class, 'destroy'])
    ->middleware(['auth:admin'])
    ->name('admin.users.destroy');

// Admin user creation (frontend modal submission)
Route::post('/admin-users', [AdminUserController::class, 'store'])
    ->middleware(['auth:admin'])
    ->name('admin-users.store');

// Admin profile (name/email/password only)
Route::middleware(['auth:admin'])->group(function () {
    Route::get('/admin/profile', [AdminProfileController::class, 'edit'])->name('admin.profile.edit');
    Route::patch('/admin/profile', [AdminProfileController::class, 'updateProfile'])->name('admin.profile.update');
    Route::put('/admin/profile/password', [AdminProfileController::class, 'updatePassword'])->name('admin.profile.password');
});

// Admin Activity History
Route::get('/admin/activity', [AdminActivityController::class, 'index'])
    ->middleware(['auth:admin'])
    ->name('admin.activity.index');


// Employee creation (Add Employee modal)
Route::post('/registration-users', function (Request $request) {
    $validated = $request->validate([
        'full_name' => ['required', 'string', 'max:255', 'unique:registration_users,full_name'],
        'type' => ['required', Rule::in(['Permanent Employee', 'Contract of Service', 'Job Order'])],
    ]);

    $employee = RegistrationUser::create([
        'full_name' => $validated['full_name'],
        'type' => $validated['type'] ?? null,
    ]);

    return response()->json([
        'message' => 'Employee added successfully.',
        'employee' => $employee->only(['id', 'full_name', 'type', 'created_at']),
    ], 201);
})->middleware(['auth:admin'])->name('registration-users.store');

//Profile Route
Route::middleware('auth:admin,web')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/request-edit', [ProfileController::class, 'requestEdit'])->name('profile.requestEdit');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Fallback: if someone lands on GET /pds/submit, send them to the PDS form instead of 405
    Route::get('/pds/submit', function () {
        return redirect()->route('pds.form1');
    })->name('pds.submit.get');

    Route::post('/pds/submit', [PdsSubmissionController::class, 'store'])->name('pds.submit');
    Route::post('/pds/save-step/{step}', [PdsStepController::class, 'saveStep'])->name('pds.saveStep');
    Route::post('/pds/autosave', [PdsStepController::class, 'autoSave'])
    ->name('pds.autosave');
    Route::post('/pds/draft/delete-key', [PdsStepController::class, 'deleteDraftKey'])
    ->name('pds.draft.deleteKey');
    Route::post('/pds/clear-signature', [PdsStepController::class, 'clearSignature'])->name('pds.clearSignature');
    Route::get('/pds/draft', [PdsStepController::class, 'draft'])->name('pds.draft');
    Route::get('/pds/pdf', [PdsPdfController::class, 'download'])->name('pds.pdf');
});

// Profile edit requests (admin only)
Route::middleware(['auth:admin'])->group(function () {
    Route::post('/profile-edit-requests/{profileEditRequest}/approve', [ProfileEditRequestController::class, 'approve'])->name('profile-edit-requests.approve');
    Route::post('/profile-edit-requests/{profileEditRequest}/reject', [ProfileEditRequestController::class, 'reject'])->name('profile-edit-requests.reject');
});

// Employee routes
Route::middleware(['auth','role:employee'])->group(function () {
    Route::get('/employee', [EmployeeController::class, 'dashboard'])->name('employee.dashboard');
    Route::get('/employee/pds/form1', [PdsStepController::class, 'form1'])->name('pds.form1');
    Route::get('/employee/pds/form2', [PdsStepController::class, 'form2'])->name('pds.form2');
    Route::get('/employee/pds/form3', [PdsStepController::class, 'form3'])->name('pds.form3');
    Route::get('/employee/pds/form4', [PdsStepController::class, 'form4'])->name('pds.form4');
    Route::get('/employee/pds/form5', [PdsStepController::class, 'form5'])->name('pds.form5');
});



// Employee routes
Route::middleware(['auth','role:employee'])->group(function () {
Route::get('/pds/view', [PdsController::class, 'view'])->name('pds.view');
    Route::get('/employee/pdsreview/form1', [PdsController::class, 'view'])->name('pdsreview.pdsreview1');
    Route::get('/employee/pdsreview/form2', [PdsController::class, 'review2'])->name('pdsreview.pdsreview2');
    Route::get('/employee/pdsreview/form3', [PdsController::class, 'review3'])->name('pdsreview.pdsreview3');
    Route::get('/employee/pdsreview/form4', [PdsController::class, 'review4'])->name('pdsreview.pdsreview4');
    Route::get('/employee/pdsreview/form5', [PdsController::class, 'review5'])->name('pdsreview.pdsreview5');
    Route::get('/employee/pdsreview/form1/pdf', [PdsPdfController::class, 'preview1'])->name('pdsreview1.pdf');
});


Route::get('/pds/pdf-preview', [PdsPdfController::class, 'preview'])
    ->name('pds.pdf.preview')
    ->middleware('signed'); 


Route::middleware('auth')->group(function () {
    Route::get('/pds/pdf-download', [PdsPdfController::class, 'download'])
        ->name('pds.pdf.download');
});


Route::get('/verification-status', function () {
    return ['verified' => auth()->user()->hasVerifiedEmail()];
})->middleware(['auth'])->name('verification.status.simple');

Route::get('/verification-stream', function () {

    $response = new \Symfony\Component\HttpFoundation\StreamedResponse(function () {

        // 🔥 Disable ALL buffering layers
        while (ob_get_level() > 0) {
            ob_end_flush();
        }
        ob_implicit_flush(true);

        // 🔥 Force server + browser to start streaming immediately
        echo str_repeat(' ', 2048) . "\n";
        flush();

        // Get authenticated user ID once
        $userId = auth()->id();
        if (!$userId) {
            return;
        }

        // Initial fetch
        $user = \App\Models\User::find($userId);
        if (!$user) {
            return;
        }

        $lastVerified = $user->hasVerifiedEmail();

        // Send initial state immediately
        echo "data: " . json_encode([
            'verified' => $lastVerified,
            'redirect' => $lastVerified
                ? (strtolower($user->role ?? '') === 'employee'
                    ? '/employee'
                    : route('dashboard', absolute: false))
                : null,
        ]) . "\n\n";

        flush();

        // If already verified, stop immediately
        if ($lastVerified) {
            return;
        }

        $heartbeatCounter = 0;

        while (true) {

            // Stop if client disconnects
            if (connection_aborted()) {
                break;
            }

            // Re-fetch user (fresh data)
            $user = \App\Models\User::find($userId);
            if (!$user) {
                echo "data: " . json_encode(['error' => 'user_not_found']) . "\n\n";
                flush();
                break;
            }

            $currentVerified = $user->hasVerifiedEmail();

            // Only send update if status changed
            if ($currentVerified !== $lastVerified) {

                $target = strtolower($user->role ?? '') === 'employee'
                    ? '/employee'
                    : route('dashboard', absolute: false);

                echo "data: " . json_encode([
                    'verified' => $currentVerified,
                    'redirect' => $currentVerified ? $target : null,
                ]) . "\n\n";

                flush();

                $lastVerified = $currentVerified;

                // If verified, end stream
                if ($currentVerified) {
                    break;
                }
            }

            // Heartbeat every ~14 seconds
            $heartbeatCounter++;
            if ($heartbeatCounter >= 14) { // 14 * 1 second = 14 seconds
                echo "data: " . json_encode(['heartbeat' => true]) . "\n\n";
                flush();
                $heartbeatCounter = 0;
            }

            sleep(1); // Check every 1 second for faster response
        }
    });

    // 🔥 SSE Headers (optimized)
    $response->headers->set('Content-Type', 'text/event-stream');
    $response->headers->set('Cache-Control', 'no-cache, no-transform');
    $response->headers->set('Connection', 'keep-alive');
    $response->headers->set('X-Accel-Buffering', 'no'); // Nginx

    return $response;

})->middleware(['auth'])->name('verification.stream');

Route::get('/otp/resend', [OtpController::class, 'resend'])
    ->name('otp.resend')
    ->middleware('guest');

    Route::middleware('web')->group(function () {
    Route::get('/otp', [OtpController::class, 'show'])->name('otp.show');
    Route::post('/otp/verify', [OtpController::class, 'verify'])->name('otp.verify');
    Route::post('/otp/resend', [OtpController::class, 'resend'])->name('otp.resend');
    Route::post('/otp/cancel', [OtpController::class, 'cancel'])->name('otp.cancel');
});

require __DIR__.'/auth.php';
