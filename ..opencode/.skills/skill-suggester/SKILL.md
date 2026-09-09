---
name: skill-suggester
description: Review detected patterns from user actions and suggest creating new skills based on repetitive behavior (≥3 occurrences). Presents evidence with session IDs, timestamps, and confidence scores. Guides through skill-architect governance workflow. Trigger: when toast shows "Pattern detected", user asks "what skills should I create", "review patterns", "skill suggestions", or after session.idle pattern analysis.
license: MIT
compatibility: opencode
---

# skill-suggester — Pattern-Based Skill Suggestion

## Tujuan

Mengidentifikasi pola perilaku berulang dari aksi pengguna (tool calls, prompt patterns, file edits) yang terdeteksi oleh plugin `pattern-tracker`, dan menyarankan pembuatan skill baru untuk mengotomatisasi atau membakukan pola tersebut. Saran disertai bukti konkret (session ID, timestamp, confidence score) dan mengikuti governance `skill-architect`.

## Dasar/Rujukan

- **`SKILL.md` §0.1 Governance:** Skill baru hanya boleh dibuat dengan evidence-based trigger (≥3 occurrences)
- **`skill-architect` skill:** Wajib dijalankan sebelum membuat skill baru apa pun
- **Plugin `pattern-tracker`:** Mendeteksi pola berulang dan menyimpannya di SQLite
- **`WORKFLOW.md` §6 Change Management:** Setiap perubahan meninggalkan jejak
- **`AGENTS.md` §Skills Available:** Daftar skill yang sudah ada (untuk cek duplikasi)

## Mekanisme Kerja

```
┌─────────────────────────────────────────────────────────────┐
│ 1. PATTERN DETECTION (by pattern-tracker plugin)            │
│    - tool.execute.after → Log tool calls                    │
│    - message.updated → Capture user prompts                 │
│    - session.idle → Analyze for repetition (≥3x)            │
│    - Store in SQLite: patterns table                        │
│    - Toast: "💡 Pattern detected: [description]"            │
└────────────────┬────────────────────────────────────────────┘
                 ↓
┌─────────────────────────────────────────────────────────────┐
│ 2. USER INVOKES skill-suggester                              │
│    - User sees toast or manually calls skill                │
│    - Agent calls skill({ name: "skill-suggester" })        │
└────────────────┬────────────────────────────────────────────┘
                 ↓
┌─────────────────────────────────────────────────────────────┐
│ 3. READ PENDING PATTERNS                                     │
│    - Read from SQLite: patterns WHERE status='pending'      │
│    - Filter out declined patterns (30-day cooldown)         │
│    - Sort by confidence (highest first)                     │
└────────────────┬────────────────────────────────────────────┘
                 ↓
┌─────────────────────────────────────────────────────────────┐
│ 4. PRESENT PATTERNS WITH EVIDENCE                            │
│    For each pattern:                                         │
│    - Description (what was detected)                        │
│    - Occurrences (how many times)                           │
│    - Confidence score (0.0-1.0)                             │
│    - Evidence: session IDs + timestamps                     │
│    - Grounding: which docs/rules justify this               │
│    - Suggested skill draft (frontmatter + skeleton)         │
└────────────────┬────────────────────────────────────────────┘
                 ↓
┌─────────────────────────────────────────────────────────────┐
│ 5. USER DECISION                                             │
│    Option A: Create skill → Run skill-architect workflow    │
│    Option B: Decline → 30-day cooldown (won't re-suggest)   │
│    Option C: Remind later → Skip, keep pending              │
└─────────────────────────────────────────────────────────────┘
```

## Langkah-Langkah

### Langkah 1: Read Pending Patterns

Baca pola yang terdeteksi dari database:

```javascript
import { getDatabase } from '../tools/lib/db-schema.mjs'
import { getPendingPatterns, formatPattern } from '../tools/lib/pattern-analyzer.mjs'

const db = getDatabase(worktree)
const patterns = getPendingPatterns(db)
```

Jika tidak ada pending patterns:
```
✅ No pending skill suggestions.

All detected patterns have been either:
- Already addressed (skill created)
- Declined (in 30-day cooldown)
- Below confidence threshold (0.7)

You can review declined patterns with: "show declined patterns"
```

