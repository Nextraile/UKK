# OpenCode Extensions for SewaKost

Custom plugins, tools, and skills for the SewaKost project.

## Runtime Requirement

**IMPORTANT:** This project requires **Bun runtime** (v1.3.14+). OpenCode 1.18.15 uses Bun as its JavaScript runtime.

### Why Bun?

- OpenCode 1.18.15 binary is built with Bun (despite documentation claiming Node.js)
- We use `bun:sqlite` (Bun's built-in SQLite module) instead of native Node.js modules
- No external dependencies needed - SQLite is built into Bun runtime

### Migration History

**2026-08-16:** Migrated from `better-sqlite3` to `bun:sqlite`
- **Reason:** better-sqlite3 (Node.js native module) caused NAPI crashes in Bun runtime
- **Solution:** Use Bun's native SQLite implementation
- **Result:** Zero crashes, faster performance, no native compilation needed

### Troubleshooting

If you see errors like:
```
panic: NAPI FATAL ERROR: Error::New napi_get_last_error_info
signal SIGILL (Illegal instruction)
```

**Root cause:** Code is trying to use Node.js native modules in Bun runtime.

**Status:** ✅ **FIXED** - We now use `bun:sqlite` which is fully compatible with Bun runtime.

## Project Structure

```
.opencode/
├── .plugins/           # Custom plugins
│   ├── doc-sync-watcher.ts    # Phase 1: Doc sync detection
│   └── pattern-tracker.ts     # Phase 2: Pattern detection & skill suggestions
├── tools/              # Custom tools
│   ├── sync-docs.ts           # sync-docs tool (check/preview/apply)
│   └── lib/                   # Shared libraries
│       ├── db-schema.mjs      # SQLite schema (8 tables)
│       ├── doc-parser.mjs     # Documentation parser
│       ├── sync-planner.mjs   # Sync planning logic
│       └── pattern-analyzer.mjs  # Pattern detection logic
├── skills/             # Custom skills
│   ├── doc-sync-checker/      # Skill for sync verification
│   ├── skill-suggester/       # Skill for pattern-based suggestions
│   └── ...                    # 18 total skills
├── package.json        # Dependencies
├── package-lock.json   # Lock file (Node.js)
└── doc-sync.db         # SQLite database (auto-created)
```

## Dependencies

- **bun:sqlite** (built-in) - SQLite database via Bun runtime
- **fast-diff** v1.3.0 - Text diffing
- **gray-matter** v4.0.3 - YAML frontmatter parsing

All dependencies installed via `npm install` in `.opencode/` directory.

**Note:** `bun:sqlite` is a built-in Bun module, not an npm package. No installation needed.

## Features

### Phase 1: Documentation Sync Detection
- Real-time file monitoring (SKILL.md, AGENTS.md, MANUAL.md, PRD.md, ARCHITECTURE.md, TODO.md)
- Semantic change detection (skill counts, versions, FR/COMP mappings)
- Confidence scoring for sync suggestions
- Integration with spec-sync skill

### Phase 2: Pattern Tracking & Skill Suggestions
- Session action logging (tool calls, prompts)
- Automatic pattern detection (≥3 occurrences)
- Skill draft generation with confidence scores
- 30-day decline cooldown
- Integration with skill-architect governance

## Database Schema

SQLite database: `.opencode/doc-sync.db`

**Phase 1 tables (4):**
- `file_hashes` - Track file content changes
- `pending_syncs` - Detected sync needs
- `sync_targets` - Files requiring updates
- `applied_syncs` - Historical sync records

**Phase 2 tables (4):**
- `session_actions` - User action logs
- `patterns` - Detected patterns
- `pattern_history` - Individual occurrences
- `declined_patterns` - User-declined suggestions with cooldown

## Custom Tools

### `sync-docs`
Check, preview, and apply documentation synchronization.

```bash
# Check for pending syncs
sync-docs check

# Preview specific sync
sync-docs preview --syncId <id>

# Apply all pending syncs
sync-docs apply

# Apply specific file only
sync-docs apply --files AGENTS.md
```

## Skills

18 skills available:
- **Development workflow:** skill-architect, adr-writer, doc-consistency-check, doc-sync-checker, docs-lookup, skill-suggester, spec-sync, task-breakdown, test-writer
- **Design:** banner-design, brand, design, design-system, slides, ui-styling, ui-ux-pro-max
- **UI craft:** impeccable (v4.1.1)
- **Skill discovery:** find-skills

See `SKILL.md` for full descriptions.

## Development

### Testing Libraries

```bash
# From .opencode/ directory
cd /home/nextraile/Workspaces/UKK/SewaKost/.opencode

# Test db-schema.mjs (use bun, not node)
bun tools/lib/db-schema.mjs

# Test doc-parser.mjs
bun tools/lib/doc-parser.mjs

# Test sync-planner.mjs
bun tools/lib/sync-planner.mjs

# Test pattern-analyzer.mjs
bun tools/lib/pattern-analyzer.mjs
```

### Adding New Dependencies

```bash
cd .opencode/
npm install <package-name>
# Creates/updates package-lock.json automatically
```

**IMPORTANT:** Avoid native Node.js modules when possible. Prefer pure JavaScript packages or Bun built-ins.

## Version History

- **v1.0.1** (2026-08-16) - **Runtime Migration: better-sqlite3 → bun:sqlite**
  - ✅ Fixed NAPI crash by migrating to Bun's native SQLite
  - Changed 2 lines in db-schema.mjs (import + pragma)
  - Removed better-sqlite3 dependency (39 packages uninstalled)
  - All 9 tables, indexes, and CRUD operations verified
  - Zero breaking changes to plugins/tools (API compatible)
  
- **v1.0.0** (2026-08-16) - Phase 1 + Phase 2 complete (3,254 lines)
  - Doc sync detection system
  - Pattern tracking & skill suggestions
  - 8-table SQLite schema
  - 18 skills registered

---

**Last updated:** 2026-08-16  
**Runtime:** Bun v1.3.14 (OpenCode 1.18.15)  
**OpenCode version:** 1.18.15
