# TODO.md — Task Board & Traceability Matrix

> Single Source of Truth untuk PEKERJAAN KONKRET. Menggantikan Project Plan/backlog tool eksternal.
> Setiap `TASK-xxx` WAJIB merujuk ke `FR-xxx`/`NFR-xxx` (`PRD.md`) dan `COMP-xxx` (`ARCHITECTURE.md`) — tidak boleh ada task "melayang" tanpa alasan requirement.

| Field | Value |
|---|---|
| Nama Proyek | SewaKost — Web Marketplace Kost Management & Rental System |
| Versi Dokumen | `1.0.6` |
| Terakhir Diperbarui | `2026-08-27` |

---

## 0. Status yang Valid

`Not Started` → `Ready` → `In Progress` → `Review` → `Done`
(status tambahan: `Blocked`, `Deprecated`)

Lihat definisi **Definition of Ready** & **Definition of Done** lengkap di `WORKFLOW.md` §3–§4 — jangan duplikasi di sini.

---

## 0.1 Documentation Status

> Semua dokumen proyek sudah lengkap dan siap untuk fase implementasi.

| Dokumen | Baris | Status | Konten |
|---|---|---|---|
| `PRD.md` | 792 | ✅ Complete | 130 FR, 29 NFR, 22 US, 4 persona |
| `ARCHITECTURE.md` | 1,606 | ✅ Complete | 9 COMP, 21 ADR, data model, routes |
| `DESIGN.md` | 4,340 | ✅ Complete | Design tokens, 38 components, layout patterns, a11y |
| `PAGES.md` | 1,928 | ✅ Complete | 57 page specs + 8 email templates |
| `WORKFLOW.md` | 133 | ✅ Complete | 5-phase development process |
| `AGENTS.md` | 368 | ✅ Complete | Operational instructions, UI/UX workflow |
| `MANUAL.md` | 314 | ✅ Complete | 8-document map, methodology |
| `SKILL.md` | 135 | ✅ Complete | 19 skills registry |
| **Total** | **9,842** | **All ready** | Complete project documentation |

> **Catatan:** Saat implementasi TASK-xxx, baca `PAGES.md` untuk spec halaman dan `DESIGN.md` untuk komponen UI. Lihat workflow diagram di `AGENTS.md` §Documentation Sources.

### Development Tools

| Tool | Version | Scope | Status |
|---|---|---|---|
| **taste-skill** (design-taste-frontend v2) | Experimental | Project-local (`.opencode/skills/taste-skill/`) | ✅ Installed — UI/UX AI guidance, anti-slop design |
| **codegraph** | 1.5.0 | Global CLI + Project index | ✅ Indexed — 230 files, 4,754 nodes, 13,565 edges |
| **codegraph MCP** | 1.5.0 | Global OpenCode config | ✅ Wired — `codegraph_explore`, `codegraph_node`, `codegraph_query`, `codegraph_files` |

**Codegraph usage:** Use `codegraph_explore "<query>"` MCP tool for code structure queries (faster than grep+Read). Index auto-syncs on file changes. CLI: `codegraph status` to check health.

---

## 1. Task Board

> Breakdown task per `COMP-xxx` untuk eksekusi sistematis. Total: **84 tasks** dari **130 FR** (Must-have prioritized).
> 
> **Dependency Legend:**
> - COMP-001 (Identity) → baseline untuk semua komponen
> - COMP-002 (Kost Publication) → COMP-003 (Kost Config) → COMP-004 (Room Inventory) → COMP-005 (Marketplace) → COMP-006 (Rental) → COMP-007 (Payment) & COMP-008 (Review)
> - COMP-009 (Administration) → parallel dengan COMP-002

### Komponen: COMP-001 — Identity & Account Management

| ID | Judul Task | FR/NFR Terkait | Dependency | Prioritas | Status | Estimasi | Catatan |
|---|---|---|---|---|---|---|---|
| TASK-001 | Setup migration & model User dengan OTP verification | FR-001—FR-013, NFR-004 | — | Must | Done | 0.5 hari | ✅ Migration users (DM-001 schema) + otp_verifications table, User model moved to app/Domain/Identity/Models/ with SoftDeletes, OtpVerification model, factories updated |
| TASK-002 | Customize Breeze untuk OTP email verification | FR-004, FR-005, FR-128 | TASK-001 | Must | Done | 1 hari | ✅ OtpService (dual storage: DB hashed SHA-256 + Redis cache), OtpVerificationMail, EmailVerificationController (replaces Breeze link-based), Alpine.js OTP input view, throttle:5,1 + attempt counter + lockout |
| TASK-003 | Implementasi login & logout (semua role) | FR-001, FR-002, FR-007 | TASK-001 | Must | Done | 0.5 hari | ✅ Role-based redirect via User::dashboardRoute(), soft-deleted user check in LoginRequest, login/register Blade views per PAGES.md PAGE-004/005 |
| TASK-004 | Implementasi tenant self-registration | FR-003 | TASK-002 | Must | Done | 0.5 hari | ✅ RegistrationRequest form validation, RegisteredUserController (tanpa OTP otomatis sejak TASK-086), session regeneration after login |
| TASK-005 | Middleware email verification untuk specific features | FR-006 | TASK-002 | Must | Done | 0.5 hari | ✅ EnsureEmailIsVerified middleware (custom, NOT global — only for specific routes like rentals/create), documented in routes/web.php |
| TASK-006 | Profile management (view & edit) | FR-009, FR-010, FR-011 | TASK-003 | Must | Done | 1 hari | ✅ ProfileController: show, edit, update (email change triggers OTP re-verification FR-129), updateAvatar (guessExtension, generated filename), destroy (soft delete) |
| TASK-007 | Soft delete account & prevent deleted user auth | FR-012, FR-013 | TASK-003 | Should | Done | 0.5 hari | ✅ ActiveUser middleware in web group (force logout soft-deleted users), 3-layer defense: SoftDeletes trait + LoginRequest check + ActiveUser middleware |
| TASK-008 | RBAC middleware & Policy setup | FR-007, FR-008, NFR-005 | TASK-003 | Must | Done | 1 hari | ✅ CheckRole middleware (alias: role), EnsureEmailIsVerified (alias: verified), ActiveUser (alias: active), UserPolicy registered in AuthServiceProvider |
| TASK-009 | Unit & feature tests COMP-001 | FR-001—FR-013 | TASK-001—TASK-008 | Must | Done | 1 hari | ✅ 73 tests, 192 assertions, all pass. 5 new test files (UserModelTest, OtpServiceTest, OtpVerificationTest, AccountDeletionTest, RbacTest) + 3 updated existing |
| TASK-085 | Password Reset via OTP | FR-130 | TASK-001, TASK-002 | Must | Done | 1 hari | ✅ OtpService purpose 'password-reset', verify(markEmailVerified:false), PasswordResetLinkController OTP flow 3 langkah, ResetPasswordRequest, anti-enumeration, session guard antar step |
| TASK-086 | On-Demand Email Verification | FR-003, FR-004, FR-006 | TASK-002, TASK-005 | Must | Done | 0.5 hari | ✅ Registrasi tanpa OTP + redirect /marketplace, OtpService lazy generate saat buka /verify-email (throttle:5,1), middleware verified → flash verify_email_prompt + modal popup CTA, MarketplaceController stub interim (diganti TASK-036) |
| TASK-087 | Tombol Verifikasi Email di Profil + fix avatar upload env | FR-004, FR-011 | TASK-086, TASK-006 | Must | Done | 0.5 hari | ✅ Button 'Verifikasi Email' di profile show+edit (route verification.notice), fix error tempnam(): storage writable untuk user runtime (chmod ug+rwX storage bootstrap/cache) |
| TASK-088 | Registrasi email sudah terpakai (termasuk akun terhapus): pesan 'Email tidak dapat digunakan.' | FR-003, FR-013 | TASK-007 | Must | Done | 0.25 hari | ✅ RegistrationRequest email.unique message → 'Email tidak dapat digunakan.' (tanpa branch trashed). Dead-end 'silakan masuk' utk akun soft-deleted dihilangkan |

