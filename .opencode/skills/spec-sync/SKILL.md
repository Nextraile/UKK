---
name: spec-sync
description: Memverifikasi konsistensi ID antar dokumen proyek (PRD.md, ARCHITECTURE.md, TODO.md). Gunakan setelah PRD.md atau ARCHITECTURE.md direvisi, atau sebelum memulai fase Build baru. Memastikan setiap FR-xxx punya COMP-xxx di ARCHITECTURE.md dan TASK-xxx di TODO.md. Melaporkan ID yatim (orphan) atau referensi rusak. Trigger: "spec sync", "consistency check antar dokumen", "orphan ID", "traceability matrix", setelah revisi PRD/ARCHITECTURE.
license: MIT
compatibility: opencode
---

# spec-sync — Verifikasi Konsistensi Traceability Antar Dokumen

## Tujuan

Memastikan setiap requirement (`FR-xxx`, `NFR-xxx`) di `PRD.md` terlacak hingga ke komponen teknis (`COMP-xxx`) di `ARCHITECTURE.md` dan task implementasi (`TASK-xxx`) di `TODO.md`, serta mendeteksi ID yatim (orphan) atau referensi rusak yang bisa menyebabkan scope gap atau mismatch antara desain dan implementasi.

## Dasar/Rujukan

- **`WORKFLOW.md` §2 Fase 2 & 3:** Design dan Planning phase memerlukan traceability eksplisit FR→COMP→TASK
- **`AGENTS.md` §Definition of Done:** Sebuah TASK harus merujuk FR/NFR dan COMP yang relevan
- **`ARCHITECTURE.md` §4:** Setiap COMP-xxx wajib menyebutkan FR mana saja yang dipenuhinya
- **`SKILL.md` §2 spec-sync:** Skill direkomendasikan sebagai gate sebelum fase Build

## Langkah-Langkah

### 1. Ekstrak Semua ID dari Dokumen

**Dari `PRD.md`:**
- Daftar semua `FR-xxx` yang didefinisikan (bukan hanya dirujuk) di §4
- Daftar semua `NFR-xxx` yang didefinisikan di §5
- Daftar semua `US-xxx` yang didefinisikan di §9 (jika ada)

**Dari `ARCHITECTURE.md`:**
- Daftar semua `COMP-xxx` yang didefinisikan di §4
- Daftar semua `ADR-xxx` yang didefinisikan di §7
- Daftar semua `DM-xxx` yang didefinisikan di §5

**Dari `TODO.md`:**
- Daftar semua `TASK-xxx` yang didefinisikan di daftar task (semua section)

### 2. Bangun Peta Referensi (Reference Map)

Untuk setiap dokumen, ekstrak referensi ID ke dokumen lain:

**`PRD.md` → luar:**
- Tidak ada (PRD tidak boleh merujuk COMP/TASK, hanya requirement definition)

**`ARCHITECTURE.md` → `PRD.md`:**
- Di setiap `COMP-xxx`, ekstrak semua `FR-xxx`/`NFR-xxx` yang disebut di bagian "Memenuhi requirement"
- Di setiap `ADR-xxx`, ekstrak semua `FR-xxx`/`NFR-xxx`/`US-xxx` yang disebut sebagai konteks keputusan

**`TODO.md` → `PRD.md` & `ARCHITECTURE.md`:**
- Di setiap `TASK-xxx`, ekstrak semua `FR-xxx`/`NFR-xxx`/`COMP-xxx` yang disebut di deskripsi atau acceptance criteria

### 3. Validasi Traceability Forward (PRD → ARCHITECTURE → TODO)

**Check 1: Setiap FR-xxx di PRD.md harus muncul di minimal satu COMP-xxx di ARCHITECTURE.md**

```
FOR EACH FR-xxx IN PRD.md §4:
  IF FR-xxx NOT REFERENCED IN any COMP-xxx:
    REPORT: "Orphan FR: FR-xxx tidak dipetakan ke komponen mana pun di ARCHITECTURE.md"
```

**Check 2: Setiap COMP-xxx di ARCHITECTURE.md harus memiliki minimal satu TASK-xxx di TODO.md**

```
FOR EACH COMP-xxx IN ARCHITECTURE.md §4:
  IF COMP-xxx NOT REFERENCED IN any TASK-xxx IN TODO.md:
    REPORT: "Orphan COMP: COMP-xxx tidak punya task implementasi di TODO.md"
```

**Check 3: Setiap NFR-xxx di PRD.md harus disebutkan di ARCHITECTURE.md (§7 ADR, §8 Security, §9 Deployment, atau §10 Skalabilitas)**

```
FOR EACH NFR-xxx IN PRD.md §5:
  IF NFR-xxx NOT REFERENCED IN ARCHITECTURE.md:
    REPORT: "Orphan NFR: NFR-xxx tidak dibahas di ARCHITECTURE.md"
```

### 4. Validasi Traceability Backward (TODO → ARCHITECTURE → PRD)

**Check 4: Setiap TASK-xxx di TODO.md harus merujuk minimal satu FR-xxx atau COMP-xxx**

```
FOR EACH TASK-xxx IN TODO.md:
  IF (NO FR-xxx REFERENCED) AND (NO COMP-xxx REFERENCED):
    REPORT: "Ungrounded TASK: TASK-xxx tidak merujuk FR atau COMP mana pun"
```

**Check 5: Setiap referensi FR-xxx/NFR-xxx/COMP-xxx di TODO.md harus valid (ID tersebut terdefinisi di dokumen asalnya)**

