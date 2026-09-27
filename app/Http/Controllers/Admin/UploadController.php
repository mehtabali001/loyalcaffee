<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MediaUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function __invoke(Request $request, MediaUploader $uploader): JsonResponse
    {
        $cfg = config('loyal.uploads');
        $request->validate([
            'files' => ['required', 'array', 'max:30'],
            'files.*' => ['file', 'max:'.$cfg['max_kb'], 'mimes:'.implode(',', $cfg['mimes'])],
            'folder' => ['nullable', 'alpha_dash', 'max:30'],
        ]);

        $out = [];
        foreach ($request->file('files') as $file) {
            $path = $uploader->store($file, $request->input('folder', 'uploads'));
            $out[] = ['path' => $path, 'url' => media_url($path)];
        }

        return response()->json(['files' => $out], 201);
    }
}
