# PRD.md — Product Requirements Document

> **Status dokumen ini:** Single Source of Truth untuk KEBUTUHAN (requirements) produk.
> Dokumen ini MENGGANTIKAN fungsi Discovery Document, Business Analysis Document, dan SRS.
> Agent/kontributor TIDAK PERLU membaca dokumen lain untuk memahami *apa* yang harus dibangun dan *mengapa* — cukup PRD.md ini.
> Untuk *bagaimana* dibangun secara teknis → lihat `ARCHITECTURE.md`.
> Untuk daftar pekerjaan konkret → lihat `TODO.md`.

| Field | Value |
|---|---|
| Nama Proyek | SewaKost — Web Marketplace Kost Management & Rental System |
| Versi Dokumen | `0.2.1` |
| Status | `Approved` |
| Pemilik (Owner) | Lauhul Ridwan |
| Terakhir Diperbarui | `2026-08-18` |

---

## 0. Cara Menggunakan Dokumen Ini

1. Setiap **requirement** punya ID unik dan permanen (FR-xxx / NFR-xxx). ID **tidak boleh diganti atau dipakai ulang** setelah pernah dipublikasikan, meskipun requirement-nya kemudian dihapus (tandai `Deprecated`, jangan hapus barisnya).
2. Semua istilah domain WAJIB didefinisikan di **§6 Glosarium**. Jangan gunakan istilah yang tidak ada di glosarium tanpa mendefinisikannya dulu.
3. Setiap requirement harus bisa ditelusuri (*traceable*) ke: User Story (US-xxx) → Komponen arsitektur (COMP-xxx di `ARCHITECTURE.md`) → Task (TASK-xxx di `TODO.md`).
4. Dokumen ini **hidup** (living document). Perubahan dicatat di §14 Changelog, bukan ditimpa diam-diam.
5. Agent coding: sebelum mengerjakan task apa pun, baca FR/NFR yang direferensikan task tersebut di `TODO.md`, bukan menebak dari judul task saja.

---

## 1. Ringkasan Produk (Executive Summary)

**SewaKost** adalah aplikasi web marketplace kost multi-owner yang mendigitalisasi seluruh siklus penyewaan kost dari pencarian hingga penyelesaian masa sewa. Platform memungkinkan **Penyewa (Tenant)** mencari kost, melakukan pemesanan, pembayaran via QRIS statis dengan upload bukti transfer, serta memenuhi administrasi penyewaan secara daring. **Admin Kost** mengelola operasional kost dan kamar, memverifikasi pembayaran dan dokumen penyewa. **Super Admin** melakukan proses review dan persetujuan sebelum kost dipublikasikan, serta mengelola kategori dan akun Admin.

**Nilai unik:** 
- Workflow verifikasi publikasi kost oleh Super Admin untuk menjaga kualitas informasi
- Verifikasi dokumen administrasi penyewa setelah pembayaran
- Pembayaran QRIS statis per kost dengan verifikasi manual oleh Admin (tanpa payment gateway kompleks)
- Siklus penyewaan end-to-end dalam satu platform

Aplikasi dikembangkan sebagai proyek pra-Ujian Kompetensi Keahlian (UKK) dengan arsitektur Laravel 13 monolith, fokus pada penyelesaian MVP yang dapat diimplementasikan oleh 1 full-stack developer.

---

## 2. Latar Belakang & Masalah (Problem Statement)

### 2.1 Masalah yang Dipecahkan

Pengelolaan kost pada skala kecil hingga menengah masih banyak dilakukan secara manual atau menggunakan media yang tidak terintegrasi, mencakup publikasi kost tersebar, pencatatan penyewa manual, administrasi dokumen tidak terstruktur, pencatatan pembayaran manual, dan tidak ada workflow verifikasi standar. Hal ini menyebabkan proses operasional tidak efisien, sulit dipantau, rentan kesalahan administrasi, dan informasi kost ke calon penyewa sering tidak valid atau tidak konsisten.

### 2.2 Kondisi Saat Ini (Current State)

Publikasi via platform listing umum, pencatatan via buku/spreadsheet/WhatsApp, pembayaran transfer manual, dokumen dikirim via chat, verifikasi kost informal tanpa dokumentasi sistem, tidak ada riwayat status penyewaan terstruktur. Kompetitor seperti Mamikos/Infokost/Travelio ada, namun kebanyakan tidak punya workflow persetujuan publikasi kost, verifikasi administrasi dilakukan di luar sistem, dan kompleksitas fitur terlalu tinggi untuk pengelola kost skala kecil-menengah.

### 2.3 Kondisi yang Diinginkan (Desired State)

Pengelola kost mengelola seluruh operasional (data kost, kamar, harga, penyewa, verifikasi) via satu platform. Platform memastikan hanya kost terverifikasi Super Admin yang dapat dipublikasikan. Penyewa mencari kost valid, booking, pembayaran QRIS, upload dokumen, pantau status penyewaan transparan. Semua pihak punya visibilitas status penyewaan dan riwayat transaksi terdokumentasi. Data operasional tersimpan terpusat, konsisten, dapat ditelusuri untuk operasional & audit.

---

## 3. Tujuan & Metrik Keberhasilan (Goals & Success Metrics)

| ID | Tujuan | Metrik | Target | Cara Ukur |
|---|---|---|---|---|
| GOAL-01 | Digitalisasi publikasi kost melalui workflow verifikasi | % kost dipublikasikan via workflow verifikasi sistem | 100% | Count kost dengan status Active yang melalui approval workflow |
| GOAL-02 | Siklus penyewaan end-to-end diselesaikan dalam sistem | % rental mencapai status Completed tanpa proses manual di luar sistem | ≥90% | Count rental Completed / total rental Created |
| GOAL-03 | Efisiensi administrasi pengelola kost | Pengurangan waktu proses administrasi penyewa | Dokumentasi kualitatif: pengelola tidak perlu mencatat manual di spreadsheet/buku | User feedback & observasi |
| GOAL-04 | Kualitas data kost marketplace | % kost di marketplace memiliki data lengkap dan valid | 100% | Validasi data wajib kost berstatus Active |

---

## 4. Target Pengguna & Persona

### Persona P-01: Rina — Penyewa Kost (Tenant)
- **Deskripsi singkat:** Mahasiswa atau pekerja muda (18-35 tahun) mencari kost jangka menengah-panjang (≥1 bulan). Familiar dengan aplikasi mobile/web dasar.
- **Tujuan utama:** Menemukan kost sesuai budget & lokasi, menyelesaikan proses penyewaan (booking, pembayaran, administrasi) secara online tanpa bertemu berkali-kali dengan pengelola, memantau status penyewaan transparan.
- **Frustrasi/pain point:** Informasi kost tidak lengkap/tidak update, harus komunikasi berulang via WhatsApp, tidak ada kepastian status booking, bukti pembayaran & dokumen via chat tanpa konfirmasi sistem.
- **Tingkat literasi teknologi:** Sedang

### Persona P-02: Budi — Pengelola Kost (Admin Kost)
- **Deskripsi singkat:** Pemilik/pengelola kost skala kecil-menengah (5-20 kamar). Usia 30-50 tahun. Mengelola operasional part-time atau full-time. Butuh sistem sederhana untuk mengurangi beban administratif.
- **Tujuan utama:** Mempublikasikan kost di marketplace terverifikasi, mengelola data kamar & harga, menerima booking online, verifikasi pembayaran QRIS & dokumen penyewa, pantau status penyewaan tanpa catatan manual.
- **Frustrasi/pain point:** Pencatatan manual di buku/spreadsheet tidak terstruktur, cek transfer bank manual satu-satu, dokumen penyewa via WhatsApp sulit diorganisir, tidak ada riwayat status penyewaan jelas.
- **Tingkat literasi teknologi:** Sedang

### Persona P-03: Pak Ahmad — Administrator Platform (Super Admin)
- **Deskripsi singkat:** Pengelola platform SewaKost. Verifikasi calon Admin Kost manual di luar sistem, review pengajuan publikasi kost, kelola master kategori, kelola akun Admin.
- **Tujuan utama:** Pastikan hanya kost terverifikasi (administrasi & lapangan) yang tampil di marketplace, kelola kategori kost standar platform, kelola akun Admin Kost terdaftar.
- **Frustrasi/pain point:** Tidak ada sistem terpusat untuk workflow approval kost. Sulit menjaga konsistensi & kualitas informasi kost jika setiap Admin langsung publish tanpa review.
- **Tingkat literasi teknologi:** Menengah hingga tinggi

---

## 5. Ruang Lingkup (Scope)

### 5.1 In Scope (dikerjakan pada MVP ini)

#### Authentication & User Management
- Login dan Logout
- Role-Based Access Control (Tenant, Admin, Super Admin)
- Registrasi akun Tenant (self-registration)
- **OTP Email Verification (6-digit code, expiry 15 menit)** — verification tidak mutlak wajib untuk login, namun wajib hanya saat mengakses fitur yang membutuhkan email terverifikasi (misal: Tenant create rental)
- Manajemen profil pengguna (first name, last name, phone, avatar, **email**)
- **User (Tenant) dapat mengubah email**, dengan konsekuensi email baru perlu diverifikasi ulang via OTP sebelum dapat digunakan untuk fitur yang membutuhkan email terverifikasi
- Soft delete akun (users.deleted_at)
- Pembuatan akun Admin oleh Super Admin (setelah verifikasi manual di luar sistem)

#### Kost Publication Workflow
- Admin membuat kost sebagai Draft
- Admin submit kost untuk Review (status: Pending Review)
- Super Admin review submission: Approve atau Reject (dengan alasan penolakan)
- Admin publish kost yang Approved → status Active
- Kost Rejected dapat direvisi oleh Admin, kembali ke Draft, dan disubmit ulang
- Kost Active ditampilkan di marketplace

#### Kost Configuration
- Informasi kost: nama, deskripsi, contact number
- Alamat lengkap + koordinat (latitude, longitude) untuk maps
- Upload & manage gambar kost (thumbnail + galeri)
- Assign kategori kost (dari master kategori yang dikelola Super Admin)
- **Facilities & Rules:** JSON array of strings di `kosts.facilities`, `kosts.rules` (Admin input/edit sebagai list teks)
- **QRIS Payment Configuration:** Upload QRIS static image + informasi rekening tujuan (bank name, account number, account holder name)
- **Document Requirements Configuration:** Admin mendefinisikan jenis dokumen yang dibutuhkan untuk rental (KTP, Selfie, Kartu Pelajar, dll.), set wajib/opsional, dan alasan permintaan dokumen

#### Room Inventory Management
- Kelola Room Type (nama, deskripsi, ukuran, max occupants, security deposit, gambar)
- **Facilities & Rules per Room Type:** JSON array of strings di `room_types.facilities`, `room_types.rules`
- Kelola Price Scheme per Room Type (harga, durasi value, durasi unit: day/week/month, status active/inactive) — **Price Scheme 1:N dengan Room Type** (setiap price scheme belongs_to satu room type)
- Kelola Room (unit fisik kamar): room code (unik per kost), status (available/occupied/reserved/maintenance)
  - Admin hanya dapat set `maintenance` dari `available`, dan kembali ke `available`
  - `reserved` dan `occupied` dikelola sistem berdasarkan lifecycle rental

#### Marketplace Exploration
- Browse daftar kost Active dengan pagination
- Search kost berdasarkan nama atau lokasi (city, district, address)
- Filter kost berdasarkan: rentang harga, kategori, rating minimum
- View detail kost:
  - Informasi kost, alamat lengkap, gambar galeri, kategori
  - Facilities & Rules kost
  - Document requirements (jenis, wajib/opsional, alasan) — agar Tenant aware sebelum booking
  - Room Types available beserta gambar, facilities, rules
  - Price Schemes aktif per Room Type
  - Reviews & ratings
  - Map lokasi (Leaflet + OpenStreetMap)

