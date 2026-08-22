# TODO.md — Task Board & Traceability Matrix

> Single Source of Truth untuk PEKERJAAN KONKRET. Menggantikan Project Plan/backlog tool eksternal.
> Setiap `TASK-xxx` WAJIB merujuk ke `FR-xxx`/`NFR-xxx` (`PRD.md`) dan `COMP-xxx` (`ARCHITECTURE.md`) — tidak boleh ada task "melayang" tanpa alasan requirement.

| Field | Value |
|---|---|
| Nama Proyek | SewaKost — Web Marketplace Kost Management & Rental System |
| Versi Dokumen | `1.0.1` |
| Terakhir Diperbarui | `2026-08-18` |

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
| `ARCHITECTURE.md` | 1,606 | ✅ Complete | 8 COMP, 21 ADR, data model, routes |
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

> Breakdown task per `COMP-xxx` untuk eksekusi sistematis. Total: **82 tasks** dari **130 FR** (Must-have prioritized).
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
| TASK-010 | Setup migration & model Kost (status lifecycle) | FR-014—FR-023 | TASK-001 | Must | Not Started | 0.5 hari | Kost model, status enum (draft/pending_review/approved/active/rejected), SoftDeletes |
| TASK-011 | Admin create & update Kost Draft | FR-014, FR-015 | TASK-010, TASK-008 | Must | Not Started | 1 hari | Controller, form request, routes, views. Policy: hanya Admin owner |
| TASK-012 | Action: SubmitKostForReview dengan validation data wajib | FR-016, FR-017 | TASK-011 | Must | Not Started | 1 hari | Action class, validasi nama/alamat/kategori/minimal 1 room type. State: draft → pending_review |
| TASK-013 | SuperAdmin review & approve/reject submission | FR-018, FR-019 | TASK-012 | Must | Not Started | 1 hari | Action ApproveKost, RejectKost. Controller, views. Rejection reason wajib |
| TASK-014 | Admin revise rejected kost | FR-020 | TASK-013 | Must | Not Started | 0.5 hari | Update kost → status kembali draft, clear rejected_reason |
| TASK-015 | Admin publish approved kost | FR-021 | TASK-013 | Must | Not Started | 0.5 hari | Action PublishKost. Status: approved → active, set published_at |
| TASK-016 | Prevent direct status change (enforce workflow) | FR-023 | TASK-012—TASK-015 | Must | Not Started | 0.5 hari | Validation di Action classes, disable manual status field di form |
| TASK-017 | Unit & feature tests COMP-002 | FR-014—FR-023 | TASK-010—TASK-016 | Must | Not Started | 1 hari | Test state machine transitions, validation, authorization |

**Subtotal COMP-002:** 8 tasks, ~6.5 hari

---

### Komponen: COMP-003 — Kost Configuration Management

| ID | Judul Task | FR/NFR Terkait | Dependency | Prioritas | Status | Estimasi | Catatan |
|---|---|---|---|---|---|---|---|
| TASK-018 | Setup migration Address, KostImage, Category, KostDocumentRequirement | FR-024—FR-035 | TASK-010 | Must | Not Started | 1 hari | 4 migrations, models, relasi 1:1 (Address), 1:N (KostImage), M:N (Category) |
| TASK-019 | Configure kost basic info & address | FR-024, FR-025 | TASK-018, TASK-011 | Must | Not Started | 1 hari | Form untuk nama, slug, deskripsi, contact, alamat lengkap, lat/long |
| TASK-020 | Upload & manage kost images (thumbnail + galeri) | FR-026, NFR-008 | TASK-018 | Must | Not Started | 1 hari | File upload validation, set thumbnail, sort order, generated filename |
| TASK-021 | Assign kost categories (from master) | FR-027 | TASK-018, TASK-030 | Must | Not Started | 0.5 hari | Junction table category_kost, multi-select di form |
| TASK-022 | Configure facilities & rules (JSON array) | FR-028, FR-029 | TASK-018 | Must | Not Started | 0.5 hari | Dynamic list input UI, simpan sebagai JSON, cast ['facilities' => 'array'] |
| TASK-023 | Upload QRIS image & configure bank account info | FR-030, FR-031 | TASK-018 | Must | Not Started | 0.5 hari | File upload QRIS, input bank_name/account_number/account_holder_name |
| TASK-024 | Configure document requirements per kost | FR-032, FR-033, FR-034 | TASK-018 | Must | Not Started | 1 hari | CRUD kost_document_requirements, set required/optional, reason |
| TASK-025 | SuperAdmin CRUD master kategori | FR-117—FR-120 | TASK-018 | Must | Not Started | 1 hari | CategoryController (SuperAdmin), views, prevent Admin access |
| TASK-026 | Unit & feature tests COMP-003 | FR-024—FR-035, FR-117—FR-120 | TASK-018—TASK-025 | Must | Not Started | 1 hari | Test config CRUD, file upload, JSON casting, authorization |

