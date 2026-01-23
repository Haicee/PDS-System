<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    // Static sample figures for quick UI iteration/testing pero diri e call ffrom database
    $stats = [
        'totalEmployees' => 128,
        'verifiedEmployees' => 117,
        'recentHires' => 6,
        'recentSubmissions' => [
            [
                'name' => 'Darlene Robertson',
                'avatar' => 'https://i.pravatar.cc/96?img=47',
                'role' => 'NSAP',
                'department' => 'NSAP',
                'type' => 'Permanent',
                'email' => 'alma.lawson@example.com',
                'phone' => '09514785214',
                'location' => 'Lagao District, General Santos City',
                'submitted_at' => 'Jan 20, 2026 • 8:15 AM',
            ],
            [
                'name' => 'Annette Black',
                'avatar' => 'https://i.pravatar.cc/96?img=32',
                'role' => 'NSAP',
                'department' => 'NSAP',
                'type' => 'Job On Call',
                'email' => 'bill.sanders@example.com',
                'phone' => '09514785214',
                'location' => 'Purok Malakas, General Santos City',
                'submitted_at' => 'Jan 19, 2026 • 4:42 PM',
            ],
            [
                'name' => 'Ronald Richards',
                'avatar' => 'https://i.pravatar.cc/96?img=12',
                'role' => 'NSAP',
                'department' => 'NSAP',
                'type' => 'Permanent',
                'email' => 'weaver@example.com',
                'phone' => '09514785214',
                'location' => 'Barangay City Heights, General Santos City',
                'submitted_at' => 'Jan 18, 2026 • 9:05 AM',
            ],
            [
                'name' => 'Ralph Edwards',
                'avatar' => 'https://i.pravatar.cc/96?img=5',
                'role' => 'ODP',
                'department' => 'ODP',
                'type' => 'Job On Call',
                'email' => 'simmons@example.com',
                'phone' => '09514785214',
                'location' => 'Barangay Fatima, General Santos City',
                'submitted_at' => 'Jan 17, 2026 • 5:30 PM',
            ],
            [
                'name' => 'Devon Lane',
                'avatar' => 'https://i.pravatar.cc/96?img=65',
                'role' => 'ODP',
                'department' => 'ODP',
                'type' => 'Permanent',
                'email' => 'devon.lane@example.com',
                'phone' => '09514785214',
                'location' => 'Barangay Tambler, General Santos City',
                'submitted_at' => 'Jan 16, 2026 • 10:12 AM',
            ],
            [
                'name' => 'Darlene Robertson',
                'avatar' => 'https://i.pravatar.cc/96?img=47',
                'role' => 'NSAP',
                'department' => 'NSAP',
                'type' => 'Permanent',
                'email' => 'alma.lawson@example.com',
                'phone' => '09514785214',
                'location' => 'Barangay San Isidro, General Santos City',
                'submitted_at' => 'Jan 20, 2026 • 8:15 AM',
            ],
            [
                'name' => 'Annette Black',
                'avatar' => 'https://i.pravatar.cc/96?img=32',
                'role' => 'NSAP',
                'department' => 'ODP',
                'type' => 'Job On Call',
                'email' => 'bill.sanders@example.com',
                'phone' => '09514785214',
                'location' => 'Barangay Apopong, General Santos City',
                'submitted_at' => 'Jan 19, 2026 • 4:42 PM',
            ],
            [
                'name' => 'Ronald Richards',
                'avatar' => 'https://i.pravatar.cc/96?img=12',
                'role' => 'NSAP',
                'department' => 'NSAP',
                'type' => 'Permanent',
                'email' => 'weaver@example.com',
                'phone' => '09125418214',
                'location' => 'Barangay Bula, General Santos City',
                'submitted_at' => 'Jan 18, 2026 • 9:05 AM',
            ],
            [
                'name' => 'Ralph Edwards',
                'avatar' => 'https://i.pravatar.cc/96?img=5',
                'role' => 'OPD',
                'department' => 'OPD',
                'type' => 'Job On Call',
                'email' => 'simmons@example.com',
                'phone' => '09125418214',
                'location' => 'Barangay Calumpang, General Santos City',
                'submitted_at' => 'Jan 17, 2026 • 5:30 PM',
            ],
            [
                'name' => 'Devon Lane',
                'avatar' => 'https://i.pravatar.cc/96?img=65',
                'role' => 'OPD',
                'department' => 'OPD',
                'type' => 'Permanent',
                'email' => 'devon.lane@example.com',
                'phone' => '09125418214',
                'location' => 'Barangay Lagao, General Santos City',
                'submitted_at' => 'Jan 16, 2026 • 10:12 AM',
            ],
        ],
    ];

    return view('dashboard', compact('stats'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/pds-form', function () {
    return view('pds-form');
})->middleware(['auth', 'verified'])->name('pds.form');

Route::get('/manage-user', function(){
    $employees = [
        [
            'name' => 'Darlene Robertson',
            'avatar' => 'https://i.pravatar.cc/96?img=47',
            'department' => 'NSAP',
            'email' => 'alma.lawson@example.com',
            'phone' => '09514785214',
            'type' => 'Permanent',
            'status' => 'Active',
            'location' => 'BFAR Regional HQ – Lagao, GenSan',
        ],
        [
            'name' => 'Annette Black',
            'avatar' => 'https://i.pravatar.cc/96?img=32',
            'department' => 'PFO',
            'email' => 'bill.sanders@example.com',
            'phone' => '09514785214',
            'type' => 'Job On Call',
            'status' => 'Inactive',
            'location' => 'General Santos Fish Port Complex',
        ],
        [
            'name' => 'Ronald Richards',
            'avatar' => 'https://i.pravatar.cc/96?img=12',
            'department' => 'NSAP',
            'email' => 'weaver@example.com',
            'phone' => '09514785214',
            'type' => 'Permanent',
            'status' => 'Active',
            'location' => 'City Hall Annex – San Isidro, GenSan',
        ],
        [
            'name' => 'Ralph Edwards',
            'avatar' => 'https://i.pravatar.cc/96?img=5',
            'department' => 'PFO',
            'email' => 'simmons@example.com',
            'phone' => '09514785214',
            'type' => 'Job On Call',
            'status' => 'Inactive',
            'location' => 'Tambler Fisheries Support Office',
        ],
        [
            'name' => 'Devon Lane',
            'avatar' => 'https://i.pravatar.cc/96?img=65',
            'department' => 'HR',
            'email' => 'devon.lane@example.com',
            'phone' => '09514785214',
            'type' => 'Permanent',
            'status' => 'Active',
            'location' => 'Tinagacan Satellite Desk, GenSan',
        ],
    ];

    return view('manage-user', compact('employees'));
})->middleware(['auth', 'verified'])->name('manage-user');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
