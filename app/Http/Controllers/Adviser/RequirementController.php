<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class RequirementController extends Controller
{
    public function view($id)
    {
        $requirement = DB::table('off_campus_requirements')
            ->where('requirement_id', $id)
            ->first();

        if (!$requirement) {
            abort(404, 'Requirement not found');
        }

        // Construct the full file path
        // The file_path in DB is like: permits/offcampus/{permit_id}/{filename}
        $fullPath = storage_path('app/' . $requirement->file_path);

        // Check if file exists
        if (!File::exists($fullPath)) {
            abort(404, 'File not found at: ' . $fullPath);
        }

        // Get file contents and mime type
        $file = File::get($fullPath);
        $mimeType = $requirement->mime_type ?? File::mimeType($fullPath);

        // Return file for viewing in browser
        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . ($requirement->original_filename ?? basename($fullPath)) . '"');
    }

    public function download($id)
    {
        $requirement = DB::table('off_campus_requirements')
            ->where('requirement_id', $id)
            ->first();

        if (!$requirement) {
            abort(404, 'Requirement not found');
        }

        // Construct the full file path
        $fullPath = storage_path('app/' . $requirement->file_path);

        // Check if file exists
        if (!File::exists($fullPath)) {
            abort(404, 'File not found');
        }

        // Get mime type
        $mimeType = $requirement->mime_type ?? File::mimeType($fullPath);
        $filename = $requirement->original_filename ?? basename($fullPath);

        // Return file as download
        return response()->download($fullPath, $filename, [
            'Content-Type' => $mimeType
        ]);
    }
}