Jika ada pending patterns → lanjut ke Langkah 2.

### Langkah 2: Present Each Pattern

Untuk setiap pattern (dari confidence tertinggi):

```markdown
## Pattern: pattern-doc-sync

**Description:** Repeated tool sequence: edit → write → edit (documentation files)
**Occurrences:** 4
**Confidence:** 85%
**Pattern ID:** pattern-abc123-xyz

### Evidence (last 5 occurrences):

| Session | Timestamp |
|---------|-----------|
| sess-001... | 2026-08-13 14:30 |
| sess-002... | 2026-08-15 09:48 |
| sess-003... | 2026-08-15 10:55 |
| sess-004... | 2026-08-15 14:20 |

### Grounding:
- WORKFLOW.md §6 Change Management
- Cross-document consistency required after metadata changes

### Suggested Skill Draft:

```yaml
---
name: doc-sync-automation
description: Auto-suggested from 4 detected occurrences. Repeated tool sequence: edit → write → edit (documentation files). Trigger: detected pattern from user actions.
license: MIT
compatibility: opencode
---
```

**Would you like to:**
A. Create this skill (guided via skill-architect)
B. Decline (won't suggest again for 30 days)
C. Remind me later
```

### Langkah 3: Handle User Decision

#### Option A: Create Skill

Jika user memilih create ("yes", "create", "a"):

1. **Validate via skill-architect:**
   ```
   Call skill({ name: "skill-architect" })
   ```

2. **Pre-fill evidence:**
   - Langkah 1 (Evidence): Pattern already provides ≥3 occurrences
   - Langkah 2 (Duplication): Check against existing skills
   - Langkah 4 (Grounding): Pattern already provides doc references

3. **Generate skill file:**
   ```bash
   mkdir -p .opencode/skills/<suggested-name>
   # Write SKILL.md with draft content
   ```

4. **Mark pattern as created:**
   ```javascript
   import { markPatternCreated } from '../tools/lib/pattern-analyzer.mjs'
   markPatternCreated(db, pattern.id)
   ```

5. **Update SKILL.md root registry:**
   - Add new skill to §1 table
   - Bump version
   - Add changelog entry

#### Option B: Decline

Jika user memilih decline ("no", "decline", "b"):

1. **Record decline:**
   ```javascript
   import { declinePattern } from '../tools/lib/pattern-analyzer.mjs'
   const result = declinePattern(db, pattern.id, reason)
   ```

2. **Confirm to user:**
   ```
   Understood. Pattern declined. This pattern won't be suggested again until ${result.cooldownUntil}.

   You can review declined patterns anytime with: "show declined patterns"
   ```

#### Option C: Remind Later

Jika user memilih remind ("later", "remind", "c"):

1. **Skip without action:**
   - Pattern stays in `pending` status
   - Will be shown again next time user invokes skill-suggester
   - No cooldown applied

2. **Confirm to user:**
   ```
   OK. Pattern remains pending. I'll remind you next time you run skill-suggester.
   ```

### Langkah 4: Review Declined Patterns (Optional)

Jika user asks "show declined patterns":

```javascript
import { getDeclinedPatterns } from '../tools/lib/pattern-analyzer.mjs'
const declined = getDeclinedPatterns(db)
```

Present:
```markdown
## Declined Patterns (3)

### 1. pattern-db-migration-rollback
- **Declined:** 2026-08-10
- **Reason:** "Not needed for this project"
- **Cooldown until:** 2026-09-10
- **Can suggest again:** No (in cooldown)

### 2. pattern-api-doc-gen
- **Declined:** 2026-08-05
- **Reason:** "Already have docs-lookup skill"
- **Cooldown until:** 2026-09-05
- **Can suggest again:** Yes (cooldown expired)

Would you like to:
A. Re-enable a declined pattern (move back to pending)
B. Delete a declined pattern permanently
C. Go back
```

## Use Cases

### Use Case 1: Doc Sync Pattern Detected

**Scenario:**
User manually syncs documentation 3 times (AGENTS.md, MANUAL.md, WORKFLOW.md) across 2 sessions after SKILL.md changes.

