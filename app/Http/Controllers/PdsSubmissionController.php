<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\PdsRejection;
use App\Notifications\PdsSubmitted;
use App\Notifications\PdsResubmitted;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\PdsSubmission;
use App\Models\User;
use App\Models\PdsDraft;
use App\Models\PdsForm5Remark;
use App\Services\PdsFileService;
use App\Services\PdsPersistenceService;

class PdsSubmissionController extends Controller
{
    public function __construct(
        private PdsFileService $fileService,
        private PdsPersistenceService $persistenceService,
    ) {}

    public function store(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) {
            abort(403, 'Unauthorized');
        }

        $draft = PdsDraft::where('user_id', $userId)->first();
        $draftData = $draft->data ?? [];
        $sessionData = session('pds', []);

        $incoming = $request->except(array_keys($request->allFiles()));
        $merged = $this->mergeFormData($draftData, $sessionData, $incoming);

        $request->merge($merged);

        session(['pds' => $merged]);
        $photoPath = $this->fileService->storePhoto($request, $userId);
        $signaturePath = $this->fileService->storeSignature($request, $userId);
        $rowHasData = function (array $row): bool {
            return collect($row)->some(fn ($v) => strlen(trim((string) $v)) > 0);
        };

        $ensureFirstRowNotBlank = function (array $fields, string $label) {
            $hasValue = collect($fields)->contains(fn ($v) => strlen(trim((string) $v)) > 0);
            if (!$hasValue) {
                abort(422, "Please fill out at least the first row of {$label} (enter NA if not applicable).");
            }
        };

        $validateNa = function (array $fields, string $label) {
            $flat = collect($fields)->flatten()->map(fn ($v) => Str::upper(trim((string) $v)));
            if ($flat->filter()->isNotEmpty()) {
                return;
            }
            if ($flat->contains('NA')) {
                return;
            }
            abort(422, "$label requires at least one entry or an 'NA'.");
        };

        $existingSignatureRow = DB::table('pds_signature_files')->where('user_id', $userId)->first();
        $existingPhotoPath = $existingSignatureRow->photo_file_path ?? null;
        $existingSignaturePath = $existingSignatureRow->signature_file_path ?? null;
        $existingThumbmarkPath = $existingSignatureRow->thumbmark_file_path ?? null;

        $ensureFirstRowNotBlank([
            $request->input('eligibility.0'),
            $request->input('rating.0'),
            $request->input('date.0'),
            $request->input('place.0'),
            $request->input('license_no.0'),
            $request->input('validity.0'),
        ], 'Civil Service Eligibility');

        $ensureFirstRowNotBlank([
            $request->input('work_from.0'),
            $request->input('work_to.0'),
            $request->input('work_position_title.0'),
            $request->input('work_department.0'),
            $request->input('work_status.0'),
            $request->input('work_govt_service.0'),
        ], 'Work Experience');

        $childNames = collect($request->input('children_familybg', []));
        $childDob   = collect($request->input('children_dateofbirth_familybg', []));
        $childNameDobValidator = Validator::make($request->all(), []);
        $childNames->each(function ($name, $i) use ($childDob, $childNameDobValidator) {
            $nameTrim = strtoupper(trim((string) $name));
            if ($nameTrim === '' || in_array($nameTrim, ['NA', 'N/A', 'NONE'], true)) {
                return;
            }
            if (trim((string) $childDob->get($i)) === '') {
                $childNameDobValidator->errors()->add("children_dateofbirth_familybg.$i", 'Date of birth is required for this child.');
            }
        });
        if ($childNameDobValidator->errors()->isNotEmpty()) {
            return redirect()->back()->withErrors($childNameDobValidator)->withInput();
        }

        $alreadySubmitted = PdsSubmission::where('user_id', $userId)->exists();

