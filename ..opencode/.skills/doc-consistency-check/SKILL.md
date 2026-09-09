---
name: doc-consistency-check
description: Memindai PRD.md, ARCHITECTURE.md, TODO.md untuk istilah yang dipakai tapi tidak ada di Glosarium (PRD.md §6), atau ID (FR-xxx/NFR-xxx/COMP-xxx/TASK-xxx/US-xxx) yang disebut tapi tidak pernah didefinisikan. Gunakan sebelum sebuah dokumen dinaikkan statusnya ke Approved. Trigger: "doc consistency", "cek glosarium", "istilah tidak terdefinisi", "validasi dokumen", "sebelum approve", "ID tidak ditemukan".
license: MIT
compatibility: opencode
---

# doc-consistency-check — Validasi Konsistensi Istilah & ID Antar Dokumen

## Tujuan

Mendeteksi inkonsistensi terminologi dan referensi ID antar dokumen proyek (`PRD.md`, `ARCHITECTURE.md`, `TODO.md`) sebelum dokumen tersebut dinaikkan statusnya ke "Approved". Mencegah istilah domain yang tidak terdefinisi, ID yang dirujuk tapi tidak pernah dibuat, atau duplikasi definisi yang bisa membingungkan developer dan stakeholder.

## Dasar/Rujukan

- **`PRD.md` §6 Glosarium:** Sumber tunggal kebenaran (single source of truth) untuk semua istilah domain proyek
- **`WORKFLOW.md` §2 Fase 2 (Design) & Fase 3 (Planning):** Gate quality sebelum dokumen naik status ke Approved
- **`SKILL.md` §2 doc-consistency-check:** Skill direkomendasikan sebagai gate sebelum document approval
- **`ARCHITECTURE.md` §4-§7:** Definisi COMP-xxx, DM-xxx, ADR-xxx yang harus konsisten dengan PRD.md

## Langkah-Langkah

### 1. Ekstrak Semua Definisi ID dari Dokumen

**Dari `PRD.md`:**
- Scan §4 (Functional Requirements): ekstrak semua `FR-xxx` yang didefinisikan (format `### FR-xxx: ...`)
- Scan §5 (Non-Functional Requirements): ekstrak semua `NFR-xxx` yang didefinisikan
- Scan §9 (User Stories, jika ada): ekstrak semua `US-xxx` yang didefinisikan
- Scan §11 (Risk Register, jika ada): ekstrak semua `RISK-xxx` yang didefinisikan
- Scan §13 (Open Questions, jika ada): ekstrak semua `Q-xxx` yang didefinisikan

**Dari `ARCHITECTURE.md`:**
- Scan §4: ekstrak semua `COMP-xxx` yang didefinisikan (format `### COMP-xxx: ...`)
- Scan §5: ekstrak semua `DM-xxx` yang didefinisikan (format `#### DM-xxx: ...`)
- Scan §7: ekstrak semua `ADR-xxx` yang didefinisikan (format `#### ADR-xxx: ...`)

**Dari `TODO.md`:**
- Scan seluruh dokumen: ekstrak semua `TASK-xxx` yang didefinisikan (format `### TASK-xxx: ...`)

**Output Langkah 1:** Daftar lengkap semua ID yang terdefinisi di setiap dokumen (ini adalah "ID valid").

### 2. Ekstrak Semua Referensi ID di Dokumen

**Dari `PRD.md`:**
- Scan seluruh teks (di luar definisi): cari semua string yang match pattern `FR-\d{3}`, `NFR-\d{3}`, `US-\d{3}`, `RISK-\d{3}`, `Q-\d{3}`, `COMP-\d{3}` (PRD tidak boleh merujuk TASK/ADR/DM — jika ada, ini error)
- Catat lokasi (section & line number)

**Dari `ARCHITECTURE.md`:**
- Scan seluruh teks (di luar definisi): cari semua string yang match pattern ID proyek
- Catat lokasi

**Dari `TODO.md`:**
- Scan seluruh teks (di luar definisi): cari semua string yang match pattern ID proyek
- Catat lokasi

**Output Langkah 2:** Daftar semua referensi ID di setiap dokumen beserta lokasinya.

### 3. Validasi Referensi ID (Cross-Check dengan Definisi)

Untuk setiap referensi ID yang ditemukan di Langkah 2:

