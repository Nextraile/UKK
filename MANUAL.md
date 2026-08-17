# MANUAL.md — Panduan Induk Agentic Development

> Dokumen ini adalah **titik masuk (entry point)**. Jika kamu baru pertama kali memakai skema ini, baca dokumen ini dari atas ke bawah dulu sebelum menyentuh dokumen lain.

> **Catatan versi skema ini:** paket `ARCHITECTURE.md`/`AGENTS.md` di sini sudah di-set dengan baseline **Laravel 13** (modular monolith, session-based auth, web routes) + **Docker via Laravel Sail** untuk local/dev — lihat `ARCHITECTURE.md` §1–§3 untuk detailnya. Jika proyekmu memakai stack lain, sesuaikan bagian tersebut (dan §7 di bawah) sebelum mulai Fase Discovery.

---

## Daftar Isi

1. Konsep Besar: Kenapa Skema Ini Ada
2. Peta 7 Dokumen & Hubungannya
3. Alur Kerja End-to-End (dari Ide Kosong sampai Rilis)
4. Setup Tools: OpenCode
5. Setup Tools: 9Router (opsional, untuk hemat biaya API)
6. Menghubungkan OpenCode ke 9Router
7. Struktur Folder Proyek yang Direkomendasikan
8. Menjalankan Sesi Kerja Pertama dengan Agent
9. FAQ & Troubleshooting
10. Checklist Cepat (Cheat Sheet)

---

## 1. Konsep Besar: Kenapa Skema Ini Ada

Metodologi software development klasik biasanya menghasilkan banyak dokumen terpisah: **Discovery Document** (riset awal), **Business Analysis Document** (kebutuhan bisnis), **SRS/Software Requirements Specification** (kebutuhan fungsional detail), dan **DDS/Design Document Specification** (desain teknis). Untuk tim manusia, dokumen-dokumen ini sering ditulis di waktu berbeda, oleh orang berbeda, dan salah satu masalah terbesarnya adalah **informasi yang sama tersebar dan bisa saling kontradiksi**.

Untuk *agentic development* (AI coding agent mengerjakan sebagian besar implementasi), masalah itu jadi lebih fatal: agent tidak punya "insting" untuk tahu dokumen mana yang paling update saat ada kontradiksi. Karena itu skema ini merampingkan semuanya menjadi **8 dokumen hidup + 1 dokumen orientasi**, dengan dua prinsip keras:

1. **Setiap dokumen berdiri sendiri (self-contained).** Agent yang hanya membaca `PRD.md` tetap paham *apa* yang dibangun tanpa perlu tahu proses riset di baliknya. Agent yang hanya membaca `ARCHITECTURE.md` tetap paham *bagaimana* sistem dibangun tanpa perlu buka `PRD.md` untuk istilah dasar (karena istilah dasar ada di Glosarium `PRD.md`, dan `ARCHITECTURE.md` hanya perlu merujuk ID-nya, bukan menjelaskan ulang).
2. **Semua saling terhubung lewat ID, bukan lewat narasi.** `FR-001` di `PRD.md` selalu berarti hal yang persis sama di mana pun ID itu disebut — di `ARCHITECTURE.md`, di `TODO.md`, di commit message. Ini yang membuat agent bisa "menelusuri" kebutuhan tanpa salah paham.

---

## 2. Peta 8 Dokumen & Hubungannya

```
                          MANUAL.md  (kamu di sini — cara pakai semuanya)
                               │
     ┌─────────────────────────┼─────────────────────────┐
     │                         │                         │
 PRD.md                 ARCHITECTURE.md               WORKFLOW.md
 (APA & MENGAPA)          (BAGAIMANA)              (PROSES & fase)
     │                         │                         │
     │              ┌──────────┴──────────┐              │
     │              │                     │              │
     │          DESIGN.md              PAGES.md          │
     │      (sistem desain UI)    (spesifikasi halaman)   │
     │              │                     │              │
     └──────────────┼─────────────────────┘              │
                    ▼                                    │
                 TODO.md  ◀──────────────────────────────┘
            (task konkret + status + traceability)
                    │
                    ▼
               AGENTS.md  (aturan teknis operasional, dibaca otomatis oleh OpenCode)
                    │
                    ▼
               SKILL.md  (indeks kemampuan/prosedur yang bisa dipakai ulang)
```

**Cara agent (dan kamu) membaca dokumen sesuai kebutuhan:**

