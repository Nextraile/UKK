---
name: doc-sync-checker
description: Verify documentation consistency after metadata changes (skill counts, versions, FR/COMP mappings). Detects when SKILL.md, ARCHITECTURE.md, or PRD.md changes require updates to dependent docs. Integrates with spec-sync for FR/COMP/TASK traceability validation. Trigger after editing documentation, before starting new build phase, when toast notification suggests sync needed.
license: MIT
compatibility: opencode
---

# doc-sync-checker — Documentation Sync Verification & Orchestration

## Tujuan

Memastikan perubahan metadata pada dokumen inti (SKILL.md, ARCHITECTURE.md, PRD.md) ter-propagate dengan benar ke dokumen terkait (AGENTS.md, MANUAL.md, TODO.md). Mengintegrasikan validasi spec-sync untuk memastikan traceability FR→COMP→TASK tetap konsisten setelah perubahan konseptual.

## Dasar/Rujukan

- **`WORKFLOW.md` §6 Change Management:** "Setiap perubahan meninggalkan jejak di Changelog"
- **`AGENTS.md` §Skills Available:** Cross-reference ke SKILL.md §1 (skill count harus match)
- **`MANUAL.md` §4.4 Skill Proyek:** Menyebutkan jumlah skill ter-install (harus match SKILL.md)
- **`ARCHITECTURE.md` §4:** Setiap COMP-xxx harus punya FR mapping (divalidasi spec-sync)
- **`PRD.md` §4:** Setiap FR-xxx harus punya COMP mapping (divalidasi spec-sync)
- **Plugin `doc-sync-watcher`:** Deteksi real-time perubahan file dokumentasi
- **Tool `sync-docs`:** Eksekusi sync verification dan apply

## Kapan Menggunakan Skill Ini

### Trigger Otomatis (dari plugin doc-sync-watcher):
- Toast notification muncul: "📄 SKILL.md changed. X file(s) may need sync."
- Agent sebaiknya proaktif call skill ini untuk check apa yang perlu di-sync

### Trigger Manual:
- Setelah user mengedit SKILL.md (tambah/hapus skill, bump version)
- Setelah user mengedit ARCHITECTURE.md (tambah COMP, bump version, tambah ADR)
- Setelah user mengedit PRD.md (tambah FR, bump version)
- Sebelum mulai build phase baru (sebagai gate, kombinasi dengan spec-sync)
- Setelah merge PR yang mengubah dokumentasi

### Jangan Gunakan Skill Ini Jika:
- Perubahan hanya pada TODO.md (task status) — TODO changes tidak trigger doc sync
- Perubahan hanya pada WORKFLOW.md/MANUAL.md (ini target files, bukan trigger)
- Perubahan hanya pada code files (bukan dokumentasi)

## Mekanisme Kerja

```
┌─────────────────────────────────────────────────────────────┐
│ 1. DETECT CHANGES                                           │
│    - Call sync-docs({ action: "check" })                    │
│    - Read pending_syncs from SQLite                         │
│    - Identify trigger file (e.g., SKILL.md)                 │
│    - Extract changes (e.g., skill count 16→17)              │
└────────────────┬────────────────────────────────────────────┘
                 ↓
┌─────────────────────────────────────────────────────────────┐
│ 2. VERIFY SYNC TARGETS                                      │
│    - Get list of affected files (e.g., AGENTS.md, MANUAL.md)│
│    - Show confidence level (0.0-1.0) per sync               │
│    - Group by confidence: High (≥0.9), Medium (0.7-0.89),   │
│      Low (<0.7)                                             │
└────────────────┬────────────────────────────────────────────┘
                 ↓
┌─────────────────────────────────────────────────────────────┐
│ 3. SPEC-SYNC INTEGRATION (if needed)                        │
│    - If ARCHITECTURE.md or PRD.md changed:                  │
│      • Check if new COMP/FR added                           │
│      • Call skill({ name: "spec-sync" })                    │
│      • Validate FR→COMP→TASK traceability                   │
│      • Report orphan IDs or broken references               │
│    - If spec-sync FAILED → STOP, report issues              │
└────────────────┬────────────────────────────────────────────┘
                 ↓
┌─────────────────────────────────────────────────────────────┐
│ 4. PRESENT SYNC PLAN                                        │
│    - Group syncs by confidence:                             │
│      • High (≥0.9): Safe to auto-apply                      │
│      • Medium (0.7-0.89): Review recommended                │
│      • Low (<0.7): Manual verification required             │
│    - Show line numbers, old/new content preview             │
└────────────────┬────────────────────────────────────────────┘
                 ↓
┌─────────────────────────────────────────────────────────────┐
│ 5. USER DECISION                                            │
│    Option A: Preview diffs first                            │
│        → sync-docs({ action: "preview" })                   │
│    Option B: Apply all high-confidence syncs                │
│        → sync-docs({ action: "apply" })                     │
│    Option C: Apply specific sync by ID                      │
│        → sync-docs({ action: "apply", syncId: "..." })      │
│    Option D: Decline (mark as reviewed, no action)          │
│        → (manual update to SQLite, or wait for Phase 2)     │
└─────────────────────────────────────────────────────────────┘
```

