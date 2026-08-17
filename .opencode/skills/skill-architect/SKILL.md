---
name: skill-architect
description: Pedoman WAJIB sebelum membuat, merevisi, atau mendaftarkan skill baru apa pun di proyek ini. Gunakan setiap kali ada dorongan untuk membuat file .opencode/skills/<nama>/SKILL.md baru — baik inisiatif agent sendiri maupun permintaan eksplisit dari pengguna ("buatkan skill untuk...", "kita butuh skill baru", dsb.). Skill ini TIDAK dipakai untuk mengerjakan task produk biasa — hanya untuk proses pembuatan/perbaikan skill itu sendiri.
license: MIT
compatibility: opencode
---

# skill-architect — Pedoman Penyusunan Skill Baru

## Tujuan

Memastikan setiap skill yang lahir di proyek ini (a) benar-benar dibutuhkan berdasarkan bukti nyata, (b) berakar pada dokumen proyek yang sudah ada (`PRD.md`, `ARCHITECTURE.md`, `AGENTS.md`, `WORKFLOW.md`) — bukan karangan/asumsi agent, dan (c) ditulis dengan struktur yang bisa diaudit dan diverifikasi orang lain. Skill yang lolos pedoman ini akan konsisten kualitasnya, tidak saling tumpang tindih, dan tidak menjadi "noise" yang membingungkan agent lain di kemudian hari.

## Prinsip Dasar

1. **Skill bukan tempat menyimpan pengetahuan umum.** Jika sesuatu sudah bisa dijawab dari pengetahuan umum LLM atau cukup satu baris di `AGENTS.md`, itu BUKAN kandidat skill. Skill dicadangkan untuk prosedur spesifik proyek ini yang butuh instruksi berulang, presisi tinggi, atau konvensi lokal yang tidak jelas dari konteks lain.
2. **Grounding adalah syarat mutlak, bukan opsional.** Setiap instruksi di dalam skill harus bisa ditelusuri ke sumber: sebuah `FR-xxx`/`NFR-xxx` (`PRD.md`), `COMP-xxx`/`ADR-xxx`/`DM-xxx` (`ARCHITECTURE.md`), aturan eksplisit di `AGENTS.md`, atau proses di `WORKFLOW.md`. Jika sebuah instruksi tidak bisa dikaitkan ke salah satu sumber ini, itu tanda instruksi tersebut adalah asumsi/karangan — HARUS dihapus atau ditandai eksplisit sebagai "usulan, perlu konfirmasi pengguna", bukan ditulis seolah fakta.
3. **Lebih baik tidak membuat skill daripada membuat skill yang lemah dasarnya.** Kalau di Langkah 1 (di bawah) tidak ada bukti kebutuhan yang jelas, hentikan proses dan sampaikan itu ke pengguna — jangan lanjut membuat file hanya karena diminta.

## Alur Kerja: 7 Langkah Wajib

### Langkah 1 — Identifikasi Kebutuhan Nyata (Evidence-Based Trigger)

Skill baru HANYA boleh dibuat jika minimal satu dari kondisi berikut terpenuhi, dan agent harus bisa menyebutkan buktinya secara konkret:

| Bukti Valid | Cara memverifikasi |
|---|---|
| Task dengan pola serupa muncul berulang (≥3 kali) di `TODO.md` | Sebutkan `TASK-xxx` mana saja yang jadi bukti pola berulang tsb |
| Prosedur disebut eksplisit sebagai "wajib diikuti" di `ARCHITECTURE.md`/`AGENTS.md` tapi terlalu panjang/detail untuk muat di sana | Kutip bagian/section persisnya |
| Kesalahan berpola tercatat — agent (di sesi sebelumnya) beberapa kali salah dengan cara yang sama pada task sejenis | Jelaskan kesalahan apa dan di task mana |

