---
name: docs-lookup
description: Mencari dan merangkum dokumentasi resmi (bukan blog/tutorial pihak ketiga) untuk tool/library/framework yang dipakai proyek — terutama Laravel 13 dan package Composer/NPM. Gunakan SEBELUM menulis kode yang menyentuh tool/library apa pun, sesuai AGENTS.md §Dokumentasi & Referensi Eksternal. Wajib cek ARCHITECTURE.md §3.1 apakah tool sudah terdaftar. Trigger: "cek dokumentasi", "laravel 13 docs", "cara pakai X di laravel", "api reference", "sebelum implementasi", "upgrade package", "breaking change".
license: MIT
compatibility: opencode
---

# docs-lookup — Pencarian & Rangkuman Dokumentasi Resmi

## Tujuan

Mencari, membaca, dan merangkum dokumentasi resmi dari tool/library/framework yang digunakan proyek (terutama Laravel 13 dan package Composer/NPM) sebelum agent menulis kode yang menyentuh tool tersebut. Memastikan implementasi menggunakan API/konvensi/best practice yang sesuai dengan versi spesifik yang dipakai proyek, bukan berdasarkan memori training data yang bisa saja outdated atau untuk versi berbeda.

## Dasar/Rujukan

- **`AGENTS.md` §Dokumentasi & Referensi Eksternal:** Agent WAJIB membaca dokumentasi resmi versi yang dipakai sebelum menulis kode yang menyentuh tool/library/framework apa pun — bukan mengandalkan hafalan atau tutorial pihak ketiga
- **`ARCHITECTURE.md` §3.1:** Tabel rujukan dokumentasi resmi untuk semua tool/library yang dipakai proyek
- **`SKILL.md` §2 docs-lookup:** Skill direkomendasikan sebelum implementasi TASK apa pun yang menyentuh tool/library baru atau API yang berpotensi berubah antar versi major

## Kapan Menggunakan Skill Ini (Trigger)

Skill ini **WAJIB** dipanggil sebelum:

| Situasi | Contoh Konkret |
|---|---|
| **Menulis kode yang menyentuh framework/library untuk pertama kali** | Implementasi autentikasi Laravel, form request validation, Eloquent relationship, queue/job, event/listener |
| **Menggunakan fitur yang berubah antar versi major** | Laravel 12 → 13 (breaking changes), PHP 8.2 → 8.3, MySQL 5.7 → 8.0 |
| **Menambahkan dependency baru** | Install package Composer/NPM baru, cek dokumentasi resmi untuk usage & compatibility |
| **Debugging error yang tidak jelas** | Error message yang kemungkinan disebabkan oleh misuse API atau versi incompatibility |
| **Implementasi NFR yang menyentuh konfigurasi framework/infra** | Security config (CORS, CSRF, rate limiting), performance tuning (cache, query optimization), deployment (Docker, Nginx) |

Skill ini **TIDAK** perlu dipanggil untuk:
- Kode business logic murni yang tidak menyentuh framework API (mis. pure function PHP)
- Fitur framework yang sangat basic dan stable lintas versi (mis. Blade `@if`, `route()` helper)

**Aturan praktis:** Jika agent tidak 100% yakin API/syntax/behavior sudah benar untuk versi spesifik yang dipakai proyek, panggil skill ini.

## Langkah-Langkah

### 1. Cek Apakah Tool Sudah Terdaftar di ARCHITECTURE.md §3.1

Baca `ARCHITECTURE.md` §3.1, cari baris untuk tool/library yang relevan dengan task saat ini.

**Jika tool sudah terdaftar:**
- Gunakan link dokumentasi resmi yang tercantum di kolom "Dokumentasi"
- Gunakan versi yang tercantum di kolom "Versi" sebagai target lookup

**Jika tool belum terdaftar:**
- Berhenti, eskalasi ke pengguna: "Tool/library '[nama]' belum terdaftar di ARCHITECTURE.md §3.1. Perlu konfirmasi versi dan link dokumentasi resmi sebelum bisa digunakan."
- Jika pengguna confirm tool & versi, tambahkan ke tabel `ARCHITECTURE.md` §3.1 terlebih dahulu (lihat Langkah 7), baru lanjut ke Langkah 2.

### 2. Tentukan Topik/Fitur Spesifik yang Perlu Dicari

Jangan baca dokumentasi secara umum/generik — fokus ke fitur/API spesifik yang dibutuhkan untuk task saat ini.

**Contoh fokus yang baik:**

| Task | Topik Dokumentasi yang Dicari |
|---|---|
| Implementasi registration (TASK-002) | Laravel Authentication - Registration, Form Request Validation, Hashing Password |
| Implementasi rental lifecycle state machine (TASK-045) | Laravel Events & Listeners, Eloquent Model Events, State Pattern |
| Setup Docker Sail untuk local dev | Laravel Sail - Installation, Configuration, Running Commands |
| Implementasi upload gambar kost | Laravel File Storage - Store Files, Public Disk, Validation |
| Optimasi query N+1 | Laravel Eloquent - Eager Loading, with(), load() |