## Langkah-Langkah

### Langkah 1: Check Pending Syncs

Call sync-docs tool untuk get current state:

```
Call sync-docs({ action: "check" })
```

**Output interpretation:**
- Jika output = "No pending syncs" → Done, semua docs consistent.
- Jika ada pending syncs → Parse output untuk extract:
  - Trigger file (e.g., SKILL.md)
  - Changes detected (e.g., skill count 16→17)
  - Affected files (e.g., AGENTS.md, MANUAL.md)
  - Sync ID (e.g., sync-abc123-xyz789)
  - Confidence scores per target

### Langkah 2: Analyze Trigger & Determine spec-sync Need

Berdasarkan trigger file dan change type, tentukan apakah spec-sync validation diperlukan:

**spec-sync WAJIB dipanggil jika:**

| Trigger File | Change Type | Reason |
|--------------|-------------|---------|
| **ARCHITECTURE.md** | New COMP added | New COMP harus punya FR mapping + TASK breakdown |
| **ARCHITECTURE.md** | COMP deleted/deprecated | Affected TASKs in TODO.md might be orphaned |
| **PRD.md** | New FR added | New FR harus punya COMP mapping |
| **PRD.md** | FR modified/deleted | Affected COMPs/TASKs might reference outdated FR |

**spec-sync SKIP jika:**

| Trigger File | Change Type | Reason |
|--------------|-------------|---------|
| **SKILL.md** | Skill count change | Tidak affect FR/COMP/TASK traceability |
| **SKILL.md** | Version bump | Metadata-only change |
| **WORKFLOW.md** | Any change | Process doc, not requirement doc |

**Action:**

Jika spec-sync needed:
```
Call skill({ name: "spec-sync" })
```

**Parse spec-sync output:**
- Jika "✅ PASSED" → Traceability OK, lanjut ke Langkah 3
- Jika "❌ FAILED" → Ada orphan IDs atau broken references:
  - **STOP immediately**
  - Report spec-sync errors ke user
  - Jangan lanjut ke doc-sync apply
  - Minta user fix issues dulu
  - Offer to re-run after fixes

**Reasoning:** Doc-sync adalah surface-level consistency (skill counts, versions). Spec-sync adalah semantic consistency (FR→COMP→TASK logic). Semantic harus OK dulu sebelum surface-level di-sync.

### Langkah 3: Preview Diffs (Optional but Recommended)

Sebelum apply, tunjukkan ke user apa yang akan berubah:

```
Call sync-docs({ action: "preview" })
```

Parse output untuk extract:
- File yang akan diubah
- Line numbers
- Old vs new content (side-by-side diff)

Present ke user dalam format readable:
```markdown
## Proposed Changes

### AGENTS.md (line 116)
```diff
- 16 skills in `.opencode/skills/`:
+ 17 skills in `.opencode/skills/`:
```

### MANUAL.md (line 132)
```diff
- **16 skills ter-install**
+ **17 skills ter-install**
```

Looks good? I can apply these changes via sync-docs({ action: "apply" }).
```

### Langkah 4: Apply Syncs (After User Approval)

