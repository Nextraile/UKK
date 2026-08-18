# SKILL.md — Skill Registry (Indeks Kemampuan Agent)

> **Penting — perbedaan dengan konvensi native OpenCode:**
> OpenCode secara native membaca skill dari **folder**, bukan satu file: `.opencode/skills/<nama-skill>/SKILL.md` (satu folder per skill, boleh berisi script/referensi tambahan). File `SKILL.md` di root ini **bukan** skill yang dibaca otomatis oleh OpenCode — ini adalah **indeks manusiawi** yang mendaftar semua skill yang ada di `.opencode/skills/`, supaya siapa pun (manusia atau agent) bisa melihat sekilas kemampuan apa saja yang tersedia tanpa membuka setiap folder satu-satu.
>
> Alur kerjanya: **SKILL.md (root, indeks)** ──merujuk──▶ **`.opencode/skills/<nama>/SKILL.md` (definisi asli, dibaca OpenCode)**

---

## 0. Cara Menambah Skill Baru

1. Buat folder: `.opencode/skills/<nama-skill-kebab-case>/`
2. Buat `SKILL.md` di dalamnya dengan frontmatter YAML wajib:
   ```yaml
   ---
   name: nama-skill
   description: Deskripsi spesifik + kata kunci pemicu (1-1024 karakter). Ini yang dibaca agent pertama kali untuk memutuskan apakah skill ini relevan.
   license: MIT
   compatibility: opencode
   ---
   ```
3. Isi badan file langsung dengan instruksi (tanpa basa-basi "Skill ini untuk..." — itu sudah ada di `description`).
4. Daftarkan skill tersebut di tabel §1 di bawah ini.
5. Skill yang butuh script/dokumen pendukung: taruh di subfolder `scripts/` atau `references/` di dalam folder skill yang sama.

**Aturan penamaan:** huruf kecil, kebab-case (`git-release`, `api-doc-writer`). Tidak boleh: awalan `-`, PascalCase, double-dash, underscore.

---

## 0.1 Alur Kerja Agent Sebelum Membuat Skill Baru (Governance)

> §0 di atas menjelaskan langkah **mekanis** (folder, frontmatter, daftar). Sebelum sampai ke langkah mekanis itu, agent WAJIB melalui **alur keputusan** agar skill yang dibuat punya dasar kuat dan tidak asal buat. Alur lengkap (7 langkah + checklist validasi + larangan keras) didefinisikan sebagai skill tersendiri:
>
> **`.opencode/skills/skill-architect/SKILL.md`**
>
> Skill ini bersifat **meta** — dipanggil setiap kali agent (atau manusia) mempertimbangkan membuat/merevisi skill apa pun di proyek ini. Ringkasan gate-nya:
>
> 1. **Evidence-based trigger** — ada bukti kebutuhan nyata (task berulang di `TODO.md`, prosedur wajib yang disebut eksplisit di `ARCHITECTURE.md`/`AGENTS.md`, atau kesalahan berpola yang tercatat) — bukan "sepertinya berguna".
> 2. **Cek duplikasi** — pastikan §1/§2 di bawah dan folder `.opencode/skills/` belum mencakup kebutuhan ini.
> 3. **Ruang lingkup sempit** — satu skill = satu prosedur.
> 4. **Grounding wajib** — setiap skill baru harus mencantumkan ID/dokumen sumber (`FR-xxx`/`COMP-xxx`/`ADR-xxx`/bagian `AGENTS.md`) yang menjustifikasinya. Tidak ada rujukan → skill dianggap mengada-ada dan ditolak.
> 5. **Checklist validasi** sebelum didaftarkan (lihat isi skill `skill-architect` untuk daftar lengkap).
>
> **Agent DILARANG membuat file skill baru tanpa menjalankan prosedur `skill-architect` terlebih dahulu.** Ini berlaku juga saat permintaan pembuatan skill datang langsung dari pengguna — jika evidence/grounding-nya lemah, agent wajib menyampaikan itu ke pengguna alih-alih langsung membuat filenya.

---

## 1. Daftar Skill Proyek Ini

> Isi tabel ini setiap kali skill baru ditambahkan. Kolom "Trigger" membantu agent (dan manusia) tahu kapan skill ini relevan dipanggil.