#### Rental Lifecycle
- **Tenant create rental:**
  1. Pilih Room Type
  2. Pilih Price Scheme yang aktif
  3. Pilih Room available
  4. Pilih contract start date (min: today, max: today + 30 hari)
  5. Tentukan durasi rental (kelipatan unit durasi dari Price Scheme)
  6. Sistem calculate total biaya (harga × durasi + security deposit)
  7. Rental created dengan status `Pending`

- **Payment via QRIS statis:**
  - Admin memiliki QRIS image per kost (di-upload saat konfigurasi kost)
  - Tenant melihat QRIS, scan & transfer manual
  - Tenant upload bukti pembayaran (screenshot/foto)
  - Admin verifikasi bukti pembayaran manual
  - **Jika approved** → Payment status `Success`, Rental status → `Paid`
  - **Jika rejected** → Admin wajib memberikan alasan penolakan. Tenant dapat melihat alasan dan upload ulang bukti pembayaran yang benar.
  - **Payment deadline: 48 jam** dari rental created_at. Jika melewati batas waktu tanpa Payment Success → Rental status `Cancelled`

- **Document submission & verification:**
  - **Requirement dokumen bergantung kebijakan kost:** jenis dokumen, wajib/opsional, alasan permintaan
  - Setelah Payment Success, Tenant upload dokumen administrasi sesuai requirement kost
  - Admin review dokumen, approve atau reject per dokumen (dengan alasan penolakan)
  - **Jika semua dokumen wajib approved** → Rental status `Confirmed`
  - **Jika ada dokumen rejected** → Tenant dapat melihat alasan dan upload ulang dokumen yang diminta
  - **Jika dokumen tidak dilengkapi sebelum contract start date** → Rental otomatis `Cancelled` oleh sistem
  - Rental status progression: `Pending` → `Paid` → (dokumen uploaded & dalam review) → `Confirmed` → `Active` → `Completed`

- **Rental activation & completion:**
  - Saat contract start date tercapai → Rental status `Active`
  - Saat contract end date tercapai → Rental status `Completed` (otomatis oleh sistem)

- **Rental manual cancellation:**
  - Tenant dapat cancel rental manual sebelum rental `Active`
  - Rental yang sudah `Active` atau `Completed` tidak dapat di-cancel
  - Refund policy berada di luar tanggung jawab aplikasi (Tenant nego langsung dengan Admin kost)
  - Tidak ada cancellation fee di sistem

- **Rental monitoring:**
  - Tenant dapat melihat status rental miliknya, status verifikasi payment & dokumen, dan alasan rejection (jika ada)
  - Admin dapat melihat rental untuk kost yang dikelolanya
  - Rental status history tercatat (rental_status_histories)
  - Cancellation info (alasan, timestamp) tersimpan dan ditampilkan

#### Review Management
- Setelah Rental `Completed`, Tenant dapat submit review
- **Review per Rental** (1 tabel `reviews`):
  - Kolom: `rental_id`, `kost_rating` (1-5), `kost_comment`, `room_rating` (1-5), `room_comment`, `images` (JSON array of image paths)
  - Tenant dapat review kost saja, room saja, atau keduanya
  - Review images di-upload dan disimpan sebagai JSON array di kolom `images`
- Review ditampilkan di detail kost marketplace

#### Administration
- Super Admin membuat akun Admin (setelah verifikasi administratif manual di luar sistem)
- Super Admin mengelola akun Admin (update info, disable via soft delete)
- Super Admin tidak dapat membuat atau promote akun ke Super Admin via UI
- Super Admin mengelola master kategori (CRUD categories via UI)
- Admin tidak dapat CRUD kategori (hanya assign kategori yang tersedia ke kost miliknya)

#### Notification
- Email notification untuk:
  - **OTP Email verification** (Tenant registration, email change re-verification)
  - Kost submission approved/rejected (ke Admin)
  - Payment verified/rejected (ke Tenant)
  - Document verified/rejected (ke Tenant)
  - Rental status changes (ke Tenant & Admin)
  - Admin account created (ke Admin)
  - Rental auto-cancelled karena dokumen incomplete/payment deadline (ke Tenant)

#### Backup & Recovery
- Database backup mechanism (scheduled)
- Backup restoration procedure
- File storage backup (images, documents)

### 5.2 Out of Scope (secara eksplisit TIDAK dikerjakan pada MVP ini)

Mobile app, multi-language, multi-currency, payment gateway otomatis (Midtrans/Xendit), multi-payment gateway, automatic refund, promo/voucher, subscription system, in-app chat, WhatsApp/Push/SMS notification, AI recommendation, advanced analytics, advanced audit log, meeting scheduler, physical document management, admin verification digitalization, extend rental feature, kost status (inactive/suspended/archived), room status inactive.

> **Penting:** Agent tidak boleh menambahkan fitur dari daftar Out of Scope tanpa persetujuan eksplisit & update dokumen ini.

---

## 6. Glosarium (Glossary)

| Istilah | Definisi |
|---|---|
| **Kost** | Properti boarding house/indekost yang dikelola satu Admin dan dapat memiliki banyak kamar untuk disewakan. |
| **Room Type** | Kelas/tipe kamar dalam satu kost (misal: Single Bed, Double Bed, Suite) yang mendefinisikan karakteristik kamar. Satu Room Type dapat memiliki banyak unit Room fisik. |
| **Room** | Unit fisik kamar individual yang dapat disewa. Setiap Room memiliki kode unik dalam satu kost dan terkait dengan satu Room Type. |
| **Price Scheme** | Skema harga sewa untuk satu Room Type, terdiri dari: harga, nilai durasi, unit durasi (day/week/month). Satu Room Type dapat memiliki beberapa Price Scheme. |
| **Rental** | Transaksi penyewaan yang menghubungkan Tenant dengan Room, menyimpan snapshot harga dan durasi sewa yang dipilih, serta melacak lifecycle dari pending hingga completed. |
| **Payment Deadline** | Batas waktu yang ditetapkan sistem untuk Tenant menyelesaikan pembayaran setelah Rental dibuat. **MVP: 48 jam.** Jika melewati deadline tanpa Payment Success, Rental otomatis menjadi Cancelled. |
| **QRIS Statis** | QR Code pembayaran statis yang di-upload Admin per kost. Tenant scan QRIS, transfer manual ke rekening kost, lalu upload bukti pembayaran untuk diverifikasi Admin. |
| **Email Verification (OTP)** | Mekanisme verifikasi email menggunakan One-Time Password (OTP) 6-digit code yang dikirim ke email user. User input kode OTP di aplikasi untuk verify email. OTP expiry time: 15 menit. Verification tidak mutlak wajib untuk login, namun wajib saat mengakses fitur tertentu (misal: Tenant create rental). OTP verifikasi email dikirim **on-demand** — saat user membuka halaman verifikasi atau diminta fitur yang membutuhkan email terverifikasi — bukan otomatis saat registrasi (lihat ADR-023 di ARCHITECTURE.md). OTP juga dipakai untuk password reset (lihat **Password Reset via OTP**). |
| **Password Reset via OTP** | Mekanisme reset password menggunakan OTP 6-digit code yang dikirim ke email terdaftar (FR-130). Alur 3 langkah: input email → verifikasi OTP → set password baru. Menggantikan reset berbasis token link Breeze. Email yang tidak terdaftar tetap mendapat respons generik (anti-enumeration). |
| **Document Requirement** | Kebijakan dokumen administrasi yang didefinisikan per kost oleh Admin, menentukan jenis dokumen apa yang dibutuhkan (KTP, Selfie, Kartu Pelajar, dll.), apakah wajib/opsional, dan alasan permintaannya. |
| **Contract Start Date** | Tanggal mulai masa sewa rental, dipilih Tenant saat booking. Min: today, Max: today + 30 hari. |
| **Contract End Date** | Tanggal berakhir masa sewa rental, dihitung dari contract start date + durasi sewa. |
| **Tenant** | Penyewa (role `user`) yang mencari kost, booking, pembayaran, upload dokumen, memberikan review setelah masa sewa selesai. |
| **Admin Kost** | Pengelola kost (role `admin`) yang mengelola data kost, kamar, harga, verifikasi pembayaran QRIS, verifikasi dokumen penyewa, operasional rental. |
| **Super Admin** | Administrator platform (role `superadmin`) yang membuat akun Admin setelah verifikasi manual, review dan approval publikasi kost, kelola master kategori. |
| **Kost Publication Workflow** | Alur: Draft → Submit for Review → Pending Review → Approve/Reject → (jika Approved) Publish → Active. Hanya kost Active yang muncul di marketplace. |
| **Rental Lifecycle** | Alur status rental: Pending → Paid (setelah payment verified) → Confirmed (setelah dokumen wajib approved) → Active (saat contract start date) → Completed (saat contract end date) atau Cancelled. |
| **Room Status - Available** | Kamar tersedia untuk disewa. Dapat dipilih Tenant saat create rental. |
| **Room Status - Occupied** | Kamar sedang disewa dan tenant menempati (max occupancy tercapai). Set otomatis sistem saat ada Rental Active. |
| **Room Status - Reserved** | Ada Rental yang terkait dengan Room ini dan belum Completed/Cancelled. Room tidak dapat dipilih untuk rental baru. |
| **Room Status - Unavailable** | Kamar tidak tersedia untuk disewa (untuk alasan apapun: renovasi, rusak, pemilik pakai sendiri, dibersihkan, dll). Hanya dapat di-set Admin dari status Available, dan dapat dikembalikan ke Available oleh Admin. Admin hanya dapat set unavailable jika room benar-benar kosong (tidak ada rental aktif/reserved). | 
| **Soft Delete** | Mekanisme penghapusan data dengan menandai `deleted_at` timestamp, sehingga data tidak benar-benar dihapus dari database namun tidak dapat diakses/digunakan lagi. |
| **Security Deposit** | Uang jaminan yang dibayar Tenant saat rental (selain harga sewa). Nilai security deposit ditentukan di Room Type. |
| **Review** | Penilaian dan komentar yang diberikan Tenant setelah Rental Completed. Satu review per rental dapat mencakup rating & comment untuk kost dan/atau room, serta upload gambar (disimpan sebagai JSON array). |
| **Marketplace** | Halaman publik yang menampilkan daftar kost berstatus Active. Dapat diakses tanpa login. Tenant dapat search/filter kost berdasarkan nama, lokasi, harga, kategori, rating. |
| **Refund Policy** | Berada di luar tanggung jawab aplikasi. Jika Tenant cancel rental setelah payment verified, Tenant harus nego refund langsung dengan Admin kost di luar sistem. Tidak ada cancellation fee di sistem. |

---

## 7. Functional Requirements (FR-xxx)

> Format ID: `FR-001`, `FR-002`, dst. — nomor urut global, tidak per-modul, tidak pernah dipakai ulang.
> Prioritas pakai MoSCoW: **Must** (wajib rilis ini) / **Should** (penting tapi bisa mundur) / **Could** (nice-to-have) / **Won't** (eksplisit tidak untuk rilis ini).
> **Status yang valid:** `Draft` → `Approved` → `In Progress` → `Done` → (opsional) `Deprecated`.

### Identity & Account Management (FR-001 — FR-013)