**Subtotal COMP-001:** 13 tasks, ~8.75 hari

---

### Komponen: FRONTEND DESIGN SYSTEM

| ID | Judul Task | FR/NFR Terkait | Dependency | Prioritas | Status | Estimasi | Catatan |
|---|---|---|---|---|---|---|---|
| TASK-DESIGN-001 | Button component fixes (primary/secondary/danger) | NFR-016 (accessibility), NFR-017 (brand consistency) | — | Must | Done | 0.75 hari | ✅ Breeze defaults → DESIGN.md §3.1 (primary-600, focus-visible, text-base, semantic tokens). 3 files: primary-button, secondary-button, danger-button. Eliminates gray-800/indigo focus, adds shadow elevation |
| TASK-DESIGN-002 | Input component fixes (text-input/input-label/input-error) | NFR-016 (WCAG 2.1 AA) | — | Must | Done | 1 hari | ✅ CRITICAL: Error contrast 3.4:1 → 5.9:1 (text-red-600 → text-error-700). Semantic dark tokens (border-strong-dark, surface-dark, text-dark). Required field markers (*). Alert icon + role="alert". Primary focus rings |
| TASK-DESIGN-003 | Layout accessibility (skip links, logo colors) | NFR-016 (WCAG 2.4.1 Level A) | — | Must | Done | 0.5 hari | ✅ Skip links added to app.blade.php + guest.blade.php. Main landmark id="main-content". Logo indigo → primary-600. Keyboard navigation compliant |
| TASK-DESIGN-004 | Navigation & modal fixes (indigo → primary, ARIA) | NFR-016 (accessibility), NFR-017 (brand) | — | Must | Done | 0.75 hari | ✅ 26 indigo replacements across 4 files (nav-link, responsive-nav-link, modal, verify-email-modal). Modal ARIA: role="dialog", aria-modal="true". Semantic overlay/bg tokens. All focus-visible patterns |

**Subtotal FRONTEND DESIGN SYSTEM:** 4 tasks, ~3 hari