**Subtotal COMP-003:** 9 tasks, ~7.5 hari

---

### Komponen: COMP-004 — Room Inventory Management

| ID | Judul Task | FR/NFR Terkait | Dependency | Prioritas | Status | Estimasi | Catatan |
|---|---|---|---|---|---|---|---|
| TASK-027 | Setup migration RoomType, RoomTypeImage, PriceScheme, Room | FR-036—FR-047 | TASK-010 | Must | Not Started | 1 hari | 4 migrations, price_scheme 1:N dengan room_type, room status enum (available/unavailable) |
| TASK-028 | Admin CRUD Room Type | FR-036, FR-037 | TASK-027 | Must | Not Started | 1 hari | Controller, form request, views. Fields: name, slug, description, room_size, max_occupants, security_deposit |
| TASK-029 | Upload & manage room type images | FR-038 | TASK-027 | Must | Not Started | 0.5 hari | File upload, set thumbnail, same pattern dengan kost images |
| TASK-030 | Configure room type facilities & rules (JSON) | FR-039, FR-040 | TASK-027 | Must | Not Started | 0.5 hari | Dynamic list input, JSON array, same pattern dengan kost |
| TASK-031 | Admin CRUD Price Scheme (1:N dengan RoomType) | FR-041, FR-042, FR-043 | TASK-027 | Must | Not Started | 1 hari | Controller, form request. Fields: price, duration_value, duration_unit, is_active |
| TASK-032 | Admin CRUD Room unit (physical room) | FR-044, FR-045 | TASK-027 | Must | Not Started | 1 hari | Room code unique per kost, pilih room_type, status default available |
| TASK-033 | Set room available/unavailable dengan validation | FR-046, FR-047 | TASK-032 | Must | Not Started | 0.5 hari | Validation: cek tidak ada rental pending/paid/confirmed/active sebelum set unavailable |
| TASK-034 | Calculate & display room occupancy (ADR-017, ADR-018) | FR-046, FR-047 | TASK-032 | Must | Not Started | 1 hari | Real-time calculation reserved/occupied/free_slots dari rentals, display di admin dashboard |
| TASK-035 | Unit & feature tests COMP-004 | FR-036—FR-047 | TASK-027—TASK-034 | Must | Not Started | 1 hari | Test CRUD, occupancy calculation, validation room status |

**Subtotal COMP-004:** 9 tasks, ~7.5 hari

---

### Komponen: COMP-005 — Marketplace

| ID | Judul Task | FR/NFR Terkait | Dependency | Prioritas | Status | Estimasi | Catatan |
|---|---|---|---|---|---|---|---|
| TASK-036 | Marketplace public browsing (list kost Active) | FR-048, FR-049, FR-022 | TASK-015, TASK-027 | Must | Not Started | 1 hari | MarketplaceController, view list. Query: WHERE status = 'active'. Display thumbnail, nama, city, starting price, rating. Mulai implementasi menggantikan stub interim dari TASK-086 (empty state) |
| TASK-037 | Pagination kost list | FR-050 | TASK-036 | Should | Not Started | 0.5 hari | Laravel paginate, 20 items per page |
| TASK-038 | Search kost by name or location | FR-051 | TASK-036 | Must | Not Started | 0.5 hari | Search: name/city/district/address LIKE %keyword% |
| TASK-039 | Filter kost by price range, category, rating | FR-052, FR-053, FR-054, FR-055 | TASK-036 | Must | Not Started | 1 hari | Filter logic, combine dengan search (AND). Join room_types/price_schemes untuk price filter |
| TASK-040 | Empty state & UI polish marketplace | FR-056 | TASK-036 | Should | Not Started | 0.5 hari | Pesan "Tidak ada kost ditemukan" |
| TASK-041 | View kost detail (info lengkap) | FR-057, FR-035 | TASK-036 | Must | Not Started | 1 hari | KostDetailController, view. Display: info, alamat, galeri, kategori, facilities/rules (parse JSON), document requirements, room types, price schemes |
| TASK-042 | Display kost location on map (Leaflet + OSM) | FR-058 | TASK-041 | Must | Not Started | 1 hari | Embed Leaflet.js, load OSM tiles, marker di lat/long. Include Leaflet CSS/JS di layout |
| TASK-043 | Display room types, pricing, availability | FR-059 | TASK-041, TASK-034 | Must | Not Started | 0.5 hari | Display per room type: nama, harga, available slots (sum free_slots) |
| TASK-044 | Display reviews & ratings di kost detail | FR-060 | TASK-041, TASK-062 | Should | Not Started | 0.5 hari | Join reviews, display rating/comment/images, reviewer info |
| TASK-045 | Unit & feature tests COMP-005 | FR-048—FR-060 | TASK-036—TASK-044 | Must | Not Started | 1 hari | Test browsing, search, filter, detail view, map display |

