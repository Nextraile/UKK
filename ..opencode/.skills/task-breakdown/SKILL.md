---
name: task-breakdown
description: Memecah satu FR-xxx atau COMP-xxx menjadi beberapa TASK-xxx berukuran wajar (≤ 1 hari kerja) dengan dependency antar-task. Gunakan saat fase Planning (WORKFLOW.md §2 Fase 3) untuk mengisi TODO.md. Setiap TASK harus merujuk FR/NFR dan COMP yang relevan. Trigger: "task breakdown", "pecah FR jadi task", "isi TODO.md", "planning fase 3", "buat task untuk FR-xxx".
license: MIT
compatibility: opencode
---

# task-breakdown — Memecah Requirement/Komponen Menjadi Task Implementasi

## Tujuan

Mengurai satu `FR-xxx` atau `COMP-xxx` menjadi unit kerja (`TASK-xxx`) yang konkret, terukur, dan dapat diselesaikan dalam ≤ 1 hari kerja oleh satu developer, lengkap dengan dependency antar-task, acceptance criteria yang jelas, dan grounding eksplisit ke requirement asal (`FR-xxx`/`NFR-xxx`) dan komponen teknis (`COMP-xxx`).

## Dasar/Rujukan

- **`WORKFLOW.md` §2 Fase 3 — Planning:** Task breakdown adalah deliverable utama fase ini sebelum masuk ke Build
- **`AGENTS.md` §Definition of Done:** Setiap TASK harus merujuk FR/NFR dan COMP yang relevan
- **`ARCHITECTURE.md` §4:** Setiap COMP-xxx punya boundary dan interface yang jelas — task harus respect boundary ini
- **`SKILL.md` §2 task-breakdown:** Skill direkomendasikan sebagai prosedur berulang untuk setiap sprint/iteration
- **`PRD.md` acceptance criteria pada setiap FR-xxx:** Sumber utama untuk menentukan scope task

## Langkah-Langkah

### 1. Identifikasi Input (FR atau COMP yang akan dipecah)

**Skenario A: Breakdown dari FR-xxx** (top-down, dari requirement)
- Baca `PRD.md` untuk FR-xxx target, catat:
  - Deskripsi lengkap
  - Acceptance criteria
  - Related `US-xxx` (jika ada)
- Cek `ARCHITECTURE.md` §4: COMP-xxx mana saja yang memenuhi FR ini
- Jika FR belum dipetakan ke COMP → **STOP**, eskalasi ke pengguna (kemungkinan ARCHITECTURE.md belum selesai atau perlu revisi)

**Skenario B: Breakdown dari COMP-xxx** (bottom-up, dari komponen teknis)
- Baca `ARCHITECTURE.md` §4 untuk COMP-xxx target, catat:
  - Tanggung jawab (Responsibilities)
  - Interface (API/routes/method utama)
  - Dependencies (COMP lain yang dipakai)
  - Data model (DM-xxx yang terlibat)
  - FR-xxx mana saja yang dipenuhi COMP ini
- Baca `ARCHITECTURE.md` §5 untuk semua DM-xxx yang disebutkan COMP → pahami struktur tabel/field

### 2. Tentukan Urutan Pengerjaan Logis (Sequencing)

Untuk COMP-xxx, urutan umum yang disarankan (sesuaikan dengan konteks):

1. **Data layer** (migration, model, factory, seeder)
2. **Core logic/Service** (business logic, validation, state transition)
3. **Interface layer** (controller, form request, routes)
4. **View layer** (Blade templates, Livewire components jika pakai)
5. **Policy/Gate** (authorization)
6. **Test** (unit, feature, integration — untuk semua layer di atas)

Dependency antar-COMP: jika COMP-xxx bergantung pada COMP-yyy, pastikan task untuk COMP-yyy selesai lebih dulu.

### 3. Dekomposisi Menjadi TASK-xxx (Granularity ≤ 1 hari kerja)

Untuk setiap layer/unit kerja dari Langkah 2, buat satu atau lebih `TASK-xxx` dengan format:

```markdown
### TASK-XXX: <Judul singkat, action-oriented>

**Status:** `Pending` / `In Progress` / `Done` / `Blocked`

**Grounding:**
- Requirement: FR-YYY, NFR-ZZZ (dari PRD.md)
- Component: COMP-AAA (dari ARCHITECTURE.md)
- Data Model: DM-BBB (jika relevan)

**Deskripsi:**
<1-3 kalimat menjelaskan apa yang dikerjakan, kenapa perlu, dan output yang diharapkan>

**Acceptance Criteria:**
- [ ] <Kriteria 1 — harus terverifikasi (bisa ditest atau dicek manual)>
- [ ] <Kriteria 2>
- [ ] <Kriteria 3>
- [ ] Test suite lulus (unit + feature yang relevan)
- [ ] Lint & typecheck lulus (`sail pint`, PHPStan jika ada)

**Dependencies:**
- TASK-XXX (harus selesai sebelum task ini bisa dimulai)

**Estimasi:** <0.5 hari / 1 hari — jika >1 hari, pecah lagi menjadi subtask>

**Notes/Risks:** <Opsional: catatan teknis, edge case, atau risiko yang perlu diperhatikan>
```

**Aturan granularity:**
- Task yang hanya membuat 1 migration sederhana → 0.5 hari
- Task yang membuat 1 controller + form request + routes → 1 hari
- Task yang membuat service logic kompleks (mis. rental lifecycle state machine) → 1 hari
- Jika satu unit kerja estimasi >1 hari → pecah menjadi subtask (mis. "Rental service: create flow" dan "Rental service: state transition")

### 4. Identifikasi Dependency Antar-Task

Untuk setiap TASK-xxx, tanyakan:
- Apakah task ini butuh database schema yang belum ada? → dependency ke TASK migration
- Apakah task ini butuh model/relasi Eloquent? → dependency ke TASK model
- Apakah task ini butuh service/logic dari COMP lain? → dependency ke TASK yang implementasi COMP tersebut
- Apakah task ini butuh authorization? → dependency ke TASK policy/gate

Tandai dependency dengan jelas di field **Dependencies** pada setiap TASK.

### 5. Map ke Acceptance Criteria FR-xxx (Verifikasi Coverage)

Setelah semua TASK dibuat, lakukan reverse-check:

```
FOR EACH acceptance-criterion IN FR-xxx:
  Apakah ada minimal 1 TASK-xxx yang eksplisit memvalidasi criterion ini?
  IF NOT:
    CREATE new TASK untuk menutup gap coverage
```

Ini memastikan tidak ada acceptance criteria yang "terlupakan" dalam breakdown.

### 6. Tulis ke TODO.md

Tambahkan semua TASK-xxx ke `TODO.md` di section yang sesuai (mis. section per COMP, atau per sprint/milestone). Urutkan berdasarkan dependency (task yang tidak punya dependency di atas, task yang punya dependency di bawah).

Format TODO.md (sesuaikan dengan struktur yang ada):

```markdown
## <COMP-XXX: Nama Komponen> atau <Sprint X / Milestone Y>

### TASK-001: ...
<isi sesuai template Langkah 3>

### TASK-002: ...
<isi sesuai template Langkah 3>

...
```

### 7. Validasi dengan Checklist

Sebelum menandai task breakdown selesai, pastikan:

- [ ] Setiap TASK-xxx merujuk minimal 1 FR-xxx atau COMP-xxx (grounding wajib)
- [ ] Tidak ada TASK dengan estimasi >1 hari (jika ada, pecah lagi)
- [ ] Dependency antar-TASK jelas dan tidak siklik (tidak ada circular dependency)
- [ ] Semua acceptance criteria dari FR-xxx tercakup oleh minimal 1 TASK
- [ ] Setiap TASK punya acceptance criteria yang terverifikasi (bisa ditest/dicek)
- [ ] Format task konsisten dengan template di Langkah 3

## Kondisi Berhenti / Eskalasi