**Contoh fokus yang buruk (terlalu luas):**
- "Laravel 13 documentation" (terlalu generik, ribuan halaman)
- "PHP best practices" (bukan dokumentasi resmi framework/library proyek)

### 3. Akses Dokumentasi Resmi (Gunakan WebFetch Tool)

**Prioritas sumber (urutan):**

1. **Dokumentasi resmi framework/library** (dari `ARCHITECTURE.md` §3.1)
   - Contoh: `https://laravel.com/docs/13.x/authentication`
   - Pastikan versi di URL match dengan versi proyek (13.x, bukan 12.x atau latest)

2. **Changelog/Upgrade Guide resmi** (jika mencari info breaking change atau fitur baru)
   - Contoh: `https://laravel.com/docs/13.x/upgrade`

3. **API Reference resmi** (jika butuh detail method signature/parameter)
   - Contoh: `https://laravel.com/api/13.x/Illuminate/Http/Request.html`

4. **Package repository README/docs** (jika package Composer/NPM)
   - Contoh: GitHub repo package, link di Packagist/npmjs.com

**Jangan gunakan:**
- Blog/tutorial pihak ketiga (Medium, Dev.to, personal blog) — bisa outdated atau salah
- Stack Overflow — boleh sebagai referensi tambahan setelah baca docs resmi, tapi bukan sumber utama untuk API
- Dokumentasi versi lain (mis. Laravel 12 docs untuk proyek Laravel 13) — bisa ada breaking change

**Cara menggunakan WebFetch:**

```
webfetch(url="https://laravel.com/docs/13.x/authentication", format="markdown")
```

Simpan hasil fetch untuk analisis di Langkah 4.

### 4. Ekstrak Informasi Relevan & Rangkum

Dari hasil fetch di Langkah 3, ekstrak informasi yang langsung relevan dengan task:

**Yang perlu dicatat:**
- **Sintaks/API yang benar** (method name, parameter, return type)
- **Contoh kode resmi** (jika ada di dokumentasi)
- **Best practice/recommendation** dari dokumentasi
- **Breaking change atau deprecation warning** (jika ada)
- **Dependency/prerequisite** (mis. "fitur ini butuh config X di .env", "harus install package Y dulu")

**Format rangkuman:**

```markdown
## Docs Lookup: [Topik]

**Sumber:** [URL dokumentasi resmi]
**Versi:** [versi tool/library]
**Tanggal lookup:** [YYYY-MM-DD]

### Key Findings

1. **[Poin 1]**
   - [Detail/sintaks/contoh]

2. **[Poin 2]**
   - [Detail/sintaks/contoh]

### Relevant Code Example (dari docs)

```php
// Contoh dari dokumentasi resmi
[paste kode contoh jika ada]
```

### Notes/Warnings

- [Breaking change/deprecation/gotcha yang perlu diperhatikan]

### Application to Current Task

[1-2 kalimat: bagaimana findings ini diterapkan ke TASK-xxx yang sedang dikerjakan]
```

### 5. Verifikasi Compatibility dengan Proyek

Cross-check findings dengan environment proyek:

**PHP version:**
- Cek `ARCHITECTURE.md` §3 atau `composer.json` untuk PHP version requirement
- Jika dokumentasi menyebutkan "fitur ini butuh PHP 8.3+", pastikan proyek pakai PHP ≥8.3

**Laravel version:**
- Pastikan dokumentasi yang dibaca untuk Laravel 13.x (bukan 12.x atau latest)
- Cek apakah ada breaking change dari versi sebelumnya yang perlu migration

**Package compatibility:**
- Jika task butuh package baru, cek `composer.json` atau dokumentasi package untuk compatibility dengan Laravel 13 & PHP version proyek

**Jika ada incompatibility:** Eskalasi ke pengguna sebelum lanjut implementasi.

### 6. Catat Findings sebagai Komentar/Notes di Task Implementation

Saat menulis kode implementasi, tambahkan komentar referensi ke dokumentasi (terutama untuk API yang tidak obvious):

```php
/**
 * Register new tenant user.
 * 
 * Flow sesuai Laravel 13 Authentication docs:
 * https://laravel.com/docs/13.x/authentication#registration
 * 
 * @param RegisterRequest $request
 * @return RedirectResponse
 */
public function register(RegisterRequest $request): RedirectResponse
{
    // Hash password automatically (Laravel Hashing docs)
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => $request->password, // auto-hashed by User model mutator
        'role' => 'tenant',
    ]);
    
    // Auto-login after registration (Laravel Auth docs)
    Auth::login($user);
    
    return redirect()->route('dashboard');
}
```

