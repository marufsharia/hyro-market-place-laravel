<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class FileUploadService
{
    /**
     * Upload a plugin logo file.
     *
     * @param UploadedFile $file The uploaded file
     * @param string|null $oldPath The path to the old file to delete (optional)
     * @return string The path to the uploaded file
     * @throws Exception If the upload fails
     */
    public function uploadPluginLogo(UploadedFile $file, ?string $oldPath = null): string
    {
        try {
            // Delete old file if exists
            if ($oldPath) {
                $this->deletePluginLogo($oldPath);
            }
            
            // Generate unique filename
            $filename = Str::uuid() . '.' . $file->extension();
            
            // Store file in public disk under plugins/logos directory
            $path = $file->storeAs('plugins/logos', $filename, 'public');
            
            if (!$path) {
                throw new Exception('Failed to store file');
            }
            
            return $path;
        } catch (Exception $e) {
            Log::error('File upload failed', [
                'filename' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            
            throw new Exception('Unable to upload file. Please try again.');
        }
    }
    
    /**
     * Delete a plugin logo file.
     *
     * @param string $path The path to the file to delete
     * @return void
     */
    public function deletePluginLogo(string $path): void
    {
        try {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        } catch (Exception $e) {
            Log::error('File deletion failed', [
                'path' => $path,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            
            // Don't throw exception for deletion failures
            // as this is not critical to the main operation
        }
    }
}
