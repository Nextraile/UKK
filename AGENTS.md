# AGENTS.md

> High-signal operational instructions for agents working in this Laravel 13 monolith.
> For requirements → `PRD.md` (130 FR). For design → `ARCHITECTURE.md` (9 COMP, 21 ADR). For UI/UX → `DESIGN.md` (38 components) + `PAGES.md` (57 pages). For tasks → `TODO.md` (84 tasks).

| Field | Value |
|---|---|
| Versi Dokumen | `1.0.6` |
| Terakhir Diperbarui | `2026-08-27` |

## Project Summary

**SewaKost** — Laravel 13 kost marketplace (booking, payment, reviews). Modular monolith, session auth, Blade+Alpine.js, QRIS payment, OTP email verification.

**Tech:** PHP 8.5, MySQL 8.0, Redis 7, Laravel Breeze (customized), Docker Sail (dev only), PHPUnit, PHPStan, Pint.

## Critical Commands

**All commands MUST run via Sail** (not bare `php`/`composer`/`npm`) — custom PHP 8.5 container. Service name is `laravel.test` (Sail-compatible), container name is `sewakost-app-1`.

```bash
# Start environment (first time or after reboot)
wsl ./vendor/bin/sail up -d

# Run tests (Definition of Done requirement)
wsl ./vendor/bin/sail artisan test                                   # PHPUnit (NOT Pest)
wsl ./vendor/bin/sail php vendor/bin/phpstan analyse                 # level 5 (via sail, bukan docker exec root)
wsl ./vendor/bin/sail pint                                           # auto-fix style

# Database
wsl ./vendor/bin/sail artisan migrate
wsl ./vendor/bin/sail artisan migrate:fresh --seed                   # WARNING: destroys data

# Create files
wsl ./vendor/bin/sail artisan make:model Domain/XXX/ModelName -mf   # migration + factory
wsl ./vendor/bin/sail artisan make:controller Admin/XXXController --resource

# Install dependencies
wsl ./vendor/bin/sail composer require vendor/package
wsl ./vendor/bin/sail npm install package-name

# Access
# http://localhost (app)
# http://localhost:8025 (Mailpit email UI)
```

**Note:** `compose.yaml` service `laravel.test` (Sail-compatible) dipakai wrapper `./vendor/bin/sail`; `container_name: sewakost-app-1` dieksplisitkan agar `docker exec sewakost-app-1 <command>` juga selalu jalan. Jika Sail wrapper gagal, fallback `docker exec sewakost-app-1 <command>`.

## Architecture Quick Reference

- **Structure:** Modular monolith. Domain logic in `app/Domain/<Component>/`, controllers in `app/Http/Controllers/<Role>/`, views in `resources/views/<role>/`.
- **Auth:** Laravel Breeze (session-based). **Customization required:** OTP email verification (6-digit, 15min expiry) instead of default link-based.
- **State machines:** Use Action classes for lifecycle transitions (Kost: draft→pending_review→approved→active, Rental: pending→paid→confirmed→active→completed). NO generic `$model->update(['status' => ...])` — see ADR-009.
- **JSON fields:** Facilities/rules stored as JSON arrays (`['facilities' => 'array']` cast). Document requirements, review images also JSON — see ADR-013, ADR-015.
- **Room availability:** Calculated real-time from rentals (`max_occupants - used_slots`), not denormalized in `rooms.status` — see ADR-017, ADR-018.
- **Routes:** Web routes only (`routes/web.php`). NO API routes unless explicitly required with ADR — see ARCHITECTURE.md §6.
- **Test framework:** PHPUnit (NOT Pest) — see ADR-021.

**Key ADRs to check before building:**
- ADR-010: Transactional rental creation with `SELECT...FOR UPDATE` room locking
- ADR-013: Facility/Rule as JSON (not normalized tables)
- ADR-014: QRIS static payment (no Midtrans)
- ADR-016: Min start_date = today+4 days (payment + doc verification time)
- ADR-020: PHP 8.5 (not 8.3)

## Documentation Sources (Check Before Coding)

**Laravel 13 specific:** Training data may have outdated APIs. ALWAYS check official docs first: https://laravel.com/docs/13.x

Full version table: `ARCHITECTURE.md` §3.1 (15 dependencies with official doc links).

