<?php

return [
    /*
    |--------------------------------------------------------------------------
    | HTTPS Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the HTTPS configuration for the application.
    | These settings help ensure secure communication between the client
    | and server.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Force HTTPS
    |--------------------------------------------------------------------------
    |
    | When this option is set to true, the application will automatically
    | redirect all HTTP requests to HTTPS. This should be enabled in
    | production environments.
    |
    */
    'force_https' => env('FORCE_HTTPS', false),

    /*
    |--------------------------------------------------------------------------
    | HSTS (HTTP Strict Transport Security)
    |--------------------------------------------------------------------------
    |
    | HSTS tells browsers to only connect to your site over HTTPS.
    | This prevents protocol downgrade attacks and cookie hijacking.
    |
    */
    'hsts' => [
        'enabled' => env('HSTS_ENABLED', false),
        'max_age' => env('HSTS_MAX_AGE', 31536000), // 1 year
        'include_subdomains' => env('HSTS_INCLUDE_SUBDOMAINS', true),
        'preload' => env('HSTS_PRELOAD', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | SSL Certificate Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for SSL certificates. This is useful for development
    | and testing environments.
    |
    */
    'ssl' => [
        'verify_peer' => env('SSL_VERIFY_PEER', true),
        'verify_host' => env('SSL_VERIFY_HOST', true),
        'certificate_path' => env('SSL_CERTIFICATE_PATH'),
        'private_key_path' => env('SSL_PRIVATE_KEY_PATH'),
        'ca_bundle_path' => env('SSL_CA_BUNDLE_PATH'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Secure Cookie Settings
    |--------------------------------------------------------------------------
    |
    | These settings ensure that cookies are only sent over HTTPS
    | connections when HTTPS is enabled.
    |
    */
    'secure_cookies' => env('SECURE_COOKIES', false),

    /*
    |--------------------------------------------------------------------------
    | Mixed Content Policy
    |--------------------------------------------------------------------------
    |
    | This setting helps prevent mixed content warnings by ensuring
    | all resources are loaded over HTTPS.
    |
    */
    'upgrade_insecure_requests' => env('UPGRADE_INSECURE_REQUESTS', false),
];
