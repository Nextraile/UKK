# WORKFLOW.md — Alur Kerja Agentic Development

> Dokumen ini mendefinisikan **proses**: urutan fase, siapa/apa mengerjakan apa, kapan sebuah dokumen boleh diubah, dan kapan sebuah task dianggap siap/selesai.
> Jika `AGENTS.md` adalah "aturan main teknis", `WORKFLOW.md` adalah "aturan main proses".

| Field | Value |
|---|---|
| Versi Dokumen | `0.2.0` |
| Terakhir Diperbarui | `2026-08-16` |

---

## 1. Filosofi Skema Ini

Skema ini mengganti dokumen tradisional (Discovery Doc, Business Analysis Doc, SRS, DDS) dengan 6 dokumen hidup yang saling terhubung via ID, ditambah 1 dokumen orientasi:

| Dokumen | Menggantikan | Menjawab pertanyaan |
|---|---|---|
| `PRD.md` | Discovery Doc + Business Analysis Doc + SRS | Apa yang dibangun & mengapa? |
| `ARCHITECTURE.md` | Design Document Specification (DDS) | Bagaimana dibangun secara teknis? |
| `TODO.md` | Project Plan / Backlog | Apa yang harus dikerjakan sekarang, oleh siapa, statusnya apa? |
| `AGENTS.md` | Engineering Handbook (ringkas) | Aturan teknis operasional untuk agent |
| `SKILL.md` | — (baru, khas agentic dev) | Kemampuan/prosedur apa saja yang tersedia untuk dipakai ulang? |
| `WORKFLOW.md` | SDLC Process Doc | Proses & urutan kerja (dokumen ini) |
| `MANUAL.md` | Onboarding/Runbook | Bagaimana semua ini dipakai dari nol, termasuk setup tools |

---

## 2. Fase Pengembangan

```
┌───────────────┐   ┌───────────────┐   ┌───────────────┐   ┌───────────────┐   ┌───────────────┐
│ 1. DISCOVERY  │──▶│ 2. DESIGN     │──▶│ 3. PLANNING   │──▶│ 4. BUILD      │──▶│ 5. VERIFY &   │
│  → PRD.md     │   │  → ARCHITECTURE│  │  → TODO.md    │   │  (agent loop) │   │    RELEASE    │
└───────────────┘   └───────────────┘   └───────────────┘   └───────────────┘   └───────────────┘
```

### Fase 1 — Discovery
- **Output:** `PRD.md` terisi minimal §1–§9 dengan status `Draft`.
- **Gate keluar:** semua FR-xxx Must-have punya acceptance criteria yang jelas; glosarium (§6) lengkap untuk semua istilah yang dipakai.
- **Siapa:** manusia (pemilik produk) berkolaborasi dengan agent untuk menstrukturkan ide mentah menjadi FR/NFR/US yang rapi.

### Fase 2 — Design
- **Input:** `PRD.md` berstatus minimal `Draft` lengkap.
- **Output:** `ARCHITECTURE.md` terisi §1–§9, setiap `COMP-xxx` merujuk ke `FR-xxx` yang relevan.
- **Gate keluar:** tidak ada `FR-xxx` Must-have yang belum punya komponen pemilik (`COMP-xxx`) di `ARCHITECTURE.md`.

### Fase 3 — Planning
- **Input:** `PRD.md` + `ARCHITECTURE.md` sudah `Approved`.
- **Output:** `TODO.md` terisi dengan `TASK-xxx` yang memecah setiap `COMP-xxx`/`FR-xxx` menjadi unit kerja yang bisa diselesaikan dalam 1 sesi agent (idealnya < 1 hari kerja).
- **Gate keluar:** setiap Must-have FR-xxx punya minimal 1 TASK-xxx.

### Fase 4 — Build (loop utama agentic development)
Ini adalah fase yang berulang untuk setiap `TASK-xxx`:

```
1. Agent memilih TASK-xxx berstatus "Ready" (bukan Blocked) dari TODO.md
2. Agent membaca FR-xxx/NFR-xxx terkait (PRD.md) + COMP-xxx/ADR-xxx terkait (ARCHITECTURE.md)
3. Agent cek tabel Rujukan Dokumentasi Resmi (ARCHITECTURE.md §3.1): jika task menyentuh
   tool/library yang belum ada di tabel, agent WAJIB mencari dokumentasi resminya dulu
   (lihat AGENTS.md §Dokumentasi & Referensi Eksternal) sebelum lanjut ke langkah 4
4. Agent cek apakah ada SKILL yang relevan (lihat SKILL.md)
5. Agent implementasi + test sesuai AGENTS.md (termasuk §Coding Guidelines)
6. Agent update status TASK-xxx: Ready → In Progress → Review → Done
7. Jika ditemukan requirement/desain yang ambigu/kurang → buka Q-xxx baru di PRD.md §13, JANGAN menebak
```