**Before implementing any TASK:**
1. Read `TODO.md` for task acceptance criteria
2. Read referenced `FR-xxx` in `PRD.md` for business context
3. Read referenced `COMP-xxx` in `ARCHITECTURE.md` for technical design
4. Read `DESIGN.md` for UI/UX components and design tokens (±38 components)
5. Read `PAGES.md` for page-specific layout, data, and user flows (±57 pages & ±8 emails)
6. Check `ARCHITECTURE.md` §3.1 for library official docs

**If docs don't answer:** Create `Q-xxx` in `PRD.md` §13 — don't guess.

### UI/UX Documentation (NEW)

**DESIGN.md** — Design System & Component Library
- **Design tokens:** Colors, typography, spacing, shadows (Tailwind CSS 4.0 compatible)
- **±38 components:** Buttons, forms, cards, modals, navigation, tables, badges, alerts, loading states
- **Layout patterns:** Public (marketplace), Admin (sidebar), Auth (centered card)
- **Responsive design:** Mobile-first approach, breakpoints, touch targets
- **Accessibility:** WCAG 2.1 AA guidelines, keyboard nav, screen reader support
- **Implementation:** Blade + Alpine.js + Tailwind examples for every component

**PAGES.md** — Page & Interface Specifications
- **±57 pages:** Public (3), Auth (6), Tenant (16), Admin (21), Super Admin (11)
- **±8 email templates:** OTP verification, payment/document notifications, rental status changes
- **Each page spec includes:** URL, auth, layout structure, components used, data requirements, validation, user flows, edge cases, accessibility notes
- **Use this for:** Understanding page-specific requirements when implementing Blade views

**Workflow for UI implementation:**
```
TASK-xxx (from TODO.md)
  ↓
Read PAGES.md → Find page spec (e.g., PAGE-001: Landing Page)
  ↓
Read DESIGN.md → Reference components used (e.g., §3.3 Kost Card)
  ↓
Create Blade view in resources/views/ (copy-paste component HTML from DESIGN.md)
  ↓
Implement controller logic (data requirements from PAGES.md)
  ↓
Test user flows (from PAGES.md spec)
  ↓
Run accessibility audit (axe DevTools)
  ↓
Mark TASK Done
```

**UI Design Pipeline (4 Fase) — untuk pekerjaan desain/UI baru (dashboard, landing, komponen):**
```
Fase 1 — Perencanaan & Strategi (skill: design + ui-ux-pro-max)
  AI bertindak sebagai perencana & UX strategist. Tentukan kebutuhan pengguna & jenis
  produk; scan basis data untuk mencocokkan industri (SaaS/E-commerce/Fintech).
  Rancang struktur layout, aturan aksesibilitas (A11y, WCAG 2.1 AA), hierarki informasi.
  Output: cetak biru sistem desain awal → DESIGN.md.
  ↓
Fase 2 — Injeksi Estetika & Orisinalitas (skill: taste-skill)
  Cegah templat standar membosankan. Serap referensi estetika orisinal tingkat tinggi.
  Tentukan kombinasi font unik + ritme tata letak bernyawa — selayaknya desainer senior.
  ↓
Fase 3 — Polesan & Eksekusi Front-End (skill: impeccable)
  Bersihkan & sempurnakan implementasi. Perbaikan visual iteratif: kontras lemah,
  spacing acak, transisi/animasi kaku. Uji kualitas interface agar matang, profesional,
  production-grade.
  ↓
Fase 4 — Implementasi Kode Akhir (skill: ui-styling)
  Terjemahkan token desain ke tech stack (Tailwind). Definisi padding/margin/warna
  sebagai token semantik bersih — TANPA hex mentah di komponen.
```
Gunakan pipeline ini SEBELUM menulis layout baru; untuk polish halaman yang ada, loncat ke Fase 3.

## Code Conventions (Non-Obvious)

- **Naming:** Model `StudlyCase` singular, Controller `StudlyCase + Controller`, FormRequest `+ Request`, migration `snake_case + timestamp`, Blade `kebab-case.blade.php`, route name `dot.notation`.
- **Structure:** Domain logic in `app/Domain/<Component>/` (NOT `app/Services/`). Controllers in `app/Http/Controllers/<Role>/` (Admin, Tenant, SuperAdmin). Thin controllers — business logic in Action classes.
- **PHPDoc:** MUST include for all public methods with `@param`, `@return`, `@throws`. Explain *why*, not *what*.
- **Type hints:** MUST use strict types (`declare(strict_types=1)` recommended). No `mixed` unless necessary.
- **Eager loading:** MUST use `with()` in list queries to avoid N+1.
- **Validation:** MUST use Form Request classes (NOT controller validation).
- **Authorization:** MUST use Policy classes (NOT inline checks in controller).

