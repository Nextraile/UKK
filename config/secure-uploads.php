<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Secure Upload Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains security rules for file uploads across the application.
    | Each upload type defines MIME types, extensions, size limits, and
    | validation rules to prevent malicious uploads.
    |
    */

    'upload_types' => [
        'avatar' => [
            'mimes' => ['image/jpeg', 'image/png'],
            'extensions' => ['jpg', 'jpeg', 'png'],
            'min_size' => 1024, // 1KB - prevent empty files
            'max_size' => 2097152, // 2MB
            'validate_dimensions' => true,
            'min_width' => 100,
            'min_height' => 100,
        ],
        'qris' => [
            'mimes' => ['image/jpeg', 'image/png'],
            'extensions' => ['jpg', 'jpeg', 'png'],
            'min_size' => 1024,
            'max_size' => 1048576, // 1MB
            'validate_dimensions' => true,
            'min_width' => 200,
            'min_height' => 200,
        ],
        'kost_image' => [
            'mimes' => ['image/jpeg', 'image/png'],
            'extensions' => ['jpg', 'jpeg', 'png'],
            'min_size' => 1024,
            'max_size' => 5242880, // 5MB
            'validate_dimensions' => true,
            'min_width' => 300,
            'min_height' => 200,
        ],
        'room_type_image' => [
            'mimes' => ['image/jpeg', 'image/png'],
            'extensions' => ['jpg', 'jpeg', 'png'],
            'min_size' => 1024,
            'max_size' => 5242880, // 5MB
            'validate_dimensions' => true,
            'min_width' => 300,
            'min_height' => 200,
        ],
        'payment_proof' => [
            'mimes' => ['image/jpeg', 'image/png', 'application/pdf'],
            'extensions' => ['jpg', 'jpeg', 'png', 'pdf'],
            'min_size' => 1024,
            'max_size' => 5242880, // 5MB
            'validate_dimensions' => true, // Only for images, PDFs skip dimension check
            'min_width' => 300,
            'min_height' => 200,
        ],
        'rental_document' => [
            'mimes' => ['image/jpeg', 'image/png', 'application/pdf'],
            'extensions' => ['jpg', 'jpeg', 'png', 'pdf'],
            'min_size' => 1024,
            'max_size' => 10485760, // 10MB
            'validate_dimensions' => true, // Only for images, PDFs skip dimension check
            'min_width' => 300,
            'min_height' => 200,
        ],
    ],
];