**Subtotal COMP-005:** 10 tasks, ~7.5 hari

---

### Komponen: COMP-006 — Rental Lifecycle Management

| ID | Judul Task | FR/NFR Terkait | Dependency | Prioritas | Status | Estimasi | Catatan |
|---|---|---|---|---|---|---|---|
| TASK-046 | Setup migration Rental, RentalDocument, RentalStatusHistory | FR-061—FR-068, FR-083—FR-104 | TASK-027 | Must | Not Started | 1 hari | Rental status enum (pending/paid/confirmed/active/completed/cancelled), snapshot fields, soft delete |
| TASK-047 | Tenant create rental dengan transactional room locking (ADR-010) | FR-061—FR-068, FR-122 | TASK-046, TASK-005, TASK-043 | Must | Not Started | 1.5 hari | Action CreateRental, DB transaction, SELECT...FOR UPDATE, validation: email verified, start_date (today+4 to today+30), check free_slots |
| TASK-048 | Calculate grand total & create payment record | FR-066, FR-079, FR-080, FR-121 | TASK-047 | Must | Not Started | 0.5 hari | Grand total = (price × duration) + security_deposit, payment record status pending, expired_at = created_at + 48h |
| TASK-049 | Tenant view own rentals & rental detail | FR-096, FR-097 | TASK-047 | Must | Not Started | 1 hari | RentalController (Tenant), list & detail views, display payment/documents/status history |
| TASK-050 | Admin view rentals for own kost | FR-098, FR-099 | TASK-047 | Must | Not Started | 1 hari | RentalManagementController (Admin), Policy: hanya rental untuk kost Admin |
| TASK-051 | Tenant upload rental documents | FR-083, FR-084, FR-085, FR-086 | TASK-047, TASK-024 | Must | Not Started | 1 hari | Upload per document type (dari kost requirements), validation file type/size, status pending |
| TASK-052 | Admin verify documents (approve/reject) | FR-087, FR-088, FR-089, FR-090, FR-091 | TASK-051 | Must | Not Started | 1 hari | Action VerifyDocument, rejection_reason wajib, Tenant dapat re-upload |
| TASK-053 | Auto-confirm rental setelah semua dokumen wajib approved | FR-092, FR-093 | TASK-052 | Must | Not Started | 0.5 hari | Check all required docs approved, transition paid → confirmed |
| TASK-054 | Tenant manual cancel rental (termasuk dari Active) | FR-123, FR-124, FR-125, FR-127 | TASK-047 | Must | Not Started | 1 hari | Action CancelRental, cancelled_reason wajib, free room slot, prevent cancel Completed |
| TASK-055 | Scheduled job: MonitorRentalLifecycle | FR-076, FR-094, FR-101, FR-102, FR-126 | TASK-047, TASK-053 | Must | Not Started | 1 hari | Job run tiap jam: check payment deadline, document deadline, auto-activate (start date), auto-complete (end date) |
| TASK-056 | Record rental status history | FR-100, FR-103 | TASK-047 | Must | Not Started | 0.5 hari | Append rental_status_histories saat setiap state transition, display timeline di UI |
| TASK-057 | Unit & feature tests COMP-006 | FR-061—FR-104, FR-121—FR-127 | TASK-046—TASK-056 | Must | Not Started | 1.5 hari | Test rental creation (with locking), lifecycle transitions, cancellation, scheduled job |

**Subtotal COMP-006:** 12 tasks, ~12 hari

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
| COMP-002 (Kost Publication) | 8 | 0 | 0 | 8 | 0 | 6.5 hari |
| COMP-003 (Kost Configuration) | 9 | 0 | 0 | 9 | 0 | 7.5 hari |
| COMP-004 (Room Inventory) | 9 | 0 | 0 | 9 | 0 | 7.5 hari |
| COMP-005 (Marketplace) | 10 | 0 | 0 | 10 | 0 | 7.5 hari |
| COMP-006 (Rental Lifecycle) | 12 | 0 | 0 | 12 | 0 | 12 hari |
| COMP-007 (Payment) | 6 | 0 | 0 | 6 | 0 | 4.5 hari |
| COMP-008 (Review) | 5 | 0 | 0 | 5 | 0 | 3 hari |
| COMP-009 (Administration) | 5 | 0 | 0 | 5 | 0 | 3 hari |
| Cross-Cutting | 5 | 0 | 0 | 5 | 0 | 4 hari |
| **TOTAL** | **82 tasks** | **13** | **0** | **69** | **0** | **~63.75 hari kerja** |