Ini membantu developer lain (atau agent di masa depan) memahami dari mana API/pattern ini berasal.

### 7. (Jika Tool Baru) Tambahkan ke ARCHITECTURE.md §3.1

Jika lookup ini untuk tool/library yang belum terdaftar di `ARCHITECTURE.md` §3.1, tambahkan baris baru:

```markdown
| [Nama Tool/Library] | [Link dokumentasi resmi] | [Versi] | ADR-XXX |
```

**Contoh:**
```markdown
| Laravel Sanctum | https://laravel.com/docs/13.x/sanctum | 4.x | ADR-020 |
| Leaflet | https://leafletjs.com/reference.html | 1.9.x | ADR-007 |
```

**Catatan:** Penambahan tool baru juga wajib punya ADR (lihat skill `adr-writer`) — jangan tambahkan dependency tanpa justifikasi.

## Kondisi Berhenti / Eskalasi

- **Dokumentasi resmi tidak bisa diakses (404, paywall, atau site down)** → Berhenti, laporkan ke pengguna: "Dokumentasi resmi [tool] di [URL] tidak bisa diakses. Alternatif: 1) Cari dokumentasi alternatif (mis. GitHub repo README), 2) Tunda implementasi hingga dokumentasi bisa diakses." Jangan lanjut implementasi berdasarkan memori/tebakan.
- **Dokumentasi tidak punya info untuk versi spesifik yang dipakai proyek** (mis. proyek pakai Laravel 13, tapi dokumentasi hanya ada untuk Laravel 12 & 14) → Eskalasi ke pengguna: "Dokumentasi resmi Laravel 13 tidak ditemukan. Apakah versi di `ARCHITECTURE.md` §3 sudah benar? Atau perlu upgrade/downgrade versi?"
- **Dokumentasi menyebutkan fitur deprecated atau akan dihapus di versi mendatang** → Eskalasi ke pengguna: "Fitur [X] yang dibutuhkan untuk TASK-xxx deprecated di Laravel 13 dan akan dihapus di Laravel 14. Alternatif: [Y]. Apakah perlu ubah pendekatan atau tetap pakai fitur deprecated untuk MVP?" Jangan diam-diam pakai fitur deprecated tanpa inform pengguna.
- **Dokumentasi berisi multiple approach berbeda untuk satu masalah, tidak ada rekomendasi jelas** → Eskalasi ke pengguna dengan opsi: "Laravel docs menyebutkan 3 cara untuk [task]: [A], [B], [C]. Mana yang lebih sesuai dengan context proyek ini?" Jangan memilih sembarangan tanpa konsultasi, karena pilihan ini bisa jadi ADR.
- **Lookup untuk package pihak ketiga yang tidak ada dokumentasi resmi memadai (hanya README singkat 5 baris)** → Berhenti, eskalasi ke pengguna: "Package [X] tidak punya dokumentasi resmi yang memadai. Apakah ada alternatif lain yang lebih well-documented?" Hindari pakai package tanpa dokumentasi untuk fitur kritikal.

## Best Practices

### Bookmark Dokumentasi yang Sering Dipakai

Untuk proyek Laravel 13, halaman docs yang paling sering direferensikan:

| Kategori | URL |
|---|---|
| Authentication | https://laravel.com/docs/13.x/authentication |
| Authorization (Gate/Policy) | https://laravel.com/docs/13.x/authorization |
| Validation | https://laravel.com/docs/13.x/validation |
| Eloquent ORM | https://laravel.com/docs/13.x/eloquent |
| Eloquent Relationships | https://laravel.com/docs/13.x/eloquent-relationships |
| Database Migrations | https://laravel.com/docs/13.x/migrations |
| Routing | https://laravel.com/docs/13.x/routing |
| Controllers | https://laravel.com/docs/13.x/controllers |
| Blade Templates | https://laravel.com/docs/13.x/blade |
| File Storage | https://laravel.com/docs/13.x/filesystem |
| Queues | https://laravel.com/docs/13.x/queues |
| Events | https://laravel.com/docs/13.x/events |
| Testing | https://laravel.com/docs/13.x/testing |

### Gunakan Search di Dokumentasi

Dokumentasi Laravel punya search bar — gunakan untuk cepat cari topik spesifik (mis. search "session" untuk cari semua referensi ke session handling).

### Baca "Upgrade Guide" Saat Naik Versi Major

Jika proyek di-upgrade (mis. Laravel 12 → 13), wajib baca upgrade guide untuk identifikasi breaking change:

```
https://laravel.com/docs/13.x/upgrade
```

Catat setiap breaking change yang affect kode yang sudah ada, buat task untuk migrasi.

### Cross-Reference dengan API Docs untuk Detail Method

