---
name: test-writer
description: Menulis test yang secara eksplisit memvalidasi acceptance criteria dari FR-xxx atau US-xxx tertentu (bukan test generik). Gunakan setiap implementasi TASK-xxx di fase Build, sebelum status diubah ke Done. Test harus mencakup happy path, alternative path, exception/edge case, dan authorization boundary sesuai acceptance criteria FR. Trigger: "write test for FR-xxx", "test acceptance criteria", "sebelum done", "test coverage", "feature test", "unit test untuk TASK-xxx".
license: MIT
compatibility: opencode
---

# test-writer — Penulisan Test Berbasis Acceptance Criteria

## Tujuan

Menulis test (unit, feature, integration) yang secara eksplisit memvalidasi acceptance criteria dari `FR-xxx` atau `US-xxx`, memastikan implementasi memenuhi requirement dan tidak regress di masa depan. Test harus mencakup tidak hanya happy path, tapi juga alternative flow, error handling, edge case, dan authorization boundary sesuai yang didefinisikan di `PRD.md`.

## Dasar/Rujukan

- **`AGENTS.md` §Definition of Done:** Sebuah TASK dianggap selesai jika seluruh perintah test & lint lulus, tidak ada regresi
- **`AGENTS.md` §Perintah Test:** Format perintah test untuk Laravel (`sail artisan test`, Pest/PHPUnit, coverage, PHPStan, Pint)
- **`PRD.md` §4-§5:** Acceptance criteria setiap FR-xxx/NFR-xxx adalah sumber utama untuk test case
- **`ARCHITECTURE.md` §11:** Struktur folder test (`tests/Unit/`, `tests/Feature/`) mengikuti konvensi Laravel
- **`SKILL.md` §2 test-writer:** Skill direkomendasikan untuk setiap implementasi TASK sebelum Done

## Prinsip Dasar Test

1. **Test harus trace back ke requirement** — setiap test file/method wajib punya komentar header yang menyebutkan `FR-xxx`/`US-xxx` mana yang divalidasi. Jangan buat test generik tanpa rujukan jelas ke requirement.
2. **Test adalah dokumentasi eksekutabel** — developer lain harus bisa membaca test dan paham apa yang diharapkan dari fitur tanpa baca kode implementasi.
3. **Test harus deterministik** — hasil sama setiap dijalankan, tidak bergantung pada waktu/urutan eksekusi/data eksternal (gunakan factory, seeder, time mocking).
4. **Test harus cepat** — feature test ≤5 detik, unit test ≤1 detik per test. Jika lebih lambat, kemungkinan test menyentuh dependency eksternal yang harus di-mock.
5. **Test coverage ≥ acceptance criteria** — setiap acceptance criteria di FR-xxx harus punya minimal 1 test yang mengecek kriteria itu secara eksplisit.

## Langkah-Langkah

### 1. Identifikasi FR/US yang Ditest

Dari `TASK-xxx` yang sedang dikerjakan, identifikasi:
- `FR-xxx`/`NFR-xxx` mana yang dipenuhi task ini (lihat field "Grounding" di TODO.md)
- Baca `PRD.md` untuk FR tersebut, catat **acceptance criteria**-nya (biasanya dalam bentuk checklist atau bullet point)

**Contoh:**

```
TASK-015: Implementasi Registration (Tenant)
Grounding: FR-001 (Registrasi tenant via email/password)

Baca PRD.md FR-001:
Acceptance Criteria:
- [ ] Email harus unique (tidak boleh duplikat)
- [ ] Email harus format valid
- [ ] Password minimal 8 karakter
- [ ] Setelah registrasi sukses, user otomatis login dan redirect ke dashboard
- [ ] Jika email sudah terdaftar, tampilkan error message
```

### 2. Tentukan Test Level (Unit vs Feature vs Integration)

**Unit Test:**
- Untuk logic yang pure/isolated (mis. Service class method, Policy method, Helper function)
- Tidak butuh database/HTTP/external dependency
- Mock semua dependency
- Lokasi: `tests/Unit/Domain/<Komponen>/`

**Feature Test:**
- Untuk flow end-to-end dari request HTTP sampai response
- Butuh database (gunakan RefreshDatabase trait untuk test isolation)
- Tidak mock controller/model (test real integration)
- Lokasi: `tests/Feature/Domain/<Komponen>/`

