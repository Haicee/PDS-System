<?php

namespace App\Http\Controllers;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PdsPdfController extends Controller
{
    public function download()
    {
        $userId = Auth::id();
        if (!$userId) {
            abort(403, 'Unauthorized');
        }

        // Form 1 tables
        $personal = DB::table('pds_personal_infos')->where('user_id', $userId)->first();
        $address = DB::table('pds_addresses')->where('user_id', $userId)->first();
        $contact = DB::table('pds_contact_infos')->where('user_id', $userId)->first();
        $idInfo = DB::table('pds_id_infos')->where('user_id', $userId)->first();
        $declaration = DB::table('pds_declarations')->where('user_id', $userId)->first();

        // Form 2 tables
        $family = DB::table('pds_family_members')->where('user_id', $userId)->get();
        $education = DB::table('pds_education_records')->where('user_id', $userId)->get();

        // Form 3 tables
        $eligibilities = DB::table('pds_eligibilities')->where('user_id', $userId)->get();
        $work = DB::table('pds_work_experiences')->where('user_id', $userId)->get();
        $voluntary = DB::table('pds_voluntary_work')->where('user_id', $userId)->get();
        $training = DB::table('pds_training_programs')->where('user_id', $userId)->get();

        // Form 4 tables
        $otherInfo = DB::table('pds_other_info')->where('user_id', $userId)->get();
        $references = DB::table('pds_references')->where('user_id', $userId)->get();

        // Form 5 table
        $remarks = DB::table('pds_form5_remarks')->where('user_id', $userId)->get();

        $data = compact(
            'personal',
            'address',
            'contact',
            'idInfo',
            'declaration',
            'family',
            'education',
            'eligibilities',
            'work',
            'voluntary',
            'training',
            'otherInfo',
            'references',
            'remarks'
        );

        $html = view('pds_form.pdf', $data)->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('a4', 'portrait');
        $dompdf->render();

        $filename = 'PDS_' . ($personal->surname ?? 'user') . '_' . now()->format('Y-m-d') . '.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