- **FR-xxx belum dipetakan ke COMP di ARCHITECTURE.md** → Berhenti, sampaikan ke pengguna bahwa task breakdown dari FR hanya bisa dilakukan setelah ARCHITECTURE.md memetakan FR ke COMP. Rekomendasikan revisi ARCHITECTURE.md atau jalankan `spec-sync` untuk cek konsistensi.
- **COMP-xxx punya dependency ke COMP lain yang belum dibreakdown** → Berhenti, beri tahu pengguna urutan COMP mana yang harus dibreakdown dulu berdasarkan dependency graph.
- **Acceptance criteria FR-xxx ambigu atau tidak terukur** (mis. "sistem harus user-friendly")** → Berhenti, eskalasi ke pengguna dengan menyebutkan kriteria mana yang ambigu, minta klarifikasi atau revisi PRD.md. Jangan menebak interpretasi sendiri.
- **Estimasi TASK >2 hari meskipun sudah dipecah** → Ini tanda scope terlalu besar atau kompleksitas tinggi. Eskalasi ke pengguna: apakah perlu tambahan COMP/Service untuk handle kompleksitas ini, atau apakah acceptance criteria perlu disederhanakan.
- **Ditemukan konflik atau inkonsistensi antar dokumen** (mis. COMP-xxx di ARCHITECTURE.md menyebut FR-yyy, tapi FR-yyy tidak ada di PRD.md) → Berhenti, jalankan `spec-sync` terlebih dahulu untuk perbaiki konsistensi dokumen.

## Contoh Output (Breakdown untuk COMP-001: Identity Management)