```
FOR EACH referenced-ID IN all-documents:
  IF referenced-ID NOT IN defined-IDs (dari Langkah 1):
    REPORT: "Broken reference: [referenced-ID] disebutkan di [dokumen]:[section]:[line] tapi tidak terdefinisi di [dokumen sumber yang seharusnya]"
```

**Contoh error:**
```
❌ Broken reference: FR-200 disebutkan di ARCHITECTURE.md §4 COMP-001 baris 123, tapi tidak terdefinisi di PRD.md §4
```

### 4. Ekstrak Glosarium dari PRD.md §6

Baca `PRD.md` §6 (Glosarium), ekstrak semua istilah yang terdefinisi. Format umumnya:

```markdown
- **Istilah:** Definisi singkat
```

Atau:

```markdown
| Istilah | Definisi |
|---|---|
| Kost | ... |
| Tenant | ... |
```

**Output Langkah 4:** Daftar semua istilah yang ada di glosarium (case-insensitive untuk matching).

### 5. Identifikasi Istilah Domain yang Tidak Terdefinisi di Glosarium

**Heuristik untuk mendeteksi "istilah domain":**
- Kata benda yang capitalize di tengah kalimat (bukan di awal kalimat, bukan akronim umum)
- Kata yang muncul berulang di multiple section (≥3 kali di dokumen berbeda)
- Kata yang spesifik untuk domain bisnis (bukan kata umum seperti "user", "system", "database" — kecuali sudah didefinisikan spesifik di glosarium, mis. "User: Actor yang login ke sistem")

**Penting:** Ini heuristik, bukan rule ketat. Agent harus punya judgment: jangan report "Laravel", "MySQL", "Docker" sebagai missing term (itu nama proper tool/library), tapi report "Rental", "Kost", "Room Type" jika belum ada di glosarium.

**Cara validasi:**

```
FOR EACH potential-term IN (PRD.md, ARCHITECTURE.md, TODO.md):
  IF potential-term is domain-specific (NOT common word, NOT tool name):
    IF potential-term NOT IN glossary (case-insensitive):
      REPORT: "Missing glossary entry: '[potential-term]' digunakan di [dokumen]:[section] tapi tidak terdefinisi di PRD.md §6 Glosarium"
```

**Threshold untuk report:** Hanya report jika istilah muncul ≥3 kali di dokumen (untuk menghindari false positive dari kata yang hanya muncul sekali sebagai contoh).

### 6. Deteksi Duplikasi Definisi ID

**Check duplikasi ID di dokumen yang sama:**

```
FOR EACH ID-pattern (FR-xxx, COMP-xxx, dst.):
  FOR EACH document:
    IF ID-pattern defined more than once in document:
      REPORT: "Duplicate ID: [ID-xxx] terdefinisi 2+ kali di [dokumen] (baris [line1], [line2])"
```

**Contoh error:**
```
❌ Duplicate ID: FR-023 terdefinisi 2 kali di PRD.md (baris 345 dan baris 678)
```

Ini indikasi copy-paste error atau merge conflict yang tidak terdeteksi.

### 7. Deteksi Inkonsistensi Istilah (Variasi Spelling/Capitalization)

Cari istilah yang mirip tapi tidak sama persis (kemungkinan typo atau inkonsistensi):

- "kost" vs "Kost" vs "KOST" (jika glosarium definisikan "Kost", gunakan itu konsisten)
- "Room Type" vs "room type" vs "RoomType" vs "room_type"
- "Tenant" vs "tenant" (jika glosarium definisikan "Tenant", gunakan itu sebagai proper noun)

**Cara validasi:**

```
FOR EACH glossary-term:
  Find all variations of term in documents (case-insensitive, space/underscore/camelCase variations)
  IF >1 variation used AND frequency-of-canonical-form < 80% of total:
    REPORT: "Inconsistent term usage: '[term]' muncul dalam [N] variasi ([variant1]: X kali, [variant2]: Y kali). Glosarium mendefinisikan '[canonical-form]' — gunakan itu konsisten."
```

**Threshold:** Hanya report jika inkonsistensi signifikan (canonical form <80% dari total usage).

### 8. Generate Report

**Jika semua check lolos:**