### Fase 5 — Verify & Release
- Checklist rilis: lihat `WORKFLOW.md` §5 di bawah.

---

## 3. Definition of Ready (DoR) — kapan sebuah Task boleh dikerjakan

Sebuah `TASK-xxx` **Ready** untuk dikerjakan jika:
- [ ] Merujuk minimal satu `FR-xxx`/`NFR-xxx` yang statusnya `Approved`.
- [ ] Merujuk `COMP-xxx` yang sudah terdefinisi di `ARCHITECTURE.md`.
- [ ] Tidak punya dependency (`TASK-xxx` lain) yang masih `Not Started`.
- [ ] Acceptance criteria task jelas dan bisa diverifikasi objektif (bukan "buat lebih bagus").

## 4. Definition of Done (DoD) — kapan sebuah Task dianggap selesai

- [ ] Acceptance criteria FR/US terkait terpenuhi.
- [ ] Seluruh perintah test/lint/static analysis di `AGENTS.md` lulus (termasuk `pint` dan `phpstan`).
- [ ] Tidak ada regresi.
- [ ] Implementasi mengikuti `AGENTS.md` §Coding Guidelines — docblock lengkap untuk class/method public, tidak ada duplikasi logika yang seharusnya diekstrak (DRY), tidak ada N+1 query baru yang tidak perlu.
- [ ] Jika task menyentuh tool/library yang belum ada di `ARCHITECTURE.md` §3.1, dokumentasi resminya sudah dicek dan baris baru sudah ditambahkan ke tabel tsb.
- [ ] `TODO.md` diperbarui statusnya menjadi `Done`.
- [ ] Jika ada perubahan pada rute/kontrak (`§6 Routes & API-xxx`) atau model data (`DM-xxx`), `ARCHITECTURE.md` diperbarui pada commit yang sama (termasuk migration baru jika ada perubahan `DM-xxx`).

## 5. Checklist Rilis (Release Checklist)

- [ ] Semua `TASK-xxx` untuk `FR-xxx` Must-have berstatus `Done`.
- [ ] Regression test penuh lulus.
- [ ] Docker image aplikasi berhasil di-build tanpa error untuk environment target (staging/production) — lihat `ARCHITECTURE.md` §9.
- [ ] Migration terbaru sudah diverifikasi jalan bersih di environment yang menyerupai production (bukan cuma local).
- [ ] `PRD.md` §14 Changelog & `ARCHITECTURE.md` §12 Changelog diperbarui.
- [ ] Versi dokumen di header `PRD.md`/`ARCHITECTURE.md`/`TODO.md` dinaikkan bersamaan.

---

## 6. Manajemen Perubahan (Change Management)

Perubahan requirement/desain **di tengah** proses Build tidak dilarang, tapi wajib melalui alur berikut agar tidak terjadi "silent drift":

1. Perubahan diajukan sebagai entri baru di `PRD.md` §13 Open Questions ATAU langsung sebagai draft revisi FR/NFR.
2. Dampak ke `ARCHITECTURE.md` (komponen mana yang terdampak) dan `TODO.md` (task mana yang jadi usang/perlu ditambah) diidentifikasi eksplisit.
3. Versi dokumen dinaikkan (minor version jika penambahan, major jika mengubah/menghapus requirement yang sudah `Approved`).
4. Task yang jadi usang akibat perubahan ditandai `Deprecated` di `TODO.md`, bukan dihapus.

> **Prinsip inti:** dokumen tidak pernah "diam-diam" berubah. Setiap perubahan pada `PRD.md`/`ARCHITECTURE.md` meninggalkan jejak di Changelog masing-masing dokumen.

---

## 7. Peran (Roles)

| Peran | Tanggung jawab utama |
|---|---|
| Product Owner (manusia) | Mengisi & meng-approve `PRD.md`, menjawab `Open Questions` |
| Tech Lead (manusia, opsional) | Meng-approve `ARCHITECTURE.md`, menulis/mereview ADR |
| Coding Agent (OpenCode) | Fase Planning detail, Build, menjalankan test, update status `TODO.md` |
| Reviewer (manusia atau agent lain) | Verifikasi DoD sebelum task ditutup |

---

## 8. Riwayat Perubahan (Changelog)

| Versi | Tanggal | Perubahan | Oleh |
|---|---|---|---|
| 0.1.0 | 2026-08-15 | Initial workflow documentation: 5 phases (Discovery→Design→Planning→Build→Verify), Definition of Ready/Done, Release Checklist, Change Management process, Roles definition. Baseline untuk SewaKost project (Laravel 13 modular monolith, agentic development workflow). | OpenCode |
