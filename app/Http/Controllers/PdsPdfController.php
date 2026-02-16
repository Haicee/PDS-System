<?php

namespace App\Http\Controllers;

use Spatie\Browsershot\Browsershot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;    
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class PdsPdfController extends Controller
{
    // This method will render the PDF preview (auth)
    public function preview1(Request $request)
    {
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
        $pdfBinary = $this->makeShot($html)->pdf();

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="PDS_preview.pdf"'
        ]);
    }

    // Signed preview endpoint for Browsershot
    public function preview(Request $request)
    {
        $userId = $request->input('user_id', Auth::id());
        if (!$userId) {
            abort(403, 'Unauthorized');
        }

        return $this->renderPdfView($userId);
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
        $education = DB::table('pds_education_records')->where('user_id', $userId)->get();
        $eligibilities = DB::table('pds_eligibilities')->where('user_id', $userId)->get();
        $work = DB::table('pds_work_experiences')->where('user_id', $userId)->get();
        $voluntary = DB::table('pds_voluntary_work')->where('user_id', $userId)->get();
        $training = DB::table('pds_training_programs')->where('user_id', $userId)->get();
        $otherInfo = DB::table('pds_other_info')->where('user_id', $userId)->get();
        $references = DB::table('pds_references')->where('user_id', $userId)->get();
        $remarks = DB::table('pds_form5_remarks')->where('user_id', $userId)->get();

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
            'eligibilities',
            'work',
            'voluntary',
            'training',
            'otherInfo',
            'references',
            'remarks'
        );
    }

    private function renderPdfView($userId)
    {
        $data = $this->buildPdfData($userId);

        return view('pds_form.pdf', $data + ['pdfMode' => true]);
    }

    // This method downloads the PDF via Spatie Browsershot
    public function download()
    {
        $userId = Auth::id();
        if (!$userId) abort(403, 'Unauthorized');

        $data = $this->buildPdfData($userId);
        $personal = $data['personal'];
        $filename = 'PDS_' . ($personal->surname ?? 'user') . '_' . now()->format('Y-m-d') . '.pdf';

        $html = view('pds_form.pdf', $data + ['pdfMode' => true])->render();

        $pdfBinary = $this->makeShot($html)->pdf();

        return response()->streamDownload(
            function () use ($pdfBinary) {
                echo $pdfBinary;
            },
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    private function makeShot(string $html): Browsershot
    {
        // Browsershot forbids HTML containing file://. Strip any accidental file:// references to prevent HtmlIsNotAllowedToContainFile.
        $html = preg_replace('/file:\/\/[\w\.\-\/:]+/i', '', $html ?? '');

        $chromePath = env('BROWSERSHOT_CHROME_PATH', 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe');
        $nodePath = env('BROWSERSHOT_NODE_PATH', 'C:\\Program Files\\nodejs\\node.exe');
        $npmPath = env('BROWSERSHOT_NPM_PATH', 'C:\\Program Files\\nodejs\\npm.cmd');

        $shot = Browsershot::html($html)
            ->format('A4')
            ->margins(10, 10, 10, 10)
            ->scale(0.49) // reduced scale to fit content better
            ->emulateMedia('print')
            ->showBackground()
            ->setOption('printBackground', true)
            ->waitUntilNetworkIdle(false)
            ->timeout(240) // increased timeout for multiple pages
            ->setDelay(1000) // increased delay to ensure all content loads
            ->hideHeaderAndFooter()
            ->setOption('preferCSSPageSize', true)
            ->setOption('args', ['--disable-dev-shm-usage', '--no-sandbox'])
            ->paperSize(8.27, 11.7); // A4 size in inches

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
