<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class PdsStepController extends Controller
{
    public function saveStep(Request $request, int $step)
    {
        // Do not store uploaded files in session (they are not serializable)
        $fileKeys = array_keys($request->allFiles());
        $data = $request->except(array_merge(['_token'], $fileKeys));

        $existing = session('pds', []);
        session(['pds' => array_replace_recursive($existing, $data)]);

        // Track names of fields seen (all textareas/inputs are considered required for front-end gating)
        $seen = session('pds_required_names', []);
        $currentNames = array_keys($data);
        session(['pds_required_names' => array_values(array_unique(array_merge($seen, $currentNames)))]);

        // Map next route
        $nextRoute = match ($step) {
            1 => 'pds.form2',
            2 => 'pds.form3',
            3 => 'pds.form4',
            4 => 'pds.form5',
            default => 'pds.form5',
        };

        return Redirect::route($nextRoute)->with('status', 'Saved step '.$step);
    }
}