```
✅ Doc Consistency Check PASSED

Summary:
- [N] FR-xxx defined, all references valid
- [M] COMP-xxx defined, all references valid
- [P] Glossary terms defined, no missing terms detected
- 0 broken references
- 0 duplicate IDs
- 0 significant inconsistencies

Document ready for Approved status.
```

**Jika ada error:**

```
❌ Doc Consistency Check FAILED

[Broken References — ID tidak terdefinisi]
- FR-200 referenced in ARCHITECTURE.md §4 COMP-001:123, not defined in PRD.md
- TASK-045 referenced in TODO.md section "Sprint 2":456, not defined anywhere

[Missing Glossary Terms — istilah domain tidak di glosarium]
- "Room Type" used 12 times in PRD.md & ARCHITECTURE.md, not in PRD.md §6 Glossary
- "Rental Period" used 8 times, not in Glossary

[Duplicate IDs]
- FR-023 defined 2 times in PRD.md (lines 345, 678)

[Inconsistent Term Usage]
- "kost" vs "Kost": glosarium definisikan "Kost" (capitalize), tapi "kost" (lowercase) muncul 15 kali di ARCHITECTURE.md — gunakan "Kost" konsisten

Action required:
1. Fix broken references (typo FR-200 → FR-020? atau tambah FR baru?)
2. Add missing terms to PRD.md §6 Glossary
3. Remove duplicate FR-023 definition (keep one, delete the other)
4. Standardize "Kost" capitalization across all documents

Run doc-consistency-check again after fixes.
```

## Kondisi Berhenti / Eskalasi

- **Dokumen masih dalam status Draft awal (banyak placeholder `<ISI>`)** → Berhenti, sampaikan bahwa doc-consistency-check hanya bermakna untuk dokumen yang sudah substantial (≥70% terisi). Jika masih Draft early-stage, tunda check ini hingga konten lebih lengkap.
- **Ditemukan >50 broken references atau missing terms** → Ini tanda dokumen sangat out-of-sync atau belum siap untuk quality gate. Laporkan jumlah total error (jangan list semua 50+, terlalu panjang), kelompokkan per kategori, dan rekomendasikan:
  1. Perbaiki broken references dulu (kemungkinan typo atau renumbering yang salah)
  2. Review ulang glosarium — apakah istilah domain yang sering muncul sudah terdefinisi
  3. Jalankan doc-consistency-check ulang setelah perbaikan mayor ini
- **Istilah yang di-report sebagai "missing" sebenarnya bukan domain term (false positive)** → Ini limitation heuristik. Jika agent tidak yakin apakah suatu term adalah domain-specific, **report dengan disclaimer**: "Potential missing term (konfirmasi ke pengguna jika ini false positive): ..."
- **Format dokumen tidak standar (section numbering berubah, atau ID tidak pakai prefix yang benar)** → Berhenti, laporkan section mana yang tidak sesuai ekspektasi format skema ini (mis. "§6 Glosarium tidak ditemukan di PRD.md"), minta pengguna perbaiki struktur dokumen dulu.

## Contoh Output

### Contoh 1: Check Passed (Minimal Issues)

```
✅ Doc Consistency Check PASSED

Validation Summary:
- 129 FR-xxx defined in PRD.md, all 187 references valid
- 33 NFR-xxx defined in PRD.md, all 45 references valid
- 9 COMP-xxx defined in ARCHITECTURE.md, all 23 references valid
- 16 DM-xxx defined in ARCHITECTURE.md, all 34 references valid
- 19 ADR-xxx defined in ARCHITECTURE.md, all 27 references valid
- 47 TASK-xxx defined in TODO.md, all 68 references valid
- 25 glossary terms defined in PRD.md §6
- 0 broken references
- 0 duplicate IDs
- 2 minor inconsistencies (non-blocking):
  - "tenant" lowercase used 3 times in ARCHITECTURE.md §8 (Glosarium define "Tenant" capitalize — suggest update untuk konsistensi, tapi tidak blocking approval)

Overall: Document ready for Approved status. Minor inconsistencies di atas bisa diperbaiki setelah approval (tidak blocking).
```

### Contoh 2: Check Failed (Multiple Issues)