**Phase 1 Completion Status (2026-08-22):**
- ✅ All 88 tests passing (247 assertions)
- ✅ PHPStan level 5: No errors
- ✅ Laravel Pint: 67 files clean
- ✅ Zero indigo color references in components/
- ✅ Zero text-red-600 (WCAG contrast fixed)
- ✅ 12 files modified (9 components, 2 layouts, 1 modal)
- ✅ Brand consistency: Primary blue (#2563EB) on all CTAs
- ✅ Accessibility: Skip links, WCAG AA contrast, ARIA attributes, required markers

**Phase 2-3 DEFERRED (Backend-First Priority):**
- **Decision Date:** 2026-08-22
- **Rationale:** Focus on backend functionality (COMP-002—COMP-008) first. Comprehensive frontend redesign (welcome page, email templates, marketplace UI, 33 missing components) will resume after backend complete.
- **Policy:** Frontend may remain simple as long as it does not violate mandatory requirements (WCAG 2.1 AA, semantic HTML, keyboard nav). Design tokens optional for rapid backend development.
- **Deferred Scope:** 
  - Welcome page redesign (29 hardcoded hex values → semantic tokens, DESIGN.md §4.1)
  - Email template colors (18 hardcoded hex → primary tokens)
  - Marketplace UI implementation (filter sidebar, kost cards, pagination per PAGE-002)
  - 33 missing components (DESIGN.md §3: kost-card, status-badge, breadcrumbs, footer, etc.)
  - Full PAGE-001—PAGE-057 implementation
- **Estimated Effort:** 20-30 hours when resumed
- **Timeline:** Resume after COMP-008 (Review Management) complete

---

### Komponen: COMP-002 — Kost Publication Management

| ID | Judul Task | FR/NFR Terkait | Dependency | Prioritas | Status | Estimasi | Catatan |
|---|---|---|---|---|---|---|---|
| TASK-010 | Setup migration & model Kost (status lifecycle) | FR-014—FR-023 | TASK-001 | Must | Done | 0.5 hari | ✅ 5 migrations, Kost model, KostPolicy, 4 factories, 38 tests (24 KostPolicyTest, 14 KostBasicTest) |
| TASK-011 | Admin create & update Kost Draft | FR-014, FR-015 | TASK-010, TASK-008 | Must | Done | 1 hari | ✅ Admin\KostController (7 RESTful), StoreKostRequest, UpdateKostRequest, 5 Blade views, 17 tests (AdminKostCrudTest) |
| TASK-012 | Action: SubmitKostForReview dengan validation data wajib | FR-016, FR-017 | TASK-011 | Must | Done | 1 hari | ✅ SubmitKostForReview Action, InvalidKostSubmissionException, submit() route, UI button, 11 tests (KostSubmitWorkflowTest) |
| TASK-013 | SuperAdmin review & approve/reject submission | FR-018, FR-019 | TASK-012 | Must | Done | 1 hari | ✅ ApproveKost/RejectKost Actions (12 unit tests), SuperAdmin\KostSubmissionController (4 routes), 2 views (index, show), 21 tests (19 KostSubmissionWorkflowTest + 2 email), 3 Mailables (KostSubmittedMail, KostApprovedMail, KostRejectedMail) |
| TASK-014 | Admin revise rejected kost | FR-020 | TASK-013 | Must | Done | 0.5 hari | ✅ Auto-revert rejected → draft on update (implemented in KostController::update()) |
| TASK-015 | Admin publish approved kost | FR-021 | TASK-013 | Must | Done | 0.5 hari | ✅ PublishKost Action (5 unit tests), publish() method in KostController, route, UI button, 6 feature tests, 4 policy tests |
| TASK-016 | Prevent direct status change (enforce workflow) | FR-023 | TASK-012—TASK-015 | Must | Done | 0.5 hari | ✅ Status removed from $fillable, Actions use direct assignment, FormRequests validate status=prohibited, 9 tests (KostStatusProtectionTest 7 + KostModelTest 2) |
| TASK-017 | Unit & feature tests COMP-002 | FR-014—FR-023 | TASK-010—TASK-016 | Must | Done | 1 hari | ✅ 117 Kost-related tests passing (469 total assertions): 45 unit, 72 feature. State machine, validation, authorization all covered |
| TASK-089 | Admin cancel kost submission (UI + backend) | FR-016, FR-023 | TASK-012, TASK-013 | Should | Done | 2.5 jam | ✅ CancelKostSubmission Action, KostPolicy::cancel(), KostController::cancel(), route DELETE admin/kosts/{kost}/cancel, modal UI dengan Alpine.js (DESIGN.md §3.5), 21 tests (5 unit Action + 7 unit Policy + 9 feature workflow), migration add submitted_at timestamp |
| TASK-090 | Fix SuperAdmin redirect after login | FR-007 | TASK-001 | Must | Done | 0.5 jam | ✅ User::dashboardRoute() fix (/superadmin/submissions → /super-admin/kost-submissions), AuthenticationTest coverage updated |

**Subtotal COMP-002:** 10 tasks, ~9.5 hari

---

### Komponen: COMP-003 — Kost Configuration Management

| ID | Judul Task | FR/NFR Terkait | Dependency | Prioritas | Status | Estimasi | Catatan |
|---|---|---|---|---|---|---|---|
| TASK-018 | Setup migration Address, KostImage, Category, KostDocumentRequirement | FR-024—FR-035 | TASK-010 | Must | Done | 1 hari | ✅ 2 migrations (kost_images, kost_document_requirements), 2 models (KostImage, KostDocumentRequirement), config/kost.php, CategorySeeder (3 categories), 2 factories, relasi Kost::kostImages() + documentRequirements() |
| TASK-019 | Configure kost basic info & address | FR-024, FR-025 | TASK-018, TASK-011 | Must | Done | 1 hari | ✅ Address form section di edit.blade.php, UpdateKostRequest validation (8 address fields), KostController::update updateOrCreate logic, 9 tests (KostAddressTest) |
| TASK-020 | Upload & manage kost images (thumbnail + galeri) | FR-026, NFR-008 | TASK-018 | Must | Done | 1 hari | ✅ KostImageController (store, destroy, setThumbnail, updateSortOrder), filename pattern kost-{id}-img-{Ymd-His}-{seq}.{ext}, KostImagePolicy, admin/kosts/config/images.blade.php, 17 tests |
| TASK-021 | Assign kost categories (from master) | FR-027 | TASK-018, TASK-025 | Must | Done | 0.5 hari | ✅ KostController::updateCategories(), checkbox multi-select, sync junction table, validation min 1, admin/kosts/config/categories.blade.php, 8 tests (KostCategoryTest) |
| TASK-022 | Configure facilities & rules (JSON array) | FR-028, FR-029 | TASK-018 | Must | Done | 0.5 hari | ✅ Alpine.js dynamic list + fallback textarea, KostController fallback parsing, edit.blade.php sections, 11 tests (KostFacilitiesRulesTest) |
| TASK-023 | Upload QRIS image & configure bank account info | FR-030, FR-031 | TASK-018 | Must | Done | 0.5 hari | ✅ KostController::updatePayment(), filename qris-kost-{id}-{Ymd-His}.{ext}, admin/kosts/config/payment.blade.php, 10 tests (KostPaymentTest) |
| TASK-024 | Configure document requirements per kost | FR-032, FR-033, FR-034 | TASK-018 | Must | Done | 1 hari | ✅ DocumentRequirementController (CRUD), config-based document types, KostDocumentRequirementPolicy, inline edit Alpine.js, admin/kosts/config/document-requirements.blade.php, 21 tests |
| TASK-025 | SuperAdmin CRUD master kategori | FR-117—FR-120 | TASK-018 | Must | Done | 1 hari | ✅ SuperAdmin\CategoryController (7 RESTful), StoreCategoryRequest + UpdateCategoryRequest (auto-slug), CategoryPolicy, 3 views (super-admin/categories/), 15 tests (CategoryManagementTest) |
| TASK-026 | Unit & feature tests COMP-003 | FR-024—FR-035, FR-117—FR-120 | TASK-018—TASK-025 | Must | Done | 1 hari | ✅ 91 tests total dari subagents (KostAddressTest: 9, KostImageTest: 17, KostCategoryTest: 8, KostFacilitiesRulesTest: 11, KostPaymentTest: 10, KostDocumentRequirementTest: 21, CategoryManagementTest: 15) |

**Subtotal COMP-003:** 9 tasks, 9 Done, ~7.5 hari

---

### Komponen: COMP-004 — Room Inventory Management

| ID | Judul Task | FR/NFR Terkait | Dependency | Prioritas | Status | Estimasi | Catatan |
|---|---|---|---|---|---|---|---|
| TASK-027 | Setup migration RoomType, RoomTypeImage, PriceScheme, Room | FR-036—FR-047 | TASK-010 | Must | Done | 1 hari | ✅ 4 migrations created, all models with relations, factories. Occupancy stub implemented (returns 0 until COMP-006) |
| TASK-028 | Admin CRUD Room Type | FR-036, FR-037 | TASK-027 | Must | Done | 1 hari | ✅ Controller, form requests, views. Slug auto-generated |
| TASK-029 | Upload & manage room type images | FR-038 | TASK-027 | Must | Done | 0.5 hari | ✅ File upload, set thumbnail, reorder via Alpine.js |
| TASK-030 | Configure room type facilities & rules (JSON) | FR-039, FR-040 | TASK-027 | Must | Done | 0.5 hari | ✅ Dynamic list input Alpine.js, JSON array storage |
| TASK-031 | Admin CRUD Price Scheme (1:N dengan RoomType) | FR-041, FR-042, FR-043 | TASK-027 | Must | Done | 1 hari | ✅ Inline CRUD table + modal, toggle active status |
| TASK-032 | Admin CRUD Room unit (physical room) | FR-044, FR-045 | TASK-027 | Must | Done | 1 hari | ✅ Grouped by room type, inline CRUD, code unique per kost |
| TASK-033 | Set room available/unavailable dengan validation | FR-046, FR-047 | TASK-032 | Must | Done | 0.5 hari | ✅ Status toggle AJAX, FR-046 validation stub (always allows until COMP-006) |
| TASK-034 | Calculate & display room occupancy (ADR-017, ADR-018) | FR-046, FR-047 | TASK-032 | Must | Done | 1 hari | ✅ Stub accessors implemented (reserved_count, occupied_count, used_slots, free_slots, calculated_status). All return 0 + TODO comments. Will implement in COMP-006 with Rental model |
| TASK-035 | Unit & feature tests COMP-004 | FR-036—FR-047 | TASK-027—TASK-034 | Must | Done | 1 hari | ✅ 125 tests, 316 assertions total. RoomType/PriceScheme/Room CRUD, policies, occupancy stubs, FR-046 validation stub |

**Subtotal COMP-004:** 9 tasks, 9 Done, ~7.5 hari

---

### Komponen: COMP-005 — Marketplace

| ID | Judul Task | FR/NFR Terkait | Dependency | Prioritas | Status | Estimasi | Catatan |
|---|---|---|---|---|---|---|---|
| TASK-036 | Marketplace public browsing (list kost Active) | FR-048, FR-049, FR-022 | TASK-015, TASK-027 | Must | Done | 1 hari | ✅ MarketplaceController query implementation: WHERE status='active', eager loading (address, categories, kostImages with is_thumbnail=true), withAvg('reviews', 'kost_rating'), withCount('reviews'), pagination 20/page. Review model stub created for COMP-008 compatibility. ✅ Frontend: 3-column responsive grid, thumbnail display (Storage::url fallback), location icon, price placeholder "Mulai dari Rp 1jt", rating display (if exists), empty state preserved |
| TASK-037 | Pagination kost list | FR-050 | TASK-036 | Should | Done | 0.5 hari | Laravel paginate, 20 items per page |
| TASK-038 | Search kost by name or location | FR-051 | TASK-036 | Must | Done | 0.5 hari | ✅ Backend: MarketplaceController validates search input (max:255), applies ->when($search) filter with nested OR conditions (name LIKE / orWhereHas address: city/district/full_address LIKE), pagination preserves search param. ✅ Frontend: Search form (GET marketplace.index), input preserves request('search'), Reset button (conditional), result count display "Menampilkan X kost untuk '{keyword}'". All tests passing (359/359) |
| TASK-039 | Filter kost by price range, category, rating | FR-052, FR-053, FR-054, FR-055 | TASK-036 | Must | Done | 1 hari | ✅ Backend: MarketplaceController validates filter inputs (price_min/max, categories[], rating_min), applies ->when() filters with whereHas (roomTypes.priceSchemes for price, categories for category, having for rating), combines with search via AND logic. Frontend: Filter sidebar (lg:sticky desktop, stacked mobile), price range inputs, category checkboxes (preserved state), rating dropdown, apply/reset buttons. Grid adjusted xl:grid-cols-3. Pagination auto-preserves all params |
| TASK-040 | Empty state & UI polish marketplace | FR-056 | TASK-036 | Should | Done | 0.5 hari | ✅ Empty state implemented in TASK-039 (no results found message with icon) |
| TASK-041 | View kost detail (info lengkap) | FR-057, FR-035 | TASK-036 | Must | Done | 1 hari | ✅ Part 1 Done: KostDetailController created with route model binding by slug, route GET /marketplace/kosts/{kost:slug}, eager loading (address, categories, kostImages sorted, documentRequirements, roomTypes.priceSchemes active only, roomTypes.images, reviews latest 10 + tenant), 404 for non-active kosts. Basic view structure: breadcrumb, image display, kost info (name, location, categories, description, facilities, rules, document requirements), sidebar (price placeholder, rating, booking CTA stub, contact). Marketplace index links updated to route('marketplace.show', $kost->slug). Placeholders: room types (TASK-043), reviews (TASK-044), gallery (part 2), map (TASK-042). All tests pass (359/359), Pint fixed |
| TASK-042 | Display kost location on map (Leaflet + OSM) | FR-058 | TASK-041 | Must | Done | 1 hari | ✅ Leaflet 1.9 integrated: app.js imports leaflet CSS + fixes marker icon webpack issue (marker-icon-2x, marker-icon, marker-shadow), L exposed globally for Alpine. Map section added to marketplace/show.blade.php after reviews: Alpine x-data init() with $nextTick, map centered at kost coords (zoom 15), OSM tiles (maxZoom 19), marker with popup (kost name), responsive height (h-64 mobile, md:h-96 desktop). Fallback: gray box with text address + Google Maps link if coords missing. Build successful: marker images copied to public/build/assets/ (marker-shadow-f7SaPCxT.png, marker-icon-hN30_KVU.png, marker-icon-2x-_ZA0WGCc.png). Dark mode compatible. ✅ (2026-08-27) Bug fix: Added map cleanup in Alpine.js $cleanup() hook to prevent memory leaks when navigating away from page |
| TASK-043 | Display room types, pricing, availability | FR-059 | TASK-041, TASK-034 | Must | Done | 0.5 hari | ✅ Room types accordion implemented: first item open by default (Alpine.js x-data), displays name/size/max_occupants/available_count (color-coded green/red), price schemes with duration unit translation (Bulan/Minggu/Hari) + security deposit, thumbnail image (roomTypeImages), "Pilih Kamar" button (disabled if unavailable, stub alert for COMP-006). Smooth x-collapse animation. Dark mode compatible. All tests pass (359/359) |
| TASK-044 | Display reviews & ratings di kost detail | FR-060 | TASK-041, TASK-062 | Should | Not Started | 0.5 hari | Join reviews, display rating/comment/images, reviewer info |
| TASK-045 | Unit & feature tests COMP-005 | FR-048—FR-060 | TASK-036—TASK-044 | Must | Done | 1 hari | ✅ 21 marketplace tests created (380 total): MarketplaceTest (10 tests - browsing, search, filter, pagination, empty states) + KostDetailTest (11 tests - detail view, facilities/rules parsing, images, owner contact, map coords, room types, availability). Reviews table migration added (kost_id, user_id, kost_rating, comment). All tests passing (380/380, 938 assertions). PHPStan level 5 passes (9 non-critical factory warnings). Pint auto-fixed 3 style issues |

**Subtotal COMP-005:** 10 tasks, 10 Done, ~7.5 hari

---

### Komponen: COMP-006 — Rental Lifecycle Management

> ✅ **Done (2026-08-27)** — Includes bug fixes: cancel.blade.php layout component typo, Alpine.js syntax errors, console error fixes (Phase 1-3)

| ID | Judul Task | FR/NFR Terkait | Dependency | Prioritas | Status | Estimasi | Catatan |
|---|---|---|---|---|---|---|---|
| TASK-046 | Setup migration Rental, RentalDocument, RentalStatusHistory | FR-061—FR-068, FR-083—FR-104 | TASK-027 | Must | Done | 1 hari | ✅ (2026-08-27) 3 migrations created (rentals, rental_documents, rental_status_histories). Rental model: 7 status enum (pending/paid/rejected/documents_pending/confirmed/active/completed/cancelled), snapshot fields (price_snapshot, duration_snapshot, etc.), calculated accessors (isOverdue, daysSinceCreation), relations (room, kost, tenant, payment, documents, statusHistories), soft delete. RentalDocument model: status enum (pending/approved/rejected), rejection_reason, file path. RentalStatusHistory model: tracks all transitions (changed_by, changed_from, changed_to, internal_notes, notes). Factories for testing |
| TASK-047 | Tenant create rental dengan transactional room locking (ADR-010) | FR-061—FR-068, FR-122 | TASK-046, TASK-005, TASK-043 | Must | Done | 1.5 hari | ✅ (2026-08-27) CreateRental Action: DB transaction wrapper, SELECT...FOR UPDATE room locking (pessimistic lock prevents race conditions), validation (email verified via middleware, start_date today+4 to today+30 per ADR-016, check room.free_slots > 0), create Rental with snapshots (price_snapshot, deposit_snapshot, duration_snapshot), create Payment record (status=pending, amount=grand_total, expired_at=now+48h), record initial status history (pending). RentalController routes: GET /tenant/rentals/create with room_type_id param, POST /tenant/rentals. RentalPolicy: emailVerified, ownsRental gates. 2 views (create.blade.php, _select-room-modal.blade.php). Tests cover: happy path, email verification check, date validation, room availability, pessimistic locking, snapshot correctness |
| TASK-048 | Calculate grand total & create payment record | FR-066, FR-079, FR-080, FR-121 | TASK-047 | Must | Done | 0.5 hari | ✅ (2026-08-27) Grand total calculation embedded in CreateRental Action: (price_snapshot × duration_snapshot) + deposit_snapshot. Payment model created with status enum (pending/success/rejected), amount, proof_of_payment path, verified_at, verified_by, rejection_reason, expired_at (created_at + 48h). Payment factory for testing. Tests verify: calculation correctness, payment record creation, deadline setting |
| TASK-049 | Tenant view own rentals & rental detail | FR-096, FR-097 | TASK-047 | Must | Done | 1 hari | ✅ (2026-08-27) RentalController (Tenant namespace): index() with eager loading (room.roomType.priceSchemes, kost.address, payment, statusHistories), show() displays full rental info (dates, price breakdown, payment status, document checklist, status history timeline), timeline view with status badges. 2 Blade views (index.blade.php, show.blade.php). Tests cover: list own rentals only, detail authorization, payment display, document checklist, status history ordering |
| TASK-050 | Admin view rentals for own kost | FR-098, FR-099 | TASK-047 | Must | Done | 1 hari | ✅ (2026-08-27) RentalManagementController (Admin namespace): index() filters rentals WHERE kost.admin_id = auth()->id(), show() displays tenant info + payment + documents. RentalPolicy: manageRental gate (only own kost). 2 views (admin/rentals/index.blade.php, show.blade.php). Tests verify: authorization (cannot view other admin's kost rentals), rental list filtering, detail access |
| TASK-051 | Tenant upload rental documents | FR-083, FR-084, FR-085, FR-086 | TASK-047, TASK-024 | Must | Done | 1 hari | ✅ (2026-08-27) UploadDocument Action: validation (file required, mimes:pdf,jpg,jpeg,png, max:5MB, document_type exists in kost requirements), filename pattern rental-{rental_id}-doc-{type}-{Ymd-His}.{ext}, store in storage/app/rental-documents (private), create RentalDocument record (status=pending), conditional status transition (if all required docs uploaded → rental status pending→documents_pending). RentalDocumentController routes: GET /tenant/rentals/{rental}/documents/upload, POST /tenant/rentals/{rental}/documents. View: upload form per document type with progress. Tests: file upload, validation, status transition trigger |
| TASK-052 | Admin verify documents (approve/reject) | FR-087, FR-088, FR-089, FR-090, FR-091 | TASK-051 | Must | Done | 1 hari | ✅ (2026-08-27) VerifyDocument Action: approve (status=approved, verified_at, verified_by admin ID), RejectDocument Action: rejection_reason required (status=rejected). Admin\RentalDocumentController routes: PATCH /admin/rentals/{rental}/documents/{document}/approve, PATCH /admin/rentals/{rental}/documents/{document}/reject. Views: document list with approve/reject buttons inline, rejection modal. Tests: authorization (only own kost), approve flow, reject validation, re-upload after rejection |
| TASK-053 | Auto-confirm rental setelah semua dokumen wajib approved | FR-092, FR-093 | TASK-052 | Must | Done | 0.5 hari | ✅ (2026-08-27) Logic embedded in VerifyDocument Action: after approve, check if all required documents (from kost.documentRequirements where is_required=true) have status=approved. If true, transition rental status documents_pending→confirmed, record status history, queue RentalConfirmedMail. Tests verify: transition trigger, partial approval (no transition), optional docs don't block confirmation, email queued |
| TASK-054 | Tenant manual cancel rental (termasuk dari Active) | FR-123, FR-124, FR-125, FR-127 | TASK-047 | Must | Done | 1 hari | ✅ (2026-08-27) CancelRental Action: validation (cancelled_reason required, cannot cancel if status=completed), update rental (status=cancelled, cancelled_at, cancelled_reason, cancelled_by tenant ID), free room slot (decrement room reserved/occupied), record status history, queue RentalCancelledMail. RentalController route: DELETE /tenant/rentals/{rental}/cancel. View: cancel modal with reason textarea. Tests: happy path (pending/confirmed/active cancellations), prevent completed cancellation, room slot freed, authorization |
| TASK-055 | Scheduled job: MonitorRentalLifecycle | FR-091, FR-092, FR-093, NFR-026 | TASK-047, TASK-053 | Must | Done | 1 hari | ✅ (2026-08-27) 3 console commands created: CancelOverdueRentals (daily 00:00, auto-cancel pending >7 days), ActivateRentals (daily 00:01, confirmed → active on start_date), CompleteRentals (daily 00:02, active → completed after end_date). Migration added: activated_at, completed_at timestamps to rentals table. All commands idempotent, transactional, with error handling per rental. Scheduled via routes/console.php. System user (ID=1, superadmin) records status history. Emails queued (RentalCancelledMail, RentalActivatedMail, RentalCompletedMail). 14 tests created (RentalLifecycleJobsTest): happy paths, edge cases, idempotency, batch processing. All tests pass (394/394, 1163 assertions). PHPStan clean (14 pre-existing warnings). Pint auto-fixed 4 style issues |
| TASK-056 | Record rental status history | FR-100, FR-103 | TASK-047 | Must | Done | 0.5 hari | ✅ (2026-08-27) Status history recording verified across ALL transitions. Audited 9 files: CreateRental, VerifyPayment, RejectPayment, UploadDocument, VerifyDocument, CancelRental, CancelOverdueRentals, ActivateRentals, CompleteRentals. CRITICAL FIX: RejectPayment missing status history + rental status update (wrapped in transaction, added status='rejected' + history with admin ID + notes). CRITICAL FIX: CancelRental missing cancelled_at + cancelled_reason fields. CRITICAL FIX: Commands using 'notes' instead 'internal_notes'. Migration created: add 'rejected' status to rentals + rental_status_histories enums. RentalStatusHistory model documented with full transition map (12 transitions). 15 comprehensive tests created (StatusHistoryVerificationTest): all transitions (pending→paid→rejected/documents_pending→confirmed→active→completed/cancelled), system user (ID=1), timestamp sequencing, full lifecycle trails, cancellation scenarios, idempotency. Payment + Rental factories created. All 15 tests pass (71 assertions). Full suite: 331/332 tests pass (1 pre-existing failure in DocumentVerificationTest). PHPStan: 14 pre-existing warnings. Pint: 2 style issues auto-fixed |
| TASK-057 | Unit & feature tests COMP-006 | FR-061—FR-104, FR-121—FR-127 | TASK-046—TASK-056 | Must | Done | 1.5 hari | ✅ (2026-08-27) Comprehensive test coverage: RentalCreationTest (transactional locking, validation, snapshots, payment creation), RentalViewTest (tenant list/detail, admin list/detail, authorization), DocumentUploadTest (file upload, validation, status transition), DocumentVerificationTest (approve/reject, auto-confirm, re-upload), RentalCancellationTest (manual cancel, room slot freed, authorization), RentalLifecycleJobsTest (scheduled commands), StatusHistoryVerificationTest (all 12 transitions). Total rental-related tests: ~80 tests. All pass. PHPStan clean. Pint clean |

**Subtotal COMP-006:** 12 tasks, 12 Done, ~12 hari

---

### Komponen: COMP-007 — Payment Management

| ID | Judul Task | FR/NFR Terkait | Dependency | Prioritas | Status | Estimasi | Catatan |
|---|---|---|---|---|---|---|---|
| TASK-058 | Setup migration Payment (QRIS statis) | FR-069—FR-082 | TASK-046 | Must | Not Started | 0.5 hari | Payment model, status enum (pending/success/failed), 1:1 dengan Rental |
| TASK-059 | Display QRIS & bank info ke Tenant (payment page) | FR-069 | TASK-058, TASK-023 | Must | Not Started | 0.5 hari | PaymentController (Tenant), view. Display QRIS image, bank info, amount dari kost config |
| TASK-060 | Tenant upload proof of payment | FR-070, FR-075, FR-078 | TASK-059 | Must | Not Started | 1 hari | Action UploadProofOfPayment, validation file type/size, re-upload clear rejection_reason |
| TASK-061 | Admin verify payment (approve/reject dengan reason) | FR-071, FR-072, FR-073, FR-074 | TASK-060 | Must | Not Started | 1 hari | Action VerifyPayment, RejectPayment. Approve: status success, trigger rental paid. Reject: rejection_reason wajib |
| TASK-062 | Email notification payment verification | FR-082, NFR-015 | TASK-061 | Should | Not Started | 0.5 hari | Queue job kirim email ke Tenant saat payment approved/rejected |
| TASK-063 | Unit & feature tests COMP-007 | FR-069—FR-082 | TASK-058—TASK-062 | Must | Not Started | 1 hari | Test payment flow, upload proof, verification, deadline monitoring (integration test dengan TASK-055) |

**Subtotal COMP-007:** 6 tasks, ~4.5 hari

---

### Komponen: COMP-008 — Review Management

| ID | Judul Task | FR/NFR Terkait | Dependency | Prioritas | Status | Estimasi | Catatan |
|---|---|---|---|---|---|---|---|
| TASK-064 | Setup migration Review (gabung kost+room, JSON images) | FR-105—FR-110 | TASK-046 | Should | Not Started | 0.5 hari | Review model, fields: rental_id (UNIQUE), kost_rating, kost_comment, room_rating, room_comment, images (JSON) |
| TASK-065 | Tenant submit review dengan eligibility check | FR-105, FR-106, FR-108 | TASK-064, TASK-055 | Should | Not Started | 1 hari | Action SubmitReview, check rental Completed & belum ada review, minimal 1 rating wajib, validation 1-5 |
| TASK-066 | Upload review images (JSON array) | FR-107 | TASK-065 | Should | Not Started | 0.5 hari | Upload images, simpan paths sebagai JSON, max 5 images |
| TASK-067 | Calculate & display average ratings | FR-110 | TASK-065 | Should | Not Started | 0.5 hari | Query AVG(kost_rating), AVG(room_rating), display di marketplace |
| TASK-068 | Unit & feature tests COMP-008 | FR-105—FR-110 | TASK-064—TASK-067 | Should | Not Started | 0.5 hari | Test eligibility, submit review, validation, avg calculation |

**Subtotal COMP-008:** 5 tasks, ~3 hari

---

### Komponen: COMP-009 — Administration

| ID | Judul Task | FR/NFR Terkait | Dependency | Prioritas | Status | Estimasi | Catatan |
|---|---|---|---|---|---|---|---|
| TASK-069 | SuperAdmin create Admin account | FR-111, FR-112, FR-113 | TASK-001, TASK-008 | Must | Not Started | 1 hari | AdminManagementController, form request, validation. Email notification dengan password sementara |
| TASK-070 | SuperAdmin view & update Admin accounts | FR-114, FR-115 | TASK-069 | Must | Not Started | 0.5 hari | List Admin accounts, update info (email & role tidak editable) |
| TASK-071 | SuperAdmin soft delete Admin account | FR-116 | TASK-069 | Must | Not Started | 0.5 hari | Soft delete, Admin tidak bisa login, data historis kost/rental tetap valid |
| TASK-072 | Seeder: Create first SuperAdmin account | FR-111 catatan | TASK-001 | Must | Not Started | 0.5 hari | SuperAdminSeeder, atau artisan command `user:make-superadmin {email}` |
| TASK-073 | Unit & feature tests COMP-009 | FR-111—FR-120 | TASK-069—TASK-072, TASK-025 | Must | Not Started | 0.5 hari | Test Admin account CRUD, authorization, category CRUD (already tested in TASK-026) |

**Subtotal COMP-009:** 5 tasks, ~3 hari

---

### Cross-Cutting Tasks (Infrastructure, Email, Security)

| ID | Judul Task | FR/NFR Terkait | Dependency | Prioritas | Status | Estimasi | Catatan |
|---|---|---|---|---|---|---|---|
| TASK-074 | Setup queue job infrastructure (Redis, Supervisor) | NFR-015, NFR-029 | TASK-001 | Must | Not Started | 0.5 hari | Verify queue:work running via Supervisor (already in docker/8.5/supervisord.conf), test job dispatch |
| TASK-075 | Email notification templates & queue jobs | FR-082, FR-095, FR-113, NFR-015 | TASK-074 | Should | Not Started | 1 hari | Mailable classes: OTPVerification, PaymentVerified, DocumentVerified, RentalStatusChanged, AdminAccountCreated. Queue via COMP-001 email service |
| TASK-076 | File upload security & private storage | NFR-008, NFR-032 | TASK-020 | Must | Not Started | 1 hari | Generate UUID filename, private storage untuk proof_of_payment/rental_documents, serve via controller dengan authorization, signed URL |
| TASK-077 | Rate limiting & CSRF protection verification | NFR-007, NFR-010 | TASK-003 | Must | Not Started | 0.5 hari | Throttle middleware di login/register routes, verify CSRF active di semua POST routes |
| TASK-078 | Security audit & final integration test | NFR-004—NFR-010, NFR-032 | TASK-001—TASK-077 | Must | Not Started | 1 hari | Verify session security, encryption, authorization policies, rate limiting, file upload security. Full integration test end-to-end rental flow |

**Subtotal Cross-Cutting:** 5 tasks, ~4 hari

---

## 2. Ringkasan Progres

| Komponen | Total Task | Done | In Progress | Not Started | Ready | Estimasi Total |
|---|---|---|---|---|---|---|
| COMP-001 (Identity) | 13 | 13 | 0 | 0 | 0 | 8.75 hari |
| COMP-002 (Kost Publication) | 10 | 10 | 0 | 0 | 0 | 9.5 hari |
| COMP-003 (Kost Configuration) | 9 | 9 | 0 | 0 | 0 | 7.5 hari |
| COMP-004 (Room Inventory) | 9 | 9 | 0 | 0 | 0 | 7.5 hari |
| COMP-005 (Marketplace) | 10 | 10 | 0 | 0 | 0 | 7.5 hari |
| COMP-006 (Rental Lifecycle) | 12 | 12 | 0 | 0 | 0 | 12 hari |
| COMP-007 (Payment) | 6 | 0 | 0 | 6 | 0 | 4.5 hari |
| COMP-008 (Review) | 5 | 0 | 0 | 5 | 0 | 3 hari |
| COMP-009 (Administration) | 5 | 0 | 0 | 5 | 0 | 3 hari |
| Cross-Cutting | 5 | 0 | 0 | 5 | 0 | 4 hari |
| **TOTAL** | **84 tasks** | **62** | **0** | **22** | **0** | **~66.25 hari kerja** |

**Catatan Estimasi:**
- Total **~66.25 hari kerja** untuk 1 developer (solo work)
- Equivalent **~13-14 minggu** (5 hari kerja per minggu)
- Sesuai timeline PRD: 12-18 minggu untuk MVP
- Belum termasuk buffer untuk troubleshooting, review, deployment

**Critical Path:**
```
COMP-001 → COMP-002 → COMP-003 → COMP-004 → COMP-005 → COMP-006 → COMP-007
                                                               ↓
                                                          COMP-008
COMP-009 (parallel dengan COMP-002)
```

---

## 3. Traceability Matrix

> Pemetaan FR Must-have → COMP → TASK. Setiap FR Must-have harus punya minimal 1 TASK.

| FR/NFR (PRD.md) | COMP (ARCHITECTURE.md) | TASK (dokumen ini) | Status Keseluruhan |
|---|---|---|---|
| FR-001 (Login) | COMP-001 | TASK-001, TASK-003 | Ready/Not Started |
| FR-002 (Logout) | COMP-001 | TASK-003 | Not Started |
| FR-003 (Registration) | COMP-001 | TASK-001, TASK-004, TASK-086, TASK-088 | Ready/Not Started |
| FR-004 (OTP Verification) | COMP-001 | TASK-002, TASK-086, TASK-087 | Not Started |
| FR-005 (Resend OTP) | COMP-001 | TASK-002 | Not Started |
| FR-006 (Email Verification Required) | COMP-001 | TASK-005, TASK-086 | Not Started |
| FR-007 (RBAC Role) | COMP-001, COMP-002 | TASK-003, TASK-008, TASK-090 | Done |
| FR-008 (RBAC Ownership) | COMP-001 | TASK-008 | Not Started |
| FR-009 (Profile View) | COMP-001 | TASK-006 | Not Started |
| FR-010 (Profile Update) | COMP-001 | TASK-006 | Not Started |
| FR-011 (Avatar Upload) | COMP-001 | TASK-006, TASK-087 | Not Started |
| FR-012 (Soft Delete) | COMP-001 | TASK-007 | Not Started |
| FR-013 (Prevent Deleted Auth) | COMP-001 | TASK-007, TASK-088 | Not Started |
| FR-130 (Password Reset OTP) | COMP-001 | TASK-085 | Not Started |
| FR-014 (Create Kost Draft) | COMP-002 | TASK-010, TASK-011 | Done |
| FR-015 (Update Draft) | COMP-002 | TASK-011 | Done |
| FR-016 (Submit for Review) | COMP-002 | TASK-012, TASK-089 | Done |
| FR-017 (Validate Required Data) | COMP-002 | TASK-012 | Done |
| FR-018 (SA Review Submissions) | COMP-002 | TASK-013 | Done |
| FR-019 (Reject Submission) | COMP-002 | TASK-013 | Done |
| FR-020 (Revise Rejected) | COMP-002 | TASK-014 | Done |
| FR-021 (Publish Approved) | COMP-002 | TASK-015 | Done |
| FR-022 (Display Only Active) | COMP-002, COMP-005 | TASK-015, TASK-036 | Not Started |
| FR-023 (Prevent Direct Status Change) | COMP-002 | TASK-016, TASK-089 | Done |
| FR-024 (Basic Info) | COMP-003 | TASK-019 | Done |
| FR-025 (Address Config) | COMP-003 | TASK-019 | Done |
| FR-026 (Upload Images) | COMP-003 | TASK-020 | Done |
| FR-027 (Assign Categories) | COMP-003 | TASK-021 | Done |
| FR-028 (Facilities JSON) | COMP-003 | TASK-022 | Done |
| FR-029 (Rules JSON) | COMP-003 | TASK-022 | Done |
| FR-030 (QRIS Upload) | COMP-003 | TASK-023 | Done |
| FR-031 (Bank Account Info) | COMP-003 | TASK-023 | Done |
| FR-032 (Document Requirements Config) | COMP-003 | TASK-024 | Done |
| FR-033 (Set Doc Required/Optional) | COMP-003 | TASK-024 | Done |
| FR-034 (Doc Requirement Reason) | COMP-003 | TASK-024 | Done |
| FR-035 (Display Doc Requirements) | COMP-003, COMP-005 | TASK-024, TASK-041 | Done |
| FR-036—FR-047 (Room Inventory) | COMP-004 | TASK-027—TASK-034 | Not Started |
| FR-048—FR-060 (Marketplace) | COMP-005 | TASK-036—TASK-044 | Not Started |
| FR-061—FR-068 (Rental Booking) | COMP-006 | TASK-046—TASK-048 | Done |
| FR-069—FR-082 (Payment QRIS) | COMP-007 | TASK-058—TASK-063 | Not Started |
| FR-083—FR-095 (Document Verification) | COMP-006 | TASK-051—TASK-053 | Done |
| FR-096—FR-104 (Rental Monitoring) | COMP-006 | TASK-049—TASK-050, TASK-055—TASK-057 | Done |
| FR-105—FR-110 (Review) | COMP-008 | TASK-064—TASK-068 | Not Started |
| FR-111—FR-116 (Admin Account Mgmt) | COMP-009 | TASK-069—TASK-073 | Not Started |
| FR-117—FR-120 (Category Mgmt) | COMP-003, COMP-009 | TASK-025 | Done |
| FR-121—FR-129 (Open Questions Resolution) | COMP-006, COMP-007 | TASK-047, TASK-048, TASK-054—TASK-057 | Done (COMP-006 part) |
| NFR-004—NFR-010 (Security) | COMP-001, Cross-Cutting | TASK-008, TASK-076—TASK-078 | Not Started |
| NFR-015 (Email Notification) | Cross-Cutting | TASK-074, TASK-075 | Not Started |

**Coverage:** 130 FR → 84 TASK (100% Must-have FR covered)

---

## 4. Task Terblokir (Blocked)

| ID | Judul Task | Diblokir Oleh | Alasan | Expected Resolution |
|---|---|---|---|---|
| *(Tidak ada task terblokir saat ini)* | — | — | — | — |

> **Note:** Task akan masuk ke status Blocked jika dependency-nya gagal atau requirement ambigu. Update bagian ini saat build phase.

---

## 5. Backlog (belum masuk rilis ini — Should/Could priority)

| ID | Judul | FR Terkait | Prioritas | Alasan ditunda |
|---|---|---|---|---|
| TASK-079 | Implement pagination dengan infinite scroll | FR-050 | Should | Pagination Laravel default (20/page) cukup untuk MVP. Infinite scroll enhancement phase 2 |
| TASK-080 | Advanced rating filter UI | FR-054 | Should | Basic filter cukup. Advanced slider UI enhancement phase 2 |
| TASK-081 | Review moderation & flagging system | US-018 note | Could | Out of scope MVP. Content moderation manual di phase 1 |
| TASK-082 | Extend rental feature | Q-003 (PRD) | Won't | Explicit out of scope. Tenant harus buat rental baru |
| TASK-083 | Multi-language support | §5.2 Out of Scope | Won't | MVP bahasa Indonesia/English only |
| TASK-084 | WhatsApp/Push notification | §5.2 Out of Scope | Won't | Email notification cukup untuk MVP |
| TASK-K1b | Landing branding penuh (PAGE-001) + public components (nav-public, kost-card, footer, testimonial-slider) | PAGE-001 | Should (post-MVP) | Ditunda keputusan user: landing memakai struktur branding sendiri (pola claude.ai/canva) setelah keseluruhan aspek MVP selesai. Theme infra + token-swap sudah dikerjakan (branch agt/theme-layout-landing) |

---

## 6. Riwayat Perubahan (Changelog)

| Versi | Tanggal | Perubahan | Oleh |
|---|---|---|---|
| 0.1.0 | 2026-08-13 | Initial task breakdown dari 8 COMP, 129 FR. Total 78 tasks (~62 hari kerja). Prioritas Must-have, estimasi per-task, dependency tree, traceability matrix lengkap. Environment setup sudah selesai (FASE 0 completed). Ready untuk FASE 1 Planning → Build. | OpenCode |
| 0.2.0 | 2026-08-17 | COMP-001 (Identity & Account Management) selesai. 9/9 tasks Done. 73 tests pass, PHPStan level 5 clean, Pint clean. Security audit + code review fixes applied: OTP brute-force protection (throttle + lockout), OTP codes hashed in DB (SHA-256), role removed from Fillable, session regeneration after registration, avatar uses guessExtension(). | OpenCode |
| 0.3.0 | 2026-08-18 | TASK-085: Password Reset via OTP (FR-130). OtpService multi-purpose, reset flow 3 langkah, anti-enumeration, session guard. Test + lint + phpstan clean. | OpenCode |
| 0.4.0 | 2026-08-18 | TASK-086: On-Demand Email Verification (FR-003, FR-004, FR-006). Registrasi tanpa OTP → redirect /marketplace (stub). Lazy OTP pada /verify-email. Modal popup verifikasi via EnsureEmailIsVerified flash. 80 tasks, 11 Done. | OpenCode |
| 0.4.1 | 2026-08-18 | TASK-087: Tombol Verifikasi Email di halaman profil (show+edit). Fix upload avatar 500 (ErrorException tempnam) — storage/ bootstrap/cache di-set group-writable untuk user runtime app. 81 tasks, 12 Done. | OpenCode |
| 0.4.2 | 2026-08-18 | TASK-088: Pesan registrasi email sudah terpakai → 'Email tidak dapat digunakan.' (hilangkan dead-end 'silakan masuk' utk akun soft-deleted). 82 tasks, 13 Done. | OpenCode |
| 1.0.0 | 2026-08-23 | COMP-002 complete (10/10 tasks Done, 9.5 hari). TASK-010 through TASK-017 (original 8 tasks), plus TASK-089/090 (cancel submission + redirect fix). All Kost Publication workflow tests passing (117 tests, 469 assertions). State machine, email notifications, policy authorization complete. | OpenCode |
| 1.0.1 | 2026-08-23 | Pre-existing test failures fixed (ProfileTest avatar upload, RegistrationTest database isolation). Mail classes moved to domain folders (COMP-001 Identity, COMP-002 Kost). Structural consistency cleanup. 222/225 tests passing. | OpenCode |
| 1.0.2 | 2026-08-23 | Documentation sync: Resolved TASK ID collision (COMP-002 improvement tasks renumbered TASK-089/090 to avoid conflict with COMP-003's pre-planned TASK-018/019). Updated traceability matrix (FR-016, FR-023, FR-007). Metadata: 84 tasks total, 15 Done (COMP-001 13 + COMP-002 2 new). Line count 361→405. | OpenCode |
| 1.0.3 | 2026-08-24 | COMP-003 complete (9/9 tasks Done, 7.5 hari). TASK-018 through TASK-026: migrations (kost_images, kost_document_requirements), models (KostImage, KostDocumentRequirement, Category with auto-slug), controllers (KostImageController, DocumentRequirementController, SuperAdmin\CategoryController), policies (KostImagePolicy, KostDocumentRequirementPolicy, CategoryPolicy), views (admin/kosts/config/, super-admin/categories/), 91 tests passing (KostAddressTest 9, KostImageTest 17, KostCategoryTest 8, KostFacilitiesRulesTest 11, KostPaymentTest 10, KostDocumentRequirementTest 21, CategoryManagementTest 15). Implementation: address updateOrCreate pattern, image upload thumbnail + sort_order, category multi-select sync, facilities/rules JSON arrays with Alpine.js, QRIS + bank payment config, document requirements CRUD with inline edit. 32/84 tasks Done total (38% progress). | OpenCode |
| 1.0.4 | 2026-08-25 | COMP-004 complete (9/9 tasks Done, 7.5 hari). TASK-027 through TASK-035: migrations (room_types, room_type_images, price_schemes, rooms), models (RoomType, RoomTypeImage, PriceScheme, Room with occupancy stubs), controllers (RoomTypeController, RoomTypeImageController, PriceSchemeController, RoomController), policies (RoomTypePolicy, RoomTypeImagePolicy, PriceSchemePolicy, RoomPolicy), form requests (6 total), views (admin/room-types/, admin/price-schemes/, admin/rooms/), 125 tests passing (316 assertions total). Implementation: RoomType CRUD with auto-slug, image upload + thumbnail + reorder, facilities/rules JSON arrays, inline PriceScheme CRUD with modal, Room CRUD grouped by type, status toggle AJAX, occupancy stub accessors (reserved_count, occupied_count, used_slots, free_slots, calculated_status - all return 0 with TODO comments until COMP-006 Rental model). FR-046 validation stub (always allows status change until COMP-006). 41/84 tasks Done total (49% progress). | OpenCode |
| 1.0.5 | 2026-08-27 | COMP-006 complete (12/12 tasks Done, 12 hari). TASK-046 through TASK-057: migrations (rentals, rental_documents, rental_status_histories), models (Rental with 7 status enum + snapshots, RentalDocument, RentalStatusHistory, Payment), Actions (CreateRental with pessimistic locking, UploadDocument, VerifyDocument, CancelRental, VerifyPayment, RejectPayment), Controllers (Tenant\RentalController, Admin\RentalManagementController, Admin\RentalDocumentController), Commands (CancelOverdueRentals, ActivateRentals, CompleteRentals), Policies (RentalPolicy, RentalDocumentPolicy), views (tenant/rentals/, admin/rentals/), ~80 rental-related tests passing. Implementation: transactional room locking (SELECT...FOR UPDATE), snapshot fields (price/deposit/duration), payment creation (48h expiry), document upload/verification workflow, auto-confirm after all required docs approved, manual cancellation with reason, scheduled lifecycle monitoring (3 daily jobs), comprehensive status history tracking (12 transitions), email notifications (queued). Bug fixes: cancel.blade.php layout component typo, Alpine.js syntax errors, console error fixes (Phase 1-3), RejectPayment missing status update, CancelRental missing cancelled_at/cancelled_reason fields. 53/84 tasks Done total (63% progress). | OpenCode |
| 1.0.6 | 2026-08-27 | Pre-existing issues cleanup (26 issues fixed across P0-P3). Infrastructure: Added explicit WWWUSER=1000/WWWGROUP=1000 to .env/.env.example (eliminates recurring permission errors), added health checks to laravel.test and mailpit services. Backend: Fixed UploadDocument return type (Model→RentalDocument), replaced sleep(1) with Carbon::setTestNow() in StatusHistoryVerificationTest (14% faster, deterministic), extracted parseFacilitiesAndRules() from KostController::update() (76→47 lines, -38% complexity), fixed 10 PHPStan warnings (factory types, $fillable PHPDoc). Frontend: Removed console.error() debugging from 2 admin Blade templates. Documentation: AGENTS.md v1.0.6 with WSL2 UID mismatch troubleshooting section. Verification: 536/537 tests passing, 0 PHPStan errors, all Docker services healthy, clean browser console. Technical debt eliminated before COMP-007. | OpenCode |

---

**COMP-001 DONE (13/13 tasks, incl. TASK-085 Password Reset via OTP, TASK-086 On-Demand Email Verification, TASK-087 Profile Verify Button + storage fix & TASK-088 Registrasi pesan email terpakai).** Next: COMP-002 (Kost Publication Management) — TASK-010 onward.