**CRITICAL:** Only apply if user explicitly approves. Look for keywords:
- Approve: "yes", "apply", "go ahead", "looks good", "ok", "do it"
- Decline: "no", "skip", "not now", "wait", "hold"

Jika user approve:

```
Call sync-docs({ action: "apply" })
```

Parse output untuk confirm success:
```
✅ AGENTS.md: Updated
✅ MANUAL.md: Updated
Total: 2 files modified
```

Kemudian inform user:
```
Done! 2 files synced successfully. Changes are ready for commit.

You can verify with: git diff
```

### Langkah 5: Handle Declines or Errors

**If user declines:**
- Don't apply syncs
- Inform: "Understood. Syncs remain pending. You can review anytime via sync-docs({ action: 'check' })"
- Note: In Phase 1, declined syncs stay in DB as 'pending'. Phase 2 will add decline tracking.

**If apply fails (❌ errors in output):**
- Report which files failed
- Show error messages
- Suggest manual intervention
- Don't mark sync as completed

**If apply partial success (some ✅, some ⚠️):**
- Report successful files
- Report skipped files with reasons
- Ask user if they want to manually fix skipped items

## Use Cases

### Use Case 1: New Skill Added

**Scenario:**
User creates new skill `api-doc-generator`, updates SKILL.md §1 table + version bump v0.6.0 → v0.7.0.

**Flow:**
1. User saves SKILL.md
2. doc-sync-watcher plugin detects change
3. Toast notification: "📄 SKILL.md changed. 2 file(s) may need sync."
4. Agent calls doc-sync-checker skill
5. Skill calls `sync-docs({ action: "check" })`
6. Output shows: "skill count 16→17, version v0.6.0→v0.7.0, affects AGENTS.md + MANUAL.md"
7. Skill determines: spec-sync NOT needed (metadata-only change)
8. Skill calls `sync-docs({ action: "preview" })`
9. Shows diffs (2 lines changed across 2 files)
10. Agent asks user: "These changes look safe. Apply?"
11. User: "yes"
12. Skill calls `sync-docs({ action: "apply" })`
13. Output: "✅ AGENTS.md: Updated, ✅ MANUAL.md: Updated"
14. Done ✅

**Expected outcome:**
- AGENTS.md line 116: "17 skills in `.opencode/skills/`:"
- MANUAL.md line 132: "17 skills ter-install"
- Sync marked as applied in SQLite
- User can commit changes

### Use Case 2: New COMP Added (with spec-sync validation)

**Scenario:**
User adds COMP-010 to ARCHITECTURE.md for new feature, bumps version v0.1.1 → v0.1.2.

**Flow:**
1. User saves ARCHITECTURE.md
2. doc-sync-watcher detects: new COMP added
3. Toast: "📄 ARCHITECTURE.md changed. Spec-sync recommended."
4. Agent calls doc-sync-checker
5. Skill detects: New COMP → **spec-sync REQUIRED**
6. Skill calls `spec-sync` first
7. spec-sync output: "❌ FAILED - COMP-010 has no TASK in TODO.md (orphan COMP)"
8. Skill reports: "Cannot proceed with doc-sync. spec-sync validation failed. Issues:\n- COMP-010 has no TASK in TODO.md\n\nPlease fix these issues first."
9. User creates TASK-xxx for COMP-010 in TODO.md
10. User asks agent to re-check
11. Agent re-runs doc-sync-checker
12. This time spec-sync passes ✅
13. Skill proceeds with doc-sync (if any metadata sync needed)

**Expected outcome:**
- spec-sync validates traceability BEFORE doc-sync runs
- User forced to maintain semantic consistency
- Doc-sync only runs after semantic validation passes

### Use Case 3: Version Bump Only (No Semantic Changes)

**Scenario:**
User edits ARCHITECTURE.md: Only bumps version v0.1.1 → v0.1.2 in changelog (minor doc clarification, no COMP/ADR changes).