**Style enforcement:** Laravel Pint (auto-run before marking task Done).

## Hard Rules

- **DO NOT** add dependencies without creating ADR in `ARCHITECTURE.md` + adding to §3.1 table.
- **DO NOT** edit `PRD.md` or `ARCHITECTURE.md` during normal task work — use `WORKFLOW.md` §Change Management.
- **DO NOT** commit secrets/keys. Use `.env` (already gitignored).
- **DO NOT** renumber existing IDs (`FR-xxx`, `TASK-xxx`). Mark deprecated instead.
- **DO NOT** work on tasks marked `Blocked` in `TODO.md`.
- **DO NOT** disable CSRF, session security, or `auth` middleware without ADR.
- **DO NOT** use `routes/api.php` — this is session-based (web routes only).
- **DO NOT** run `php`/`composer`/`npm` on host — MUST use `./vendor/bin/sail` for consistency.
- **DO NOT** use Sail config for production — Sail is dev-only (ADR-004).
- **DO NOT** mark TASK as Done if tests/lint fail.
- **DO NOT** work outside a new branch. Every change (coding, docs, env, config, etc.) MUST be done on a new branch based off the `agent` branch with prefix `agt/<change-summary>`. Examples: `agt/setup-opencode`, `agt/add-feature-x`, `agt/fix-bug-y`.
- **DO NOT** `git commit` or `git push` to git/GitHub. The user handles committing and pushing. The agent only makes changes on the working branch and reports them to the user.

**If user instructions conflict with PRD/ARCHITECTURE:** Flag conflict to user, don't silently pick one.

## Definition of Done

A `TASK-xxx` is Done when:
1. Acceptance criteria (from referenced `FR-xxx`) met
2. `./vendor/bin/sail artisan test` passes
3. `./vendor/bin/sail php vendor/bin/phpstan analyse` passes (level 5)
4. `./vendor/bin/sail pint` passes (auto-fix before commit)
5. No regressions in existing tests
6. Updated `TODO.md` status to Done

See `WORKFLOW.md` for full DoD checklist.

## Test Troubleshooting

Common test issues fixes. Run diagnostics before filing bug reports.

### Storage Permission Errors

**Symptom:** `FilesystemIterator::__construct(...): Permission denied` running tests `Storage::fake()`.

**Root cause:** Test storage directories created wrong ownership (root:root instead sail:sail).

**Fix:**
```bash
# Clean up test storage directories
wsl ./vendor/bin/sail exec laravel.test rm -rf /var/www/html/storage/framework/testing/disks/public/avatars
wsl ./vendor/bin/sail exec laravel.test rm -rf /var/www/html/storage/framework/testing/disks/public/kost-images
wsl ./vendor/bin/sail exec laravel.test rm -rf /var/www/html/storage/framework/testing/disks/public/qris

# Fix permissions (run after any permission errors)
wsl ./vendor/bin/sail exec laravel.test chmod -R 775 /var/www/html/storage/framework/testing
wsl ./vendor/bin/sail exec laravel.test chown -R sail:sail /var/www/html/storage/framework/testing
```

**Prevention:** Always run tests via `wsl ./vendor/bin/sail artisan test` (never root user).

---

### Database State Leaks

**Symptom:** Tests expect X records but find Y (e.g., expects 1 user, finds 7). Database count assertions fail.

**Root cause:** Test missing `RefreshDatabase` trait, causing data persist tests.

**Fix:**
```php
// Add test class
use Illuminate\Foundation\Testing\RefreshDatabase;

class YourTest extends TestCase
{
    use RefreshDatabase;  // ← Add this
    
    // ... tests
}
```

**Verification:**
```bash
# Run single test in isolation
wsl ./vendor/bin/sail artisan test --filter=YourTestName

# If passes alone but fails in suite → state leak confirmed
```

---

### Database Corruption

**Symptom:** Migration errors like "Table already exists" or "Table doesn't exist" during test runs.

**Root cause:** Corrupted `testing` database from interrupted migrations zombie test processes.

