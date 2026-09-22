<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Payment Configuration
    |--------------------------------------------------------------------------
    |
    | Payment deadline and expiry settings for rental payments.
    |
    */

    'payment' => [
        'expiry_hours' => env('RENTAL_PAYMENT_EXPIRY_HOURS', 48),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rental Creation Rules
    |--------------------------------------------------------------------------
    |
    | Business rules for rental creation and booking.
    |
    */

    'booking' => [
        'min_days_advance' => env('RENTAL_MIN_DAYS_ADVANCE', 4),
        'max_days_advance' => env('RENTAL_MAX_DAYS_ADVANCE', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Document Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Total steps for document upload progress tracking.
    |
    */

    'document_upload' => [
        'total_steps' => 4,
    ],

];