**Integration Test (opsional, untuk kasus kompleks):**
- Untuk flow yang melibatkan multiple component
- Mis. Rental creation yang trigger payment + email notification
- Lokasi: `tests/Integration/`

**Aturan praktis:**
- Jika TASK membuat controller + form request + routes → **Feature Test** (test HTTP request/response)
- Jika TASK membuat service/business logic class → **Unit Test** untuk method internal + **Feature Test** untuk HTTP endpoint yang pakai service itu
- Jika TASK membuat Policy → **Unit Test** untuk policy logic

### 3. Tulis Test File dengan Struktur Baku

**Template untuk Feature Test (Laravel Pest):**

```php
<?php

/**
 * Feature Test: [Nama Fitur]
 * 
 * Validasi acceptance criteria dari:
 * - FR-XXX: [Judul FR]
 * - US-YYY: [Judul US, jika ada]
 * 
 * Coverage:
 * - Happy path: [deskripsi singkat]
 * - Alternative path: [deskripsi singkat]
 * - Error cases: [deskripsi singkat]
 * - Authorization: [deskripsi singkat]
 */

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('FR-XXX: [Nama Fitur]', function () {
    
    // Happy Path Tests
    
    test('tenant dapat registrasi dengan email dan password valid', function () {
        // Arrange
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
        
        // Act
        $response = $this->post('/register', $data);
        
        // Assert
        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'role' => 'tenant',
        ]);
        $this->assertAuthenticatedAs(User::where('email', 'john@example.com')->first());
    });
    
    // Validation Tests (Error Cases)
    
    test('registrasi gagal jika email sudah terdaftar', function () {
        // Arrange
        User::factory()->create(['email' => 'existing@example.com']);
        
        $data = [
            'name' => 'Jane Doe',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
        
        // Act
        $response = $this->post('/register', $data);
        
        // Assert
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    });
    
    test('registrasi gagal jika email tidak valid', function () {
        $data = [
            'name' => 'John Doe',
            'email' => 'not-an-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
        
        $response = $this->post('/register', $data);
        
        $response->assertSessionHasErrors('email');
    });
    
    test('registrasi gagal jika password kurang dari 8 karakter', function () {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'pass',
            'password_confirmation' => 'pass',
        ];
        
        $response = $this->post('/register', $data);
        
        $response->assertSessionHasErrors('password');
    });
    
    test('registrasi gagal jika password confirmation tidak match', function () {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different',
        ];
        
        $response = $this->post('/register', $data);
        
        $response->assertSessionHasErrors('password');
    });
    
    // Edge Cases
    
    test('email disimpan dalam lowercase', function () {
        $data = [
            'name' => 'John Doe',
            'email' => 'JOHN@EXAMPLE.COM',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
        
        $this->post('/register', $data);
        
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com', // lowercase
        ]);
    });
    
});
```

**Template untuk Unit Test (Laravel Pest):**

```php
<?php

/**
 * Unit Test: [Nama Class/Method]
 * 
 * Validasi logic dari:
 * - FR-XXX: [Judul FR]
 * 
 * Tested methods:
 * - [ClassName::methodName]
 */

use App\Domain\Identity\Services\UserService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('UserService', function () {
    
    test('createTenant membuat user baru dengan role tenant', function () {
        // Arrange
        $service = new UserService();
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];
        
        // Act
        $user = $service->createTenant($data);
        
        // Assert
        expect($user)->toBeInstanceOf(User::class)
            ->and($user->email)->toBe('john@example.com')
            ->and($user->role)->toBe('tenant')
            ->and(Hash::check('password123', $user->password))->toBeTrue();
    });
    
    test('createTenant throw exception jika email sudah ada', function () {
        User::factory()->create(['email' => 'existing@example.com']);
        
        $service = new UserService();
        $data = [
            'name' => 'Jane Doe',
            'email' => 'existing@example.com',
            'password' => 'password123',
        ];
        
        expect(fn() => $service->createTenant($data))
            ->toThrow(\Illuminate\Database\QueryException::class);
    });
    
});
```

### 4. Map Test Cases ke Acceptance Criteria (Checklist Coverage)

Setelah menulis test, lakukan reverse-check:

```
FOR EACH acceptance-criterion IN FR-XXX:
    Apakah ada test yang eksplisit mengecek criterion ini?
    IF NOT:
        Tambahkan test baru untuk criterion tersebut
```