**Fix:**
```bash
# Nuclear option: wipe and rebuild
wsl ./vendor/bin/sail artisan db:wipe --database=mysql --force
wsl ./vendor/bin/sail artisan migrate:fresh --seed
wsl ./vendor/bin/sail artisan test
```

---

### Hanging Tests

**Symptom:** Test command runs but produces no output, times out after 3+ minutes.

**Root cause:** Zombie PHPUnit processes consuming resources deadlocked operations.

**Diagnosis:**
```bash
# Check for zombie processes
wsl docker exec sewakost-app-1 ps aux | grep -E "phpunit|artisan test"
```

**Fix:**
```bash
# Kill all test processes
wsl docker exec sewakost-app-1 pkill -f "phpunit"
wsl docker exec sewakost-app-1 pkill -f "artisan test"

# run tests again
wsl ./vendor/bin/sail artisan test
```

---

### PHPUnit Result Cache Permission

**Symptom:** Warning `file_put_contents(.phpunit.result.cache): Permission denied` (non-blocking).

**Fix:**
```bash
# Delete cache file, let PHPUnit recreate it
wsl rm -f /home/nextraile/Workspaces/Code/SewaKost/.phpunit.result.cache
```

**Note:** warning doesn't affect test results, safe ignore.

---

### Test Execution Best Practices

1. **Run full suite before marking task Done:**
   ```bash
   wsl ./vendor/bin/sail artisan test
   ```

2. **Run specific test debugging:**
   ```bash
   wsl ./vendor/bin/sail artisan test --filter=TestClassName::test_method_name
   ```

3. **Stop on first failure (faster feedback):**
   ```bash
   wsl ./vendor/bin/sail artisan test --stop-on-failure
   ```

4. **Check test coverage (if needed):**
   ```bash
   wsl ./vendor/bin/sail artisan test --coverage
   ```

5. **Never root** — always `./vendor/bin/sail` wrapper.

6. **If tests hang >1 minute** kill processes retry "Hanging Tests" above).

---

## Agent Coordination Strategy

### Format Response
- **Concise and to the point** — Avoid verbose explanations unless explicitly requested
- Answer directly without unnecessary preamble or postamble
- Use bullet points for lists, prose for explanations

### Planning Mode Workflow
When operating in planning mode:
1. **Ask clarifying questions** until requirements are clear enough to proceed
2. **Use deep dive subagents** (via Task tool) to assist with research and review different aspects of the plan
3. **Review research results** from subagents before presenting consolidated plan to user
4. **DO NOT implement** — planning mode is read-only, focus on analysis and design

### Build Mode Workflow
When operating in build mode:
- **Orkestrasi subagent wajib** — main agent adalah koordinator, bukan eksekutor. Setiap task (frontend, backend, review, test, security, devops, dokumentasi, desain) harus di-delegate ke subagent sesuai role spesifiknya masing-masing, meskipun kurang efisien. Main agent hanya orkestrasi, menyatukan hasil, dan menjalankan verifikasi akhir (test, phpstan, pint).
1. **Never implement features yourself when subagents are available** — act as coordinator
2. **Identify parallel workstreams** from the plan that can be implemented concurrently
3. **Delegate to specialized subagents** for implementation (use Task tool with clear, unambiguous briefs)
4. **Coordinate subagent work** — ensure briefs are very clear so subagents won't misinterpret requirements
5. **After task completion:**
   - Run tests (`./vendor/bin/sail artisan test`, `phpstan`, `pint`)
   - Verify acceptance criteria met
   - Summarize what changed (files modified, features added, issues fixed)
   - Update `TODO.md` status

### Subagent Delegation Guidelines
When delegating to subagents:
- **Be specific** — Include FR-xxx/COMP-xxx references, acceptance criteria, file paths
- **Provide context** — Link to relevant documentation sections (PRD.md, ARCHITECTURE.md, DESIGN.md, PAGES.md)
- **Set expectations** — Clarify what output you need (code implementation, analysis report, test results)
- **Avoid ambiguity** — Use concrete examples, not vague instructions like "make it better"