**Catatan Estimasi:**
- Total **~63.75 hari kerja** untuk 1 developer (solo work)
- Equivalent **~12-14 minggu** (5 hari kerja per minggu)
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
| FR-007 (RBAC Role) | COMP-001 | TASK-003, TASK-008 | Not Started |
| FR-008 (RBAC Ownership) | COMP-001 | TASK-008 | Not Started |
| FR-009 (Profile View) | COMP-001 | TASK-006 | Not Started |
| FR-010 (Profile Update) | COMP-001 | TASK-006 | Not Started |
| FR-011 (Avatar Upload) | COMP-001 | TASK-006, TASK-087 | Not Started |
| FR-012 (Soft Delete) | COMP-001 | TASK-007 | Not Started |
| FR-013 (Prevent Deleted Auth) | COMP-001 | TASK-007, TASK-088 | Not Started |
| FR-130 (Password Reset OTP) | COMP-001 | TASK-085 | Not Started |
| FR-014 (Create Kost Draft) | COMP-002 | TASK-010, TASK-011 | Not Started |
| FR-015 (Update Draft) | COMP-002 | TASK-011 | Not Started |
| FR-016 (Submit for Review) | COMP-002 | TASK-012 | Not Started |
| FR-017 (Validate Before Submit) | COMP-002 | TASK-012 | Not Started |
| FR-018 (Approve Submission) | COMP-002 | TASK-013 | Not Started |
| FR-019 (Reject Submission) | COMP-002 | TASK-013 | Not Started |
| FR-020 (Revise Rejected) | COMP-002 | TASK-014 | Not Started |
| FR-021 (Publish Approved) | COMP-002 | TASK-015 | Not Started |
| FR-022 (Display Only Active) | COMP-002, COMP-005 | TASK-015, TASK-036 | Not Started |
| FR-023 (Prevent Direct Status Change) | COMP-002 | TASK-016 | Not Started |
| FR-024 (Basic Info) | COMP-003 | TASK-019 | Not Started |
| FR-025 (Address Config) | COMP-003 | TASK-019 | Not Started |
| FR-026 (Upload Images) | COMP-003 | TASK-020 | Not Started |
| FR-027 (Assign Categories) | COMP-003 | TASK-021 | Not Started |
| FR-028 (Facilities JSON) | COMP-003 | TASK-022 | Not Started |
| FR-029 (Rules JSON) | COMP-003 | TASK-022 | Not Started |
| FR-030 (QRIS Upload) | COMP-003 | TASK-023 | Not Started |
| FR-031 (Bank Account Info) | COMP-003 | TASK-023 | Not Started |
| FR-032 (Document Requirements Config) | COMP-003 | TASK-024 | Not Started |
| FR-033 (Set Doc Required/Optional) | COMP-003 | TASK-024 | Not Started |
| FR-034 (Doc Requirement Reason) | COMP-003 | TASK-024 | Not Started |
| FR-035 (Display Doc Requirements) | COMP-003, COMP-005 | TASK-024, TASK-041 | Not Started |
| FR-036—FR-047 (Room Inventory) | COMP-004 | TASK-027—TASK-034 | Not Started |
| FR-048—FR-060 (Marketplace) | COMP-005 | TASK-036—TASK-044 | Not Started |
| FR-061—FR-068 (Rental Booking) | COMP-006 | TASK-046—TASK-048 | Not Started |
| FR-069—FR-082 (Payment QRIS) | COMP-007 | TASK-058—TASK-063 | Not Started |
| FR-083—FR-095 (Document Verification) | COMP-006 | TASK-051—TASK-053 | Not Started |
| FR-096—FR-104 (Rental Monitoring) | COMP-006 | TASK-049—TASK-050, TASK-055—TASK-056 | Not Started |
| FR-105—FR-110 (Review) | COMP-008 | TASK-064—TASK-068 | Not Started |
| FR-111—FR-116 (Admin Account Mgmt) | COMP-009 | TASK-069—TASK-073 | Not Started |
| FR-117—FR-120 (Category Mgmt) | COMP-003, COMP-009 | TASK-025 | Not Started |
| FR-121—FR-129 (Open Questions Resolution) | COMP-006, COMP-007 | TASK-047, TASK-048, TASK-054—TASK-055 | Not Started |
| NFR-004—NFR-010 (Security) | COMP-001, Cross-Cutting | TASK-008, TASK-076—TASK-078 | Not Started |
| NFR-015 (Email Notification) | Cross-Cutting | TASK-074, TASK-075 | Not Started |

**Coverage:** 130 FR → 81 TASK (100% Must-have FR covered)

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

---

**COMP-001 DONE (13/13 tasks, incl. TASK-085 Password Reset via OTP, TASK-086 On-Demand Email Verification, TASK-087 Profile Verify Button + storage fix & TASK-088 Registrasi pesan email terpakai).** Next: COMP-002 (Kost Publication Management) — TASK-010 onward.
