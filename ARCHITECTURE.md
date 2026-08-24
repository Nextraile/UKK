# ARCHITECTURE.md — Architecture & Design Document

> **Status dokumen ini:** Single Source of Truth untuk DESAIN TEKNIS.
> Dokumen ini MENGGANTIKAN fungsi Design Document Specification (DDS).
> Menjawab pertanyaan "bagaimana sistem ini dibangun dan mengapa keputusan teknis ini diambil".
> Setiap komponen/keputusan di sini WAJIB merujuk balik ke `FR-xxx`/`NFR-xxx` di `PRD.md` — jangan membangun sesuatu yang tidak berakar dari requirement.

| Field | Value |
|---|---|
| Nama Proyek | SewaKost — Web Marketplace Kost Management & Rental System |
| Versi Dokumen | `0.2.1` |
| Terakhir Diperbarui | `2026-08-24` |
| Baseline Arsitektur | **Laravel 13** — Modular Monolith, session-based auth, server-rendered web routes, containerized via **Docker (Laravel Sail untuk local/dev)** |

---

## 0. Cara Menggunakan Dokumen Ini

1. **COMP-xxx** = komponen/modul sistem (unit deployable atau unit logis yang jelas batasnya).
2. **ADR-xxx** = Architecture Decision Record — keputusan teknis penting beserta alasannya. Sekali ditulis, ADR tidak diedit isi keputusannya; jika keputusan berubah, buat ADR baru yang men-supersede ADR lama (jangan hapus riwayat).
3. **DM-xxx** = entitas data (Data Model).
4. **API-xxx** = kontrak endpoint/interface (kondisional — lihat §6.2, karena baseline proyek ini web routes bukan API-first).
5. Setiap agent yang akan mengimplementasikan sebuah komponen WAJIB membaca bagian komponen tsb + ADR terkait sebelum menulis kode, bukan menebak dari nama file.
6. Dokumen ini sudah mengasumsikan **baseline arsitektur** di atas (Laravel 13 monolith, auth session-based, web routes, Docker). Baseline ini sudah diputuskan sejak awal (lihat ADR-001, ADR-002, dan ADR-004 di §7) — jangan diubah diam-diam di tengah proyek; perubahan baseline wajib lewat ADR baru yang men-supersede.
7. **Sebelum mengimplementasikan apa pun yang menyentuh sebuah tool/library/framework** (termasuk Laravel sendiri), agent WAJIB mengecek dokumentasi resminya lebih dulu — lihat §3.1 dan `AGENTS.md` §Dokumentasi & Referensi Eksternal.

---

## 1. Gambaran Sistem (System Overview)

Aplikasi ini dibangun sebagai **modular monolith** di atas **Laravel 13**, menggunakan **web routes** (`routes/web.php`) dengan render server-side (Blade + Alpine.js untuk interaktivitas parsial) — bukan arsitektur API-first/SPA terpisah. Autentikasi memakai **session-based auth** bawaan Laravel (cookie session + CSRF protection), bukan token-based (Sanctum/Passport), kecuali kebutuhan eksplisit muncul (mis. klien mobile/SPA terpisah) — jika ini terjadi, itu adalah penyimpangan dari baseline dan wajib didokumentasikan sebagai ADR baru, bukan dicampur diam-diam. Environment **local/development dijalankan via Laravel Sail** (`./vendor/bin/sail`) — CLI resmi Laravel yang membungkus Docker Compose dengan konfigurasi siap pakai untuk PHP, database, cache, dst. Staging/production tetap berjalan di **container Docker**, tapi memakai image produksi terpisah yang dioptimasi (bukan sekadar menjalankan setup Sail apa adanya — lihat §9 dan ADR-004).

SewaKost adalah aplikasi marketplace kost multi-owner yang mendigitalisasi siklus penyewaan end-to-end: pencarian kost → booking → pembayaran QRIS statis dengan upload bukti → verifikasi dokumen administrasi → aktivasi rental → completion → review. Target pengguna: Penyewa (Tenant), Admin Kost (pengelola), dan Super Admin (verifikasi publikasi kost). Skala MVP: 100 kost, 500 rooms, 1000 rentals, 1 full-stack developer, timeline 12-18 minggu. Tidak ada penyimpangan dari baseline arsitektur — tetap monolith, web routes, session-based auth.

### 1.1 Diagram Konteks (Context Diagram)

```
[User Browser] ──HTTPS + Cookie Session──▶ [Laravel App Container
                                              (web routes, Blade views,
                                               Nginx/Octane + PHP-FPM)]
                                                        │
                                     ┌──────────────────┼──────────────────┬────────────────────┐
                                     ▼                  ▼                  ▼                     ▼
                              [DB Container]   [Cache/Session Store   [Queue Worker        [3rd-Party API
                               (mis. MySQL/     Container, mis. Redis]  Container, opsional]  eksternal]
                               PostgreSQL)]

Semua container di atas dijalankan/diorkestrasi via docker-compose — untuk local/dev melalui Laravel Sail (`./vendor/bin/sail up -d`), lihat §9.
```

---

## 2. Pola & Gaya Arsitektur

| Aspek | Pilihan | Alasan Singkat (detail di ADR terkait) |
|---|---|---|
| Gaya arsitektur | Modular Monolith (Laravel 13) — satu codebase/deployable, dipecah **logis** per-domain mengikuti `COMP-xxx` di §4, bukan microservices | Lihat ADR-001 |
| Pola komunikasi antar-komponen | Server-rendered web routes (`routes/web.php`) + session-based state; panggilan antar-modul terjadi in-process (Service/Action class), bukan HTTP internal. Job berat lewat Laravel Queue (async) | Lihat ADR-002 |
| Pola penyimpanan data | Eloquent ORM + migration-based schema, satu database relasional utama (pilihan RDBMS di §3) | Lihat ADR-003 |
| Containerization | Docker — **Laravel Sail** untuk local/dev, image produksi terpisah untuk staging/prod | Lihat ADR-004 |

> Jika sebagian requirement benar-benar butuh API murni (mis. dikonsumsi mobile app terpisah), itu **bukan** otomatis Won't-do — tapi wajib didaftarkan sebagai `FR-xxx` eksplisit di `PRD.md` dan didesain via ADR baru sebagai penyimpangan dari baseline, bukan dicampur diam-diam ke web routes yang sama.

---

## 3. Tumpukan Teknologi (Tech Stack)

| Layer | Teknologi | Versi | Alasan |
|---|---|---|---|
| Bahasa/Runtime | PHP | `8.5` | Dibutuhkan Laravel 13 (8.3+). Lihat ADR-020 untuk rationale PHP 8.5 |
| Framework Backend | Laravel | `13.x` | Baseline arsitektur proyek ini — lihat §1 |
| Rendering/Frontend | Blade + Alpine.js | Blade: Laravel 13, Alpine.js: `3.x` | Server-rendered, selaras dengan pola web routes + session. Alpine.js untuk interaktivitas minimal (dropdown, modal, form validation feedback) |
| Auth | Laravel Breeze (session-based) | Laravel 13 | Baseline session-based auth, sudah include login/register/email verification scaffold. Customized untuk OTP verification (6-digit code) |
| Database | MySQL | `8.0` | Relational integrity, FK constraints, transaction support, widely supported |
| Cache / Session Store | Redis | `7.x` | Dipakai untuk cache, session driver (wajib untuk multi-instance scaling), dan queue driver |
| Queue | Redis queue driver | — | Untuk job async (email sending, rental lifecycle monitoring) |
| Containerization (local/dev) | Laravel Sail | `1.x` (paket `laravel/sail`) | Baseline — CLI resmi Laravel, siap pakai untuk dev, lihat §9 |
| Containerization (staging/prod) | Docker (image produksi custom) | Docker 24+ | Sail untuk dev, image terpisah untuk production — lihat ADR-004 |
| Web Server (dalam container) | Nginx + PHP-FPM | Nginx 1.25+, PHP-FPM 8.3+ | Standard production setup Laravel |
| Maps | Leaflet.js | `1.9.x` | Display lokasi kost (Leaflet + OpenStreetMap tile) |
| CI/CD | GitHub Actions (opsional) | — | Untuk automated testing (jika dipakai) |

### 3.1 Rujukan Dokumentasi Resmi (WAJIB dicek sebelum implementasi)

> **Aturan keras:** untuk setiap tool/library/framework yang dipakai proyek ini — terutama yang versinya spesifik seperti Laravel 13 — agent DILARANG mengandalkan hafalan/training data begitu saja, karena API dan konvensi bisa berubah antar versi major. Sebelum menulis kode yang menyentuh tool tsb, agent WAJIB mencari & membaca dokumentasi resmi versi yang sesuai (web search/fetch), lalu boleh meringkas hasilnya di catatan implementasi `COMP-xxx` terkait. Detail aturan proses ada di `AGENTS.md` §Dokumentasi & Referensi Eksternal.

| Tool/Library | Versi Dipakai | Dokumentasi Resmi | Catatan |
|---|---|---|---|
| Laravel | 13.22.0 | https://laravel.com/docs/13.x | Cek upgrade guide/changelog resmi jika ragu API berubah dari versi sebelumnya |
| Laravel Sail | 1.64.0 | https://laravel.com/docs/13.x/sail | Cek juga bagian "Sail vs production" di docs — Sail tidak otomatis dipakai untuk deploy production |
| Laravel Breeze | 2.4.2 | https://laravel.com/docs/13.x/starter-kits#laravel-breeze | Session-based auth scaffold, customized untuk OTP verification |
| PHP | 8.5 | https://www.php.net/docs.php | Lihat ADR-020 untuk rationale penggunaan PHP 8.5 |
| MySQL | 8.0 | https://dev.mysql.com/doc/refman/8.0/en/ | Pinned di compose.yaml ke `mysql:8.0` |
| Redis | 7.x | https://redis.io/docs/ | Pinned di compose.yaml ke `redis:7-alpine` |
| Docker / Docker Compose | 29.5.1 / 5.1.4 | https://docs.docker.com/ | — |
| Leaflet.js | 1.9.x | https://leafletjs.com/reference.html | Display map dengan OpenStreetMap tiles |
| Alpine.js | 3.14.x | https://alpinejs.dev/start-here | Minimal interactivity di Blade views |
| Vite | 8.2.1 | https://vite.dev/ | Frontend build tool untuk asset compilation |
| Tailwind CSS | 4.0.0 | https://tailwindcss.com/docs | Utility-first CSS framework |
| PHPStan / Larastan | 2.2.8 / 3.10.0 | https://phpstan.org/user-guide/getting-started <br> https://github.com/larastan/larastan | Static analysis, level 5 (lihat phpstan.neon) |
| PHPUnit | 12.5.12 | https://docs.phpunit.de/en/12.5/ | Testing framework (lihat ADR-021 untuk rationale PHPUnit vs Pest) |
| Mailpit | latest | https://mailpit.axllent.org/ | Email testing tool untuk development (SMTP server + web UI) |

> **Wajib diperbarui:** setiap kali dependency baru ditambahkan (lihat `AGENTS.md` §Guardrails soal ADR untuk dependency baru), baris baru WAJIB ditambahkan ke tabel ini pada commit yang sama dengan ADR-nya.

---

## 4. Komponen Sistem (COMP-xxx)

> Setiap komponen wajib self-contained: agent yang membaca satu blok ini harus paham tanggung jawab komponen tanpa perlu buka kode dulu.
>
> Karena baseline monolith Laravel, `COMP-xxx` di sini adalah pemisahan **logis** (folder/namespace), bukan service terpisah. Rekomendasi struktur: `app/Domain/<NamaKomponen>/` berisi Model + Service/Action class-nya, dengan Controller di `app/Http/Controllers/<NamaKomponen>/` dan view di `resources/views/<nama-komponen>/` — lihat §11.

### COMP-001: Identity & Account Management
- **Tanggung jawab:** 
  - User authentication (login/logout)
  - Self-registration Tenant dengan OTP email verification (6-digit code, expiry 15 menit)
  - Verifikasi email bersifat **on-demand** (ADR-023): tidak ada OTP saat registrasi (FR-003); OTP dikirim saat user membuka halaman verifikasi atau diminta fitur yang membutuhkan email terverifikasi via popup (FR-004, FR-006). User unverified juga dapat memulai verifikasi dari tombol 'Verifikasi Email' di halaman profil (menu profile).
  - Email change dengan re-verification OTP
  - Profile management (first name, last name, phone, avatar, email)
  - Soft delete account
  - RBAC (role: user/admin/superadmin)
  - TIDAK menangani: business-specific authorization (mis. "apakah Admin dapat edit kost ini") — itu tanggung jawab komponen bisnis masing-masing via Policy/Gate
- **Memenuhi requirement:** FR-001—FR-013, FR-130, NFR-004—NFR-010 (Security)
- **Lokasi di repo:** 
  - `app/Domain/Identity/` (Models: User, OtpVerification jika perlu)
  - `app/Http/Controllers/Auth/` (Laravel Breeze controllers, customized untuk OTP)
  - `app/Http/Controllers/ProfileController.php`
  - `resources/views/auth/`, `resources/views/profile/`
- **Bergantung pada:** —
- **Digunakan oleh:** Semua komponen lain (autentikasi & otorisasi)
- **Interface publik:** Web routes di §6.1 (login, register, logout, profile, email verification)
- **Catatan implementasi:** 
  - Laravel Breeze sebagai starting point, customisasi untuk OTP verification (bukan link). OTP disimpan di cache Redis (key: `otp:{user_id}`, expiry: 15 menit) atau di tabel `otp_verifications` jika perlu audit trail.
  - Email verification tidak mutlak wajib untuk login, hanya saat akses fitur tertentu (FR-006). Middleware `verified` hanya dipasang di route yang butuh email verified (misal: create rental).
  - Email change: email baru disimpan di `users.email`, tapi `email_verified_at` di-null dan OTP baru dikirim. Email lama tetap di session sampai email baru verified.
  - Password reset via OTP (FR-130, ADR-022): alur 3 langkah (email → OTP 6 digit → password baru), reuse OtpService dengan purpose `password-reset`, menggantikan token link Breeze. `password_reset_tokens` tidak dipakai lagi (tidak di-drop).
  - Policy untuk resource ownership (FR-008): setiap komponen bisnis (Kost, Rental, dll.) implement Policy yang check ownership.
  - Soft delete: `deleted_at` di-set, user logout paksa (invalidate session), dan tidak dapat login lagi. Middleware `active` untuk check `deleted_at IS NULL`.

### COMP-002: Kost Publication Management
- **Tanggung jawab:**
  - Admin create kost sebagai Draft
  - Admin submit untuk review (Draft → Pending Review)
  - Super Admin approve/reject submission (dengan alasan jika reject)
  - Admin revise rejected kost (Rejected → Draft)
  - Admin publish approved kost (Approved → Active)
  - Status lifecycle enforcement: Draft → Pending Review → Approved/Rejected → Active (5 state)
  - TIDAK menangai: konfigurasi detail kost (facilities, rules, QRIS, dll.) — itu COMP-003
- **Memenuhi requirement:** FR-014—FR-023, US-004—US-008
- **Lokasi di repo:**
  - `app/Domain/Kost/Models/Kost.php`
  - `app/Domain/Kost/Actions/` (SubmitKostForReview, ApproveKost, RejectKost, PublishKost)
  - `app/Http/Controllers/Admin/KostController.php`
  - `app/Http/Controllers/SuperAdmin/KostSubmissionController.php`
  - `resources/views/admin/kosts/`, `resources/views/superadmin/submissions/`
- **Bergantung pada:** COMP-001 (Auth), COMP-003 (Kost Configuration untuk data wajib validation sebelum submit)
- **Digunakan oleh:** COMP-005 (Marketplace hanya tampilkan Active kost)
- **Interface publik:** Web routes §6.1 (admin kost CRUD, superadmin submission review)
- **Catatan implementasi:**
  - State transition tidak via generic update, gunakan Action class per transition dengan state validation.
  - Validation data wajib sebelum submit (FR-017): nama, alamat, kategori, minimal 1 room type (cek via relasi). Action `SubmitKostForReview` wajib check completeness sebelum transition ke `Pending Review`.
  - Approval/Rejection via Action class. Saat reject, `rejected_reason` wajib diisi (validation). Saat approve, `approved_by` dan `approved_at` di-set.
  - Publish kost hanya dapat dilakukan jika status = `Approved`. Saat publish, `published_at` di-set dan status → `Active`.
  - Soft delete untuk kosts (preserve historical data). Kost yang sudah pernah Active tidak boleh di-hard delete.
  - Status enum di database: `draft`, `pending_review`, `approved`, `active`, `rejected`.

### COMP-003: Kost Configuration Management
- **Tanggung jawab:**
  - Admin kelola informasi kost (nama, slug, deskripsi, contact_number)
  - Admin kelola alamat kost (full address, district, city, province, postal_code, country, lat/long)
  - Admin upload & manage gambar kost (thumbnail + galeri)
  - Admin assign kategori kost (dari master category yang dikelola Super Admin)
  - Admin input facilities & rules sebagai JSON array of strings (ADR-013)
  - Admin upload QRIS static image + bank account info (bank_name, account_number, account_holder_name)
  - Admin define document requirements per kost (jenis dokumen, wajib/opsional, alasan)
  - Super Admin CRUD master kategori
  - TIDAK menangani: room type/pricing — itu COMP-004
- **Memenuhi requirement:** FR-024—FR-035, FR-117—FR-120, US-004, US-009, US-021
- **Lokasi di repo:**
  - `app/Domain/Kost/Models/` (Kost, Address, KostImage, Category, KostDocumentRequirement)
  - `app/Domain/Kost/Actions/` (UpdateKostConfiguration, UploadKostImages, ConfigureDocumentRequirements)
  - `app/Http/Controllers/Admin/KostConfigurationController.php`
  - `app/Http/Controllers/SuperAdmin/CategoryController.php`
  - `resources/views/admin/kosts/config/`, `resources/views/superadmin/categories/`