### Specialized Subagents Available
9 specialized subagents in `.opencode/agents/`:
- **architect-designer** — System architecture, database schema, endpoint design, technology selection
- **backend-developer** — Business logic, ORM, controllers, actions, policies, migrations
- **frontend-developer** — Blade views, Alpine.js, Tailwind styling, components, responsive design
- **code-reviewer** — Clean code review, performance analysis, architecture compliance, refactoring suggestions
- **security-auditor** — OWASP Top 10 auditing, input validation, credential protection, access control
- **qa-test-engineer** — Unit tests, integration tests, factories, edge case scenarios
- **devops-specialist** — Docker, CI/CD, Nginx config, environment management, migrations
- **technical-writer** — Documentation, changelog, API docs, user guides, README
- **ui-ux-director** — Design system fidelity, visual direction, UI/UX review, and coordination across all pages and components

Invoke with `@agent-name` or delegate via Task tool. See individual agent specs in `.opencode/agents/` for detailed responsibilities.

## Skills Available

19 skills in `.opencode/skills/`:
- **Development workflow:** `skill-architect`, `adr-writer`, `doc-consistency-check`, `doc-sync-checker`, `docs-lookup`, `skill-suggester`, `spec-sync`, `task-breakdown`, `test-writer`
- **Design:** `banner-design`, `brand`, `design`, `design-system`, `slides`, `ui-styling`, `ui-ux-pro-max`
- **UI craft:** `impeccable` (v4.1.1 — 20+ commands for iterative design work)
- **Skill discovery:** `find-skills` (ecosystem access via skills.sh)
- **UI/UX AI guidance:** `taste-skill` (design-taste-frontend v2 — anti-slop frontend rules, complements DESIGN.md)

See `SKILL.md` §1 for full descriptions and trigger keywords. Use `skill-architect` before creating new skills.

## Browser Automation (playwright-mcp)

**Playwright MCP v0.0.79** — Browser automation via accessibility snapshots (not screenshots). Enabled for UI testing, form automation, visual regression, and web scraping workflows.

**Config:** `opencode.json` → remote container (`sewakost-playwright-mcp`, headless Chromium, terminal tidak butuh Chrome).

**Index stats:** 40+ MCP tools covering navigation, interaction, network inspection, screenshots, console logs

### Key Concepts
- **Accessibility-based** — Pages represented as accessibility trees with element refs (e.g., `e5`, `e10`), not pixel coordinates
- **Headless in container** — Browser jalan di container `sewakost-playwright-mcp`; navigasi target `http://sewakost-app-1/`
- **Persistent sessions** — Login state, cookies, localStorage saved (`--isolated` off), reset via `docker restart sewakost-playwright-mcp`
- **Token efficient** — Snapshots cost ~200-400 tokens vs thousands for full DOM

### Common Tools

| Tool | Purpose | Read-only |
|------|---------|-----------|
| `browser_navigate` | Navigate to URL | No |
| `browser_snapshot` | Capture page structure with element refs | Yes |
| `browser_find` | Search snapshot for text/regex (cheaper than full snapshot) | Yes |
| `browser_click` | Click element by ref or selector | No |
| `browser_type` | Type text into element, optional submit | No |
| `browser_fill_form` | Fill multiple form fields at once | No |
| `browser_take_screenshot` | Capture visual evidence (PNG/JPEG/WebP) | Yes |
| `browser_console_messages` | Get JS errors/warnings/logs | Yes |
| `browser_network_requests` | Inspect network requests since page load | Yes |
| `browser_evaluate` | Execute JavaScript on page/element | No |

**Full tool list:** https://playwright.dev/mcp/tools

### Typical Workflow
```
1. browser_navigate → http://sewakost-app-1/
2. browser_snapshot → Get page structure, see [textbox "Email" [ref=e5]]
3. browser_type → { target: "e5", text: "user@example.com" }
4. browser_click → { target: "e10" } (submit button ref)
5. browser_wait_for → { text: "Welcome" }
6. browser_take_screenshot → Capture success state
7. browser_console_messages → Check for JS errors
```

### When to Use
- **UI testing** — Verify page rendering, form validation, user flows
- **Visual regression** — Screenshot comparison before/after changes
- **Form automation** — Fill multi-step forms, upload files
- **Network debugging** — Inspect API calls, verify request/response
- **Authentication flows** — Test login, OTP verification, session management
- **E2E workflows** — Complete rental flow (search → book → pay → upload docs)

### When NOT to Use
- **Coding tasks** — For code implementation, use read/edit/bash tools directly (more token-efficient)
- **API testing** — Use `bash` with `curl` instead (faster, no browser overhead)
- **Unit testing** — Use PHPUnit directly via `./vendor/bin/sail artisan test`