| ID | Judul | Deskripsi | Prioritas | Acceptance Criteria | User Story Terkait | Status |
|---|---|---|---|---|---|---|
| FR-001 | User Login | Sistem harus memungkinkan user (Tenant, Admin, Super Admin) melakukan login dengan email dan password. | Must | Given user memiliki akun valid, When memasukkan email & password benar, Then user terautentikasi dan diarahkan ke halaman sesuai role. | US-001 | Draft |
| FR-002 | User Logout | Sistem harus memungkinkan user yang terautentikasi melakukan logout. | Must | Given user terautentikasi, When memilih logout, Then session berakhir dan user diarahkan ke halaman publik. | US-001 | Draft |
| FR-003 | Tenant Self-Registration | Sistem harus memungkinkan calon Tenant mendaftar akun baru dengan email, password, first name, last name. | Must | Given calon Tenant mengisi form registrasi valid, When submit, Then akun Tenant dibuat dengan role `user`, user diarahkan ke marketplace, dan verifikasi email bersifat OPSIONAL (tidak ada OTP otomatis dikirim). | US-002 | Draft |
| FR-004 | OTP Email Verification | Sistem harus mengirim OTP 6-digit code (expiry 15 menit) ke email Tenant. OTP dikirim ON-DEMAND saat Tenant membuka halaman verifikasi email atau mengakses fitur yang membutuhkan email terverifikasi. Tidak dikirim otomatis saat registrasi. Tenant verify email dengan input kode OTP yang benar di aplikasi. | Must | Given Tenant membuka halaman verifikasi email (atau diminta verifikasi oleh fitur), When input kode OTP yang benar sebelum expiry, Then email_verified_at di-set dan Tenant dapat mengakses fitur yang membutuhkan email terverifikasi. | US-002 | Draft |
| FR-005 | Resend OTP | Sistem harus memungkinkan Tenant yang belum verifikasi email meminta pengiriman ulang OTP. OTP lama expired saat OTP baru dikirim. | Must | Given Tenant belum verified, When request resend, Then OTP baru dikirim dan OTP lama expired. | US-002 | Draft |
| FR-006 | Email Verification Required for Specific Features | Sistem harus mewajibkan email terverifikasi hanya saat Tenant mengakses fitur tertentu (misal: create rental). Tenant yang belum verifikasi email tetap dapat login dan browse marketplace. Saat Tenant yang belum terverifikasi mengakses fitur tersebut, sistem menampilkan popup yang menjelaskan perlunya verifikasi email dengan CTA button menuju halaman verifikasi OTP. | Must | Given Tenant belum verified, When mencoba create rental, Then sistem tolak dan minta Tenant verifikasi email dulu. Tenant tetap dapat login dan browse marketplace tanpa verifikasi. | US-002, US-010 | Draft |
| FR-007 | RBAC - Role-Based Access | Sistem harus membatasi akses fungsi berdasarkan role user (Tenant, Admin, Super Admin). | Must | Given user terautentikasi dengan role tertentu, When mengakses fungsi, Then hanya fungsi sesuai role yang accessible. | US-001 | Draft |
| FR-008 | RBAC - Resource Ownership | Sistem harus memastikan user hanya dapat melakukan operasi terhadap resource yang menjadi kewenangannya. | Must | Given user mencoba akses resource, When resource bukan milik/kewenangan user, Then akses ditolak. | US-001 | Draft |
| FR-009 | Manage User Profile - View | Sistem harus memungkinkan user melihat profil miliknya (first name, last name, email, phone, avatar). | Must | Given user terautentikasi, When membuka profil, Then data profil ditampilkan. | US-003 | Draft |
| FR-010 | Manage User Profile - Update | Sistem harus memungkinkan user mengupdate profil miliknya (first name, last name, phone, avatar, **email**). Jika email diubah, email baru perlu diverifikasi ulang via OTP. | Must | Given user membuka profil, When update data valid (termasuk email baru), Then perubahan tersimpan. Email baru belum dapat digunakan untuk fitur yang membutuhkan email terverifikasi sampai OTP di-input benar. | US-003 | Draft |
| FR-011 | Manage User Profile - Update Avatar | Sistem harus memungkinkan user mengupload dan mengupdate foto profil (avatar). | Should | Given user membuka profil, When upload file image valid, Then avatar tersimpan dan ditampilkan. | US-003 | Draft |
| FR-012 | Soft Delete Account | Sistem harus memungkinkan user menghapus akun miliknya (soft delete dengan deleted_at). | Should | Given user memilih delete account, When konfirmasi, Then deleted_at di-set, user logout, dan tidak dapat login lagi. Data historis (rental, review, approval) tetap valid. | US-003 | Draft |
| FR-013 | Prevent Deleted User Authentication | Sistem harus mencegah user yang sudah soft deleted melakukan autentikasi atau aktivitas bisnis baru. | Must | Given user sudah soft deleted, When mencoba login, Then autentikasi ditolak. | US-003 | Draft |

### Kost Publication Workflow (FR-014 — FR-023)

| ID | Judul | Deskripsi | Prioritas | Acceptance Criteria | User Story Terkait | Status |
|---|---|---|---|---|---|---|
| FR-014 | Create Kost Draft | Sistem harus memungkinkan Admin membuat kost baru dengan status awal `Draft`. | Must | Given Admin terautentikasi, When mengisi data kost dan simpan, Then kost dibuat dengan status `Draft` dan terkait dengan Admin tersebut. | US-004 | Draft |
| FR-015 | Update Kost Draft | Sistem harus memungkinkan Admin mengupdate data kost yang berstatus `Draft` atau `Rejected`. | Must | Given kost berstatus Draft/Rejected dan milik Admin, When Admin update data valid, Then perubahan tersimpan. | US-004 | Draft |
| FR-016 | Submit Kost for Review | Sistem harus memungkinkan Admin submit kost Draft untuk direview oleh Super Admin (status menjadi `Pending Review`). | Must | Given kost berstatus Draft dan data wajib lengkap, When Admin submit for review, Then status berubah `Pending Review` dan Super Admin dapat melihat submission. | US-005 | Draft |
| FR-017 | Validate Required Data Before Submit | Sistem harus memvalidasi data wajib kost (nama, alamat, kategori, minimal 1 room type, dll.) sebelum dapat disubmit for review. | Must | Given kost Draft dengan data wajib tidak lengkap, When Admin submit, Then sistem tolak dan tampilkan data apa yang kurang. | US-005 | Draft |
| FR-018 | Review Kost Submission - Approve | Sistem harus memungkinkan Super Admin mereview submission dan approve kost (status menjadi `Approved`). | Must | Given kost Pending Review, When Super Admin approve, Then status berubah `Approved` dan Admin dapat publish. | US-006 | Draft |
| FR-019 | Review Kost Submission - Reject | Sistem harus memungkinkan Super Admin reject submission dengan alasan penolakan (status menjadi `Rejected`). | Must | Given kost Pending Review, When Super Admin reject dengan alasan, Then status `Rejected`, alasan tersimpan, dan Admin dapat melihat alasan & merevisi. | US-006 | Draft |
| FR-020 | Revise Rejected Kost | Sistem harus memungkinkan Admin merevisi kost Rejected. Saat Admin simpan perubahan, status kembali ke `Draft` dan dapat disubmit ulang. | Must | Given kost Rejected, When Admin update data dan simpan, Then status kembali `Draft`, rejected_reason di-clear, dan Admin dapat submit ulang. | US-007 | Draft |
| FR-021 | Publish Approved Kost | Sistem harus memungkinkan Admin mempublikasikan kost yang sudah Approved (status menjadi `Active`). | Must | Given kost Approved, When Admin publish, Then status `Active`, published_at timestamp di-set, dan kost muncul di marketplace. | US-008 | Draft |
| FR-022 | Display Only Active Kost in Marketplace | Sistem hanya boleh menampilkan kost berstatus `Active` di marketplace. Kost dengan status lain tidak muncul di public listing. | Must | Given marketplace diakses, When sistem load daftar kost, Then hanya kost Active yang ditampilkan. | US-014 | Draft |
| FR-023 | Prevent Direct Status Change | Sistem harus mencegah Admin mengubah status kost secara arbitrary. Transisi status harus mengikuti workflow. | Must | Given Admin mencoba bypass workflow, When sistem validasi, Then operasi ditolak dan Admin diminta ikuti workflow yang benar. | US-008 | Draft |

### Kost Configuration (FR-024 — FR-035)

| ID | Judul | Deskripsi | Prioritas | Acceptance Criteria | User Story Terkait | Status |
|---|---|---|---|---|---|---|
| FR-024 | Configure Kost Basic Information | Sistem harus memungkinkan Admin mengelola informasi dasar kost: nama, slug, deskripsi, contact number. | Must | Given Admin membuka kost miliknya, When update info dan simpan, Then perubahan tersimpan dan ditampilkan di detail kost. | US-004 | Draft |
| FR-025 | Configure Kost Address | Sistem harus memungkinkan Admin mengelola alamat lengkap kost: full address, district, city, province, postal code, country, latitude, longitude. | Must | Given Admin input alamat valid, When simpan, Then alamat tersimpan dan lat/long digunakan untuk maps display. | US-004 | Draft |
| FR-026 | Upload Kost Images | Sistem harus memungkinkan Admin mengupload gambar kost. Satu gambar dapat ditandai sebagai thumbnail. | Must | Given Admin upload image file valid, When simpan, Then image tersimpan dengan sort order. Admin dapat set 1 gambar sebagai thumbnail. | US-004 | Draft |
| FR-027 | Assign Kost Categories | Sistem harus memungkinkan Admin memilih kategori kost dari master kategori yang tersedia (dikelola Super Admin). Admin tidak dapat membuat kategori baru. | Must | Given Admin membuka kategori kost, When pilih dari list kategori available, Then kategori ter-assign ke kost dan ditampilkan di marketplace. | US-004, US-009 | Draft |
| FR-028 | Configure Kost Facilities (JSON) | Sistem harus memungkinkan Admin mengelola facilities kost sebagai JSON array of strings. | Must | Given Admin input list facilities, When simpan, Then facilities tersimpan sebagai JSON dan ditampilkan di detail kost. | US-004 | Draft |
| FR-029 | Configure Kost Rules (JSON) | Sistem harus memungkinkan Admin mengelola rules kost sebagai JSON array of strings. | Must | Given Admin input list rules, When simpan, Then rules tersimpan sebagai JSON dan ditampilkan di detail kost. | US-004 | Draft |
| FR-030 | Upload QRIS Static Image | Sistem harus memungkinkan Admin mengupload QRIS static image untuk kost. QRIS ini ditampilkan ke Tenant saat payment. | Must | Given Admin upload QRIS image file valid, When simpan, Then QRIS image tersimpan dan ditampilkan saat Tenant melakukan payment. | US-004 | Draft |
| FR-031 | Configure Bank Account Info | Sistem harus memungkinkan Admin menginput informasi rekening tujuan: bank name, account number, account holder name. Info ini ditampilkan ke Tenant saat payment. | Must | Given Admin input info rekening valid, When simpan, Then info tersimpan dan ditampilkan bersama QRIS saat payment. | US-004 | Draft |
| FR-032 | Configure Document Requirements | Sistem harus memungkinkan Admin mendefinisikan jenis dokumen yang dibutuhkan untuk rental di kost tersebut. | Must | Given Admin membuka config document requirements, When pilih jenis dokumen dan simpan, Then requirement tersimpan untuk kost. | US-004 | Draft |
| FR-033 | Set Document Requirement Status | Sistem harus memungkinkan Admin menandai setiap jenis dokumen requirement sebagai Wajib atau Opsional. | Must | Given dokumen requirement sudah ditambahkan, When Admin set sebagai Required/Optional, Then status tersimpan dan digunakan saat verifikasi dokumen rental. | US-004 | Draft |
| FR-034 | Add Document Requirement Reason | Sistem harus memungkinkan Admin memberikan alasan/penjelasan untuk setiap dokumen requirement. | Must | Given dokumen requirement sudah ditambahkan, When Admin input reason, Then reason tersimpan dan ditampilkan ke Tenant di detail kost. | US-004 | Draft |
| FR-035 | Display Document Requirements in Kost Detail | Sistem harus menampilkan list document requirements (jenis, wajib/opsional, alasan) di halaman detail kost marketplace agar Tenant aware sebelum booking. | Must | Given kost memiliki document requirements, When Tenant buka detail kost, Then requirements ditampilkan dengan jelas. | US-014 | Draft |