- **Bergantung pada:** COMP-001 (Auth), COMP-002 (Kost entity)
- **Digunakan oleh:** COMP-005 (Marketplace display), COMP-006 (Rental document requirements)
- **Interface publik:** Web routes §6.1
- **Catatan implementasi:**
  - **ADR-013:** Facility/Rule disimpan sebagai JSON array of strings di `kosts.facilities`, `kosts.rules`. Form input sebagai textarea multi-line atau dynamic list input di UI, disimpan sebagai JSON. Cast Eloquent attribute: `protected $casts = ['facilities' => 'array', 'rules' => 'array'];`.
  - Kategori master hanya CRUD oleh Super Admin (FR-117—FR-120). Admin hanya dapat assign kategori via junction table `category_kost` (many-to-many).
  - QRIS image + bank info untuk COMP-007 (Payment). Validasi upload: image (jpeg/png), max 2MB untuk QRIS.
  - Document requirements: tabel `kost_document_requirements` dengan kolom `document_type` (enum: ktp, selfie, student_card, family_card, reference_letter, other), `is_required` (boolean), `reason` (text). Admin dapat tambah/edit/hapus per kost.
  - File upload validation: image (jpeg/png/jpg), max 2MB untuk QRIS, max 5MB untuk gambar kost. Generated server-side filename untuk security.
  - Alamat: 1:1 relationship dengan Kost. Lat/long untuk Leaflet map display (COMP-005).

### COMP-004: Room Inventory Management
- **Tanggung jawab:**
  - Admin kelola Room Type (nama, slug, deskripsi, room_size, max_occupants, security_deposit, gambar)
  - Admin input facilities & rules per Room Type sebagai JSON array of strings (ADR-013)
  - Admin kelola Price Scheme per Room Type (harga, duration_value, duration_unit, is_active) — **1:N relationship** dengan Room Type (ADR-013)
  - Admin kelola Room (unit fisik): room code, status (available/unavailable)
  - Room status enforcement: Admin hanya set `unavailable` jika room benar-benar kosong (tidak ada rental pending/paid/confirmed/active)
  - Display room availability per Room Type: Available/Reserved/Occupied/Unavailable count (calculated real-time dari rentals — ADR-017, ADR-018)
  - TIDAK menangani: rental creation — itu COMP-006
- **Memenuhi requirement:** FR-036—FR-047, US-005
- **Lokasi di repo:**
  - `app/Domain/RoomInventory/Models/` (RoomType, RoomTypeImage, PriceScheme, Room)
  - `app/Domain/RoomInventory/Actions/` (CreateRoomType, CreatePriceScheme, CreateRoom, SetRoomUnavailable)
  - `app/Http/Controllers/Admin/RoomTypeController.php`
  - `app/Http/Controllers/Admin/PriceSchemeController.php`
  - `app/Http/Controllers/Admin/RoomController.php`
  - `resources/views/admin/room-types/`, `resources/views/admin/rooms/`
- **Bergantung pada:** COMP-001, COMP-002 (Kost entity)
- **Digunakan oleh:** COMP-005 (Marketplace display pricing), COMP-006 (Rental room selection)
- **Interface publik:** Web routes §6.1
- **Catatan implementasi:**
  - **Price Scheme 1:N dengan Room Type** (setiap price scheme belongs_to satu room_type). Hapus M:N junction table. Migration: `price_schemes.room_type_id` foreign key ke `room_types.id`.
  - **Room Type facilities/rules:** JSON array di `room_types.facilities`, `room_types.rules` (sama pattern dengan kost). Cast attribute: `['facilities' => 'array', 'rules' => 'array']`.
  - **Room status enum: `available`, `unavailable` only** (ADR-017). Admin dapat toggle status. Validation sebelum set `unavailable`: query `SELECT COUNT(*) FROM rentals WHERE room_id = ? AND status IN ('pending','paid','confirmed','active') = 0`. Jika > 0, reject dengan error message.
  - **Room availability calculation (ADR-017, ADR-018):**
    ```php
    // Per Room Type, calculate occupancy berdasarkan max_occupants
    $roomType->max_occupants; // kapasitas per room
    
    // Per room: hitung used_slots dari rentals
    $room->reserved_count = rentals WHERE room_id = X AND status IN ('pending','paid','confirmed') AND start_date > NOW()
    $room->occupied_count = rentals WHERE room_id = X AND status = 'active'
    $room->used_slots = reserved_count + occupied_count
    $room->free_slots = max_occupants - used_slots
    
    // Room available for booking if: status = 'available' AND free_slots > 0
    ```
  - **Display okupasi:**
    - **Marketplace (Tenant/Public):** Hanya tampilkan total available slots per room type (sum dari semua room free_slots). Tidak tampilkan reserved/occupied/unavailable detail.
    - **Admin dashboard:** Tampilkan per-room detail: Reserved count, Occupied count, Available slots, status (available/unavailable).
  - Rental creation (COMP-006) menggunakan **transactional room locking** (`SELECT ... FOR UPDATE`) untuk prevent double booking (ADR-010, ADR-011).
  - Soft delete untuk room_types, price_schemes, rooms (preserve historical data).

### COMP-005: Marketplace
- **Tanggung jawab:**
  - Public browsing kost Active (tanpa login)
  - Search kost by name/location (city/district/address)
  - Filter kost by price range, category, rating
  - View detail kost: info, alamat, gambar, kategori, facilities/rules (display JSON), document requirements, room types, price schemes, reviews, map (Leaflet + OpenStreetMap)
  - Pagination kost list
  - TIDAK menangani: rental creation — itu COMP-006
- **Memenuhi requirement:** FR-048—FR-060, US-014—US-016
- **Lokasi di repo:**
  - `app/Http/Controllers/MarketplaceController.php`
  - `app/Http/Controllers/KostDetailController.php`
  - `resources/views/marketplace/`, `resources/views/kosts/`
- **Bergantung pada:** COMP-002 (hanya tampilkan Active kost), COMP-003 (kost config), COMP-004 (room types & pricing), COMP-008 (reviews untuk display)
- **Digunakan oleh:** COMP-006 (Tenant buka marketplace → pilih kost → create rental)
- **Interface publik:** Web routes §6.1 (public routes, tidak butuh auth untuk browse)
- **Catatan implementasi:**
  - Hanya tampilkan kost berstatus `active` (FR-022). Query: `WHERE status = 'active' AND deleted_at IS NULL`.
  - Map display menggunakan Leaflet.js + OSM tiles (ADR-007). Embed map di detail kost dengan marker di lat/long.
  - Facilities/rules dari JSON ditampilkan sebagai list di UI (bukan filter dimension, hanya display content). Parse JSON dan loop di Blade.
  - Average rating dihitung dari reviews (COMP-008): `AVG(kost_rating)` untuk kost rating.
  - Search: `WHERE name LIKE '%keyword%' OR city LIKE '%keyword%' OR district LIKE '%keyword%' OR full_address LIKE '%keyword%'`.
  - Filter price: join `room_types` → `price_schemes`, filter `WHERE price BETWEEN min AND max`.
  - Filter category: join `category_kost`, filter `WHERE category_id IN (...)`.
  - Filter rating: `HAVING AVG(kost_rating) >= min_rating`.
  - Pagination: Laravel paginate (default 20 items per page).
  - **Room availability display di Marketplace:** hanya tampilkan total available slots per room type, tidak tampilkan detail reserved/occupied (ADR-017). Display: "X kamar tersedia" (sum free_slots untuk room type).

### COMP-006: Rental Lifecycle Management
- **Tanggung jawab:**
  - Tenant create rental (pilih room type → price scheme → room → start date → duration → calculate total)
  - Rental status lifecycle: Pending → Paid → Confirmed → Active → Completed (atau Cancelled)
  - Tenant manual cancel rental (dari status apapun termasuk Active — perubahan dari rencana awal)
  - Tenant upload dokumen administrasi (sesuai document requirements kost)
  - Admin verify dokumen per-dokumen (approve/reject dengan alasan)
  - Auto-cancel rental jika payment deadline terlewati (48 jam)
  - Auto-cancel rental jika dokumen tidak lengkap sebelum start date
  - Auto-activate rental saat start date tercapai (jika sudah Confirmed)
  - Auto-complete rental saat end date tercapai
  - Record rental status history untuk audit trail
  - TIDAK menangani: payment verification — itu COMP-007
- **Memenuhi requirement:** FR-061—FR-068, FR-083—FR-104, FR-121—FR-127, US-010, US-013—US-014, US-017, US-021—US-022
- **Lokasi di repo:**
  - `app/Domain/Rental/Models/` (Rental, RentalDocument, RentalStatusHistory)
  - `app/Domain/Rental/Actions/` (CreateRental, UploadDocument, VerifyDocument, ConfirmRental, ActivateRental, CompleteRental, CancelRental)
  - `app/Domain/Rental/Jobs/` (MonitorRentalLifecycle — scheduled job)
  - `app/Http/Controllers/Tenant/RentalController.php`
  - `app/Http/Controllers/Admin/RentalManagementController.php`
  - `resources/views/tenant/rentals/`, `resources/views/admin/rentals/`
- **Bergantung pada:** COMP-001, COMP-003 (document requirements), COMP-004 (room availability), COMP-007 (payment verification)
- **Digunakan oleh:** COMP-008 (review eligibility check)
- **Interface publik:** Web routes §6.1
- **Catatan implementasi:**
  - **Rental creation transactional** (ADR-010, ADR-011): create rental + increment room used_slots dalam satu DB transaction. Lock room dengan `SELECT ... FOR UPDATE` untuk prevent double booking berdasarkan `max_occupants`.
  - Rental stores **snapshot** price + duration (tidak dynamic reference ke price scheme). Kolom: `room_price`, `security_deposit`, `duration_value`, `duration_unit`, `grand_total`.
  - **Contract start date di-set Tenant (ADR-016): min = today+4 hari, max = today+30 hari**. Validation di Form Request.
  - **Payment deadline 48 jam** (FR-121). Payment record created saat rental created dengan `expired_at = created_at + 48 hours`.
  - Document requirements per kost (dari COMP-003 `kost_document_requirements`). Tenant upload dokumen setelah payment success. Document verification dengan `rejection_reason` (FR-089—FR-090).
  - **Rental status transitions:**
    ```
    pending ──→ paid ──→ confirmed ──→ active ──→ completed
      │         │          │            │
      └─────────┴──────────┴────────────┴──→ cancelled
    ```
  - **Cancel dari Active diperbolehkan** (perubahan dari rencana). Tenant dapat cancel kapan saja (tidak ada restriction by status). No refund di sistem (FR-125).
  - Scheduled job `MonitorRentalLifecycle` run setiap jam:
    1. Check payment deadline: `WHERE status = 'pending' AND payments.expired_at < NOW()` → Cancel
    2. Check document deadline: `WHERE status = 'paid' AND start_date < NOW()` → Cancel (cancelled_reason = "Dokumen tidak dilengkapi")
    3. Auto-activate: `WHERE status = 'confirmed' AND start_date <= NOW()` → Active
    4. Auto-complete: `WHERE status = 'active' AND end_date <= NOW()` → Completed
  - State transition via Action class (bukan generic update). Action class wajib:
    - Validate current state
    - Validate preconditions
    - Update state
    - Append `rental_status_histories` record
    - Execute side effects (email notification, room status update, dll.)
  - **Room occupancy calculation** (ADR-017, ADR-018): 1 rental = 1 person. Room `free_slots` berkurang saat rental created (status pending/paid/confirmed/active). Room `free_slots` bertambah saat rental completed/cancelled.
  - Rental status history tercatat di `rental_status_histories` (FR-100): kolom `status`, `changed_by`, `internal_notes`, `created_at`.

### COMP-007: Payment Management
- **Tanggung jawab:**
  - Display QRIS static image + bank info ke Tenant saat payment page
  - Tenant upload bukti pembayaran (proof of payment)
  - Admin verify bukti pembayaran manual (approve/reject dengan rejection_reason)
  - Payment status: pending → success/failed
  - Payment deadline monitoring (48 jam dari rental created_at)
  - Notify Tenant saat payment approved/rejected (email)
  - TIDAK ada integrasi Midtrans (ADR-014 supersede ADR-006)
- **Memenuhi requirement:** FR-069—FR-082, FR-121, US-011—US-012
- **Lokasi di repo:**
  - `app/Domain/Payment/Models/Payment.php`
  - `app/Domain/Payment/Actions/` (UploadProofOfPayment, VerifyPayment, RejectPayment)
  - `app/Http/Controllers/Tenant/PaymentController.php`
  - `app/Http/Controllers/Admin/PaymentVerificationController.php`
  - `resources/views/tenant/payments/`, `resources/views/admin/payments/`
- **Bergantung pada:** COMP-001, COMP-006 (rental entity)
- **Digunakan oleh:** COMP-006 (rental lifecycle transition Pending → Paid setelah payment success)
- **Interface publik:** Web routes §6.1
- **Catatan implementasi:**
  - **ADR-014:** Payment menggunakan QRIS statis + upload bukti + verifikasi manual Admin (bukan Midtrans payment gateway). Hapus kolom `transaction_id`, `gateway`, `method`, `payment_url`. Hapus tabel `payment_logs`.
  - **Payment table schema:** `rental_id` (FK, UNIQUE), `qris_image_path`, `amount`, `proof_of_payment_path`, `status`, `verified_by` (FK → users.id), `verified_at`, `rejection_reason`, `expired_at`, `paid_at`, `created_at`, `updated_at`.
  - 1 Rental : 1 Payment (1:1 relationship). Payment record created saat rental created, dengan `expired_at = created_at + 48 jam`.
  - Admin wajib input `rejection_reason` saat reject (FR-073). Validation required di Form Request.
  - Tenant dapat upload ulang proof setelah rejected (FR-075). Upload baru → `proof_of_payment_path` di-replace, `rejection_reason` di-clear, `status` kembali `pending`.
  - File upload validation: image (jpeg/png/jpg), max 5MB.
  - Email notification saat payment verified/rejected (async via queue, COMP-001 email service).
  - Payment verification via Action class. Saat approve: `status = 'success'`, `verified_by`, `verified_at`, `paid_at` di-set. Trigger rental status transition (Pending → Paid) via COMP-006.

### COMP-008: Review Management
- **Tanggung jawab:**
  - Tenant submit review setelah rental Completed
  - 1 review per rental (gabung kost + room review dalam 1 tabel — ADR-015)
  - Review fields: kost_rating (1-5, optional), kost_comment (optional), room_rating (1-5, optional), room_comment (optional), images (JSON array)
  - Upload review images (disimpan sebagai JSON array, bukan tabel terpisah)
  - Display reviews di detail kost marketplace
  - Calculate average ratings (kost & room)
  - TIDAK menangani: review moderation/flagging — out of scope MVP
- **Memenuhi requirement:** FR-105—FR-110, US-018
- **Lokasi di repo:**
  - `app/Domain/Review/Models/Review.php`
  - `app/Domain/Review/Actions/` (SubmitReview, UploadReviewImages)
  - `app/Http/Controllers/Tenant/ReviewController.php`
  - `resources/views/tenant/reviews/`
- **Bergantung pada:** COMP-001, COMP-006 (rental entity, eligibility check)
- **Digunakan oleh:** COMP-005 (marketplace display reviews & ratings)
- **Interface publik:** Web routes §6.1
- **Catatan implementasi:**
  - **ADR-015:** Gabung `kost_reviews` + `room_reviews` jadi 1 tabel `reviews`. Review images disimpan sebagai JSON array (bukan polymorphic table). Schema: `rental_id` (FK, UNIQUE), `kost_rating`, `kost_comment`, `room_rating`, `room_comment`, `images` (JSON), `created_at`, `updated_at`.
  - Review eligibility (FR-105): rental status = `Completed`, belum ada review untuk rental ini. Check di Action class sebelum create.
  - Minimal 1 rating harus diisi (kost_rating atau room_rating) — FR-108. Validation di Form Request.
  - Review images uploaded dan path-nya disimpan sebagai JSON array di `reviews.images`. Cast attribute: `['images' => 'array']`. Max 5 images per review.
  - File upload validation: image (jpeg/png/jpg), max 2MB per image.
  - Display reviews di marketplace (COMP-005): join `reviews` dengan `rentals` dan `users` untuk reviewer info. Order by `created_at DESC`.
  - Calculate average ratings:
    ```sql
    SELECT 
      AVG(kost_rating) as avg_kost_rating,
      AVG(room_rating) as avg_room_rating,
      COUNT(*) as review_count
    FROM reviews
    JOIN rentals ON reviews.rental_id = rentals.id
    WHERE rentals.kost_id = ?
    ```

### COMP-009: Administration
- **Tanggung jawab:**
  - Super Admin create akun Admin (setelah verifikasi manual di luar sistem)
  - Super Admin manage akun Admin (update info, soft delete)
  - Super Admin CRUD master kategori (FR-117—FR-120)
  - Admin tidak dapat CRUD kategori (hanya assign ke kost miliknya)
  - TIDAK menangani: Super Admin account creation via UI — Super Admin pertama dibuat via seeder/artisan command
- **Memenuhi requirement:** FR-111—FR-120, US-019—US-021
- **Lokasi di repo:**
  - `app/Http/Controllers/SuperAdmin/AdminManagementController.php`
  - `app/Http/Controllers/SuperAdmin/CategoryController.php`
  - `resources/views/superadmin/admins/`, `resources/views/superadmin/categories/`
- **Bergantung pada:** COMP-001 (User model)
- **Digunakan oleh:** COMP-003 (kategori untuk kost)
- **Interface publik:** Web routes §6.1 (superadmin routes)
- **Catatan implementasi:**
  - Super Admin tidak dapat create/promote ke Super Admin via UI (FR-111 catatan). Super Admin pertama dibuat via `php artisan db:seed --class=SuperAdminSeeder` atau artisan command `php artisan user:make-superadmin {email}`.
  - Email notification ke Admin baru saat account created (async via queue). Email berisi: username (email), password sementara (generated), link untuk set password baru.
  - Soft delete Admin tidak menghilangkan data historis kost/rental (FR-116). `deleted_at` di-set, Admin tidak dapat login. Data kost/rental yang dibuat Admin tetap valid (FK `ON DELETE SET NULL` atau `RESTRICT`).
  - Category CRUD hanya accessible oleh Super Admin. Middleware `role:superadmin` di route. Admin mencoba akses → 403 Forbidden.
  - Category soft delete: `deleted_at` di-set, kategori tidak muncul di dropdown Admin. Kost yang sudah pakai kategori ini tetap valid (junction table `category_kost` tidak di-cascade delete).

---

## 5. Model Data (DM-xxx)

> Konvensi (Eloquent/Laravel): nama tabel snake_case plural, primary key `id` bigIncrement (default Eloquent) kecuali ada alasan kuat (dokumentasikan sebagai ADR). Setiap `DM-xxx` WAJIB punya migration — jangan pernah ubah schema manual langsung di database production.

### DM-001: users

