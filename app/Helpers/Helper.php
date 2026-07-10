<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;

class Helper
{
    public static function uploadImage($photo, string $folder = 'photos'): array
    {
        if (! $photo) {
            return ['status' => false, 'name' => ''];
        }

        $fileName = self::renameFile($photo->getClientOriginalName());
        $folderPath = public_path('uploads/' . trim($folder, '/'));

        if (! File::exists($folderPath)) {
            File::makeDirectory($folderPath, 0755, true);
        }

        $photo->move($folderPath, $fileName);

        return [
            'status' => true,
            'name' => 'uploads/' . trim($folder, '/') . '/' . $fileName,
        ];
    }

    public static function renameFile(string $fullFilename = ''): string
    {
        $extension = $fullFilename ? pathinfo($fullFilename, PATHINFO_EXTENSION) : 'jpg';
        $milliseconds = floor(microtime(true) * 10000);

        return date('d-m-Y-H-i-') . $milliseconds . '.' . strtolower($extension ?: 'jpg');
    }

    public static function unlinkImage(?string $photoName): void
    {
        if ($photoName && file_exists(public_path($photoName))) {
            unlink(public_path($photoName));
        }
    }
}
