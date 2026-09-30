<?php

declare(strict_types=1);

return [
    'presets' => [
        'minimal' => [
            'users' => 10,
            'admins' => 3,
            'kosts' => 10,
            'rentals' => 10,
        ],
        'development' => [
            'users' => 50,
            'admins' => 10,
            'kosts' => 100,
            'rentals' => 100,
        ],
        'large' => [
            'users' => 50,
            'admins' => 30,
            'kosts' => 300,
            'rentals' => 500,
        ],
        'stress' => [
            'users' => 200,
            'admins' => 50,
            'kosts' => 1000,
            'rentals' => 2000,
        ],
    ],

    'active_preset' => env('SEED_PRESET', 'large'),

    'counts' => [
        'users' => env('SEED_USERS', 50),
        'admins' => env('SEED_ADMINS', 30),
        'kosts' => [
            'total' => env('SEED_KOSTS', 300),
            'active' => env('SEED_KOSTS_ACTIVE', 150),
            'draft' => env('SEED_KOSTS_DRAFT', 80),
            'pending_review' => env('SEED_KOSTS_PENDING', 40),
            'approved' => env('SEED_KOSTS_APPROVED', 20),
            'rejected' => env('SEED_KOSTS_REJECTED', 10),
        ],
        'rentals' => [
            'total' => env('SEED_RENTALS', 500),
            'payment_pending' => env('SEED_RENTALS_PAYMENT_PENDING', 50),
            'paid' => env('SEED_RENTALS_PAID', 75),
            'confirmed' => env('SEED_RENTALS_CONFIRMED', 75),
            'active' => env('SEED_RENTALS_ACTIVE', 150),
            'completed' => env('SEED_RENTALS_COMPLETED', 100),
            'cancelled' => env('SEED_RENTALS_CANCELLED', 50),
        ],
        'reviews_percentage' => env('SEED_REVIEWS_PERCENTAGE', 90),
        'categories' => 8,
    ],

    'cities' => [
        'Bandung' => 40,
        'Jakarta' => 30,
        'Yogyakarta' => 20,
        'Surabaya' => 10,
    ],

    'image_strategy' => env('SEED_IMAGE_STRATEGY', 'external_url'),

    'image_sources' => [
        'avatar' => 'https://loremflickr.com/400/400/portrait,face?random={id}',
        'kost' => 'https://loremflickr.com/800/600/house,building?random={id}',
        'room' => 'https://loremflickr.com/800/600/bedroom,room?random={id}',
        'document' => 'https://loremflickr.com/800/1200/document,id?random={id}',
        'qris' => 'https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=00020101021126670016COM.NOBUBANK.WWW01189360050300000870214{merchant_id}0303UMI51440014ID.CO.QRIS.WWW0215ID{random}0303UMI5204481253033605802ID5913{name}6009{city}61056{postal}62{crc}6304{checksum}',
    ],

    'preserve_test_data' => [
        'users' => [
            ['email' => 'system@sewakost.local', 'role' => 'superadmin', 'name' => 'System User'],
            ['email' => 'superadmin@sewakost.local', 'role' => 'superadmin', 'name' => 'Super Administrator'],
            ['email' => 'admin1@sewakost.local', 'role' => 'admin', 'name' => 'Admin Pertama'],
            ['email' => 'admin2@sewakost.local', 'role' => 'admin', 'name' => 'Admin Kedua'],
            ['email' => 'admin3@sewakost.local', 'role' => 'admin', 'name' => 'Admin Ketiga'],
        ],
        'kosts' => [
            ['name' => 'Kost Mawar Indah', 'slug' => 'kost-mawar-indah-bandung', 'status' => 'active', 'city' => 'Bandung'],
            ['name' => 'Kost Melati Residence', 'slug' => 'kost-melati-residence-jakarta', 'status' => 'active', 'city' => 'Jakarta'],
            ['name' => 'Kost Anggrek Premium', 'slug' => 'kost-anggrek-premium-yogyakarta', 'status' => 'active', 'city' => 'Yogyakarta'],
            ['name' => 'Kost Dahlia Budget', 'slug' => 'kost-dahlia-budget-surabaya', 'status' => 'active', 'city' => 'Surabaya'],
            ['name' => 'Kost Tulip Syariah', 'slug' => 'kost-tulip-syariah-bandung', 'status' => 'draft', 'city' => 'Bandung'],
        ],
    ],

    'performance' => [
        'chunk_size' => env('SEED_CHUNK_SIZE', 100),
        'show_progress' => env('SEED_SHOW_PROGRESS', true),
    ],
];