### Room Inventory Management (FR-036 — FR-047)

| ID | Judul | Deskripsi | Prioritas | Acceptance Criteria | User Story Terkait | Status |
|---|---|---|---|---|---|---|
| FR-036 | Create Room Type | Sistem harus memungkinkan Admin membuat Room Type untuk kost miliknya dengan: name, slug, description, room size, max occupants, security deposit. | Must | Given Admin membuka kost, When membuat room type dengan data valid, Then room type tersimpan dan ter-associate dengan kost. | US-005 | Draft |
| FR-037 | Update Room Type | Sistem harus memungkinkan Admin mengupdate data Room Type miliknya. | Must | Given room type milik Admin, When update data valid, Then perubahan tersimpan. | US-005 | Draft |
| FR-038 | Upload Room Type Images | Sistem harus memungkinkan Admin mengupload gambar untuk Room Type. Satu gambar dapat ditandai sebagai thumbnail. | Must | Given Admin upload image file valid untuk room type, When simpan, Then image tersimpan. Admin dapat set 1 gambar sebagai thumbnail. | US-005 | Draft |
| FR-039 | Configure Room Type Facilities (JSON) | Sistem harus memungkinkan Admin mengelola facilities per Room Type sebagai JSON array of strings. | Must | Given Admin input list facilities untuk room type, When simpan, Then facilities tersimpan sebagai JSON dan ditampilkan di detail room type. | US-005 | Draft |
| FR-040 | Configure Room Type Rules (JSON) | Sistem harus memungkinkan Admin mengelola rules per Room Type sebagai JSON array of strings. | Must | Given Admin input list rules untuk room type, When simpan, Then rules tersimpan sebagai JSON dan ditampilkan di detail room type. | US-005 | Draft |
| FR-041 | Create Price Scheme for Room Type | Sistem harus memungkinkan Admin membuat Price Scheme untuk Room Type dengan: price, duration_value, duration_unit (day/week/month), is_active. Price Scheme belongs to satu Room Type (1:N). | Must | Given Admin membuka room type, When membuat price scheme valid, Then price scheme tersimpan untuk room type tersebut. | US-005 | Draft |
| FR-042 | Update Price Scheme | Sistem harus memungkinkan Admin mengupdate Price Scheme miliknya. | Must | Given price scheme milik Admin, When update, Then perubahan tersimpan. | US-005 | Draft |
| FR-043 | Activate/Deactivate Price Scheme | Sistem harus memungkinkan Admin mengaktifkan atau menonaktifkan Price Scheme. Price Scheme inactive tidak dapat dipilih Tenant untuk rental baru. | Must | Given price scheme exists, When Admin toggle is_active, Then status tersimpan. Inactive scheme tidak muncul di pilihan Tenant saat create rental. | US-005 | Draft |
| FR-044 | Create Room Unit | Sistem harus memungkinkan Admin membuat Room (unit fisik kamar) dengan: room code (unik per kost), room type, status awal `available`. | Must | Given Admin pilih room type, When buat room dengan code unik valid, Then room tersimpan dengan status `available`. | US-005 | Draft |
| FR-045 | Update Room Unit | Sistem harus memungkinkan Admin mengupdate room code atau room type dari Room. | Must | Given room milik Admin, When update, Then perubahan tersimpan. | US-005 | Draft |
| FR-046 | Set Room to Unavailable | Sistem harus memungkinkan Admin mengubah Room status dari `available` ke `unavailable`, dan sebaliknya. Room hanya dapat di-set `unavailable` jika benar-benar kosong (tidak ada rental pending/paid/confirmed/active). | Must | Given room berstatus `available` dan tidak ada rental aktif, When Admin set unavailable, Then status berubah `unavailable` dan room tidak dapat dipilih untuk rental baru. Admin dapat set kembali ke `available`. Validation: sistem wajib cek tidak ada rental dengan status pending/paid/confirmed/active untuk room tersebut sebelum allow set unavailable. | US-005 | Draft |
| FR-047 | Prevent Room Status Manual Change to Reserved/Occupied | Sistem harus mencegah Admin secara manual mengubah Room status ke nilai selain `available` atau `unavailable`. Status `reserved` dan `occupied` dihitung real-time dari rental, bukan disimpan di room status. | Must | Given Admin mencoba set room status manual, When sistem validasi, Then hanya `available` dan `unavailable` yang diperbolehkan. Reserved/occupied count dihitung dari rentals table. | US-005 | Draft |

### Marketplace Exploration (FR-048 — FR-060)

| ID | Judul | Deskripsi | Prioritas | Acceptance Criteria | User Story Terkait | Status |
|---|---|---|---|---|---|---|
| FR-048 | Browse Marketplace without Login | Sistem harus memungkinkan visitor browsing daftar kost di marketplace tanpa login. | Must | Given visitor buka marketplace, When load page, Then daftar kost Active ditampilkan. Login tidak required untuk browsing. | US-014 | Draft |
| FR-049 | Display Kost List | Sistem harus menampilkan daftar kost Active dengan informasi ringkas: thumbnail, nama, lokasi (city), harga mulai dari, rating rata-rata. | Must | Given marketplace page loaded, When sistem render list, Then setiap kost item menampilkan thumbnail, nama, city, starting price, avg rating. | US-014 | Draft |
| FR-050 | Pagination Kost List | Sistem harus menampilkan kost list dengan pagination atau infinite scroll. | Should | Given banyak kost Active, When user scroll/navigate, Then load kost secara bertahap. | US-014 | Draft |
| FR-051 | Search Kost by Name or Location | Sistem harus memungkinkan Tenant mencari kost berdasarkan nama kost atau lokasi (city, district, address). | Must | Given Tenant input keyword search, When submit, Then sistem tampilkan kost yang match dengan keyword. | US-015 | Draft |
| FR-052 | Filter Kost by Price Range | Sistem harus memungkinkan Tenant memfilter kost berdasarkan rentang harga (min - max). | Must | Given Tenant set filter harga min-max, When apply filter, Then hanya kost dengan price scheme dalam rentang tersebut yang ditampilkan. | US-015 | Draft |
| FR-053 | Filter Kost by Category | Sistem harus memungkinkan Tenant memfilter kost berdasarkan kategori. | Must | Given Tenant pilih kategori, When apply filter, Then hanya kost yang ter-assign kategori tersebut yang ditampilkan. | US-015 | Draft |
| FR-054 | Filter Kost by Rating | Sistem harus memungkinkan Tenant memfilter kost berdasarkan rating minimum. | Should | Given Tenant set filter rating min, When apply, Then hanya kost dengan avg rating ≥ min yang ditampilkan. | US-015 | Draft |
| FR-055 | Combine Search and Filters | Sistem harus mengkombinasikan kriteria search dan filters untuk menampilkan hasil yang sesuai. | Must | Given Tenant input keyword dan pilih filters, When apply, Then hasil harus match semua kriteria yang dipilih (AND logic). | US-015 | Draft |
| FR-056 | Display Empty State | Sistem harus menampilkan pesan yang jelas ketika tidak ada kost yang match dengan kriteria. | Should | Given tidak ada kost match, When render, Then tampilkan "Tidak ada kost ditemukan". | US-015 | Draft |
| FR-057 | View Kost Detail | Sistem harus menampilkan detail lengkap kost: info kost, alamat, gambar galeri, kategori, facilities, rules, document requirements, room types, price schemes, reviews, map. | Must | Given Tenant klik kost item, When load detail page, Then semua informasi kost ditampilkan lengkap. | US-016 | Draft |
| FR-058 | Display Kost Location on Map | Sistem harus menampilkan lokasi kost di map (Leaflet + OpenStreetMap) berdasarkan latitude & longitude. | Must | Given kost memiliki lat/long valid, When Tenant buka detail kost, Then map menampilkan marker di lokasi kost. | US-016 | Draft |
| FR-059 | Display Room Types and Pricing | Sistem harus menampilkan list Room Types available di kost beserta price schemes aktif per room type. | Must | Given kost memiliki room types, When Tenant buka detail, Then room types ditampilkan dengan harga-harga yang available. | US-016 | Draft |
| FR-060 | Display Reviews and Ratings | Sistem harus menampilkan reviews yang ada untuk kost dan room di detail kost, beserta gambar review. | Should | Given kost memiliki reviews, When Tenant buka detail, Then reviews ditampilkan dengan rating, comment, gambar, reviewer info. | US-016 | Draft |

### Rental Lifecycle - Booking & Payment (FR-061 — FR-082)

