<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PdsDraft;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class PdsStepController extends Controller
{
    public function saveStep(Request $request, int $step)
    {
        $userId = Auth::id();

        $signaturePath = $this->storeSignature($request, $userId);
        $fileKeys = array_keys($request->allFiles());
        $data = $request->except(array_merge(['_token'], $fileKeys));

        $data = $this->normalizeArrayFields($data);

        if ($signaturePath) {
            $data['signature_path'] = $signaturePath;
        }

        $draft = PdsDraft::firstOrCreate(
            ['user_id' => $userId]
        );

        $existingData = $draft->data ?? [];

        $draft->data = array_replace_recursive($existingData, $data);
        $draft->save();

        // keep session cache in sync per user
        session(['pds' => $draft->data, 'pds_owner' => $userId]);

        $nextRoute = match ($step) {
            1 => 'pds.form2',
            2 => 'pds.form3',
            3 => 'pds.form4',
            4 => 'pds.form5',
            default => 'pds.form5',
        };

        return redirect()->route($nextRoute)
            ->with('status', 'Saved step ' . $step);
    }

    public function autoSave(Request $request)
    {
        $userId = Auth::id();
        \Log::info('Auto-save attempt', ['user_id' => $userId, 'has_data' => !empty($request->all())]);
        
        if (!$userId) {
            \Log::error('Auto-save failed: No user authenticated');
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $signaturePath = $this->storeSignature($request, $userId);
        $fileKeys = array_keys($request->allFiles());
        $data = $request->except(array_merge(['_token'], $fileKeys));

        $data = $this->normalizeArrayFields($data);

        if ($signaturePath) {
            $data['signature_path'] = $signaturePath;
        }

        $draft = PdsDraft::firstOrCreate(['user_id' => $userId]);
        $existingData = $draft->data ?? [];

        $draft->data = array_replace_recursive($existingData, $data);
        $draft->save();

        // keep session cache in sync per user
        session(['pds' => $draft->data, 'pds_owner' => $userId]);

        \Log::info('Auto-save successful', ['user_id' => $userId, 'data_keys' => array_keys($data)]);

        return response()->json(['status' => 'ok', 'saved_keys' => array_keys($data)]);
    }

    public function draft()
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $draft = PdsDraft::where('user_id', $userId)->first();

        return response()->json([
            'data' => $draft->data ?? [],
        ]);
    }

    public function form1()
    {
        $userId = Auth::id();
        // clear stale session cache if it belongs to another user
        if (session('pds_owner') && session('pds_owner') !== $userId) {
            session()->forget(['pds', 'pds_owner']);
        }
        $draft = PdsDraft::where('user_id', $userId)->first();
        $data = $draft->data ?? [];
        $signaturePath = $data['signature_path'] ?? DB::table('pds_signature_files')->where('user_id', $userId)->value('signature_file_path');

        return view('pds_form.form1', compact('data', 'signaturePath'));
    }

      public function form2()
    {
        $userId = Auth::id();
        if (session('pds_owner') && session('pds_owner') !== $userId) {
            session()->forget(['pds', 'pds_owner']);
        }
        $draft = PdsDraft::where('user_id', $userId)->first();
        $data = $draft->data ?? [];
        $signaturePath = $data['signature_path'] ?? DB::table('pds_signature_files')->where('user_id', $userId)->value('signature_file_path');

        return view('pds_form.form2', compact('data', 'signaturePath'));
    }

      public function form3()
    {
        $userId = Auth::id();
        if (session('pds_owner') && session('pds_owner') !== $userId) {
            session()->forget(['pds', 'pds_owner']);
        }
        $draft = PdsDraft::where('user_id', $userId)->first();
        $data = $draft->data ?? [];
        $signaturePath = $data['signature_path'] ?? DB::table('pds_signature_files')->where('user_id', $userId)->value('signature_file_path');

        return view('pds_form.form3', compact('data', 'signaturePath'));
    }

      public function form4()
    {
        $userId = Auth::id();
        if (session('pds_owner') && session('pds_owner') !== $userId) {
            session()->forget(['pds', 'pds_owner']);
        }
        $draft = PdsDraft::where('user_id', $userId)->first();
        $data = $draft->data ?? [];
        $signaturePath = $data['signature_path'] ?? DB::table('pds_signature_files')->where('user_id', $userId)->value('signature_file_path');

        return view('pds_form.form4', compact('data', 'signaturePath'));
    }

    public function form5()
    {
        $userId = Auth::id();
        if (session('pds_owner') && session('pds_owner') !== $userId) {
            session()->forget(['pds', 'pds_owner']);
        }
        $draft = PdsDraft::where('user_id', $userId)->first();
        $data = $draft->data ?? [];
        $signaturePath = $data['signature_path'] ?? DB::table('pds_signature_files')->where('user_id', $userId)->value('signature_file_path');

        return view('pds_form.form5', compact('data', 'signaturePath'));
    }

    /**
     * Ensure checkbox groups are stored as arrays even when only one option is selected.
     */
    private function normalizeArrayFields(array $data): array
    {
        $singleSelectCheckboxes = ['sex', 'civilstatus', 'citizenship'];

        foreach ($singleSelectCheckboxes as $key) {
            if (array_key_exists($key, $data)) {
                $data[$key] = Arr::wrap($data[$key]);
            }
        }

        if (array_key_exists('remarks', $data)) {
            $data['remarks'] = Arr::wrap($data['remarks']);
        }

        return $data;
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

        $deleteExisting = function (?string $path) use ($disk) {
            if ($path && Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        };

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
                DB::table('pds_signature_files')->updateOrInsert(
                    ['user_id' => $userId],
                    ['signature_file_path' => $path]
                );
                if ($existingPath && $existingPath !== $path) {
                    $deleteExisting($existingPath);
                }
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
                    DB::table('pds_signature_files')->updateOrInsert(
                        ['user_id' => $userId],
                        ['signature_file_path' => $path]
                    );
                    if ($existingPath && $existingPath !== $path) {
                        $deleteExisting($existingPath);
                    }
                    return $path;
                }
            }
        }

        return $existingPath;
    }
}