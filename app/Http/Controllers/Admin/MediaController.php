<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $uploadedUrls = [];

        foreach ($request->file('files') as $file) {
            $filename = Str::random(10) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/pages/media', $filename);
            
            // GrapeJS expects an array of objects with `src` property
            $uploadedUrls[] = [
                'src' => Storage::url($path)
            ];
        }

        return response()->json([
            'data' => $uploadedUrls
        ]);
    }
}
