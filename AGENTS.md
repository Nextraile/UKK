# AGENTS.md

> High-signal operational instructions for agents working in this Laravel 13 monolith.
> For requirements → `PRD.md` (129 FR). For design → `ARCHITECTURE.md` (8 COMP, 21 ADR). For UI/UX → `DESIGN.md` (35+ components) + `PAGES.md` (54 pages). For tasks → `TODO.md` (78 tasks).

| Field | Value |
|---|---|
| Versi Dokumen | `1.0.2` |
| Terakhir Diperbarui | `2026-08-17` |

## Project Summary

**SewaKost** — Laravel 13 kost marketplace (booking, payment, reviews). Modular monolith, session auth, Blade+Alpine.js, QRIS payment, OTP email verification.

**Tech:** PHP 8.5, MySQL 8.0, Redis 7, Laravel Breeze (customized), Docker Sail (dev only), PHPUnit, PHPStan, Pint.

## Critical Commands

**All commands MUST run via Sail** (not bare `php`/`composer`/`npm`) — custom PHP 8.5 container. Service name is `app`, container name is `sewakost-app-1`.

```bash
# Start environment (first time or after reboot)
./vendor/bin/sail up -d

# Run tests (Definition of Done requirement)
./vendor/bin/sail artisan test                                   # PHPUnit (NOT Pest)
docker exec sewakost-app-1 ./vendor/bin/phpstan analyse          # level 5
./vendor/bin/sail pint                                           # auto-fix style

# Database
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan migrate:fresh --seed                   # WARNING: destroys data

# Create files
./vendor/bin/sail artisan make:model Domain/XXX/ModelName -mf   # migration + factory
./vendor/bin/sail artisan make:controller Admin/XXXController --resource

# Install dependencies
./vendor/bin/sail composer require vendor/package
./vendor/bin/sail npm install package-name

# Access
# http://localhost (app)
# http://localhost:8025 (Mailpit email UI)
```

**Gotcha:** `./vendor/bin/sail` commands expect service named `laravel.test` but actual service is `app`. Use `docker exec sewakost-app-1 <command>` if Sail wrapper fails.

## Architecture Quick Reference

- **Structure:** Modular monolith. Domain logic in `app/Domain/<Component>/`, controllers in `app/Http/Controllers/<Role>/`, views in `resources/views/<role>/`.
- **Auth:** Laravel Breeze (session-based). **Customization required:** OTP email verification (6-digit, 15min expiry) instead of default link-based. NOT implemented yet — see COMP-001 in TODO.md.
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
4. **Read `DESIGN.md` for UI/UX components and design tokens** (2588 lines, 35+ components)
5. **Read `PAGES.md` for page-specific layout, data, and user flows** (1219 lines, 54 pages + 7 emails)
6. Check `ARCHITECTURE.md` §3.1 for library official docs

**If docs don't answer:** Create `Q-xxx` in `PRD.md` §13 — don't guess.

### UI/UX Documentation (NEW)

**DESIGN.md** — Design System & Component Library
- **Design tokens:** Colors, typography, spacing, shadows (Tailwind CSS 4.0 compatible)
- **35+ components:** Buttons, forms, cards, modals, navigation, tables, badges, alerts, loading states
- **Layout patterns:** Public (marketplace), Admin (sidebar), Auth (centered card)
- **Responsive design:** Mobile-first approach, breakpoints, touch targets
- **Accessibility:** WCAG 2.1 AA guidelines, keyboard nav, screen reader support
- **Implementation:** Blade + Alpine.js + Tailwind examples for every component

**PAGES.md** — Page & Interface Specifications
- **54 pages:** Public (3), Auth (6), Tenant (14), Admin (21), Super Admin (10)
- **7 email templates:** OTP verification, payment/document notifications, rental status changes
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
3. `docker exec sewakost-app-1 ./vendor/bin/phpstan analyse` passes (level 5)
4. `./vendor/bin/sail pint` passes (auto-fix before commit)
5. No regressions in existing tests
6. Updated `TODO.md` status to Done

See `WORKFLOW.md` for full DoD checklist.

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
8 specialized subagents in `.opencode/agents/`:
- **architect-designer** — System architecture, database schema, endpoint design, technology selection
- **backend-developer** — Business logic, ORM, controllers, actions, policies, migrations
- **frontend-developer** — Blade views, Alpine.js, Tailwind styling, components, responsive design
- **code-reviewer** — Clean code review, performance analysis, architecture compliance, refactoring suggestions
- **security-auditor** — OWASP Top 10 auditing, input validation, credential protection, access control
- **qa-test-engineer** — Unit tests, integration tests, factories, edge case scenarios
- **devops-specialist** — Docker, CI/CD, Nginx config, environment management, migrations
- **technical-writer** — Documentation, changelog, API docs, user guides, README

Invoke with `@agent-name` or delegate via Task tool. See individual agent specs in `.opencode/agents/` for detailed responsibilities.

## Skills Available