| ID | Judul | Deskripsi | Prioritas | Acceptance Criteria | User Story Terkait | Status |
|---|---|---|---|---|---|---|
| FR-061 | Tenant Must Login to Create Rental | Sistem harus mewajibkan Tenant login sebelum dapat membuat rental. | Must | Given visitor belum login, When mencoba create rental, Then redirect ke login page. | US-010 | Draft |
| FR-062 | Tenant Must Verify Email to Create Rental | Sistem harus mencegah Tenant yang belum verify email membuat rental. | Must | Given Tenant login tapi email belum verified, When mencoba create rental, Then sistem tolak dan minta verify email dulu. | US-010 | Draft |
| FR-063 | Create Rental - Select Room Type and Price Scheme | Sistem harus memungkinkan Tenant memilih Room Type dan Price Scheme aktif dari kost. | Must | Given Tenant buka detail kost, When pilih room type dan price scheme valid, Then sistem tampilkan room available. | US-010 | Draft |
| FR-064 | Create Rental - Select Available Room | Sistem harus menampilkan hanya Room berstatus `available` untuk Room Type yang dipilih. | Must | Given Tenant pilih room type, When sistem load room list, Then hanya room dengan status `available` yang ditampilkan. | US-010 | Draft |
| FR-065 | Create Rental - Specify Duration | Sistem harus memungkinkan Tenant menentukan durasi rental berdasarkan unit durasi dari Price Scheme. | Must | Given Tenant pilih price scheme dengan duration_unit = month, When input duration value (misal: 3), Then sistem calculate total = price × 3 + security deposit. | US-010 | Draft |
| FR-066 | Create Rental - Calculate Total Cost | Sistem harus menghitung total biaya rental: (price × duration value) + security deposit. | Must | Given Tenant input duration, When sistem calculate, Then grand_total ditampilkan ke Tenant sebelum konfirmasi. | US-010 | Draft |
| FR-067 | Create Rental - Save Rental Record | Sistem harus membuat record Rental baru dengan status `Pending`, menyimpan snapshot data. | Must | Given Tenant konfirmasi create rental, When submit, Then rental record tersimpan dengan status `Pending` dan snapshot data. Room status berubah `reserved`. | US-010 | Draft |
| FR-068 | Set Payment Deadline | Sistem harus menetapkan payment deadline saat rental dibuat. | Must | Given rental created, When sistem buat payment record, Then expired_at = created_at + 48 jam. | US-010 | Draft |
| FR-069 | Display QRIS and Bank Info to Tenant | Sistem harus menampilkan QRIS static image dan informasi rekening kost ke Tenant saat payment page. | Must | Given rental Pending, When Tenant buka payment page, Then QRIS image, bank info, dan total amount ditampilkan. | US-011 | Draft |
| FR-070 | Upload Proof of Payment | Sistem harus memungkinkan Tenant mengupload bukti pembayaran untuk rental Pending. | Must | Given Tenant sudah transfer, When upload image file valid, Then file tersimpan dan status payment tetap `pending` (menunggu verifikasi Admin). | US-011 | Draft |
| FR-071 | Admin View Payment Proof | Sistem harus menampilkan list rental dengan payment pending verification ke Admin. | Must | Given payment dengan proof uploaded, When Admin buka rental management, Then Admin dapat lihat rental yang butuh verifikasi payment. | US-012 | Draft |
| FR-072 | Admin Verify Payment - Approve | Sistem harus memungkinkan Admin approve payment setelah verifikasi manual bukti transfer. | Must | Given Admin review proof valid, When approve payment, Then payments.status = `success`, rentals.status = `Paid`. | US-012 | Draft |
| FR-073 | Admin Verify Payment - Reject with Reason | Sistem harus mewajibkan Admin memberikan alasan penolakan saat reject payment. | Must | Given Admin review proof tidak valid, When reject, Then sistem wajib Admin input rejection_reason. Tenant dapat lihat alasan dan upload ulang. | US-012 | Draft |
| FR-074 | Display Payment Rejection Reason to Tenant | Sistem harus menampilkan alasan penolakan payment ke Tenant. | Must | Given payment rejected dengan alasan, When Tenant buka rental detail, Then rejection_reason ditampilkan. | US-011 | Draft |
| FR-075 | Re-upload Proof of Payment | Sistem harus memungkinkan Tenant upload ulang proof of payment setelah rejected. | Must | Given payment rejected, When Tenant upload proof baru, Then proof lama di-replace, rejection_reason di-clear, status kembali `pending`. | US-011 | Draft |
| FR-076 | Payment Deadline Monitoring | Sistem harus memantau payment deadline. Jika expired_at terlewati dan payment belum `success`, rental otomatis `Cancelled`. | Must | Given payment deadline terlewati, When sistem check, Then jika payment bukan `success`, set rentals.status = `Cancelled` dan room status kembali `available`. | US-011 | Draft |
| FR-077 | Prevent Rental Progress without Payment Success | Sistem harus mencegah rental melanjutkan lifecycle jika payment belum `success`. | Must | Given rental dengan payment belum success, When Tenant mencoba upload dokumen, Then sistem tolak. | US-011 | Draft |
| FR-078 | Record Payment Transaction | Sistem harus mencatat semua informasi payment transaction. | Must | Given payment flow terjadi, When status berubah, Then semua field payment ter-update dengan benar. | US-011, US-012 | Draft |
| FR-079 | Create Payment Record on Rental Creation | Sistem harus otomatis membuat payment record dengan status `pending` saat rental dibuat. | Must | Given rental created, When sistem simpan rental, Then payment record juga dibuat dengan status `pending`, expired_at = created_at + 48 jam. | US-010 | Draft |
| FR-080 | One Payment per Rental | Sistem harus memastikan satu rental hanya memiliki satu payment record (1:1). | Must | Given rental exists, When sistem check payments, Then hanya ada 1 payment record untuk rental tersebut. | US-010 | Draft |
| FR-081 | Update Room Status on Payment Success | Sistem harus mengupdate room status saat payment success. | Should | Given payment approved, When rentals.status = `Paid`, Then rooms.status tetap `reserved` atau berubah `occupied` (sesuai business rule). | US-012 | Draft |
| FR-082 | Notify Tenant on Payment Verification | Sistem harus mengirim email notification ke Tenant saat payment approved atau rejected. | Should | Given Admin verify payment, When status berubah, Then email dikirim ke Tenant dengan info hasil verifikasi. | US-011, US-012 | Draft |

### Rental Lifecycle - Document Verification (FR-083 — FR-095)

| ID | Judul | Deskripsi | Prioritas | Acceptance Criteria | User Story Terkait | Status |
|---|---|---|---|---|---|---|
| FR-083 | Display Document Requirements to Tenant | Sistem harus menampilkan list dokumen yang dibutuhkan untuk rental ke Tenant setelah payment success. | Must | Given rental berstatus `Paid`, When Tenant buka rental detail, Then list dokumen wajib & opsional ditampilkan beserta alasan. | US-013 | Draft |
| FR-084 | Upload Rental Document | Sistem harus memungkinkan Tenant mengupload dokumen administrasi rental sesuai dengan requirement kost. | Must | Given rental Paid, When Tenant upload file untuk jenis dokumen tertentu, Then rental_documents record dibuat dengan verification_status = `pending`. | US-013 | Draft |
| FR-085 | Validate Document File Type and Size | Sistem harus memvalidasi dokumen yang diupload Tenant: hanya image (jpg, png, pdf), max size 5MB. | Must | Given Tenant upload file, When sistem validasi, Then jika tipe/size tidak valid, upload ditolak. | US-013 | Draft |
| FR-086 | Display Document Upload Status to Tenant | Sistem harus menampilkan status upload dan verifikasi per dokumen ke Tenant. | Must | Given Tenant upload dokumen, When Tenant buka rental detail, Then status setiap dokumen ditampilkan. | US-013 | Draft |
| FR-087 | Admin View Submitted Documents | Sistem harus menampilkan list rental dengan dokumen pending verification ke Admin. | Must | Given dokumen uploaded, When Admin buka rental management, Then Admin dapat lihat rental yang butuh verifikasi dokumen. | US-014 | Draft |
| FR-088 | Admin Verify Document - Approve | Sistem harus memungkinkan Admin approve dokumen rental setelah review. | Must | Given Admin review dokumen valid, When approve, Then rental_documents.verification_status = `approved`. | US-014 | Draft |
| FR-089 | Admin Verify Document - Reject with Reason | Sistem harus mewajibkan Admin memberikan alasan penolakan saat reject dokumen rental. | Must | Given Admin review dokumen tidak valid, When reject, Then sistem wajib Admin input rejection_reason. Tenant dapat lihat alasan dan upload ulang. | US-014 | Draft |
| FR-090 | Display Document Rejection Reason to Tenant | Sistem harus menampilkan alasan penolakan dokumen per-dokumen ke Tenant. | Must | Given dokumen rejected dengan alasan, When Tenant buka rental detail, Then rejection_reason untuk dokumen tersebut ditampilkan. | US-013 | Draft |
| FR-091 | Re-upload Rejected Document | Sistem harus memungkinkan Tenant upload ulang dokumen yang rejected. | Must | Given dokumen rejected, When Tenant upload file baru, Then status kembali `pending`. | US-013 | Draft |
| FR-092 | Check All Required Documents Approved | Sistem harus memeriksa apakah semua dokumen wajib sudah approved sebelum rental dapat Confirmed. | Must | Given rental dengan dokumen uploaded, When sistem check, Then jika semua dokumen wajib approved, rental dapat transition ke `Confirmed`. | US-013, US-014 | Draft |
| FR-093 | Transition Rental to Confirmed | Sistem harus mengubah rental status dari `Paid` ke `Confirmed` setelah semua dokumen wajib approved. | Must | Given semua dokumen wajib approved, When Admin approve dokumen terakhir yang wajib, Then rentals.status = `Confirmed` dan Tenant di-notify. | US-014 | Draft |
| FR-094 | Prevent Rental Activation without Document Confirmation | Sistem harus mencegah rental menjadi `Active` jika belum `Confirmed`. | Must | Given rental belum Confirmed, When contract start date tercapai, Then sistem tidak mengubah status ke Active. | US-014 | Draft |
| FR-095 | Notify Tenant on Document Verification | Sistem harus mengirim email notification ke Tenant saat dokumen approved atau rejected. | Should | Given Admin verify dokumen, When status berubah, Then email dikirim ke Tenant. | US-013, US-014 | Draft |

### Rental Lifecycle - Monitoring & Completion (FR-096 — FR-104)

| ID | Judul | Deskripsi | Prioritas | Acceptance Criteria | User Story Terkait | Status |
|---|---|---|---|---|---|---|
| FR-096 | Tenant View Own Rentals | Sistem harus memungkinkan Tenant melihat list rental miliknya (all statuses). | Must | Given Tenant login, When buka rental list, Then semua rental Tenant ditampilkan. | US-015 | Draft |
| FR-097 | Tenant View Rental Detail | Sistem harus memungkinkan Tenant melihat detail rental miliknya: info lengkap, payment, documents, status history. | Must | Given Tenant pilih rental, When buka detail, Then semua informasi rental ditampilkan lengkap. | US-015 | Draft |
| FR-098 | Admin View Rentals for Own Kost | Sistem harus memungkinkan Admin melihat list rental untuk kost yang dikelolanya. | Must | Given Admin login, When buka rental management, Then rental untuk kost Admin ditampilkan. | US-016 | Draft |
| FR-099 | Admin View Rental Detail | Sistem harus memungkinkan Admin melihat detail rental untuk kost miliknya. | Must | Given Admin pilih rental dari kost miliknya, When buka detail, Then semua info rental ditampilkan. | US-016 | Draft |
| FR-100 | Record Rental Status History | Sistem harus mencatat setiap perubahan status rental di rental_status_histories. | Must | Given rental status berubah, When transition terjadi, Then rental_status_histories record dibuat. | US-015, US-016 | Draft |
| FR-101 | Activate Rental on Contract Start Date | Sistem harus otomatis mengubah rental status dari `Confirmed` ke `Active` saat contract start date tercapai. | Must | Given rental Confirmed dan contract_start_date <= now, When sistem check, Then rentals.status = `Active`. | US-017 | Draft |
| FR-102 | Complete Rental on Contract End Date | Sistem harus otomatis mengubah rental status dari `Active` ke `Completed` saat contract end date tercapai. | Must | Given rental Active dan contract_end_date <= now, When sistem check, Then rentals.status = `Completed`, room.status = `available`. | US-017 | Draft |
| FR-103 | Display Rental Timeline to User | Sistem harus menampilkan rental timeline/status history ke Tenant dan Admin. | Should | Given rental memiliki status history, When user buka rental detail, Then timeline ditampilkan. | US-015, US-016 | Draft |
| FR-104 | Prevent Manual Status Change to Active/Completed | Sistem harus mencegah Admin atau Tenant mengubah rental status ke `Active` atau `Completed` secara manual. | Must | Given user mencoba ubah status rental manual, When sistem validasi, Then jika target status = Active/Completed, operasi ditolak. | US-016 | Draft |

### Review Management (FR-105 — FR-110)

| ID | Judul | Deskripsi | Prioritas | Acceptance Criteria | User Story Terkait | Status |
|---|---|---|---|---|---|---|
| FR-105 | Review Eligibility Check | Sistem harus memastikan hanya Tenant dengan rental `Completed` yang dapat submit review. | Must | Given Tenant, When mencoba submit review, Then sistem check: rental status = Completed, rental milik Tenant, belum ada review. | US-018 | Draft |
| FR-106 | Submit Review | Sistem harus memungkinkan Tenant submit review untuk rental Completed dengan: kost_rating (1-5, optional), kost_comment, room_rating (1-5, optional), room_comment. Minimal salah satu rating harus diisi. | Must | Given rental Completed, When Tenant submit review dengan minimal 1 rating valid, Then review record dibuat. | US-018 | Draft |
| FR-107 | Upload Review Images | Sistem harus memungkinkan Tenant mengupload gambar review. Images disimpan sebagai JSON array di kolom `reviews.images`. | Should | Given Tenant submit review, When upload image files valid, Then images tersimpan sebagai JSON array. | US-018 | Draft |
| FR-108 | Validate Review Rating | Sistem harus memvalidasi rating review: nilai harus 1-5, minimal salah satu rating harus diisi. | Must | Given Tenant input rating, When submit, Then sistem validasi: rating dalam range 1-5, minimal kost_rating atau room_rating diisi. | US-018 | Draft |
| FR-109 | Display Reviews in Kost Detail | Sistem harus menampilkan reviews di halaman detail kost marketplace. | Should | Given kost memiliki reviews, When visitor/Tenant buka detail kost, Then reviews ditampilkan dengan info lengkap. | US-016 | Draft |
| FR-110 | Calculate Average Ratings | Sistem harus menghitung rating rata-rata kost dan room berdasarkan reviews yang ada. | Should | Given kost memiliki reviews, When sistem calculate, Then avg ratings dihitung dan digunakan untuk display & filter. | US-015, US-016 | Draft |

