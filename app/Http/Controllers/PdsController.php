<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\PdsForm5Remark;
use App\Repositories\PdsRepository;

class PdsController extends Controller
{
    public function __construct(private PdsRepository $repository) {}

    public function view()
    {
        $userId = $this->requireUserId();
        $pdsData = $this->repository->getAllPdsData($userId);
        [$signaturePath, $photoPath] = $this->getSignaturePaths($userId);

        $family = $pdsData['family'];
        $education = $pdsData['education'];

        // Build extra education tables using repository
        $extraEduTables = $this->repository->buildExtraEducationTables($education);

        return view('pdsreview.pdsreview1', [
            'personal' => $pdsData['personal'],
            'address' => $pdsData['address'],
            'contact' => $pdsData['contact'],
            'idInfo' => $pdsData['idInfo'],
            'spouse' => $family->where('type', 'spouse')->first(),
            'father' => $family->where('type', 'father')->first(),
            'mother' => $family->where('type', 'mother')->first(),
            'children' => $family->where('type', 'child')->values(),
            'education' => $education,
            'extraEduTables' => $extraEduTables,
            'eligibilities' => $pdsData['eligibilities'],
            'work' => $pdsData['workExperiences'],
            'voluntary' => $pdsData['voluntaryWorks'],
            'declaration' => $pdsData['declaration'],
            'signaturePath' => $signaturePath,
            'photoPath' => $photoPath,
        ]);
    }

    public function review2()
    {
        $userId = $this->requireUserId();
        $pdsData = $this->repository->getAllPdsData($userId);
        [$signaturePath, $photoPath] = $this->getSignaturePaths($userId);

        return view('pdsreview.pdsreview2', [
            'eligibilities' => $pdsData['eligibilities'],
            'workExperiences' => $pdsData['workExperiences'],
            'declaration' => $pdsData['declaration'],
            'signaturePath' => $signaturePath,
            'photoPath' => $photoPath,
        ]);
    }

    public function review3()
    {
        $userId = $this->requireUserId();
        $pdsData = $this->repository->getAllPdsData($userId);
        [$signaturePath, $photoPath] = $this->getSignaturePaths($userId);

        $training = $pdsData['training'];

        // Build extra training tables using repository
        $extraTrainingTables = $this->repository->buildExtraTrainingTables($training['added']);

        return view('pdsreview.pdsreview3', [
            'voluntaryWorks' => $pdsData['voluntaryWorks'],
            'training' => $training['main'],
            'extraTrainingTables' => $extraTrainingTables,
            'other' => $pdsData['otherInfo'],
            'declaration' => $pdsData['declaration'],
            'signaturePath' => $signaturePath,
            'photoPath' => $photoPath,
        ]);
    }

    public function review4()
    {
        $userId = $this->requireUserId();
        $pdsData = $this->repository->getAllPdsData($userId);
        [$signaturePath, $photoPath] = $this->getSignaturePaths($userId);

        $passportPhotoUrl = null;
        if ($photoPath) {
            $filename = basename($photoPath);
            $sanitized = $filename ? 'passport_photo/' . $filename : null;
            if ($sanitized && Storage::disk('public')->exists($sanitized)) {
                $passportPhotoUrl = $this->assetFromPublicDisk($sanitized);
            }
        }

        return view('pdsreview.pdsreview4', [
            'declaration' => $pdsData['declaration'],
            'idInfo' => $pdsData['idInfo'],
            'references' => $pdsData['references'],
            'passportPhotoUrl' => $passportPhotoUrl,
            'signaturePath' => $signaturePath,
        ]);
    }

    public function review5()
    {
        $userId = $this->requireUserId();
        $pdsData = $this->repository->getAllPdsData($userId);
        [$signaturePath, $photoPath] = $this->getSignaturePaths($userId);

        return view('pdsreview.pdsreview5', [
            'workExperiences' => $pdsData['remarks'],
            'declaration' => $pdsData['declaration'],
            'signaturePath' => $signaturePath,
            'photoPath' => $photoPath,
        ]);
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
        $signatureFiles = $this->repository->getSignatureFiles($userId);

        return [
            ($row->signature_file_path ?? null) === 'NA' ? null : ($row->signature_file_path ?? null),
            ($row->photo_file_path ?? null) === 'NA' ? null : ($row->photo_file_path ?? null),
        ];
    }

}
