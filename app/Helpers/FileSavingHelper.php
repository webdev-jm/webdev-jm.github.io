<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;

class FileSavingHelper
{
    public static function saveFile($file, $id, $module)
    {
        // Define the relative path
        $relativePath = "{$module}/{$id}";

        // Ensure the directory exists and set permissions
        $fullPath = public_path("uploads/{$relativePath}");
        if (! is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
            chmod($fullPath, 0755);
        }

        // Get the original filename from the uploaded file.
        $originalName = $file->getClientOriginalName();

        // Sanitize the filename.
        // This regular expression replaces any character that is not a word character (\w includes a-z, A-Z, 0-9, _),
        // a dot (.), or a hyphen (-) with an underscore (_).
        // The 'u' flag ensures it correctly handles Unicode (UTF-8) characters.
        $sanitizedName = preg_replace('/[^\w.\-]+/u', '_', $originalName);

        // Generate a unique filename by prepending the current timestamp.
        $filename = time().'_'.$sanitizedName;

        // Move the file to the specified path using Laravel's Storage facade
        // The 'uploads' disk should be configured in your config/filesystems.php.
        $path = $file->storeAs($relativePath, $filename, 'uploads');

        ImageOptimizer::optimize(public_path("uploads/{$path}"));

        return 'uploads/'.$path;
    }

    public static function deleteFile($path)
    {
        // Strip the "uploads/" prefix if present
        $cleanPath = str_replace('uploads/', '', $path);

        // Delete the file using the 'uploads' disk
        return Storage::disk('uploads')->delete($cleanPath);
    }
}