### Administration - Admin Account Management (FR-111 — FR-116)

| ID | Judul | Deskripsi | Prioritas | Acceptance Criteria | User Story Terkait | Status |
|---|---|---|---|---|---|---|
| FR-111 | Super Admin Create Admin Account | Sistem harus memungkinkan Super Admin membuat akun Admin baru. | Must | Given Super Admin input data Admin valid, When create account, Then user record dibuat dengan role `admin`, password di-hash, email verification dikirim. | US-019 | Draft |
| FR-112 | Validate Admin Account Data | Sistem harus memvalidasi data akun Admin sebelum dibuat. | Must | Given Super Admin input data, When submit, Then sistem validasi: email unique, password valid, name tidak kosong. | US-019 | Draft |
| FR-113 | Send Admin Account Notification | Sistem harus mengirim email notification ke Admin baru. | Must | Given Admin account created, When sistem simpan, Then email dikirim ke Admin dengan info akun dan instruksi aktivasi. | US-019 | Draft |
| FR-114 | Super Admin View Admin Accounts | Sistem harus menampilkan list akun Admin yang terdaftar ke Super Admin. | Must | Given Super Admin login, When buka admin management, Then list semua user dengan role `admin` ditampilkan. | US-020 | Draft |
| FR-115 | Super Admin Update Admin Account | Sistem harus memungkinkan Super Admin mengupdate informasi akun Admin. | Must | Given Super Admin pilih Admin account, When update info valid, Then perubahan tersimpan. Email & role tidak editable. | US-020 | Draft |
| FR-116 | Super Admin Soft Delete Admin Account | Sistem harus memungkinkan Super Admin soft delete akun Admin. | Must | Given Super Admin pilih Admin account, When soft delete, Then users.deleted_at di-set, Admin tidak dapat login lagi. | US-020 | Draft |

### Administration - Category Management (FR-117 — FR-120)

| ID | Judul | Deskripsi | Prioritas | Acceptance Criteria | User Story Terkait | Status |
|---|---|---|---|---|---|---|
| FR-117 | Super Admin Create Category | Sistem harus memungkinkan Super Admin membuat kategori kost baru. | Must | Given Super Admin input data kategori valid, When create, Then category record dibuat dan tersedia untuk di-assign Admin. | US-021 | Draft |
| FR-118 | Super Admin Update Category | Sistem harus memungkinkan Super Admin mengupdate kategori. | Must | Given Super Admin pilih kategori, When update data valid, Then perubahan tersimpan. | US-021 | Draft |
| FR-119 | Super Admin Soft Delete Category | Sistem harus memungkinkan Super Admin soft delete kategori. | Must | Given Super Admin soft delete kategori, When deleted_at di-set, Then kategori tidak muncul di dropdown Admin, namun kost yang sudah pakai tetap valid. | US-021 | Draft |
| FR-120 | Prevent Admin CRUD Category | Sistem harus mencegah Admin membuat, mengupdate, atau menghapus kategori. | Must | Given Admin login, When mencoba akses category management, Then akses ditolak. | US-004, US-009 | Draft |

### Additional Rules from Open Questions Resolution (FR-121 — FR-127)

| ID | Judul | Deskripsi | Prioritas | Acceptance Criteria | User Story Terkait | Status |
|---|---|---|---|---|---|---|
| FR-121 | Payment Deadline 48 Hours | Sistem harus menetapkan payment deadline = 48 jam dari rental created_at. | Must | Given rental created, When sistem buat payment record, Then payments.expired_at = rentals.created_at + 48 hours. | US-010 | Draft |
| FR-122 | Tenant Select Contract Start Date | Sistem harus memungkinkan Tenant memilih contract start date saat create rental. Min: today+4 hari, Max: today + 30 hari. | Must | Given Tenant create rental, When Tenant input start date valid (>= today+4 hari, <= today+30 hari), Then start date tersimpan di rentals.start_date. Contract end date = start_date + duration. Min 4 hari untuk memberikan waktu verifikasi payment dan dokumen. | US-010 | Draft |
| FR-123 | Tenant Manual Cancel Rental | Sistem harus memungkinkan Tenant cancel rental miliknya secara manual (dari status apapun termasuk Active). | Must | Given rental milik Tenant dengan status Pending/Paid/Confirmed/Active, When Tenant pilih "Cancel Rental" dan konfirmasi dengan input reason, Then rentals.status = `Cancelled`, rentals.cancelled_reason diisi, rentals.cancelled_at di-set, room occupancy slot dikembalikan (free_slots bertambah). | US-022 | Draft |
| FR-124 | Prevent Cancel Completed Rental | Sistem harus mencegah Tenant cancel rental yang sudah Completed. | Must | Given rental Completed, When Tenant mencoba cancel, Then sistem tolak dan tampilkan pesan "Rental yang sudah selesai tidak dapat dibatalkan". | US-022 | Draft |
| FR-125 | No Refund Handling in System | Sistem tidak menangani proses refund. Jika Tenant cancel setelah payment success, Tenant harus nego refund langsung dengan Admin kost. | Must | Given Tenant cancel rental dengan payment success, When rental cancelled, Then sistem hanya ubah status, tidak ada mekanisme refund otomatis. | US-022 | Draft |
| FR-126 | Auto-Cancel if Document Not Completed Before Start Date | Sistem harus otomatis cancel rental jika Tenant tidak menyelesaikan document verification sebelum contract start date. | Must | Given rental berstatus Paid dan contract_start_date <= now, When scheduled job check, Then rentals.status = `Cancelled`, cancelled_reason = "Dokumen tidak dilengkapi sebelum start date". | US-017 | Draft |
| FR-127 | Display Cancellation Info | Sistem harus menampilkan info cancellation (alasan, timestamp) di rental detail. | Should | Given rental berstatus Cancelled, When Tenant/Admin buka rental detail, Then cancelled_reason dan cancelled_at ditampilkan. | US-021, US-022 | Draft |
| FR-128 | OTP Expiry Time | Sistem harus menetapkan expiry time OTP = 15 menit. Jika OTP expired, user harus request OTP baru. | Must | Given OTP dikirim, When 15 menit berlalu tanpa input, Then OTP expired dan user harus request OTP baru untuk verifikasi. | US-002 | Draft |
| FR-129 | Email Change Re-verification | Sistem harus mewajibkan re-verification via OTP saat user mengubah email. Email baru belum dapat digunakan untuk fitur yang membutuhkan email terverifikasi sampai OTP baru di-input benar. | Must | Given user mengubah email, When email baru disimpan, Then sistem kirim OTP ke email baru. Email lama tetap valid sampai email baru terverifikasi. Fitur yang membutuhkan email terverifikasi (misal: create rental) tetap blocked sampai email baru terverifikasi. | US-003 | Draft |

### Authentication - Password Reset (FR-130)

| ID | Judul | Deskripsi | Prioritas | Acceptance Criteria | User Story Terkait | Status |
|---|---|---|---|---|---|---|
| FR-130 | Password Reset via OTP | Sistem harus memungkinkan user reset password via OTP 6 digit yang dikirim ke email terdaftar. | Must | Given user lupa password dan input email, When sistem kirim OTP reset, Then user verify OTP dan dapat set password baru; email yang tidak terdaftar tidak mengungkap status (anti-enumeration). | US-001 | Draft |

---

## 8. Non-Functional Requirements (NFR-xxx)

> Kategori wajib dipertimbangkan: Performance, Security, Scalability, Reliability/Availability, Usability/Accessibility, Maintainability, Compliance/Legal.

### Performance (NFR-001 — NFR-003)

| ID | Kategori | Deskripsi | Target Terukur | Prioritas |
|---|---|---|---|---|
| NFR-001 | Performance | Waktu respons operasi umum marketplace dan administrasi harus acceptable | Operasi CRUD dasar selesai < 3 detik. Page load marketplace < 2 detik untuk 20 kost items. | Must |
| NFR-002 | Performance | Sistem harus menghindari N+1 query problem | Query database menggunakan eager loading. Monitoring query count via Laravel Debugbar/Telescope. | Must |
| NFR-003 | Performance | Integrasi eksternal tidak boleh menyebabkan proses bisnis menggantung | Email sending menggunakan queue (async). Maps API load timeout max 5 detik dengan fallback. | Must |

### Security (NFR-004 — NFR-010)

| ID | Kategori | Deskripsi | Target Terukur | Prioritas |
|---|---|---|---|---|
| NFR-004 | Security | Autentikasi user sebelum mengakses fungsi yang memerlukan login | Route yang butuh auth menggunakan middleware `auth`. | Must |
| NFR-005 | Security | RBAC untuk membatasi akses fungsi berdasarkan role | Middleware role check di route yang spesifik per role. | Must |
| NFR-006 | Security | Password user harus di-hash dengan algoritma secure | Laravel default password hashing (bcrypt). Password tidak pernah di-return atau di-log. | Must |
| NFR-007 | Security | Validasi semua input user untuk mencegah injection attacks | Eloquent ORM untuk query. Blade auto-escape. CSRF token di semua form. Form Request validation. | Must |
| NFR-008 | Security | File upload harus divalidasi: type, size, dan disimpan di storage aman | Validasi: mimes (jpeg, png, pdf), max 5MB dokumen, 2MB images. Generated filename. Private storage untuk dokumen rental. | Must |
| NFR-009 | Security | Secret dan credential harus di environment variable (.env), tidak di source code | .env tidak di-commit. .env.example di-commit sebagai template. | Must |
| NFR-010 | Security | Mencegah akses langsung ke resource yang bukan kewenangan user | Policy/Gate authorization check di setiap operasi CRUD resource. Return 403 jika tidak berwenang. | Must |

### Reliability & Availability (NFR-011 — NFR-014)

| ID | Kategori | Deskripsi | Target Terukur | Prioritas |
|---|---|---|---|---|
| NFR-011 | Reliability | Konsistensi data ketika proses bisnis gagal | Operasi atomic dibungkus dalam database transaction. Rollback jika salah satu step gagal. | Must |
| NFR-012 | Reliability | Rental status history untuk audit trail | Setiap perubahan rental status tercatat di `rental_status_histories`. | Must |
| NFR-013 | Reliability | Kegagalan Email Service tidak boleh menyebabkan operasi bisnis gagal | Email sending via queue (async). Jika email gagal, log error tapi tidak rollback transaksi bisnis. | Must |
| NFR-014 | Availability | Sistem harus dapat diakses 24/7 kecuali maintenance terjadwal | Process manager (PM2/Supervisor) untuk auto-restart. Maintenance window dikomunikasikan. | Should |

### Maintainability (NFR-015 — NFR-018)

| ID | Kategori | Deskripsi | Target Terukur | Prioritas |
|---|---|---|---|---|
| NFR-015 | Maintainability | Separation of concerns: Controller tipis, business logic di Service/Action class | Controller hanya handle request/response dan call service. Form Request untuk validation. | Must |
| NFR-016 | Maintainability | Dependency dikelola dengan version control yang reproducible | composer.lock dan package-lock.json di-commit. Dependency version pinned. | Must |
| NFR-017 | Maintainability | Database schema changes harus melalui migration | Semua perubahan schema via Laravel migration. Production di-migrate via `php artisan migrate`. | Must |
| NFR-018 | Maintainability | Konfigurasi environment-specific dipisahkan dari code via .env | Semua config environment-specific di .env. Code tidak hardcode value production. | Must |