| Field | Tipe | Wajib? | Deskripsi | Constraint |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED AUTO_INCREMENT | Ya | Primary key | Unique |
| `first_name` | VARCHAR(100) | Ya | Nama depan user | — |
| `last_name` | VARCHAR(100) | Tidak | Nama belakang user | — |
| `email` | VARCHAR(255) | Ya | Email user (untuk login & notification) | Unique |
| `email_verified_at` | TIMESTAMP | Tidak | Timestamp saat email verified via OTP | — |
| `password` | VARCHAR(255) | Ya | Password hash (bcrypt) | — |
| `phone` | VARCHAR(20) | Tidak | Nomor telepon user | — |
| `phone_verified_at` | TIMESTAMP | Tidak | (Untuk future) Phone verification | — |
| `role` | ENUM('user','admin','superadmin') | Ya | User role untuk RBAC | Default: 'user' |
| `avatar_path` | VARCHAR(255) | Tidak | Path ke file avatar image | — |
| `remember_token` | VARCHAR(100) | Tidak | Laravel remember token | — |
| `created_at` | TIMESTAMP | Ya | — | — |
| `updated_at` | TIMESTAMP | Ya | — | — |
| `deleted_at` | TIMESTAMP | Tidak | Soft delete timestamp | — |

**Relasi:**
- DM-001 memiliki banyak DM-002 (kosts) — one-to-many via `kosts.user_id`
- DM-001 memiliki banyak DM-012 (rentals) — one-to-many via `rentals.user_id`

**Index:**
- Unique: `email`
- Index: `role`, `deleted_at` (untuk query filtering)

**Catatan:**
- Soft delete: user yang di-delete tidak bisa login, tapi data historis (kost, rental, approval) tetap valid.
- OTP verification: OTP disimpan di Redis cache (key: `otp:{user_id}`, value: `{code}`, TTL: 15 menit) atau tabel terpisah `otp_verifications` jika perlu audit trail.

---

### DM-002: kosts

| Field | Tipe | Wajib? | Deskripsi | Constraint |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED AUTO_INCREMENT | Ya | Primary key | Unique |
| `user_id` | BIGINT UNSIGNED | Ya | Owner kost (Admin yang create) | FK → users.id |
| `slug` | VARCHAR(150) | Ya | URL-friendly identifier | Unique |
| `name` | VARCHAR(150) | Ya | Nama kost | — |
| `description` | TEXT | Tidak | Deskripsi kost | — |
| `contact_number` | VARCHAR(20) | Ya | Nomor kontak kost | — |
| `facilities` | JSON | Tidak | Array of strings (facilities kost level) | — |
| `rules` | JSON | Tidak | Array of strings (rules kost level) | — |
| `qris_image_path` | VARCHAR(255) | Tidak | Path ke QRIS static image | — |
| `bank_name` | VARCHAR(100) | Tidak | Nama bank tujuan transfer | — |
| `account_number` | VARCHAR(50) | Tidak | Nomor rekening tujuan | — |
| `account_holder_name` | VARCHAR(150) | Tidak | Nama pemilik rekening | — |
| `status` | ENUM('draft','pending_review','approved','active','rejected') | Ya | Status publikasi kost | Default: 'draft' |
| `published_at` | TIMESTAMP | Tidak | Timestamp saat kost published (status Active) | — |
| `approved_at` | TIMESTAMP | Tidak | Timestamp saat kost approved | — |
| `approved_by` | BIGINT UNSIGNED | Tidak | Super Admin yang approve | FK → users.id |
| `rejected_reason` | TEXT | Tidak | Alasan reject dari Super Admin | — |
| `created_at` | TIMESTAMP | Ya | — | — |
| `updated_at` | TIMESTAMP | Ya | — | — |
| `deleted_at` | TIMESTAMP | Tidak | Soft delete timestamp | — |

**Relasi:**
- DM-002 dimiliki oleh DM-001 (users) — `user_id` FK
- DM-002 approved_by DM-001 (users) — `approved_by` FK
- DM-002 memiliki satu DM-003 (addresses) — one-to-one
- DM-002 memiliki banyak DM-004 (kost_images) — one-to-many
- DM-002 memiliki banyak DM-006 (category_kost) — many-to-many via junction
- DM-002 memiliki banyak DM-007 (kost_document_requirements) — one-to-many
- DM-002 memiliki banyak DM-008 (room_types) — one-to-many

**Index:**
- Unique: `slug`
- Index: `user_id`, `status`, `published_at`, `deleted_at`

**Catatan:**
- Status lifecycle: draft → pending_review → approved/rejected → active
- ADR-013: facilities & rules disimpan sebagai JSON array of strings (bukan relational scheme)
- ADR-014: QRIS + bank info untuk payment (bukan Midtrans)

---

### DM-003: addresses

| Field | Tipe | Wajib? | Deskripsi | Constraint |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED AUTO_INCREMENT | Ya | Primary key | Unique |
| `kost_id` | BIGINT UNSIGNED | Ya | Kost yang memiliki alamat ini | FK → kosts.id, UNIQUE |
| `full_address` | TEXT | Ya | Alamat lengkap | — |
| `district` | VARCHAR(100) | Ya | Kecamatan | — |
| `city` | VARCHAR(100) | Ya | Kota/Kabupaten | — |
| `province` | VARCHAR(100) | Ya | Provinsi | — |
| `postal_code` | VARCHAR(10) | Tidak | Kode pos | — |
| `country` | VARCHAR(100) | Ya | Negara | Default: 'Indonesia' |
| `latitude` | DECIMAL(10,8) | Tidak | Koordinat latitude untuk map | — |
| `longitude` | DECIMAL(11,8) | Tidak | Koordinat longitude untuk map | — |
| `created_at` | TIMESTAMP | Ya | — | — |
| `updated_at` | TIMESTAMP | Ya | — | — |

**Relasi:**
- DM-003 dimiliki oleh DM-002 (kosts) — one-to-one via `kost_id` (UNIQUE)

**Index:**
- Unique: `kost_id`
- Index: `city`, `district` (untuk search/filter)

**Catatan:**
- 1:1 relationship dengan kosts. Saat kost di-delete, address ikut cascade delete.

---

### DM-004: kost_images

| Field | Tipe | Wajib? | Deskripsi | Constraint |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED AUTO_INCREMENT | Ya | Primary key | Unique |
| `kost_id` | BIGINT UNSIGNED | Ya | Kost yang memiliki image ini | FK → kosts.id |
| `image_path` | VARCHAR(255) | Ya | Path ke file image | — |
| `is_thumbnail` | BOOLEAN | Ya | Marker thumbnail utama | Default: false |
| `sort_order` | SMALLINT UNSIGNED | Ya | Urutan display di galeri | — |
| `created_at` | TIMESTAMP | Ya | — | — |
| `updated_at` | TIMESTAMP | Ya | — | — |

**Relasi:**
- DM-004 dimiliki oleh DM-002 (kosts) — many-to-one via `kost_id`

**Index:**
- Index: `kost_id`, `is_thumbnail`
- Unique composite: `(kost_id, is_thumbnail)` WHERE `is_thumbnail = true` — maksimal 1 thumbnail per kost

**Catatan:**
- Satu kost hanya boleh punya 1 thumbnail. Saat Admin set image sebagai thumbnail, thumbnail lama di-unset.

---

### DM-005: categories

| Field | Tipe | Wajib? | Deskripsi | Constraint |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED AUTO_INCREMENT | Ya | Primary key | Unique |
| `name` | VARCHAR(100) | Ya | Nama kategori (misal: Putra, Putri, Campur) | — |
| `slug` | VARCHAR(100) | Ya | URL-friendly identifier | Unique |
| `description` | TEXT | Tidak | Deskripsi kategori | — |
| `created_at` | TIMESTAMP | Ya | — | — |
| `updated_at` | TIMESTAMP | Ya | — | — |
| `deleted_at` | TIMESTAMP | Tidak | Soft delete timestamp | — |

**Relasi:**
- DM-005 memiliki banyak DM-006 (category_kost) — many-to-many via junction

**Index:**
- Unique: `slug`
- Index: `deleted_at`

**Catatan:**
- Master category hanya CRUD oleh Super Admin.
- Soft delete: kategori tidak muncul di dropdown Admin, tapi kost yang sudah pakai tetap valid.

---

### DM-006: category_kost (junction table)

| Field | Tipe | Wajib? | Deskripsi | Constraint |
|---|---|---|---|---|
| `kost_id` | BIGINT UNSIGNED | Ya | FK ke kosts | FK → kosts.id |
| `category_id` | BIGINT UNSIGNED | Ya | FK ke categories | FK → categories.id |

**Primary Key:** Composite (`kost_id`, `category_id`)

**Relasi:**
- Junction table untuk many-to-many antara DM-002 (kosts) dan DM-005 (categories)

**Index:**
- Composite primary key: `(kost_id, category_id)`

**Catatan:**
- Satu kost dapat punya banyak kategori, satu kategori dapat di-assign ke banyak kost.

---

### DM-007: kost_document_requirements

| Field | Tipe | Wajib? | Deskripsi | Constraint |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED AUTO_INCREMENT | Ya | Primary key | Unique |
| `kost_id` | BIGINT UNSIGNED | Ya | Kost yang memiliki requirement ini | FK → kosts.id |
| `document_type` | VARCHAR(50) | Ya | Jenis dokumen (ktp, selfie, student_card, family_card, reference_letter, other) | — |
| `is_required` | BOOLEAN | Ya | Wajib atau opsional | Default: false |
| `reason` | TEXT | Tidak | Alasan permintaan dokumen ini | — |
| `created_at` | TIMESTAMP | Ya | — | — |
| `updated_at` | TIMESTAMP | Ya | — | — |

**Relasi:**
- DM-007 dimiliki oleh DM-002 (kosts) — many-to-one via `kost_id`

**Index:**
- Index: `kost_id`
- Unique composite: `(kost_id, document_type)` — 1 jenis dokumen hanya muncul 1x per kost

**Catatan:**
- Admin dapat tambah/edit/hapus document requirement per kost.
- Ditampilkan di marketplace detail kost agar Tenant aware sebelum booking.

---

### DM-008: room_types

| Field | Tipe | Wajib? | Deskripsi | Constraint |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED AUTO_INCREMENT | Ya | Primary key | Unique |
| `kost_id` | BIGINT UNSIGNED | Ya | Kost yang memiliki room type ini | FK → kosts.id |
| `name` | VARCHAR(100) | Ya | Nama room type (misal: Single, Double) | — |
| `slug` | VARCHAR(100) | Ya | URL-friendly identifier | — |
| `description` | TEXT | Tidak | Deskripsi room type | — |
| `room_size` | VARCHAR(50) | Ya | Ukuran kamar (misal: 3x4 m) | — |
| `max_occupants` | TINYINT UNSIGNED | Ya | Kapasitas maksimal orang per room | — |
| `security_deposit` | DECIMAL(12,2) | Ya | Uang jaminan (deposit) | — |
| `facilities` | JSON | Tidak | Array of strings (facilities room type level) | — |
| `rules` | JSON | Tidak | Array of strings (rules room type level) | — |
| `created_at` | TIMESTAMP | Ya | — | — |
| `updated_at` | TIMESTAMP | Ya | — | — |
| `deleted_at` | TIMESTAMP | Tidak | Soft delete timestamp | — |

**Relasi:**
- DM-008 dimiliki oleh DM-002 (kosts) — many-to-one via `kost_id`
- DM-008 memiliki banyak DM-009 (room_type_images) — one-to-many
- DM-008 memiliki banyak DM-010 (price_schemes) — one-to-many
- DM-008 memiliki banyak DM-011 (rooms) — one-to-many

**Index:**
- Index: `kost_id`, `deleted_at`
- Unique composite: `(kost_id, name)`, `(kost_id, slug)`

**Catatan:**
- ADR-013: facilities & rules disimpan sebagai JSON array of strings (sama pattern dengan kost).
- ADR-018: `max_occupants` menentukan kapasitas room. 1 room bisa > 1 rental aktif (selama belum penuh).

---

### DM-009: room_type_images

| Field | Tipe | Wajib? | Deskripsi | Constraint |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED AUTO_INCREMENT | Ya | Primary key | Unique |
| `room_type_id` | BIGINT UNSIGNED | Ya | Room type yang memiliki image ini | FK → room_types.id |
| `image_path` | VARCHAR(255) | Ya | Path ke file image | — |
| `is_thumbnail` | BOOLEAN | Ya | Marker thumbnail utama | Default: false |
| `sort_order` | SMALLINT UNSIGNED | Ya | Urutan display di galeri | — |
| `created_at` | TIMESTAMP | Ya | — | — |
| `updated_at` | TIMESTAMP | Ya | — | — |

**Relasi:**
- DM-009 dimiliki oleh DM-008 (room_types) — many-to-one via `room_type_id`

**Index:**
- Index: `room_type_id`, `is_thumbnail`
- Unique composite: `(room_type_id, is_thumbnail)` WHERE `is_thumbnail = true`

**Catatan:**
- Sama pattern dengan kost_images. 1 thumbnail per room type.

---

### DM-010: price_schemes

| Field | Tipe | Wajib? | Deskripsi | Constraint |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED AUTO_INCREMENT | Ya | Primary key | Unique |
| `room_type_id` | BIGINT UNSIGNED | Ya | Room type yang memiliki price scheme ini | FK → room_types.id |
| `name` | VARCHAR(100) | Ya | Nama price scheme (misal: Harian, Bulanan) | — |
| `description` | TEXT | Tidak | Deskripsi price scheme | — |
| `price` | DECIMAL(12,2) | Ya | Harga sewa per durasi | — |
| `duration_value` | SMALLINT UNSIGNED | Ya | Nilai durasi (misal: 1, 3, 6) | — |
| `duration_unit` | ENUM('day','week','month') | Ya | Unit durasi (day/week/month) | — |
| `is_active` | BOOLEAN | Ya | Status aktif (dapat dipilih Tenant) | Default: true |
| `created_at` | TIMESTAMP | Ya | — | — |
| `updated_at` | TIMESTAMP | Ya | — | — |
| `deleted_at` | TIMESTAMP | Tidak | Soft delete timestamp | — |

**Relasi:**
- DM-010 dimiliki oleh DM-008 (room_types) — many-to-one via `room_type_id` (ADR-013: 1:N, bukan M:N)
- DM-010 direferensikan oleh DM-012 (rentals) — one-to-many via `rentals.price_scheme_id` (snapshot)

**Index:**
- Index: `room_type_id`, `is_active`, `deleted_at`

**Catatan:**
- ADR-013: Price Scheme 1:N dengan Room Type (setiap price scheme belongs_to 1 room type). Hapus junction table `room_type_price_schemes`.
- Price scheme inactive tidak muncul di pilihan Tenant saat create rental.
- Rental menyimpan snapshot price, jadi edit price scheme tidak mempengaruhi rental lama.

---

### DM-011: rooms

| Field | Tipe | Wajib? | Deskripsi | Constraint |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED AUTO_INCREMENT | Ya | Primary key | Unique |
| `kost_id` | BIGINT UNSIGNED | Ya | Kost yang memiliki room ini | FK → kosts.id |
| `room_type_id` | BIGINT UNSIGNED | Ya | Room type dari room ini | FK → room_types.id |
| `code` | VARCHAR(30) | Ya | Kode unik room dalam 1 kost (misal: A1, B2) | — |
| `status` | ENUM('available','unavailable') | Ya | Status ketersediaan room | Default: 'available' |
| `internal_notes` | TEXT | Tidak | Catatan internal Admin tentang room | — |
| `created_at` | TIMESTAMP | Ya | — | — |
| `updated_at` | TIMESTAMP | Ya | — | — |
| `deleted_at` | TIMESTAMP | Tidak | Soft delete timestamp | — |

**Relasi:**
- DM-011 dimiliki oleh DM-002 (kosts) — many-to-one via `kost_id`
- DM-011 dimiliki oleh DM-008 (room_types) — many-to-one via `room_type_id`
- DM-011 memiliki banyak DM-012 (rentals) — one-to-many

**Index:**
- Index: `kost_id`, `room_type_id`, `status`, `deleted_at`
- Unique composite: `(kost_id, code)`

**Catatan:**
- **ADR-017:** Status enum hanya 2 values: `available`, `unavailable` (bukan reserved/occupied/maintenance). Reserved/Occupied dihitung real-time dari rentals.
- **ADR-018:** Room availability dihitung berdasarkan `max_occupants` dari room_type. 1 room bisa punya multiple rental aktif (selama `used_slots < max_occupants`).
- Admin hanya dapat set `unavailable` jika room benar-benar kosong (tidak ada rental pending/paid/confirmed/active).
- Formula okupasi per room:
  ```
  reserved_count = COUNT(rentals WHERE room_id = X AND status IN ('pending','paid','confirmed'))
  occupied_count = COUNT(rentals WHERE room_id = X AND status = 'active')
  used_slots = reserved_count + occupied_count
  free_slots = room_type.max_occupants - used_slots
  ```

---

### DM-012: rentals

| Field | Tipe | Wajib? | Deskripsi | Constraint |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED AUTO_INCREMENT | Ya | Primary key | Unique |
| `room_id` | BIGINT UNSIGNED | Ya | Room yang disewa | FK → rooms.id |
| `user_id` | BIGINT UNSIGNED | Ya | Tenant penyewa | FK → users.id |
| `price_scheme_id` | BIGINT UNSIGNED | Ya | Price scheme yang dipilih (untuk referensi, snapshot disimpan di kolom lain) | FK → price_schemes.id |
| `duration_value` | SMALLINT UNSIGNED | Ya | Snapshot durasi value | — |
| `duration_unit` | ENUM('day','week','month') | Ya | Snapshot durasi unit | — |
| `start_date` | DATETIME | Ya | Tanggal mulai sewa (Tenant pilih saat booking) | — |
| `end_date` | DATETIME | Ya | Tanggal selesai sewa (calculated: start_date + duration) | — |
| `room_price` | DECIMAL(12,2) | Ya | Snapshot harga sewa | — |
| `security_deposit` | DECIMAL(12,2) | Ya | Snapshot uang jaminan | — |
| `grand_total` | DECIMAL(12,2) | Ya | Total biaya (room_price × duration_value + security_deposit) | — |
| `status` | ENUM('pending','paid','confirmed','active','completed','cancelled') | Ya | Status rental | Default: 'pending' |
| `cancelled_reason` | TEXT | Tidak | Alasan cancellation (manual atau auto) | — |
| `cancelled_at` | TIMESTAMP | Tidak | Timestamp saat rental cancelled | — |
| `created_at` | TIMESTAMP | Ya | — | — |
| `updated_at` | TIMESTAMP | Ya | — | — |