| Nama Skill | Lokasi | Deskripsi Singkat | Trigger / Kapan Dipakai |
|---|---|---|---|
| **Development Skills (Project-Specific)** | | | |
| `skill-architect` | `.opencode/skills/skill-architect/` | Pedoman meta (governance) untuk membuat/merevisi skill baru | Setiap kali ada dorongan membuat skill baru |
| `spec-sync` | `.opencode/skills/spec-sync/` | Verifikasi konsistensi ID antar dokumen (PRD/ARCHITECTURE/TODO) | Setelah revisi PRD/ARCHITECTURE, sebelum fase Build |
| `task-breakdown` | `.opencode/skills/task-breakdown/` | Memecah FR/COMP menjadi TASK ≤ 1 hari kerja | Fase Planning (WORKFLOW §2 Fase 3) |
| `adr-writer` | `.opencode/skills/adr-writer/` | Menulis ADR dengan format baku | Keputusan teknis signifikan (library/pola/infra baru) |
| `doc-consistency-check` | `.opencode/skills/doc-consistency-check/` | Scan istilah & ID yang tidak konsisten antar dokumen | Sebelum dokumen dinaikkan ke Approved |
| `test-writer` | `.opencode/skills/test-writer/` | Menulis test yang memvalidasi acceptance criteria FR/US | Setiap implementasi TASK, sebelum Done |
| `docs-lookup` | `.opencode/skills/docs-lookup/` | Cari & rangkum dokumentasi resmi sebelum implementasi | Sebelum implementasi TASK yang menyentuh tool/library |
| `doc-sync-checker` | `.opencode/skills/doc-sync-checker/` | Verifikasi konsistensi dokumentasi setelah perubahan metadata (skill count, version, FR/COMP), integrasi dengan spec-sync | Setelah edit SKILL.md/ARCHITECTURE.md/PRD.md, sebelum build phase, saat toast notification muncul |
| `skill-suggester` | `.opencode/skills/skill-suggester/` | Review pola berulang dari aksi user (≥3 occurrences) dan sarankan pembuatan skill baru dengan evidence + skill-architect integration | Saat toast "Pattern detected", user tanya "skill suggestions", "review patterns", setelah session.idle analysis |
| **Design Skills (from ui-ux-pro-max-cli)** | | | |
| `ui-ux-pro-max` | `.opencode/skills/ui-ux-pro-max/` | UI/UX design intelligence dengan database searchable (67 styles, 161 palettes, 57 font pairings, 99 UX guidelines) | Desain UI/UX, pilih style/color/font, UX guidelines |
| `design` | `.opencode/skills/design/` | Unified design skill: brand, tokens, UI, logo, CIP, slides, banners, social photos, icons | Request desain apapun (brand, logo, banner, presentasi, social media) |
| `brand` | `.opencode/skills/brand/` | Brand voice, visual identity, messaging, asset management, brand consistency | Brand voice, style guide, brand assets, tone of voice |
| `design-system` | `.opencode/skills/design-system/` | Token architecture (3-layer: primitive→semantic→component), component specs, CSS variables | Design tokens, systematic design, Tailwind theme config |
| `ui-styling` | `.opencode/skills/ui-styling/` | shadcn/ui components (Radix UI + Tailwind), canvas-based visual designs | Build UI dengan React, accessible components, responsive layouts, dark mode |
| `slides` | `.opencode/skills/slides/` | Strategic HTML presentations dengan Chart.js, design tokens, copywriting formulas | Buat presentasi/pitch deck dengan data visualization |
| `banner-design` | `.opencode/skills/banner-design/` | Design banners untuk social media, ads, website heroes, print (22 styles, multi-platform) | Desain banner/cover untuk Facebook, Twitter, LinkedIn, YouTube, Instagram, Google Ads, web hero |
| **Frontend/UI Skills** | | | |
| `impeccable` | `.opencode/skills/impeccable/` | UI/UX design craft tool dengan commands (shape, audit, polish, animate, colorize, typeset, dll) - production-grade frontend quality | Design/redesign/audit/improve frontend interface, landing pages, dashboards, UX review, visual hierarchy |
| **Skill Management & Discovery** | | | |
| `find-skills` | `.opencode/skills/find-skills/` | Discover & install skills dari ecosystem (skills.sh) menggunakan Skills CLI | User tanya "how do I do X", cari skill eksternal, extend capabilities |
| **UI/UX AI Guidance** | | | |
| `taste-skill` | `.opencode/skills/taste-skill/` | UI/UX design intelligence (design-taste-frontend v2) - mencegah generic/boring AI-generated interfaces, guidelines untuk layout/typography/motion/spacing | Design/redesign frontend interface, landing page, dashboard, user-facing UI - complement DESIGN.md dengan anti-slop guidance |

---

## 2. Skill yang Direkomendasikan untuk Skema Ini

> Skill-skill berikut dirancang khusus untuk mendukung alur `PRD.md → ARCHITECTURE.md → TODO.md` di skema ini. Buat sesuai kebutuhan proyek (tidak wajib semua ada sejak awal).

