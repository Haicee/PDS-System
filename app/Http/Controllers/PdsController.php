<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\PdsRejection;
use App\Models\PdsSubmission;
use App\Models\PdsForm5Remark;
use App\Services\PdsDraftDataService;

class PdsController extends Controller
{
    public function __construct(private PdsDraftDataService $draftDataService) {}

    public function view()
    {
        $userId = $this->requireUserId();

        [$signaturePath, $photoPath] = $this->getSignaturePaths($userId);

        $personal = DB::table('pds_personal_infos')->where('user_id', $userId)->first();
        $address = DB::table('pds_addresses')->where('user_id', $userId)->first();
        $contact = DB::table('pds_contact_infos')->where('user_id', $userId)->first();
        $idInfo = DB::table('pds_id_infos')->where('user_id', $userId)->first();

        $family   = DB::table('pds_family_members')->where('user_id', $userId)->get();
        $spouse   = $family->where('type', 'spouse')->first();
        $father   = $family->where('type', 'father')->first();
        $mother   = $family->where('type', 'mother')->first();
        $children = $family->where('type', 'child')->values();

        // Fetch education from database (authoritative source for review)
        $education = DB::table('pds_education_records')->where('user_id', $userId)->get();

        // Extra education tables (dynamic tables) only exist in drafts
        $draft = DB::table('pds_drafts')->where('user_id', $userId)->first();
        $extraEduTables = collect();
        if ($draft && !empty($draft->data)) {
            $data = is_array($draft->data) ? $draft->data : json_decode($draft->data, true);
            $extraEduTables = $this->draftDataService->buildExtraEduTables($data);
        }
        $eligibilities = DB::table('pds_eligibilities')->where('user_id', $userId)->get();
        $work = DB::table('pds_work_experiences')->where('user_id', $userId)->orderByDesc('from')->get();
        $voluntary = DB::table('pds_voluntary_work')->where('user_id', $userId)->orderByDesc('from')->get();
        $declaration = DB::table('pds_declarations')->where('user_id', $userId)->first();
        
        return view('pdsreview.pdsreview1', compact(
            'personal',
            'address',
            'contact',
            'idInfo',
            'spouse',
            'father',
            'mother',
            'children',
            'education',
            'extraEduTables',
            'eligibilities',
            'work',
            'voluntary',
            'declaration',
            'signaturePath',
            'photoPath'
        ));
    }

    public function review2()
    {
        $userId = $this->requireUserId();

        [$signaturePath, $photoPath] = $this->getSignaturePaths($userId);

        $eligibilities = DB::table('pds_eligibilities')
            ->where('user_id', $userId)
            ->where(function ($query) {
                $query->whereNotNull('eligibility')
                      ->where('eligibility', '!=', '')
                      ->whereNotIn('eligibility', ['NA', 'N/A', 'NONE']);
            })
            ->get();
        $workExperiences = DB::table('pds_work_experiences')->where('user_id', $userId)->get();
        $declaration = DB::table('pds_declarations')->where('user_id', $userId)->first();

        return view('pdsreview.pdsreview2', compact('eligibilities', 'workExperiences', 'declaration', 'signaturePath', 'photoPath'));
    }

    public function review3()
    {
        $userId = $this->requireUserId();

        [$signaturePath, $photoPath] = $this->getSignaturePaths($userId);

        $voluntaryWorks = DB::table('pds_voluntary_work')
            ->where('user_id', $userId)
            ->get();

        // Fetch training from database (authoritative source for review)
        $training = DB::table('pds_training_programs')
            ->where('user_id', $userId)
            ->get();

        // Extra training tables (dynamic tables) only exist in drafts
        $draft = DB::table('pds_drafts')->where('user_id', $userId)->first();
        $extraTrainingTables = collect();
        if ($draft && !empty($draft->data)) {
            $draftData = is_array($draft->data) ? $draft->data : json_decode($draft->data, true);
            $extraTrainingTables = $this->draftDataService->buildExtraTrainingTables($draftData);
        }

        $other = DB::table('pds_other_info')
            ->where('user_id', $userId)
            ->get();

        $declaration = DB::table('pds_declarations')->where('user_id', $userId)->first();

        return view('pdsreview.pdsreview3', compact('voluntaryWorks', 'training', 'extraTrainingTables', 'other', 'declaration', 'signaturePath', 'photoPath'));
    }

    public function review4()
    {
        $userId = $this->requireUserId();

        [$signaturePath, $photoPath] = $this->getSignaturePaths($userId);

        $declaration = DB::table('pds_declarations')->where('user_id', $userId)->first();
        $idInfo = DB::table('pds_id_infos')->where('user_id', $userId)->first();
        $references = DB::table('pds_references')
            ->where('user_id', $userId)
            ->orderBy('id')
            ->limit(7)
            ->get();
        $passportPhotoUrl = null;
        if ($photoPath) {
            $filename = basename($photoPath);
            $sanitized = $filename ? 'passport_photo/' . $filename : null;
            if ($sanitized && Storage::disk('public')->exists($sanitized)) {
                $passportPhotoUrl = $this->assetFromPublicDisk($sanitized);
            }
        }

        return view('pdsreview.pdsreview4', compact('declaration', 'idInfo', 'references', 'passportPhotoUrl', 'signaturePath'));
    }

    public function review5()
    {
        $userId = $this->requireUserId();

        [$signaturePath, $photoPath] = $this->getSignaturePaths($userId);

        $workExperiences = PdsForm5Remark::where('user_id', $userId)->orderBy('id')->get();
        $declaration = DB::table('pds_declarations')->where('user_id', $userId)->first();

        return view('pdsreview.pdsreview5', compact('workExperiences', 'declaration', 'signaturePath', 'photoPath'));
    }

    private function requireUserId(): int
    {
        $userId = Auth::id();
        if (!$userId) {
            abort(403, 'Unauthorized');
        }
        return $userId;
    }

    private function getSignaturePaths(int $userId): array
    {
        $row = DB::table('pds_signature_files')->where('user_id', $userId)->first();

        return [
            $row->signature_file_path ?? null,
            $row->photo_file_path ?? null,
        ];
    }
}
