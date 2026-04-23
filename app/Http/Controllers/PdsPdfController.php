<?php

namespace App\Http\Controllers;

use Spatie\Browsershot\Browsershot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\PdsDraftDataService;

class PdsPdfController extends Controller
{
    public function __construct(private PdsDraftDataService $draftDataService) {}

    public function preview1(Request $request)
    {
        $this->initPdfEnv();
        $userId = Auth::id();
        if (!$userId) {
            abort(403, 'Unauthorized');
        }

        // If debug=1, show raw HTML for troubleshooting
        if ($request->boolean('debug')) {
            return $this->renderPdfView($userId);
        }

        $data = $this->buildPdfData($userId);
        $html = view('pds_form.pdf', $data + ['pdfMode' => true])->render();
        
        try {
            $pdfBinary = $this->makeShot($html)->pdf();
            
            return response($pdfBinary, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="PDS_preview.pdf"'
            ]);
        } catch (\Exception $e) {
            // If PDF generation fails, return HTML view with error message
            return response()->view('pds_form.pdf', $data + ['pdfMode' => true, 'pdfError' => $e->getMessage()]);
        }
    }

    public function preview(Request $request)
    {
        $userId = $request->input('user_id', Auth::id());
        if (!$userId) {
            abort(403, 'Unauthorized');
        }

        return $this->renderPdfView($userId);
    }

    public function previewForAdmin(int $user)
    {
        $data = $this->buildPdfData($user);

        return view('pds_form.pdf', $data + ['pdfMode' => true]);
    }

    public function downloadForAdmin(int $user)
    {
        $this->initPdfEnv();
        $data     = $this->buildPdfData($user);
        $filename = $this->pdfFilename($data['personal']);

        return $this->streamPdf($data, $filename);
    }

    private function buildPdfData($userId)
    {
        $personal = DB::table('pds_personal_infos')->where('user_id', $userId)->first();
        $address = DB::table('pds_addresses')->where('user_id', $userId)->first();
        $contact = DB::table('pds_contact_infos')->where('user_id', $userId)->first();
        $idInfo = DB::table('pds_id_infos')->where('user_id', $userId)->first();
        $declaration = DB::table('pds_declarations')->where('user_id', $userId)->first();

        $family = DB::table('pds_family_members')->where('user_id', $userId)->get();
        $spouse = $family->where('type', 'spouse')->first();
        $father = $family->where('type', 'father')->first();
        $mother = $family->where('type', 'mother')->first();
        $children = $family->where('type', 'child')->values();
        // Fetch education from database (authoritative source for review)
        $education = DB::table('pds_education_records')->where('user_id', $userId)->get();

        // Extra education tables (dynamic tables) only exist in drafts
        $draft = DB::table('pds_drafts')->where('user_id', $userId)->first();
        $extraEduTables = collect();
        if ($draft && !empty($draft->data)) {
            $draftData = is_array($draft->data) ? $draft->data : json_decode($draft->data, true);
            $extraEduTables = $this->draftDataService->buildExtraEduTables($draftData);
        }
        $eligibilities = DB::table('pds_eligibilities')
            ->where('user_id', $userId)
            ->where(function ($query) {
                $query->whereNotNull('eligibility')
                      ->where('eligibility', '!=', '')
                      ->whereNotIn('eligibility', ['NA', 'N/A', 'NONE']);
            })
            ->get();
        $work = DB::table('pds_work_experiences')
            ->where('user_id', $userId)
            ->get();
        $voluntary = DB::table('pds_voluntary_work')->where('user_id', $userId)->get();
        // Fetch training from database (authoritative source for review)
        $training = DB::table('pds_training_programs')->where('user_id', $userId)->get();

        // Extra training tables (dynamic tables) only exist in drafts
        $extraTrainingTables = collect();
        if ($draft && !empty($draft->data)) {
            $draftData = is_array($draft->data) ? $draft->data : json_decode($draft->data, true);
            $extraTrainingTables = $this->draftDataService->buildExtraTrainingTables($draftData);
        }

        $otherInfo = DB::table('pds_other_info')->where('user_id', $userId)->get();
        $references = DB::table('pds_references')
            ->where('user_id', $userId)
            ->orderBy('id')
            ->limit(7)
            ->get();
        $remarks = DB::table('pds_form5_remarks')->where('user_id', $userId)->orderBy('id')->get();

        $signatureFiles = DB::table('pds_signature_files')->where('user_id', $userId)->first();
        $signaturePath = $signatureFiles->signature_file_path ?? null;
        $photoPath = $signatureFiles->photo_file_path ?? null;

        $signatureUrl = null;
        $photoUrl = null;

        if ($signaturePath && file_exists(storage_path('app/public/' . $signaturePath))) {
            $signatureUrl = 'data:image/png;base64,' . base64_encode(file_get_contents(storage_path('app/public/' . $signaturePath)));
        }

        if ($photoPath && file_exists(storage_path('app/public/' . $photoPath))) {
            $photoUrl = 'data:image/png;base64,' . base64_encode(file_get_contents(storage_path('app/public/' . $photoPath)));
        }

        return compact(
            'personal',
            'address',
            'contact',
            'idInfo',
            'declaration',
            'family',
            'spouse',
            'father',
            'mother',
            'children',
            'education',
            'extraEduTables',
            'eligibilities',
            'work',
            'voluntary',
            'training',
            'extraTrainingTables',
            'otherInfo',
            'references',
            'remarks',
            'signatureUrl',
            'photoUrl'
        );
    }

    private function renderPdfView($userId)
    {
        $data = $this->buildPdfData($userId);

        return view('pds_form.pdf', $data + ['pdfMode' => true]);
    }

    public function download()
    {
        $this->initPdfEnv();
        $userId = Auth::id();
        if (!$userId) abort(403, 'Unauthorized');

        $data     = $this->buildPdfData($userId);
        $filename = $this->pdfFilename($data['personal']);

        return $this->streamPdf($data, $filename);
    }

    private function initPdfEnv(): void
    {
        set_time_limit(180);
        ini_set('memory_limit', '512M');
    }

    private function pdfFilename(?object $personal): string
    {
        return 'PDS_' . ($personal->surname ?? 'user') . '_' . now()->format('Y-m-d') . '.pdf';
    }

    private function streamPdf(array $data, string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $html      = view('pds_form.pdf', $data + ['pdfMode' => true])->render();
        $pdfBinary = $this->makeShot($html)->pdf();

        return response()->streamDownload(
            function () use ($pdfBinary) { echo $pdfBinary; },
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    private function makeShot(string $html): Browsershot
    {
        $html = str_ireplace([
            'file:///', 'file:\\', 'file://', 'file:/', 'file:\\/', 'file:\\', 'file:\\//'
        ], '', $html);

        $html = preg_replace('#file:[^\s\"' . "\n" . '<>]+#i', '', $html);

        $chromePath = env('BROWSERSHOT_CHROME_PATH', 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe');
        $nodePath   = env('BROWSERSHOT_NODE_PATH',   'C:\\Program Files\\nodejs\\node.exe');
        $npmPath    = env('BROWSERSHOT_NPM_PATH',    'C:\\Program Files\\nodejs\\npm.cmd');

        $shot = Browsershot::html($html)
            ->paperSize(8.5, 13, 'in')
            ->margins(10, 10, 10, 10)
            ->scale(.56)
            ->emulateMedia('print')
            ->showBackground()
            ->setOption('printBackground', true)
            ->timeout(180)
            ->noSandbox()
            ->hideHeaderAndFooter()
            ->disableJavascript()
            ->setOption('args', [
                '--disable-dev-shm-usage',
                '--disable-gpu',
                '--no-first-run',
                '--disable-extensions',
            ]);

        if (is_file($nodePath)) {
            $shot->setNodeBinary($nodePath);
        }
        if (is_file($npmPath)) {
            $shot->setNpmBinary($npmPath);
        }
        if (is_file($chromePath)) {
            $shot->setChromePath($chromePath);
        }

        return $shot;
    }
}