**Relasi:**
- DM-012 dimiliki oleh DM-011 (rooms) — many-to-one via `room_id`
- DM-012 dimiliki oleh DM-001 (users) — many-to-one via `user_id`
- DM-012 reference DM-010 (price_schemes) — many-to-one via `price_scheme_id` (snapshot)
- DM-012 memiliki banyak DM-013 (rental_documents) — one-to-many
- DM-012 memiliki satu DM-014 (payments) — one-to-one
- DM-012 memiliki banyak DM-015 (rental_status_histories) — one-to-many
- DM-012 memiliki satu DM-016 (reviews) — one-to-one (optional, setelah completed)

**Index:**
- Index: `room_id`, `user_id`, `price_scheme_id`, `status`, `start_date`, `end_date`, `created_at`

**Catatan:**
- **ADR-016:** Min start_date = today + 4 hari (payment 48h + document processing 48-72h).
- Status lifecycle: `pending` → `paid` → `confirmed` → `active` → `completed` (atau `cancelled` dari status apapun).
- Cancel dari Active diperbolehkan (perubahan dari rencana). Tenant dapat cancel kapan saja. No refund di sistem.
- Snapshot price + duration disimpan untuk protect dari perubahan price scheme di masa depan.

---

### DM-013: rental_documents

| Field | Tipe | Wajib? | Deskripsi | Constraint |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED AUTO_INCREMENT | Ya | Primary key | Unique |
| `rental_id` | BIGINT UNSIGNED | Ya | Rental yang memiliki dokumen ini | FK → rentals.id |
| `document_type` | VARCHAR(50) | Ya | Jenis dokumen (ktp, selfie, student_card, dll.) | — |
| `document_path` | VARCHAR(255) | Ya | Path ke file dokumen | — |
| `uploaded_at` | TIMESTAMP | Ya | Timestamp saat Tenant upload | — |
| `verification_status` | ENUM('pending','approved','rejected') | Ya | Status verifikasi Admin | Default: 'pending' |
| `verified_by` | BIGINT UNSIGNED | Tidak | Admin yang verify | FK → users.id |
| `verified_at` | TIMESTAMP | Tidak | Timestamp saat Admin verify | — |
| `rejection_reason` | TEXT | Tidak | Alasan reject dari Admin | — |
| `notes` | TEXT | Tidak | Catatan Admin | — |
| `created_at` | TIMESTAMP | Ya | — | — |
| `updated_at` | TIMESTAMP | Ya | — | — |

**Relasi:**
- DM-013 dimiliki oleh DM-012 (rentals) — many-to-one via `rental_id`
- DM-013 verified_by DM-001 (users) — many-to-one via `verified_by`

**Index:**
- Index: `rental_id`, `verification_status`, `verified_by`

**Catatan:**
- Tenant dapat upload ulang dokumen setelah rejected. Upload baru → status kembali `pending`, `rejection_reason` di-clear.
- Admin wajib input `rejection_reason` saat reject.

---

### DM-014: payments

| Field | Tipe | Wajib? | Deskripsi | Constraint |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED AUTO_INCREMENT | Ya | Primary key | Unique |
| `rental_id` | BIGINT UNSIGNED | Ya | Rental yang memiliki payment ini (1:1) | FK → rentals.id, UNIQUE |
| `qris_image_path` | VARCHAR(255) | Ya | Path ke QRIS image kost (copy dari kosts.qris_image_path) | — |
| `amount` | DECIMAL(12,2) | Ya | Total amount (copy dari rentals.grand_total) | — |
| `proof_of_payment_path` | VARCHAR(255) | Tidak | Path ke bukti pembayaran yang diupload Tenant | — |
| `status` | ENUM('pending','success','failed') | Ya | Status payment | Default: 'pending' |
| `verified_by` | BIGINT UNSIGNED | Tidak | Admin yang verify payment | FK → users.id |
| `verified_at` | TIMESTAMP | Tidak | Timestamp saat Admin verify | — |
| `rejection_reason` | TEXT | Tidak | Alasan reject dari Admin | — |
| `expired_at` | TIMESTAMP | Ya | Payment deadline (created_at + 48 hours) | — |
| `paid_at` | TIMESTAMP | Tidak | Timestamp saat payment success | — |
| `created_at` | TIMESTAMP | Ya | — | — |
| `updated_at` | TIMESTAMP | Ya | — | — |

**Relasi:**
- DM-014 dimiliki oleh DM-012 (rentals) — one-to-one via `rental_id` (UNIQUE)
- DM-014 verified_by DM-001 (users) — many-to-one via `verified_by`

**Index:**
- Unique: `rental_id`
- Index: `status`, `verified_by`, `expired_at`

**Catatan:**
- **ADR-014:** Payment menggunakan QRIS statis + upload bukti + verifikasi manual Admin (bukan Midtrans). Hapus kolom `transaction_id`, `gateway`, `method`, `payment_url`. Hapus tabel `payment_logs`.
- 1 Rental : 1 Payment (1:1 relationship). Payment record created saat rental created.
- Payment deadline: 48 jam dari rental created_at. Auto-cancel rental jika expired_at terlewati dan status bukan `success`.
- Tenant dapat upload ulang proof setelah rejected. Upload baru → `rejection_reason` di-clear, status kembali `pending`.

---

### DM-015: rental_status_histories

| Field | Tipe | Wajib? | Deskripsi | Constraint |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED AUTO_INCREMENT | Ya | Primary key | Unique |
| `rental_id` | BIGINT UNSIGNED | Ya | Rental yang memiliki history ini | FK → rentals.id |
| `status` | ENUM('pending','paid','confirmed','active','completed','cancelled') | Ya | Status baru setelah transition | — |
| `changed_by` | BIGINT UNSIGNED | Ya | User yang trigger perubahan (Tenant, Admin, atau System) | FK → users.id |
| `internal_notes` | TEXT | Tidak | Catatan internal tentang perubahan | — |
| `created_at` | TIMESTAMP | Ya | Timestamp saat perubahan status | — |

**Relasi:**
- DM-015 dimiliki oleh DM-012 (rentals) — many-to-one via `rental_id`
- DM-015 changed_by DM-001 (users) — many-to-one via `changed_by`

**Index:**
- Index: `rental_id`, `status`, `created_at`

**Catatan:**
- Audit trail untuk setiap perubahan status rental. Created via observer atau Action class.
- Untuk auto-transition (auto-cancel, auto-activate, auto-complete), `changed_by` bisa di-set ke system user ID (misal: user_id = 1 untuk system).

---

### DM-016: reviews

| Field | Tipe | Wajib? | Deskripsi | Constraint |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED AUTO_INCREMENT | Ya | Primary key | Unique |
| `rental_id` | BIGINT UNSIGNED | Ya | Rental yang direview (1:1) | FK → rentals.id, UNIQUE |
| `kost_rating` | TINYINT UNSIGNED | Tidak | Rating kost (1-5) | CHECK (kost_rating BETWEEN 1 AND 5) |
| `kost_comment` | TEXT | Tidak | Comment untuk kost | — |
| `room_rating` | TINYINT UNSIGNED | Tidak | Rating room (1-5) | CHECK (room_rating BETWEEN 1 AND 5) |
| `room_comment` | TEXT | Tidak | Comment untuk room | — |
| `images` | JSON | Tidak | Array of image paths untuk review images | — |
| `created_at` | TIMESTAMP | Ya | — | — |
| `updated_at` | TIMESTAMP | Ya | — | — |

**Relasi:**
- DM-016 dimiliki oleh DM-012 (rentals) — one-to-one via `rental_id` (UNIQUE)

**Index:**
- Unique: `rental_id`
- Index: `kost_rating`, `room_rating`, `created_at`

**Catatan:**
- **ADR-015:** Gabung `kost_reviews` + `room_reviews` jadi 1 tabel. Review images disimpan sebagai JSON array (bukan polymorphic table).
- Review eligibility: rental status = `completed`, belum ada review untuk rental ini.
- Minimal 1 rating harus diisi (kost_rating atau room_rating). Validation di application layer.
- Review images: max 5 images per review, disimpan sebagai JSON array di kolom `images`. Cast attribute: `['images' => 'array']`.

---

## 6. Rute & Kontrak (Routes & API-xxx)

### 6.1 Peta Route Web (Web Routes Map) — jalur utama, sesuai baseline

> Karena baseline proyek ini adalah **web routes** (bukan API-first), sebagian besar interface publik didaftarkan di sini, bukan sebagai `API-xxx`. Middleware `auth` berarti butuh session login aktif. Middleware `verified` berarti butuh email verified. Middleware `role:xxx` berarti butuh role tertentu.

#### Public Routes (no auth required)

| Route | Method | Controller@Action | Middleware | Memenuhi Requirement |
|---|---|---|---|---|
| `/` | GET | `HomeController@index` | — | Landing page |
| `/marketplace` | GET | `MarketplaceController@index` | — | FR-048, US-014 (stub interim sejak TASK-086/ADR-023 — empty state; implementasi penuh TASK-036, COMP-005) |
| `/marketplace/kosts/{kost}` | GET | `KostDetailController@show` | — | FR-057, US-016 |

#### Auth Routes (Laravel Breeze, customized untuk OTP)

| Route | Method | Controller@Action | Middleware | Memenuhi Requirement |
|---|---|---|---|---|
| `/login` | GET | `Auth\LoginController@create` | `guest` | FR-001 |
| `/login` | POST | `Auth\LoginController@store` | `guest` | FR-001 |
| `/register` | GET | `Auth\RegisterController@create` | `guest` | FR-003 |
| `/register` | POST | `Auth\RegisterController@store` | `guest` | FR-003 |
| `/logout` | POST | `Auth\LoginController@destroy` | `auth` | FR-002 |
| `/verify-email` | GET | `Auth\EmailVerificationController@show` | `auth`, `throttle:5,1` | FR-004 |
| `/verify-email` | POST | `Auth\EmailVerificationController@verify` | `auth`, `throttle:5,1` | FR-004 |
| `/email/resend` | POST | `Auth\EmailVerificationController@resend` | `auth` | FR-005 |
| `/forgot-password` | GET | `Auth\PasswordResetLinkController@create` | `guest` | FR-130 |
| `/forgot-password` | POST | `Auth\PasswordResetLinkController@store` | `guest`, `throttle:5,1` | FR-130 |
| `/reset-password` | GET | `Auth\PasswordResetLinkController@showOtp` | `guest` | FR-130 |
| `/reset-password/verify` | POST | `Auth\PasswordResetLinkController@verifyOtp` | `guest`, `throttle:5,1` | FR-130 |
| `/reset-password/change` | GET | `Auth\NewPasswordController@create` | `guest` | FR-130 |
| `/reset-password/change` | POST | `Auth\NewPasswordController@store` | `guest` | FR-130 |

#### Profile Routes

| Route | Method | Controller@Action | Middleware | Memenuhi Requirement |
|---|---|---|---|---|
| `/profile` | GET | `ProfileController@show` | `auth` | FR-009 |
| `/profile` | PATCH | `ProfileController@update` | `auth` | FR-010, FR-129 |
| `/profile/avatar` | POST | `ProfileController@updateAvatar` | `auth` | FR-011 |
| `/account` | DELETE | `ProfileController@destroy` | `auth` | FR-012 |

#### Tenant Routes

| Route | Method | Controller@Action | Middleware | Memenuhi Requirement |
|---|---|---|---|---|
| `/rentals` | GET | `Tenant\RentalController@index` | `auth`, `role:user` | FR-096, US-021 |
| `/rentals/create` | GET | `Tenant\RentalController@create` | `auth`, `verified`, `role:user` | FR-061, FR-062 |
| `/rentals` | POST | `Tenant\RentalController@store` | `auth`, `verified`, `role:user` | FR-063—FR-067, US-010 |
| `/rentals/{rental}` | GET | `Tenant\RentalController@show` | `auth`, `role:user` | FR-097, US-021 |
| `/rentals/{rental}/cancel` | POST | `Tenant\RentalController@cancel` | `auth`, `role:user` | FR-123, US-022 |
| `/rentals/{rental}/payment` | GET | `Tenant\PaymentController@show` | `auth`, `role:user` | FR-069, US-011 |
| `/rentals/{rental}/payment/proof` | POST | `Tenant\PaymentController@uploadProof` | `auth`, `role:user` | FR-070, FR-075, US-011 |
| `/rentals/{rental}/documents` | POST | `Tenant\RentalDocumentController@store` | `auth`, `role:user` | FR-084, US-013 |
| `/rentals/{rental}/reviews` | GET | `Tenant\ReviewController@create` | `auth`, `role:user` | FR-105 |
| `/rentals/{rental}/reviews` | POST | `Tenant\ReviewController@store` | `auth`, `role:user` | FR-106—FR-108, US-018 |

#### Admin Routes

| Route | Method | Controller@Action | Middleware | Memenuhi Requirement |
|---|---|---|---|---|
| `/admin/kosts` | GET | `Admin\KostController@index` | `auth`, `role:admin` | US-004 |
| `/admin/kosts/create` | GET | `Admin\KostController@create` | `auth`, `role:admin` | FR-014 |
| `/admin/kosts` | POST | `Admin\KostController@store` | `auth`, `role:admin` | FR-014, US-004 |
| `/admin/kosts/{kost}` | GET | `Admin\KostController@show` | `auth`, `role:admin` | US-004 |
| `/admin/kosts/{kost}/edit` | GET | `Admin\KostController@edit` | `auth`, `role:admin` | FR-015 |
| `/admin/kosts/{kost}` | PATCH | `Admin\KostController@update` | `auth`, `role:admin` | FR-015, US-004 |
| `/admin/kosts/{kost}/submit` | POST | `Admin\KostController@submit` | `auth`, `role:admin` | FR-016, US-005 |
| `/admin/kosts/{kost}/publish` | POST | `Admin\KostController@publish` | `auth`, `role:admin` | FR-021, US-008 |
| `/admin/kosts/{kost}/categories` | GET | `Admin\KostController@editCategories` | `auth`, `role:admin` | FR-027 |
| `/admin/kosts/{kost}/categories` | PATCH | `Admin\KostController@updateCategories` | `auth`, `role:admin` | FR-027, US-009 |
| `/admin/kosts/{kost}/payment` | GET | `Admin\KostController@editPayment` | `auth`, `role:admin` | FR-030, FR-031 |
| `/admin/kosts/{kost}/payment` | PATCH | `Admin\KostController@updatePayment` | `auth`, `role:admin` | FR-030, FR-031 |
| `/admin/kosts/{kost}/images` | GET | `Admin\KostImageController@index` | `auth`, `role:admin` | FR-026 |
| `/admin/kosts/{kost}/images` | POST | `Admin\KostImageController@store` | `auth`, `role:admin` | FR-026 |
| `/admin/kosts/{kost}/images/{image}` | DELETE | `Admin\KostImageController@destroy` | `auth`, `role:admin` | FR-026 |
| `/admin/kosts/{kost}/images/{image}/thumbnail` | PATCH | `Admin\KostImageController@setThumbnail` | `auth`, `role:admin` | FR-026 |
| `/admin/kosts/{kost}/images/sort-order` | PATCH | `Admin\KostImageController@updateSortOrder` | `auth`, `role:admin` | FR-026 |
| `/admin/kosts/{kost}/document-requirements` | GET | `Admin\DocumentRequirementController@index` | `auth`, `role:admin` | FR-032, FR-033 |
| `/admin/kosts/{kost}/document-requirements` | POST | `Admin\DocumentRequirementController@store` | `auth`, `role:admin` | FR-032, FR-033 |
| `/admin/kosts/{kost}/document-requirements/{requirement}` | PATCH | `Admin\DocumentRequirementController@update` | `auth`, `role:admin` | FR-032, FR-033 |
| `/admin/kosts/{kost}/document-requirements/{requirement}` | DELETE | `Admin\DocumentRequirementController@destroy` | `auth`, `role:admin` | FR-034 |

> **Implementation note (COMP-003, 2026-08-24):** Original design in document referenced `Admin\KostConfigurationController` single controller all configuration endpoints. Actual implementation (TASK-018—TASK-026) split responsibilities: basic info/address/categories/payment handled by `Admin\KostController` RESTful methods (editCategories, updateCategories, editPayment, updatePayment), images by `Admin\KostImageController`, document requirements by `Admin\DocumentRequirementController`. Controller split improves separation concerns follows Laravel resource controller patterns. See `routes/web.php` lines 42-103 complete route definitions. Eager loading documented: `KostController::show()` loads address, categories, kostImages, documentRequirements prevent N+1 queries.

| `/admin/kosts/{kost}/room-types/create` | GET | `Admin\RoomTypeController@create` | `auth`, `role:admin` | FR-036 |
| `/admin/kosts/{kost}/room-types` | POST | `Admin\RoomTypeController@store` | `auth`, `role:admin` | FR-036, US-005 |
| `/admin/room-types/{roomType}/edit` | GET | `Admin\RoomTypeController@edit` | `auth`, `role:admin` | FR-037 |
| `/admin/room-types/{roomType}` | PATCH | `Admin\RoomTypeController@update` | `auth`, `role:admin` | FR-037, US-005 |
| `/admin/room-types/{roomType}/images` | POST | `Admin\RoomTypeController@uploadImages` | `auth`, `role:admin` | FR-038 |
| `/admin/room-types/{roomType}/facilities` | PATCH | `Admin\RoomTypeController@updateFacilities` | `auth`, `role:admin` | FR-039 |
| `/admin/room-types/{roomType}/rules` | PATCH | `Admin\RoomTypeController@updateRules` | `auth`, `role:admin` | FR-040 |
| `/admin/room-types/{roomType}/price-schemes/create` | GET | `Admin\PriceSchemeController@create` | `auth`, `role:admin` | FR-041 |
| `/admin/room-types/{roomType}/price-schemes` | POST | `Admin\PriceSchemeController@store` | `auth`, `role:admin` | FR-041, US-005 |
| `/admin/price-schemes/{priceScheme}/edit` | GET | `Admin\PriceSchemeController@edit` | `auth`, `role:admin` | FR-042 |
| `/admin/price-schemes/{priceScheme}` | PATCH | `Admin\PriceSchemeController@update` | `auth`, `role:admin` | FR-042, US-005 |
| `/admin/price-schemes/{priceScheme}/toggle` | POST | `Admin\PriceSchemeController@toggle` | `auth`, `role:admin` | FR-043 |
| `/admin/room-types/{roomType}/rooms/create` | GET | `Admin\RoomController@create` | `auth`, `role:admin` | FR-044 |
| `/admin/room-types/{roomType}/rooms` | POST | `Admin\RoomController@store` | `auth`, `role:admin` | FR-044, US-005 |
| `/admin/rooms/{room}/edit` | GET | `Admin\RoomController@edit` | `auth`, `role:admin` | FR-045 |
| `/admin/rooms/{room}` | PATCH | `Admin\RoomController@update` | `auth`, `role:admin` | FR-045, US-005 |
| `/admin/rooms/{room}/toggle-unavailable` | POST | `Admin\RoomController@toggleUnavailable` | `auth`, `role:admin` | FR-046 |
| `/admin/rentals` | GET | `Admin\RentalManagementController@index` | `auth`, `role:admin` | FR-098, US-016 |
| `/admin/rentals/{rental}` | GET | `Admin\RentalManagementController@show` | `auth`, `role:admin` | FR-099, US-016 |
| `/admin/payments/{payment}/verify` | POST | `Admin\PaymentVerificationController@approve` | `auth`, `role:admin` | FR-072, US-012 |
| `/admin/payments/{payment}/reject` | POST | `Admin\PaymentVerificationController@reject` | `auth`, `role:admin` | FR-073, US-012 |
| `/admin/rental-documents/{document}/verify` | POST | `Admin\DocumentVerificationController@approve` | `auth`, `role:admin` | FR-088, US-014 |
| `/admin/rental-documents/{document}/reject` | POST | `Admin\DocumentVerificationController@reject` | `auth`, `role:admin` | FR-089, US-014 |

