<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class FileUploadHelper
{
    /**
     * Allowed image MIME types
     */
    private static $allowedImageMimes = [
        'image/jpeg',
        'image/png',
        'image/jpg',
        'image/webp'
    ];

    /**
     * Allowed image extensions
     */
    private static $allowedImageExtensions = [
        'jpg',
        'jpeg',
        'png',
        'webp'
    ];

    /**
     * Maximum file size in bytes (10MB)
     * Can be overridden via getMaxFileSize() method
     */
    private static $maxFileSize = 10485760;

    /**
     * Get maximum file size in bytes
     * Can be configured via environment variable or config
     *
     * @return int
     */
    public static function getMaxFileSize(): int
    {
        return config('upload.max_file_size', self::$maxFileSize);
    }

    /**
     * Get maximum file size in kilobytes for validation rules
     *
     * @return int
     */
    public static function getMaxFileSizeKB(): int
    {
        return (int) (self::getMaxFileSize() / 1024);
    }

    /**
     * Get human-readable file size
     *
     * @return string
     */
    public static function getMaxFileSizeHuman(): string
    {
        $bytes = self::getMaxFileSize();
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 1) . $units[$i];
    }

    /**
     * Validate uploaded image file
     *
     * @param UploadedFile $file
     * @return array
     */
    public static function validateImageFile(UploadedFile $file): array
    {
        $errors = [];

        // Check file size
        $maxSize = self::getMaxFileSize();
        if ($file->getSize() > $maxSize) {
            $maxSizeMB = round($maxSize / 1048576, 1);
            $errors[] = "File size must not exceed {$maxSizeMB}MB";
        }

        // Check MIME type
        if (!in_array($file->getMimeType(), self::$allowedImageMimes)) {
            $errors[] = 'Invalid file type. Only JPEG, PNG, and WebP images are allowed';
        }

        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, self::$allowedImageExtensions)) {
            $errors[] = 'Invalid file extension';
        }

        // Check if file is actually an image by trying to read it
        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->path());

            // Additional security: check image dimensions
            if ($image->width() > 5000 || $image->height() > 5000) {
                $errors[] = 'Image dimensions too large. Maximum 5000x5000 pixels';
            }

            if ($image->width() < 10 || $image->height() < 10) {
                $errors[] = 'Image dimensions too small. Minimum 10x10 pixels';
            }
        } catch (\Exception $e) {
            $errors[] = 'Invalid image file or corrupted data';

            // Log security violation for corrupted/malicious files
            \Log::warning('File upload security violation - corrupted or malicious file', [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'error' => $e->getMessage(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'user_id' => auth()->check() ? auth()->id() : 'guest'
            ]);
        }

        // Log successful file validation for monitoring
        if (empty($errors)) {
            \Log::info('File upload validation successful', [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'user_id' => auth()->check() ? auth()->id() : 'guest'
            ]);
        } else {
            // Log validation failures
            \Log::warning('File upload validation failed', [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'errors' => $errors,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'user_id' => auth()->check() ? auth()->id() : 'guest'
            ]);
        }

        return $errors;
    }

    /**
     * Generate secure filename
     *
     * @param UploadedFile $file
     * @param string $prefix
     * @return string
     */
    public static function generateSecureFilename(UploadedFile $file, string $prefix = ''): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $timestamp = now()->timestamp;
        $random = bin2hex(random_bytes(8));

        return $prefix . $timestamp . '_' . $random . '.' . $extension;
    }

    /**
     * Create directory if it doesn't exist
     *
     * @param string $path
     * @return bool
     */
    public static function ensureDirectoryExists(string $path): bool
    {
        if (!File::exists($path)) {
            return File::makeDirectory($path, 0755, true);
        }
        return true;
    }

    /**
     * Delete file if it exists
     *
     * @param string $filePath
     * @return bool
     */
    public static function deleteFileIfExists(string $filePath): bool
    {
        if (File::exists($filePath)) {
            return File::delete($filePath);
        }
        return true;
    }

    /**
     * Process and save image with security measures
     *
     * @param UploadedFile $file
     * @param string $destinationPath
     * @param string $filename
     * @param int $maxWidth
     * @param int $maxHeight
     * @return bool
     */
    public static function processAndSaveImage(
        UploadedFile $file,
        string $destinationPath,
        string $filename,
        int $maxWidth = 1200,
        int $maxHeight = 1200
    ): bool {
        try {
            // Ensure directory exists
            self::ensureDirectoryExists($destinationPath);

            // Process image
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->path());

            // Strip EXIF data for privacy and security
            $image = $image->removeAnimation();

            // Resize if necessary
            if ($image->width() > $maxWidth || $image->height() > $maxHeight) {
                $image->scale(width: $maxWidth, height: $maxHeight);
            }

            // Save with quality optimization
            $image->save($destinationPath . '/' . $filename, quality: 85);

            return true;
        } catch (\Exception $e) {
            \Log::error('Image processing failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get validation rules for image upload
     *
     * @param bool $required
     * @return array
     */
    public static function getImageValidationRules(bool $required = true): array
    {
        $maxSizeKB = self::getMaxFileSizeKB();
        $rules = [
            'image',
            'mimes:jpeg,png,jpg,webp',
            "max:{$maxSizeKB}", // Dynamic file size in kilobytes
            'dimensions:min_width=10,min_height=10,max_width=5000,max_height=5000'
        ];

        if ($required) {
            array_unshift($rules, 'required');
        }

        return $rules;
    }
}
