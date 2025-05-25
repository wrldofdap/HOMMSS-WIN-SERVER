<?php

return [

    /*
    |--------------------------------------------------------------------------
    | File Upload Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for file uploads in the
    | HOMMSS e-commerce application.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Maximum File Size
    |--------------------------------------------------------------------------
    |
    | The maximum file size allowed for uploads in bytes.
    | Default: 10MB (10485760 bytes)
    |
    | Common sizes:
    | - 2MB:  2097152
    | - 5MB:  5242880
    | - 10MB: 10485760
    | - 20MB: 20971520
    | - 50MB: 52428800
    |
    */

    'max_file_size' => env('UPLOAD_MAX_FILE_SIZE', 10485760), // 10MB

    /*
    |--------------------------------------------------------------------------
    | Allowed Image MIME Types
    |--------------------------------------------------------------------------
    |
    | The MIME types that are allowed for image uploads.
    |
    */

    'allowed_image_mimes' => [
        'image/jpeg',
        'image/png',
        'image/jpg',
        'image/webp'
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Image Extensions
    |--------------------------------------------------------------------------
    |
    | The file extensions that are allowed for image uploads.
    |
    */

    'allowed_image_extensions' => [
        'jpg',
        'jpeg',
        'png',
        'webp'
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Dimension Limits
    |--------------------------------------------------------------------------
    |
    | The minimum and maximum dimensions allowed for uploaded images.
    |
    */

    'image_dimensions' => [
        'min_width' => 10,
        'min_height' => 10,
        'max_width' => 5000,
        'max_height' => 5000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Directories
    |--------------------------------------------------------------------------
    |
    | The directories where uploaded files will be stored.
    |
    */

    'directories' => [
        'products' => 'uploads/products',
        'product_thumbnails' => 'uploads/products/thumbnails',
        'brands' => 'uploads/brands',
        'categories' => 'uploads/categories',
        'profile' => 'uploads/profile',
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Quality Settings
    |--------------------------------------------------------------------------
    |
    | Settings for image processing and optimization.
    |
    */

    'image_quality' => [
        'jpeg_quality' => 85,
        'png_compression' => 6,
        'webp_quality' => 85,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Security-related settings for file uploads.
    |
    */

    'security' => [
        'strip_exif' => true,
        'remove_animation' => true,
        'validate_content' => true,
        'secure_filenames' => true,
    ],

];