Jika dokumentasi guide kurang detail (mis. tidak sebutkan semua parameter optional), buka API reference:

```
https://laravel.com/api/13.x/Illuminate/Http/Request.html
```

API docs punya signature lengkap, termasuk type hint, default value, dan PHPDoc.

## Contoh Workflow Lengkap

**Scenario:** Agent sedang mengerjakan TASK-015 (Implementasi Registration), butuh info Laravel Authentication.

### Langkah 1: Cek ARCHITECTURE.md §3.1

```
Agent: Baca ARCHITECTURE.md §3.1
Found: | Laravel | https://laravel.com/docs/13.x | 13.x | ADR-001 |
```

### Langkah 2: Tentukan Topik

```
Agent: Task-015 butuh:
- User registration flow
- Form request validation
- Password hashing
- Auto-login after registration

Topik lookup: Laravel Authentication - Registration
```

### Langkah 3: Fetch Dokumentasi

```
Agent: webfetch("https://laravel.com/docs/13.x/authentication")

Result: [isi dokumentasi Laravel Authentication]
```

### Langkah 4: Ekstrak & Rangkum

```markdown
## Docs Lookup: Laravel 13 Authentication - Registration

**Sumber:** https://laravel.com/docs/13.x/authentication
**Versi:** Laravel 13.x
**Tanggal lookup:** 2026-08-13

### Key Findings

1. **Laravel tidak punya built-in registration controller di Laravel 13** (berbeda dari versi lama yang pakai Laravel Breeze/Jetstream scaffolding)
   - Perlu buat manual: controller, form request, routes, views
   - Best practice: validasi via Form Request class (bukan di controller)

2. **Password auto-hashing**
   - Laravel 13 recommend pakai `Hashed` cast di model:
     ```php
     protected function casts(): array
     {
         return ['password' => 'hashed'];
     }
     ```
   - Jika pakai cast ini, tidak perlu manual `Hash::make()` di controller

3. **Auto-login after registration**
   - Gunakan `Auth::login($user)` setelah create user
   - Atau gunakan `Auth::attempt(['email' => ..., 'password' => ...])` jika ingin validasi password lagi

### Relevant Code Example (dari docs)

```php
// User model dengan Hashed cast (Laravel 13)
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // auto hash/verify
        ];
    }
}
```

### Notes/Warnings

- Laravel 13 menghilangkan `Laravel\Fortify` dari dependency default (breaking change dari Laravel 12). Jika butuh two-factor auth atau email verification, harus install manual.
- Session config default di `config/session.php`: lifetime 120 menit, secure flag auto true jika HTTPS.

### Application to Current Task

TASK-015 akan:
1. Buat `RegisterRequest` untuk validasi (email unique, password min 8)
2. Tambahkan `'password' => 'hashed'` cast di User model (no manual Hash::make)
3. Controller: `User::create($request->validated())` → `Auth::login($user)` → redirect
4. Test: assert user created, assert authenticated, assert password di-hash di database
```

### Langkah 5: Verifikasi Compatibility

```
Agent: Cek composer.json proyek
PHP requirement: ^8.2
Laravel: ^13.0

Compatibility: ✅ All features available in Laravel 13 + PHP 8.2
```

### Langkah 6: Implementasi dengan Referensi Docs

```php
// app/Http/Controllers/Auth/AuthController.php

/**
 * Register new tenant user.
 * 
 * Implementation based on Laravel 13 Authentication docs:
 * https://laravel.com/docs/13.x/authentication#registration
 */
public function register(RegisterRequest $request): RedirectResponse
{
    // Create user — password auto-hashed by model cast
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => $request->password,
        'role' => 'tenant',
    ]);
    
    // Auto-login (Laravel Auth docs)
    Auth::login($user);
    
    return redirect()->route('dashboard')
        ->with('success', 'Registration successful!');
}
```

## Improvement Notes (vs Versi Sebelumnya yang Hilang)

- Tambah **Langkah 1 (Cek ARCHITECTURE.md §3.1 dulu)** — memastikan tool sudah terdaftar sebelum lookup, menghindari penggunaan tool yang belum di-approve
- Tambah **Langkah 7 (Tambahkan tool baru ke ARCHITECTURE.md)** — menutup loop governance untuk dependency baru
- Tambah **tabel bookmark dokumentasi Laravel yang sering dipakai** — efficiency untuk lookup berulang
- Tambah **contoh workflow lengkap end-to-end** (TASK-015 Registration) — referensi praktis cara pakai skill ini
- Tambah **kondisi eskalasi untuk fitur deprecated** — mencegah agent diam-diam pakai API yang akan dihapus di versi mendatang tanpa inform pengguna
- Klarifikasi **prioritas sumber dokumentasi** (resmi → changelog → API reference → package README) — mengurangi referensi ke sumber yang tidak reliable