| Pertanyaan | Baca dokumen |
|---|---|
| "Fitur ini untuk apa? Siapa penggunanya?" | `PRD.md` |
| "Bagaimana cara implementasi teknisnya?" | `ARCHITECTURE.md` |
| "Komponen UI apa yang dipakai? Design token apa?" | `DESIGN.md` |
| "Layout halaman ini seperti apa? Data apa yang dibutuhkan?" | `PAGES.md` |
| "Apa yang harus saya kerjakan sekarang?" | `TODO.md` |
| "Bagaimana urutan kerja proyek ini dari awal sampai rilis?" | `WORKFLOW.md` |
| "Perintah build/test apa? Aturan commit apa?" | `AGENTS.md` |
| "Ada prosedur siap pakai untuk tugas ini?" | `SKILL.md` |
| "Bagaimana cara pakai semua ini / setup tools?" | `MANUAL.md` (dokumen ini) |

---

## 3. Alur Kerja End-to-End (dari Ide Kosong sampai Rilis)

Ringkasan — detail tiap fase ada di `WORKFLOW.md` §2:

1. **Tulis ide mentah** → duduk bareng OpenCode, minta bantu menstrukturkannya jadi `PRD.md` (isi §1–§9: ringkasan, masalah, tujuan, persona, scope, glosarium, FR, NFR, user story).
2. **Rancang arsitektur** → dari `PRD.md` yang sudah matang, minta OpenCode membantu mengisi `ARCHITECTURE.md` (komponen, model data, kontrak API, ADR).
3. **Pecah jadi task** → isi `TODO.md` dengan `TASK-xxx` yang memecah setiap `FR-xxx`/`COMP-xxx`.
4. **Build berulang** → untuk tiap task: agent baca requirement + desain terkait → implementasi → test → update status.
5. **Verifikasi & rilis** → jalankan Release Checklist di `WORKFLOW.md` §5.

---

## 4. Setup Tools: OpenCode

**Apa itu OpenCode?** Coding agent open-source yang jalan di terminal (juga tersedia versi desktop/IDE). Berbeda dari tools berlangganan seperti Claude Code atau Cursor, OpenCode tidak mewajibkan subscription — kamu pakai API key sendiri dari provider mana pun (Anthropic, OpenAI, Gemini, atau 75+ provider lain), dan hanya bayar sesuai pemakaian token ke provider tersebut secara langsung.

### 4.1 Instalasi

```bash
curl -fsSL https://opencode.ai/install | bash
```

Atau via npm:

```bash
npm i -g opencode-ai
```

### 4.2 Inisialisasi di Proyek Baru

```bash
cd nama-proyek-kamu
opencode
```

Di dalam sesi OpenCode, jalankan:

```
/init
```

Perintah ini akan memindai repo (package manager, test runner, linter yang terpasang), lalu **membuat atau memperbarui `AGENTS.md` secara otomatis** dengan perintah build/test/lint yang terdeteksi. Karena skema ini sudah menyediakan template `AGENTS.md` yang lebih lengkap (mencakup aturan proses, bukan cuma perintah teknis), jalankan `/init` **setelah** menaruh template `AGENTS.md` dari skema ini di root proyek — OpenCode akan memperkaya bagian teknis (bagian "Setup & Perintah") tanpa menghapus bagian proses yang sudah kamu tulis.

### 4.3 Mode Kerja: Plan vs Build

OpenCode punya dua agent bawaan:
- **Plan** — mode read-only, cocok untuk fase Discovery/Design/Planning (mengisi `PRD.md`/`ARCHITECTURE.md`/`TODO.md`) tanpa risiko agent mengubah kode secara tidak sengaja.
- **Build** — mode dengan akses penuh, dipakai di fase Build untuk benar-benar mengimplementasikan `TASK-xxx`.

**Rekomendasi:** pakai mode **Plan** saat sesi mengisi `PRD.md`/`ARCHITECTURE.md`/`TODO.md`, baru pindah ke **Build** saat mulai eksekusi `TASK-xxx`.

### 4.4 Menempatkan Skill Proyek

Sesuai `SKILL.md`, skill sesungguhnya (yang dibaca otomatis oleh OpenCode) ditaruh di:

```
.opencode/skills/<nama-skill>/SKILL.md
```

OpenCode akan otomatis menemukannya selama kamu bekerja di dalam direktori proyek (OpenCode menelusuri ke atas dari direktori kerja sampai root git). Skill global (lintas proyek, tidak ikut ter-commit) ditaruh di `~/.config/opencode/skills/`.

**Proyek SewaKost saat ini memiliki 18 skills ter-install** (9 development workflow, 7 design & branding, 1 UI craft tool, 1 skill discovery). Lihat `SKILL.md` §1 untuk daftar lengkap dengan trigger keywords dan usage guidance.

---

## 5. Setup Tools: 9Router (opsional, untuk hemat biaya API)

