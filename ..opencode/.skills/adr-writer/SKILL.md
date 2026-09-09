---
name: adr-writer
description: Membantu menulis Architecture Decision Record (ADR) baru dengan format baku: Konteks → Keputusan → Alternatif → Konsekuensi. Gunakan setiap kali ada keputusan teknis signifikan (pilihan library, pola arsitektur, infrastruktur baru). ADR tidak boleh diedit isi keputusannya setelah published — jika berubah, buat ADR baru yang men-supersede. Trigger: "buat ADR", "architecture decision", "pilihan library", "kenapa pakai X bukan Y", "catat keputusan teknis".
license: MIT
compatibility: opencode
---

# adr-writer — Penulisan Architecture Decision Record

## Tujuan

Mendokumentasikan keputusan arsitektur/teknis signifikan dengan format terstruktur yang dapat diaudit, diverifikasi, dan dilacak alasannya di kemudian hari. ADR memastikan setiap keputusan teknis punya justifikasi eksplisit dan alternatif yang dipertimbangkan tercatat, sehingga developer/stakeholder di masa depan memahami *mengapa* pilihan tertentu diambil — bukan hanya *apa* yang digunakan.

## Dasar/Rujukan

- **`ARCHITECTURE.md` §7:** Semua ADR disimpan di section ini dengan format baku
- **`AGENTS.md` §Guardrails:** Jangan menambahkan dependency/library baru tanpa mencatat alasannya sebagai ADR
- **`AGENTS.md` §Dokumentasi & Referensi Eksternal:** Setiap dependency baru wajib ditambahkan ke tabel dokumentasi dengan ADR barunya
- **`SKILL.md` §2 adr-writer:** Skill direkomendasikan untuk setiap keputusan teknis signifikan

## Kapan Membuat ADR Baru (Trigger Decision)

ADR **WAJIB** dibuat untuk:

| Kategori | Contoh Konkret |
|---|---|
| **Pilihan framework/library baru** | Memilih Laravel (vs Symfony/CodeIgniter), Alpine.js (vs Vue/React), Leaflet (vs Google Maps API) |
| **Perubahan pola arsitektur** | Modular monolith (vs microservices), Session-based auth (vs JWT/OAuth), Transactional rental (vs free-form booking) |
| **Infrastruktur/deployment** | Docker Sail (vs native/Vagrant), MySQL (vs PostgreSQL), Filesystem storage (vs S3/MinIO) |
| **Penyimpangan dari konvensi standar** | JSON array untuk facility/rule (vs relational tables), QRIS statis (vs payment gateway integration) |
| **Supersede ADR lama** | Keputusan berubah (mis. ADR-006 Midtrans → ADR-014 QRIS statis) |

ADR **TIDAK** perlu dibuat untuk:
- Pilihan trivial yang sudah jelas dari framework (mis. "gunakan Eloquent untuk ORM" di proyek Laravel)
- Detail implementasi dalam satu komponen (mis. nama variable, folder struktur internal service)
- Bug fix atau refactoring yang tidak mengubah keputusan arsitektur

**Aturan praktis:** Jika keputusan ini akan memengaruhi cara developer lain bekerja di masa depan (library baru, API pattern, data model design, security policy), buat ADR.

## Langkah-Langkah

### 1. Tentukan Nomor ADR Berikutnya

Baca `ARCHITECTURE.md` §7, cari ADR terakhir (mis. ADR-019), maka ADR baru = **ADR-020**.

**Aturan penomoran:**
- Sequential, tidak boleh ada gap (jangan loncat dari ADR-019 ke ADR-021)
- Tidak boleh menghapus atau menomori ulang ADR lama — jika keputusan berubah, buat ADR baru yang men-supersede (lihat Langkah 7)

### 2. Tulis Judul yang Jelas & Actionable

Format: **ADR-XXX: [Verb] [Object] — [Outcome/Reason singkat]**

**Contoh baik:**
- ADR-001: Pilih Laravel 13 sebagai Framework Backend — Modular Monolith untuk MVP
- ADR-014: Ganti Midtrans dengan QRIS Statis + Verifikasi Manual — Simplifikasi Payment Flow MVP

**Contoh buruk (hindari):**
- ADR-001: Framework (tidak jelas apa keputusannya)
- ADR-014: Payment (terlalu generik)

