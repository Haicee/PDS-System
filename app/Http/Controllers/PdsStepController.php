<?php

namespace App\Http\Controllers;

use App\Models\PdsDraft;
use App\Models\PdsRejection;
use App\Models\PdsSubmission;
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

        $draft        = PdsDraft::firstOrCreate(['user_id' => $userId]);
        $existingData = $draft->data ?? [];

        foreach ($eduRemoved as $idx) {
            unset($existingData['education_' . intval($idx)]);
        }

        $draft->data = $this->replaceArrays($existingData, $data);
        $draft->save();
        session(['pds' => $draft->data, 'pds_owner' => $userId]);

        Log::info('Auto-save successful', ['user_id' => $userId, 'data_keys' => array_keys($data)]);

        return response()->json(['status' => 'ok', 'saved_keys' => array_keys($data)]);
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
            session(['pds' => $draft->data, 'pds_owner' => $userId]);
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

        $signaturePath = DB::table('pds_signature_files')
            ->where('user_id', $userId)
            ->value('signature_file_path');

        if (!$signaturePath) {
            unset($data['signature_path'], $data['signature_data']);
        }

        if (!empty($data)) {
            session(['pds' => $data, 'pds_owner' => $userId]);
        }

        $highlightedSections = PdsRejection::where('user_id', $userId)
            ->first()?->highlighted_sections ?? [];

        return view('pds_form.form' . $step, compact('data', 'signaturePath', 'highlightedSections'));
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
        foreach ($incoming as $key => $value) {
            if (preg_match('/^(education|learning)_\d+$/', $key) && is_array($value)) {
                $allEmpty = true;
                array_walk_recursive($value, function($v) use (&$allEmpty) {
                    if (trim((string)($v ?? '')) !== '') $allEmpty = false;
                });
                if ($allEmpty) {
                    unset($existing[$key]);
                }
            }
        }

        foreach ($incoming as $key => $value) {
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

        session(['pds' => $draft->data, 'pds_owner' => $userId]);

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

        DB::table('pds_signature_files')->where('user_id', $userId)->delete();

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