<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'overview' => [
                ['label' => 'My submissions', 'value' => 8, 'accent' => 'from-sky-500 to-blue-500'],
                ['label' => 'Pending review', 'value' => 3, 'accent' => 'from-amber-400 to-orange-400'],
                ['label' => 'Approved', 'value' => 4, 'accent' => 'from-emerald-400 to-teal-500'],
                ['label' => 'Returned for edit', 'value' => 1, 'accent' => 'from-rose-400 to-pink-500'],
            ],
            'quickLinks' => [
                ['label' => 'Start new PDS', 'href' => '#', 'icon' => 'file-plus'],
                ['label' => 'Upload supporting docs', 'href' => '#', 'icon' => 'upload'],
                ['label' => 'View submission history', 'href' => '#', 'icon' => 'clock'],
            ],
            'recentSubmissions' => [
                [
                    'name' => 'Darlene Robertson',
                    'avatar' => 'https://i.pravatar.cc/96?img=47',
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
                    'department' => 'ODP',
                    'type' => 'Job On Call',
                    'email' => 'simmons@example.com',
                    'phone' => '09514785214',
                    'location' => 'Barangay Fatima, General Santos City',
                    'submitted_at' => 'Jan 18, 2026 • 9:05 AM',
                ],
            ],
        ];

        return view('employee_dashboard.employee_dashboard', compact('stats'));
    }
}