**Contoh mapping (untuk FR-001 Registration):**

| Acceptance Criteria | Test Method |
|---|---|
| Email harus unique | `test('registrasi gagal jika email sudah terdaftar')` |
| Email harus format valid | `test('registrasi gagal jika email tidak valid')` |
| Password minimal 8 karakter | `test('registrasi gagal jika password kurang dari 8 karakter')` |
| Setelah registrasi sukses, user otomatis login | `test('tenant dapat registrasi dengan email dan password valid')` → assert `assertAuthenticatedAs()` |
| Jika email sudah terdaftar, tampilkan error | `test('registrasi gagal jika email sudah terdaftar')` → assert `assertSessionHasErrors()` |

**Jika ada acceptance criteria yang belum tercakup:** tambahkan test baru.

### 5. Tulis Test untuk Authorization (Jika Relevan)

Jika FR menyebutkan authorization constraint (mis. "hanya admin yang bisa", "user hanya bisa edit profil sendiri"), wajib tambahkan test untuk:

- User tanpa permission akses endpoint → 403 Forbidden
- User dengan permission berbeda akses endpoint → 403 Forbidden
- User dengan permission tepat akses endpoint → 200 OK

**Contoh:**

```php
test('tenant tidak bisa akses admin dashboard', function () {
    $tenant = User::factory()->create(['role' => 'tenant']);
    
    $response = $this->actingAs($tenant)->get('/admin/dashboard');
    
    $response->assertForbidden();
});

test('admin bisa akses admin dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    
    $response = $this->actingAs($admin)->get('/admin/dashboard');
    
    $response->assertOk();
});
```

### 6. Jalankan Test & Verifikasi

```bash
# Jalankan semua test
./vendor/bin/sail artisan test

# Jalankan test spesifik file
./vendor/bin/sail artisan test --filter=RegistrationTest

# Jalankan dengan coverage
./vendor/bin/sail artisan test --coverage

# Jalankan dengan parallel (jika ada banyak test)
./vendor/bin/sail artisan test --parallel
```

**Kriteria lulus:**
- Semua test hijau (passed)
- Coverage untuk file yang di-test ≥80% (line coverage)
- Tidak ada test yang di-skip/incomplete tanpa alasan jelas

### 7. Update Acceptance Criteria di TODO.md

Setelah test ditulis & lulus, update `TASK-xxx` di `TODO.md`:

```markdown
**Acceptance Criteria:**
- [x] Email harus unique → test: RegistrationTest::registrasi_gagal_jika_email_sudah_terdaftar
- [x] Email harus format valid → test: RegistrationTest::registrasi_gagal_jika_email_tidak_valid
- [x] Password minimal 8 karakter → test: RegistrationTest::registrasi_gagal_jika_password_kurang_dari_8_karakter
- [x] Test suite lulus (unit + feature) ✅
- [x] Lint & typecheck lulus (`sail pint`) ✅
```

Ini memberi traceability dari acceptance criteria → test method yang memvalidasinya.

## Kondisi Berhenti / Eskalasi

- **Acceptance criteria di FR-xxx ambigu atau tidak terukur** (mis. "sistem harus cepat", "UI harus menarik") → Berhenti, eskalasi ke pengguna: "Acceptance criteria '[criterion]' tidak bisa di-test secara otomatis. Perlu definisi terukur (mis. 'response time <2 detik', 'WCAG AA compliant') atau test manual." Jangan menebak interpretasi sendiri.
- **FR-xxx tidak punya acceptance criteria sama sekali** → Berhenti, eskalasi ke pengguna: "FR-xxx tidak punya acceptance criteria. Test tidak bisa ditulis tanpa kriteria yang jelas. Request revisi PRD.md atau konfirmasi acceptance criteria dengan pengguna."
- **Test butuh data eksternal yang tidak bisa di-mock (mis. real payment gateway, third-party API live)** → Eskalasi ke pengguna: "Test untuk FR-xxx butuh integrasi [service eksternal]. Apakah ada mock/sandbox environment? Atau test ini harus manual/E2E test terpisah?"
- **Test butuh setup kompleks (mis. multi-tenant database, Docker compose khusus)** → Eskalasi ke pengguna: "Test untuk FR-xxx butuh setup [deskripsi]. Apakah perlu environment khusus atau test ini di-skip untuk CI dan dijalankan manual?"
- **Test gagal karena bug di implementasi, bukan di test** → Jangan ubah test agar lulus jika implementasi yang salah. Perbaiki implementasi dulu, baru re-run test. Jika tidak yakin mana yang salah (test atau implementasi), eskalasi ke pengguna untuk review.