        DB::transaction(function () use ($request, $userId, $rowHasData, $validateNa, $signaturePath, $photoPath, $existingPhotoPath, $existingSignaturePath, $existingThumbmarkPath, $draftData) {
            $req = $request;
            $this->persistenceService->persistForm1($userId, $req->all());

            DB::table('pds_declarations')->updateOrInsert(
                ['user_id' => $userId],
                [
                    'date_accomplished' => $req->input('date5') ?? $req->input('date_accomplished'),
                    'q34_a' => $req->input('q34_a'),
                    'q34_a_details' => $req->input('q34_a_details'),
                    'q34_b' => $req->input('q34_b'),
                    'q34_b_details' => $req->input('q34_b_details'),
                    'q35_a' => $req->input('q35_a'),
                    'q35_a_details' => $req->input('q35_a_details'),
                    'q35_b' => $req->input('q35_b'),
                    'q35_b_details_date' => $req->input('q35_b_details_date'),
                    'q35_b_details_status' => $req->input('q35_b_details_status'),
                    'q36' => $req->input('q36'),
                    'q36_details' => $req->input('q36_details'),
                    'q37' => $req->input('q37'),
                    'q37_details' => $req->input('q37_details'),
                    'q38_a' => $req->input('q38_a'),
                    'q38_a_details' => $req->input('q38_a_details'),
                    'q38_b' => $req->input('q38_b'),
                    'q38_b_details' => $req->input('q38_b_details'),
                    'q39' => $req->input('q39'),
                    'q39_details' => $req->input('q39_details'),
                    'q40_a' => $req->input('q40_a'),
                    'q40_a_details' => $req->input('q40_a_details'),
                    'q40_b' => $req->input('q40_b'),
                    'q40_b_details' => $req->input('q40_b_details'),
                    'q40_c' => $req->input('q40_c'),
                    'q40_c_details' => $req->input('q40_c_details'),
                ]
            );

            DB::table('pds_education_records')->where('user_id', $userId)->delete();
            $eduArray = collect($req->input('education', []));

            $edu = $eduArray->map(function ($row, $level) use ($userId) {
                return [
                    'user_id' => $userId,
                    'level' => $level,
                    'school_name' => $row['school_name'] ?? null,
                    'degree_course' => $row['basic_education'] ?? null,
                    'from' => $row['from'] ?? null,
                    'to' => $row['to'] ?? null,
                    'highest_level' => $row['highest_level'] ?? null,
                    'year_graduated' => $row['year_graduated'] ?? null,
                    'academic_honors' => $row['scholarship_acadhonors'] ?? null,
                ];
            })->filter($rowHasData);

            $hasRealEdu = $edu->contains(function ($row) {
                $name = strtoupper(trim((string) $row['school_name']));
                return $name !== '' && $name !== 'NA';
            });

            if ($hasRealEdu) {
                $edu = $edu->filter(function ($row) {
                    $name = strtoupper(trim((string) $row['school_name']));
                    return $name !== '' && $name !== 'NA';
                });
            } elseif ($edu->isNotEmpty()) {
                $edu = collect([$edu->first()]);
            }

            $extraLevels  = $this->draftFallback($req, $draftData, 'education_extra_level');
            $extraSchools = $this->draftFallback($req, $draftData, 'education_extra_school_name');
            $extraBasics  = $this->draftFallback($req, $draftData, 'education_extra_basic_education');
            $extraFrom    = $this->draftFallback($req, $draftData, 'education_extra_from');
            $extraTo      = $this->draftFallback($req, $draftData, 'education_extra_to');
            $extraHighest = $this->draftFallback($req, $draftData, 'education_extra_highest_level');
            $extraYear    = $this->draftFallback($req, $draftData, 'education_extra_year_graduated');
            $extraHonors  = $this->draftFallback($req, $draftData, 'education_extra_scholarship_acadhonors');

            $extraEdu = collect($extraLevels)->map(function ($level, $i) use ($userId, $extraSchools, $extraBasics, $extraFrom, $extraTo, $extraHighest, $extraYear, $extraHonors) {
                return [
                    'user_id' => $userId,
                    'level' => $level ?? null,
                    'school_name' => $extraSchools[$i] ?? null,
                    'degree_course' => $extraBasics[$i] ?? null,
                    'from' => $extraFrom[$i] ?? null,
                    'to' => $extraTo[$i] ?? null,
                    'highest_level' => $extraHighest[$i] ?? null,
                    'year_graduated' => $extraYear[$i] ?? null,
                    'academic_honors' => $extraHonors[$i] ?? null,
                ];
            })->filter($rowHasData);

            $allEdu = $edu->concat($extraEdu);

            if ($allEdu->isNotEmpty()) {
                DB::table('pds_education_records')->insert($allEdu->values()->all());
            }

            $elig = collect($req->input('eligibility', []))->map(function ($val, $i) use ($req, $userId) {
                return [
                    'user_id' => $userId,
                    'eligibility' => $val,
                    'rating' => $req->input("rating.$i"),
                    'exam_date' => $req->input("date.$i"),
                    'exam_place' => $req->input("place.$i"),
                    'license_no' => $req->input("license_no.$i"),
                    'validity' => $req->input("validity.$i"),
                ];
            })->filter($rowHasData)->values();
            if ($elig->isNotEmpty()) {
                $validateNa([$req->input('eligibility', [])], 'Eligibilities');
                $this->syncTableRows('pds_eligibilities', $elig, $userId);
            } else {
                DB::table('pds_eligibilities')->where('user_id', $userId)->delete();
            }

            $work = collect($req->input('work_from', []))->map(function ($from, $i) use ($req, $userId) {
                return [
                    'user_id' => $userId,
                    'from' => $from,
                    'to' => $req->input("work_to.$i"),
                    'position_title' => $req->input("work_position_title.$i"),
                    'department' => $req->input("work_department.$i"),
                    'status' => $req->input("work_status.$i"),
                    'govt_service' => $req->input("work_govt_service.$i"),
                ];
            })->filter($rowHasData)->values();
            if ($work->isNotEmpty()) {
                $this->syncTableRows('pds_work_experiences', $work, $userId);
            } else {
                DB::table('pds_work_experiences')->where('user_id', $userId)->delete();
            }

            $vol = collect($req->input('voluntary_organization', []))->map(function ($org, $i) use ($req, $userId) {
                return [
                    'user_id' => $userId,
                    'organization' => $org,
                    'address' => $req->input("voluntary_address.$i"),
                    'from' => $req->input("voluntary_from.$i"),
                    'to' => $req->input("voluntary_to.$i"),
                    'hours' => $req->input("voluntary_hours.$i"),
                    'position' => $req->input("voluntary_position_nature_of_work.$i"),
                ];
            })->filter($rowHasData)->values();
            if ($vol->isNotEmpty()) {
                $validateNa([$req->input('voluntary_organization', [])], 'Voluntary work');
                $this->syncTableRows('pds_voluntary_work', $vol, $userId);
            } else {
                DB::table('pds_voluntary_work')->where('user_id', $userId)->delete();
            }

            // Collect main training table data
            $mainTrain = collect($req->input('learning_title_of_ld', []))->map(function ($title, $i) use ($req, $userId) {
                return [
                    'user_id' => $userId,
                    'title' => $title,
                    'from' => $req->input("learning_from.$i"),
                    'to' => $req->input("learning_to.$i"),
                    'hours' => $req->input("learning_hours.$i"),
                    'type_of_ld' => $req->input("learning_type_of_ld.$i"),
                    'conducted_by' => $req->input("learning_conducted_sponsored_by.$i"),
                ];
            })->filter($rowHasData);

            // Collect data from dynamic training tables (learning_1, learning_2, etc.)
            $extraTrain = collect();
            $allInputs = $req->all();
            foreach ($allInputs as $key => $value) {
                if (preg_match('/^learning_(\d+)$/', $key, $matches)) {
                    $tableData = $value;
                    $titles = $tableData['title_of_ld'] ?? [];
                    foreach ($titles as $i => $title) {
                        // Check if any data field has content (excluding user_id)
                        $hasData = collect([$title, $tableData['from'][$i] ?? null, $tableData['to'][$i] ?? null, $tableData['hours'][$i] ?? null, $tableData['type_of_ld'][$i] ?? null, $tableData['conducted_sponsored_by'][$i] ?? null])
                            ->some(fn ($v) => strlen(trim((string) $v)) > 0);
                        if (!$hasData) continue;

                        $extraTrain->push([
                            'user_id' => $userId,
                            'title' => $title,
                            'from' => $tableData['from'][$i] ?? null,
                            'to' => $tableData['to'][$i] ?? null,
                            'hours' => $tableData['hours'][$i] ?? null,
                            'type_of_ld' => $tableData['type_of_ld'][$i] ?? null,
                            'conducted_by' => $tableData['conducted_sponsored_by'][$i] ?? null,
                        ]);
                    }
                }
            }

            // Merge main and extra training data
            $train = $mainTrain->concat($extraTrain)->values();

            if ($train->isNotEmpty()) {
                $validateNa([$req->input('learning_title_of_ld', [])], 'Training');
                $this->syncTableRows('pds_training_programs', $train, $userId);
            } else {
                DB::table('pds_training_programs')->where('user_id', $userId)->delete();
            }

            $skills = collect($req->input('special_skills_hobbies', []))->map(fn ($v) => ['category' => 'skills', 'description' => $v]);
            $recognition = collect($req->input('non_academic_distinctions_recognition', []))->map(fn ($v) => ['category' => 'recognition', 'description' => $v]);
            $assoc = collect($req->input('membership_in_association_organization', []))->map(fn ($v) => ['category' => 'association', 'description' => $v]);
            $otherCombined = $skills->concat($recognition)->concat($assoc)->map(fn ($row) => array_merge($row, ['user_id' => $userId]))->filter($rowHasData);
            if ($otherCombined->isNotEmpty()) {
                $validateNa([$req->input('special_skills_hobbies', []), $req->input('non_academic_distinctions_recognition', []), $req->input('membership_in_association_organization', [])], 'Other info');
                $this->syncTableRows('pds_other_info', $otherCombined->values(), $userId);
            } else {
                DB::table('pds_other_info')->where('user_id', $userId)->delete();
            }

            $refs = collect($req->input('reference_name', []))->map(function ($name, $i) use ($req, $userId) {
                return [
                    'user_id' => $userId,
                    'name' => $name,
                    'address' => $req->input("reference_address.$i"),
                    'contact' => $req->input("reference_contact.$i"),
                ];
            })->filter(function ($row) use ($rowHasData) {
                $name = strtoupper(trim((string) ($row['name'] ?? '')));
                if ($name === 'NA' || $name === 'N/A') {
                    return false;
                }
                return $rowHasData($row);
            });

            DB::table('pds_references')->where('user_id', $userId)->delete();
            if ($refs->isNotEmpty()) {
                DB::table('pds_references')->insert($refs->all());
            }

            PdsForm5Remark::where('user_id', $userId)->delete();

            $durations = $req->input('duration', []);
            $positionTitles = $req->input('position_title', []);
            $officeUnits = $req->input('office_unit', []);
            $immediateSupervisors = $req->input('immediate_supervisor', []);
            $agencyLocations = $req->input('agency_location', []);
            $accomplishmentsIndexed = $req->input('accomplishments_indexed', []);
            $duties = $req->input('duties', []);

            $photoPathToPersist = $photoPath ?? $existingPhotoPath;
            $signaturePathToPersist = $signaturePath ?? ($existingSignaturePath ?? 'NA');
            $thumbmarkPathToPersist = $existingThumbmarkPath ?? 'NA';

            $workExperienceData = [];
            $maxRows = max(count($durations), count($positionTitles), count($officeUnits),
                          count($immediateSupervisors), count($agencyLocations), count($duties));

            for ($i = 0; $i < $maxRows; $i++) {
                $hasData = !empty($durations[$i]) || !empty($positionTitles[$i]) ||
                          !empty($officeUnits[$i]) || !empty($immediateSupervisors[$i]) ||
                          !empty($agencyLocations[$i]) || !empty($duties[$i]);

                if ($hasData) {
                    $rowAccomplishments = [];
                    if (!empty($accomplishmentsIndexed[$i]) && is_array($accomplishmentsIndexed[$i])) {
                        $rowAccomplishments = array_values(array_filter($accomplishmentsIndexed[$i], fn($v) => !empty(trim($v))));
                    }

                    $workExperienceData[] = [
                        'user_id' => $userId,
                        'duration' => $durations[$i] ?? null,
                        'position_title' => $positionTitles[$i] ?? null,
                        'office_unit' => $officeUnits[$i] ?? null,
                        'immediate_supervisor' => $immediateSupervisors[$i] ?? null,
                        'agency_location' => $agencyLocations[$i] ?? null,
                        'accomplishments' => $rowAccomplishments,
                        'duties' => $duties[$i] ?? null,
                        'signature_path' => $signaturePathToPersist,
                        'signature_data' => $req->input('signature_data'),
                        'date5' => $req->input('date5'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            
            if (!empty($workExperienceData)) {
                foreach ($workExperienceData as $row) {
                    PdsForm5Remark::create($row);
                }
            }

            DB::table('pds_signature_files')->updateOrInsert(
                ['user_id' => $userId],
                [
                    'photo_file_path' => $photoPathToPersist,
                    'signature_file_path' => $signaturePathToPersist,
                    'thumbmark_file_path' => $thumbmarkPathToPersist,
                ]
            );

            $user = User::find($userId);
            if ($user) {
                PdsSubmission::updateOrCreate(
                    ['user_id' => $userId],
                    [
                        'name' => $user->name,
                        'unit' => $user->unit,
                        'email' => $user->email,
                        'type' => $user->type,
                        'status' => 'Pending',
                        'submitted' => now(),
                    ]
                );

                    PdsRejection::where('user_id', $userId)->delete();
            }
        });

        session()->forget('pds');

        $user = User::find($userId);
        if ($user) {
            $notification = $alreadySubmitted
                ? new PdsResubmitted($user)
                : new PdsSubmitted($user);
            $this->notifyAdmins($notification);
        }

        return back()->with('status', 'PDS saved');
    }

    private function syncTableRows(string $table, \Illuminate\Support\Collection $rows, int $userId): void
    {
        $existing = DB::table($table)->where('user_id', $userId)->orderBy('id')->get();

        $rows->each(function ($row, $idx) use ($table, $existing) {
            $existingRow = $existing[$idx] ?? null;
            if ($existingRow) {
                DB::table($table)->where('id', $existingRow->id)->update($row);
            }
        });

        if ($rows->count() > $existing->count()) {
            DB::table($table)->insert($rows->slice($existing->count())->all());
        }

        if ($rows->count() < $existing->count()) {
            DB::table($table)->whereIn('id', $existing->slice($rows->count())->pluck('id'))->delete();
        }
    }

    private function draftFallback(Request $request, array $draftData, string $key): mixed
    {
        $val = $request->input($key);
        return (!empty($val)) ? $val : ($draftData[$key] ?? null);
    }

    private function mergeFormData(array $draft, array $session, array $incoming): array
    {
        $base = $draft;
        foreach ($session as $key => $value) {
            $base[$key] = $value;
        }
        foreach ($incoming as $key => $value) {
            $base[$key] = $value;
        }
        return $base;
    }

}