**Flow:**
1. User saves ARCHITECTURE.md
2. doc-sync-watcher parses metadata
3. Detects: version change, but no COMP changes, no ADR changes
4. Determines: No sync targets (version bump alone doesn't require propagation)
5. No toast shown (nothing to sync)
6. Done ✅ (silent success)

**Expected outcome:**
- Smart detection: Not all file edits trigger syncs
- Reduces notification noise for trivial changes

### Use Case 4: Manual Verification Before Build Phase

**Scenario:**
User about to start build phase (TASK-001). Wants to ensure all docs consistent.

**Flow:**
1. User: "Check if all documentation is consistent before I start coding"
2. Agent calls doc-sync-checker skill
3. Skill calls `sync-docs({ action: "check" })`
4. Output: "No pending syncs. All docs consistent."
5. Skill calls `spec-sync` (as additional verification)
6. spec-sync output: "✅ PASSED - All FR→COMP→TASK mappings valid"
7. Skill reports: "Documentation fully consistent. Safe to start build phase."

**Expected outcome:**
- doc-sync-checker acts as gate before build
- Combines surface (doc-sync) + semantic (spec-sync) checks
- User confidence++ before starting implementation

## Kondisi Berhenti / Eskalasi

### Kondisi Berhenti (Stop and Ask User):

1. **spec-sync validation failed**
   - Jika spec-sync melaporkan orphan IDs atau broken references
   - STOP immediately, jangan lanjut ke doc-sync apply
   - Reason: Semantic consistency harus OK dulu sebelum surface sync
   - Action: Report spec-sync errors, minta user fix, offer to re-run

2. **Sync confidence <0.7 untuk critical files**
   - Jika confidence <0.7 untuk sync ke AGENTS.md atau ARCHITECTURE.md
   - STOP and ask user untuk manual verification
   - Reason: Critical files, salah sync bisa break project understanding
   - Action: Show preview, ask "Low confidence sync. Review carefully?"

3. **Multiple conflicting syncs pending**
   - Jika >1 pending sync untuk file yang sama dengan different targets
   - Example: SKILL.md (skill count) AND ARCHITECTURE.md (COMP) both want to update AGENTS.md
   - STOP and ask which to apply first
   - Reason: Race condition, applying both might create conflict

4. **SQLite database missing or corrupted**
   - Jika `.opencode/doc-sync.db` tidak bisa dibuka atau schema invalid
   - STOP and report error
   - Action: Suggest re-initialization or manual inspection

### Kondisi Eskalasi (Continue but Warn):

1. **Sync target file not found**
   - Jika target file tidak exist (e.g., MANUAL.md deleted)
   - CONTINUE tapi skip that target, report warning
   - Action: "Warning: MANUAL.md not found. Skipping. If unexpected, restore file."

2. **Sync target file modified since detection**
   - Jika file hash changed between detection and apply
   - CONTINUE but re-compute diff, show warning
   - Action: "Warning: AGENTS.md modified since sync detected. Re-computing diff..."

3. **High number of pending syncs (>5)**
   - Jika >5 pending syncs
   - CONTINUE but suggest batch review
   - Action: "Found 7 pending syncs. Review via sync-docs({ action: 'preview' }) recommended."

## Contoh Output

### Output 1: Sync Needed (High Confidence)

```markdown
## Documentation Sync Check

Pending syncs: 2 (from SKILL.md change 5 minutes ago)

### Changes Detected:
- Skill count: 16 → 17
- New skill added: `api-doc-generator`
- Version: v0.6.0 → v0.7.0

### Required Updates:
✅ **AGENTS.md** (line 116, confidence: 95%)
   - "16 skills" → "17 skills"

✅ **MANUAL.md** (line 132, confidence: 90%)
   - "16 skills ter-install" → "17 skills ter-install"

### Recommendation:
All syncs have high confidence (≥90%). Safe to apply.

**Next steps:**
1. Preview: sync-docs({ action: "preview" })
2. Apply: sync-docs({ action: "apply" })

What would you like to do?
```

### Output 2: Sync Needed + spec-sync Required

```markdown
## Documentation Sync Check

Pending syncs: 1 (from ARCHITECTURE.md change 2 minutes ago)

### Changes Detected:
- New component: COMP-010 (Payment Gateway Integration)
- Version: v0.1.1 → v0.1.2

### Traceability Validation Required
⚠️ New COMP detected → Running spec-sync...

Calling spec-sync...

❌ **spec-sync FAILED**

Issues:
- COMP-010 has no TASK in TODO.md (orphan COMP)
- COMP-010 references FR-150, FR-151 not defined in PRD.md

**Cannot proceed with doc-sync until spec-sync passes.**

**Action Required:**
1. Add TASK entries for COMP-010 in TODO.md
2. Verify FR-150, FR-151 exist in PRD.md (or fix references)
3. Re-run doc-sync-checker

Would you like help creating TASK breakdown for COMP-010?
```

### Output 3: All Docs Consistent

```markdown
## Documentation Sync Check

✅ **No pending syncs**

All documentation is consistent:
- SKILL.md ↔ AGENTS.md, MANUAL.md (skill counts match: 17)
- Last sync: 2 hours ago (sync-abc123)

**Status:** Ready for build phase.

Optional: Run spec-sync for FR→COMP→TASK traceability validation.
```

## Manual (Panduan Penggunaan Skill)

### Untuk Agent:

**Kapan memanggil skill ini:**
- Setiap kali melihat toast notification tentang doc sync
- Sebelum memulai build phase (sebagai gate check)
- Setelah user mengedit SKILL.md, ARCHITECTURE.md, atau PRD.md
- Jika user explicitly ask "check docs" atau "are docs consistent"

**Cara memanggil:**
```
skill({ name: "doc-sync-checker" })
```

**Interpret output:**
- "No pending syncs" → All good, proceed
- "X syncs needed" → Follow langkah-langkah di atas
- "spec-sync FAILED" → STOP, report issues, don't apply doc-sync

**Best practices:**
- Always call preview before apply (unless user says "skip preview")
- Always integrate spec-sync for ARCHITECTURE.md/PRD.md changes involving COMPs/FRs
- Don't auto-apply syncs dengan confidence <0.7 tanpa user review
- Report sync results clearly (files changed, lines modified)

### Untuk User:

**Kapan menjalankan skill ini:**

1. **After editing documentation:**
   - Added/removed skill in SKILL.md
   - Added/modified COMP in ARCHITECTURE.md
   - Added/modified FR in PRD.md
   - Bumped version in any core doc

2. **Before starting build phase:**
   - Ensures all documentation aligned
   - Catches inconsistencies early

3. **After merging documentation PRs:**
   - Multiple doc changes might have cascading sync needs

4. **When toast notification appears:**
   - System detected potential sync need

**Typical workflow:**
```
You: [edit SKILL.md, add new skill]
System: 📄 SKILL.md changed. 2 file(s) may need sync.
You: "Check if docs need sync"
Agent: [calls doc-sync-checker]
Agent: "Found 2 syncs: AGENTS.md + MANUAL.md. Preview diffs?"
You: "Yes"
Agent: [shows diffs]
You: "Looks good, apply"
Agent: "Done! 2 files synced."
```

## Integration dengan Skill Lain

### spec-sync (Traceability Validation)

doc-sync-checker **WAJIB** memanggil spec-sync ketika:
- ARCHITECTURE.md: New/modified COMP
- PRD.md: New/modified FR
- TODO.md: New TASK added (though TODO changes don't trigger doc-sync)

**Flow:**
```
doc-sync-checker detects ARCHITECTURE.md change (new COMP)
    ↓
Calls spec-sync FIRST
    ↓
If spec-sync PASSED → Proceed with doc-sync
If spec-sync FAILED → STOP, report errors, don't sync
```

**Reasoning:**
spec-sync validates semantic consistency (FR→COMP→TASK logic).
doc-sync validates surface consistency (skill counts, versions).
Semantic must be valid before surface sync.

### task-breakdown (Future Integration)

If spec-sync reports orphan COMP (no TASK), doc-sync-checker bisa offer:
```
"COMP-010 has no TASK. Would you like me to run task-breakdown to create them?"
```

This integration is Phase 2 scope.

## Changelog Skill

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0 | 2026-08-15 | Initial version: Real-time sync detection, check/preview/apply modes, spec-sync integration, SQLite storage, toast notifications, confidence scoring. |
