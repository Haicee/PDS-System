<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PdsDraft;
use App\Models\PdsRejection;
use App\Models\PdsSubmission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class PdsStepController extends Controller
{
    public function saveStep(Request $request, int $step)
    {
        $userId = Auth::id();

        $signaturePath = $this->storeSignature($request, $userId);
        $fileKeys = array_keys($request->allFiles());
        $data = $request->except(array_merge(['_token'], $fileKeys));

        $data = $this->normalizeArrayFields($data);

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

            $checkRows = function (array $columns) use ($data, $rowValidator) {
                $cols = array_map(fn ($key) => collect($data[$key] ?? []), $columns);
                $max = collect($cols)->map->count()->max();
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
                        $val = $rowVals[$idx] ?? '';
                        $upper = strtoupper($val);
                        $isNa = in_array($upper, ['NA', 'N/A', 'NONE'], true);
                        if ($val === '' || $isNa) {
                            if (!$isNa) {
                                $rowValidator->errors()->add("{$key}.{$i}", 'Complete all fields in this row or clear the first column.');
                            }
                        }
                    }
                }
            };

            $checkRows(['eligibility', 'rating', 'date', 'place', 'license_no', 'validity']);
            $checkRows(['work_from', 'work_to', 'work_position_title', 'work_department', 'work_status', 'work_govt_service']);

            if ($rowValidator->errors()->isNotEmpty()) {
                return redirect()->back()->withErrors($rowValidator)->withInput();
            }
        }

        if ($step === 3) {
            $rowValidator = Validator::make($data, []);

            $checkRows = function (array $columns) use ($data, $rowValidator) {
                $cols = array_map(fn ($key) => collect($data[$key] ?? []), $columns);
                $max = collect($cols)->map->count()->max();
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
                        $val = $rowVals[$idx] ?? '';
                        $upper = strtoupper($val);
                        $isNa = in_array($upper, ['NA', 'N/A', 'NONE'], true);
                        if ($val === '' || $isNa) {
                            if (!$isNa) {
                                $rowValidator->errors()->add("{$key}.{$i}", 'Complete all fields in this row or clear the entries.');
                            }
                        }
                    }
                }
            };

            $checkRows(['learning_title_of_ld', 'learning_from', 'learning_to', 'learning_hours', 'learning_type_of_ld', 'learning_conducted_sponsored_by']);
            $checkRows(['voluntary_organization', 'voluntary_from', 'voluntary_to', 'voluntary_hours', 'voluntary_position_nature_of_work']);
            $checkRows(['special_skills_hobbies', 'non_academic_distinctions_recognition', 'membership_in_association_organization']);

            if ($rowValidator->errors()->isNotEmpty()) {
                return redirect()->back()->withErrors($rowValidator)->withInput();
            }
        }

        $draft = PdsDraft::firstOrCreate(
            ['user_id' => $userId]
        );

        $existingData = $draft->data ?? [];

        $draft->data = $this->replaceArrays($existingData, $data);
        $draft->save();

        // keep session cache in sync per user
        session(['pds' => $draft->data, 'pds_owner' => $userId]);

        if ($step === 1) {
            $this->persistForm1ToDb($userId, $draft->data);
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
        \Log::info('Auto-save attempt', ['user_id' => $userId, 'has_data' => !empty($request->all())]);
        
        if (!$userId) {
            \Log::error('Auto-save failed: No user authenticated');
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $signaturePath = $this->storeSignature($request, $userId);
        $fileKeys = array_keys($request->allFiles());
        $data = $request->except(array_merge(['_token'], $fileKeys));

        // Explicitly remove education_N keys the client flagged as deleted
        $eduRemoved = $request->input('_edu_removed', []);
        unset($data['_edu_removed']);

        $data = $this->normalizeArrayFields($data);

        if ($signaturePath) {
            $data['signature_path'] = $signaturePath;
        }

        $draft = PdsDraft::firstOrCreate(['user_id' => $userId]);
        $existingData = $draft->data ?? [];

        // Delete explicitly removed education_N keys from existing draft
        foreach ($eduRemoved as $idx) {
            $key = 'education_' . intval($idx);
            unset($existingData[$key]);
        }

        $draft->data = $this->replaceArrays($existingData, $data);
        $draft->save();

        // keep session cache in sync per user
        session(['pds' => $draft->data, 'pds_owner' => $userId]);

        \Log::info('Auto-save successful', ['user_id' => $userId, 'data_keys' => array_keys($data)]);

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
            $data = $draft->data ?? [];
            unset($data[$key]);
            $draft->data = $data;
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

    public function form1()
    {
        $userId = Auth::id();
        $redirect = $this->redirectIfLocked($userId);
        if ($redirect) {
            return $redirect;
        }
        // clear stale session cache if it belongs to another user
        if (session('pds_owner') && session('pds_owner') !== $userId) {
            session()->forget(['pds', 'pds_owner']);
        }
        $draft = PdsDraft::where('user_id', $userId)->first();
        $data = $draft->data ?? [];
        // Get signature path from database only, not from draft data (to respect clearSignature)
        $signaturePath = DB::table('pds_signature_files')->where('user_id', $userId)->value('signature_file_path');

        // Remove signature data from the data array if database has no signature
        // This prevents JavaScript from restoring old signature from draft data
        if (!$signaturePath) {
            unset($data['signature_path']);
            unset($data['signature_data']);
        }

        if (!empty($data)) {
            session(['pds' => $data, 'pds_owner' => $userId]);
        }

        $highlightedSections = PdsRejection::where('user_id', $userId)->first()?->highlighted_sections ?? [];

        return view('pds_form.form1', compact('data', 'signaturePath', 'highlightedSections'));
    }

      public function form2()
    {
        $userId = Auth::id();
        $redirect = $this->redirectIfLocked($userId);
        if ($redirect) {
            return $redirect;
        }
        if (session('pds_owner') && session('pds_owner') !== $userId) {
            session()->forget(['pds', 'pds_owner']);
        }
        $draft = PdsDraft::where('user_id', $userId)->first();
        $data = $draft->data ?? [];
        $signaturePath = DB::table('pds_signature_files')->where('user_id', $userId)->value('signature_file_path');

        // Remove signature data from the data array if database has no signature
        if (!$signaturePath) {
            unset($data['signature_path']);
            unset($data['signature_data']);
        }

        if (!empty($data)) {
            session(['pds' => $data, 'pds_owner' => $userId]);
        }

        $highlightedSections = PdsRejection::where('user_id', $userId)->first()?->highlighted_sections ?? [];

        return view('pds_form.form2', compact('data', 'signaturePath', 'highlightedSections'));
    }

      public function form3()
    {
        $userId = Auth::id();
        $redirect = $this->redirectIfLocked($userId);
        if ($redirect) {
            return $redirect;
        }
        if (session('pds_owner') && session('pds_owner') !== $userId) {
            session()->forget(['pds', 'pds_owner']);
        }
        $draft = PdsDraft::where('user_id', $userId)->first();
        $data = $draft->data ?? [];
        $signaturePath = DB::table('pds_signature_files')->where('user_id', $userId)->value('signature_file_path');

        // Remove signature data from the data array if database has no signature
        if (!$signaturePath) {
            unset($data['signature_path']);
            unset($data['signature_data']);
        }

        if (!empty($data)) {
            session(['pds' => $data, 'pds_owner' => $userId]);
        }

        $highlightedSections = PdsRejection::where('user_id', $userId)->first()?->highlighted_sections ?? [];

        return view('pds_form.form3', compact('data', 'signaturePath', 'highlightedSections'));
    }

      public function form4()
    {
        $userId = Auth::id();
        $redirect = $this->redirectIfLocked($userId);
        if ($redirect) {
            return $redirect;
        }
        if (session('pds_owner') && session('pds_owner') !== $userId) {
            session()->forget(['pds', 'pds_owner']);
        }
        $draft = PdsDraft::where('user_id', $userId)->first();
        $data = $draft->data ?? [];
        $signaturePath = DB::table('pds_signature_files')->where('user_id', $userId)->value('signature_file_path');

        // Remove signature data from the data array if database has no signature
        if (!$signaturePath) {
            unset($data['signature_path']);
            unset($data['signature_data']);
        }

        if (!empty($data)) {
            session(['pds' => $data, 'pds_owner' => $userId]);
        }

        $highlightedSections = PdsRejection::where('user_id', $userId)->first()?->highlighted_sections ?? [];

        return view('pds_form.form4', compact('data', 'signaturePath', 'highlightedSections'));
    }

    public function form5()
    {
        $userId = Auth::id();
        $redirect = $this->redirectIfLocked($userId);
        if ($redirect) {
            return $redirect;
        }
        if (session('pds_owner') && session('pds_owner') !== $userId) {
            session()->forget(['pds', 'pds_owner']);
        }
        $draft = PdsDraft::where('user_id', $userId)->first();
        $data = $draft->data ?? [];
        $signaturePath = DB::table('pds_signature_files')->where('user_id', $userId)->value('signature_file_path');

        // Remove signature data from the data array if database has no signature
        if (!$signaturePath) {
            unset($data['signature_path']);
            unset($data['signature_data']);
        }

        if (!empty($data)) {
            session(['pds' => $data, 'pds_owner' => $userId]);
        }

        $highlightedSections = PdsRejection::where('user_id', $userId)->first()?->highlighted_sections ?? [];

        return view('pds_form.form5', compact('data', 'signaturePath', 'highlightedSections'));
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

        foreach ($singleSelectCheckboxes as $key) {
            if (array_key_exists($key, $data)) {
                $data[$key] = Arr::wrap($data[$key]);
            }
        }

        foreach ($educationExtraKeys as $key) {
            if (array_key_exists($key, $data)) {
                $data[$key] = Arr::wrap($data[$key]);
            }
        }

        // Handle dynamically added educational background tables (education_1, education_2, etc.)
        foreach ($data as $key => $value) {
            if (preg_match('/^education_\d+$/', $key)) {
                // Keep as-is since they are already nested arrays
                continue;
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
            if (is_array($value)) {
                $existing[$key] = $value;
            } else {
                $existing[$key] = $value;
            }
        }

        return $existing;
    }

    private function persistForm1ToDb(int $userId, array $d): void
    {
        $rowHasData = fn(array $row) => collect($row)->some(fn($v) => strlen(trim((string) $v)) > 0);

        DB::table('pds_personal_infos')->updateOrInsert(
            ['user_id' => $userId],
            [
                'surname'            => $d['surname'] ?? null,
                'firstname'          => $d['firstname'] ?? null,
                'middlename'         => $d['middlename'] ?? null,
                'name_extension'     => $d['employee_name_extension'] ?? null,
                'date_of_birth'      => $d['date_of_birth'] ?? null,
                'place_of_birth'     => $d['place_of_birth'] ?? null,
                'sex'                => collect($d['sex'] ?? [])->first(),
                'civil_status'       => collect($d['civilstatus'] ?? [])->first(),
                'height'             => $d['height'] ?? null,
                'weight'             => $d['weight'] ?? null,
                'blood_type'         => $d['blood_type'] ?? null,
                'umid_no'            => $d['umid_id_no'] ?? null,
                'country'            => $d['country'] ?? null,
                'pagibig_no'         => $d['pagibig_id_no'] ?? null,
                'philhealth_no'      => $d['philhealth_no'] ?? null,
                'philsys_no'         => $d['philsys_no'] ?? null,
                'tin_no'             => $d['tin_no'] ?? null,
                'agency_employee_no' => $d['agency_employee_no'] ?? null,
                'citizenship'        => collect($d['citizenship'] ?? [])->first(),
            ]
        );

        DB::table('pds_addresses')->updateOrInsert(
            ['user_id' => $userId],
            [
                'present_house_block_lot'       => $d['house_block_lot'] ?? null,
                'present_street'                => $d['street'] ?? null,
                'present_subdivision_village'   => $d['subdivision_village'] ?? null,
                'present_barangay'              => $d['baranggay'] ?? null,
                'present_city_municipality'     => $d['city_municipality'] ?? null,
                'present_province'              => $d['province'] ?? null,
                'present_zip_code'              => $d['zip_code'] ?? null,
                'permanent_house_block_lot'     => $d['permanent_house_block_lot'] ?? null,
                'permanent_street'              => $d['permanent_street'] ?? null,
                'permanent_subdivision_village' => $d['permanent_subdivision_village'] ?? null,
                'permanent_barangay'            => $d['permanent_baranggay'] ?? null,
                'permanent_city_municipality'   => $d['permanent_city_municipality'] ?? null,
                'permanent_province'            => $d['permanent_province'] ?? null,
            ]
        );

        DB::table('pds_contact_infos')->updateOrInsert(
            ['user_id' => $userId],
            [
                'telephone_no'  => $d['telephone_no'] ?? null,
                'mobile_no'     => $d['mobile_no'] ?? null,
                'email_address' => $d['email_address'] ?? null,
            ]
        );

        DB::table('pds_id_infos')->updateOrInsert(
            ['user_id' => $userId],
            [
                'gov_id'              => $d['gov_id'] ?? null,
                'passport_licence_id' => $d['licence_passport_id'] ?? null,
                'date_place_issuance' => $d['id_issue_date_place'] ?? null,
            ]
        );

        DB::table('pds_family_members')->where('user_id', $userId)->delete();

        $spouse = [
            'type'             => 'spouse',
            'firstname'        => $d['spouse_firstname'] ?? null,
            'middlename'       => $d['spouse_middlename'] ?? null,
            'surname'          => $d['spouse_surname'] ?? null,
            'name_extension'   => $d['spouse_name_extension'] ?? null,
            'occupation'       => $d['spouse_occupation'] ?? null,
            'employer'         => $d['spouse_employer_business_name'] ?? null,
            'business_address' => $d['spouse_business_address'] ?? null,
            'telephone_no'     => $d['spouse_telephone_no'] ?? null,
        ];
        if ($rowHasData(array_diff_key($spouse, ['type' => true]))) {
            DB::table('pds_family_members')->insert(array_merge($spouse, ['user_id' => $userId]));
        }

        $childNames = collect($d['children_familybg'] ?? []);
        $childDobs  = collect($d['children_dateofbirth_familybg'] ?? []);
        $children = $childNames->map(function ($name, $i) use ($childDobs, $userId) {
            return [
                'user_id'       => $userId,
                'type'          => 'child',
                'firstname'     => $name,
                'date_of_birth' => $childDobs->get($i),
            ];
        })->filter(function ($row) {
            return strlen(trim((string) ($row['firstname'] ?? ''))) > 0;
        });

        $hasRealChild = $children->contains(function ($row) {
            $name = strtoupper(trim((string) $row['firstname']));
            return $name !== '' && $name !== 'NA';
        });

        if ($hasRealChild) {
            $children = $children->filter(function ($row) {
                $name = strtoupper(trim((string) $row['firstname']));
                return $name !== '' && $name !== 'NA';
            });
        } elseif ($children->isNotEmpty()) {
            $children = collect([$children->first()]);
        }

        if ($children->isNotEmpty()) {
            DB::table('pds_family_members')->insert($children->all());
        }

        $father = [
            'type'           => 'father',
            'firstname'      => $d['father_firstname'] ?? null,
            'middlename'     => $d['father_middlename'] ?? null,
            'surname'        => $d['father_surname'] ?? null,
            'name_extension' => $d['father_name_extension'] ?? null,
        ];
        if ($rowHasData(array_diff_key($father, ['type' => true]))) {
            DB::table('pds_family_members')->insert(array_merge($father, ['user_id' => $userId]));
        }

        $mother = [
            'type'       => 'mother',
            'firstname'  => $d['mother_firstname'] ?? null,
            'middlename' => $d['mother_middlename'] ?? null,
            'surname'    => $d['mother_surname'] ?? null,
        ];
        if ($rowHasData(array_diff_key($mother, ['type' => true]))) {
            DB::table('pds_family_members')->insert(array_merge($mother, ['user_id' => $userId]));
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

    private function storeSignature(Request $request, int $userId): ?string
    {
        $disk = 'public';
        $directory = 'pds/signatures';

        $existingPath = DB::table('pds_signature_files')->where('user_id', $userId)->value('signature_file_path');
        $providedPath = $request->input('signature_path');
        if ($providedPath && !$request->hasFile('signature') && !$request->hasFile('signature_attachment')) {
            return $providedPath;
        }

        $deleteExisting = function (?string $path) use ($disk) {
            if ($path && Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        };

        $fileKeys = [
            'signature',
            'signature_attachment',
            'signature_attachment_1',
            'signature_attachment_2',
            'signature_attachment_3',
            'signature_attachment_4',
            'signature_attachment_5',
            'signature_file',
        ];

        foreach ($fileKeys as $key) {
            $uploaded = $request->file($key);
            if ($uploaded) {
                $filename = 'signature_' . $userId . '_' . time() . '.' . $uploaded->getClientOriginalExtension();
                $path = $uploaded->storeAs($directory, $filename, $disk);
                DB::table('pds_signature_files')->updateOrInsert(
                    ['user_id' => $userId],
                    ['signature_file_path' => $path]
                );
                if ($existingPath && $existingPath !== $path) {
                    $deleteExisting($existingPath);
                }
                return $path;
            }
        }

        $dataUrl = $request->input('signature_data');
        if ($dataUrl && str_starts_with($dataUrl, 'data:image')) {
            if (preg_match('/^data:(image\/[a-zA-Z0-9.+-]+);base64,(.*)$/', $dataUrl, $matches)) {
                $mime = $matches[1];
                $base64 = $matches[2];
                $binary = base64_decode($base64);
                if ($binary !== false) {
                    $extension = match ($mime) {
                        'image/png' => 'png',
                        'image/webp' => 'webp',
                        default => 'jpg',
                    };
                    $filename = 'signature_' . $userId . '_' . time() . '.' . $extension;
                    $path = $directory . '/' . $filename;
                    Storage::disk($disk)->put($path, $binary, 'public');
                    DB::table('pds_signature_files')->updateOrInsert(
                        ['user_id' => $userId],
                        ['signature_file_path' => $path]
                    );
                    if ($existingPath && $existingPath !== $path) {
                        $deleteExisting($existingPath);
                    }
                    return $path;
                }
            }
        }

        return $existingPath;
    }

    public function clearSignature(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Delete the signature file from storage
        $existingPath = DB::table('pds_signature_files')->where('user_id', $userId)->value('signature_file_path');
        if ($existingPath && Storage::disk('public')->exists($existingPath)) {
            Storage::disk('public')->delete($existingPath);
        }

        // Remove from signature files table
        DB::table('pds_signature_files')->where('user_id', $userId)->delete();

        // Remove from draft data
        $draft = PdsDraft::where('user_id', $userId)->first();
        if ($draft) {
            $draftData = $draft->data ?? [];
            unset($draftData['signature_path']);
            unset($draftData['signature_data']);
            $draft->data = $draftData;
            $draft->save();
        }

        // Clear session cache
        session()->forget('pds');
        session()->forget('pds_owner');

        return response()->json(['status' => 'ok']);
    }
}