### `skill-architect` — **wajib ada sejak awal, sebelum skill lain dibuat**
- **Tujuan:** pedoman meta (governance) yang wajib diikuti agent setiap kali membuat atau merevisi skill apa pun di proyek ini — memastikan skill yang lahir punya bukti kebutuhan nyata dan berakar pada dokumen proyek (`PRD.md`/`ARCHITECTURE.md`/`AGENTS.md`), bukan hasil karangan/asumsi.
- **Kapan dipakai:** setiap kali ada dorongan (dari agent maupun permintaan pengguna) untuk membuat skill baru — lihat `SKILL.md` §0.1 root untuk ringkasan gate-nya, dan folder skill ini untuk prosedur 7-langkah lengkap + checklist validasi.

### `spec-sync`
- **Tujuan:** memverifikasi konsistensi ID antar dokumen — memastikan setiap `FR-xxx` di `PRD.md` punya `COMP-xxx` di `ARCHITECTURE.md` dan minimal satu `TASK-xxx` di `TODO.md`; melaporkan ID yatim (orphan) atau referensi rusak.
- **Kapan dipakai:** sebelum memulai fase Build baru, atau setelah `PRD.md`/`ARCHITECTURE.md` direvisi.

### `task-breakdown`
- **Tujuan:** memecah satu `FR-xxx`/`COMP-xxx` menjadi beberapa `TASK-xxx` yang berukuran wajar (≤ 1 hari kerja), lengkap dengan dependency antar-task.
- **Kapan dipakai:** fase Planning (lihat `WORKFLOW.md` §2 Fase 3).

### `adr-writer`
- **Tujuan:** membantu menulis Architecture Decision Record baru dengan format Konteks → Keputusan → Alternatif → Konsekuensi yang konsisten.
- **Kapan dipakai:** setiap kali ada keputusan teknis signifikan (pilihan library/pola/infra baru).

### `doc-consistency-check`
- **Tujuan:** memindai `PRD.md`, `ARCHITECTURE.md`, `TODO.md` untuk istilah yang dipakai tapi tidak ada di Glosarium (`PRD.md` §6), atau ID yang disebut tapi tidak pernah didefinisikan.
- **Kapan dipakai:** sebelum sebuah dokumen dinaikkan statusnya ke `Approved`.

### `test-writer`
- **Tujuan:** menulis test yang secara eksplisit memvalidasi acceptance criteria dari `FR-xxx`/`US-xxx` tertentu (bukan sekadar test generik).
- **Kapan dipakai:** setiap implementasi `TASK-xxx` di fase Build, sebelum status diubah ke `Done`.

### `docs-lookup`
- **Tujuan:** mencari & merangkum dokumentasi resmi (bukan blog/tutorial pihak ketiga) untuk tool/library yang dipakai proyek — terutama Laravel 13 dan package Composer/NPM lain — sebelum agent menulis kode yang menyentuhnya, sesuai aturan di `AGENTS.md` §Dokumentasi & Referensi Eksternal.
- **Kapan dipakai:** sebelum implementasi `TASK-xxx` apa pun yang menyentuh tool/library baru, atau API yang berpotensi berubah antar versi major (mis. upgrade Laravel).

> Skill di luar daftar ini (mis. `code-reviewer`, `git-commit-writer`, `readme-generator`) adalah skill umum non-spesifik-proyek — bisa dipasang langsung dari komunitas OpenCode (lihat `MANUAL.md` §Setup untuk caranya) tanpa perlu didefinisikan ulang di sini.

---

## 3. Skill Global vs Proyek

| Scope | Lokasi | Ikut ter-commit ke Git? | Contoh isi |
|---|---|---|---|
| Proyek (project-local) | `.opencode/skills/` | Ya | Skill spesifik domain proyek ini (tabel §1–§2) |
| Personal/global | `~/.config/opencode/skills/` | Tidak | Preferensi pribadi lintas-proyek |

---

## 4. Riwayat Perubahan (Changelog)

