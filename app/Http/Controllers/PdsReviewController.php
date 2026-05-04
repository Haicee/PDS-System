<?php

namespace App\Http\Controllers;

use App\Models\PdsRejection;
use App\Models\PdsSubmission;
use App\Notifications\PdsStatusUpdated;
use App\Services\ActivityLogger;
use App\Services\ExportService;
use App\Repositories\PdsRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;

class PdsReviewController extends Controller
{
    public function __construct(
        private ExportService $exportService,
        private PdsRepository $repository,
    ) {}

    public function index(Request $request)
    {
        $status = $request->query('status');
        $submissions = $this->mappedSubmissions();

        return view('pds-form', compact('submissions', 'status'));
    }

    public function latest(): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->mappedSubmissions());
    }

    private function mappedSubmissions(): array
    {
        return PdsSubmission::with('user')
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
    }

    public function updateStatus(Request $request, $id)
    {
        $data = $request->validate([
            'status' => 'required|in:Pending,Approved,Rejected',
            'note' => 'nullable|string|max:2000',
            'highlighted_sections' => 'nullable|array',
            'highlighted_sections.*' => 'string',
        ]);

        $submission = PdsSubmission::findOrFail($id);
        $submission->status = $data['status'];

        // Reset approval dismissal so modals can surface on any new decision.
        // If approved: ensure prior dismissal doesn't suppress the modal.
        // If not approved: clear dismissal for future approvals.
        $submission->approval_dismissed_at = null;

        $submission->save();

        if ($submission->status === 'Rejected' && $submission->user_id) {
            PdsRejection::updateOrCreate(
                ['user_id' => $submission->user_id],
                [
                    'name' => $submission->name ?? $submission->user?->name ?? 'Unknown',
                    'status' => 'Rejected',
                    'notes' => $data['note'] ?? null,
                    'highlighted_sections' => $data['highlighted_sections'] ?? null,
                ]
            );
        } elseif ($submission->user_id) {
            PdsRejection::where('user_id', $submission->user_id)->delete();
        }

        if ($submission->user) {
            $noteToSend = $data['note'] ?? null;

            if (!$noteToSend && $submission->status === 'Rejected') {
                $noteToSend = PdsRejection::where('user_id', $submission->user_id)->value('notes');
            }

            Notification::send($submission->user, new PdsStatusUpdated($submission, $noteToSend));
            $this->trimNotificationHistory($submission->user);
        }

        $employee = $submission->user;
        $activityPhrase = match ($submission->status) {
            'Approved' => "Approved the PDS submission.",
            'Rejected' => "Rejected the PDS submission." . ($data['note'] ? " Reason: {$data['note']}" : ''),
            'Pending'  => "Set the PDS submission back to Pending.",
            default    => "Updated PDS status to {$submission->status}.",
        };

        ActivityLogger::log(
            'pds_status',
            $activityPhrase,
            [
                'id'    => $employee?->id,
                'name'  => $submission->name,
                'email' => $submission->email ?? $employee?->email,
                'type'  => $submission->type ?? $employee?->type,
                'unit'  => $submission->unit ?? $employee?->unit,
            ],
            ['pds_status' => $submission->status, 'pds_id' => $submission->id]
        );

        // Clear PDS cache so employee sees fresh data
        if ($submission->user_id) {
            $this->repository->clearCache($submission->user_id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'submission' => [
                'id' => $submission->id,
                'status' => $submission->status,
                'status_key' => strtolower($submission->status),
                'note' => $data['note'] ?? null,
            ],
        ]);
    }

    public function export(): \Illuminate\Http\Response
    {
        if (! class_exists(\ZipArchive::class)) {
            abort(500, 'ZipArchive PHP extension is required to export XLSX. Please enable php_zip.');
        }

        $submissions = $this->rawSubmissions();
        $columns     = ['Employee', 'Division/Section/Unit/Office', 'Email', 'Submitted', 'Employee Status', 'Status'];
        $colWidths   = [34, 38, 40, 30, 24, 18];
        $xlsx        = $this->exportService->buildPdsSubmissionsXlsx($columns, $submissions, $colWidths);

        return response($xlsx, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="BFAR_PDS_Submissions_' . date('Y-m-d') . '.xlsx"',
        ]);
    }

    public function exportDetails(): \Illuminate\Http\Response
    {
        if (! class_exists(\ZipArchive::class)) {
            abort(500, 'ZipArchive PHP extension is required to export XLSX. Please enable php_zip.');
        }

        $columns = [
            'Surname',
            'First Name',
            'Middle Name',
            'Name Extension (e.g Jr. Sr. III)',
            'Civil Status',
            'Gender',
            'Birthdate MM/DD/YYYY',
            'Birthday Month/Day/Year',
            'AGE AS OF TODAY',
            'Eligibility',
            'Cellphone No.',
            'Email Address',
            'Educational Attainment',
            'SCHOOL GRADUATED',
            'Residential Address',
            'Permanent Address',
            'Place Of Birth',
            'PhilHealth',
            'Pag-Ibig',
            'TIN No.',
            'UMID ID No.',
        ];

        $colWidths = [18, 18, 18, 14, 14, 10, 18, 22, 10, 30, 18, 26, 22, 28, 40, 40, 24, 18, 18, 18, 18];

        $rows = $this->pdsDetailsRows();
        $xlsx = $this->exportService->buildPdsDetailsXlsx($columns, $rows, $colWidths);

        return response($xlsx, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="BFAR_PDS_Details_' . date('Y-m-d') . '.xlsx"',
        ]);
    }

    public function downloadDocx(string $key): \Illuminate\Http\Response
    {
        if (! class_exists(\ZipArchive::class)) {
            abort(500, 'ZipArchive PHP extension is required to export DOCX. Please enable php_zip.');
        }

        $submission = collect($this->rawSubmissions())->firstWhere('key', $key);
        if (! $submission) {
            abort(404, 'Submission not found.');
        }

        $docx     = $this->exportService->buildPdsDocx($submission);
        $safeName = preg_replace('/[^A-Za-z0-9_-]+/', '_', $submission['name'] ?? 'PDS');

        return response($docx, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="PDS_' . $safeName . '_' . date('Y-m-d') . '.docx"',
        ]);
    }

    private function pdsDetailsRows(): array
    {
        // Collect user_ids from all users who have submitted a PDS.
        $userIds = PdsSubmission::query()
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->unique()
            ->values()
            ->all();

        if (empty($userIds)) {
            return [];
        }

        $personalInfos = DB::table('pds_personal_infos')
            ->whereIn('user_id', $userIds)
            ->get()
            ->keyBy('user_id');

        $addresses = DB::table('pds_addresses')
            ->whereIn('user_id', $userIds)
            ->get()
            ->keyBy('user_id');

        $contacts = DB::table('pds_contact_infos')
            ->whereIn('user_id', $userIds)
            ->get()
            ->keyBy('user_id');

        // Eligibilities grouped by user_id
        $eligibilities = DB::table('pds_eligibilities')
            ->whereIn('user_id', $userIds)
            ->whereNotNull('eligibility')
            ->where('eligibility', '!=', '')
            ->orderBy('id')
            ->get()
            ->groupBy('user_id');

        // Education records grouped by user_id (main source only)
        $educationQuery = DB::table('pds_education_records')
            ->whereIn('user_id', $userIds);

        // Only restrict by 'main' source if the column exists (it's added by a later migration)
        if (Schema::hasColumn('pds_education_records', 'source')) {
            $educationQuery->where(function ($q) {
                $q->where('source', 'main')->orWhereNull('source');
            });
        }

        $educations = $educationQuery->orderBy('id')->get()->groupBy('user_id');

        // Use latest submission's "name" as sort order
        $submissionsByUser = PdsSubmission::query()
            ->whereIn('user_id', $userIds)
            ->orderByDesc('submitted')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('user_id');

        $rows = [];

        foreach ($userIds as $userId) {
            $pi        = $personalInfos->get($userId);
            $addr      = $addresses->get($userId);
            $contact   = $contacts->get($userId);
            $eligList  = $eligibilities->get($userId, collect());
            $eduList   = $educations->get($userId, collect());
            $submission = $submissionsByUser->get($userId, collect())->first();

            [$latestLevel, $latestSchool] = $this->latestEducationalAttainment($eduList);

            [$birthMmDdYyyy, $birthLong, $age] = $this->formatBirthdate($pi->date_of_birth ?? null);

            $eligibilityStr = $eligList
                ->pluck('eligibility')
                ->filter(fn ($v) => filled($v))
                ->unique()
                ->values()
                ->implode("\n");

            $residential = $this->formatAddress($addr, 'present');
            $permanent   = $this->formatAddress($addr, 'permanent');

            $rows[] = [
                $pi->surname        ?? '',
                $pi->firstname      ?? '',
                $pi->middlename     ?? '',
                $pi->name_extension ?? '',
                $pi->civil_status   ?? '',
                $pi->sex            ?? '',
                $birthMmDdYyyy,
                $birthLong,
                $age,
                $eligibilityStr,
                $contact->mobile_no      ?? '',
                $contact->email_address  ?? '',
                $latestLevel,
                $latestSchool,
                $residential,
                $permanent,
                $pi->place_of_birth ?? '',
                $pi->philhealth_no  ?? '',
                $pi->pagibig_no     ?? '',
                $pi->tin_no         ?? '',
                $pi->umid_no        ?? '',
            ];
        }

        // Sort alphabetically by surname for stable, predictable ordering
        usort($rows, fn ($a, $b) => strcasecmp((string) $a[0], (string) $b[0]));

        return $rows;
    }

    /**
     * Determine the highest educational level with data and return [label, school_name].
     */
    private function latestEducationalAttainment($educationRecords): array
    {
        // Highest to lowest
        $priority = ['graduate_studies', 'college', 'vocational', 'secondary', 'elementary'];

        $labels = [
            'graduate_studies' => 'Graduate Studies',
            'college'          => 'College',
            'vocational'       => 'Vocational / Trade Course',
            'secondary'        => 'Secondary',
            'elementary'       => 'Elementary',
        ];

        // Index records by level (keep first meaningful match per level)
        $byLevel = [];
        foreach ($educationRecords as $rec) {
            $level = strtolower(trim((string) ($rec->level ?? '')));
            if ($level === '') {
                continue;
            }
            // Only count a level as present if there is at least a school_name or degree_course
            $hasContent = filled($rec->school_name ?? null)
                || filled($rec->degree_course ?? null)
                || filled($rec->year_graduated ?? null)
                || filled($rec->from ?? null)
                || filled($rec->to ?? null);

            if (! $hasContent) {
                continue;
            }

            // Keep the most recent (later `to` or greater year) per level
            if (! isset($byLevel[$level]) || $this->eduRecordIsLater($rec, $byLevel[$level])) {
                $byLevel[$level] = $rec;
            }
        }

        foreach ($priority as $level) {
            if (isset($byLevel[$level])) {
                $rec = $byLevel[$level];
                return [
                    $labels[$level] ?? ucfirst($level),
                    (string) ($rec->school_name ?? ''),
                ];
            }
        }

        return ['', ''];
    }

    private function eduRecordIsLater($a, $b): bool
    {
        $ay = (int) preg_replace('/\D/', '', (string) ($a->to ?? $a->year_graduated ?? '0')) ?: 0;
        $by = (int) preg_replace('/\D/', '', (string) ($b->to ?? $b->year_graduated ?? '0')) ?: 0;
        return $ay > $by;
    }

    /**
     * Parse date_of_birth string and return [mm/dd/yyyy, "Month d, Y", age].
     */
    private function formatBirthdate(?string $dob): array
    {
        if (! filled($dob)) {
            return ['', '', ''];
        }

        try {
            $date = Carbon::parse($dob);
        } catch (\Throwable $e) {
            return [(string) $dob, (string) $dob, ''];
        }

        return [
            $date->format('m/d/Y'),
            $date->format('F d, Y'),
            (string) $date->age,
        ];
    }

    private function formatAddress(?object $addr, string $prefix): string
    {
        if (! $addr) {
            return '';
        }

        $parts = [
            $addr->{$prefix . '_house_block_lot'}        ?? null,
            $addr->{$prefix . '_street'}                 ?? null,
            $addr->{$prefix . '_subdivision_village'}    ?? null,
            $addr->{$prefix . '_barangay'}               ?? null,
            $addr->{$prefix . '_city_municipality'}      ?? null,
            $addr->{$prefix . '_province'}               ?? null,
            $addr->{$prefix . '_zip_code'}               ?? null,
        ];

        return collect($parts)
            ->map(fn ($v) => trim((string) $v))
            ->filter(fn ($v) => $v !== '')
            ->implode(', ');
    }

    private function rawSubmissions(): array
    {
        return PdsSubmission::with('user')
            ->orderByDesc('submitted')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (PdsSubmission $submission) {
                $user = $submission->user;

                $submittedAt = $submission->submitted
                    ? $submission->submitted->format('M d, Y • g:i A')
                    : ($submission->created_at?->format('M d, Y • g:i A') ?? '');

                return [
                    'key'          => 'pds-' . $submission->id,
                    'name'         => $submission->name ?? $user?->name ?? '',
                    'department'   => $submission->unit ?? $user?->unit ?? '',
                    'email'        => $submission->email ?? $user?->email ?? '',
                    'submitted_at' => $submittedAt,
                    'type'         => $submission->type ?? $user?->type ?? '',
                    'status'       => $submission->status ?? 'Pending',
                ];
            })
            ->toArray();
    }

}