**Bukti yang TIDAK valid** (jangan lanjutkan bila hanya ini alasannya): "sepertinya akan berguna nanti", "ini best practice umum di industri", permintaan pengguna yang masih generik tanpa dikaitkan ke task/dokumen proyek yang konkret. Jika pengguna meminta skill dengan alasan generik, agent wajib bertanya balik: "task/requirement spesifik mana yang akan dibantu skill ini?" sebelum lanjut ke Langkah 2.

### Langkah 2 — Cek Duplikasi

Baca `SKILL.md` §1 (Daftar Skill Proyek) dan §2 (Skill Direkomendasikan) di root, serta isi folder `.opencode/skills/` yang sudah ada. Jika sudah ada skill yang mencakup >70% kebutuhan yang sama → **revisi skill yang ada**, jangan buat baru. Skill duplikat/tumpang tindih membingungkan agent lain saat memilih skill mana yang relevan.

### Langkah 3 — Tentukan Ruang Lingkup Sempit (Single Responsibility)

Satu skill = satu prosedur/kemampuan yang jelas batasnya. Uji cepat: jika deskripsi skill butuh kata penghubung "dan" untuk menggabungkan dua hal yang tidak berkaitan langsung, itu tanda harus dipecah jadi dua skill terpisah.

### Langkah 4 — Kumpulkan Rujukan Sumber (Grounding)

Sebelum menulis satu baris instruksi pun, daftar dulu ID/dokumen yang menjadi dasar skill ini, contoh: "berdasarkan `ARCHITECTURE.md` ADR-001 dan ADR-003, `AGENTS.md` §Guardrails, `PRD.md` FR-004". Daftar ini nanti wajib muncul di badan skill sebagai bagian **Dasar/Rujukan** (lihat Langkah 6) agar siapa pun bisa mengaudit dari mana instruksi itu berasal.

Jika skill menyentuh tool/library eksternal (Laravel, Sail, package Composer/NPM apa pun), instruksi teknisnya **wajib** merujuk ke dokumentasi resmi yang terdaftar di `AGENTS.md` §"Dokumentasi Resmi yang Wajib Dirujuk" — bukan ditulis dari ingatan/memori pelatihan. Jika tool tersebut belum ada di tabel itu, tambahkan dulu barisnya di `AGENTS.md` sebelum menulis skill yang bergantung padanya.

### Langkah 5 — Tulis Frontmatter & Deskripsi Trigger

```yaml
---
name: nama-skill-kebab-case
description: Deskripsi spesifik + kata kunci pemicu konkret (bukan umum), agar agent lain tahu kapan skill ini relevan tanpa membaca isi penuh. Sebutkan situasi nyata proyek, bukan istilah generik.
license: MIT
compatibility: opencode
---
```

Aturan penamaan mengikuti `SKILL.md` root §0: huruf kecil, kebab-case, tanpa awalan `-`, tanpa PascalCase/double-dash/underscore.

### Langkah 6 — Tulis Badan Skill dengan Struktur Baku

```markdown
# <nama-skill>

## Tujuan
1-2 kalimat, spesifik.

## Dasar/Rujukan
- <ID/dokumen 1 dari Langkah 4>
- <ID/dokumen 2>

## Langkah-Langkah
1. <langkah konkret, actionable, bisa diverifikasi>
2. <langkah berikutnya>
...

## Kondisi Berhenti / Eskalasi
Kapan agent harus berhenti dan bertanya ke pengguna alih-alih menebak/melanjutkan
(mis. requirement ambigu, dokumentasi resmi tidak ditemukan, konflik dengan ADR yang ada).

## Contoh (opsional)
Contoh konkret penerapan skill ini di konteks proyek — boleh dilewati jika langkah
sudah cukup jelas tanpa contoh.
```

Instruksi di bagian **Langkah-Langkah** harus berupa tindakan konkret yang bisa diverifikasi berhasil/gagal — bukan nasihat abstrak seperti "tulis kode yang bersih" atau "ikuti best practice".