**Apa itu 9Router?** Proxy/gateway open-source (self-hosted) yang berada di antara tools coding kamu (termasuk OpenCode) dan puluhan LLM provider. Fungsinya:
- Menerjemahkan format API antar provider (OpenAI ↔ Claude ↔ Gemini, dst.) sehingga satu endpoint bisa dipakai untuk banyak model.
- **Auto-fallback bertingkat:** kalau kuota provider utama habis, otomatis pindah ke provider lain (mis. subscription → API murah → provider gratis) tanpa menghentikan kerja agent.
- **Kompresi token (RTK)**: memampatkan output tool yang boros token (`git diff`, `grep`, `ls`, `tree`) sebelum dikirim ke LLM, biasanya menghemat 20-40% token input.
- Dashboard untuk memantau pemakaian kuota secara real-time.

> 9Router murni proxy jaringan — dia sendiri **tidak pernah menagih biaya apa pun**; kamu tetap bayar langsung ke provider LLM yang kamu pilih (atau pakai tier gratis yang tersedia).

### 5.1 Instalasi Cepat

```bash
npm install -g 9router
9router
```

Dashboard otomatis terbuka di `http://localhost:20128`.

### 5.2 Menjalankan dari Docker (alternatif)

```bash
docker run -d \
  --name 9router \
  -p 20128:20128 \
  -v "$HOME/.9router:/app/data" \
  -e DATA_DIR=/app/data \
  decolua/9router:latest
```

Lalu buka `http://localhost:20128`.

### 5.3 Menghubungkan Provider LLM

Di dashboard 9Router (`Providers`), kamu bisa menghubungkan:
- **Provider berlangganan (OAuth):** Claude Code, Codex, GitHub Copilot, Cursor — login sekali, 9Router melacak kuota otomatis.
- **Provider API key:** Anthropic, OpenAI, Gemini, GLM, DeepSeek, Groq, MiniMax, dan puluhan lainnya — masukkan API key di dashboard.
- **Provider gratis (tanpa perlu API key sendiri):** mis. opsi OAuth gratis yang tersedia di dashboard, atau endpoint tanpa autentikasi — ketersediaan dan kuota tiap provider gratis bisa berubah sewaktu-waktu, jadi selalu cek daftar terbaru langsung di dashboard 9Router sebelum mengandalkannya untuk kerja penting.

### 5.4 Membuat Combo (Fallback Bertingkat)

Contoh combo yang memaksimalkan subscription lalu jatuh ke opsi lebih murah:

```
Dashboard → Combos → Create New
Nama: kerja-utama
Urutan model:
  1. <model dari subscription yang kamu punya>
  2. <model API murah sebagai cadangan>
  3. <model tier gratis sebagai cadangan terakhir>
```

Saat model di urutan pertama kehabisan kuota, 9Router otomatis pindah ke urutan berikutnya — sesi kerja agent tidak terputus.

---

## 6. Menghubungkan OpenCode ke 9Router

Karena 9Router menyediakan endpoint yang kompatibel dengan format OpenAI di `http://localhost:20128/v1`, kamu tinggal mengarahkan OpenCode ke endpoint tersebut alih-alih langsung ke provider aslinya. Konfigurasikan di `opencode.json` (root proyek) atau `~/.config/opencode/opencode.json` (global):

```json
{
  "$schema": "https://opencode.ai/config.json",
  "provider": {
    "9router": {
      "npm": "@ai-sdk/openai-compatible",
      "options": {
        "baseURL": "http://localhost:20128/v1",
        "apiKey": "<API key dari dashboard 9Router>"
      },
      "models": {
        "<id-model-pilihan, mis. kombinasi combo yang sudah dibuat>": {}
      }
    }
  }
}
```

Setelah itu, saat menjalankan `opencode`, pilih provider `9router` dan model/combo yang sudah dikonfigurasi. Semua request dari OpenCode akan melewati 9Router — otomatis dapat manfaat kompresi token dan fallback tanpa perlu ubah cara kerja OpenCode sama sekali.

> **Catatan:** ini opsional. Jika kamu sudah nyaman langsung pakai API key provider (mis. langsung Anthropic) di OpenCode tanpa proxy, itu juga valid — 9Router murni pilihan untuk efisiensi biaya/kuota, bukan keharusan.

---

## 7. Struktur Folder Proyek yang Direkomendasikan

> Struktur di bawah menggabungkan folder skema (dokumen hidup + OpenCode) dengan struktur standar Laravel + Docker (via Laravel Sail untuk local/dev). Detail lengkap folder `app/`, `routes/`, dst. ada di `ARCHITECTURE.md` §11 — jangan duplikasi di sini, cukup rujuk.