```
❌ Doc Consistency Check FAILED

Issues Found: 12 total (5 broken references, 3 missing terms, 2 duplicates, 2 inconsistencies)

[1. Broken References — ID tidak terdefinisi]
- FR-047 referenced in ARCHITECTURE.md §4 COMP-002:line 234
  → Not defined in PRD.md §4 (last FR is FR-129)
  → Possible typo? FR-047 does not exist. Check if meant FR-046 or FR-048.

- COMP-010 referenced in TODO.md TASK-023:line 567
  → Not defined in ARCHITECTURE.md §4 (last COMP is COMP-009)
  → Typo or outdated reference? Verify with ARCHITECTURE.md.

- ADR-025 referenced in ARCHITECTURE.md §4 COMP-007:line 890
  → Not defined in ARCHITECTURE.md §7 (last ADR is ADR-019)
  → Likely placeholder or future ADR not yet written.

[2. Missing Glossary Terms — istilah domain tidak di glosarium]
- "Room Type" used 18 times across PRD.md (§4 FR-014, FR-015, FR-016) & ARCHITECTURE.md (§4 COMP-003, §5 DM-004)
  → Not defined in PRD.md §6 Glossary
  → Action: Add definition to Glossary, e.g., "Room Type: Kategori kamar (single, double, suite) dengan harga & fasilitas berbeda."

- "Rental Lifecycle" used 9 times in ARCHITECTURE.md §4 COMP-006
  → Not defined in PRD.md §6 Glossary
  → Action: Add definition or reference to state machine diagram.

- "QRIS" used 7 times in PRD.md §4 FR-085, FR-087 & ARCHITECTURE.md ADR-014
  → Not defined in PRD.md §6 Glossary
  → Action: Add definition, e.g., "QRIS: Quick Response Code Indonesian Standard — format QR code standar Indonesia untuk pembayaran digital."

[3. Duplicate IDs]
- FR-089 defined 2 times in PRD.md:
  → Line 1234: "### FR-089: Admin dapat melihat daftar semua kost"
  → Line 1456: "### FR-089: Admin dapat mengubah status kost"
  → Action: Renumber salah satu (kemungkinan copy-paste error). Check git history untuk tahu mana yang original.

- TASK-012 defined 2 times in TODO.md:
  → Line 345: "### TASK-012: Buat migration users"
  → Line 678: "### TASK-012: Buat controller AuthController"
  → Action: Renumber salah satu.

[4. Inconsistent Term Usage]
- "Kost" vs "kost": Glosarium define "Kost" (capitalize sebagai proper noun), tapi:
  → "kost" (lowercase) muncul 23 kali di ARCHITECTURE.md §4, §5, §8
  → "Kost" (capitalize) muncul 45 kali di PRD.md & ARCHITECTURE.md §7
  → Action: Standardize ke "Kost" (capitalize) untuk konsistensi — ini domain entity, bukan common noun.

- "rental" vs "Rental": Glosarium define "Rental" (capitalize), tapi:
  → "rental" (lowercase) muncul 12 kali di TODO.md
  → Action: Update TODO.md untuk pakai "Rental" konsisten dengan glosarium.

─────────────────────────────────────────────────────────
Action Required (prioritized):
1. [HIGH] Fix broken references FR-047, COMP-010, ADR-025 (kemungkinan typo)
2. [HIGH] Fix duplicate IDs FR-089, TASK-012 (renumber atau remove duplicate)
3. [MEDIUM] Add 3 missing terms to PRD.md §6 Glossary (Room Type, Rental Lifecycle, QRIS)
4. [LOW] Standardize capitalization "Kost" & "Rental" (bisa dilakukan setelah perbaikan high/medium)

Re-run doc-consistency-check after fixing high/medium priority issues.
```

## Improvement Notes (vs Versi Sebelumnya yang Hilang)

- Tambah **Langkah 6 (Deteksi Duplikasi Definisi ID)** — menangkap copy-paste error atau merge conflict yang tidak terdeteksi
- Tambah **Langkah 7 (Deteksi Inkonsistensi Istilah)** — memastikan term usage konsisten dengan glosarium (capitalization, spelling)
- Tambah **threshold untuk report** (istilah harus muncul ≥3 kali, inkonsistensi >20%) — mengurangi false positive
- Tambah **contoh output lengkap dengan prioritas action** (HIGH/MEDIUM/LOW) — membantu user tahu mana yang harus diperbaiki dulu
- Klarifikasi **kondisi eskalasi untuk >50 error** — mencegah report terlalu panjang yang tidak actionable
