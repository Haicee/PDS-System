<?php

namespace App\Http\Controllers;

use App\Models\PdsDraft;
use App\Models\PdsRejection;
use App\Models\PdsSubmission;
use App\Repositories\PdsRepository;
use App\Services\PdsFileService;
use App\Services\PdsPersistenceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class PdsStepController extends Controller
{
    public function __construct(
        private PdsFileService $fileService,
        private PdsPersistenceService $persistenceService,
        private PdsRepository $repository,
    ) {}

    public function saveStep(Request $request, int $step)
    {
        $userId = Auth::id();

        $data          = $this->extractRequestData($request);
        $signaturePath = $this->fileService->storeSignature($request, $userId);

        if ($signaturePath) {
            $data['signature_path'] = $signaturePath;
        }

        if ($step === 1) {
            $names = collect($data['children_familybg'] ?? []);
            $dobs = collect($data['children_dateofbirth_familybg'] ?? []);

            $childValidator = Validator::make($data, []);
            $names->each(function ($name, $i) use ($dobs, $childValidator) {
                $nameTrim = strtoupper(trim((string) $name));
                if ($nameTrim === '' || in_array($nameTrim, ['NA', 'N/A', 'NONE'], true)) {
                    return;
                }

                $dob = trim((string) $dobs->get($i));
                if ($dob === '') {
                    $childValidator->errors()->add("children_dateofbirth_familybg.$i", 'Date of birth is required for this child.');
                }
            });

            if ($childValidator->errors()->isNotEmpty()) {
                return redirect()->back()->withErrors($childValidator)->withInput();
            }
        }

        if ($step === 2) {
            $rowValidator = Validator::make($data, []);
            $this->checkTableRows($data, $rowValidator, ['eligibility', 'rating', 'date', 'place', 'license_no', 'validity']);
            $this->checkTableRows($data, $rowValidator, ['work_from', 'work_to', 'work_position_title', 'work_department', 'work_status', 'work_govt_service']);
            if ($rowValidator->errors()->isNotEmpty()) {
                return redirect()->back()->withErrors($rowValidator)->withInput();
            }
        }

        if ($step === 3) {
            $rowValidator = Validator::make($data, []);
            $this->checkTableRows($data, $rowValidator, ['learning_title_of_ld', 'learning_from', 'learning_to', 'learning_hours', 'learning_type_of_ld', 'learning_conducted_sponsored_by']);
            $this->checkTableRows($data, $rowValidator, ['voluntary_organization', 'voluntary_from', 'voluntary_to', 'voluntary_hours', 'voluntary_position_nature_of_work']);
            $this->checkTableRows($data, $rowValidator, ['special_skills_hobbies', 'non_academic_distinctions_recognition', 'membership_in_association_organization']);
            if ($rowValidator->errors()->isNotEmpty()) {
                return redirect()->back()->withErrors($rowValidator)->withInput();
            }
        }

        $draft = $this->saveDraftAndSyncSession($userId, $data);

        if ($step === 1) {
            $this->persistenceService->persistForm1($userId, $draft->data);
            $this->repository->clearCache($userId);
        }

        $nextRoute = match ($step) {
            1 => 'pds.form2',
            2 => 'pds.form3',
            3 => 'pds.form4',
            4 => 'pds.form5',
            default => 'pds.form5',
        };

        return redirect()->route($nextRoute)
            ->with('status', 'Saved step ' . $step);
    }

    public function autoSave(Request $request)
    {
        $userId = Auth::id();
        Log::info('Auto-save attempt', ['user_id' => $userId, 'has_data' => !empty($request->all())]);

        if (!$userId) {
            Log::error('Auto-save failed: No user authenticated');
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $data       = $this->extractRequestData($request);
        $eduRemoved = $request->input('_edu_removed', []);
        unset($data['_edu_removed']);

        $signaturePath = $this->fileService->storeSignature($request, $userId);
        if ($signaturePath) {
            $data['signature_path'] = $signaturePath;
        }

        // Use database lock to prevent race conditions
        $draft = DB::transaction(function () use ($userId, $data, $eduRemoved) {
            $draft = PdsDraft::lockForUpdate()->firstOrCreate(['user_id' => $userId]);
            $existingData = $draft->data ?? [];

            foreach ($eduRemoved as $idx) {
                unset($existingData['education_' . intval($idx)]);
            }

            $draft->data = $this->replaceArrays($existingData, $data);
            $draft->save();

            return $draft;
        });

        // Store compressed/compressed session data
        $compressedData = $this->compressSessionData($draft->data);
        session(['pds' => $compressedData, 'pds_owner' => $userId]);

        Log::info('Auto-save successful', ['user_id' => $userId, 'data_keys' => array_keys($data)]);

        return response()->json(['status' => 'ok', 'saved_keys' => array_keys($data)]);
    }

    /**
     * Compress session data to reduce memory usage.
     */
    private function compressSessionData(array $data): array
    {
        // Remove empty padded values from dynamic tables to reduce size
        // Only compress learning_N (flat column arrays); education_N uses nested
        // associative structure {level: {field: value}} which removeEmptyRows can't handle.
        foreach ($data as $key => $value) {
            if (preg_match('/^learning_\d+$/', $key) && is_array($value)) {
                $data[$key] = $this->removeEmptyRows($value);
            }
        }

        return $data;
    }

    /**
     * Remove empty rows from table data.
     */
    private function removeEmptyRows(array $tableData): array
    {
        if (!isset($tableData[array_keys($tableData)[0]])) {
            return $tableData;
        }

        $keys = array_keys($tableData);
        $rowCount = count($tableData[$keys[0]] ?? []);
        $nonEmptyIndices = [];

        for ($i = 0; $i < $rowCount; $i++) {
            $hasData = false;
            foreach ($keys as $key) {
                $val = $tableData[$key][$i] ?? null;
                if ($val !== null && $val !== '') {
                    $hasData = true;
                    break;
                }
            }
            if ($hasData) {
                $nonEmptyIndices[] = $i;
            }
        }

        // Keep at least some empty rows for UI (10 for training, 2 for education)
        $minRows = isset($tableData['title_of_ld']) ? 10 : 2;
        while (count($nonEmptyIndices) < $minRows && count($nonEmptyIndices) < $rowCount) {
            $nonEmptyIndices[] = count($nonEmptyIndices);
        }

        $result = [];
        foreach ($keys as $key) {
            $result[$key] = array_intersect_key(
                $tableData[$key],
                array_flip($nonEmptyIndices)
            );
            // Re-index array
            $result[$key] = array_values($result[$key]);
        }

        return $result;
    }

    public function deleteDraftKey(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $key = $request->input('key');
        // Only allow deleting learning_N or education_N keys for safety
        if (!$key || !preg_match('/^(learning|education)_\d+$/', $key)) {
            return response()->json(['message' => 'Invalid key'], 422);
        }
        $draft = PdsDraft::where('user_id', $userId)->first();
        if ($draft) {
            $draftData = $draft->data ?? [];
            unset($draftData[$key]);
            $draft->data = $draftData;
            $draft->save();
            session(['pds' => $this->compressSessionData($draft->data), 'pds_owner' => $userId]);
        }
        return response()->json(['status' => 'ok', 'deleted' => $key]);
    }

    public function draft()
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $draft = PdsDraft::where('user_id', $userId)->first();

        return response()->json([
            'data' => $draft->data ?? [],
        ]);
    }

    public function form1() { return $this->loadFormView(1); }
    public function form2() { return $this->loadFormView(2); }
    public function form3() { return $this->loadFormView(3); }
    public function form4() { return $this->loadFormView(4); }
    public function form5() { return $this->loadFormView(5); }

    private function loadFormView(int $step)
    {
        $userId = Auth::id();
        if ($redirect = $this->redirectIfLocked($userId)) {
            return $redirect;
        }

        if (session('pds_owner') && session('pds_owner') !== $userId) {
            session()->forget(['pds', 'pds_owner']);
        }

        $draft = PdsDraft::where('user_id', $userId)->first();
        $data  = $draft->data ?? [];

        // For form 1, sync education data from database to populate tables
        if ($step === 1) {
            $data = $this->syncEducationDataFromDb($userId, $data);
        }

        // For form 3, sync training data from database to populate dynamic tables
        if ($step === 3) {
            $data = $this->syncTrainingDataFromDb($userId, $data);
        }

        // For form 5, sync work experience data from database
        if ($step === 5) {
            $data = $this->syncForm5DataFromDb($userId, $data);
        }

        $signaturePath = DB::table('pds_signature_files')
            ->where('user_id', $userId)
            ->value('signature_file_path');

        if (!$signaturePath || $signaturePath === 'NA') {
            $signaturePath = null;
            unset($data['signature_path'], $data['signature_data']);
        }

        if (!empty($data)) {
            session(['pds' => $this->compressSessionData($data), 'pds_owner' => $userId]);
        }

        $highlightedSections = PdsRejection::where('user_id', $userId)
            ->first()?->highlighted_sections ?? [];

        return view('pds_form.form' . $step, compact('data', 'signaturePath', 'highlightedSections'));
    }

    /**
     * Sync training data from pds_training_programs table to draft format.
     * ALWAYS rebuilds from DB to ensure consistency (e.g., after rejection).
     * Stale learning_N keys are removed from draft before rebuilding.
     */
    private function syncTrainingDataFromDb(int $userId, array $data): array
    {
        // Always fetch from DB — this is the authoritative source
        $trainingRows = DB::table('pds_training_programs')
            ->where('user_id', $userId)
            ->orderBy('id')
            ->get();

        if ($trainingRows->isEmpty()) {
            return $data;
        }

        // Remove stale draft training keys before rebuilding
        unset($data['learning_title_of_ld'], $data['learning_from'], $data['learning_to']);
        unset($data['learning_hours'], $data['learning_type_of_ld'], $data['learning_conducted_sponsored_by']);
        foreach (array_keys($data) as $key) {
            if (preg_match('/^learning_\d+$/', $key)) {
                unset($data[$key]);
            }
        }

        // Use source column to properly separate main and added training
        $mainTableRows = $trainingRows->filter(fn($row) => ($row->source ?? 'main') === 'main')->values();
        $extraRows = $trainingRows->filter(fn($row) => ($row->source ?? '') === 'added')->values();

        // Populate main table arrays
        $mainTitles = $mainTableRows->pluck('title')->filter(fn($v) => !empty($v))->toArray();
        if (!empty($mainTitles)) {
            $data['learning_title_of_ld'] = $mainTableRows->pluck('title')->toArray();
            $data['learning_from'] = $mainTableRows->pluck('from')->toArray();
            $data['learning_to'] = $mainTableRows->pluck('to')->toArray();
            $data['learning_hours'] = $mainTableRows->pluck('hours')->toArray();
            $data['learning_type_of_ld'] = $mainTableRows->pluck('type_of_ld')->toArray();
            $data['learning_conducted_sponsored_by'] = $mainTableRows->pluck('conducted_by')->toArray();
        }

        // Create dynamic tables for added training (45 rows per table - matching form3 addLearningTable)
        // Store only actual data rows — the JS form will pad when rendering
        if ($extraRows->isNotEmpty()) {
            $extraTableCount = ceil($extraRows->count() / 45);
            for ($i = 0; $i < $extraTableCount; $i++) {
                $tableRows = $extraRows->slice($i * 45, 45)->values();
                $tableNum = $i + 1;

                $data["learning_{$tableNum}"] = [
                    'title_of_ld' => $tableRows->pluck('title')->toArray(),
                    'from' => $tableRows->pluck('from')->toArray(),
                    'to' => $tableRows->pluck('to')->toArray(),
                    'hours' => $tableRows->pluck('hours')->toArray(),
                    'type_of_ld' => $tableRows->pluck('type_of_ld')->toArray(),
                    'conducted_sponsored_by' => $tableRows->pluck('conducted_by')->toArray(),
                ];
            }
        }

        // Only persist if data actually changed
        $draft = PdsDraft::where('user_id', $userId)->first();
        if ($draft && $draft->data !== $data) {
            $draft->data = $data;
            $draft->save();
        }

        return $data;
    }

    /**
     * Sync education data from pds_education_records table to draft format.
     * ALWAYS rebuilds from DB to ensure consistency (e.g., after rejection).
     * Stale education_N keys are removed from draft before rebuilding.
     */
    private function syncEducationDataFromDb(int $userId, array $data): array
    {
        // Always fetch from DB — this is the authoritative source
        $eduRows = DB::table('pds_education_records')
            ->where('user_id', $userId)
            ->orderBy('id')
            ->get();

        if ($eduRows->isEmpty()) {
            return $data;
        }

        // Remove stale draft education keys before rebuilding
        unset($data['education']);
        foreach (array_keys($data) as $key) {
            if (preg_match('/^education_\d+$/', $key)) {
                unset($data[$key]);
            }
        }

        // Base levels for main education table
        $baseLevels = ['elementary', 'secondary', 'vocational', 'college', 'graduate_studies'];

        // Separate using source column (main vs added)
        $mainEdu = $eduRows->filter(fn ($row) => ($row->source ?? 'main') === 'main')->values();
        $extraEdu = $eduRows->filter(fn ($row) => ($row->source ?? 'main') === 'added')->values();

        // Populate main education table
        foreach ($baseLevels as $level) {
            $row = $mainEdu->first(fn ($r) => strtolower($r->level) === $level);
            if ($row) {
                $data['education'][$level] = [
                    'school_name' => $row->school_name,
                    'basic_education' => $row->degree_course,
                    'from' => $row->from,
                    'to' => $row->to,
                    'highest_level' => $row->highest_level,
                    'year_graduated' => $row->year_graduated,
                    'scholarship_acadhonors' => $row->academic_honors,
                ];
            }
        }

        // Create dynamic tables for extra education (5 rows per table)
        // Each table has rows keyed by baseLevels (elementary, secondary, etc.)
        if ($extraEdu->isNotEmpty()) {
            $extraTableCount = ceil($extraEdu->count() / 5);
            for ($i = 0; $i < $extraTableCount; $i++) {
                $tableRows = $extraEdu->slice($i * 5, 5)->values();
                $tableNum = $i + 1;

                foreach ($tableRows as $idx => $row) {
                    // Use the row's actual level as key (matches form structure)
                    $levelKey = strtolower($row->level ?? ($baseLevels[$idx] ?? 'extra_' . $idx));
                    $data["education_{$tableNum}"][$levelKey] = [
                        'school_name' => $row->school_name,
                        'basic_education' => $row->degree_course,
                        'from' => $row->from,
                        'to' => $row->to,
                        'highest_level' => $row->highest_level,
                        'year_graduated' => $row->year_graduated,
                        'scholarship_acadhonors' => $row->academic_honors,
                    ];
                }
            }
        }

        // Only persist if data actually changed
        $draft = PdsDraft::where('user_id', $userId)->first();
        if ($draft && $draft->data !== $data) {
            $draft->data = $data;
            $draft->save();
        }

        return $data;
    }

    /**
     * Sync form5 work experience data from pds_form5_remarks table to draft format.
     * ALWAYS rebuilds from DB to ensure consistency (e.g., after rejection).
     */
    private function syncForm5DataFromDb(int $userId, array $data): array
    {
        // If draft already has form5 work-experience data (from autosave), preserve it.
        // Only rebuild from DB when draft is missing these keys entirely
        // (e.g., first load, or after admin cleared the draft post-rejection).
        $hasDraftForm5 = !empty($data['duration']) || !empty($data['position_title'])
                      || !empty($data['office_unit']);

        if ($hasDraftForm5) {
            return $data;
        }

        $remarks = DB::table('pds_form5_remarks')
            ->where('user_id', $userId)
            ->orderBy('id')
            ->get();

        if ($remarks->isEmpty()) {
            return $data;
        }

        $data['duration'] = $remarks->pluck('duration')->toArray();
        $data['position_title'] = $remarks->pluck('position_title')->toArray();
        $data['office_unit'] = $remarks->pluck('office_unit')->toArray();
        $data['immediate_supervisor'] = $remarks->pluck('immediate_supervisor')->toArray();
        $data['agency_location'] = $remarks->pluck('agency_location')->toArray();
        $data['duties'] = $remarks->pluck('duties')->toArray();

        // Rebuild accomplishments_indexed (per-row buckets)
        $accomplishmentsIndexed = [];
        foreach ($remarks as $remark) {
            $acc = $remark->accomplishments;
            if (is_string($acc)) {
                $acc = json_decode($acc, true);
            }
            $accomplishmentsIndexed[] = is_array($acc) ? array_values($acc) : [];
        }
        $data['accomplishments_indexed'] = $accomplishmentsIndexed;

        // Flatten all accomplishments for the accomplishments[] field
        $allAccomplishments = [];
        foreach ($accomplishmentsIndexed as $bucket) {
            foreach ($bucket as $item) {
                $allAccomplishments[] = $item;
            }
        }
        $data['accomplishments'] = $allAccomplishments;

        // Persist date5 if present on first remark
        $first = $remarks->first();
        if (!empty($first->date5) && empty($data['date5'])) {
            $data['date5'] = $first->date5;
        }

        // Persist rebuilt data to draft
        $draft = PdsDraft::where('user_id', $userId)->first();
        if ($draft && $draft->data !== $data) {
            $draft->data = $data;
            $draft->save();
        }

        return $data;
    }

    /**
     * Ensure checkbox groups are stored as arrays even when only one option is selected.
     */
    private function normalizeArrayFields(array $data): array
    {
        $singleSelectCheckboxes = ['sex', 'civilstatus', 'citizenship'];

        $educationExtraKeys = [
            'education_extra_level',
            'education_extra_school_name',
            'education_extra_basic_education',
            'education_extra_from',
            'education_extra_to',
            'education_extra_highest_level',
            'education_extra_year_graduated',
            'education_extra_scholarship_acadhonors',
        ];

        foreach (array_merge($singleSelectCheckboxes, $educationExtraKeys) as $key) {
            if (array_key_exists($key, $data)) {
                $data[$key] = Arr::wrap($data[$key]);
            }
        }

        if (array_key_exists('remarks', $data)) {
            $data['remarks'] = Arr::wrap($data['remarks']);
        }

        return $data;
    }

    /**
     * When auto-saving, ensure provided arrays fully replace existing ones (so removed rows don't reappear).
     */
    private function replaceArrays(array $existing, array $incoming): array
    {
        // Remove education_N / learning_N keys only when incoming explicitly sends that key with all-empty values.
        // If a key is simply absent from incoming, it means the table wasn't in the DOM at save time
        // (e.g. page-load autosave fires before the restore recreates the table), so preserve it.
        $emptied = [];
        foreach ($incoming as $key => $value) {
            if (preg_match('/^(education|learning)_\d+$/', $key) && is_array($value)) {
                $allEmpty = true;
                array_walk_recursive($value, function($v) use (&$allEmpty) {
                    if (trim((string)($v ?? '')) !== '') $allEmpty = false;
                });
                if ($allEmpty) {
                    unset($existing[$key]);
                    $emptied[$key] = true;
                }
            }
        }

        foreach ($incoming as $key => $value) {
            if (isset($emptied[$key])) continue;
            $existing[$key] = $value;
        }

        return $existing;
    }

    private function extractRequestData(Request $request): array
    {
        $fileKeys = array_keys($request->allFiles());
        $data     = $request->except(array_merge(['_token'], $fileKeys));

        return $this->normalizeArrayFields($data);
    }

    private function saveDraftAndSyncSession(int $userId, array $data): PdsDraft
    {
        $draft        = PdsDraft::firstOrCreate(['user_id' => $userId]);
        $existingData = $draft->data ?? [];

        $draft->data = $this->replaceArrays($existingData, $data);
        $draft->save();

        session(['pds' => $this->compressSessionData($draft->data), 'pds_owner' => $userId]);

        return $draft;
    }

    private function checkTableRows(array $data, \Illuminate\Validation\Validator $validator, array $columns): void
    {
        $cols = array_map(fn ($key) => collect($data[$key] ?? []), $columns);
        $max  = collect($cols)->map->count()->max() ?? 0;

        for ($i = 0; $i < $max; $i++) {
            $rowVals = array_map(fn ($col) => trim((string) $col->get($i)), $cols);
            $rowHasData = collect($rowVals)->some(function ($val) {
                $upper = strtoupper($val);
                return $val !== '' && !in_array($upper, ['NA', 'N/A', 'NONE'], true);
            });

            if (!$rowHasData) {
                continue;
            }

            foreach ($columns as $idx => $key) {
                $val   = $rowVals[$idx] ?? '';
                $upper = strtoupper($val);
                $isNa  = in_array($upper, ['NA', 'N/A', 'NONE'], true);
                if ($val === '' && !$isNa) {
                    $validator->errors()->add("{$key}.{$i}", 'Complete all fields in this row or clear the first column.');
                }
            }
        }
    }

    private function redirectIfLocked(?int $userId)
    {
        if (!$userId) {
            abort(403, 'Unauthorized');
        }

        $submission = PdsSubmission::where('user_id', $userId)->first();
        $rejected = PdsRejection::where('user_id', $userId)->exists();

        // If approved, lock (view only)
        if ($submission && $submission->status === 'Approved') {
            return redirect()->route('pds.view');
        }

        // If rejected exists, allow editing
        if ($rejected) {
            return null;
        }

        // If pending submission exists, keep in view-only until admin decides
        if ($submission && $submission->status === 'Pending') {
            return redirect()->route('pds.view');
        }

        return null;
    }

    public function clearSignature(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $existingPath = DB::table('pds_signature_files')->where('user_id', $userId)->value('signature_file_path');
        if ($existingPath && Storage::disk('public')->exists($existingPath)) {
            Storage::disk('public')->delete($existingPath);
        }

        DB::table('pds_signature_files')->where('user_id', $userId)->update([
            'signature_file_path' => null,
        ]);

        $draft = PdsDraft::where('user_id', $userId)->first();
        if ($draft) {
            $draftData = $draft->data ?? [];
            unset($draftData['signature_path'], $draftData['signature_data']);
            $draft->data = $draftData;
            $draft->save();
        }

        session()->forget(['pds', 'pds_owner']);

        return response()->json(['status' => 'ok']);
    }
}