**Flow:**
1. Session 1: User edits SKILL.md, manually updates AGENTS.md + MANUAL.md
2. Session 2: User edits SKILL.md, manually updates AGENTS.md + WORKFLOW.md
3. Session 3: User edits SKILL.md, manually updates AGENTS.md + MANUAL.md
4. pattern-tracker detects: "Repeated doc sync pattern (3x)"
5. Toast: "💡 Pattern detected: Repeated doc sync. Run skill-suggester."
6. User invokes skill-suggester
7. Skill presents pattern with 3 evidence points
8. User chooses "Create skill"
9. Skill-architect workflow runs with pre-filled evidence
10. New skill `doc-sync-automation` created ✅

### Use Case 2: Test Writing Pattern

**Scenario:**
User calls test-writer skill 4 times for migration-related tests.

**Flow:**
1. Session 1: User calls test-writer for TASK-001 (User migration)
2. Session 2: User calls test-writer for TASK-010 (Kost migration)
3. Session 3: User calls test-writer for TASK-015 (Room migration)
4. pattern-tracker detects: "Skill test-writer invoked 4 times for migration tests"
5. User invokes skill-suggester
6. Pattern shown: "Migration test pattern (4x, confidence 90%)"
7. User chooses "Create skill"
8. New skill `migration-test-helper` created ✅

### Use Case 3: Declined Pattern Cooldown

**Scenario:**
Pattern detected for "API documentation generation" but user already has `docs-lookup` skill.

**Flow:**
1. Pattern detected: "API doc lookup pattern (3x)"
2. User invokes skill-suggester
3. Pattern shown with evidence
4. User chooses "Decline" with reason: "Already have docs-lookup skill"
5. Pattern marked as declined (30-day cooldown)
6. Same pattern occurs 2 more times (total 5x)
7. Pattern NOT re-suggested (in cooldown)
8. After 30 days: cooldown expires, can_suggest_again = true
9. If pattern occurs again: Re-suggested

## Kondisi Berhenti / Eskalasi

### Kondisi Berhenti (Stop and Ask User):

1. **No pending patterns**
   - Jika tidak ada pattern yang terdeteksi
   - Inform user: "No patterns detected. Keep working — patterns emerge after ≥3 repetitions."
   - Done

2. **All patterns in cooldown**
   - Jika semua pattern sudah declined dan masih dalam cooldown
   - Inform user: "All detected patterns are in cooldown. Review declined patterns with 'show declined'."
   - Done

3. **Pattern confidence <0.7**
   - Jika confidence score di bawah threshold
   - Skip pattern, don't suggest
   - Reason: Low confidence patterns might be noise

4. **Duplicate skill already exists**
   - Jika suggested skill name already exists di SKILL.md §1
   - Skip creation, suggest revising existing skill instead
   - Action: "Skill `doc-sync-automation` already exists. Consider revising it instead."

### Kondisi Eskalasi (Continue but Warn):

1. **Pattern from single session only**
   - Jika pattern hanya dari 1 session (tidak cross-session)
   - CONTINUE but note: "Note: Pattern detected in single session. May be project-specific task, not general pattern."

2. **High number of pending patterns (>5)**
   - Jika >5 pending patterns
   - CONTINUE but suggest batch review
   - Action: "Found 7 pending patterns. Consider reviewing the top 3 by confidence first."

## Contoh Output

### Output 1: Patterns Found

```markdown
## Skill Suggestions

Found 2 pending pattern(s):

---

### 1. Pattern: pattern-doc-sync-manual (Confidence: 88%)

**Description:** Repeated manual documentation sync after SKILL.md changes
**Occurrences:** 4
**Last detected:** 2026-08-15 14:20

**Evidence:**

| Session | Timestamp |
|---------|-----------|
| sess-abc... | 2026-08-13 14:30 |
| sess-def... | 2026-08-15 09:48 |
| sess-ghi... | 2026-08-15 10:55 |
| sess-jkl... | 2026-08-15 14:20 |

**Grounding:**
- WORKFLOW.md §6 Change Management
- Cross-document consistency required

**Suggested skill:** `doc-sync-automation`

---

### 2. Pattern: pattern-test-writer-migration (Confidence: 80%)

**Description:** Skill test-writer invoked 4 times for migration tests
**Occurrences:** 4
**Last detected:** 2026-08-15 16:00

**Evidence:**

| Session | Timestamp |
|---------|-----------|
| sess-abc... | 2026-08-14 10:00 |
| sess-def... | 2026-08-14 15:30 |
| sess-ghi... | 2026-08-15 09:00 |
| sess-jkl... | 2026-08-15 16:00 |

**Suggested skill:** `migration-test-helper`

---

**Options for Pattern 1:**
A. Create skill (guided via skill-architect)
B. Decline (30-day cooldown)
C. Remind later
D. Next pattern

What would you like to do?
```