## Best Practices

### Naming Convention

**Test method name harus deskriptif (tidak singkatan):**

**Baik:**
- `test('tenant dapat registrasi dengan email dan password valid')`
- `test('admin tidak bisa delete kost milik admin lain')`
- `test('rental otomatis berubah status dari Pending ke Active saat start_date tercapai')`

**Buruk (hindari):**
- `test('registration works')` (terlalu generik)
- `test('test_case_1')` (tidak deskriptif)
- `test('canRegister')` (camelCase tidak konsisten dengan konvensi Laravel test)

### Arrange-Act-Assert Pattern

Gunakan pola AAA untuk readability:

```php
test('tenant dapat update profil sendiri', function () {
    // Arrange — setup data & state
    $tenant = User::factory()->create(['role' => 'tenant', 'name' => 'Old Name']);
    
    // Act — jalankan action yang ditest
    $response = $this->actingAs($tenant)->put('/profile', [
        'name' => 'New Name',
        'email' => $tenant->email,
    ]);
    
    // Assert — verifikasi outcome
    $response->assertRedirect('/profile');
    $this->assertDatabaseHas('users', [
        'id' => $tenant->id,
        'name' => 'New Name',
    ]);
});
```

### Factory & Seeder untuk Test Data

Gunakan factory (bukan hardcode data) agar test lebih maintainable:

**Buruk:**
```php
$user = User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => Hash::make('password'),
    'role' => 'tenant',
    'email_verified_at' => now(),
]);
```

**Baik:**
```php
$user = User::factory()->create(['role' => 'tenant']);
```

Factory bisa di-customize:
```php
$admin = User::factory()->admin()->create(); // jika ada state 'admin' di factory
$verified = User::factory()->verified()->create();
```

### Time Mocking untuk Test yang Time-Dependent

Jika FR menyentuh waktu (mis. rental start_date, expired token), gunakan Carbon time travel:

```php
use Illuminate\Support\Carbon;

test('rental berubah status ke Active saat start_date tercapai', function () {
    // Arrange
    Carbon::setTestNow('2026-08-01 00:00:00');
    $rental = Rental::factory()->create([
        'status' => 'Confirmed',
        'start_date' => '2026-08-10',
    ]);
    
    // Act — maju waktu ke start_date
    Carbon::setTestNow('2026-08-10 08:00:00');
    Artisan::call('rental:update-status'); // command yang cek & update status
    
    // Assert
    $rental->refresh();
    expect($rental->status)->toBe('Active');
});
```

### Isolation — Test Tidak Boleh Bergantung Satu Sama Lain

**Buruk (stateful test):**
```php
test('create user', function () {
    User::create(['email' => 'test@example.com', ...]); // global state
});

test('find user', function () {
    $user = User::where('email', 'test@example.com')->first(); // bergantung test sebelumnya
    expect($user)->not->toBeNull();
});
```

**Baik (isolated test):**
```php
test('find user', function () {
    $user = User::factory()->create(['email' => 'test@example.com']); // setup sendiri
    
    $found = User::where('email', 'test@example.com')->first();
    expect($found->id)->toBe($user->id);
});
```

Gunakan `RefreshDatabase` trait untuk reset database setelah setiap test.

## Improvement Notes (vs Versi Sebelumnya yang Hilang)

- Tambah **Langkah 4 (Map Test Cases ke Acceptance Criteria)** dengan tabel coverage — memastikan setiap acceptance criteria punya test eksplisit
- Tambah **Langkah 7 (Update Acceptance Criteria di TODO.md)** dengan link ke test method — traceability test ↔ requirement
- Tambah **section Best Practices** lengkap (naming, AAA pattern, factory, time mocking, isolation) — mengurangi bad practice test yang tidak maintainable
- Tambah **template test lengkap** (Pest syntax) dengan contoh happy path, validation, edge case, authorization — referensi format yang konsisten
- Klarifikasi **kondisi eskalasi untuk acceptance criteria ambigu/tidak ada** — mencegah agent menulis test untuk requirement yang tidak jelas