#### Super Admin Routes

| Route | Method | Controller@Action | Middleware | Memenuhi Requirement |
|---|---|---|---|---|
| `/superadmin/submissions` | GET | `SuperAdmin\KostSubmissionController@index` | `auth`, `role:superadmin` | FR-018 |
| `/superadmin/submissions/{kost}` | GET | `SuperAdmin\KostSubmissionController@show` | `auth`, `role:superadmin` | FR-018 |
| `/superadmin/submissions/{kost}/approve` | POST | `SuperAdmin\KostSubmissionController@approve` | `auth`, `role:superadmin` | FR-018, US-006 |
| `/superadmin/submissions/{kost}/reject` | POST | `SuperAdmin\KostSubmissionController@reject` | `auth`, `role:superadmin` | FR-019, US-006 |
| `/superadmin/admins` | GET | `SuperAdmin\AdminManagementController@index` | `auth`, `role:superadmin` | FR-114, US-020 |
| `/superadmin/admins/create` | GET | `SuperAdmin\AdminManagementController@create` | `auth`, `role:superadmin` | FR-111 |
| `/superadmin/admins` | POST | `SuperAdmin\AdminManagementController@store` | `auth`, `role:superadmin` | FR-111, US-019 |
| `/superadmin/admins/{admin}/edit` | GET | `SuperAdmin\AdminManagementController@edit` | `auth`, `role:superadmin` | FR-115 |
| `/superadmin/admins/{admin}` | PATCH | `SuperAdmin\AdminManagementController@update` | `auth`, `role:superadmin` | FR-115, US-020 |
| `/superadmin/admins/{admin}` | DELETE | `SuperAdmin\AdminManagementController@destroy` | `auth`, `role:superadmin` | FR-116, US-020 |
| `/superadmin/categories` | GET | `SuperAdmin\CategoryController@index` | `auth`, `role:superadmin` | FR-117 |
| `/superadmin/categories/create` | GET | `SuperAdmin\CategoryController@create` | `auth`, `role:superadmin` | FR-117 |
| `/superadmin/categories` | POST | `SuperAdmin\CategoryController@store` | `auth`, `role:superadmin` | FR-117, US-009, US-021 |
| `/superadmin/categories/{category}/edit` | GET | `SuperAdmin\CategoryController@edit` | `auth`, `role:superadmin` | FR-118 |
| `/superadmin/categories/{category}` | PATCH | `SuperAdmin\CategoryController@update` | `auth`, `role:superadmin` | FR-118, US-021 |
| `/superadmin/categories/{category}` | DELETE | `SuperAdmin\CategoryController@destroy` | `auth`, `role:superadmin` | FR-119, US-021 |

### 6.2 Kontrak API (API-xxx) — kondisional

> Bagian ini HANYA dipakai untuk endpoint yang memang dirancang mengembalikan JSON murni (mis. webhook, endpoint AJAX yang di-fetch dari Blade/Alpine.js, atau integrasi eksternal) — bukan untuk mendeskripsikan route halaman biasa yang sudah tercakup di §6.1. Jika proyek ternyata butuh API publik penuh (dikonsumsi klien eksternal/mobile), itu penyimpangan dari baseline dan wajib didahului ADR baru (lihat §7).

**Untuk MVP ini: tidak ada API eksternal.** Semua interaksi via web routes dengan render server-side.

---

## 7. Architecture Decision Records (ADR-xxx)

> Format wajib: Konteks → Keputusan → Alternatif yang dipertimbangkan → Konsekuensi.

### ADR-001: Modular Monolith dengan Laravel 13
- **Status:** `Accepted`
- **Konteks:** Proyek SewaKost adalah proyek pra-UKK dengan timeline 12-18 minggu, dikerjakan oleh 1 full-stack developer. Skala MVP: 100 kost, 500 rooms, 1000 rentals. Tidak ada kebutuhan untuk independent deployment per-modul atau scaling berbeda per-service. Baseline teknis proyek ini sudah ditetapkan sebelum Fase Design dimulai: Laravel 13 sebagai framework utama.
- **Keputusan:** Membangun sistem sebagai **modular monolith** menggunakan Laravel 13 — satu codebase/deployable, dipecah secara **logis** per-domain (`COMP-xxx`) di dalam repo yang sama, bukan microservices. Setiap domain (Identity, Kost, Rental, Payment, dll.) diorganisir dalam folder `app/Domain/<NamaKomponen>/` dengan Models + Service/Action class-nya, Controller di `app/Http/Controllers/`, dan view di `resources/views/`.
- **Alternatif yang dipertimbangkan:**
  - **Microservices** — ditolak karena kompleksitas operasional (orkestrasi, observability lintas service, distributed transaction, inter-service communication) tidak sepadan untuk skala/tahap proyek saat ini. Overhead deployment, monitoring, dan debugging microservices terlalu tinggi untuk 1 developer dengan timeline 12-18 minggu.
  - **Framework lain (mis. Symfony, CodeIgniter)** — ditolak, Laravel 13 dipilih sebagai standar baseline proyek karena ekosistem lengkap (ORM, queue, cache, mail, auth starter kit), dokumentasi kuat, dan cocok untuk MVP rapid development.
  - **Unstructured monolith (tanpa pemisahan domain)** — ditolak karena akan menyebabkan coupling tinggi, sulit maintain, dan sulit scale logic/knowledge saat proyek berkembang.
- **Konsekuensi:**
  - **Positif:** Development velocity tinggi (1 codebase, 1 deployment, shared code mudah), debugging mudah (single process), transaction management mudah (1 database connection).
  - **Negatif:** Scaling dilakukan per-instance keseluruhan aplikasi (bukan per-modul). Jika di masa depan ada modul yang butuh scaling berbeda (misal: Marketplace traffic tinggi vs Admin traffic rendah), perlu re-architecture atau service extraction.
  - **Mitigasi:** Disiplin pemisahan folder per `COMP-xxx` (lihat §11) menjadi penting agar monolith tidak berubah jadi "big ball of mud". Dependency injection dan interface contract harus dijaga agar modul tetap loosely coupled dan mudah di-extract jika suatu saat dibutuhkan.

### ADR-002: Session-based Auth + Web Routes (bukan API-first)
- **Status:** `Accepted`
- **Konteks:** Tidak ada kebutuhan klien terpisah (SPA/mobile app) yang butuh token auth pada baseline proyek MVP ini. Target pengguna mengakses aplikasi via browser (desktop & mobile responsive). Tidak ada requirement untuk API publik yang dikonsumsi eksternal atau third-party integration yang butuh token-based auth.
- **Keputusan:** Autentikasi memakai **session Laravel bawaan** (cookie + CSRF protection); seluruh UI dirender server-side lewat **web routes** (`routes/web.php`) menggunakan Blade template engine (+ Alpine.js untuk interaktivitas minimal) — bukan SPA terpisah dengan token auth (Sanctum SPA mode/Passport). Laravel Breeze digunakan sebagai auth starter kit (login, register, email verification), dengan customisasi untuk OTP verification (6-digit code) menggantikan verification link.
- **Alternatif yang dipertimbangkan:**
  - **Token-based auth (Sanctum/Passport)** — ditolak untuk baseline MVP karena tidak ada klien terpisah yang butuh token. Token-based auth menambah kompleksitas (token storage, refresh token, CORS, stateless management) tanpa benefit nyata untuk use case server-rendered web app. Bisa ditambahkan lewat ADR baru jika nanti muncul kebutuhan mobile app atau API eksternal.
  - **SPA frontend (Vue/React) + API backend** — ditolak karena memisahkan frontend/backend menambah complexity (2 deployment, CORS, state management, SEO handling) tanpa benefit untuk MVP. Server-rendered Blade lebih simple dan cocok untuk rapid development 1 developer.
- **Konsekuensi:**
  - **Positif:** Simple architecture, CSRF protection bawaan Laravel, session management robust, SEO-friendly (server-rendered HTML), faster development untuk MVP.
  - **Negatif:** Tidak ada API publik by default. Jika suatu saat butuh mobile app atau API eksternal, perlu effort untuk add token-based auth layer (bukan major re-architecture, tapi perlu ADR baru yang men-supersede bagian ini). Interactivity terbatas pada Alpine.js (tidak seinteraktif SPA penuh).
  - **Kewajiban:** CSRF protection wajib aktif di semua form (middleware `VerifyCsrfToken`). Jangan pernah exclude CSRF tanpa ADR eksplisit. Session store harus shared (Redis) jika aplikasi di-scale ke >1 instance (lihat ADR-004, §10).

### ADR-003: MySQL sebagai Primary Persistence
- **Status:** `Accepted`
- **Konteks:** Proyek membutuhkan database relasional untuk menjaga referential integrity antar entities (kost → room_types → rooms → rentals, user → rentals → payments, dll.). ERD memiliki banyak relasi 1:N dan M:N dengan FK constraints. Transaction-sensitive operations (rental creation + room reservation, payment verification + rental status update) membutuhkan ACID guarantee. MySQL adalah RDBMS yang paling umum, widely supported, dan sudah familiar untuk kebanyakan developer. Hosting VPS biasanya menyediakan MySQL by default.
- **Keputusan:** Memilih **MySQL 8.0** sebagai database utama, diakses via **Eloquent ORM** Laravel. Semua perubahan schema melalui Laravel migration (versioned). Database backup terjadwal (daily) dengan retention 7 hari (lihat NFR-026, NFR-027).
- **Alternatif yang dipertimbangkan:**
  - **PostgreSQL** — ditolak bukan karena inferior, tapi karena MySQL lebih umum di shared hosting/VPS dan setup lebih simple untuk MVP. PostgreSQL bisa menjadi pilihan jika proyek butuh advanced features (full-text search, JSON query, spatial data) di masa depan.
  - **SQLite** — ditolak karena tidak cocok untuk production multi-user (locking issue, concurrency terbatas). SQLite hanya untuk development/testing.
  - **NoSQL (MongoDB, DynamoDB)** — ditolak karena ERD proyek ini sangat relational (banyak FK, join query). NoSQL menambah complexity untuk enforce referential integrity dan transaction.
- **Konsekuensi:**
  - **Positif:** ACID guarantee, FK constraints di database level, mature tooling (backup, replication, monitoring), Eloquent ORM simplifies query, migration versioning mudah di-maintain.
  - **Negatif:** Vertical scaling limit (MySQL scaling horizontal via read replica/sharding lebih complex dibanding NoSQL). Untuk MVP scale (1000 rentals) ini bukan masalah.
  - **Kewajiban:** Indexing wajib di semua FK, status columns, dan timestamp untuk query filtering (lihat §5 per-entity index notes). Migration wajib untuk semua schema changes (NFR-017). N+1 query harus dihindari via eager loading (NFR-002).

### ADR-004: Containerization via Docker — Laravel Sail untuk Local/Dev
- **Status:** `Accepted`
- **Konteks:** Kebutuhan konsistensi environment antara local development, staging, dan production. Tanpa containerization, risiko "works on my machine" tinggi (beda versi PHP, ekstensi PHP tidak install, database version mismatch). Laravel Sail adalah CLI resmi Laravel yang menyediakan Docker Compose setup siap pakai untuk development (PHP, MySQL/PostgreSQL, Redis, Mailpit, dll.) tanpa harus menulis `docker-compose.yml` dari nol. Sail mempercepat onboarding developer/agent baru.
- **Keputusan:** Environment **local/development** memakai **Laravel Sail** (`laravel/sail`, dijalankan lewat `./vendor/bin/sail`) — CLI resmi Laravel yang membungkus Docker Compose dengan image PHP, database, cache, dst. yang sudah dikonfigurasi sesuai kebutuhan Laravel. Environment **staging/production** tetap berjalan di Docker, tapi memakai **image produksi terpisah** (Dockerfile custom, multi-stage build, dioptimasi untuk production — mis. tanpa Xdebug/tooling dev, optimized opcache, smaller image size), bukan menjalankan setup Sail apa adanya, karena Sail secara resmi ditujukan untuk local development, bukan production.
- **Alternatif yang dipertimbangkan:**
  - **Menulis `docker-compose.yml` custom dari nol tanpa Sail** — ditolak untuk local/dev karena Sail sudah menyediakan setup siap pakai yang terintegrasi dengan Artisan (`sail artisan`, `sail composer`, `sail test`, `sail npm`), mempercepat onboarding. Untuk production tetap pakai Dockerfile custom (bukan Sail).
  - **Memakai image Sail apa adanya untuk production** — ditolak karena Sail tidak dirancang/dioptimasi untuk beban production (image size besar, include dev tooling, tidak optimized untuk security/performance). Dokumentasi resmi Laravel Sail juga menyatakan Sail untuk development, bukan production.
  - **Instalasi manual per-environment (tanpa Docker sama sekali)** — ditolak karena rawan drift ("works on my machine"), sulit reproduce issue, dan sulit onboarding.
- **Konsekuensi:**
  - **Positif:** Konsistensi environment local/staging/prod, onboarding cepat (1 command: `sail up`), isolasi dependencies, reproducible builds.
  - **Negatif:** Ada **dua definisi container** yang harus dijaga selaras: konfigurasi Sail (`docker-compose.yml` hasil `sail:install`) untuk dev, dan Dockerfile produksi terpisah untuk staging/prod. Perubahan versi PHP/ekstensi wajib disinkronkan di keduanya. Overhead disk space untuk Docker images di local (bukan masalah untuk development modern).
  - **Kewajiban:** Semua perintah PHP/Artisan/Composer di lingkungan dev dijalankan lewat `sail ...`, bukan langsung di host (lihat `AGENTS.md` §Setup & Perintah). Untuk production, Dockerfile wajib follow best practices: multi-stage build, non-root user, minimal attack surface, health check endpoint.

### ADR-005: Filesystem Storage (bukan Cloud Object Storage)
- **Status:** `Accepted`
- **Konteks:** MVP ini di-deploy di single Linux VPS (lihat PRD §10.2 Constraints, DEP-004). Tidak ada requirement untuk cloud object storage (S3, GCS) di MVP. Semua file upload (kost images, room images, QRIS, proof of payment, rental documents, review images, avatar) disimpan di filesystem server. Volume file untuk MVP scale (100 kost, 1000 rentals) masih manageable di VPS storage (estimasi: <10GB untuk 1 tahun).
- **Keputusan:** File disimpan di **filesystem lokal server** menggunakan Laravel Storage facade (default disk: `local` untuk private files, `public` untuk public files). Private storage (`storage/app/private/`) untuk file sensitif (rental documents, proof of payment). Public storage (`storage/app/public/`) untuk file yang boleh diakses langsung (kost images, room images, avatar). Symlink dari `public/storage` ke `storage/app/public` untuk serving public files. File wajib include dalam backup (lihat NFR-027).
- **Alternatif yang dipertimbangkan:**
  - **Cloud object storage (S3/GCS/Azure Blob)** — ditolak untuk MVP karena menambah dependency eksternal, biaya bulanan (storage + bandwidth), dan kompleksitas setup (IAM, credentials, SDK). Bisa menjadi pilihan jika di masa depan aplikasi di-scale ke multi-server atau butuh CDN. Laravel Storage facade abstraksi ini, jadi migrasi ke S3 di masa depan relatif mudah (hanya ubah config disk).
  - **Database BLOB storage** — ditolak karena performance issue (large blob memperlambat query, database size bloat), backup/restore lama, dan tidak recommended practice.
- **Konsekuensi:**
  - **Positif:** Simple setup, no external dependency, no monthly cost, fast access (local disk).
  - **Negatif:** Tidak scalable untuk multi-server (jika di masa depan scale horizontal, perlu shared storage atau migrate ke S3). Disk space limit (VPS storage finite, perlu monitoring). Backup wajib include file storage (tidak otomatis terpisah seperti S3 versioning).
  - **Kewajiban:**
    - File upload validation wajib: extension whitelist, MIME type check, max size (NFR-008).
    - Generated server-side filename (UUID/hash) untuk security, jangan pakai original filename user.
    - Private storage untuk dokumen sensitif (rental documents, proof of payment), pakai Laravel authorization (Policy/Gate) untuk akses file via controller.
    - Backup file storage bersama database backup (NFR-027).
    - Monitoring disk space VPS (alert jika >80% used).

### ADR-006: Email-only Notification (MVP)
- **Status:** `Accepted`
- **Konteks:** MVP membutuhkan notification untuk berbagai event (email verification OTP, kost approval/rejection, payment verified/rejected, document verified/rejected, rental status changes, admin account created, auto-cancel rental). PRD §5.2 Out of Scope menyatakan WhatsApp notification dan Push notification tidak dikerjakan di MVP. Email adalah channel notification paling universal dan mudah di-setup (SMTP provider murah/free: Mailtrap, SendGrid free tier, Gmail SMTP).
- **Keputusan:** Notification hanya via **Email (SMTP)** untuk MVP. Semua email dikirim via Laravel Mail facade dengan queue (async) menggunakan Redis queue driver. Email template menggunakan Laravel Mailable + Blade. Email failure tidak boleh menyebabkan operasi bisnis gagal (NFR-013) — email dikirim async, jika gagal hanya di-log error, transaksi bisnis tetap commit.
- **Alternatif yang dipertimbangkan:**
  - **WhatsApp notification (via WhatsApp Business API atau third-party gateway)** — ditolak untuk MVP karena kompleksitas setup (butuh WhatsApp Business approval, API key, webhook handling), biaya per-message, dan out of scope sesuai PRD §5.2. Bisa dipertimbangkan di masa depan jika ada budget dan requirement eksplisit.
  - **Push notification (web push / mobile push)** — ditolak untuk MVP karena tidak ada mobile app, dan web push membutuhkan service worker + HTTPS + user permission yang menambah complexity. Out of scope PRD §5.2.
  - **SMS notification** — ditolak karena biaya per-SMS tinggi, dan out of scope MVP.
  - **In-app notification only (tanpa email)** — ditolak karena user tidak aware perubahan status jika tidak buka aplikasi. Email memberikan proactive notification.