```
nama-proyek/
├── PRD.md
├── ARCHITECTURE.md
├── WORKFLOW.md
├── AGENTS.md
├── SKILL.md
├── TODO.md
├── MANUAL.md
├── opencode.json              # konfigurasi provider OpenCode (lihat §6)
├── .opencode/
│   └── skills/
│       ├── spec-sync/SKILL.md
│       ├── task-breakdown/SKILL.md
│       ├── docs-lookup/SKILL.md
│       └── ...
├── app/                         # kode Laravel — lihat ARCHITECTURE.md §11 untuk detail per-folder
├── routes/
├── resources/views/
├── database/
├── tests/
├── docker/                       # Dockerfile & config produksi (terpisah dari Sail)
├── docker-compose.yml            # digenerate oleh `php artisan sail:install` (Laravel Sail, local/dev)
├── .env.example
└── docs/
    ├── archived/                # dokumentasi historis (Discovery Doc, BAD, SRS v1.0.7, DDS v1.0.0) — sudah digantikan oleh PRD.md + ARCHITECTURE.md
    └── ui-design.jpg            # referensi desain UI yang digunakan untuk implementasi
```

---

## 8. Menjalankan Sesi Kerja Pertama dengan Agent

Karena ini pertama kali kamu mencoba alur ini, berikut urutan konkret yang disarankan:

1. Salin ketujuh file skema ini ke root proyek barumu.
2. Jalankan `opencode`, masuk mode **Plan**, lalu minta agent membantu mengisi `PRD.md` dari ide kasarmu — sebutkan idemu dalam bahasa natural, biarkan agent yang menstrukturkannya ke format FR/NFR/US sesuai template.
3. Review isi `PRD.md`, perbaiki bagian yang kurang tepat, ubah status jadi `Approved` setelah puas.
4. Masih di mode Plan, minta agent mengisi `ARCHITECTURE.md` berdasarkan `PRD.md` yang sudah disetujui.
5. Minta agent memecah `ARCHITECTURE.md` menjadi `TASK-xxx` di `TODO.md`.
6. Jalankan `/init` agar `AGENTS.md` terisi perintah teknis aktual proyekmu (build/test/lint).
7. Pindah ke mode **Build**, minta agent mengerjakan `TASK-xxx` satu per satu sesuai `WORKFLOW.md` §2 Fase 4.

---

## 9. FAQ & Troubleshooting

**Q: Agent menebak-nebak requirement yang tidak jelas, bukan bertanya. Bagaimana mencegahnya?**
A: Pastikan instruksi di `AGENTS.md` §Guardrails ("jika ambigu, buka Q-xxx, jangan menebak") benar-benar ada di root `AGENTS.md` proyekmu — ini yang secara otomatis dibaca OpenCode di awal tiap sesi.

**Q: Dokumen `PRD.md` dan `ARCHITECTURE.md` mulai tidak sinkron seiring proyek berjalan. Bagaimana mendeteksinya?**
A: Gunakan skill `spec-sync` (lihat `SKILL.md` §2) untuk memindai ID yatim/rusak, atau jalankan pengecekan manual via Traceability Matrix di `TODO.md` §3.

**Q: 9Router menampilkan angka "cost" yang besar padahal saya cuma pakai provider gratis.**
A: Itu bukan tagihan — dashboard 9Router menampilkan estimasi berapa yang **akan** kamu bayar jika pakai API berbayar langsung, sebagai indikator penghematan. Selama kamu memakai provider gratis, biaya aktualmu tetap $0.

**Q: Apakah wajib pakai 9Router?**
A: Tidak. 9Router murni opsional untuk efisiensi biaya/kuota. OpenCode bisa langsung dikonfigurasi dengan API key provider mana pun tanpa proxy tambahan.

**Q: Boleh tidak memakai semua 7 dokumen sekaligus untuk proyek kecil/prototipe?**
A: Boleh disederhanakan (mis. skip `SKILL.md` di awal), tapi `PRD.md`, `ARCHITECTURE.md`, `TODO.md`, dan `AGENTS.md` sebaiknya tetap ada dalam bentuk minimal — empat dokumen ini yang paling menentukan konsistensi kerja agent.

---

## 10. Checklist Cepat (Cheat Sheet)

- [ ] Install OpenCode (`curl -fsSL https://opencode.ai/install | bash`)
- [ ] (Opsional) Install & jalankan 9Router (`npm install -g 9router && 9router`)
- [ ] (Opsional) Hubungkan OpenCode ke 9Router via `opencode.json`
- [ ] Salin 7 file skema ke root proyek
- [ ] Isi `PRD.md` (mode Plan) → status `Approved`
- [ ] Isi `ARCHITECTURE.md` (mode Plan) → status `Approved`
- [ ] Jalankan `/init` untuk melengkapi `AGENTS.md`
- [ ] Isi `TODO.md` dengan `TASK-xxx`
- [ ] Pindah ke mode Build, kerjakan task satu per satu sesuai `WORKFLOW.md`
- [ ] Sebelum rilis, jalankan Release Checklist di `WORKFLOW.md` §5

---

## Riwayat Perubahan (Changelog)

| Versi | Tanggal | Perubahan | Oleh |
|---|---|---|---|
| 0.1.0 | `<YYYY-MM-DD>` | Draft awal dibuat | `<ISI>` |