```
FOR EACH ID-REFERENCE IN TODO.md:
  IF ID is FR-xxx AND FR-xxx NOT DEFINED IN PRD.md §4:
    REPORT: "Broken reference: FR-xxx disebutkan di TODO.md tapi tidak terdefinisi di PRD.md"
  IF ID is COMP-xxx AND COMP-xxx NOT DEFINED IN ARCHITECTURE.md §4:
    REPORT: "Broken reference: COMP-xxx disebutkan di TODO.md tapi tidak terdefinisi di ARCHITECTURE.md"
```

### 5. Generate Traceability Matrix (Opsional, untuk visibility)

Jika tidak ada error dari Check 1-5, buat tabel ringkasan:

| FR-xxx | COMP-xxx (dari ARCHITECTURE) | TASK-xxx (dari TODO) | Status |
|---|---|---|---|
| FR-001 | COMP-001, COMP-002 | TASK-003, TASK-007, TASK-011 | ✅ Fully traced |
| FR-002 | COMP-003 | (none) | ⚠️ No implementation task |
| ... | ... | ... | ... |

Tabel ini membantu stakeholder melihat sekilas mana requirement yang sudah/belum terimplementasi.

### 6. Laporkan Hasil

**Jika semua check lolos:**
```
✅ Spec Sync PASSED
- X FR-xxx fully traced to COMP and TASK
- Y COMP-xxx fully traced to TASK
- Z NFR-xxx addressed in ARCHITECTURE.md
- 0 broken references
```

**Jika ada error:**
```
❌ Spec Sync FAILED
- N orphan FR (list them)
- M orphan COMP (list them)
- P broken references (list them)
- Q ungrounded TASK (list them)

Action required: Fix traceability gaps before proceeding to Build phase.
```

## Kondisi Berhenti / Eskalasi

- **`TODO.md` belum ada atau masih kosong** → Berhenti, sampaikan ke pengguna bahwa spec-sync hanya bisa dijalankan setelah task breakdown (fase Planning) selesai. Tawarkan untuk menjalankan skill `task-breakdown` terlebih dahulu.
- **`ARCHITECTURE.md` atau `PRD.md` dalam status Draft** → Berhenti, spec-sync hanya bermakna untuk dokumen yang statusnya minimal "Ready for Review" atau "Approved". Jika status masih Draft, konfirmasi dulu ke pengguna apakah mereka ingin lanjut validasi (hasil bisa misleading karena dokumen belum final).
- **Ditemukan >20 orphan ID** → Ini tanda dokumen sangat out-of-sync (kemungkinan besar PRD/ARCHITECTURE baru direvisi besar-besaran tapi TODO.md belum diupdate). Laporkan jumlah total orphan, jangan list semua (terlalu panjang), dan rekomendasikan pengguna untuk:
  1. Jalankan `task-breakdown` ulang untuk semua COMP yang orphan, atau
  2. Jika revisi PRD/ARCHITECTURE masih ongoing, tunda spec-sync hingga dokumen stabil.
- **Format dokumen tidak standar (ID tidak pakai prefix yang benar, atau section numbering berubah)** → Berhenti, laporkan bagian mana yang tidak sesuai ekspektasi format, minta pengguna perbaiki dulu sebelum spec-sync bisa berjalan.

## Contoh Output

### Contoh 1: Spec Sync Passed

```
✅ Spec Sync Validation PASSED

Traceability Summary:
- 129 FR-xxx → all mapped to COMP (9 COMP total)
- 9 COMP-xxx → all have TASK in TODO.md (47 TASK total)
- 33 NFR-xxx → all addressed in ARCHITECTURE.md §7-§10
- 0 broken references
- 0 orphan IDs

Next step: Ready to proceed to Build phase (WORKFLOW.md §2 Fase 4).
```

### Contoh 2: Spec Sync Failed (dengan detail error)

```
❌ Spec Sync Validation FAILED

Issues found:

[Orphan FR — tidak dipetakan ke COMP]
- FR-047 (PRD.md:234) → tidak disebutkan di COMP mana pun di ARCHITECTURE.md
- FR-089 (PRD.md:456) → tidak disebutkan di COMP mana pun di ARCHITECTURE.md

[Orphan COMP — tidak punya TASK]
- COMP-005 (ARCHITECTURE.md:678) → tidak ada TASK-xxx yang merujuknya di TODO.md

[Broken reference — ID tidak terdefinisi]
- TODO.md TASK-023 merujuk "FR-200" yang tidak ada di PRD.md §4
- TODO.md TASK-031 merujuk "COMP-010" yang tidak ada di ARCHITECTURE.md §4

[Ungrounded TASK — tidak merujuk FR/COMP]
- TASK-015 (TODO.md:123) → tidak menyebut FR atau COMP mana pun

Action required:
1. Update ARCHITECTURE.md untuk peta FR-047 dan FR-089 ke COMP yang relevan
2. Jalankan task-breakdown untuk COMP-005
3. Perbaiki typo/broken reference di TASK-023 dan TASK-031
4. Tambahkan grounding (rujukan FR/COMP) ke TASK-015

Setelah perbaikan, jalankan ulang spec-sync sebelum lanjut ke Build.
```

## Improvement Notes (vs Versi Sebelumnya yang Hilang)

- Tambah validasi NFR traceability (Check 3) — versi sebelumnya mungkin hanya fokus ke FR
- Tambah kondisi eskalasi untuk >20 orphan (agar output tidak membanjiri user)
- Tambah contoh output konkret agar user tahu ekspektasi hasil validasi
- Klarifikasi bahwa skill ini baru bisa dijalankan setelah TODO.md terisi (dependency ke task-breakdown)
