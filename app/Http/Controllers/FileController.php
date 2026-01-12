<?php

// ============================================
// FileController.php
// ============================================

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    // Upload a file
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:5120', // 5 MB
        ]);

        $file = $request->file('file');
        $disk = config('filesystems.default');

        // Unique path
        $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('uploads', $filename, $disk);

        return response()->json([
            'path' => $path,
            // Return URL to controller endpoint, not direct storage URL
            'url'  => route('files.show', ['file' => $filename]),
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
        ]);
    }

    // Serve a file by filename
    public function show(string $file)
    {
        $disk = config('filesystems.default');
        
        // Reconstruct the path with uploads directory
        $path = 'uploads/' . $file;
        
        // Validate and prevent directory traversal
        if (str_contains($file, '..') || str_contains($file, '/')) {
            abort(403, 'Invalid file path');
        }

        abort_unless(Storage::disk($disk)->exists($path), 404);

        // Get file info
        $mimeType = Storage::disk($disk)->mimeType($path);
        $size = Storage::disk($disk)->size($path);
        $lastModified = Storage::disk($disk)->lastModified($path);

        // Use StreamedResponse for better compatibility
        return response()->stream(
            function () use ($disk, $path) {
                $stream = Storage::disk($disk)->readStream($path);
                if ($stream === false) {
                    abort(500, 'Failed to read file');
                }
                fpassthru($stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
            },
            200,
            [
                'Content-Type' => $mimeType,
                'Content-Length' => $size,
                'Content-Disposition' => 'inline; filename="' . basename($file) . '"',
                'Cache-Control' => 'public, max-age=31536000',
                'Last-Modified' => gmdate('D, d M Y H:i:s', $lastModified) . ' GMT',
                'Accept-Ranges' => 'bytes',
            ]
        );
    }

    // Delete a file by filename
    public function destroy(string $file)
    {
        $disk = config('filesystems.default');
        
        // Reconstruct the path with uploads directory
        $path = 'uploads/' . $file;
        
        // Validate and prevent directory traversal
        if (str_contains($file, '..') || str_contains($file, '/')) {
            abort(403, 'Invalid file path');
        }

        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }

        return response()->noContent();
    }
}