### Output 2: No Patterns

```markdown
## Skill Suggestions

✅ No pending skill suggestions.

The pattern-tracker plugin hasn't detected any repetitive patterns (≥3 occurrences) that would justify creating a new skill.

**How patterns get detected:**
1. Plugin tracks tool calls, prompts, and file edits
2. At session end, it analyzes for repetition
3. Patterns with ≥3 occurrences and ≥0.7 confidence are stored
4. This skill presents them for your review

**Tips for triggering pattern detection:**
- Perform similar workflows across multiple sessions
- Use the same skill repeatedly for similar tasks
- Edit the same set of files repeatedly

Check declined patterns with: "show declined patterns"
```

## Manual (Panduan Penggunaan Skill)

### Untuk Agent:

**Kapan memanggil skill ini:**
- Setelah melihat toast "💡 Pattern detected: ..."
- User asks "what skills should I create", "review patterns", "skill suggestions"
- User asks about repetitive tasks they've been doing
- At session start if patterns were detected in previous session

**Cara memanggil:**
```
skill({ name: "skill-suggester" })
```

**Best practices:**
- Always present evidence (session IDs, timestamps) — don't just say "pattern detected"
- Show grounding (which docs justify this skill)
- Let user decide: Create / Decline / Remind
- If Create: Pre-fill skill-architect evidence from pattern data
- If Decline: Record reason and set 30-day cooldown
- Never auto-create skills without user approval

### Untuk User:

**Kapan menjalankan:**
- When toast notification appears about detected pattern
- When you feel like you're doing the same thing repeatedly
- Before starting a new build phase (check if patterns emerged)
- After completing a series of similar tasks

**Typical workflow:**
```
System: 💡 Pattern detected: Repeated doc sync (3x). Run skill-suggester.
You: "Review skill suggestions"
Agent: [calls skill-suggester]
Agent: "Found 1 pattern: doc-sync-manual (88% confidence). Create skill?"
You: "Yes, create it"
Agent: [guides through skill-architect with pre-filled evidence]
Agent: "Skill `doc-sync-automation` created ✅"
```

## Integration dengan Skill Lain

### skill-architect (Governance)

skill-suggester **WAJIB** memanggil skill-architect ketika user memilih "Create skill".

**Flow:**
```
skill-suggester presents pattern
    ↓
User: "Create skill"
    ↓
skill-suggester calls skill-architect
    ↓
skill-architect runs 7-step validation
    ↓
If passes → Create skill file
If fails → Report issues, don't create
```

**Pre-filled data from pattern:**
- Langkah 1 (Evidence): Pattern.occurrences + history
- Langkah 2 (Duplication): Check against SKILL.md §1
- Langkah 4 (Grounding): Pattern.grounding
- Langkah 5 (Frontmatter): Generated draft

### pattern-tracker (Plugin)

skill-suggester reads data yang dikumpulkan oleh pattern-tracker plugin:
- Table `patterns`: Pending patterns
- Table `pattern_history`: Evidence (session IDs, timestamps)
- Table `declined_patterns`: Cooldown tracking

### doc-sync-checker (Phase 1)

If pattern-tracker detects "manual doc sync" pattern, skill-suggester can suggest creating `doc-sync-automation` skill — which would essentially formalize what doc-sync-checker already does into a standalone skill.

## Changelog Skill

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0 | 2026-08-15 | Initial version: Pattern review, evidence presentation, skill draft generation, decline tracking with 30-day cooldown, skill-architect integration. |