### 3. Tulis Section "Konteks" — Situasi & Problem yang Melatarbelakangi Keputusan

**Isi:**
- Requirement atau constraint apa yang memicu keputusan ini (rujuk `FR-xxx`/`NFR-xxx` jika relevan)
- Batasan proyek (waktu, budget, skill tim, complexity limit untuk MVP)
- Asumsi yang dipegang (mis. "traffic MVP < 1000 user/day", "deploy ke single VPS")

**Panjang:** 2-5 kalimat, fokus ke *mengapa* keputusan ini perlu dibuat.

**Contoh:**
```markdown
**Konteks:**
Proyek ini adalah MVP dengan target development 2-3 bulan dan deploy ke single VPS. Tim terdiri dari 2 developer yang familiar dengan Laravel. Requirement mencakup autentikasi session-based, RBAC 3 role, dan integrasi peta untuk listing kost (FR-001 s.d. FR-129). Tidak ada requirement untuk REST API publik atau mobile app di fase MVP.
```

### 4. Tulis Section "Keputusan" — Apa yang Diputuskan

**Isi:**
- Nyatakan keputusan dengan jelas dan tegas (gunakan "akan", "harus", bukan "mungkin", "bisa")
- Jika melibatkan tool/library, sebutkan versi spesifik
- Jika melibatkan pola/pattern, jelaskan pattern-nya secara singkat (tidak perlu tutorial lengkap — cukup definisi)

**Panjang:** 2-4 kalimat.

**Contoh:**
```markdown
**Keputusan:**
Sistem akan menggunakan Laravel 13 sebagai framework backend, dengan pola arsitektur modular monolith (satu codebase, diorganisir per domain di `app/Domain/`). Autentikasi menggunakan session-based (bukan JWT/stateless). Frontend menggunakan Blade templates + Alpine.js untuk interaktivitas sederhana (bukan SPA framework).
```

### 5. Tulis Section "Alternatif yang Dipertimbangkan" — Opsi Lain & Kenapa Ditolak

**Isi:**
- Minimal 1-2 alternatif lain yang secara rasional bisa dipertimbangkan
- Untuk setiap alternatif, jelaskan singkat kenapa *tidak* dipilih (tradeoff-nya apa, constraint mana yang dilanggar)

**Penting:** Jangan straw man argument (mis. "alternatif X buruk karena lambat" tanpa bukti). Jika alternatif valid tapi tidak cocok untuk konteks *ini*, katakan begitu.

**Panjang:** 3-6 kalimat (1-2 per alternatif).

**Contoh:**
```markdown
**Alternatif yang Dipertimbangkan:**

1. **Symfony 7:** Framework PHP mature dengan fleksibilitas tinggi, tapi learning curve lebih curam untuk tim yang sudah familiar Laravel. Boilerplate setup lebih banyak untuk fitur-fitur yang sudah built-in di Laravel (mis. authentication scaffolding).

2. **Microservices (Laravel + Node.js services terpisah):** Memberikan skalabilitas horizontal lebih mudah, tapi overhead development & deployment terlalu tinggi untuk MVP (perlu API versioning, service discovery, distributed logging). Complexity ini tidak justified untuk target traffic <1000 user/day.

3. **CodeIgniter 4:** Lebih ringan dari Laravel, tapi ekosistem package lebih kecil dan tidak ada built-in support untuk queue, event, atau advanced ORM seperti Eloquent yang dibutuhkan untuk rental lifecycle (FR-049 s.d. FR-077).
```

### 6. Tulis Section "Konsekuensi" — Dampak Positif & Negatif dari Keputusan Ini

**Isi:**
- **Positif:** Apa yang jadi lebih mudah/cepat/aman dengan keputusan ini
- **Negatif/Tradeoff:** Apa yang jadi lebih sulit, atau limitation yang harus diterima
- **Action items** (jika ada): Langkah follow-up yang diperlukan karena keputusan ini (mis. "harus tambahkan dependency X", "harus dokumentasikan pattern Y di AGENTS.md")

**Panjang:** 4-8 kalimat.

