<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Document Types for Kost Rental Requirements
    |--------------------------------------------------------------------------
    |
    | This configuration defines the available document types that can be
    | required from tenants when renting a kost. Extensible without migration.
    |
    */

    'document_types' => [
        'ktp' => 'KTP (Kartu Tanda Penduduk)',
        'selfie' => 'Foto Selfie dengan KTP',
        'student_card' => 'Kartu Pelajar/Mahasiswa',
        'family_card' => 'Kartu Keluarga',
        'reference_letter' => 'Surat Keterangan',
        'other' => 'Dokumen Lainnya',
    ],
];