### Scalability (NFR-019 — NFR-020)

| ID | Kategori | Deskripsi | Target Terukur | Prioritas |
|---|---|---|---|---|
| NFR-019 | Scalability | Struktur database dapat menampung volume data MVP | Handle minimal: 100 kost, 500 rooms, 1000 rentals, 500 reviews tanpa degradation. Indexing di foreign keys, status, created_at. | Must |
| NFR-020 | Scalability | Komponen eksternal dapat diganti tanpa mengubah business logic | Email sending menggunakan Laravel Mail facade. Ganti SMTP provider hanya ubah .env. | Should |

### Usability (NFR-021 — NFR-023)

| ID | Kategori | Deskripsi | Target Terukur | Prioritas |
|---|---|---|---|---|
| NFR-021 | Usability | Feedback jelas untuk setiap operasi penting | Loading indicator, success/error message (toast/alert). Error message user-friendly. | Must |
| NFR-022 | Usability | Alur proses utama intuitif tanpa langkah ambigu | Rental flow dapat diselesaikan tanpa manual/panduan. Status rental ditampilkan dengan progress indicator. | Must |
| NFR-023 | Usability | Responsive design untuk desktop, tablet, smartphone | CSS framework responsive. Fungsi utama dapat digunakan di smartphone. | Must |

### Accessibility (NFR-024 — NFR-025)

| ID | Kategori | Deskripsi | Target Terukur | Prioritas |
|---|---|---|---|---|
| NFR-024 | Accessibility | Form input memiliki label jelas dan dapat diakses via keyboard | Semua input field memiliki `<label>`. Tab order logis. Button dapat di-trigger via keyboard. | Should |
| NFR-025 | Accessibility | Status dan error message tidak boleh hanya mengandalkan warna | Error message sebagai text. Success/error menggunakan icon + text. | Should |

### Backup & Recovery (NFR-026 — NFR-028)

| ID | Kategori | Deskripsi | Target Terukur | Prioritas |
|---|---|---|---|---|
| NFR-026 | Backup | Database di-backup secara terjadwal (daily) | Backup script/cron job. Backup disimpan dengan timestamp. Retention: 7 hari. | Must |
| NFR-027 | Backup | File storage ter-include dalam backup | Folder `storage/app` di-backup bersama database. Restore DB + file konsisten. | Must |
| NFR-028 | Recovery | Prosedur restore database dan file dari backup | Dokumentasi restore procedure tersedia. Restore procedure ditest minimal sekali. | Must |

### Logging & Monitoring (NFR-029 — NFR-031)

| ID | Kategori | Deskripsi | Target Terukur | Prioritas |
|---|---|---|---|---|
| NFR-029 | Logging | Log event penting untuk troubleshooting dan audit | Laravel log ke `storage/logs/laravel.log` dengan level INFO untuk business events, ERROR untuk failures. | Must |
| NFR-030 | Logging | Log tidak boleh menyimpan data sensitif | Log hanya menyimpan identifier (user_id, rental_id). Tidak log password, image binary, PII. | Must |
| NFR-031 | Monitoring | Kegagalan critical dapat diidentifikasi via log | Error log level ERROR untuk critical failures. Log dapat di-filter berdasarkan level. | Should |

### Legal & Compliance (NFR-032 — NFR-033)

| ID | Kategori | Deskripsi | Target Terukur | Prioritas |
|---|---|---|---|---|
| NFR-032 | Compliance | Data pribadi user harus dilindungi dari akses tidak berwenang | Data pribadi hanya accessible oleh user owner dan Admin/Super Admin yang berwenang. Dokumen rental di private storage. | Must |
| NFR-033 | Compliance | Data historis rental, payment, review harus dipertahankan sesuai lifecycle | Rental, payment, rental_status_histories, reviews tidak di-hard delete. Soft delete user tidak menghilangkan data historis. | Must |

---

## 9. User Stories (US-001 — US-022)

> Format: **Sebagai** `<persona>`, **saya ingin** `<tujuan>`, **agar** `<manfaat>`.
> Setiap US-xxx wajib merujuk ke minimal satu Persona (§4) dan minimal satu FR-xxx (§7).

### US-001: User Authentication
- **Sebagai** User (Tenant/Admin/Super Admin)
- **Saya ingin** login dengan email dan password, serta logout saat selesai
- **Agar** saya dapat mengakses fungsi sesuai role saya dan data saya terlindungi
- **Terkait FR:** FR-001, FR-002, FR-007, FR-008

### US-002: Tenant Registration & Email Verification
- **Sebagai** Calon Tenant (Persona P-01)
- **Saya ingin** mendaftar akun baru dengan email dan verify email saya
- **Agar** saya dapat mulai mencari kost dan membuat booking
- **Terkait FR:** FR-003, FR-004, FR-005, FR-006

### US-003: Manage User Profile
- **Sebagai** User (Tenant/Admin/Super Admin)
- **Saya ingin** melihat dan mengupdate profil saya, serta menghapus akun jika perlu
- **Agar** informasi profil saya tetap up-to-date dan saya punya kontrol atas akun saya
- **Terkait FR:** FR-009, FR-010, FR-011, FR-012, FR-013

### US-004: Admin Kelola Kost dan Konfigurasi
- **Sebagai** Admin Kost (Persona P-02)
- **Saya ingin** membuat dan mengelola data kost saya (info, alamat, gambar, kategori, facilities, rules, QRIS, requirement dokumen)
- **Agar** kost saya dapat dipublikasikan dengan informasi lengkap dan valid
- **Terkait FR:** FR-014, FR-015, FR-024—FR-035

### US-005: Admin Kelola Room Type, Pricing, dan Room Inventory
- **Sebagai** Admin Kost (Persona P-02)
- **Saya ingin** membuat room type, mengatur harga sewa, dan mengelola room unit fisik
- **Agar** Tenant dapat melihat pilihan kamar dan harga yang available untuk booking
- **Terkait FR:** FR-036—FR-047

### US-006: Super Admin Review Kost Submission
- **Sebagai** Super Admin (Persona P-03)
- **Saya ingin** mereview pengajuan kost dari Admin dan memberikan keputusan approve atau reject dengan alasan
- **Agar** hanya kost yang valid dan sesuai standar platform yang dapat dipublikasikan
- **Terkait FR:** FR-018, FR-019

### US-007: Admin Revisi Kost yang Rejected
- **Sebagai** Admin Kost (Persona P-02)
- **Saya ingin** melihat alasan penolakan kost saya dan merevisi data kost tersebut
- **Agar** saya dapat memperbaiki masalah dan submit ulang untuk review
- **Terkait FR:** FR-020

### US-008: Admin Publish Kost yang Approved
- **Sebagai** Admin Kost (Persona P-02)
- **Saya ingin** mempublikasikan kost yang sudah diapprove Super Admin
- **Agar** kost saya muncul di marketplace dan dapat dilihat calon penyewa
- **Terkait FR:** FR-021, FR-022, FR-023

### US-009: Super Admin Kelola Kategori Kost
- **Sebagai** Super Admin (Persona P-03)
- **Saya ingin** membuat, mengupdate, dan mengelola master kategori kost
- **Agar** Admin dapat memilih kategori standar yang konsisten untuk kost mereka
- **Terkait FR:** FR-027, FR-117, FR-118, FR-119, FR-120

### US-010: Tenant Booking Kamar
- **Sebagai** Tenant (Persona P-01)
- **Saya ingin** memilih room type, harga, kamar available, dan tanggal mulai sewa lalu membuat booking
- **Agar** saya dapat memesan kamar yang saya inginkan dan lanjut ke proses pembayaran
- **Terkait FR:** FR-061—FR-068, FR-079, FR-080, FR-121, FR-122

### US-011: Tenant Bayar via QRIS dan Upload Bukti
- **Sebagai** Tenant (Persona P-01)
- **Saya ingin** melihat QRIS kost, transfer manual, lalu upload bukti pembayaran
- **Agar** Admin dapat verifikasi pembayaran saya dan rental saya dapat dilanjutkan
- **Terkait FR:** FR-069—FR-078, FR-082

### US-012: Admin Verifikasi Pembayaran QRIS
- **Sebagai** Admin Kost (Persona P-02)
- **Saya ingin** melihat bukti pembayaran yang diupload Tenant dan memberikan keputusan approve/reject dengan alasan
- **Agar** saya dapat memastikan pembayaran sesuai dengan data transfer di rekening saya
- **Terkait FR:** FR-071—FR-074, FR-078, FR-082

### US-013: Tenant Upload Dokumen Administrasi
- **Sebagai** Tenant (Persona P-01)
- **Saya ingin** melihat dokumen apa yang dibutuhkan kost dan upload dokumen saya
- **Agar** Admin dapat verifikasi identitas saya dan rental saya dapat dikonfirmasi
- **Terkait FR:** FR-083—FR-086, FR-090, FR-091, FR-095

### US-014: Admin Verifikasi Dokumen Rental
- **Sebagai** Admin Kost (Persona P-02)
- **Saya ingin** melihat dokumen yang diupload Tenant dan memberikan keputusan approve/reject per dokumen dengan alasan
- **Agar** saya dapat memastikan identitas dan kelengkapan administrasi Tenant
- **Terkait FR:** FR-087—FR-095

### US-015: Tenant Browse dan Search Kost di Marketplace
- **Sebagai** Tenant (Persona P-01)
- **Saya ingin** melihat daftar kost, mencari berdasarkan nama/lokasi, dan memfilter berdasarkan harga/kategori/rating
- **Agar** saya dapat menemukan kost yang sesuai dengan kebutuhan dan budget saya
- **Terkait FR:** FR-048—FR-056

### US-016: Tenant View Detail Kost
- **Sebagai** Tenant (Persona P-01)
- **Saya ingin** melihat informasi lengkap kost (alamat, gambar, facilities, rules, room types, harga, document requirements, reviews, map)
- **Agar** saya dapat mengevaluasi apakah kost ini sesuai dengan kebutuhan saya sebelum booking
- **Terkait FR:** FR-057—FR-060, FR-035

### US-017: Sistem Monitoring Rental Lifecycle
- **Sebagai** Sistem (otomatis)
- **Saya ingin** memantau rental dan mengubah status sesuai timeline (payment deadline → Cancelled, document incomplete → Cancelled, contract start → Active, contract end → Completed)
- **Agar** rental lifecycle berjalan otomatis tanpa manual intervention
- **Terkait FR:** FR-076, FR-101, FR-102, FR-126

### US-018: Tenant Submit Review Setelah Rental Completed
- **Sebagai** Tenant (Persona P-01)
- **Saya ingin** memberikan review (rating & comment) untuk kost dan/atau room setelah masa sewa selesai, serta upload gambar review
- **Agar** saya dapat berbagi pengalaman saya dan membantu calon penyewa lain
- **Terkait FR:** FR-105—FR-110

### US-019: Super Admin Create Admin Account
- **Sebagai** Super Admin (Persona P-03)
- **Saya ingin** membuat akun Admin baru setelah calon Admin melalui verifikasi manual
- **Agar** Admin yang terverifikasi dapat mulai mengelola kost mereka di platform
- **Terkait FR:** FR-111—FR-113

### US-020: Super Admin Kelola Admin Account
- **Sebagai** Super Admin (Persona P-03)
- **Saya ingin** melihat list Admin, update info Admin, atau disable akun Admin yang bermasalah
- **Agar** saya dapat mengelola akses Admin ke platform sesuai kebijakan
- **Terkait FR:** FR-114—FR-116