**Contoh:**
```markdown
**Konsekuensi:**

**Positif:**
- Development velocity tinggi untuk MVP — Laravel menyediakan authentication, authorization, migration, queue, event, testing out-of-the-box.
- Modular monolith memudahkan refactoring awal tanpa overhead network call antar-service.
- Session-based auth lebih sederhana untuk web-only app (no token refresh logic, CSRF protection built-in).

**Negatif/Tradeoff:**
- Jika di masa depan perlu REST API untuk mobile app, session-based auth harus diubah atau ditambahkan API token (mis. Laravel Sanctum) — ini akan butuh ADR baru.
- Monolith bisa jadi bottleneck jika traffic tumbuh >10k user/day — perlu strategy caching & horizontal scaling (load balancer + shared session storage, lihat ADR-XXX di masa depan).

**Action items:**
- Dokumentasikan struktur folder domain (`app/Domain/`) di ARCHITECTURE.md §11 (DONE — lihat commit abc123)
- Tambahkan Laravel 13 ke tabel dokumentasi di ARCHITECTURE.md §3.1 dengan link ke https://laravel.com/docs/13.x (DONE)
```

### 7. (Opsional) Jika ADR Ini Men-supersede ADR Lama

Jika keputusan berubah dari ADR lama, **jangan edit ADR lama** — buat ADR baru dengan section tambahan:

```markdown
**Supersedes:** ADR-006 (Midtrans Payment Gateway Integration)

**Alasan perubahan:**
Setelah diskusi dengan stakeholder (tanggal YYYY-MM-DD), disepakati bahwa integrasi Midtrans terlalu kompleks untuk MVP dan memerlukan proses verifikasi merchant yang lama. Untuk mempercepat launch, payment flow disederhanakan menjadi QRIS statis + upload bukti + verifikasi manual oleh admin (FR-087 direvisi dari "otomatis" menjadi "manual approval").
```

Kemudian di ADR lama (ADR-006), tambahkan baris di akhir:

```markdown
**Status:** Superseded by ADR-014 (tanggal YYYY-MM-DD)
```

### 8. Tambahkan ADR ke ARCHITECTURE.md §7

Sisipkan ADR baru di bagian bawah `ARCHITECTURE.md` §7 (setelah ADR terakhir), dengan format:

```markdown
#### ADR-XXX: [Judul dari Langkah 2]

**Konteks:**
[dari Langkah 3]

**Keputusan:**
[dari Langkah 4]

**Alternatif yang Dipertimbangkan:**
[dari Langkah 5]

**Konsekuensi:**
[dari Langkah 6]

[Opsional: Supersedes section dari Langkah 7]

---
```

### 9. Update Changelog ARCHITECTURE.md §12

Tambahkan baris baru di `ARCHITECTURE.md` §12:

```markdown
| 0.X.Y | YYYY-MM-DD | Tambah ADR-XXX: [judul singkat]. [Dampak utama, mis. "Supersede ADR-006" atau "Tambah dependency Alpine.js"] | [Nama agent/developer] |
```

Increment versi sesuai semantic versioning:
- Tambah ADR yang **tidak** supersede ADR lama → **minor version** (0.1.0 → 0.2.0)
- Tambah ADR yang **supersede** ADR lama (perubahan keputusan signifikan) → bisa **major** atau **minor** tergantung dampak (mis. 0.1.0 → 1.0.0 jika breaking change besar)

### 10. (Jika Melibatkan Dependency Baru) Update Tabel Dokumentasi ARCHITECTURE.md §3.1

Jika ADR ini memutuskan untuk menggunakan library/tool baru, tambahkan baris di tabel `ARCHITECTURE.md` §3.1:

```markdown
| [Nama Tool/Library] | [Link dokumentasi resmi] | [Versi] | ADR-XXX |
```

**Contoh:**
```markdown
| Alpine.js | https://alpinejs.dev/start-here | 3.x | ADR-002 |
| Leaflet | https://leafletjs.com/reference.html | 1.9.x | ADR-007 |
```

## Kondisi Berhenti / Eskalasi

