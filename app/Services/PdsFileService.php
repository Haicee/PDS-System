<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PdsFileService
{
    private const DISK = 'public';
    private const SIGNATURE_DIR = 'pds/signatures';
    private const PHOTO_DIR = 'passport_photo';

    public function storeSignature(Request $request, int $userId): ?string
    {
        $existingPath = DB::table('pds_signature_files')
            ->where('user_id', $userId)
            ->value('signature_file_path');

        $providedPath = $request->input('signature_path');
        if ($providedPath && !$request->hasFile('signature') && !$request->hasFile('signature_attachment')) {
            return $providedPath;
        }

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
                $path = $uploaded->storeAs(self::SIGNATURE_DIR, $filename, self::DISK);
                $this->deleteFile($existingPath);
                return $path;
            }
        }

        $dataUrl = $request->input('signature_data');
        if ($dataUrl && str_starts_with($dataUrl, 'data:image')) {
            $path = $this->saveBase64Image($dataUrl, $userId, 'signature_', self::SIGNATURE_DIR);
            if ($path) {
                $this->deleteFile($existingPath);
                return $path;
            }
        }

        return $existingPath;
    }

    public function storePhoto(Request $request, int $userId): ?string
    {
        $existingPath = DB::table('pds_signature_files')
            ->where('user_id', $userId)
            ->value('photo_file_path');

        $uploaded = $request->file('photo');
        if ($uploaded) {
            $filename = 'photo_' . $userId . '_' . time() . '.' . $uploaded->getClientOriginalExtension();
            $path = $uploaded->storeAs(self::PHOTO_DIR, $filename, self::DISK);
            $this->deleteFile($existingPath);
            $this->deleteOldUserPhotos($userId, $path);
            return $path;
        }

        $dataUrl = $request->input('photo_data');
        if ($dataUrl && str_starts_with($dataUrl, 'data:image')) {
            $path = $this->saveBase64Image($dataUrl, $userId, 'photo_', self::PHOTO_DIR);
            if ($path) {
                $this->deleteFile($existingPath);
                $this->deleteOldUserPhotos($userId, $path);
                return $path;
            }
        }

        return $existingPath;
    }

    private function saveBase64Image(string $dataUrl, int $userId, string $prefix, string $directory): ?string
    {
        if (!preg_match('/^data:(image\/[a-zA-Z0-9.+-]+);base64,(.*)$/', $dataUrl, $matches)) {
            return null;
        }

        $mime = $matches[1];
        $binary = base64_decode($matches[2]);
        if ($binary === false) {
            return null;
        }

        $extension = match ($mime) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };

        $path = $directory . '/' . $prefix . $userId . '_' . time() . '.' . $extension;
        Storage::disk(self::DISK)->put($path, $binary, 'public');

        return $path;
    }

    private function deleteFile(?string $path): void
    {
        if ($path && Storage::disk(self::DISK)->exists($path)) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    private function deleteOldUserPhotos(int $userId, string $exceptPath): void
    {
        $files = Storage::disk(self::DISK)->files(self::PHOTO_DIR);
        foreach ($files as $file) {
            if (str_starts_with(basename($file), "photo_{$userId}_") && $file !== $exceptPath) {
                Storage::disk(self::DISK)->delete($file);
            }
        }
    }
}
