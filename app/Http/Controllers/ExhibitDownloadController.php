<?php

namespace App\Http\Controllers;

use App\Models\Exhibit;
use App\Models\Exposition;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class ExhibitDownloadController extends Controller
{
    public function downloadExhibit(Exposition $exposition, Exhibit $exhibit): BinaryFileResponse
    {
        // Verify exhibit belongs to this exposition
        if ($exhibit->exposition_id !== $exposition->id) {
            abort(404, 'Exhibit not found in this exposition');
        }

        // Check if exposition is public or if user is owner
        $userId = Auth::id();
        if (!$exposition->is_public && (!$userId || $exposition->user_id !== $userId)) {
            abort(403, 'Unauthorized access to this exposition');
        }

        // Check if exhibit has media
        if (!$exhibit->media_path || !Storage::disk('public')->exists($exhibit->media_path)) {
            abort(404, 'Exhibit files not found');
        }

        // Create a temporary zip file
        $tempZip = tempnam(sys_get_temp_dir(), 'exhibit_');
        $zip = new ZipArchive();

        if ($zip->open($tempZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Unable to create zip file');
        }

        // Get all files in the exhibit's media folder
        $files = Storage::disk('public')->files($exhibit->media_path);

        foreach ($files as $file) {
            $content = Storage::disk('public')->get($file);
            $filename = basename($file);
            $zip->addFromString($filename, $content);
        }

        $zip->close();

        // Stream the zip file to the user
        return response()
            ->download($tempZip, "{$exhibit->title}_exhibit.zip")
            ->deleteFileAfterSend();
    }
}