- **Keputusan belum final/masih diskusi** → Jangan buat ADR sampai keputusan benar-benar diambil. ADR adalah *record of decision*, bukan *proposal*. Jika masih tahap eksplorasi, catat sebagai `Q-xxx` di `PRD.md` §13 atau diskusikan dengan pengguna dulu.
- **Tidak ada alternatif yang dipertimbangkan** → Berhenti, tanyakan ke pengguna: "Apakah ada opsi lain yang sempat dipertimbangkan?" Jika benar-benar tidak ada alternatif (mis. hanya satu tool yang memenuhi requirement), sebutkan eksplisit di section Alternatif: "Tidak ada alternatif viable karena [alasan]."
- **Keputusan trivial/sudah jelas dari framework** → Eskalasi ke pengguna: "Apakah keputusan ini cukup signifikan untuk didokumentasikan sebagai ADR?" Jika tidak, cukup catat sebagai catatan di `AGENTS.md` atau langsung implementasi tanpa ADR.
- **ADR baru konflik dengan ADR lama tapi tidak men-supersede** → Berhenti, ada kemungkinan inkonsistensi. Klarifikasi ke pengguna: apakah ADR lama perlu di-supersede, atau keputusan baru ini sebenarnya tidak konflik (cukup refine/clarify ADR lama).

## Contoh ADR Lengkap

```markdown
#### ADR-020: Gunakan Laravel Sanctum untuk API Token Authentication — Support Mobile App di Fase 2

**Konteks:**
Di fase MVP (ADR-001), sistem hanya menggunakan session-based authentication untuk web app. Sekarang (Fase 2), ada requirement baru untuk mobile app (Android/iOS) yang butuh REST API (FR-200 s.d. FR-215). Session-based auth tidak cocok untuk mobile karena CSRF protection dan cookie handling kompleks di native app. Perlu token-based authentication yang stateless dan bisa di-refresh.

**Keputusan:**
Sistem akan menambahkan Laravel Sanctum (https://laravel.com/docs/13.x/sanctum) untuk API token authentication, khusus untuk mobile app. Web app tetap menggunakan session-based auth (ADR-001 tidak di-supersede). Sanctum dipilih karena built-in di Laravel, support personal access token (PAT) untuk mobile, dan tidak butuh OAuth complexity seperti Passport.

**Alternatif yang Dipertimbangkan:**

1. **Laravel Passport (OAuth 2.0):** Lebih kompleks, cocok untuk third-party API access. Overkill untuk mobile app internal yang tidak perlu OAuth flow.

2. **JWT custom (tymon/jwt-auth):** Fleksibel tapi butuh manual handling untuk token refresh, revocation, dan rate limiting — semua sudah built-in di Sanctum.

3. **Ubah web app ke stateless token juga:** Menghilangkan CSRF protection benefit dari session, dan butuh refactor besar di semua controller yang sudah pakai session. Tidak worth it jika web app sudah stabil.

**Konsekuensi:**

**Positif:**
- Mobile app bisa authenticate dengan token (stateless, scalable).
- Web app tidak terpengaruh (backward compatible).
- Sanctum ringan, tidak ada overhead OAuth server.

**Negatif/Tradeoff:**
- Sekarang ada dua authentication mechanism di codebase (session untuk web, token untuk mobile) — middleware & test harus aware kedua mode ini.
- Token expiration & refresh logic harus diimplementasikan manual di mobile app (Sanctum PAT tidak auto-refresh seperti OAuth).

**Action items:**
- Tambahkan Sanctum ke `composer.json`: `composer require laravel/sanctum` (DONE)
- Tambahkan middleware `auth:sanctum` untuk route API di `routes/api.php` (TASK-XXX)
- Update `ARCHITECTURE.md` §3.1 dengan link dokumentasi Sanctum (DONE)
- Update `AGENTS.md` §Konvensi Kode: API route sekarang boleh digunakan (ubah larangan di ADR-001) (DONE)

---
```

## Improvement Notes (vs Versi Sebelumnya yang Hilang)

- Tambah **Langkah 10 (Update Tabel Dokumentasi)** — memastikan dependency baru langsung terdokumentasi dengan rujukan ke ADR-nya
- Tambah **Kondisi Berhenti untuk keputusan belum final** — mencegah ADR dibuat terlalu dini (seharusnya proposal dulu, bukan ADR)
- Tambah **Contoh ADR lengkap** dengan semua section terisi — referensi format yang konsisten
- Klarifikasi **kapan ADR wajib vs tidak perlu** dengan tabel kategori trigger decision
- Tambah **aturan penomoran sequential** — mencegah gap atau renumbering yang membingungkan