### US-021: Tenant dan Admin Monitor Rental Status
- **Sebagai** Tenant (Persona P-01) atau Admin Kost (Persona P-02)
- **Saya ingin** melihat list rental saya dan detail rental dengan status lengkap (payment, dokumen, timeline, cancellation info)
- **Agar** saya dapat memantau progres rental dan tahu langkah apa yang harus dilakukan selanjutnya
- **Terkait FR:** FR-096—FR-100, FR-103, FR-127

### US-022: Tenant Cancel Rental Manual
- **Sebagai** Tenant (Persona P-01)
- **Saya ingin** membatalkan rental yang saya buat sebelum masa sewa dimulai
- **Agar** saya dapat membatalkan transaksi dan room kembali available untuk Tenant lain
- **Terkait FR:** FR-123, FR-124, FR-125, FR-127

---

## 10. Asumsi & Batasan (Assumptions & Constraints)

### 10.1 Asumsi

- Pengguna memiliki koneksi internet stabil
- Tenant memiliki smartphone atau komputer untuk mengakses aplikasi
- Admin Kost memiliki rekening bank dan dapat generate QRIS statis
- Super Admin melakukan verifikasi administratif calon Admin Kost secara manual di luar sistem
- Super Admin melakukan verifikasi lapangan kost secara manual di luar sistem
- Verifikasi dokumen penyewa telah dilakukan secara digital melalui aplikasi. Admin dapat mencetak dokumen fisik dari sistem jika diperlukan untuk keperluan administratif di luar aplikasi (misal: arsip fisik, persetujuan kontrak)
- Email Service (SMTP) dapat mengirim email dengan delivery rate > 95%
- Transaksi pembayaran menggunakan transfer bank manual ke rekening kost (via QRIS scan atau transfer manual)
- Admin dapat mengecek mutasi rekening bank manual untuk memverifikasi pembayaran
- Browser modern (Chrome, Firefox, Safari, Edge) versi 2 tahun terakhir digunakan
- **Refund policy berada di luar tanggung jawab aplikasi.** Jika Tenant cancel rental setelah payment verified, Tenant harus nego refund langsung dengan Admin kost di luar sistem.
- **Tenant diharapkan segera menyelesaikan fase rental setelah payment verified.** Jika Tenant tidak upload dokumen atau dokumen tidak diverifikasi sebelum contract start date, rental otomatis dibatalkan oleh sistem.

### 10.2 Batasan (Constraints)

**Platform:** Web-based saja (desktop & mobile responsive), tidak ada native mobile app.

**Arsitektur & Teknologi:** Laravel 13 monolithic, PHP 8.x, MySQL, web routes dengan session-based auth, single Linux VPS untuk MVP.

**Payment:** QRIS statis + transfer manual dengan verifikasi Admin (bukan payment gateway otomatis), satu payment method per kost, currency hanya IDR.

**Notification:** Email via SMTP saja (tidak ada WhatsApp, SMS, Push Notification).

**Maps:** Display lokasi kost saja (Leaflet + OpenStreetMap), tidak ada fitur routing/geocoding otomatis/search location.

**File Storage:** Server filesystem (storage/app), bukan cloud object storage (S3, GCS).

**Scope MVP:** Pra-UKK dengan timeline terbatas, fokus end-to-end rental lifecycle, fitur administratif lanjutan ditunda, 1 full-stack developer.

**Timeline:** Estimasi development MVP: 12-18 minggu.

---

## 11. Risiko (RISK-xxx)

| ID | Risiko | Dampak | Kemungkinan | Mitigasi |
|---|---|---|---|---|
| RISK-001 | Perubahan requirement di tengah development menyebabkan delay timeline MVP | Tinggi | Sedang | Membekukan requirement setelah PRD & ARCHITECTURE approved. Perubahan melalui change management process (catat di §14 Changelog, update dokumen, re-estimate timeline). |
| RISK-002 | Verifikasi pembayaran manual rentan fraud (Tenant upload bukti palsu, edit nominal transfer) | Sedang | Sedang | Admin wajib cross-check bukti pembayaran dengan mutasi rekening bank real. Sistem mewajibkan Admin input rejection reason yang detail. Training Admin untuk detect bukti palsu. |
| RISK-003 | Email notification gagal terkirim menyebabkan Tenant/Admin tidak aware perubahan status rental | Sedang | Sedang | Email sending menggunakan queue dengan retry mechanism. Email failure di-log. Critical status change tetap tersimpan di database dan dapat dilihat di sistem meskipun email gagal. |
| RISK-004 | Kesalahan input data Admin (typo harga, salah set document requirement, salah verify payment) | Sedang | Sedang | Validasi input data di semua form. Konfirmasi sebelum operasi penting. Rental status history sebagai audit trail. |
| RISK-005 | Single Linux VPS failure (server down, storage full, database corrupt) | Tinggi | Rendah | Database backup terjadwal (daily) dengan retention 7 hari. File storage ter-include backup. Documented restore procedure. |
| RISK-006 | Timeline development MVP melebihi estimasi 18 minggu | Tinggi | Sedang | Prioritas fitur Must-have di PRD & TODO. Fitur Should/Could ditunda jika timeline mepet. Tracking progress via TODO weekly. |
| RISK-007 | Tenant tidak aware bahwa dokumen harus dilengkapi sebelum start date, sehingga rental auto-cancelled | Sedang | Sedang | Display warning/reminder jelas di rental detail. Email notification: "Pastikan semua dokumen diupload & diverifikasi sebelum [contract_start_date]." Countdown/progress bar fase rental. |

---

## 12. Dependencies Eksternal

| ID | Dependency | Jenis | Catatan |
|---|---|---|---|
| DEP-001 | SMTP Email Service | Service/Vendor | Untuk email verification & notification. Kegagalan email tidak boleh block operasi bisnis utama (async via queue). |
| DEP-002 | Leaflet JavaScript Library | Open Source Library | Untuk display map di detail kost. License: BSD 2-Clause (free). |
| DEP-003 | OpenStreetMap Tile Server | Public Service | Untuk map tiles data via Leaflet. Rate limit OSM public tiles perlu diperhatikan. |
| DEP-004 | Linux VPS Hosting | Infrastructure | Min spec: 2 vCPU, 4GB RAM, 40GB SSD, bandwidth sufficient untuk MVP traffic. |
| DEP-005 | Domain Name (optional) | Service | Jika deployment production membutuhkan custom domain. Bisa pakai IP VPS atau subdomain dari hosting provider. |
| DEP-006 | SSL Certificate | Service | Untuk HTTPS. Bisa pakai Let's Encrypt (free) via Certbot, atau SSL dari hosting provider. HTTPS wajib untuk production. |

---

## 13. Open Questions

| ID | Pertanyaan | Pemilik | Status | Resolusi | Tanggal |
|---|---|---|---|---|---|
| Q-001 | Berapa payment deadline yang sesuai untuk MVP? | Product Owner | **Resolved** | **48 jam** dari rental created_at | 2026-08-12 |
| Q-002 | Apakah contract start date di-set Tenant saat booking atau otomatis? | Product Owner | **Resolved** | **Start date di-set Tenant saat booking** (min: today+4 hari, max: today+30 hari). Min 4 hari untuk memberikan waktu verifikasi payment (48h) + document processing (48-72h) sebelum rental active. Lihat ADR-016 di ARCHITECTURE.md. | 2026-08-12 (updated 2026-08-13) |
| Q-003 | Apakah perlu fitur "extend rental" di MVP? | Product Owner | **Resolved** | **Tidak perlu.** Tenant harus buat rental baru jika ingin menyewa lagi setelah completed. | 2026-08-12 |
| Q-004 | Apakah Admin dapat edit Price Scheme setelah ada rental menggunakannya? | Tech Lead | **Resolved** | **Ya, Admin dapat edit.** Price Scheme pada rental merupakan snapshot, jadi perubahan Price Scheme tidak mempengaruhi rental yang sudah ada. | 2026-08-12 |
| Q-005 | Apakah Tenant dapat cancel rental manual di MVP? Policy refund? | Product Owner | **Resolved** | **Ya, Tenant dapat cancel rental manual sebelum Active.** Refund berada di luar tanggung jawab aplikasi. Tidak ada cancellation fee. **Tambahan:** Tenant harus segera menyelesaikan fase rental setelah payment verified. Jika tidak upload dokumen sebelum start date, rental otomatis dibatalkan. | 2026-08-12 |

---

## 14. Riwayat Perubahan (Changelog)

| Versi | Tanggal | Perubahan | Oleh |
|---|---|---|---|
| 0.1.0 | 2026-08-12 | Draft awal PRD dibuat. Konsolidasi dari docs/ (Discovery Document, Business Analysis Document, SRS v1.0.7, DDS v1.0.0, ERD). Total 127 FR, 33 NFR, 22 US. Penyederhanaan: (1) Facility/Rule Scheme → JSON, (2) Price Scheme → 1:N, (3) Kost Status → 5 state, (4) Payment → QRIS statis + verifikasi manual, (5) Review Images → JSON, (6) Kost+Room Review → 1 tabel, (7) Soft delete users, (8) Category Management UI. Open Questions resolved. | Lauhul Ridwan + OpenCode |
| 0.1.1 | 2026-08-12 | Revisi: (1) Expand §5.1 In Scope ke versi detail, (2) Email verification → OTP 6-digit code (expiry 15 menit), tidak mutlak wajib (hanya saat akses fitur tertentu), (3) User dapat ubah email dengan re-verification, (4) Hapus asumsi dokumen fisik check-in, (5) Tambah FR-128 (OTP expiry) & FR-129 (email change re-verification). Total FR: 129. | Lauhul Ridwan + OpenCode |
| 0.1.2 | 2026-08-13 | Revisi sinkronisasi dengan ARCHITECTURE.md v0.1.0: (1) Q-002: Update min start_date dari "today" → "today+4 hari" (ADR-016), (2) FR-046: Update room status "maintenance" → "unavailable", (3) FR-047: Klarifikasi room status hanya 2 values (available/unavailable), reserved/occupied dihitung real-time, (4) FR-122: Update min start_date = today+4 hari, (5) FR-123: Update cancel rental termasuk dari Active, (6) FR-124: Update prevent cancel hanya untuk Completed, (7) Glosarium: Update definisi "Room Status - Unavailable". Total FR: 129 (tidak ada penambahan FR baru). | Lauhul Ridwan + OpenCode |
| 0.1.3 | 2026-08-18 | Penambahan FR-130 (Password Reset via OTP) — reset password berbasis OTP menggantikan mekanisme token link Breeze. | OpenCode |
| 0.1.4 | 2026-08-18 | Revisi: Email verification menjadi on-demand (FR-003/FR-004). Registrasi tidak lagi mengirim OTP otomatis; user diarahkan ke marketplace. OTP kirim saat buka halaman verifikasi atau akses fitur ber-verifikasi (popup + CTA). Implementasi di TASK-086. | OpenCode |

---

## 15. Traceability Overview

Rantai penelusuran wajib untuk setiap requirement:

```
FR-xxx / NFR-xxx  (PRD.md, dokumen ini)
     │
     ├─→  US-xxx           (PRD.md §9 — kebutuhan dari sudut pandang user)
     │
     ├─→  COMP-xxx / ADR-xxx   (ARCHITECTURE.md — bagaimana dibangun)
     │
     └─→  TASK-xxx         (TODO.md — pekerjaan konkret + status)
```

Matriks traceability lengkap (mapping semua ID) dipelihara di `TODO.md` §Traceability Matrix — jangan duplikasi di sini agar tidak ada dua sumber kebenaran yang bisa saling kontradiksi.
