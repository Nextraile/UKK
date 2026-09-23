## Project Summary

**SewaKost** — Laravel 13 kost marketplace (booking, payment, reviews). Modular monolith, session auth, Blade+Alpine.js, manual payment verification, OTP email verification.

**Tech:** PHP 8.5, MySQL 8.0, Redis 7, Laravel Breeze (customized), Docker Sail (dev only), PHPUnit, PHPStan, Pint.

## Critical Commands

**All commands MUST run via Sail** (not bare `php`/`composer`/`npm`) — custom PHP 8.5 container. Service name is `laravel.test` (Sail-compatible), container name is `sewakost-app-1`.

```bash
# Start environment (first time or after reboot)
wsl ./vendor/bin/sail up -d

# Check Supervisor status (queue worker, scheduler, vite, php server)
wsl ./vendor/bin/sail exec laravel.test supervisorctl status         # Should show 4 programs RUNNING
wsl ./vendor/bin/sail exec laravel.test supervisorctl restart laravel-queue  # Restart queue worker if needed

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
```

**Note:** `compose.yaml` service `laravel.test` (Sail-compatible) dipakai wrapper `./vendor/bin/sail`; `container_name: sewakost-app-1` dieksplisitkan agar `docker exec sewakost-app-1 <command>` juga selalu jalan. Jika Sail wrapper gagal, fallback `docker exec sewakost-app-1 <command>`.

## Architecture Quick Reference

- **Structure:** Modular monolith. Domain logic in `app/Domain/<Component>/`, controllers in `app/Http/Controllers/<Role>/`, views in `resources/views/<role>/`.
- **Auth:** Laravel Breeze (session-based). **Customization required:** OTP email verification (6-digit, 15min expiry) instead of default link-based.
- **State machines:** Use Action classes for lifecycle transitions (Kost: draft→pending_review→approved→active, Rental: pending→paid→confirmed→active→completed). NO generic `$model->update(['status' => ...])`.
- **Routes:** Web routes only (`routes/web.php`). NO API routes unless explicitly required with ADR.
- **Test framework:** PHPUnit (NOT Pest).

## Documentation Sources (Check Before Coding)

**Laravel 13 specific:** Training data may have outdated APIs. ALWAYS check official docs first: https://laravel.com/docs/13.x

**If docs don't answer:** ask! don't guess.

### UI/UX Documentation

**DESIGN.md** — Design System & Component Library
- **Design tokens:** Colors, typography, spacing, shadows (Tailwind CSS 4.0 compatible)
- **Components:** Buttons, forms, cards, modals, navigation, tables, badges, alerts, loading states
- **Layout patterns:** Public (marketplace), Admin (sidebar), Auth (centered card)
- **Responsive design:** Mobile-first approach, breakpoints, touch targets
- **Accessibility:** WCAG 2.1 AA guidelines, keyboard nav, screen reader support
- **Implementation:** Blade + Alpine.js + Tailwind examples for every component

**PAGES.md** — Page & Interface Specifications
- **Pages:** Public, Auth, Tenant, Admin, Super Admin
- **Email templates:** OTP verification, payment/document notifications, rental status changes
- **Each page spec includes:** URL, auth, layout structure, components used, data requirements, validation, user flows, edge cases, accessibility notes
- **Use this for:** Understanding page-specific requirements when implementing Blade views

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

- **DO NOT** disable CSRF, session security, or `auth` middleware without ADR.
- **DO NOT** use `routes/api.php` — this is session-based (web routes only).
- **DO NOT** run `php`/`composer`/`npm` on host — MUST use `./vendor/bin/sail` for consistency.
- **DO NOT** use Sail config for production — Sail is dev-only.
- **DO NOT** `git commit` or `git push` to git/GitHub. The user handles committing and pushing. The agent only makes changes on the working branch and reports them to the user.

**If user instructions conflict with PRD/ARCHITECTURE:** Flag conflict to user, don't silently pick one.

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

6. **If tests hang >1 minute** kill processes retry "Hanging Tests" above.

---

## Agent Coordination Strategy

### Format Response
- **Concise and to the point** — Avoid verbose explanations unless explicitly requested
- Answer directly without unnecessary preamble or postamble
- Use bullet points for lists, prose for explanations

### Planning Mode Workflow
1. **Ask clarifying questions** until requirements are clear enough to proceed
2. **Use deep dive subagents** (via Task tool) to assist with research and review different aspects of the plan
3. **Review research results** from subagents before presenting consolidated plan to user

### Build Mode Workflow
- **Orkestrasi subagent wajib** — main agent adalah koordinator, bukan eksekutor. Setiap task (frontend, backend, review, test, security, devops, dokumentasi, desain) harus di-delegate ke subagent sesuai role spesifiknya masing-masing, meskipun kurang efisien. Main agent hanya orkestrasi, menyatukan hasil, dan menjalankan verifikasi akhir (test, phpstan, pint), serta menyesuaikan hasil kerja subagents.
1. **Never implement features yourself when subagents are available** — act as coordinator
2. **Identify parallel workstreams** from the plan that can be implemented concurrently
3. **Delegate to specialized subagents** for implementation (use Task tool with clear, unambiguous briefs)
4. **Coordinate subagent work** — ensure briefs are very clear so subagents won't misinterpret requirements
5. **After task completion:**
   - Run tests (`./vendor/bin/sail artisan test`, `phpstan`, `pint`)
   - Verify acceptance criteria met
   - Summarize what changed (files modified, features added, issues fixed)

### Subagent Delegation Guidelines
- **Be specific** — Include file paths, line numbers, and code snippets when possible
- **Provide context** — Explain why the change is needed, not just what to change
- **Set expectations** — Clarify what output you need (code implementation, analysis report, test results)
- **Avoid ambiguity** — Use concrete examples, not vague instructions like "make it better"
