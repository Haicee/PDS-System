<?php

namespace App\Http\Controllers;

use Spatie\Browsershot\Browsershot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;    
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;

class PdsPdfController extends Controller
{
    /**
     * Content scale configurations for different zoom levels
     */
    private function getContentScale(string $scaleType = 'normal'): float
    {
        return match($scaleType) {
            'small' => 0.8,
            'normal' => 1.0,
            'large' => 1.2,
            'xlarge' => 1.4,
            default => 1.0
        };
    }

    /**
     * Parse individual table scales from request
     */
    private function getTableScales(Request $request): array
    {
        return [
            'firstTableScale' => $request->input('first_table_scale', $request->input('scale', 'normal')),
            'secondTableScale' => $request->input('second_table_scale', $request->input('scale', 'normal')),
            'thirdTableScale' => $request->input('third_table_scale', $request->input('scale', 'normal')),
            'fourthTableScale' => $request->input('fourth_table_scale', $request->input('scale', 'normal')),
        ];
    }
    // This method will render the PDF preview (auth)
    public function preview1(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) {
            abort(403, 'Unauthorized');
        }

        // Allow enough time for Browsershot/Chrome to generate the PDF (default PHP max is usually 30s)
        @set_time_limit(240);

        // If debug=1, show raw HTML for troubleshooting
        if ($request->boolean('debug')) {
            return $this->renderPdfView($userId);
        }

        $scaleType = $request->input('scale', 'normal');
        $contentScale = $this->getContentScale($scaleType);
        $tableScales = $this->getTableScales($request);
        
        // Convert scale types to actual values
        $tableScaleValues = array_map(fn($scale) => $this->getContentScale($scale), $tableScales);
        
        $data = $this->buildPdfData($userId);
        // Employee preview: render a lighter PDF by excluding the heavy Work Experience Sheet/remarks attachment page
        $html = view('pds_form.pdf', $data + [
                'pdfMode' => true,
                'contentScale' => $contentScale,
                'includeRemarks' => false,
            ] + $tableScaleValues
        )->render();
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

    // Admin preview for a specific user (HTML rendered in modal iframe)
    public function previewForAdmin(int $user)
    {
        $data = $this->buildPdfData($user);

        return view('pds_form.pdf', $data + ['pdfMode' => true]);
    }

    // Admin download PDF for a specific user
    public function downloadForAdmin(int $user, Request $request)
    {
        $scaleType = $request->input('scale', 'normal');
        $contentScale = $this->getContentScale($scaleType);
        $tableScales = $this->getTableScales($request);
        
        // Convert scale types to actual values
        $tableScaleValues = array_map(fn($scale) => $this->getContentScale($scale), $tableScales);
        
        $data = $this->buildPdfData($user);
        $personal = $data['personal'];
        $filename = 'PDS_' . ($personal->surname ?? 'user') . '_' . now()->format('Y-m-d') . '.pdf';

        $html = view('pds_form.pdf', $data + ['pdfMode' => true, 'contentScale' => $contentScale] + $tableScaleValues)->render();
        $pdfBinary = $this->makeShot($html)->pdf();

        return response()->streamDownload(
            function () use ($pdfBinary) {
                echo $pdfBinary;
            },
            $filename,
            ['Content-Type' => 'application/pdf']
        );
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
        // Keep user-entered order (insertion sequence) for work experiences
        $work = DB::table('pds_work_experiences')
            ->where('user_id', $userId)
            ->get();
        $voluntary = DB::table('pds_voluntary_work')->where('user_id', $userId)->get();
        $training = DB::table('pds_training_programs')->where('user_id', $userId)->get();
        $otherInfo = DB::table('pds_other_info')->where('user_id', $userId)->get();
        // Limit to the on-form capacity (7 rows) and keep stable insertion order
        $references = DB::table('pds_references')
            ->where('user_id', $userId)
            ->orderBy('id')
            ->limit(7)
            ->get();
        $remarks = DB::table('pds_form5_remarks')->where('user_id', $userId)->get();

        $signatureFiles = DB::table('pds_signature_files')->where('user_id', $userId)->first();
$signaturePath = $signatureFiles->signature_file_path ?? null;
$photoPath = $signatureFiles->photo_file_path ?? null;

// Browsershot-safe URL or Base64
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
    'eligibilities',
    'work',
    'voluntary',
    'training',
    'otherInfo',
    'references',
    'remarks',
    'signatureUrl',
    'photoUrl' // <-- use this in Blade
);
    }

    private function renderPdfView($userId)
    {
        $data = $this->buildPdfData($userId);

        return view('pds_form.pdf', $data + ['pdfMode' => true]);
    }

    // This method downloads the PDF via Spatie Browsershot
    public function download(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) abort(403, 'Unauthorized');

        // Allow enough time for Browsershot/Chrome to generate the PDF (employee download)
        @set_time_limit(240);

        $scaleType = $request->input('scale', 'normal');
        $contentScale = $this->getContentScale($scaleType);
        $tableScales = $this->getTableScales($request);
        
        // Convert scale types to actual values
        $tableScaleValues = array_map(fn($scale) => $this->getContentScale($scale), $tableScales);
        
        $data = $this->buildPdfData($userId);
        $personal = $data['personal'];
        $filename = 'PDS_' . ($personal->surname ?? 'user') . '_' . now()->format('Y-m-d') . '.pdf';

        $html = view('pds_form.pdf', $data + ['pdfMode' => true, 'contentScale' => $contentScale] + $tableScaleValues)->render();

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
        // Browsershot forbids HTML containing file://. Strip any accidental file:// or file:/ references (including backslashes/spaces) to prevent HtmlIsNotAllowedToContainFile.
        $html = $html ?? '';

        // Fast removal of protocol markers (handles variants like file:///, file:\, etc.)
        $html = str_ireplace([
            'file:///', 'file:\\', 'file://', 'file:/', 'file:\\/', 'file:\\', 'file:\\//'
        ], '', $html);

        // Extra guard: remove any remaining file: tokens up to the next whitespace/quote/angle bracket
        $html = preg_replace('#file:[^\s\"\
\n<>]+#i', '', $html);

        $chromePath = env('BROWSERSHOT_CHROME_PATH', 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe');
        $nodePath = env('BROWSERSHOT_NODE_PATH', 'C:\\Program Files\\nodejs\\node.exe');
        $npmPath = env('BROWSERSHOT_NPM_PATH', 'C:\\Program Files\\nodejs\\npm.cmd');

        $shot = Browsershot::html($html)
        ->paperSize(8.5, 13, 'in') // FORCE inches
        ->margins(10, 10, 10, 10)
        ->scale(.60)
        ->emulateMedia('print')
        ->showBackground()
        ->setOption('printBackground', true)
        ->timeout(240)
        // We only use inline styles and data URLs, so we don't need to wait for network idle
        ->setDelay(500)
        ->hideHeaderAndFooter()
        ->setOption('args', ['--disable-dev-shm-usage', '--no-sandbox']);


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
