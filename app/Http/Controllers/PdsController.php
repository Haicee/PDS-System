<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PdsController extends Controller
{
    public function view()
    {
        $userId = Auth::id();
        if (!$userId) {
            abort(403, 'Unauthorized');
        }

        $personal = DB::table('pds_personal_infos')->where('user_id', $userId)->first();
        $address = DB::table('pds_addresses')->where('user_id', $userId)->first();
        $contact = DB::table('pds_contact_infos')->where('user_id', $userId)->first();
        $idInfo = DB::table('pds_id_infos')->where('user_id', $userId)->first();

        $spouse = DB::table('pds_family_members')->where('user_id', $userId)->where('type', 'spouse')->first();
        $father = DB::table('pds_family_members')->where('user_id', $userId)->where('type', 'father')->first();
        $mother = DB::table('pds_family_members')->where('user_id', $userId)->where('type', 'mother')->first();
        $children = DB::table('pds_family_members')->where('user_id', $userId)->where('type', 'child')->get();

        $education = DB::table('pds_education_records')->where('user_id', $userId)->get();
        $eligibilities = DB::table('pds_eligibilities')->where('user_id', $userId)->get();
        $work = DB::table('pds_work_experiences')->where('user_id', $userId)->orderByDesc('from')->get();
        $voluntary = DB::table('pds_voluntary_work')->where('user_id', $userId)->orderByDesc('from')->get();
        
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
            'eligibilities',
            'work',
            'voluntary'
        ));
    }

    public function review2()
    {
        $userId = Auth::id();
        if (!$userId) {
            abort(403, 'Unauthorized');
        }

        $eligibilities = DB::table('pds_eligibilities')->where('user_id', $userId)->get();
        $workExperiences = DB::table('pds_work_experiences')->where('user_id', $userId)->orderBy('from')->get();

        return view('pdsreview.pdsreview2', compact('eligibilities', 'workExperiences'));
    }

    public function review3()
    {
        $userId = Auth::id();
        if (!$userId) {
            abort(403, 'Unauthorized');
        }

        $voluntaryWorks = DB::table('pds_voluntary_work')
            ->where('user_id', $userId)
            ->orderBy('from')
            ->get();

        $training = DB::table('pds_training_programs')
            ->where('user_id', $userId)
            ->orderBy('from')
            ->get();
        
        $other = DB::table('pds_other_info')
            ->where('user_id', $userId)
            ->get();


        return view('pdsreview.pdsreview3', compact('voluntaryWorks', 'training', 'other'));
    }

    public function review4()
    {
        $userId = Auth::id();
        if (!$userId) {
            abort(403, 'Unauthorized');
        }

        $declaration = DB::table('pds_declarations')->where('user_id', $userId)->first();
        $idInfo = DB::table('pds_id_infos')->where('user_id', $userId)->first();
        $references = DB::table('pds_references')->where('user_id', $userId)->get();

        return view('pdsreview.pdsreview4', compact('declaration', 'idInfo', 'references'));
    }

    public function review5()
    {
        $userId = Auth::id();
        if (!$userId) {
            abort(403, 'Unauthorized');
        }

        $remarks = DB::table('pds_form5_remarks')->where('user_id', $userId)->get();

        return view('pdsreview.pdsreview5', compact('remarks'));
    }
}