19 skills in `.opencode/skills/`:
- **Development workflow:** `skill-architect`, `adr-writer`, `doc-consistency-check`, `doc-sync-checker`, `docs-lookup`, `skill-suggester`, `spec-sync`, `task-breakdown`, `test-writer`
- **Design:** `banner-design`, `brand`, `design`, `design-system`, `slides`, `ui-styling`, `ui-ux-pro-max`
- **UI craft:** `impeccable` (v4.1.1 — 20+ commands for iterative design work)
- **Skill discovery:** `find-skills` (ecosystem access via skills.sh)
- **UI/UX AI guidance:** `taste-skill` (design-taste-frontend v2 — anti-slop frontend rules, complements DESIGN.md)

See `SKILL.md` §1 for full descriptions and trigger keywords. Use `skill-architect` before creating new skills.

## Code Intelligence (codegraph MCP)

**codegraph v1.5.0** — Pre-indexed code knowledge graph for faster agent context (44% lower cost, 62% fewer tokens, 88% fewer tool calls per benchmark).

**Index stats:** 230 files · 4,754 nodes · 13,565 edges · 22.5 MB SQLite (auto-syncs on file changes)

**Available MCP tools:**
- `codegraph_explore <query>` — Find relevant symbols + source code + call paths in one shot (PREFERRED over grep+Read)
- `codegraph_node <name>` — Get one symbol's source + caller/callee trail
- `codegraph_query <search>` — Full-text search for symbols across codebase
- `codegraph_files` — Show project file structure from index

**When to use:**
- "Find the User model" → `codegraph_explore "User model"`
- "What calls this method?" → `codegraph_node "MethodName"`
- "Where is OTP verification?" → `codegraph_query "OTP"`
- Before refactoring: check blast radius (what depends on this symbol)
- Faster than grep + multiple Read calls — one query returns full context

**CLI commands** (terminal, not MCP):
- `codegraph status` — Check index health + sync status
- `codegraph explore "<query>"` — CLI exploration
- `codegraph sync` — Manual sync (rarely needed, auto-sync default ON)

**Index location:** `.codegraph/` (gitignored, regenerate with `codegraph init`)

## Browser Automation (playwright-mcp)

**Playwright MCP v0.0.79** — Browser automation via accessibility snapshots (not screenshots). Enabled for UI testing, form automation, visual regression, and web scraping workflows.

**Config:** `opencode.json` (headed Chrome by default, profile persists between sessions)

**Index stats:** 40+ MCP tools covering navigation, interaction, network inspection, screenshots, console logs

### Key Concepts
- **Accessibility-based** — Pages represented as accessibility trees with element refs (e.g., `e5`, `e10`), not pixel coordinates
- **Persistent sessions** — Login state, cookies, localStorage saved between sessions (profile at `~/.cache/ms-playwright/mcp-chrome-{workspace-hash}`)
- **Token efficient** — Snapshots cost ~200-400 tokens vs thousands for full DOM
- **Headed by default** — Browser opens visibly so you can see what's happening

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
1. browser_navigate → https://localhost
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
Current config in `opencode.json`:
```json
{
  "mcp": {
    "playwright": {
      "type": "local",
      "command": ["npx", "@playwright/mcp@latest"],
      "enabled": true
    }
  }
}
```

**Common options** (add to `"command"` array):
- `--headless` — Run browser headless (default: headed)
- `--mobile` — Emulate generic mobile device
- `--caps=network,storage` — Enable network mocking, cookie/storage management
- `--console-level=debug` — Capture all console messages (default: info)
- `--isolated` — Don't persist profile to disk (ephemeral sessions)

**Example with options:**
```json
"command": ["npx", "@playwright/mcp@latest", "--mobile", "--caps=network,storage"]
```

### Security Notes
- `--allow-unrestricted-file-access` disabled by default (restricts to workspace roots)
- `browser_run_code_unsafe` is RCE-equivalent — only use for trusted code
- Profile persists cookies/localStorage — clear manually if testing auth flows with different accounts

**Docs:** https://playwright.dev/mcp/introduction

---

**Project status:** Environment ready. UI/UX documentation complete (DESIGN.md + PAGES.md). Skills operational (19 installed). Codegraph indexed (230 files). 78 tasks in TODO.md (~62 days, 12-14 weeks). Ready to start TASK-001 (User migration + OTP verification).

**Documentation inventory:**
- PRD.md (783 lines): 129 FR, 29 NFR, 22 US, 4 personas
- ARCHITECTURE.md (1572 lines): 8 COMP, 21 ADR, data models, routes
- DESIGN.md (2585 lines): Design system, 35+ components, layout patterns, accessibility guidelines
- PAGES.md (1216 lines): 54 page specs + 7 email templates
- TODO.md (321 lines): 78 tasks across 9 components
- WORKFLOW.md (130 lines): 5-phase development process
- AGENTS.md (this file): Operational instructions
- MANUAL.md (307 lines): Development methodology

Total: 7,228 lines of documentation