- **Konsekuensi:**
  - **Positif:** Simple setup, low cost (free tier SMTP cukup untuk MVP volume), universal (semua user punya email), mature tooling (Laravel Mail).
  - **Negatif:** Email delivery tidak 100% guaranteed (spam folder, email server down, invalid email). User mungkin tidak aware notification jika tidak cek email. Email notification kurang real-time dibanding WhatsApp/Push.
  - **Mitigasi:** Email wajib dikirim via queue (async) dengan retry mechanism (Laravel queue retry). Email failure di-log untuk monitoring (NFR-029, NFR-030). Critical status tetap tersimpan di database dan dapat dilihat di aplikasi meskipun email gagal. Untuk future: bisa add WhatsApp/Push via ADR baru tanpa menghapus email (multi-channel notification).

### ADR-007: Map Display Only (Leaflet + OpenStreetMap)
- **Status:** `Accepted`
- **Konteks:** Marketplace membutuhkan display lokasi kost di map (FR-058, US-016) agar Tenant dapat melihat lokasi geografis kost sebelum booking. Tidak ada requirement untuk fitur map advanced (routing, geocoding otomatis, search location by map, distance calculation, nearby search). Admin input latitude/longitude manual saat configure kost address. MVP hanya butuh display marker di map statis.
- **Keputusan:** Menggunakan **Leaflet.js** (open-source JavaScript library) + **OpenStreetMap tiles** untuk display map di halaman detail kost. Map hanya untuk display marker lokasi (read-only), tidak ada interactivity selain zoom/pan basic. Admin input lat/long manual di form address (atau dapat dari service geocoding eksternal di luar aplikasi, lalu paste ke form).
- **Alternatif yang dipertimbangkan:**
  - **Google Maps JavaScript API** — ditolak karena butuh API key, billing account (meskipun ada free tier, tetap perlu credit card), dan rate limit. Leaflet + OSM gratis tanpa API key untuk MVP scale.
  - **Mapbox** — ditolak karena sama dengan Google Maps, butuh API key dan ada pricing (meskipun free tier generous, tetap ada setup overhead).
  - **Self-hosted tile server** — ditolak karena resource-intensive (storage tile data ratusan GB, server dedicated untuk tile serving). Tidak sepadan untuk MVP yang hanya display marker.
