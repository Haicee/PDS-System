<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\PdsSubmission;
use App\Models\User;
use App\Models\PdsDraft;

class PdsSubmissionController extends Controller
{
    public function store(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) {
            abort(403, 'Unauthorized');
        }

        // Merge prior step data (draft + session) with current payload to avoid null inserts
        $draft = PdsDraft::where('user_id', $userId)->first();
        $draftData = $draft->data ?? [];
        $sessionData = session('pds', []);

        $incoming = $request->except(array_keys($request->allFiles()));
        $merged = array_replace_recursive($draftData, $sessionData, $incoming);

        $request->merge($merged);

        // store merged step without files for consistency
        session(['pds' => $merged]);
        $req = $request;
        $userId = Auth::id();
        $photoPath = $this->storePhoto($request, $userId);
        $signaturePath = $this->storeSignature($request, $userId);
        $rowHasData = function (array $row): bool {
            return collect($row)->some(fn ($v) => strlen(trim((string) $v)) > 0);
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

        $newPhotoPath = null;
        if ($req->hasFile('photo')) {
            $newPhotoPath = $req->file('photo')->store('passport_photo', 'public');
        } elseif ($req->filled('photo_data')) {
            // Fallback: data URL from camera capture saved in session
            $dataUrl = $req->input('photo_data');
            if (str_starts_with($dataUrl, 'data:image')) {
                [$meta, $content] = explode(',', $dataUrl, 2);
                $ext = 'jpg';
                if (preg_match('/data:image\/(.+);base64/', $meta, $m)) {
                    $ext = $m[1];
                }
                $binary = base64_decode($content, true);
                if ($binary !== false) {
                    $filename = 'passport_photo/' . uniqid('passport_', true) . '.' . $ext;
                    Storage::disk('public')->put($filename, $binary);
                    $newPhotoPath = $filename;
                }
            }
        }

        $oldPhotoPath = null;
        $existingSignatureRow = DB::table('pds_signature_files')->where('user_id', $userId)->first();

        DB::transaction(function () use ($req, $userId, $rowHasData, $validateNa, $newPhotoPath, &$oldPhotoPath, $signaturePath) {
            DB::table('pds_personal_infos')->updateOrInsert(
                ['user_id' => $userId],
                [
                    'surname' => $req->input('surname'),
                    'firstname' => $req->input('firstname'),
                    'middlename' => $req->input('middlename'),
                    'name_extension' => $req->input('employee_name_extension'),
                    'date_of_birth' => $req->input('date_of_birth'),
                    'place_of_birth' => $req->input('place_of_birth'),
                    'sex' => collect($req->input('sex', []))->first(),
                    'civil_status' => collect($req->input('civilstatus', []))->first(),
                    'height' => $req->input('height'),
                    'weight' => $req->input('weight'),
                    'blood_type' => $req->input('blood_type'),
                    'umid_no' => $req->input('umid_id_no'),
                    'country' => $req->input('country'),
                    'pagibig_no' => $req->input('pagibig_id_no'),
                    'philhealth_no' => $req->input('philhealth_no'),
                    'philsys_no' => $req->input('philsys_no'),
                    'tin_no' => $req->input('tin_no'),
                    'agency_employee_no' => $req->input('agency_employee_no'),
                    'citizenship' => collect($req->input('citizenship', []))->first(),
                ]
            );

            DB::table('pds_addresses')->updateOrInsert(
                ['user_id' => $userId],
                [
                    'present_house_block_lot' => $req->input('house_block_lot'),
                    'present_street' => $req->input('street'),
                    'present_subdivision_village' => $req->input('subdivision_village'),
                    'present_barangay' => $req->input('baranggay'),
                    'present_city_municipality' => $req->input('city_municipality'),
                    'present_province' => $req->input('province'),
                    'present_zip_code' => $req->input('zip_code'),
                    'permanent_house_block_lot' => $req->input('permanent_house_block_lot'),
                    'permanent_street' => $req->input('permanent_street'),
                    'permanent_subdivision_village' => $req->input('permanent_subdivision_village'),
                    'permanent_barangay' => $req->input('permanent_baranggay'),
                    'permanent_city_municipality' => $req->input('permanent_city_municipality'),
                    'permanent_province' => $req->input('permanent_province'),
                ]
            );

            DB::table('pds_contact_infos')->updateOrInsert(
                ['user_id' => $userId],
                [
                    'telephone_no' => $req->input('telephone_no'),
                    'mobile_no' => $req->input('mobile_no'),
                    'email_address' => $req->input('email_address'),
                ]
            );

            DB::table('pds_id_infos')->updateOrInsert(
                ['user_id' => $userId],
                [
                    'gov_id' => $req->input('gov_id'),
                    'passport_licence_id' => $req->input('licence_passport_id'),
                    'date_place_issuance' => $req->input('id_issue_date_place'),
                ]
            );

            DB::table('pds_signature_files')->updateOrInsert(
                ['user_id' => $userId],
                [
                    'signature_file_path' => $signaturePath,
                    'photo_file_path' => $photoPath,
                ]
            );

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

            DB::table('pds_family_members')->where('user_id', $userId)->delete();

            $spouse = [
                'type' => 'spouse',
                'firstname' => $req->input('spouse_firstname'),
                'middlename' => $req->input('spouse_middlename'),
                'surname' => $req->input('spouse_surname'),
                'name_extension' => $req->input('spouse_name_extension'),
                'occupation' => $req->input('spouse_occupation'),
                'employer' => $req->input('spouse_employer_business_name'),
                'business_address' => $req->input('spouse_business_address'),
                'telephone_no' => $req->input('spouse_telephone_no'),
            ];
            if ($rowHasData($spouse)) {
                DB::table('pds_family_members')->insert(array_merge($spouse, ['user_id' => $userId]));
            }

            $childNames = collect($req->input('children_familybg', []));
            $childDob = collect($req->input('children_dateofbirth_familybg', []));

            $children = $childNames->map(function ($name, $i) use ($childDob, $userId) {
                return [
                    'user_id' => $userId,
                    'type' => 'child',
                    'firstname' => $name,
                    'date_of_birth' => $childDob->get($i),
                ];
            })->filter($rowHasData);

            $hasRealChild = $children->contains(function ($row) {
                $name = strtoupper(trim((string) $row['firstname']));
                return $name !== '' && $name !== 'NA';
            });

        if ($oldPhotoPath && $newPhotoPath && $oldPhotoPath !== $newPhotoPath) {
            $oldFilename = basename($oldPhotoPath);
            $oldSanitized = $oldFilename ? 'passport_photo/' . $oldFilename : null;
            if ($oldSanitized && Storage::disk('public')->exists($oldSanitized)) {
                Storage::disk('public')->delete($oldSanitized);
            }
        }

        if ($oldPhotoPath && $newPhotoPath && $oldPhotoPath !== $newPhotoPath) {
            $oldFilename = basename($oldPhotoPath);
            $oldSanitized = $oldFilename ? 'passport_photo/' . $oldFilename : null;
            if ($oldSanitized && Storage::disk('public')->exists($oldSanitized)) {
                Storage::disk('public')->delete($oldSanitized);
            }
        }

            if ($hasRealChild) {
                $children = $children->filter(function ($row) {
                    $name = strtoupper(trim((string) $row['firstname']));
                    return $name !== '' && $name !== 'NA';
                });
            } elseif ($children->isNotEmpty()) {
                // Keep only the first NA entry
                $first = $children->first();
                $children = collect([$first]);
            }

            if ($children->isNotEmpty()) {
                DB::table('pds_family_members')->insert($children->all());
            }

            $father = [
                'type' => 'father',
                'firstname' => $req->input('father_firstname'),
                'middlename' => $req->input('father_middlename'),
                'surname' => $req->input('father_surname'),
                'name_extension' => $req->input('father_name_extension'),
            ];
            if ($rowHasData($father)) {
                DB::table('pds_family_members')->insert(array_merge($father, ['user_id' => $userId]));
            }

            $mother = [
                'type' => 'mother',
                'firstname' => $req->input('mother_firstname'),
                'middlename' => $req->input('mother_middlename'),
                'surname' => $req->input('mother_surname'),
            ];
            if ($rowHasData($mother)) {
                DB::table('pds_family_members')->insert(array_merge($mother, ['user_id' => $userId]));
            }

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

            if ($edu->isNotEmpty()) {
                DB::table('pds_education_records')->insert($edu->all());
            }

            DB::table('pds_eligibilities')->where('user_id', $userId)->delete();
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
            })->filter($rowHasData);
            if ($elig->isNotEmpty()) {
                $validateNa([$req->input('eligibility', [])], 'Eligibilities');
                DB::table('pds_eligibilities')->insert($elig->all());
            }

            DB::table('pds_work_experiences')->where('user_id', $userId)->delete();
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
            })->filter($rowHasData);
            if ($work->isNotEmpty()) {
                $validateNa([$req->input('work_from', [])], 'Work experience');
                DB::table('pds_work_experiences')->insert($work->all());
            }

            DB::table('pds_voluntary_work')->where('user_id', $userId)->delete();
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
            })->filter($rowHasData);
            if ($vol->isNotEmpty()) {
                $validateNa([$req->input('voluntary_organization', [])], 'Voluntary work');
                DB::table('pds_voluntary_work')->insert($vol->all());
            }

            DB::table('pds_training_programs')->where('user_id', $userId)->delete();
            $train = collect($req->input('learning_title_of_ld', []))->map(function ($title, $i) use ($req, $userId) {
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
            if ($train->isNotEmpty()) {
                $validateNa([$req->input('learning_title_of_ld', [])], 'Training');
                DB::table('pds_training_programs')->insert($train->all());
            }

            DB::table('pds_other_info')->where('user_id', $userId)->delete();
            $skills = collect($req->input('special_skills_hobbies', []))->map(fn ($v) => ['category' => 'skills', 'description' => $v]);
            $recognition = collect($req->input('non_academic_distinctions_recognition', []))->map(fn ($v) => ['category' => 'recognition', 'description' => $v]);
            $assoc = collect($req->input('membership_in_association_organization', []))->map(fn ($v) => ['category' => 'association', 'description' => $v]);
            $otherCombined = $skills->concat($recognition)->concat($assoc)->map(fn ($row) => array_merge($row, ['user_id' => $userId]))->filter($rowHasData);
            if ($otherCombined->isNotEmpty()) {
                $validateNa([$req->input('special_skills_hobbies', []), $req->input('non_academic_distinctions_recognition', []), $req->input('membership_in_association_organization', [])], 'Other info');
                DB::table('pds_other_info')->insert($otherCombined->all());
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

            if ($refs->isNotEmpty()) {
                // Use upsert to avoid PK collisions
                DB::table('pds_references')->upsert($refs->all(), ['id'], ['name','address','contact','user_id']);
                // ensure only current user's refs remain
                DB::table('pds_references')->where('user_id', $userId)->whereNotIn('id', function ($q) use ($refs, $userId) {
                    $q->select('id')->from('pds_references')->where('user_id', $userId)->orderBy('id')->limit($refs->count());
                });
            } else {
                DB::table('pds_references')->where('user_id', $userId)->delete();
            }

            DB::table('pds_form5_remarks')->where('user_id', $userId)->delete();
            $remarks = collect($req->input('remarks', []))->filter(fn ($v) => strlen(trim((string) $v)) > 0);
            if ($remarks->isNotEmpty()) {
                DB::table('pds_form5_remarks')->insert($remarks->map(fn ($v) => ['user_id' => $userId, 'remarks' => $v])->all());
            }

            $photoPathToPersist = $newPhotoPath ?? ($existingSignatureRow->photo_file_path ?? null);
            $oldPhotoPath = $existingSignatureRow->photo_file_path ?? null;

            DB::table('pds_signature_files')->updateOrInsert(
                ['user_id' => $userId],
                [
                    'photo_file_path' => $photoPathToPersist,
                    'signature_file_path' => $existingSignatureRow->signature_file_path ?? 'NA',
                    'thumbmark_file_path' => $existingSignatureRow->thumbmark_file_path ?? 'NA',
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
                        'submitted' => now(),
                    ]
                );
            }
        });

        session()->forget('pds');

        return back()->with('status', 'PDS saved');
    }

    /**
     * Persist submitted or cached photo to storage and return its path.
     */
    private function storePhoto(Request $request, int $userId): ?string
    {
        $disk = 'public';
        $directory = 'pds/photos';
        $existingPath = DB::table('pds_signature_files')->where('user_id', $userId)->value('photo_file_path');

        $uploaded = $request->file('photo');
        if ($uploaded) {
            $filename = 'photo_' . $userId . '_' . time() . '.' . $uploaded->getClientOriginalExtension();
            $path = $uploaded->storeAs($directory, $filename, $disk);
            return $path;
        }

        $dataUrl = $request->input('photo_data');
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
                    $filename = 'photo_' . $userId . '_' . time() . '.' . $extension;
                    $path = $directory . '/' . $filename;
                    Storage::disk($disk)->put($path, $binary, 'public');
                    return $path;
                }
            }
        }

        return $existingPath;
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
                    return $path;
                }
            }
        }

        return $existingPath;
    }
}