### Configuration Options
Current config in `opencode.json` (server remote di container):
```json
{
  "mcp": {
    "playwright": {
      "type": "remote",
      "url": "http://localhost:8931/mcp",
      "enabled": true
    }
  }
}
```

**Container setup** (browser runtime, portrait terpisah dari Sail):
```bash
docker compose -f docker-compose.playwright-mcp.yml up -d --build   # start / rebuild
docker compose -f docker-compose.playwright-mcp.yml down            # stop
```
- Image `sewakost-playwright-mcp` (Dockerfile `docker/playwright-mcp/`) = node:24-slim + `@playwright/mcp` v0.0.79 (playwright 1.63.0-alpha) + chromium + chrome-for-testing (headless, `--no-sandbox`).
- Terhubung ke network **`sewakost_sail`** sehingga bisa reach app: navigasi pakai `http://sewakost-app-1/` (BUKAN `localhost` — itu container itu sendiri, bukan host).
- Endpoint MCP: streamable HTTP di `http://localhost:8931/mcp` (legacy SSE: `/sse`).
- `--restart unless-stopped` → ikut bangun dengan Docker daemon.

**Common options** (ubah `CMD` di Dockerfile, tidak di config opencode):
- `--headless` — Run browser headless (default: headed)
- `--mobile` — Emulate generic mobile device
- `--caps=network,storage` — Enable network mocking, cookie/storage management
- `--console-level=debug` — Capture all console messages (default: info)
- `--isolated` — Don't persist profile to disk (ephemeral sessions)

**Example with options:**
```json
"command": ["npx", "@playwright/mcp@latest", "--mobile", "--caps=network,storage"]
```
(untuk container: setara diubah lewat baris `CMD` di `docker/playwright-mcp/Dockerfile`)

### Security Notes
- `--allow-unrestricted-file-access` disabled by default (restricts to workspace roots)
- `browser_run_code_unsafe` is RCE-equivalent — only use for trusted code
- Profile persists cookies/localStorage — clear manually if testing auth flows with different accounts

**Docs:** https://playwright.dev/mcp/introduction

---

**Project status:** Environment ready. Technical debt cleanup complete (26 issues fixed, v1.0.6). UI/UX documentation complete (DESIGN.md + PAGES.md). Skills operational (19 installed). Codegraph indexed (230 files). 84 tasks in TODO.md (~66 days, 13-14 weeks). COMP-001 (Identity, 13 tasks) Done ✅. COMP-002 (Kost Publication, 10 tasks) Done ✅. COMP-003 (Kost Configuration, 7 tasks) Done ✅. COMP-004 (Booking, 9 tasks) Done ✅. COMP-005 (Rental Management, 11 tasks) Done ✅. COMP-006 (Document Verification, 9 tasks) Done ✅. Ready for COMP-007 (Payment Management).

**Documentation inventory:**
- PRD.md (792 lines): 130 FR, 29 NFR, 22 US, 4 personas
- ARCHITECTURE.md (1606 lines): 9 COMP, 21 ADR, data models, routes
- DESIGN.md (4340 lines): Design system, 38 components, layout patterns, accessibility guidelines
- PAGES.md (1928 lines): 57 page specs + 8 email templates
- TODO.md (405 lines): 84 tasks across 9 components
- WORKFLOW.md (133 lines): 5-phase development process
- AGENTS.md (this file): Operational instructions
- MANUAL.md (314 lines): Development methodology

Total: 9,886 lines of documentation

## graphify

This project has a knowledge graph at graphify-out/ with god nodes, community structure, and cross-file relationships.

use the installed graphify skill or instructions before doing anything else.

Rules:
- For codebase questions, first run `graphify query "<question>"` when graphify-out/graph.json exists. Use `graphify path "<A>" "<B>"` for relationships and `graphify explain "<concept>"` for focused concepts. These return a scoped subgraph, usually much smaller than GRAPH_REPORT.md or raw grep output.
- Dirty graphify-out/ files are expected after hooks or incremental updates; dirty graph files are not a reason to skip graphify. Only skip graphify if the task is about stale or incorrect graph output, or the user explicitly says not to use it.
- If graphify-out/wiki/index.md exists, use it for broad navigation instead of raw source browsing.
- Read graphify-out/GRAPH_REPORT.md only for broad architecture review or when query/path/explain do not surface enough context.
- After modifying code, run `graphify update .` to keep the graph current (AST-only, no API cost).