### Langkah 7 — Validasi Sebelum Didaftarkan (Checklist Self-Review)

Sebelum skill dianggap selesai, jawab semua poin ini dengan jujur:

- [ ] Ada bukti kebutuhan nyata dari Langkah 1 (bukan "sepertinya berguna")?
- [ ] Sudah dicek tidak duplikat dengan skill lain (Langkah 2)?
- [ ] Ruang lingkup sempit, single-responsibility (Langkah 3)?
- [ ] Setiap instruksi bisa ditelusuri ke ID/dokumen sumber yang eksplisit dicantumkan di bagian "Dasar/Rujukan" (Langkah 4 & 6)?
- [ ] Trigger keywords di `description` spesifik terhadap proyek ini, bukan generik?
- [ ] Semua langkah actionable & terverifikasi, bukan opini/nasihat kabur?
- [ ] Jika menyentuh tool eksternal, instruksinya merujuk dokumentasi resmi (bukan ingatan), dan tool-nya sudah terdaftar di `AGENTS.md`?

**Jika ada satu saja poin yang gagal:** JANGAN daftarkan skill ini. Perbaiki dulu bagian yang gagal, atau — jika akar masalahnya ada di Langkah 1 (tidak ada bukti kebutuhan nyata) — batalkan pembuatan skill ini sepenuhnya dan sampaikan alasannya ke pengguna.

### Langkah 8 — Daftarkan di `SKILL.md` Root

Tambahkan satu baris baru di tabel `SKILL.md` §1 root (Nama Skill, Lokasi, Deskripsi Singkat, Trigger/Kapan Dipakai). Ini langkah terakhir, dilakukan HANYA setelah checklist Langkah 7 lolos semua.

## Larangan Keras (Anti-Hallucination Guardrails)

- Jangan membuat skill "just in case"/"siapa tahu berguna" tanpa bukti dari Langkah 1.
- Jangan menyalin saran umum/generic best-practice dari luar tanpa mengaitkannya secara eksplisit ke konteks proyek ini (Laravel 13, arsitektur monolitik, autentikasi session-based, dst. — lihat `ARCHITECTURE.md`).
- Jangan membuat skill yang isinya menduplikasi `AGENTS.md`/`ARCHITECTURE.md` secara verbatim — skill harus menambah nilai prosedural (langkah konkret), bukan salinan kebijakan yang sudah ada.
- Jangan menuliskan klaim teknis (versi API, sintaks framework, opsi konfigurasi) tanpa rujukan dokumentasi resmi yang tercatat — sesuai aturan di `AGENTS.md` §"Dokumentasi Resmi yang Wajib Dirujuk".
- Jangan mendaftarkan skill ke `SKILL.md` root sebelum checklist Langkah 7 selesai dan lolos.

## Kondisi Berhenti / Eskalasi

- Bukti kebutuhan di Langkah 1 lemah/tidak ada → berhenti, tanyakan ke pengguna task/requirement konkret apa yang melandasinya.
- Ditemukan skill lain yang >70% tumpang tindih di Langkah 2 → berhenti, tawarkan revisi skill yang ada alih-alih membuat baru.
- Skill akan menyentuh tool eksternal yang dokumentasi resminya tidak bisa diakses/ditemukan → berhenti, jangan menebak dari memori; laporkan ketidakpastian ke pengguna dan, bila relevan, catat sebagai `Q-xxx` baru di `PRD.md` §13.

## Kapan Meng-update / Men-deprecate Skill

Jika perubahan pada `ARCHITECTURE.md`/`AGENTS.md` (mis. ADR baru yang men-supersede ADR lama) membuat sebuah skill jadi usang, tandai skill tersebut **Deprecated** di tabel `SKILL.md` §1 root — jangan dihapus diam-diam, agar jejak audit (kenapa skill itu pernah dibuat, dan kenapa berhenti relevan) tetap tersimpan.