| Versi | Tanggal | Perubahan | Oleh |
|---|---|---|---|
| 0.1.0 | `<YYYY-MM-DD>` | Draft awal dibuat | `<ISI>` |
| 0.2.0 | `<YYYY-MM-DD>` | Ditambahkan skill `docs-lookup` | `<ISI>` |
| 0.3.0 | `<YYYY-MM-DD>` | Ditambahkan §0.1 Alur Kerja Governance sebelum membuat skill baru + skill meta `skill-architect` (wajib dijalankan sebelum skill lain dibuat) | `<ISI>` |
| 0.4.0 | 2026-08-12 | Dibuat 6 skill direkomendasikan: `spec-sync`, `task-breakdown`, `adr-writer`, `doc-consistency-check`, `test-writer`, `docs-lookup`. Semua dibuat melalui prosedur `skill-architect` (Langkah 1-8, lolos checklist validasi). Diisi §1 Daftar Skill dengan 7 skill (termasuk `skill-architect` yang sudah ada sebelumnya). | OpenCode |
| 0.5.0 | 2026-08-13 | **Recovery skills yang hilang + install design skills:** (1) Rekonstruksi 6 skills yang terhapus (`spec-sync`, `task-breakdown`, `adr-writer`, `doc-consistency-check`, `test-writer`, `docs-lookup`) dengan improved version (tambahan best practices, checklist validasi, contoh lengkap, kondisi eskalasi yang lebih detail). (2) Install 7 design skills via `npx ui-ux-pro-max-cli init --ai opencode`: `ui-ux-pro-max`, `design`, `brand`, `design-system`, `ui-styling`, `slides`, `banner-design`. Total skills sekarang: 14 (7 development + 7 design). | OpenCode |
| 0.6.0 | 2026-08-15 | **Skill installation wave 2:** (1) Registered `impeccable` yang sudah terinstall sebelumnya (v4.1.1 - UI craft tool dengan 20+ commands). (2) Verified `npx impeccable install` - skills sudah up-to-date, tidak ada yang ditambahkan. (3) Install `find-skills` (Vercel Labs) - discovery & installation dari skills.sh ecosystem menggunakan Skills CLI. (4) Conflict analysis: No conflicts detected - `impeccable` fokus pada UI craft/iteration, design skills fokus pada brand/tokens/assets, `find-skills` untuk external discovery. Total skills sekarang: 16 (7 development + 7 design + 1 UI craft + 1 skill discovery). | OpenCode |
| 0.7.0 | 2026-08-15 | **Phase 1: Doc Sync Automation System:** (1) Created `doc-sync-checker` skill - verifikasi konsistensi dokumentasi setelah perubahan metadata dengan integrasi spec-sync. (2) Implemented `doc-sync-watcher` plugin - real-time file watcher untuk 7 core docs dengan SQLite storage. (3) Implemented `sync-docs` custom tool - check/preview/apply modes untuk sync execution. (4) Shared libraries: doc-parser (extract metadata), sync-planner (determine targets), db-schema (SQLite). (5) Dependencies added: better-sqlite3, fast-diff, gray-matter. Total skills sekarang: 17 (8 development + 7 design + 1 UI craft + 1 skill discovery). **System capabilities:** Auto-detect doc changes, toast notifications, confidence scoring, spec-sync integration, manual approval flow. | OpenCode |
| 0.8.0 | 2026-08-15 | **Phase 2: Pattern Tracking & Skill Suggestions:** (1) Created `skill-suggester` skill - review pola berulang dari aksi user, generate skill drafts dengan evidence, integrate dengan skill-architect. (2) Implemented `pattern-tracker` plugin - track tool calls/prompts/file edits, detect ≥3 repetitions, confidence scoring, session.idle analysis. (3) Extended SQLite schema: 4 new tables (session_actions, patterns, pattern_history, declined_patterns). (4) New library: pattern-analyzer.mjs (detect, score, ground, generate drafts, decline tracking). (5) Decline cooldown: 30-day, auto-expiry. Total skills sekarang: 18 (9 development + 7 design + 1 UI craft + 1 skill discovery). **System capabilities:** Auto-detect repetitive patterns, toast suggestions, skill draft generation, 30-day decline cooldown, cross-session pattern tracking. | OpenCode |
| 0.9.0 | 2026-08-16 | **External Tools Installation:** (1) Installed `taste-skill` (design-taste-frontend v2 experimental) to `.opencode/skills/taste-skill/` - UI/UX AI guidance untuk mencegah generic/boring interfaces, complements DESIGN.md dengan anti-slop design rules. (2) Installed codegraph v1.5.0 globally - code intelligence MCP server untuk faster agent context (44% lower cost, 62% fewer tokens, 88% fewer tool calls per benchmark). (3) Indexed SewaKost codebase: 230 files, 4,754 nodes, 13,565 edges (PHP/JavaScript/TypeScript/Python). (4) Added .codegraph/ to .gitignore. Total skills sekarang: 19 (9 development + 7 design + 1 UI craft + 1 skill discovery + 1 UI/UX AI guidance). **MCP tools available:** codegraph_explore, codegraph_node, codegraph_query, codegraph_files. | OpenCode |