```markdown
## COMP-001: Identity Management

### TASK-001: Buat migration & model User

**Status:** Pending

**Grounding:**
- Requirement: FR-001, FR-002, FR-003, NFR-001
- Component: COMP-001 (Identity Management)
- Data Model: DM-001 (users)

**Deskripsi:**
Membuat migration untuk tabel `users` sesuai DM-001 di ARCHITECTURE.md §5, termasuk field role (enum: tenant, admin, superadmin), dan model Eloquent `User` dengan relasi ke `Kost` (untuk admin) dan `Rental` (untuk tenant).

**Acceptance Criteria:**
- [ ] Migration `create_users_table` berisi semua field dari DM-001
- [ ] Model `User` punya cast untuk `role` (enum)
- [ ] Relasi `User::kosts()` (hasMany untuk admin) terdefinisi
- [ ] Relasi `User::rentals()` (hasMany untuk tenant) terdefinisi
- [ ] Factory dan seeder dasar untuk testing (minimal 3 user: tenant, admin, superadmin)
- [ ] Migration dapat dijalankan tanpa error: `sail artisan migrate`

**Dependencies:** (none)

**Estimasi:** 0.5 hari

---

### TASK-002: Implementasi Registration (Tenant)

**Status:** Pending

**Grounding:**
- Requirement: FR-001 (Registrasi tenant via email/password)
- Component: COMP-001
- Data Model: DM-001

**Deskripsi:**
Membuat form request, controller action, routes, dan view untuk registrasi tenant. Email harus unique, password hashing otomatis, role default = tenant.

**Acceptance Criteria:**
- [ ] `RegisterRequest` validasi email (unique, format valid) & password (min 8 char)
- [ ] `AuthController@register` menyimpan user baru dengan role = tenant
- [ ] Route `GET /register` dan `POST /register` (guest middleware)
- [ ] Blade view `auth/register.blade.php` dengan form email & password
- [ ] Setelah registrasi sukses, redirect ke halaman login dengan flash message
- [ ] Feature test `RegisterTest` validasi happy path & validation errors
- [ ] Lint lulus: `sail pint`

**Dependencies:** TASK-001

**Estimasi:** 1 hari

---

### TASK-003: Implementasi Login (Semua role)

**Status:** Pending

**Grounding:**
- Requirement: FR-002 (Login tenant), FR-009 (Login admin), FR-116 (Login superadmin)
- Component: COMP-001

**Deskripsi:**
Membuat form request, controller action, routes, view, dan session-based authentication untuk login semua role. Setelah login, redirect berdasarkan role (tenant → /, admin → /admin, superadmin → /superadmin).

**Acceptance Criteria:**
- [ ] `LoginRequest` validasi email & password
- [ ] `AuthController@login` autentikasi via `Auth::attempt()`, set session
- [ ] Route `GET /login` dan `POST /login` (guest middleware)
- [ ] Blade view `auth/login.blade.php`
- [ ] Middleware redirect setelah login berdasarkan role (lihat ARCHITECTURE.md §6)
- [ ] Feature test `LoginTest` validasi login sukses (3 role), login gagal, redirect logic
- [ ] Lint lulus: `sail pint`

**Dependencies:** TASK-001

**Estimasi:** 1 hari

---

### TASK-004: Implementasi Logout

**Status:** Pending

**Grounding:**
- Requirement: FR-003 (Logout tenant), FR-010 (Logout admin), FR-117 (Logout superadmin)
- Component: COMP-001

**Deskripsi:**
Membuat controller action & route untuk logout, menghapus session, redirect ke login page.

**Acceptance Criteria:**
- [ ] `AuthController@logout` panggil `Auth::logout()`, invalidate session, regenerate CSRF token
- [ ] Route `POST /logout` (auth middleware)
- [ ] Redirect ke `/login` setelah logout
- [ ] Feature test `LogoutTest` validasi session dihapus setelah logout
- [ ] Lint lulus: `sail pint`

**Dependencies:** TASK-003

**Estimasi:** 0.5 hari

---

### TASK-005: Profile Management (View & Edit)

**Status:** Pending

**Grounding:**
- Requirement: FR-004 (Tenant view/edit profile), FR-011 (Admin view/edit profile), FR-118 (Superadmin view/edit profile)
- Component: COMP-001

**Deskripsi:**
Membuat controller, form request, routes, dan view untuk melihat dan mengedit profil user (nama, email, password opsional).

**Acceptance Criteria:**
- [ ] `UpdateProfileRequest` validasi nama, email (unique kecuali milik sendiri), password opsional
- [ ] `ProfileController@show` tampilkan data user yang sedang login
- [ ] `ProfileController@update` update data user, hash password jika diubah
- [ ] Route `GET /profile` dan `PUT /profile` (auth middleware)
- [ ] Blade view `profile/edit.blade.php`
- [ ] Feature test `ProfileTest` validasi update sukses, validation errors, password update
- [ ] Lint lulus: `sail pint`

**Dependencies:** TASK-003

**Estimasi:** 1 hari

---

### TASK-006: Authorization Policy untuk User

**Status:** Pending

**Grounding:**
- Requirement: NFR-005 (Authorization: user hanya bisa edit profil sendiri)
- Component: COMP-001

**Deskripsi:**
Membuat `UserPolicy` dengan method `update(User $authUser, User $targetUser)` yang memastikan user hanya bisa edit profilnya sendiri (kecuali superadmin yang bisa edit semua).

**Acceptance Criteria:**
- [ ] `UserPolicy@update` return true jika `$authUser->id === $targetUser->id` atau `$authUser->role === 'superadmin'`
- [ ] Policy terdaftar di `AuthServiceProvider`
- [ ] Controller `ProfileController@update` panggil `$this->authorize('update', $user)` sebelum update
- [ ] Unit test `UserPolicyTest` validasi logic authorization
- [ ] Feature test update profile orang lain sebagai tenant → 403 Forbidden
- [ ] Lint lulus: `sail pint`

**Dependencies:** TASK-005

**Estimasi:** 0.5 hari

```

**Total estimasi COMP-001:** 5 hari kerja (6 tasks)

## Improvement Notes (vs Versi Sebelumnya yang Hilang)

- Tambah **Langkah 5 (Map ke Acceptance Criteria)** untuk verifikasi coverage — memastikan tidak ada gap antara FR dan TASK
- Tambah **field Estimasi** di template TASK — membantu project planning & capacity
- Tambah **kondisi eskalasi untuk acceptance criteria ambigu** — menghindari agent menebak interpretasi requirement
- Tambah **contoh output lengkap (COMP-001)** dengan 6 task konkret untuk referensi format yang konsisten
- Klarifikasi **aturan granularity** (≤1 hari) dengan contoh estimasi per jenis task
