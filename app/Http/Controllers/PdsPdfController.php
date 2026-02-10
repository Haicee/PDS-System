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
    public function preview1()
    {
        $userId = Auth::id();
        if (!$userId) {
            abort(403, 'Unauthorized');
        }

        return $this->renderPdfView($userId);
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

    private function renderPdfView($userId)
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

        $data = compact(
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

        return view('pds_form.pdf', $data + ['pdfMode' => true]);
    }

    // This method downloads the PDF
    public function download()
    {
        $userId = Auth::id();
        if (!$userId) abort(403, 'Unauthorized');

        $personal = DB::table('pds_personal_infos')->where('user_id', $userId)->first();
        $filename = 'PDS_' . ($personal->surname ?? 'user') . '_' . now()->format('Y-m-d') . '.pdf';
        $path = storage_path('app/' . $filename);

        $signedUrl = URL::signedRoute('pds.pdf.preview', ['user_id' => $userId], now()->addMinutes(10));

        // Generate PDF with Browsershot (Legal, full-width, print media)
        Browsershot::url($signedUrl)
            ->windowSize(1800, 2800)
            ->format('Legal')
            ->margins(4, 4, 4, 4)
            ->scale(0.95)
            ->emulateMedia('print')
            ->showBackground()
            ->waitUntilNetworkIdle()
            ->timeout(120)
            ->save($path);

        // Send PDF as download (force attachment)
        return response()
            ->download(
                $path,
                $filename,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"'
                ]
            )
            ->deleteFileAfterSend();
    }
}