- **Konsekuensi:**
  - **Positif:** Free, no API key, no rate limit untuk reasonable usage, open-source, lightweight library.
  - **Negatif:** OpenStreetMap public tile server punya fair use policy (rate limit tidak documented tapi ada). Jika traffic tinggi, perlu migrate ke tile provider berbayar atau self-hosted tile. Map styling terbatas dibanding Google Maps/Mapbox. Geocoding (address → lat/long) harus dilakukan manual atau via service eksternal (Nominatim API, Google Geocoding), tidak built-in di Leaflet.
  - **Kewajiban:** Respect OSM tile usage policy (https://operations.osmfoundation.org/policies/tiles/). Jika aplikasi scale dan traffic map tinggi, pertimbangkan migrate ke Mapbox/Google Maps atau CDN tile caching. Admin wajib input lat/long valid; validasi format di Form Request.

### ADR-008: Actor Generalization (User + Role)
- **Status:** `Accepted`
- **Konteks:** Sistem memiliki 3 jenis actor: Tenant (penyewa), Admin Kost (pengelola), Super Admin (platform admin). Setiap actor punya authentication (email/password) dan profile data (name, email, phone, avatar). Perbedaan utama hanya di authorization (apa yang boleh dilakukan). Tidak ada perbedaan fundamental di entity level yang memerlukan tabel terpisah.
- **Keputusan:** Menggunakan **single table `users`** dengan kolom `role` (enum: `user`, `admin`, `superadmin`) untuk RBAC. Tenant = user dengan role `user`, Admin Kost = user dengan role `admin`, Super Admin = user dengan role `superadmin`. Laravel middleware `role:xxx` untuk authorization di route level. Laravel Policy/Gate untuk resource-level authorization (misal: Admin hanya dapat edit kost miliknya).
- **Alternatif yang dipertimbangkan:**
  - **Separate tables per actor type** (`tenants`, `admins`, `superadmins`) — ditolak karena data redundan (email, password, name di 3 tabel berbeda), query complex (UNION untuk list all users), dan tidak flexible jika ada actor type baru di masa depan.
  - **Polymorphic relationship** (tabel `users` generic + `tenants`, `admins` detail via polymorphic) — ditolak karena over-engineering untuk use case ini. Perbedaan actor hanya di authorization, bukan di data structure.
  - **Spatie Permission package (role + permission granular)** — ditolak untuk MVP karena tidak butuh permission granular. Role-based sederhana cukup (3 role, authorization jelas per-role). Spatie bisa dipertimbangkan jika di masa depan butuh permission matrix complex.
- **Konsekuensi:**
  - **Positif:** Simple schema, query mudah, flexible untuk add role baru, authentication flow seragam.
  - **Negatif:** Tidak ada enforcing di database level untuk role-specific fields (misal: jika Tenant butuh field khusus yang tidak dipakai Admin). Semua field harus nullable atau default, tidak bisa required per-role. Untuk MVP ini bukan masalah karena profile data seragam.
  - **Kewajiban:** Middleware `role:xxx` wajib dipasang di semua route yang role-specific. Policy/Gate wajib implement resource ownership check (FR-008). Guest (unauthenticated) bukan user — tidak ada role `guest` di database, hanya status unauthenticated di session.

### ADR-009: State Transition Service Boundary
- **Status:** `Accepted`
- **Konteks:** Beberapa entity punya lifecycle state machine yang complex: Kost (draft → pending_review → approved/rejected → active), Rental (pending → paid → confirmed → active → completed/cancelled), Payment (pending → success/failed). State transition harus validate: (1) current state valid, (2) user punya authority untuk trigger transition, (3) preconditions terpenuhi (misal: kost baru bisa submit jika data wajib lengkap), (4) side effects dijalankan (misal: rental active → room occupied_count++), (5) history tercatat. Generic CRUD update (`PATCH /rentals/{id} { "status": "active" }`) rawan bypass validasi.
- **Keputusan:** Lifecycle transition **TIDAK melalui arbitrary CRUD update**. Setiap transition punya dedicated **Action class** dengan semantic operation name (misal: `SubmitKostForReview`, `ApproveKost`, `ActivateRental`, `CancelRental`). Action class wajib:
  1. Validate current state (misal: kost harus `draft` sebelum submit)
  2. Validate authority (Policy/Gate check: user berwenang trigger transition ini?)
  3. Validate preconditions (misal: data wajib lengkap, dokumen approved)
  4. Update state di database (transaction)
  5. Append history record (misal: `rental_status_histories`)
  6. Execute side effects (misal: send email notification, update room occupancy)
- **Alternatif yang dipertimbangkan:**
  - **Generic CRUD update** (Controller terima `status` field dari request, langsung update database) — ditolak karena tidak ada validation state machine, authority check mudah lupa, preconditions tidak enforced, side effects tidak konsisten.
  - **State machine library** (mis. `sebdesign/laravel-state-machine`, `spatie/laravel-model-states`) — ditolak untuk MVP karena overhead learning curve dan not critical. Action class pattern cukup simple dan explicit untuk use case ini. Library bisa dipertimbangkan jika state machine bertambah complex di masa depan.
  - **Eloquent Observer untuk enforce state transition** — ditolak sebagai sole mechanism karena observer tidak bisa inject authorization check (observer tidak punya context user). Observer boleh dipakai untuk append history, tapi validation + authorization tetap di Action class.
- **Konsekuensi:**
  - **Positif:** Explicit validation, authority check enforced, preconditions konsisten, side effects tidak lupa, audit trail history lengkap, testable (unit test per Action class).
  - **Negatif:** Lebih banyak code dibanding generic CRUD (1 Action class per transition). Untuk MVP dengan 3-4 state machine ini acceptable.
  - **Kewajiban:** Setiap state transition wajib via Action class, bukan langsung update Eloquent model. Controller hanya call Action class, tidak boleh langsung `$model->update(['status' => ...])`. Action class wajib wrap dalam DB transaction jika ada multi-step update.

### ADR-010: Transactional Rental Creation
- **Status:** `Accepted`
- **Konteks:** Rental creation melibatkan multi-step: (1) create rental record, (2) increment room occupancy (reserved_count/occupied_count via rental count), (3) create payment record. Jika salah satu step gagal (misal: payment record gagal create karena constraint violation), rental record tetap ada tapi data inconsistent. Risk: double booking (2 request concurrent create rental untuk room yang sama, race condition).
- **Keputusan:** Rental creation **wajib dalam DB transaction**. Action class `CreateRental` wrap semua step dalam `DB::transaction()`. Lock room dengan `FOR UPDATE` untuk prevent race condition:
  ```php
  DB::transaction(function() {
      // 1. Lock room untuk prevent double booking
      $room = Room::where('id', $roomId)
          ->where('status', 'available')
          ->lockForUpdate()
          ->firstOrFail();
      
      // 2. Check room occupancy (used_slots < max_occupants)
      $usedSlots = Rental::where('room_id', $room->id)
          ->whereIn('status', ['pending','paid','confirmed','active'])
          ->count();
      
      if ($usedSlots >= $room->roomType->max_occupants) {
          throw new RoomFullException();
      }
      
      // 3. Create rental record
      $rental = Rental::create([...]);
      
      // 4. Create payment record
      Payment::create([
          'rental_id' => $rental->id,
          'expired_at' => now()->addHours(48),
          ...
      ]);
      
      // 5. Append rental_status_histories
      RentalStatusHistory::create([...]);
  });
  ```
- **Alternatif yang dipertimbangkan:**
  - **Tanpa transaction** — ditolak karena inconsistency risk. Jika step gagal di tengah, data corrupt.
  - **Optimistic locking** (check occupancy, create rental, recheck occupancy, rollback if changed) — ditolak karena kompleksitas logic dan tetap ada race condition window. Pessimistic locking (`FOR UPDATE`) lebih reliable untuk prevent double booking.
  - **Redis distributed lock** (acquire lock untuk room_id, create rental, release lock) — ditolak untuk MVP karena overhead. DB-level lock cukup untuk single-database MVP. Redis lock bisa dipertimbangkan jika aplikasi scale ke multi-database/multi-server.
- **Konsekuensi:**
  - **Positif:** Atomicity guaranteed, inconsistency tidak mungkin, double booking prevented via lock.
  - **Negatif:** Performance impact (lock menahan concurrent request untuk room yang sama). Untuk MVP scale (1000 rentals) ini acceptable. Deadlock risk jika ada circular lock (mitigasi: lock order consistency).
  - **Kewajiban:** Semua multi-step create/update yang melibatkan relasi data wajib dalam transaction. Payment verification + rental status update juga wajib transaction. Document verification (semua required docs approved) + rental confirmation wajib transaction.

### ADR-011: Payment State Separation
- **Status:** `Accepted`
- **Konteks:** Payment state (`pending` → `success`/`failed`) dan Rental state (`pending` → `paid` → `confirmed` → `active` → `completed`/`cancelled`) adalah 2 lifecycle berbeda. Payment state merepresentasikan status transaksi pembayaran (apakah Tenant sudah bayar). Rental state merepresentasikan status keseluruhan lifecycle rental (dari booking sampai selesai sewa). Rental bisa cancelled meskipun payment success (misal: dokumen tidak dilengkapi). Payment success adalah **precondition** untuk rental transition `pending` → `paid`, tapi rental punya state tambahan setelah paid.
- **Keputusan:** **Payment state terpisah dari Rental state**. Tabel `payments` punya kolom `status` (enum: `pending`, `success`, `failed`). Tabel `rentals` punya kolom `status` (enum: `pending`, `paid`, `confirmed`, `active`, `completed`, `cancelled`). Rental `pending` → `paid` hanya terjadi jika payment `success`. Payment `success` tidak otomatis transition rental ke state lain (butuh document verification dulu sebelum `confirmed`). Payment failure atau expire menyebabkan rental `cancelled`, tapi payment record tetap ada (status `failed`) untuk audit.
- **Alternatif yang dipertimbangkan:**
  - **Rental state include payment state** (rental status: `pending_payment`, `payment_verified`, `document_pending`, dst.) — ditolak karena state explosion (terlalu banyak state kombinatorial), tidak flexible jika payment flow berubah, dan sulit extend untuk partial payment/installment di masa depan.
  - **Payment state adalah derived dari rental state** (jika rental `paid`, berarti payment success) — ditolak karena kehilangan audit trail payment. Tidak bisa track payment failure, retry, rejection reason jika payment state tidak eksplisit.
- **Konsekuensi:**
  - **Positif:** Separation of concerns, audit trail jelas (payment log terpisah dari rental log), flexible untuk extend payment flow (misal: partial payment, installment, multiple payment method), state machine rental tidak bloat.
  - **Negatif:** Perlu koordinasi antar state (payment success → trigger rental transition). Koordinasi ini handle via Action class `VerifyPayment` yang update payment state + trigger rental transition dalam transaction.
  - **Kewajiban:** Action class `VerifyPayment` wajib dalam transaction: (1) update payment status → `success`, (2) update rental status → `paid`, (3) append rental_status_histories, (4) send email notification. Payment record wajib 1:1 dengan rental (UNIQUE constraint `payments.rental_id`).

### ADR-012: History Preservation
- **Status:** `Accepted`
- **Konteks:** Data historis penting untuk audit, dispute resolution, dan reporting. Rental yang sudah completed/cancelled tidak boleh hilang. Review dari Tenant yang sudah soft-deleted accountnya tetap harus tampil di marketplace (reviewer anonymous). Kost yang pernah active tidak boleh di-hard delete (referential integrity untuk rental historis). Payment logs perlu untuk audit financial. Rental status histories perlu untuk track lifecycle dan troubleshooting.
- **Keputusan:** **Historical entities retained, bukan physically deleted**:
  1. **Soft delete** untuk entities yang boleh di-"delete" user tapi data historis harus tetap: `users`, `kosts`, `categories`, `room_types`, `price_schemes`, `rooms`. Soft delete via `deleted_at` timestamp (Laravel SoftDeletes trait).
  2. **No delete** (hard atau soft) untuk transactional entities: `rentals`, `payments`, `rental_documents`, `rental_status_histories`, `reviews`. Entities ini tidak punya UI "delete" — sekali created, permanent (hanya bisa cancel/complete, tidak bisa delete).
  3. **Cascade delete** hanya untuk fully-dependent child entities: `addresses` (jika kost deleted), `kost_images` (jika kost deleted), `room_type_images` (jika room_type deleted). Ini acceptable karena child data tidak ada independent meaning tanpa parent.
  4. **FK constraint ON DELETE behavior**:
     - `users` → `kosts`: RESTRICT (tidak boleh delete user jika masih punya kost, paksa transfer ownership atau soft delete kost dulu)
     - `users` (approved_by) → `kosts`: SET NULL (approval history tetap valid meskipun Super Admin deleted)
     - `rentals` → `reviews`: CASCADE (jika rental somehow deleted — yang seharusnya tidak terjadi — review ikut deleted)
     - Semua FK lain ke transactional data: RESTRICT (prevent accidental delete parent yang masih direferensi).
- **Alternatif yang dipertimbangkan:**
  - **Hard delete all** (tidak ada soft delete) — ditolak karena data historis hilang, audit trail putus, dispute tidak bisa diselesaikan.
  - **Soft delete semua entities** (termasuk transactional) — ditolak karena kompleksitas query (semua query harus `WHERE deleted_at IS NULL`), dan transactional data seharusnya memang tidak di-delete, bukan di-hide.
  - **Archive table** (move deleted records ke `users_archive`, `rentals_archive`, dst.) — ditolak karena overhead maintain 2 schema, query cross-table complex untuk reporting, dan soft delete sudah cukup simple untuk MVP.
- **Konsekuensi:**
  - **Positif:** Audit trail lengkap, dispute resolution possible, reporting historical data accurate, referential integrity terjaga.
  - **Negatif:** Database size growth (data tidak pernah physically deleted). Untuk MVP scale (1000 rentals, 500 users) dalam 1-2 tahun ini tidak menjadi masalah (<1GB data). Jika aplikasi scale ke jutaan records, perlu strategy archival (pindahkan data >2 tahun ke cold storage).
  - **Kewajiban:** Eloquent query wajib aware soft delete (default behavior Laravel SoftDeletes trait: `WHERE deleted_at IS NULL`). Untuk query yang perlu include soft deleted, explicit `withTrashed()`. Migration wajib set FK constraint ON DELETE sesuai tabel di atas. Backup wajib retain historical data (bukan only active data).

### ADR-013: Penyederhanaan Facility/Rule Scheme → JSON
- **Status:** `Accepted`
- **Konteks:** DDS original menggunakan pattern master items → scheme → scheme_items → junction untuk facilities dan rules. Total 10 tabel. Untuk MVP 1 developer timeline 12-18 minggu, pattern ini over-engineering. Admin hanya perlu input list facilities/rules sebagai teks. Facilities/rules adalah informational content untuk display, bukan filter dimensions (PRD §5.2 Out of Scope).
- **Keputusan:** Hapus 10 tabel facility/rule scheme. Ganti dengan kolom JSON di `kosts.facilities`, `kosts.rules`, `room_types.facilities`, `room_types.rules`. JSON berisi array of strings. Admin input via textarea multi-line atau dynamic list input di UI. Eloquent cast: `['facilities' => 'array', 'rules' => 'array']`.
- **Alternatif yang dipertimbangkan:**
  - Pertahankan full scheme pattern — ditolak karena kompleksitas tidak sepadan untuk MVP.
  - M:N langsung tanpa scheme — ditolak karena tetap butuh tabel master + junction. JSON lebih sederhana.
  - Single text column (comma-separated) — ditolak karena parsing tidak reliable.
- **Konsekuensi:**
  - Positif: Pengurangan 10 tabel, simplify UI, Admin lebih fleksibel, development velocity tinggi.
  - Negatif: Tidak ada validasi master items, tidak bisa filter marketplace by facility (tapi Out of Scope).
  - Terpengaruh: COMP-003, COMP-004, COMP-005, DM-002, DM-008, FR-028, FR-029, FR-039, FR-040.

### ADR-014: Payment QRIS Statis + Verifikasi Manual
- **Status:** `Accepted` (supersede ADR-006 Midtrans dari DDS)
- **Konteks:** PRD FR-069—FR-082 menggunakan QRIS statis + upload bukti + verifikasi manual. Payment gateway otomatis out of scope MVP (PRD §5.2). Midtrans kompleksitas setup (merchant account, webhook) tidak sepadan untuk MVP UKK pra-kompetensi timeline 12-18 minggu.
- **Keputusan:** QRIS statis per kost (Admin upload dari banking app) + Tenant upload bukti transfer + Admin verify manual dengan cross-check mutasi bank. Admin wajib `rejection_reason` jika reject. Payment deadline 48 jam. Hapus kolom Midtrans (`transaction_id`, `gateway`, `method`, `payment_url`). Hapus tabel `payment_logs`.
- **Alternatif yang dipertimbangkan:**
  - Pertahankan Midtrans — ditolak karena kompleksitas setup, biaya transaksi, tidak ada budget MVP.
  - Transfer manual tanpa QRIS — ditolak karena QRIS lebih convenient dan mengurangi error transfer.
- **Konsekuensi:**
  - Positif: No external dependency, no transaction fee, setup cepat, Admin kontrol penuh.
  - Negatif: Verifikasi manual, rentan fraud (bukti palsu). Mitigasi: Admin cross-check mutasi bank, wajib rejection_reason detail (RISK-002).
  - Terpengaruh: COMP-007, DM-014, FR-069—FR-082, RISK-002, US-011—US-012.

### ADR-015: Review Gabung + JSON Images
- **Status:** `Accepted`
- **Konteks:** DDS original: 2 tabel (`kost_reviews`, `room_reviews`) + polymorphic `review_images`. Total 3 tabel. Tenant hanya review setelah rental Completed, jadi 1 review per rental cukup mencakup kost+room sekaligus.
- **Keputusan:** Gabung jadi 1 tabel `reviews` dengan kolom: `rental_id`, `kost_rating`, `kost_comment`, `room_rating`, `room_comment`, `images` (JSON array). Tenant bisa review kost saja, room saja, atau keduanya (minimal 1 rating). Cast: `['images' => 'array']`.
- **Alternatif yang dipertimbangkan:**
  - Pertahankan 2 tabel + polymorphic — ditolak karena over-engineering untuk MVP.
  - Tabel images terpisah — ditolak karena JSON array lebih simple untuk max 5 images.
- **Konsekuensi:**
  - Positif: Pengurangan 3 tabel → 1 tabel, simplify submission, simplify display.
  - Negatif: Tidak bisa review terpisah di waktu berbeda (tapi requirement memang 1 review setelah completed).
  - Terpengaruh: COMP-008, DM-016, FR-105—FR-110, US-018.

### ADR-016: Rental Lifecycle Timing Constraints
- **Status:** `Accepted`
- **Konteks:** Payment deadline 48h + document processing ~48-72h. PRD Q-002 original: min start_date = today. Kontradiksi: start_date terlewati sebelum verification selesai → auto-cancel prematur.
- **Keputusan:** Min start_date = **today + 4 hari**. Max = today + 30 hari. Rationale: H+0-2 payment, H+2-4 document processing, H+4 earliest safe start. Auto-cancel jika: (1) payment belum success setelah 48h, (2) dokumen tidak lengkap sebelum start_date. Validation: `start_date >= today+4 AND <= today+30`.
- **Alternatif yang dipertimbangkan:**
  - Min = today (instant booking) — ditolak karena kontradiksi dengan processing time.
  - Min = today+2 dengan grace period — ditolak karena kompleksitas state management.
  - Dual flow (instant vs normal) — ditolak karena kompleksitas UX.
- **Konsekuensi:**
  - Positif: No kontradiksi timing, sistem punya waktu cukup untuk verification.
  - Negatif: Tenant tidak bisa booking untuk hari ini/besok/lusa (earliest H+4). Acceptable untuk MVP.
  - Terpengaruh: FR-067, COMP-006, DM-012, UI date picker, Q-002 di PRD (perlu update).

### ADR-017: Room Availability Calculation Strategy
- **Status:** `Accepted`
- **Konteks:** DDS original: `rooms.status` enum 4-5 values including `reserved`/`occupied`/`maintenance`. User feedback: "maintenance" terlalu spesifik. `reserved`/`occupied` adalah derived state dari rentals. Denormalisasi menambah kompleksitas transaction. Real-time calculation lebih sederhana dan always consistent.
- **Keputusan:** `rooms.status` hanya 2 values: `available`, `unavailable`. `reserved`/`occupied` dihitung real-time dari rentals: Reserved = rentals status pending/paid/confirmed, Occupied = rentals status active. Admin set `unavailable` hanya jika room kosong (validasi: no rental pending/paid/confirmed/active). Display okupasi: Marketplace hanya total available, Admin dashboard detail per-room.
- **Alternatif yang dipertimbangkan:**
  - Denormalisasi reserved/occupied — ditolak karena transaction complexity, inconsistency risk.
  - Hybrid Redis cache — ditolak karena over-engineering untuk MVP scale.
  - Keep maintenance separate — ditolak karena terlalu spesifik, `unavailable` lebih generic.
- **Konsekuensi:**
  - Positif: Simplifikasi state, no denormalisasi = no inconsistency, always consistent.
  - Negatif: Query availability butuh JOIN rentals (sedikit lebih lambat, tapi acceptable dengan indexing).
  - Terpengaruh: DM-011, COMP-004, COMP-006, FR-046, FR-047, UI okupasi display.

### ADR-018: Room Multi-Occupancy Support
- **Status:** `Accepted`
- **Konteks:** User feedback: 1 room bisa kapasitas > 1 orang (Twin, Dormitory). Rencana awal 1 rental = 1 room exclusive waste capacity untuk room max_occupants > 1. Use case valid: 2 mahasiswa beda sewa 1 room twin (each create rental sendiri).
- **Keputusan:** 1 room bisa > 1 rental aktif (selama belum penuh). `room_types.max_occupants` menentukan kapasitas. 1 rental = 1 orang. Availability per-slot: `used_slots = reserved + occupied`, `free_slots = max_occupants - used_slots`. Room available jika `status = available AND free_slots > 0`. Display Marketplace: "X slot tersedia". Lock query check `used_slots < max_occupants`.
- **Alternatif yang dipertimbangkan:**
  - 1 rental = 1 room exclusive — ditolak karena waste capacity untuk twin/dormitory.
  - 1 rental untuk > 1 orang — ditolak karena kompleksitas pricing dan use case tidak match.
- **Konsekuensi:**
  - Positif: Room kapasitas > 1 tidak waste, flexible untuk berbagai room type, pricing simple.
  - Negatif: Rental creation logic lebih complex (check occupancy), query availability count rentals per room.
  - Terpengaruh: DM-008 (max_occupants field), DM-011, COMP-004, COMP-006, FR-044, FR-064, ADR-017.

### ADR-019: Cancel Rental dari Status Active
- **Status:** `Accepted`
- **Konteks:** User feedback: "tenant dapat membatalkan rental meskipun statusnya telah aktif". Rencana awal: cancel hanya sebelum Active. Realitas: Tenant mungkin perlu cancel dari Active (pindah kota mendadak, kondisi darurat). Refund policy tetap: no refund di sistem (PRD FR-125).
- **Keputusan:** Tenant dapat cancel dari status apapun termasuk Active. Status transitions: `pending/paid/confirmed/active` semua bisa → `cancelled`. Tidak bisa cancel dari `completed`. No refund di sistem. `cancelled_reason` wajib diisi. Saat cancel, occupancy slot dikembalikan (free_slots++).
- **Alternatif yang dipertimbangkan:**
  - Restrict cancel hanya sebelum Active — ditolak karena tidak flexible untuk kondisi darurat.
  - Cancel Active butuh approval Admin — ditolak karena menambah friction, Admin tidak punya incentive reject.
  - Refund partial untuk cancel Active — ditolak karena kompleksitas kalkulasi, PRD eksplisit no refund di sistem.
- **Konsekuensi:**
  - Positif: Flexible untuk Tenant, simple implementation (no approval, no refund calculation).
  - Negatif: Admin handle refund nego manual (di luar sistem), room bisa kosong mendadak, no penalty/fee di sistem.
  - Terpengaruh: FR-123, FR-124, COMP-006, DM-012, UI rental detail (button Cancel untuk Active).

### ADR-020: PHP 8.5 untuk Development
- **Status:** `Accepted`
- **Konteks:** ARCHITECTURE.md §3 original menyebutkan PHP 8.3+ sebagai minimum requirement untuk Laravel 13. Saat setup environment development dengan Laravel Sail, Dockerfile yang di-generate default menggunakan PHP 8.5 (latest dari PPA Ondřej Surý untuk Ubuntu 24.04). PHP 8.5 adalah versi yang kompatibel dengan Laravel 13 (requirement: PHP 8.2+, rekomendasi: 8.3+). Pertanyaan: apakah kita tetap pakai PHP 8.5 dari Sail atau downgrade ke 8.3 untuk strict adherence ke spec original?
- **Keputusan:** **Menggunakan PHP 8.5** untuk development (Sail) dan production. Rationale:
  1. **Kompatibilitas:** PHP 8.5 kompatibel dengan Laravel 13 (requirement minimum terpenuhi: 8.2+).
  2. **Sail default:** Laravel Sail official Dockerfile untuk PHP 8.5 sudah include semua extension yang dibutuhkan (mysql, redis, imagick, xdebug, dll.) — custom Dockerfile sudah disiapkan di `docker/8.5/Dockerfile`.
  3. **Future-proof:** PHP 8.5 lebih baru, dapat benefit dari performance improvement dan security patches terbaru.
  4. **Development velocity:** Tidak ada breaking change dari 8.3 → 8.5 yang mempengaruhi codebase Laravel 13 standar. Downgrade ke 8.3 tidak memberikan nilai tambah untuk MVP.
  5. **Konsistensi dev/prod:** Menggunakan version yang sama untuk development dan production mengurangi risk "works on my machine".
- **Alternatif yang dipertimbangkan:**
  - **Downgrade ke PHP 8.3** — ditolak karena tidak ada benefit signifikan, justru menambah friction (custom Dockerfile, test compatibility, manual extension install).
  - **PHP 8.4** — tidak ada karena 8.5 adalah latest stable yang tersedia di PPA resmi saat setup (2026-08-13).
- **Konsekuensi:**
  - **Positif:** Mengikuti Sail default (less custom config), future-proof, development velocity tinggi, konsistensi dev/prod.
  - **Negatif:** Spec ARCHITECTURE.md §3 original menyebut "8.3+" yang bisa ambigu (apakah 8.5 termasuk?). Dokumentasi perlu update.
  - **Kewajiban:** Update ARCHITECTURE.md §3 Tech Stack table: PHP 8.3+ → 8.5 (atau "8.3-8.5" jika ingin explicit range). Update §3.1 dokumentasi link: tambah note PHP 8.5. Production Dockerfile (§9) update dari `php:8.3-fpm-alpine` → `php:8.5-fpm-alpine` atau pin ke 8.5 explicit. Testing wajib di PHP 8.5 environment (bukan assume backward compat dengan 8.3).

### ADR-021: PHPUnit sebagai Test Framework Utama
- **Status:** `Accepted`
- **Konteks:** Laravel 13 support 2 test framework: PHPUnit (default, mature) dan Pest (modern, expressive syntax). AGENTS.md §Setup & Perintah menyebutkan "jika proyek pakai Pest: `./vendor/bin/sail test`", yang membingungkan karena tidak tegas framework mana yang dipakai. Saat setup awal, `composer.json` default Laravel 13 include PHPUnit 12.5.12, tidak include Pest. Pertanyaan: apakah proyek ini pakai PHPUnit atau Pest?
- **Keputusan:** **Proyek ini menggunakan PHPUnit** sebagai test framework utama (bukan Pest). Rationale:
  1. **Laravel default:** Laravel 13 fresh installation include PHPUnit, bukan Pest. Pest optional.
  2. **Team familiarity:** PHPUnit adalah standard industry, lebih mature, dokumentasi lengkap, support universal. Pest adalah wrapper di atas PHPUnit dengan syntax sugar — tidak add fundamental capability, hanya syntax preference.
  3. **Simplicity:** Untuk MVP dengan 1 developer (solo atau dengan agent), tambahan dependency Pest tidak critical. PHPUnit cukup untuk write test readable dan maintainable.
  4. **Compatibility:** PHPUnit 12.x stable, compatible dengan Laravel 13, no breaking changes expected untuk lifecycle proyek MVP ini.
  5. **AGENTS.md ambiguitas:** Dokumentasi AGENTS.md perlu update untuk remove ambiguity — tegas pakai PHPUnit, remove mention Pest kecuali ada keputusan explicit untuk switch.
- **Alternatif yang dipertimbangkan:**
  - **Pest** — ditolak untuk MVP karena tidak ada added value signifikan, menambah dependency, dan syntax preference bukan blocker. Pest bisa dipertimbangkan di masa depan jika team grow dan ada preference kuat untuk expressive syntax.
  - **Dual support (PHPUnit + Pest)** — ditolak karena menambah kompleksitas (2 test suite, mixing styles), tidak ada benefit untuk solo development.
- **Konsekuensi:**
  - **Positif:** Consistency (stick to Laravel default), mature tooling, less dependency, clear documentation path.
  - **Negatif:** Tidak dapat benefit dari Pest syntax (test description natural language, `it()` / `expect()` chaining). Untuk developer yang prefer Pest, ini bisa dianggap kurang ergonomic.
  - **Kewajiban:** Update AGENTS.md §Setup & Perintah: remove mention Pest, tegas command test: `./vendor/bin/sail artisan test` atau `./vendor/bin/sail test` (PHPUnit). Update ARCHITECTURE.md §3.1: tambah PHPUnit version (12.5.12) ke tabel dokumentasi. Test ditulis dengan PHPUnit syntax (bukan Pest), mengikuti struktur `tests/Feature/` dan `tests/Unit/` standar Laravel.

### ADR-022: Password Reset via OTP (reuse OTP service)
- **Status:** `Accepted`
- **Konteks:** Breeze default reset password berbasis token link (`password_reset_tokens` + email berisi link). Sistem sudah memiliki infrastruktur OTP untuk email verification (FR-004/005/128): tabel `otp_verifications`, cache Redis, hash SHA-256, lockout, throttle. Mekanisme token link Breeze butuh klik email, tidak punya lockout brute-force, dan memakai tabel terpisah.
- **Keputusan:** Reset password memakai alur OTP 3 langkah (email → OTP → password baru), reuse `OtpService` dengan purpose `'password-reset'`. `OtpService::verify` menambah parameter `markEmailVerified: false` agar reset password tidak menandai email sebagai verified. Session menyimpan `password_reset_email` + `password_reset_verified` sebagai guard antar langkah. Anti-enumeration: response generik untuk email yang tidak terdaftar. Tabel `password_reset_tokens` dibiarkan (tidak dipakai, tidak di-drop — YAGNI). Route: `password.request`, `password.email`, `password.otp`, `password.otp.verify`, `password.reset`, `password.store` (lihat §6.1). OtpVerificationMail memilih subject + instruksi berdasarkan purpose; untuk `password-reset`: `[SewaKost] Kode Reset Password Anda`.
- **Alternatif yang dipertimbangkan:**
  - **Token link Breeze** — ditolak: butuh klik email, tanpa lockout brute-force, tabel terpisah, alur lebih panjang untuk user.
  - **Tabel OTP terpisah khusus reset password** — ditolak: duplikasi infrastruktur OTP (storage, lockout, throttle, expiry) yang sudah ada.
- **Konsekuensi:**
  - **Positif:** Satu infrastruktur OTP dipakai untuk verifikasi email & reset password; lockout & throttle berlaku di kedua konteks; alur reset lebih pendek (tanpa klik email).
  - **Negatif:** `OtpService` menjadi multi-purpose (konteks di-kodekan via `$purpose` di `generate`/`verify`); satu OTP aktif per user — request reset password menimpa OTP email verification yang belum dipakai; shared lockout counter antara verification & reset (percobaan gagal reset ikut menghitung lockout verification).
  - **Kewajiban:** Update PRD.md (FR-130), PAGES.md (PAGE-006A/006B/006C + EMAIL-008), TODO.md (TASK-085). `User::maskedEmail()` dipakai untuk menampilkan email tersamar (menggantikan `maskEmail` private di EmailVerificationController).

### ADR-023: On-Demand Email Verification
- **Status:** `Accepted`
- **Konteks:** Awalnya OTP dikirim otomatis saat registrasi (FR-003/FR-004 lama). Kenyataan: verifikasi tidak wajib untuk semua fitur (FR-006) — user bisa langsung explore marketplace tanpa verified; OTP tidak diminta. Kirim otomatis = spam email + paksaan langkah yang tidak diperlukan.
- **Keputusan:** Registrasi hanya membuat akun (role `user`, `email_verified_at` NULL) + redirect ke `/marketplace` (`marketplace.index`) tanpa mengirim OTP. OTP dikirim **on-demand (lazy)** saat: (a) user membuka `/verify-email` (`verification.notice`) dan belum ada OTP valid (`hasValidOtp` false → `OtpService::generate`), atau OTP sebelumnya expired (>15 menit, FR-128); (b) middleware `verified` (`EnsureEmailIsVerified`) memblok fitur yang butuh email terverifikasi → `redirect()->back()` + flash `verify_email_prompt` → modal popup dengan CTA ke halaman verifikasi. Route GET `verification.notice` diberi `throttle:5,1` karena berpotensi mengirim email. Marketplace `/marketplace` dibuat **stub interim** (`MarketplaceController@index`, view empty state, `$kosts = collect()`, tanpa auth) agar redirect pasca-registrasi valid; diganti implementasi penuh di TASK-036 (COMP-005).
- **Alternatif yang dipertimbangkan:**
  - **Tetap kirim OTP saat registrasi** — ditolak: menjadi required step, spam email untuk user yang tidak butuh verified, bertentangan dengan FR-006.
  - **Modal langsung berisi form OTP di halaman yang diblok** — ditolak: versi 2 kompleksitas — halaman fitur harus render + kelola state OTP; keep simple, modal hanya prompt + CTA ke halaman verifikasi.
- **Konsekuensi:**
  - **Positif:** Email OTP hanya keluar saat user menunjukkan intent; register flow lebih singkat (tanpa step verifikasi); sesuai FR-006 (browse marketplace tanpa verified); modal popup seragam via flash session di layout.
  - **Negatif:** Resend/expiry tetap 15 menit (FR-128); `OtpService::hasValidOtp` menjadi titik cek lazy di `EmailVerificationController@show`; diperlukan stub marketplace (vs "kosong sampai COMP-005"); user yang tidak pernah verified menumpuk di DB (akun pasif, tanpa OTP terkirim).

---

## 8. Keamanan (Security Architecture)

| Aspek | Pendekatan | NFR Terkait |
|---|---|---|
| Autentikasi | Session-based Laravel (cookie `HttpOnly` + `Secure`, session ID di-regenerate saat login) — lihat ADR-002. Laravel Breeze sebagai starter kit dengan customisasi OTP verification. | NFR-004, NFR-006 |
| Otorisasi | RBAC dengan role (`user`, `admin`, `superadmin`) via middleware `role:xxx`. Laravel Gate/Policy per model untuk resource-level authorization (FR-008: user hanya akses resource miliknya). | NFR-005, NFR-010 |
| CSRF Protection | Wajib aktif (middleware `VerifyCsrfToken` default Laravel) di semua form web routes — jangan pernah di-exclude tanpa ADR eksplisit. Blade `@csrf` directive di semua form. | NFR-007 |
| Session security | `SESSION_SECURE_COOKIE=true` (HTTPS only) + `SESSION_HTTP_ONLY=true` di production. Session store terpusat (Redis, bukan file) di production agar valid saat scaling >1 instance. Session ID regenerate saat login/logout untuk prevent session fixation. | NFR-004, NFR-010 |
| Enkripsi data at rest | Password hashed via bcrypt (Laravel default). Sensitive fields (proof of payment path, rental documents path) disimpan di private storage (`storage/app/private/`) dengan authorization via Policy sebelum file serving. Database encryption untuk PII optional (MySQL transparent encryption jika available di VPS). | NFR-006, NFR-032 |
| Enkripsi data in transit | TLS 1.2+ wajib di production, diterminasi di reverse proxy/load balancer (Nginx) di depan container aplikasi. Let's Encrypt untuk SSL certificate (free, auto-renew). | NFR-032 |
| Manajemen secret | Environment variable via `.env` (TIDAK PERNAH commit `.env` asli — hanya `.env.example`). Di container, inject via Docker environment variable atau Docker secrets. Database credentials, SMTP password, APP_KEY tidak hardcode di code/image. | NFR-009 |
| Input validation | Form Request validation untuk semua input user. Eloquent ORM untuk prevent SQL injection. Blade auto-escape output untuk prevent XSS. File upload validation: whitelist extension (jpeg/png/pdf), MIME type check, max size (NFR-008). | NFR-007 |
| File upload security | Generated server-side filename (UUID), tidak pakai original filename user. Private storage untuk sensitive files (rental documents, proof of payment) — serve via controller dengan authorization check. Public storage untuk non-sensitive (kost images, avatar). File path tidak expose di URL (use signed URL atau controller route). | NFR-008, NFR-032 |
| Rate limiting | Laravel throttle middleware di login route (max 5 attempts per menit per IP), register route (max 3 per menit per IP). Global rate limit 60 requests per menit per IP untuk authenticated routes. | NFR-xxx (abuse prevention) |
| Audit trail | Rental status histories (`rental_status_histories`) untuk track lifecycle. Soft delete untuk preserve historical data. Log level ERROR untuk security events (failed login, unauthorized access attempt). | NFR-012, NFR-029, NFR-030 |

---

## 9. Arsitektur Deployment

```
Local/Dev (Laravel Sail):
  developer/agent → `./vendor/bin/sail up -d` → docker-compose.yml (hasil `sail:install`)
        └─ container: laravel.test (app) ─ container: mysql ─ container: redis ─ container: mailpit

Staging/Production (Docker, image terpisah — BUKAN setup Sail apa adanya):
                [Reverse Proxy / Load Balancer (Nginx) — TLS termination]
                                    │
                                    ▼
        ┌───────────────────────────────────────────────────┐
        │  Docker Container: Laravel App (image produksi)     │
        │  (Nginx + PHP-FPM 8.3)                              │
        │  - /var/www/html (application code)                 │
        │  - Supervisor (queue worker)                        │
        └───────────────────────────────────────────────────┘
                    │              │                │
                    ▼              ▼                ▼
        [Container: MySQL 8]  [Container: Redis 7]  [Volume: storage/app]

Single VPS Deployment (MVP):
- 1 VPS Linux (2 vCPU, 4GB RAM, 40GB SSD) — lihat PRD DEP-004
- Docker Compose untuk orchestration (production compose file terpisah dari Sail)
- Nginx sebagai reverse proxy di host (TLS termination, forward ke app container port 80)
- Backup cron job di host (daily backup database + storage ke external location)
```

| Environment | URL | Auto-deploy dari branch | Catatan |
|---|---|---|---|
| Development (Local) | `http://localhost` atau `http://localhost:80` | — | Via Laravel Sail, developer/agent local machine |
| Staging | `https://staging.sewakost.example.com` (TBD) | `staging` branch (optional) | Pre-production testing environment (optional untuk MVP) |
| Production | `https://sewakost.example.com` (TBD) | `main` branch (manual deploy via SSH + git pull) | Single VPS, no auto-deploy untuk MVP (manual safer) |

**Deployment Procedure (Production):**
1. SSH ke VPS: `ssh user@vps-ip`
2. Navigate ke app directory: `cd /var/www/sewakost`
3. Pull latest code: `git pull origin main`
4. Install dependencies: `docker-compose exec app composer install --no-dev --optimize-autoloader`
5. Run migrations: `docker-compose exec app php artisan migrate --force`
6. Clear cache: `docker-compose exec app php artisan config:cache && php artisan route:cache && php artisan view:cache`
7. Restart containers: `docker-compose restart app`
8. Restart queue worker: `docker-compose exec app php artisan queue:restart`

**Dockerfile Production** (simplified, di folder `docker/php/Dockerfile`):
```dockerfile
FROM php:8.3-fpm-alpine

# Install dependencies
RUN apk add --no-cache nginx supervisor mysql-client

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql bcmath opcache

# Copy application
COPY . /var/www/html
WORKDIR /var/www/html

# Composer install
RUN composer install --no-dev --optimize-autoloader

# File permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Supervisor config (queue worker + php-fpm)
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
```

---

## 10. Skalabilitas & Performa

### 10.1 Target Performance (dari NFR-001)
- Operasi CRUD dasar selesai < 3 detik
- Page load marketplace < 2 detik untuk 20 kost items
- Query database avoid N+1 problem (eager loading wajib)

### 10.2 Database Indexing Strategy
Berdasarkan §5 Data Model, indexing wajib di:
- **Primary keys** (auto-index)
- **Foreign keys:** Semua FK column (user_id, kost_id, room_id, rental_id, dll.) — accelerate JOIN query
- **Status columns:** `kosts.status`, `rooms.status`, `rentals.status`, `payments.status`, `rental_documents.verification_status` — frequently filtered
- **Timestamp columns:** `rentals.start_date`, `rentals.end_date`, `payments.expired_at`, `created_at` — date range query
- **Search columns:** `kosts.name`, `kosts.slug`, `addresses.city`, `addresses.district` — marketplace search/filter
- **Unique constraints:** `users.email`, `kosts.slug`, `rooms.code` (per kost), `rentals.rental_id` (payment 1:1)
- **Composite index untuk occupancy query:** `rentals(room_id, status, start_date)` — ADR-017 room availability calculation

### 10.3 Caching Strategy
- **Config cache:** `php artisan config:cache` di production (cache semua config di 1 file)
- **Route cache:** `php artisan route:cache` di production (compile routes)
- **View cache:** `php artisan view:cache` di production (pre-compile Blade templates)
- **Query cache (Redis):** Cache marketplace kost list (TTL: 5 menit), cache kost detail (TTL: 10 menit), invalidate cache saat kost status berubah atau data updated
- **Session cache (Redis):** Session driver = Redis untuk support multi-instance scaling
- **OTP cache (Redis):** OTP verification code (TTL: 15 menit), key: `otp:{user_id}`

### 10.4 Queue & Background Jobs
- **Queue driver:** Redis (production), sync (development)
- **Queue worker:** Run via Supervisor di container (auto-restart on failure)
- **Jobs yang di-queue:**
  - Email sending (verification OTP, payment notification, document notification, rental status notification) — async untuk tidak block request
  - Scheduled job `MonitorRentalLifecycle` (run setiap jam via Laravel Scheduler): check payment deadline, document deadline, auto-activate, auto-complete

### 10.5 Eager Loading untuk Prevent N+1
- **Marketplace kost list:** `Kost::with(['address', 'categories', 'kost_images' => fn($q) => $q->where('is_thumbnail', true)])`
- **Kost detail:** `Kost::with(['address', 'categories', 'kost_images', 'room_types.room_type_images', 'room_types.price_schemes', 'reviews.rental.user'])`
- **Rental list:** `Rental::with(['room.room_type', 'payment', 'rental_documents', 'user'])`
- Monitor N+1 via Laravel Debugbar (development) atau Laravel Telescope (optional)

### 10.6 Horizontal Scaling Considerations (Future)
Untuk MVP: single VPS cukup. Jika di masa depan perlu scale horizontal (>1 instance):
- **Session store:** Sudah Redis (shared across instances) ✅
- **File storage:** Migrate ke S3/GCS (shared storage) atau NFS mount — saat ini filesystem lokal (lihat ADR-005)
- **Database:** Read replica MySQL (write ke master, read dari replica) — saat ini single MySQL
- **Load balancer:** Nginx/HAProxy di depan multiple app instances — saat ini single instance
- **Queue worker:** Separate container/server untuk queue worker (scale independent dari web)

---

## 11. Struktur Direktori (Convention)

```
sewakost/
├── app/
│   ├── Domain/               # satu folder per COMP-xxx (Model + Service/Action per-domain)
│   │   ├── Identity/         # COMP-001: User, OtpVerification (optional)
│   │   ├── Kost/             # COMP-002, COMP-003: Kost, Address, KostImage, Category, KostDocumentRequirement
│   │   ├── RoomInventory/    # COMP-004: RoomType, RoomTypeImage, PriceScheme, Room
│   │   ├── Rental/           # COMP-006: Rental, RentalDocument, RentalStatusHistory
│   │   ├── Payment/          # COMP-007: Payment
│   │   ├── Review/           # COMP-008: Review
│   │   └── Actions/          # Shared Action classes (atau per-domain jika prefer)
│   ├── Http/
│   │   ├── Controllers/      # satu subfolder per role/context
│   │   │   ├── Auth/         # Laravel Breeze auth controllers (customized OTP)
│   │   │   ├── ProfileController.php
│   │   │   ├── MarketplaceController.php
│   │   │   ├── KostDetailController.php
│   │   │   ├── Tenant/       # Tenant-specific controllers (RentalController, PaymentController, ReviewController)
│   │   │   ├── Admin/        # Admin-specific controllers (KostController, RoomTypeController, RentalManagementController, dll.)
│   │   │   └── SuperAdmin/   # SuperAdmin-specific (KostSubmissionController, AdminManagementController, CategoryController)
│   │   ├── Middleware/       # Custom middleware (RoleMiddleware, EnsureEmailVerified, dll.)
│   │   └── Requests/         # Form Request untuk validation (StoreKostRequest, CreateRentalRequest, dll.)
│   ├── Policies/             # Policy per model (KostPolicy, RentalPolicy, PaymentPolicy)
│   └── Providers/
├── routes/
│   ├── web.php               # rute utama — baseline: web routes, session-based (lihat §6.1)
│   ├── console.php           # Artisan commands (optional: make:superadmin, dll.)
│   └── api.php               # KOSONG untuk MVP (tidak ada API eksternal)
├── resources/
│   └── views/                # Blade templates, satu subfolder per context
│       ├── auth/             # Laravel Breeze views (login, register, verify-email)
│       ├── profile/
│       ├── marketplace/
│       ├── kosts/            # Kost detail view
│       ├── tenant/           # Tenant-specific views (rentals/, payments/, reviews/)
│       ├── admin/            # Admin-specific views (kosts/, room-types/, rentals/)
│       └── superadmin/       # SuperAdmin-specific views (submissions/, admins/, categories/)
├── database/
│   ├── migrations/
│   ├── seeders/              # SuperAdminSeeder (create first superadmin)
│   └── factories/
├── tests/
│   ├── Feature/              # Feature tests per FR (test acceptance criteria)
│   └── Unit/                 # Unit tests per Action class
├── docker/                   # Dockerfile & config produksi (terpisah dari setup Sail)
│   ├── php/
│   │   └── Dockerfile        # Production PHP image (multi-stage build)
│   ├── nginx/
│   │   └── default.conf      # Nginx config untuk production
│   └── supervisord.conf      # Supervisor config (queue worker + php-fpm)
├── storage/
│   ├── app/
│   │   ├── public/           # Public files (kost images, avatar, room images) — symlinked ke public/storage
│   │   └── private/          # Private files (rental documents, proof of payment) — serve via controller
│   └── logs/
├── docker-compose.yml        # digenerate oleh `php artisan sail:install` — untuk local/dev (Sail)
├── docker-compose.prod.yml   # Production compose file (terpisah dari Sail)
├── .env.example
├── .gitignore
├── composer.json
├── package.json              # NPM dependencies (Alpine.js, Leaflet.js, Tailwind CSS optional)
├── AGENTS.md                 # Instruksi untuk OpenCode/agent (this file is read by agent)
├── PRD.md                    # Product Requirements (Approved)
├── ARCHITECTURE.md           # Architecture & Design (this file)
├── TODO.md                   # Task breakdown & tracking
└── WORKFLOW.md               # Process & workflow

```

> Aturan penamaan file/folder mengikuti konvensi yang tercatat di `AGENTS.md` §Code Style — jangan duplikasi aturan itu di sini.
> `docker-compose.yml` di root dikelola oleh Laravel Sail — kustomisasi service lewat `sail:install` ulang atau edit manual dengan hati-hati. Untuk image produksi, gunakan `docker/` yang terpisah (lihat §9), jangan pakai `docker-compose.yml` Sail untuk deploy production.

---

## 12. Riwayat Perubahan (Changelog)

| Versi | Tanggal | Perubahan | Oleh |
|---|---|---|---|
| 0.2.1 | 2026-08-24 | COMP-003 implementation complete: 13 routes added (categories GET/PATCH, payment GET/PATCH, images CRUD 5 routes, document requirements CRUD 4 routes). Controller architecture: `KostController` handles categories + payment methods (editCategories, updateCategories, editPayment, updatePayment), `KostImageController` handles image management (index, store, destroy, setThumbnail, updateSortOrder), `DocumentRequirementController` handles document config (index, store, update, destroy) — vs. planned single `KostConfigurationController`. §6.1 routes table updated reflect actual implementation 13 endpoints. Eager loading documented: `KostController::show()` loads address, categories, kostImages, documentRequirements. Data models: Address (embedded 1:1), KostImage (1:N thumbnail flag + sort_order), Category (M:N junction, auto-slug name), KostDocumentRequirement (1:N, config-based types). ADR-013 facilities/rules JSON storage implemented Alpine.js dynamic list + textarea fallback. | OpenCode |
| 0.1.4 | 2026-08-18 | COMP-001: user unverified dapat memulai verifikasi dari tombol 'Verifikasi Email' di halaman profil. Catatan env: fix izin storage untuk user runtime (avatar upload 500). | OpenCode |
| 0.1.3 | 2026-08-18 | Tambah ADR-023 (On-Demand Email Verification): registrasi tanpa OTP → redirect `/marketplace` (stub interim TASK-086), OTP lazy saat buka `/verify-email` (throttle:5,1) atau diminta fitur via middleware `verified` + modal popup. Update COMP-001, §6.1 routes (`/verify-email`, `/marketplace` stub). Total 23 ADR. | OpenCode |
| 0.1.2 | 2026-08-18 | Tambah ADR-022 (Password Reset via OTP): OtpService multi-purpose (`password-reset`), alur 3 langkah, anti-enumeration, session guard, `password_reset_tokens` tidak dipakai. Update §6.1 routes auth (forgot/reset password), COMP-001 (FR-130). | OpenCode |
| 0.1.1 | 2026-08-13 | Environment setup & dependency sync: (1) Update §3 Tech Stack table: PHP 8.3+ → 8.5, tambah version numbers untuk Laravel (13.22.0), Sail (1.64.0), Breeze (2.4.2), (2) Update §3.1 Rujukan Dokumentasi: tambah Vite 8.2.1, Tailwind CSS 4.0.0, Alpine.js 3.14.x, PHPStan/Larastan, PHPUnit 12.5.12, Mailpit, Docker actual version (29.5.1/5.1.4), pin MySQL 8.0 & Redis 7-alpine di compose.yaml, (3) Tambah ADR-020 (PHP 8.5 rationale) & ADR-021 (PHPUnit vs Pest). Environment setup selesai: APP_KEY generated, DB credentials set, npm dependencies installed (Alpine.js, Leaflet.js), Laravel Breeze installed, PHPStan/Larastan installed, Mailpit service added, Sail containers running & verified, migrations executed, frontend assets built. | OpenCode |
| 0.1.0 | 2026-08-12 | Draft awal ARCHITECTURE.md dibuat. Konsolidasi dari DDS v1.0.0 dengan penyederhanaan: (1) Hapus 10 tabel facility/rule scheme → JSON (ADR-013), (2) Payment Midtrans → QRIS statis + verifikasi manual (ADR-014), (3) Gabung kost+room review + JSON images → 1 tabel (ADR-015), (4) Room status 2 values (`available`, `unavailable`) + real-time occupancy calculation (ADR-017), (5) Room multi-occupancy support dengan `max_occupants` (ADR-018), (6) Cancel rental dari Active diperbolehkan (ADR-019), (7) Min start_date = today + 4 hari (ADR-016). Total 9 COMP, 16 DM (dari 29 di DDS), ~70 Web Routes, 19 ADR (12 baseline + 7 penyederhanaan/constraints). Baseline: Laravel 13 modular monolith, session-based auth, web routes, Docker (Sail dev + custom image prod), MySQL 8, Redis, Leaflet, Alpine.js. | Lauhul Ridwan + OpenCode |

---
