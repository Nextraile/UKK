# PAGES.md — Page & Interface Specifications

> **Status dokumen ini:** Single Source of Truth untuk SPESIFIKASI HALAMAN & EMAIL.
> Dokumen ini menyediakan detail lengkap untuk semua 57 halaman + 8 email template di aplikasi SewaKost.
> Setiap page spec mencakup: URL, auth requirement, layout, components, data, validation, user flows, accessibility.

| Field | Value |
|---|---|
| Nama Proyek | SewaKost — Web Marketplace Kost Management & Rental System |
| Versi Dokumen | `1.3.2` |
| Terakhir Diperbarui | `2026-08-30` |
| Total Pages | 64 pages + 8 email templates |

---

## 0. Cara Menggunakan Dokumen Ini

### 0.1 Untuk Agent/Developer
1. **Baca DESIGN.md terlebih dahulu** untuk memahami design tokens, components, layout patterns
2. **Cari page spec** berdasarkan URL atau FR-xxx yang direferensikan dari TODO.md
3. **Implementasi Blade view** berdasarkan spec: layout, components, data requirements, validation rules
4. **Follow user flows** untuk memahami interaksi dan edge cases
5. **Test accessibility** sesuai catatan aksesibilitas per page
6. **Understand unified navigation** — All pages use unified navbar component `<x-nav-public />`:
   - Shows same UI for all roles (guest, tenant, admin, superadmin)
   - **Dashboard link** routes to role-specific page via `auth()->user()->dashboardRoute()` method
   - **Admin/Super Admin pages**: Use admin sidebar layout (`layouts/admin.blade.php`) for page content, but navbar at top is still `<x-nav-public />`
   - **Breadcrumbs**: No longer include "Dashboard" link — start directly with section context (e.g., "Rentals", "Kost Management", "Profile")

### 0.2 Struktur Page Spec
Setiap page specification berisi:
- **URL & Route Name:** Routing information dari ARCHITECTURE.md §6.1
- **Auth Requirement:** Guest/authenticated/verified/role-specific
- **Purpose:** Tujuan halaman (dari user perspective)
- **Layout:** Layout pattern (Public/Admin/Auth) + structure detail
- **Components Used:** Referensi ke DESIGN.md §3 Component Library
- **Data Requirements:** Query/eager loading/pagination needs
- **Validation Rules:** Form validation (jika ada form)
- **User Flows:** Step-by-step interaction scenarios
- **Edge Cases:** Error states, empty states, permission denied
- **Accessibility Notes:** Keyboard nav, screen reader, focus management

### 0.3 Hubungan dengan Dokumen Lain
- **DESIGN.md §3** → Component specifications (HTML/Tailwind/Alpine.js examples)
- **DESIGN.md §4** → Layout patterns (Public/Admin/Auth layouts)
- **ARCHITECTURE.md §6.1** → Routes & middleware configuration
- **PRD.md §7** → Functional requirements (FR-xxx) per page
- **TODO.md** → Implementation tasks (TASK-xxx) reference page specs

---

## 1. Page Inventory Summary

### 1.1 By User Context

| Context | Page Count | Description |
|---|---|---|
| **Public (No Auth)** | 3 pages | Landing, Marketplace List, Kost Detail |
| **Auth Pages** | 6 pages | Login, Register, OTP Verify, Forgot Password, Reset OTP, Set New Password (incl. Verify Email Modal PAGE-006D spec) |
| **Tenant Interface** | 16 pages | Dashboard, Rental Management (+ Create/Review), Payment, Documents, Review |
| **Admin Interface** | 28 pages | Dashboard, Kost CRUD (PAGE-011), Kost Config Hub (PAGE-020), 6 Config sections (PAGE-014—PAGE-019), Room Inventory, Rental Verification |
| **Super Admin Interface** | 11 pages | Submissions Review (PAGE-012), Admin Management, Category Management |
| **Email Templates** | 8 templates | OTP, Reset OTP, Admin Account, Payment/Document Verifications, Rental Status |

**Total:** 64 pages + 8 email templates = **72 interface specifications**

---

## 2. Public Pages (No Auth Required) — 3 Pages

### PAGE-001: Landing Page

**URL:** `/`  
**Route Name:** `home`  
**Method:** GET  
**Auth:** None (public)  
**Controller:** `HomeController@index`  
**FR Reference:** Not explicitly in FR (marketing/entry point)

#### Purpose
- Marketing landing page untuk introduce SewaKost value proposition
- Drive conversions: visitor → registered tenant OR browse marketplace
- Build trust dengan featured kosts (highest rated) dan testimonials

#### Layout Structure
```
┌─────────────────────────────────────────────┐
│ Public Nav (sticky)                         │
├─────────────────────────────────────────────┤
│ Hero Section (gradient bg, h-screen)        │
│ - Headline: "Temukan Kost Impian Anda"     │
│ - Subtext: value proposition                │
│ - CTA: "Cari Kost" + "Daftar Sekarang"     │
├─────────────────────────────────────────────┤
│ Featured Kosts (3-col grid, 6 cards)        │
├─────────────────────────────────────────────┤
│ How It Works (3 steps, icon + text)         │
├─────────────────────────────────────────────┤
│ Testimonials (slider, 3+ items)             │
├─────────────────────────────────────────────┤
│ Footer (multi-column: links, contact)       │
└─────────────────────────────────────────────┘
```

#### Components Used (DESIGN.md refs)
- `<x-nav-public>` — Public navigation (§3.6)
- Hero: Custom gradient section with primary + outline buttons (§3.1)
- `<x-kost-card />` x 6 — Featured kost cards (§3.3)
- Process steps: Icon + heading + description (custom)
- `<x-testimonial-slider />` — Card with quote + avatar + name (§3.34)
- `<x-footer />` — Multi-column footer (§3.35)

#### Data Requirements
```php
// Controller
public function index()
{
    $featuredKosts = Kost::query()
        ->where('status', 'active')
        ->withAvg('reviews', 'kost_rating')
        ->with(['address', 'images' => fn($q) => $q->where('is_thumbnail', true)])
        ->orderByDesc('reviews_avg_kost_rating')
        ->limit(6)
        ->get();
    
    // Static testimonials (or query from reviews table)
    $testimonials = [
        ['quote' => '...', 'name' => '...', 'avatar' => '...', 'rating' => 5],
    ];
    
    return view('welcome', compact('featuredKosts', 'testimonials'));
}
```

**Eager Loading:**
- `address` (for location display)
- `images` (thumbnail only)
- `reviews_avg_kost_rating` (for sorting)

#### User Flows

**Flow 1: Guest browsing (most common)**
1. Visitor lands on `/`
2. Sees hero headline + featured kosts
3. Clicks "Cari Kost" CTA → redirect to `/marketplace`

**Flow 2: Guest wants to register**
1. Visitor lands on `/`
2. Clicks "Daftar Sekarang" → redirect to `/register`

**Flow 3: Authenticated user lands on homepage**
1. User (already logged in) navigates to `/`
2. Sees nav with "Rental Saya" link
3. Clicks "Rental Saya" → redirect to `/rentals`

**Flow 4: Click featured kost**
1. Visitor clicks featured kost card
2. Redirect to `/marketplace/kosts/{slug}` (PAGE-003)

#### Responsive Behavior
- **Desktop (≥1024px):** Hero full-screen height, 3-column grid for kosts
- **Tablet (768-1023px):** Hero 70vh, 2-column grid
- **Mobile (<768px):** Hero 60vh, 1-column stack, testimonials full-width swipe

#### Accessibility Notes
- Hero headline: `<h1>` with proper heading hierarchy
- CTA buttons: adequate touch targets (44x44px min)
- Featured kosts: each card is `<article>` with proper semantic structure
- Testimonials slider: keyboard navigable (left/right arrow keys)
- Skip link: "Skip to main content" at top for keyboard users

#### Performance Optimization
- Hero gradient: CSS (no image), fast render
- Featured kost images: lazy loading with placeholder
- Testimonials: load 3 initially, lazy load rest if slider scrolled

---

### PAGE-002: Marketplace List

**URL:** `/marketplace`  
**Route Name:** `marketplace.index`  
**Method:** GET  
**Auth:** None (browsing public)  
**Controller:** `MarketplaceController@index`  
**FR Reference:** FR-048 (Browse without login), FR-049 (Display list), FR-051-055 (Search/Filter), FR-056 (Empty state)

> **Catatan (Stub):** Sejak COMP-001/TASK-086 (ADR-023), `/marketplace` dijalankan sebagai **STUB interim** — `MarketplaceController@index` menampilkan empty state (`$kosts = collect()`), tanpa auth, agar redirect pasca-registrasi valid. Implementasi penuh (list kost Active, search, filter, pagination) dibangun di TASK-036 (COMP-005). URL/route name tidak berubah: `/marketplace`, `marketplace.index`.

#### Purpose
- Display all active kosts dengan filtering & search capabilities
- Allow visitor/tenant to browse, filter by price/category/rating, search by name/location
- Drive traffic to kost detail pages (conversions)

#### Layout Structure
```
┌─────────────────────────────────────────────┐
│ Public Nav (sticky)                         │
├──────────┬──────────────────────────────────┤
│ Sidebar  │ Search Bar (top, full-width)     │
│ Filters  │                                  │
│ (25%,    │ Kost Grid (3 cols, responsive)   │
│ sticky)  │ - Kost Card x N                  │
│          │                                  │
│ Price    │ Pagination (bottom)              │
│ Category │                                  │
│ Rating   │ Empty State (if no results)      │
│          │                                  │
│ [Apply]  │                                  │
├──────────┴──────────────────────────────────┤
│ Footer                                      │
└─────────────────────────────────────────────┘
```

#### Components Used
- `<x-nav-public />` — Public navigation (§3.6) ✅ **WAJIB** — sticky navbar dengan logo, "Cari Kost" link, auth links/user menu
- `<x-search />` — Search bar (nama/lokasi) + submit-on-Enter (§3.36)
- `<x-mobile-filter-drawer />` — Filter drawer mobile (harga min-max + kategori + rating, chips ringkasan filter aktif) (§3.32). Desktop: panel filter inline (sidebar kiri, sticky) memakai pola yang sama: `fieldset` + `legend` per grup filter, tombol "Terapkan Filter" `x-button` primary — lihat §3.32 untuk struktur field + a11y
- `<x-kost-card />` x N (§3.3)
- `<x-pagination />` (§3.15, Laravel default styled)
- `<x-empty-state />` — No results state (§3.8)
- `<x-skeleton variant="card" />` x 12 — Loading state (§3.38) (skeleton card pattern — DESIGN §3.9)
- `<x-footer />` — Multi-column footer (§3.35) ✅ **WAJIB** — brand, navigation links, social media, copyright. Sesuai DESIGN.md §4.1 Public Layout Pattern

#### Data Requirements
```php
public function index(Request $request)
{
    $query = Kost::query()
        ->where('status', 'active')
        ->with(['address', 'categories', 'images' => fn($q) => $q->where('is_thumbnail', true)])
        ->withAvg('reviews', 'kost_rating')
        ->withCount('reviews');
    
    // Search filter (FR-051)
    if ($search = $request->input('search')) {
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhereHas('address', fn($q) => $q->where('city', 'like', "%{$search}%")
                                                   ->orWhere('district', 'like', "%{$search}%"));
        });
    }
    
    // Price range filter (FR-052)
    if ($min = $request->input('price_min')) {
        $query->whereHas('roomTypes.priceSchemes', fn($q) => $q->where('price', '>=', $min));
    }
    if ($max = $request->input('price_max')) {
        $query->whereHas('roomTypes.priceSchemes', fn($q) => $q->where('price', '<=', $max));
    }
    
    // Category filter (FR-053)
    if ($categories = $request->input('categories', [])) {
        $query->whereHas('categories', fn($q) => $q->whereIn('categories.id', $categories));
    }
    
    // Rating filter (FR-054)
    if ($ratingMin = $request->input('rating_min')) {
        $query->having('reviews_avg_kost_rating', '>=', $ratingMin);
    }
    
    $kosts = $query->paginate(20);
    
    $categories = Category::withCount('kosts')->get(); // For filter sidebar
    
    return view('marketplace.index', compact('kosts', 'categories'));
}
```

**Validation:**
- `price_min`: nullable, numeric, min:0
- `price_max`: nullable, numeric, min:0, gte:price_min
- `categories`: nullable, array
- `categories.*`: exists:categories,id
- `rating_min`: nullable, numeric, min:1, max:5
- `search`: nullable, string, max:255

#### User Flows

**Flow 1: Browse without filters (default)**
1. User navigates to `/marketplace`
2. Page loads with all active kosts (paginated 20/page)
3. User scrolls, sees kost cards
4. User clicks kost card → `/marketplace/kosts/{slug}` (PAGE-003)

**Flow 2: Search by location**
1. User enters "Bandung" in search box
2. Submits form (or auto-submit on Enter)
3. Page reloads with filtered results (kosts in Bandung)
4. Results count displayed: "Menampilkan X kost di Bandung"

**Flow 3: Filter by price + category**
1. User sets price min = 1000000, max = 2000000
2. User checks "Kost Putra" category
3. User clicks "Terapkan Filter"
4. Page reloads with filtered results (AND logic)
5. URL: `/marketplace?price_min=1000000&price_max=2000000&categories[]=1`

**Flow 4: No results found**
1. User applies very restrictive filters (e.g., price max = 100000)
2. No kosts match criteria
3. Empty state displayed: "Tidak ada kost ditemukan. Coba ubah filter Anda."
4. "Reset Filter" button visible → clears all filters, redirects to `/marketplace`

**Flow 5: Pagination**
1. User scrolls to bottom, sees page 1 of 5
2. Clicks "Next" or page number "2"
3. Page reloads with next 20 kosts, scroll to top
4. Filter params preserved in pagination links

#### Responsive Behavior
- **Desktop:** Sidebar always visible (left 25%), 3-column grid
- **Tablet:** Sidebar in drawer (hamburger icon), 2-column grid
- **Mobile:** Sidebar in bottom sheet drawer, 1-column stack

**Filter drawer (mobile):** `<x-mobile-filter-drawer />` (§3.32) — drawer kanan, backdrop click/Esc close, focus trap, body scroll lock, tombol "Terapkan Filter". Toggle button ada di bar atas (icon filter + badge jumlah filter aktif), bukan FAB — konsisten dengan `x-nav-public` dan pola kartu. Desktop: panel filter inline (sidebar) dengan struktur fieldset/legend yang sama.

#### Accessibility Notes
- Search input: `aria-label="Search kosts by name or location"`
- Filter checkboxes: proper `<fieldset>` + `<legend>` grouping
- Results count: `aria-live="polite"` announce to screen readers when filters change
- Empty state: clear heading + actionable CTA
- Pagination: `aria-label="Pagination navigation"` on nav element

#### Edge Cases
- **No kosts in database:** Show empty state dengan CTA untuk Admin/Super Admin only (if authenticated)
- **Price min > price max:** Validation error, inline message "Harga minimum tidak boleh lebih besar dari maksimum"
- **Invalid category ID:** Silently ignore (don't crash), or show validation error
- **Search query too short (<3 chars):** Optional UX improvement: show hint "Ketik minimal 3 karakter"

---

### PAGE-003: Kost Detail

**URL:** `/marketplace/kosts/{kost:slug}`  
**Route Name:** `marketplace.show`  
**Method:** GET  
**Auth:** None (detail public, booking requires auth)  
**Controller:** `KostDetailController@show`  
**FR Reference:** FR-057 (View detail), FR-058 (Map display), FR-059 (Room types pricing), FR-060 (Reviews), FR-035 (Document requirements display)

#### Purpose
- Show complete kost information: images, description, facilities, rules, room types, pricing, reviews, location map
- Drive booking action: "Book Now" button (redirects to login if guest, or rental create if authenticated)
- Display document requirements transparently (FR-035)

#### Layout Structure
```
┌─────────────────────────────────────────────┐
│ Public Nav (sticky)                         │
├─────────────────────────────────────────────┤
│ Breadcrumbs                                 │
├───────────────────────┬─────────────────────┤
│ Image Gallery (hero)   │ Booking Sidebar     │
│ - Main image (large)   │ (sticky, 30%)       │
│ - Thumbnail grid (5+)  │                     │
│                        │ Price: Rp X/bln     │
│ Info Section           │ Rating: ★4.8 (32)   │
│ - Name, address        │                     │
│ - Description          │ [Book Now] btn      │
│ - Facilities list      │ Contact Info        │
│ - Rules list           │                     │
│                        │                     │
│ Document Requirements  │                     │
│ - Checklist display    │                     │
│                        │                     │
│ Room Types Accordion   │                     │
│ - Type A (expand)      │                     │
│   - Size, occupancy    │                     │
│   - Price schemes      │                     │
│   - Availability       │                     │
│                        │                     │
│ Reviews Section        │                     │
│ - Rating summary       │                     │
│ - Review cards (5+)    │                     │
│ - Pagination           │                     │
│                        │                     │
│ Map (Leaflet.js)       │                     │
│ - Location marker      │                     │
├───────────────────────┴─────────────────────┤
│ Footer                                      │
└─────────────────────────────────────────────┘
```

#### Components Used
- `<x-nav-public />` — Public navigation (§3.6) ✅ **WAJIB** — sticky navbar dengan logo, "Cari Kost" link, auth links/user menu
- `<x-breadcrumbs />` (§3.13)
- `<x-gallery-lightbox />` — Hero image + thumbnail grid + lightbox (focus trap, Esc, arrow prev/next) (§3.27)
- Kost Info Card (custom, display facilities/rules as list)
- Document Requirements: `<x-document-upload />` (per dokumen wajib, status upload/terverifikasi) (§3.24) + checklist display (`x-empty-state`/badge pola §3.4 untuk required vs optional) — lihat §3.24 status verifikasi
- `<x-room-card />` x N — Room types accordion (size, occupancy, price schemes, availability real-time) (DESIGN §3.3)
- `<x-review-card />` x N (§3.30)
- `<x-map />` — Map Widget (Leaflet.js, read-only, fallback alamat teks + link Google Maps) (§3.28)
- `<x-rating :value="4.8" :count="32" />` — Rating summary di booking sidebar (§3.29)
- Booking Sidebar (sticky): `<x-booking-form />` panel summary (§3.23); di mobile → `<x-sticky-action-bar />` (fixed bottom) (§3.33)
- `<x-footer />` — Multi-column footer (§3.35) ✅ **WAJIB** — brand, navigation links, social media, copyright. Sesuai DESIGN.md §4.1 Public Layout Pattern

#### Data Requirements
```php
public function show(Kost $kost)
{
    // Route model binding dengan slug
    // Kost must be active untuk public access
    abort_if($kost->status !== 'active', 404);
    
    $kost->load([
        'address',
        'categories',
        'kostImages' => fn($q) => $q->orderBy('sort_order'),
        'documentRequirements',
        'roomTypes' => fn($q) => $q->with([
            'priceSchemes' => fn($q) => $q->where('is_active', true),
            'roomTypeImages' => fn($q) => $q->where('is_thumbnail', true),
        ]),
        'reviews' => fn($q) => $q->latest()->with('tenant')->limit(10),
    ]);
    
    // Calculate availability per room type (real-time)
    $kost->roomTypes->each(function($roomType) {
        $roomType->available_count = $roomType->rooms()
            ->where('status', 'available')
            ->whereDoesntHave('rentals', fn($q) => 
                $q->whereIn('status', ['pending', 'paid', 'confirmed', 'active'])
            )
            ->count();
    });
    
    // Avg rating
    $avgRating = $kost->reviews()->avg('kost_rating');
    $reviewCount = $kost->reviews()->count();
    
    return view('marketplace.show', compact('kost', 'avgRating', 'reviewCount'));
}
```

**Eager Loading:**
- `address` (for map coordinates + full address display)
- `categories` (display as tags)
- `kostImages` (gallery, sorted by sort_order)
- `documentRequirements` (checklist display)
- `roomTypes.priceSchemes` (active only)
- `roomTypes.roomTypeImages` (thumbnail)
- `reviews.tenant` (for reviewer name + avatar)

#### User Flows

**Flow 1: Guest views detail (not logged in)**
1. Guest clicks kost card from marketplace → `/marketplace/kosts/kost-mawar-indah`
2. Page loads dengan full kost info
3. Guest scrolls, reads description, sees room types, reviews
4. Guest clicks "Book Now" button
5. **Redirect to login** with return URL: `/login?redirect=/rentals/create?kost_id={id}`
6. After login, redirect to rental create form with kost pre-selected

**Flow 2: Authenticated tenant views detail (email NOT verified)**
1. Tenant (logged in, email not verified) navigates to kost detail
2. Sees "Book Now" button (enabled because authenticated)
3. Clicks "Book Now"
4. Middleware `verified` memblok → redirect back + flash `verify_email_prompt`
5. **Modal popup verifikasi muncul** (PAGE-006D) — CTA menuju `/verify-email` (PAGE-006), OTP dikirim on-demand

**Flow 3: Authenticated tenant views detail (email verified)**
1. Tenant (logged in, email verified) navigates to kost detail
2. Clicks "Book Now" button
3. **Redirect to rental create:** `/rentals/create?kost_id={id}&room_type_id={selected_room_type_id}`
4. Rental form pre-populated with selected kost + room type

**Flow 4: Explore room types**
1. User scrolls to "Room Types" section (accordion)
2. Clicks "Kamar Standard" → accordion expands
3. Sees size (3x4m), max occupancy (2 orang), price schemes (1 bulan = Rp 1.5jt, 3 bulan = Rp 4jt), availability (3 kamar tersedia)
4. Clicks "Pilih Kamar" button → triggers same flow as "Book Now" (redirect based on auth state)

**Flow 5: View reviews**
1. User scrolls to "Reviews & Ratings" section
2. Sees rating summary (★4.8 average, 32 reviews)
3. Sees 10 recent reviews (user avatar, name, rating, comment, images, date)
4. If >10 reviews exist: pagination visible
5. User clicks "Next" → load next 10 reviews

**Flow 6: View location map**
1. User scrolls to "Lokasi" section
2. Leaflet map renders dengan marker at kost coordinates (latitude, longitude)
3. User can pan/zoom map (read-only, no directions)
4. Address displayed below map: "Jl. Mawar Indah No.123, Bandung Barat, Jawa Barat"

#### Responsive Behavior
- **Desktop:** 2-column (content 70% + sidebar 30% sticky)
- **Tablet:** Sidebar below content (not sticky), full-width
- **Mobile:** Single column stack, "Book Now" button sticky at bottom (fixed position)

**Mobile booking button (sticky bottom):** `<x-sticky-action-bar />` (§3.33) — fixed bottom, berisi `<x-rating :value="4.8" :count="32" />` + harga + tombol "Book Now" (`x-button` primary, full-width, `rounded-md`). Token: `bg-surface-raised dark:bg-surface-raised-dark border-border dark:border-border-dark`.

#### Accessibility Notes
- Image gallery: Keyboard navigable (Tab to thumbnails, Enter to open lightbox, Esc to close)
- Lightbox modal: Focus trap, Esc key close, arrow keys navigation
- Map: `<div role="region" aria-label="Kost location map">`, focusable marker
- Room type accordion: `aria-expanded`, `aria-controls` attributes
- Reviews: Proper heading hierarchy (`<h2>Reviews</h2>` → `<h3>` per review)
- Booking sidebar: Sticky position doesn't hide critical content on scroll

#### Edge Cases
- **Kost status not active:** 404 error (handled in controller)
- **No images uploaded:** Show placeholder image
- **No room types:** Display message "Belum ada tipe kamar tersedia"
- **No reviews:** Empty state "Belum ada review. Jadilah yang pertama!"
- **No coordinates (lat/long null):** Hide map section entirely
- **All room types unavailable:** "Book Now" button disabled, message "Kamar penuh"

---

## 3. Auth Pages (Laravel Breeze Customized) — 6 Pages + 1 Modal (PAGE-006D)

### PAGE-004: Login

**URL:** `/login`  
**Route Name:** `login`  
**Method:** GET, POST  
**Auth:** Guest only (redirect if authenticated)  
**Controller:** `Auth\LoginController@create`, `@store`  
**FR Reference:** FR-001 (User login), FR-007 (RBAC redirect)

#### Purpose
- User authentication dengan email + password
- Redirect based on role after successful login:
  - Tenant → `/rentals`
  - Admin → `/admin/kosts`
  - Super Admin → `/superadmin/submissions`

#### Layout Structure (Auth Layout)
```
┌─────────────────────────────────────────────┐
│ (Centered, min-h-screen flex)               │
│                                             │
│        ┌─────────────────┐                  │
│        │  Logo + Title   │                  │
│        │  "SewaKost"     │                  │
│        │                 │                  │
│        │  Login Form     │                  │
│        │  - Email        │                  │
│        │  - Password     │                  │
│        │  - Remember Me  │                  │
│        │  - [Login] btn  │                  │
│        │                 │                  │
│        │  Forgot Pass?   │                  │
│        │  Register link  │                  │
│        └─────────────────┘                  │
│                                             │
└─────────────────────────────────────────────┘
```

**Container:** `max-w-md mx-auto px-4`  
**Card:** `bg-surface-raised dark:bg-surface-raised-dark shadow-xl rounded-xl p-8`

#### Components Used
- Logo image (custom)
- `<x-input />` x 2 (email, password with toggle visibility) (§3.2)
- `<x-checkbox />` (remember me) (§3.2)
- `<x-button variant="primary" />` (submit) (§3.1)
- Error display: inline `text-error-700` + `aria-describedby="{field}-error"` menghubungkan input ke pesan error (§3.2) — bukan komponen terpisah

#### Data Requirements
```php
// LoginController@store
public function store(LoginRequest $request)
{
    $request->authenticate(); // Laravel Breeze method
    
    $request->session()->regenerate();
    
    // Role-based redirect
    return match(auth()->user()->role) {
        'superadmin' => redirect()->intended('/superadmin/submissions'),
        'admin' => redirect()->intended('/admin/kosts'),
        default => redirect()->intended('/rentals'),
    };
}
```

**Validation (LoginRequest):**
```php
public function rules()
{
    return [
        'email' => ['required', 'email'],
        'password' => ['required'],
    ];
}

public function authenticate()
{
    if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }
    
    // Check if account is soft deleted
    if (auth()->user()->trashed()) {
        Auth::logout();
        throw ValidationException::withMessages([
            'email' => 'Akun Anda sudah tidak aktif.',
        ]);
    }
}
```

#### User Flows

**Flow 1: Successful login (Tenant)**
1. User navigates to `/login`
2. Enters email + password
3. Clicks "Masuk"
4. Backend validates credentials
5. Session created, redirect to `/rentals`

**Flow 2: Invalid credentials**
1. User enters wrong email or password
2. Clicks "Masuk"
3. Form reloads dengan error: "Email atau password tidak valid" (under email field)
4. Password field cleared, email field retains value
5. Focus returns to email field

**Flow 3: Account soft deleted**
1. User (previously deleted account) attempts login
2. Backend checks `deleted_at` column
3. Auth attempt succeeds initially, then logout forced
4. Error message: "Akun Anda sudah tidak aktif"

**Flow 4: Remember me**
1. User checks "Remember Me" checkbox
2. Logs in successfully
3. Session cookie extended to 2 weeks (Laravel default)
4. User closes browser, returns later → still logged in

**Flow 5: Redirect after login (intended URL)**
1. Guest tries to access `/rentals/create` (requires auth)
2. Middleware redirects to `/login?redirect=/rentals/create`
3. User logs in
4. Redirect to intended URL (`/rentals/create`) instead of default dashboard

**Flow 6: Already authenticated**
1. Logged-in user navigates to `/login`
2. Middleware detects authenticated session
3. Redirect to role-based dashboard (no login form shown)

#### Responsive Behavior
- **All screen sizes:** Centered card, max-width 448px (md)
- **Mobile:** Full-width card (px-4), adequate touch targets

#### Accessibility Notes
- **Autofocus:** Email input focused on page load (`autofocus` attribute)
- **Error association:** `aria-describedby="email-error"` links input to error message
- **Password toggle:** Icon button has `aria-label="Show password"` / "Hide password"
- **Submit button:** Disabled state while submitting (prevent double-click)

#### Edge Cases
- **Rate limiting:** After 5 failed attempts, throttle for 1 minute (Laravel Breeze default)
- **Error message:** "Too many login attempts. Please try again in 60 seconds."
- **CSRF token expired:** Laravel automatic handling, show error "Page expired, please refresh"

---



### PAGE-005: Register (Tenant Self-Registration)

**URL:** `/register`  
**Route Name:** `register`  
**Method:** GET, POST  
**Auth:** Guest only  
**Controller:** `Auth\RegisterController@create`, `@store`  
**FR Reference:** FR-003 (Tenant self-registration — verifikasi email on-demand, tidak ada OTP otomatis), FR-004 (OTP email verification — dikirim on-demand saat user buka halaman verifikasi atau diminta fitur)

#### Purpose
- Tenant account creation (self-service)
- Redirect ke `/marketplace` setelah registrasi (verifikasi email opsional, on-demand — FR-003)

#### Components Used
- `<x-input />` x 4 (first_name, last_name, email, password with strength indicator)
- `<x-checkbox />` (terms agreement)
- `<x-button variant="primary" full-width />` (submit)
- `<x-password-strength />` — strength meter + toggle visibility (Alpine.js) (§3.21)

#### Validation Rules
```php
'first_name' => ['required', 'string', 'max:100'],
'last_name' => ['nullable', 'string', 'max:100'],
'email' => ['required', 'email', 'unique:users,email'],
'password' => ['required', 'min:8', 'confirmed'],
'terms' => ['required', 'accepted'],
```

#### User Flow
1. User fills form (first name, last name, email, password, confirm password)
2. Checks "Setuju syarat dan ketentuan"
3. Clicks "Daftar"
4. Backend creates user record (role = `user`, email_verified_at = NULL)
5. `Akun dibuat (role=user, email_verified_at=NULL)`
6. `Redirect ke /marketplace` TANPA kirim OTP

> **Catatan:** Verifikasi email opsional — user bisa browse marketplace tanpa verified (FR-006). Popup verifikasi muncul saat akses fitur yang membutuhkan email terverifikasi (lihat PAGE-006D).

**Error States:**
- Email exists: "Email sudah terdaftar, silakan masuk"
- Password mismatch: "Konfirmasi password tidak cocok"
- Terms not checked: "Anda harus menyetujui syarat dan ketentuan"

#### Accessibility Notes
- Setiap input punya `<label for>` eksplisit; error inline `text-error-700` terhubung via `aria-describedby="{field}-error"` (§3.2)
- Strength meter `<x-password-strength>`: skor dinamis diumumkan `aria-live="polite"` (mis. "Kekuatan: Kuat"); disabled state pada submit saat password lemah
- `autofocus` pada first_name saat halaman dimuat
- Terms checkbox: label menautkan seluruh teks syarat; error `aria-describedby="terms-error"`
- Submit button `x-button` disabled saat proses (prevent double-click); visible focus ring di semua field

---

### PAGE-006: OTP Email Verification

**URL:** `/verify-email`  
**Route Name:** `verification.notice`  
**Method:** GET (throttle:5,1), POST  
**Auth:** Authenticated, NOT verified  
**Controller:** `Auth\EmailVerificationController@show`, `@verify`  
**FR Reference:** FR-004 (OTP verification), FR-005 (Resend OTP), FR-128 (15min expiry)

#### Trigger (On-Demand)
- OTP **tidak dikirim saat registrasi** (FR-003). Dikirim otomatis (lazy) saat halaman ini dibuka **jika belum ada OTP valid** (`OtpService::hasValidOtp` false → `OtpService::generate`).
- OTP juga dikirim ulang otomatis saat halaman dibuka jika OTP sebelumnya sudah **expired** (>15 menit, FR-128).
- Route GET `verification.notice` diberi `throttle:5,1` karena berpotensi mengirim email (5 request per menit per user).
- Selain diblokir oleh fitur ber-verifikasi (popup PAGE-006D), user dapat memulai verifikasi dari tombol **'Verifikasi Email'** di halaman profil (show & edit) untuk user unverified (`email_verified_at` null) → `route('verification.notice')`. Setelah verified, badge email berubah **'Terverifikasi'** (FR-004 on-demand).

#### Purpose
- Input 6-digit OTP code to verify email
- Countdown timer showing resend throttle (60 seconds)
- Resend OTP functionality

#### Layout Structure
```
Centered card (max-w-md):
- Email icon (large)
- Instruction: "Kami telah mengirim kode OTP ke r***@gmail.com"
- Single OTP input field (letter-spacing visual, placeholder `● ● ● ● ● ●`)
- Countdown: "Kirim ulang tersedia dalam 58 detik"
- Resend link (disabled until countdown reaches 0)
- Auto-submit on 6 digits (no manual submit button)
```

#### Components Used
- `<x-otp-input />` (single input field, native paste/autofill, auto-submit) (§3.19)
- `<x-countdown />` (Alpine.js, hh:mm:ss, `aria-live` + expired callback) (§3.20)
- Resend link button

#### Data Requirements
```php
// OTP disimpan DUAL (bukan "ATAU"): tabel otp_verifications + cache Redis
// 1. Tabel otp_verifications: user_id, otp_code (hash SHA-256), purpose, expires_at,
//    attempts (lockout 5x/15 menit), created_at — audit trail + lockout (lihat COMP-001/ADR-022)
// 2. Cache Redis key "otp:{user_id}", TTL 15 menit (FR-128) — lookup cepat
//    Cache::put("otp:{$userId}", $otpCode, now()->addMinutes(15));
// Service: OtpService::generate / verify / hasValidOtp / resend (multi-purpose,
// 'email-verification' vs 'password-reset' — lihat ARCHITECTURE.md ADR-022)
```

#### User Flows

**Flow 1: Successful verification**
1. User membuka `/verify-email` (dari CTA modal popup PAGE-006D, tombol 'Verifikasi Email' di halaman profil, menu, atau akses langsung)
2. Sees masked email address
3. Enters 6-digit code from email
4. Auto-submits after 6th digit (or clicks "Verifikasi")
5. OTP dikirim otomatis saat halaman dibuka (lazy) jika belum ada OTP valid — tidak dikirim saat registrasi
6. Backend validates: OTP matches + not expired
7. Set `email_verified_at = now()`
8. Redirect to `/rentals` with toast "Email berhasil diverifikasi!"

**Flow 2: Invalid OTP**
1. User enters wrong code
2. Error message: "Kode OTP tidak valid"
3. Inputs cleared, focus returns to first box

**Flow 3: Expired OTP**
1. User waits >15 minutes
2. Enters code (even if correct)
3. Error: "Kode OTP sudah expired, klik 'Kirim ulang OTP'"
4. Countdown shows "00:00", resend link enabled

**Flow 4: Resend OTP**
1. Countdown reaches 00:00
2. "Kirim ulang OTP" link enabled
3. User clicks
4. New OTP generated, old expired
5. Email sent (EMAIL-001)
6. Countdown resets to 15:00
7. Toast: "OTP baru telah dikirim"

#### Accessibility Notes
- Single OTP input: `aria-label="Kode OTP"` + `inputmode="numeric"` + `autocomplete="one-time-code"`
- Error message: `role="alert"` for screen reader announcement
- Countdown: Shows resend throttle countdown (60 seconds)

---

### PAGE-006D: Verify Email Modal (Popup)

**URL:** None (overlay di layout — tidak ada route sendiri)  
**Route Name:** — (CTA button menuju `verification.notice`)  
**Method:** —  
**Auth:** Authenticated (user belum verified)  
**Component:** `components/verify-email-modal.blade.php` (di-render di layout app)  
**FR Reference:** FR-004 (OTP on-demand), FR-006 (popup saat akses fitur ber-verifikasi)

#### Trigger
- Middleware `verified` (alias `EnsureEmailIsVerified`, dipasang COMP-006 / TASK-047 mis. create rental) memblok akses fitur yang butuh email terverifikasi.
- Middleware melakukan `redirect()->back()` + flash session `verify_email_prompt=true` (+ pesan error). Layout app membaca flash ini dan menampilkan modal sebagai overlay — user tetap di halaman asal.

#### Isi Modal
- Ikon email/alert (DESIGN.md §3.18)
- Judul: "Email Anda Belum Diverifikasi"
- Body: penjelasan 1–2 kalimat perlunya verifikasi email untuk fitur tersebut
- CTA primary button: "Verifikasi Email" → `route('verification.notice')` (`/verify-email`)
- Tombol tutup/close (dismiss) — modal bisa ditutup, user tetap bisa browse

#### User Flow
1. User (belum verified) mengakses fitur ber-verified (mis. create rental)
2. Middleware `verified` redirect back + flash `verify_email_prompt=true`
3. Modal popup muncul di layout
4. User klik CTA "Verifikasi Email"
5. Redirect ke `/verify-email` → OTP terkirim otomatis (lazy, PAGE-006 Trigger)
6. User input OTP → verified (`email_verified_at` di-set)
7. Redirect sesuai role dashboard; user dapat mengakses fitur yang tadi diblokir

#### Accessibility Notes
- `role="dialog"`, `aria-modal="true"`, `aria-labelledby` mengacu judul modal
- Focus trap di dalam modal saat terbuka; focus kembali ke trigger saat ditutup
- Tombol tutup punya `aria-label` jelas; `Escape` menutup modal
- CTA fokus awal (primary action)

---

### PAGE-006A: Forgot Password (Request Reset OTP)

**URL:** `/forgot-password`  
**Route Name:** `password.request` (GET), `password.email` (POST)  
**Method:** GET, POST  
**Auth:** Guest only  
**Controller:** `Auth\PasswordResetLinkController@create`, `@store`  
**FR Reference:** FR-130 (Password Reset via OTP)

#### Purpose
- User input email untuk memulai alur reset password
- Kirim OTP reset ke email terdaftar, set session `password_reset_email`
- Anti-enumeration: email yang tidak terdaftar mendapat respons yang sama (tidak mengungkap status)

#### Layout Structure (Auth Layout)
```
Centered card (max-w-md):
- Lock icon (large)
- Heading: "Lupa Password?"
- Instruction: "Masukkan email terdaftar. Kami akan mengirim kode OTP reset."
- Email input
- [Kirim Kode Reset] button
- Back link: "Kembali ke login"
```

#### Components Used
- `<x-input type="email" />` (email)
- `<x-button variant="primary" full-width />` (submit)
- Alert/toast untuk feedback (DESIGN.md §3.10)

#### Data Requirements
```php
// POST password.email — setelah validasi:
// 1. Cari user by email. Jika tidak ditemukan → tetap redirect generik (anti-enumeration).
// 2. Jika ditemukan: OtpService::generate($user, 'password-reset')
// 3. Kirim OtpVerificationMail (purpose: password-reset → EMAIL-008)
// 4. Session::put('password_reset_email', $email)
// 5. Redirect ke /reset-password (password.otp)
```

#### Validation Rules
```php
'email' => ['required', 'string', 'email', 'max:255'],
```

#### User Flows

**Flow 1: Email terdaftar (happy path)**
1. User buka `/forgot-password`
2. Input email terdaftar
3. Submit → sistem generate OTP purpose `password-reset` (15 menit expiry), kirim email (EMAIL-008), set session `password_reset_email`
4. Redirect `/reset-password` dengan toast "Kode OTP telah dikirim ke email Anda"

**Flow 2: Email tidak terdaftar (anti-enumeration)**
1. User input email tidak dikenal
2. Sistem tetap redirect `/reset-password` dengan pesan generik yang sama (tidak ada perbedaan respons)
3. Tidak ada OTP yang dikirim, session `password_reset_email` tidak di-set

**Edge: Throttle**
- Route POST dibatasi `throttle:5,1` — setelah 5 request per menit, error "Too Many Attempts"

#### Accessibility Notes
- `autofocus` pada email input
- Error message terhubung via `aria-describedby`
- Submit button disabled saat proses (prevent double-click)

---

### PAGE-006B: Reset Password OTP Verification

**URL:** `/reset-password` (GET), `/reset-password/verify` (POST)  
**Route Name:** `password.otp`, `password.otp.verify`  
**Method:** GET, POST  
**Auth:** Guest only  
**Controller:** `Auth\PasswordResetLinkController@showOtp`, `@verifyOtp`  
**FR Reference:** FR-130 (Password Reset via OTP), FR-128 (15 menit expiry)

#### Purpose
- Input OTP 6 digit reset yang dikirim ke email
- Validasi via `OtpService::verify($user, $code, markEmailVerified: false)` — tidak menandai email verified
- Set session `password_reset_verified` sebagai guard sebelum set password baru

#### Layout Structure (Auth Layout)
```
Centered card (max-w-md):
- Email icon (large)
- Instruction: "Kami telah mengirim kode OTP ke r***@gmail.com" (via User::maskedEmail())
- Single OTP input field (reuse komponen OTP PAGE-006)
- Countdown: "Kirim ulang tersedia dalam 58 detik"
- Auto-submit on 6 digits (no manual submit button)
```

#### Components Used
- `<x-otp-input />` (single input field, native paste/autofill, auto-submit) (§3.19) — sama dengan PAGE-006
- `<x-countdown />` (Alpine.js, expired callback enable "Kirim ulang") (§3.20)
- Alert untuk error/lockout

#### Data Requirements
```php
// GET password.otp:
// Guard: jika session password_reset_email kosong → redirect /forgot-password
// Tampilkan masked email: User::maskedEmail() dari session password_reset_email

// POST password.otp.verify:
// OtpService::verify($user, $code, markEmailVerified: false)
//   → true: Session::put('password_reset_verified', true), redirect /reset-password/change
//   → false: error (kode salah / expired / lockout)
```

#### Validation Rules
```php
// Kode OTP: 6 digit numerik
'code' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
```

#### User Flows

**Flow 1: OTP benar (happy path)**
1. User mendarat di `/reset-password` (session `password_reset_email` terisi)
2. Input 6 digit kode
3. Auto-submit / klik "Verifikasi"
4. `OtpService::verify($user, $code, markEmailVerified: false)` sukses
5. Session `password_reset_verified = true`
6. Redirect `/reset-password/change`

**Flow 2: Kode salah**
1. Input kode salah
2. Error: "Kode OTP tidak valid"
3. Input di-clear, fokus kembali ke box pertama

**Flow 3: Lockout (5× gagal / 15 menit)**
1. 5 percobaan salah dalam 15 menit
2. Error: "Terlalu banyak percobaan. Coba lagi dalam 15 menit"

**Flow 4: OTP expired (>15 menit)**
1. Error: "Kode OTP sudah expired"
2. User harus request ulang: kembali ke `/forgot-password` dan submit email lagi

**Edge: Resend OTP**
- `OtpService::resend` ada untuk email verification, namun route resend khusus reset **belum** diimplementasikan di scope ini (enhancement). Saat ini user yang butuh OTP baru mengulang alur di `/forgot-password`.

**Edge: Direct access tanpa request**
- Session `password_reset_email` kosong → redirect `/forgot-password`

#### Accessibility Notes
- Single OTP input: `aria-label="Kode OTP"` + `inputmode="numeric"` + `autocomplete="one-time-code"`
- Countdown: Shows resend throttle countdown (60 seconds)
- Error message: `role="alert"` for screen reader announcement

---

### PAGE-006C: Set New Password

**URL:** `/reset-password/change`  
**Route Name:** `password.reset` (GET), `password.store` (POST)  
**Method:** GET, POST  
**Auth:** Guest only  
**Controller:** `Auth\NewPasswordController@create`, `@store`  
**FR Reference:** FR-130 (Password Reset via OTP)

#### Purpose
- Set password baru setelah OTP reset terverifikasi
- Guard session `password_reset_verified`; sukses → redirect login

#### Layout Structure (Auth Layout)
```
Centered card (max-w-md):
- Key icon (large)
- Heading: "Buat Password Baru"
- Password input + konfirmasi password
- [Simpan Password] button
```

#### Components Used
- `<x-input type="password" />` x 2 (password, password_confirmation)
- `<x-password-strength />` — strength meter + toggle visibility (Alpine.js) (§3.21)
- `<x-button variant="primary" full-width />`

#### Validation Rules
```php
'password' => ['required', 'string', 'min:8', 'confirmed'],
// Konfirmasi: password_confirmation harus sama
```

#### User Flows

**Flow 1: Happy path**
1. User mendarat di `/reset-password/change` (session `password_reset_verified = true`)
2. Input password baru + konfirmasi
3. Submit → hash password baru disimpan (`users.password` update)
4. Session `password_reset_email` + `password_reset_verified` di-clear
5. Redirect `/login` dengan toast "Password berhasil diubah. Silakan masuk"

**Flow 2: Direct access tanpa verifikasi OTP**
1. User buka `/reset-password/change` langsung (tanpa session `password_reset_verified`)
2. Redirect `/forgot-password`

**Edge: Password tidak memenuhi syarat**
- Error validasi standar (min 8 karakter, konfirmasi cocok)

#### Accessibility Notes
- Password toggle icon button `aria-label="Show password"` / "Hide password"
- Error association via `aria-describedby`
- Submit disabled saat proses (prevent double-click)

---

## 4. Tenant Interface Pages — 16 Pages

### PAGE-007: Profile Show

**URL:** `/profile`  
**Route Name:** `profile.show`  
**Method:** GET  
**Auth:** Authenticated  
**Controller:** `ProfileController@show`  
**FR Reference:** FR-002 (User profile management)

#### Purpose
- Display user profile information (read-only view)
- Show email verification status with visual badge
- Show role badge (Tenant, Admin, Super Admin)
- Provide quick access to edit profile page

#### Layout Structure
```
┌─────────────────────────────────────────────┐
│ Public Nav (unified navbar)                 │
├─────────────────────────────────────────────┤
│ Page Header                                 │
│ - Breadcrumb: Profil                        │
│ - Title: "Profil Saya"                      │
│ - Action: [Edit Profil] button             │
├─────────────────────────────────────────────┤
│ Profile Card (bg-surface-raised)            │
│ ┌─────────────────────────────────────────┐ │
│ │ Avatar (circle, 96×96) + Name Header    │ │
│ │ - First + Last Name (text-xl bold)      │ │
│ │ - Email (text-sm muted)                 │ │
│ │ - Role Badge (color-coded)              │ │
│ ├─────────────────────────────────────────┤ │
│ │ Detail Rows (border-t, divide-y)        │ │
│ │ • Email + Verification Status Badge     │ │
│ │ • Phone Number                          │ │
│ │ • First Name                            │ │
│ │ • Last Name                             │ │
│ ├─────────────────────────────────────────┤ │
│ │ Footer Action (bg-surface)              │ │
│ │ [Edit Profil] button                    │ │
│ └─────────────────────────────────────────┘ │
└─────────────────────────────────────────────┘
```

**Container:** `max-w-3xl mx-auto` (centered, narrower than full-width)  
**Card:** `bg-surface-raised dark:bg-surface-raised-dark shadow-sm rounded-lg`

#### Components Used
- `<x-base-layout variant="full-width">` (§4.1)
- `<x-page-header>` — breadcrumb + title + action slot (§3.26)
- `<x-role-badge :role="$user->role">` — color-coded role badge (NEW component)
- Avatar: conditional render `avatar_path` vs initials circle (pattern from existing show.blade.php)
- Email verification badge: inline badge (success/warning)
- Success message: session flash display (if redirected from edit)

#### Data Requirements
```php
// ProfileController@show
public function show(Request $request)
{
    return view('profile.show', [
        'user' => $request->user(), // Current authenticated user
    ]);
}
```

**User model fields displayed:**
- `first_name` (required)
- `last_name` (optional, show "Belum diisi" if null)
- `email` (required)
- `email_verified_at` (show badge: verified = success, not verified = warning + CTA)
- `phone` (optional, show "Belum diisi" if null)
- `role` (user|admin|superadmin → badge)
- `avatar_path` (optional, show initials circle if null)

#### User Flows

**Flow 1: View profile (happy path)**
1. User navigates to `/profile` from navbar dropdown
2. Page loads with current user data
3. User sees avatar, name, email, phone, role
4. Email verification status clearly visible with badge
5. User can click "Edit Profil" to modify information

**Flow 2: Unverified email prompt**
1. User with unverified email views profile
2. Email row shows warning badge "Belum Verifikasi"
3. CTA button "Verifikasi Email" next to badge
4. Clicking CTA → redirect to `/verify-email` (PAGE-006)

**Flow 3: After profile update**
1. User completes profile edit (PAGE-008)
2. Redirected to `/profile` with success message
3. Toast/alert shows "Profil berhasil diperbarui"
4. Updated data displayed

#### Edge Cases
- **No avatar uploaded:** Show initials circle with first letter of first_name
- **Missing optional fields:** Display "Belum diisi" (gray, muted)
- **Soft-deleted user:** Middleware prevents access (redirect to login)
- **Session status message:** Display success/info alert at top if session flash exists

#### Accessibility Notes
- **Avatar:** `alt="Avatar"` or `aria-label="Inisial [Name]"` for initials circle
- **Role badge:** Use semantic color with sufficient contrast (DESIGN.md §3.4)
- **Email verification badge:** 
  - Verified: `<span role="status" aria-label="Email terverifikasi">✓ Terverifikasi</span>`
  - Not verified: `<span role="status" aria-label="Email belum diverifikasi">⚠ Belum Verifikasi</span>`
- **Edit button:** Clear label "Edit Profil", visible focus indicator
- **Keyboard navigation:** All interactive elements (edit button, verify email CTA) tabbable

---

### PAGE-008: Profile Edit

**URL:** `/profile/edit`  
**Route Name:** `profile.edit`  
**Method:** GET  
**Auth:** Authenticated  
**Controller:** `ProfileController@edit`  
**FR Reference:** FR-002 (User profile management), FR-003 (Email change requires re-verification)

#### Purpose
- Edit user profile information (name, email, phone, avatar)
- Update password
- Delete account permanently
- Four separate forms in sections: Avatar, Profile Info, Password, Delete Account

#### Layout Structure
```
┌─────────────────────────────────────────────┐
│ Public Nav (unified navbar)                 │
├─────────────────────────────────────────────┤
│ Page Header                                 │
│ - Breadcrumb: Profil > Edit                 │
│ - Title: "Edit Profil"                      │
│ - Action: <x-button variant="outline">     │
├─────────────────────────────────────────────┤
│ Status Message (if session flash)           │
├─────────────────────────────────────────────┤
│ Section 1: Avatar Upload (id="avatar")      │
│ ┌─────────────────────────────────────────┐ │
│ │ Form Error Summary (if $errors->any())  │ │
│ │ Current Avatar + File Upload            │ │
│ │ - Preview on select                     │ │
│ │ - [Upload] button                       │ │
│ └─────────────────────────────────────────┘ │
├─────────────────────────────────────────────┤
│ Section 2: Profile Info (id="profile-info") │
│ ┌─────────────────────────────────────────┐ │
│ │ Form Error Summary (if $errors->any())  │ │
│ │ - First Name (required)                 │ │
│ │ - Last Name (optional)                  │ │
│ │ - Email (warning callout below)         │ │
│ │ - Phone (optional)                      │ │
│ │ - [Simpan] button                       │ │
│ └─────────────────────────────────────────┘ │
├─────────────────────────────────────────────┤
│ Section 3: Password (id="password")         │
│ ┌─────────────────────────────────────────┐ │
│ │ Form Error Summary (if $errors->any())  │ │
│ │ - Current Password                      │ │
│ │ - New Password                          │ │
│ │ - Confirm Password                      │ │
│ │ - [Simpan] button                       │ │
│ └─────────────────────────────────────────┘ │
├─────────────────────────────────────────────┤
│ Section 4: Delete Account (id="delete")     │
│ ┌─────────────────────────────────────────┐ │
│ │ Warning text + [Hapus Akun] button      │ │
│ │ → Opens enhanced confirmation modal     │ │
│ └─────────────────────────────────────────┘ │
└─────────────────────────────────────────────┘
```

**Container:** `max-w-7xl mx-auto` with 4 separate cards  
**Each section:** `bg-surface-raised dark:bg-surface-raised-dark shadow-sm rounded-lg p-4 sm:p-8`  
**Inner content:** `max-w-xl` (constrain form width)

#### Components Used
- `<x-base-layout variant="full-width">` (§4.1)
- `<x-page-header>` with back button using `<x-button variant="outline">` (C-2 fix)
- `<x-callout type="warning">` — email change warning (C-4 fix)
- Form error summary block with `role="alert" aria-live="assertive"` (C-7 fix)
- `<x-input-label>`, `<x-text-input>`, `<x-input-error>` (existing Breeze components)
- `<x-primary-button>` (existing)
- Avatar upload: Alpine.js preview + drag-drop zone (C-3 fix)
- Delete modal: Enhanced with checkbox + email confirmation (C-5 fix)

#### Data Requirements
```php
// ProfileController@edit
public function edit(Request $request)
{
    return view('profile.edit', [
        'user' => $request->user(),
    ]);
}
```

**Forms POST to:**
1. Avatar: `POST /profile/avatar` → `ProfileController@updateAvatar`
2. Profile info: `PATCH /profile` → `ProfileController@update`
3. Password: `PUT /profile/password` → `PasswordController@update`
4. Delete: `DELETE /profile` → `ProfileController@destroy`

#### Validation Rules

**Profile Info (ProfileUpdateRequest):**
```php
'first_name' => ['required', 'string', 'max:255'],
'last_name' => ['nullable', 'string', 'max:255'],
'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
'phone' => ['nullable', 'string', 'max:20'],
```

**Password Update:**
```php
'current_password' => ['required', 'current_password'],
'password' => ['required', 'confirmed', 'min:8'],
```

**Avatar Upload:**
```php
'avatar' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:2048'], // 2MB
```

**Account Deletion:**
```php
'password' => ['required', 'current_password'],
'email_confirmation' => ['required', 'email', 'in:' . $user->email], // Must match
'confirmation_checkbox' => ['accepted'], // Must check understanding
```

#### User Flows

**Flow 1: Edit profile info**
1. User clicks "Edit Profil" from profile show page
2. Page loads with 4 sections, profile info form pre-filled
3. User changes first name, clicks Save
4. Success: Redirect to `/profile` with toast "Profil berhasil diperbarui"
5. Error: Form re-displayed with error messages + error summary at top

**Flow 2: Change email (requires re-verification)**
1. User changes email in profile info form
2. Warning callout visible: "Mengubah email memerlukan verifikasi ulang. Akses fitur rental akan diblokir sampai email baru diverifikasi."
3. User submits form
4. Backend: Update email, set `email_verified_at = null`
5. Redirect to `/verify-email` (PAGE-006) to verify new email
6. OTP sent to new email address

**Flow 3: Upload avatar with preview**
1. User clicks file input or drags image to drop zone
2. Alpine.js immediately shows preview thumbnail
3. User sees preview, clicks "Upload" button
4. Form submits via POST to `/profile/avatar`
5. Success: Page reloads with new avatar visible
6. Error: Show error message below upload zone

**Flow 4: Change password**
1. User scrolls to password section
2. Fills current password, new password, confirm password
3. Clicks Save
4. Success: Toast "Password berhasil diperbarui", form cleared
5. Error: Show validation errors (e.g., "Current password salah")

**Flow 5: Delete account (enhanced confirmation)**
1. User clicks red "Hapus Akun" button
2. Modal opens with:
   - Checkbox: "Saya memahami tindakan ini tidak dapat dibatalkan"
   - Account data summary: "X rental aktif, Y reviews akan dihapus"
   - Email confirmation input: "Ketik email Anda untuk konfirmasi"
   - Password input
   - Submit button disabled until checkbox checked AND email matches
3. User checks box, types email, enters password
4. Submit button becomes enabled
5. User clicks "Hapus Akun"
6. Backend soft-deletes user account
7. Logout and redirect to homepage

**Flow 6: Focus management with URL hash**
1. User navigates to `/profile/edit#password`
2. Page loads and auto-scrolls to password section
3. Focus moves to first input in that section

#### Edge Cases
- **Email change while unverified:** Allow change, but immediately redirect to `/verify-email`
- **Avatar upload fails (file too large):** Show error "Ukuran file maksimal 2MB"
- **Incorrect current password:** Show error "Password saat ini salah"
- **Password too weak:** Show validation error "Password minimal 8 karakter"
- **Delete account with active rentals:** Show in modal: "Anda memiliki X rental aktif" + warning
- **Email confirmation mismatch:** Submit button stays disabled, show helper text "Email tidak cocok"

#### Accessibility Notes

**Form Error Announcements (C-7):**
- Each form section includes error summary block at top with `role="alert" aria-live="assertive"`
- Screen reader announces errors immediately
- Per-field errors still shown below each input with `<x-input-error>`

**Focus Management (C-6):**
- Alpine.js manages URL hash navigation
- Each section has `id` attribute: `avatar`, `profile-info`, `password`, `delete`
- Auto-scroll and focus on page load if hash present
- After form submission, focus returns to edited section

**Avatar Upload (C-3):**
- Drop zone is `<label>` wrapping hidden `<input type="file">`
- Keyboard accessible: Tab to drop zone, Enter to open file picker
- Alpine.js preview updates on file select
- Preview has descriptive `alt` text

**Email Change Warning (C-4):**
- `<x-callout type="warning">` immediately below email input
- Always visible, clear consequences explained

**Delete Account Modal (C-5):**
- Modal with full accessibility attributes
- Checkbox + email confirmation + password required
- Submit disabled until all conditions met
- Focus trap, Escape to close, focus restoration

---

### PAGE-009: Tenant Dashboard

**URL:** `/rentals` (serves as dashboard)  
**Route Name:** `rentals.index`  
**Method:** GET  
**Auth:** Authenticated, role:user  
**Controller:** `Tenant\RentalController@index`  
**FR Reference:** FR-096 (View own rentals)

#### Purpose
- Central hub untuk tenant: view all rentals (current + past)
- Quick access to pending actions (upload payment, upload documents)
- Status overview

#### Layout Structure
```
Public Nav (authenticated) + Main Content:
- Welcome message: "Halo, {FirstName}"
- Stat cards row (3 cards):
  - Active Rentals (count)
  - Pending Actions (count)
  - Completed Rentals (count)
- Filters/Tabs: All | Active | Pending | Completed
- Rental cards list (sorted by created_at desc)
- Empty state if no rentals
```

#### Components Used
- `<x-nav-public />` with authenticated state (§3.6) — Public navbar showing tenant-specific dropdown: "Rental Saya", "Profil", "Logout"
- `<x-stat-card />` x 3 — Active Rentals, Pending Actions, Completed Rentals (icon + label + nilai, delta opsional) (§3.31)
- `<x-rental-card />` x N (§3.3)
- `<x-confirm-dialog />` — aksi destruktif dari kartu (mis. cancel rental / hapus) (§3.25)
- `<x-empty-state />` (§3.8)

#### Data Requirements
```php
public function index()
{
    $rentals = auth()->user()->rentals()
        ->with(['kost', 'room', 'payment', 'rentalDocuments'])
        ->latest()
        ->get();
    
    $stats = [
        'active' => $rentals->where('status', 'active')->count(),
        'pending_actions' => $rentals->whereIn('status', ['pending', 'paid'])->count(),
        'completed' => $rentals->where('status', 'completed')->count(),
    ];
    
    return view('tenant.rentals.index', compact('rentals', 'stats'));
}
```

#### User Flow
1. Tenant logs in → redirect to `/rentals`
2. Sees stat cards overview
3. Scrolls rental list
4. Clicks rental card → `/rentals/{id}` (PAGE-008)
5. Or clicks action button on card (e.g., "Upload Bukti Bayar") → `/rentals/{id}/payment`

#### Accessibility Notes
- Stat cards: nilai besar dibaca SR sebagai satu frasa (label + angka), bukan angka terpisah (pola `x-stat-card` §3.31)
- Rental cards: `<article>` + heading `<h2>/<h3>`; aksi tiap kartu adalah link/button bernama jelas, bukan icon-only tanpa `aria-label`
- Tabs/filter status (All | Active | Pending | Completed): pola tab `role="tablist"` + `aria-selected`; keyboard Arrow Left/Right antar tab
- Aksi destruktif lewat `<x-confirm-dialog>` (§3.25): focus trap, Esc close, fokus kembali ke trigger saat tutup
- Empty state: heading + CTA fokus pertama saat tidak ada rental

---

### PAGE-008: Rental Detail (Tenant View) — REDESIGNED v2.0

**URL:** `/rentals/{rental}`  
**Route Name:** `rentals.show`  
**Method:** GET  
**Auth:** Authenticated, role:user, owner  
**Controller:** `Tenant\RentalController@show`  
**FR Reference:** FR-097 (View rental detail), FR-103 (Timeline display), FR-070 (Upload payment), FR-071 (Upload documents), FR-120 (Cancel rental)

**Design Change:** Unified single-page experience. All rental lifecycle stages (payment, documents, timeline, review) visible in one view with section-based organization. Eliminates navigation fragmentation (previously 4-5 page transitions).

#### Purpose
- **Single-page rental management:** All actions accessible from one unified view
- **Clear progress guidance:** Visual progress tracker shows current step and what's next
- **Section-based workflow:** Payment, documents, timeline, review organized as conditional sections
- **Mobile-first:** Camera-native document upload, touch-optimized interactions, thumb-zone CTAs

#### Design Principles
- **Section states:** Active (interactive), Preview (read-only, collapsible), Locked (greyed with clear message)
- **Progressive disclosure:** Show all sections but only active section is interactive based on rental status
- **Context preservation:** No navigation away from detail page (payment/documents inline or modal)
- **Transparent workflow:** Progress tracker + section states eliminate "what's next?" confusion

#### Layout Structure

**Mobile (< 640px):**
```
┌─────────────────────────────────────┐
│ ← Rental #12345           [⋯ Menu]  │ ← App header
├─────────────────────────────────────┤
│ ┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓ │
│ ┃ Step 2 of 4: Upload Documents  ┃ │ ← Sticky progress chip (tap to expand)
│ ┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛ │
│ 📋 Rental Summary (always visible)  │
│ ✓ Payment (preview, collapsible)    │
│ 📋 Documents (ACTIVE, expanded)     │
│ 🔒 Confirmation (locked)            │
│ 📜 Timeline (collapsible)           │
│                                  ┌──┐│
│                               │💬││ ← FAB: Contact owner (fixed)
│                               └──┘│
├─────────────────────────────────────┤
│ ┌───────────────────────────────┐ │ ← Sticky bottom bar
│ │      [⋯ More Actions ▾]       │ │   (Cancel rental in menu)
│ └───────────────────────────────┘ │
└─────────────────────────────────────┘
```

**Desktop (> 1024px):**
```
┌─────────────────────────────────────────────────────────────────┐
│ ← Back                                    Rental #12345   [Menu] │
├─────────────────────────────────────────────────┬───────────────┤
│ Main Content (70%)                              │ Sidebar (30%) │
│                                                 │ ┌───────────┐ │
│ 📋 Rental Summary                               │ │Progress   │ │
│ ✓ Payment (preview)                             │ │Tracker    │ │
│ 📋 Documents (ACTIVE)                           │ │(sticky)   │ │
│ 🔒 Confirmation (locked)                        │ │           │ │
│                                                 │ │● Step 1✓  │ │
│                                                 │ │● Step 2   │ │
│                                                 │ │○ Step 3🔒 │ │
│                                                 │ │○ Step 4🔒 │ │
│                                                 │ └───────────┘ │
│                                                 │ ┌───────────┐ │
│                                                 │ │Timeline   │ │
│                                                 │ │(scroll)   │ │
│                                                 │ └───────────┘ │
│                                                 │ ┌───────────┐ │
│                                                 │ │Contact    │ │
│                                                 │ │Owner      │ │
│                                                 │ └───────────┘ │
│                                                 │ ┌───────────┐ │
│                                                 │ │[Cancel]   │ │
│                                                 │ └───────────┘ │
└─────────────────────────────────────────────────┴───────────────┘
```

#### Components Used

**Existing Components:**
- `<x-status-badge />` — Rental status, document verification status (§3.4)
- `<x-button />` — All CTAs (upload, submit, cancel) (§3.1)
- `<x-modal />` — Payment upload modal, cancel confirmation (§3.10)
- `<x-alert />` — Section state messages, error states (§3.6)

**New Components (to be added to DESIGN.md):**
- `<x-progress-stepper />` — 4-step rental lifecycle tracker (vertical desktop, collapsible mobile)
- `<x-document-card />` — Per-document upload card (camera button, preview, status badge)
- `<x-fab />` — Floating Action Button for contact owner (mobile only)
- `<x-section-state />` — Visual wrapper for active/preview/locked states

#### Section Organization & States

| Section | Pending | Paid | Documents Pending | Confirmed | Active | Completed |
|---------|---------|------|-------------------|-----------|--------|-----------|
| **Progress Tracker** | Step 1 active | Step 2 active | Step 2 active | Step 3 active | Step 3 ✓ | Step 4 active |
| **Rental Summary** | Preview (always) | Preview | Preview | Preview | Preview | Preview |
| **Payment** | 🟢 ACTIVE | 🟡 Preview ✓ | 🟡 Preview ✓ | 🟡 Preview ✓ | 🟡 Preview ✓ | 🟡 Preview ✓ |
| **Documents** | 🔒 Locked | 🟢 ACTIVE | 🟢 ACTIVE | 🟡 Preview ✓ | 🟡 Preview ✓ | 🟡 Preview ✓ |
| **Timeline** | Preview | Preview | Preview | Preview | Preview | Preview |
| **Review** | Hidden | Hidden | Hidden | Hidden | Hidden | 🟢 ACTIVE |
| **Cancel Action** | Active | Active | Active | Disabled | Disabled | Disabled |
| **Contact Owner** | Active (always) | Active | Active | Active | Active | Active |

**Visual State CSS:**
- **🟢 Active:** `border-2 border-primary-500 bg-white shadow-md`
- **🟡 Preview:** `border border-gray-300 bg-gray-50` (collapsible on mobile)
- **🔒 Locked:** `border border-dashed border-gray-400 bg-gray-100 opacity-60` + lock icon + "Available after..." message

#### Data Requirements

```php
// Tenant\RentalController@show
public function show(Rental $rental)
{
    // Authorization: Ensure rental belongs to authenticated tenant
    $this->authorize('view', $rental);
    
    // Eager load all relationships for single-page view
    $rental->load([
        'room.roomType.kost.address',
        'room.roomType.kost.owner', // For contact info
        'payment', // Payment proof and verification status
        'rentalDocuments.documentRequirement', // All required docs
        'statusHistories' => fn($q) => $q->latest(), // Timeline
        'review', // If completed status
    ]);
    
    // Calculate progress
    $totalSteps = 4;
    $currentStep = match($rental->status) {
        'pending' => 1,
        'paid', 'documents_pending' => 2,
        'confirmed' => 3,
        'active' => 3, // Same as confirmed visually
        'completed' => 4,
        'cancelled', 'rejected' => 0, // No active step
    };
    
    // Document upload progress
    $requiredDocs = $rental->room->roomType->kost->documentRequirements;
    $uploadedDocs = $rental->rentalDocuments->filter(fn($d) => $d->file_path !== null);
    $verifiedDocs = $uploadedDocs->filter(fn($d) => $d->verified_at !== null);
    $docProgress = [
        'total' => $requiredDocs->count(),
        'uploaded' => $uploadedDocs->count(),
        'verified' => $verifiedDocs->count(),
    ];
    
    return view('tenant.rentals.show', compact('rental', 'currentStep', 'totalSteps', 'docProgress'));
}
```

#### User Flows

**Flow 1: Upload Payment (Status: pending)**
1. Land on rental detail page
2. See "Payment Required" section (ACTIVE state, primary border)
3. Section shows: Total amount, "Show QRIS Code" collapsible button
4. Tap "📤 Upload Payment Proof" button → Modal opens (90vw mobile, 600px desktop)
5. Modal content:
   - Total amount header
   - "Show QRIS Code" collapsible (QRIS image + download button)
   - File input: "📷 Take Photo or Upload" (triggers camera on mobile via `capture="environment"`)
   - Optional notes textarea
6. Select/capture payment screenshot → Preview shows in modal
7. Tap "Upload" button → AJAX POST to `/rentals/{id}/payment`
8. Success:
   - Modal closes
   - Payment section transitions: ACTIVE → Preview (✓ Verified badge)
   - Document section transitions: Locked → ACTIVE
   - Progress tracker updates: Step 1 ✓, Step 2 now active
   - Toast notification: "Payment proof uploaded"
9. Page does NOT reload (AJAX update + Alpine.js reactivity)

**Flow 2: Upload Documents (Status: paid)**
1. Payment section now Preview (collapsed on mobile, can expand to see proof)
2. Document section now ACTIVE
3. See list of required documents as individual cards:
   - Each card: Document name, empty state placeholder, "📷 Take Photo or Upload" button
4. Tap button on first card (e.g., KTP) → File picker/camera opens
5. Select/capture image → Preview appears in card immediately (no page refresh)
6. Tap "Upload" button on card → AJAX POST to `/rentals/{id}/documents`
7. Success:
   - Card shows checkmark badge "Uploaded"
   - Upload button changes to "Replace Photo"
   - Progress tracker updates: "[1/3]" counter
8. Repeat for remaining documents (no batch upload, per-document flow)
9. After all documents uploaded:
   - Document section shows "Waiting for verification" message
   - Section state remains ACTIVE (tenant can still replace photos)
   - Timeline updates: "Documents uploaded" timestamp

**Flow 3: Cancel Rental (Any status before active)**
1. Mobile: Tap "⋯ More Actions" sticky bottom button → Dropdown menu shows "Cancel Rental"
2. Desktop: See "Cancel Rental" button in sidebar Actions card
3. Tap "Cancel Rental" → Confirmation modal opens
4. Modal content:
   - Warning icon + "Are you sure?"
   - Rental summary (room, dates, amount)
   - Reason textarea (optional)
   - Buttons: [Go Back] [Confirm Cancellation]
5. Tap "Confirm" → AJAX POST to `/rentals/{id}/cancel`
6. Success:
   - Modal closes
   - Page reloads with status=cancelled
   - All sections transition to read-only
   - Timeline shows "Cancelled" timestamp + reason
   - Toast: "Rental cancelled"

**Flow 4: Contact Owner (Always available)**
1. Mobile: Tap floating 💬 FAB (bottom-right, fixed)
2. Desktop: See "Contact Owner" card in sidebar
3. Contact options:
   - 📧 Email → Opens email client (`mailto:`)
   - ☎️ Phone → Opens phone dialer (`tel:`)
   - 💬 WhatsApp → Opens WhatsApp chat (if available)

**Flow 5: View Timeline/History (Any status)**
1. Mobile: Tap "📜 Status Timeline [Expand]" collapsed section
2. Desktop: Timeline visible in sidebar (scrollable)
3. Shows status history: Created → Paid → Documents uploaded → Confirmed → Active → Completed
4. Each entry: Timestamp, actor (tenant/admin), status badge

#### Edge Cases

**Payment Upload Failures:**
- File too large (>5MB): Show inline error below file input, suggest compression
- Invalid file type: Show error "Only images (JPG, PNG) or PDF accepted"
- Network error during upload: Show retry button, preserve selected file in modal
- Deadline expired: Disable upload button, show "Payment deadline passed. Rental auto-cancelled."

**Document Upload Failures:**
- Camera permission denied: Fallback to file picker, show toast "Camera not available"
- Missing required documents: Progress tracker shows "[2/3]", locked section message updates
- Admin rejects document: Card border → red, badge → "✗ Rejected", rejection reason shown, "Re-upload" button replaces "Upload"

**Cancel Rental Edge Cases:**
- Status = active/completed: Cancel button disabled (greyed), tooltip "Cannot cancel active/completed rental"
- Status = cancelled: Cancel button hidden
- Network error: Show error toast, modal stays open, allow retry

**Empty States:**
- No payment uploaded: Payment section shows QRIS + upload button (default state)
- No documents uploaded: Cards show empty state placeholders with camera icons
- No status history: Timeline shows only "Created" entry

#### Accessibility Notes

**Keyboard Navigation:**
- All sections keyboard-focusable (tab order: Progress → Summary → Active section → Preview sections → Actions)
- Modal focus trap (Escape closes, focus returns to trigger button)
- File inputs: Hidden but keyboard-accessible via `<label>` click triggers
- FAB (mobile): `tabindex="0"`, Enter/Space triggers

**Screen Reader:**
- Progress tracker: `role="progressbar"`, `aria-valuenow="{currentStep}"`, `aria-valuemax="4"`
- Section states: 
  - Active: `aria-current="true"`
  - Locked: `aria-disabled="true"`, `aria-describedby="lock-message-{id}"`
  - Preview: Normal navigation, no special ARIA
- Status badges: `aria-label="Payment status: Verified"` (not just color)
- Document cards: `aria-label="KTP upload: Uploaded, awaiting verification"`
- Upload progress: `aria-live="polite"` announces "Uploading... Done."

**Color Contrast:**
- All text: min 4.5:1 (WCAG AA)
- Locked section text: `text-gray-700` on `bg-gray-100` = 4.6:1 ✓
- Error states: `text-red-700` on white = 4.6:1 ✓
- Disabled buttons: 3:1 for non-text (border/background)

**Touch Targets (Mobile):**
- All buttons: min 56px height (primary CTAs)
- Secondary buttons: min 48px height
- Document card tap areas: min 80px height
- FAB: 56×56px (thumb-zone optimized, bottom-right)
- Spacing: 8px gap between adjacent targets

**Responsive Design:**
- Mobile (< 640px): Single column, sticky progress chip, FAB for contact, bottom action bar
- Tablet (640-1024px): Single column centered (70% width), sidebar below main
- Desktop (> 1024px): 70/30 two-column, sticky sidebar (progress + timeline + actions)

#### Implementation Notes

**Alpine.js State Management:**
```javascript
// Simplified per-document upload (no batch complexity)
x-data="{
  currentStep: {{ $currentStep }},
  sections: {
    payment: { state: '{{ $paymentState }}', expanded: false },
    documents: { state: '{{ $documentsState }}', expanded: true },
    timeline: { expanded: false }
  },
  documents: {
    ktp: { file: null, preview: null, uploading: false, uploaded: {{ $ktp->uploaded ?? false }} },
    selfie: { file: null, preview: null, uploading: false, uploaded: {{ $selfie->uploaded ?? false }} },
    // ...per required doc
  },
  
  async uploadDocument(type) {
    const doc = this.documents[type];
    doc.uploading = true;
    
    const formData = new FormData();
    formData.append('document', doc.file);
    formData.append('type', type);
    
    try {
      const response = await fetch('{{ route('tenant.rentals.documents.upload', $rental) }}', {
        method: 'POST',
        body: formData,
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
      });
      
      if (response.ok) {
        doc.uploaded = true;
        this.$dispatch('toast', { type: 'success', message: 'Document uploaded!' });
        // Update progress counter
        this.$dispatch('update-progress');
      }
    } catch (error) {
      this.$dispatch('toast', { type: 'error', message: 'Upload failed. Try again.' });
    } finally {
      doc.uploading = false;
    }
  }
}"
```

**Backend Routes (New/Modified):**
```php
// routes/web.php - Tenant rental routes
Route::middleware(['auth', 'verified'])->prefix('rentals')->name('rentals.')->group(function () {
    Route::get('/{rental}', [RentalController::class, 'show'])->name('show');
    
    // AJAX endpoints for inline uploads (no separate pages)
    Route::post('/{rental}/payment', [RentalController::class, 'uploadPayment'])->name('payment.upload');
    Route::post('/{rental}/documents', [RentalController::class, 'uploadDocument'])->name('documents.upload');
    Route::post('/{rental}/cancel', [RentalController::class, 'cancel'])->name('cancel');
});
```

**Removed Routes:**
- ~~`/rentals/{id}/payment` (GET)~~ → Merged into detail page as modal
- ~~`/rentals/{id}/cancel` (GET)~~ → Merged into detail page as modal

#### Testing Checklist

**Functional Tests:**
- [ ] Payment upload modal opens/closes correctly
- [ ] File upload triggers camera on mobile (playwright camera emulation)
- [ ] Document upload updates progress counter without page reload
- [ ] Section states transition correctly based on rental status
- [ ] Cancel modal shows confirmation, executes cancellation
- [ ] Contact owner FAB/buttons open external apps (email/phone/WhatsApp)

**Responsive Tests:**
- [ ] Mobile (375px): Single column, sticky elements work, FAB visible
- [ ] Tablet (768px): Layout flows correctly, sidebar below main
- [ ] Desktop (1280px): 70/30 layout, sticky sidebar doesn't overflow

**Accessibility Tests:**
- [ ] Keyboard-only navigation works (no mouse)
- [ ] Screen reader announces section states, upload progress
- [ ] Focus management in modals (trap, return on close)
- [ ] Color contrast passes (axe DevTools audit)

**Edge Case Tests:**
- [ ] File upload errors show inline messages
- [ ] Network failures allow retry
- [ ] Deadline expired disables upload
- [ ] Rejected documents show rejection reason + re-upload button

---

### PAGE-009: Payment Page (Upload Proof)

**URL:** `/rentals/{rental}/payment`  
**Route Name:** `rentals.payment.show`  
**Method:** GET  
**Auth:** Authenticated, rental owner  
**Controller:** `Tenant\PaymentController@show`  
**FR Reference:** FR-069 (Display QRIS + bank info), FR-070 (Upload proof), FR-075 (Re-upload)

#### Purpose
- Display payment instructions: QRIS static image + bank account details
- Upload payment proof (image file)
- Show deadline countdown

#### Layout Structure
```
Centered content (max-w-3xl):
- Back button
- Deadline callout (warning): "Bayar sebelum {deadline}"
- Payment amount (large, bold): Rp 4.500.000
- QRIS image (downloadable)
- Bank account details (copy-able)
- Upload proof section:
  - Drag-drop zone or file picker
  - Preview thumbnail after upload
  - [Submit] button
- Instructions text
```

#### Components Used
- `<x-callout type="warning" />` (deadline) (§3.17)
- `<x-countdown />` — deadline countdown (hh:mm:ss, `<60s` `text-error-700`, expired callback disable submit) (§3.20)
- `<x-qris-payment />` — QRIS image + merchant + payment ref, tab bank BCA/BNI/Mandiri, copy-to-clipboard (long-press context) (§3.22)
- Upload bukti bayar: pola `<x-document-upload />` (drag-drop + picker, jpeg/png/pdf ≤5MB, preview, remove) (§3.24)
- `<x-button variant="primary" />` (submit)

#### User Flow
1. Tenant clicks "Upload Bukti Bayar" from rental detail
2. Page loads with QRIS + bank info
3. Tenant transfers via bank/e-wallet
4. Takes screenshot of transfer receipt
5. Uploads screenshot (drag-drop or file picker)
6. Preview shown
7. Clicks "Kirim Bukti Bayar"
8. Backend stores file path in `payments.proof_path`
9. Toast: "Bukti pembayaran berhasil diupload. Tunggu verifikasi admin."
10. Redirect to `/rentals/{id}` with payment section showing "Menunggu verifikasi"

**Re-upload flow (if rejected):**
- Rejection reason displayed
- "Upload Ulang" button visible
- Same upload process, replaces old proof

#### Accessibility Notes
- Deadline countdown `<x-countdown>`: `aria-live="polite"` (per menit; di bawah 60 detik `text-error-700` tetap terbaca teks penuh)
- QRIS image: `alt` deskriptif (mis. "QRIS bayar Rp 4.500.000 dari {bank}"); tombol copy rekening `aria-label` jelas
- Upload zone (<x-document-upload> §3.24): dropzone bisa dioper keyboard (input file focusable), error file (tipe/ukuran) `aria-describedby` + `role="alert"`
- Label form + preview thumbnail: status "sedang upload" diumumkan `aria-live="polite"`; progress bar readable
- Submit button disabled saat upload berjalan / countdown expired (prevent submit lewat deadline)

### PAGE-007: Profile Show

**URL:** `/profile`  
**Route Name:** `profile.show`  
**Method:** GET  
**Auth:** Authenticated  
**Controller:** `ProfileController@show`  
**FR Reference:** FR-002 (User profile management)

#### Purpose
- Display user profile information (read-only view)
- Show email verification status with visual badge
- Show role badge (Tenant, Admin, Super Admin)
- Provide quick access to edit profile page

#### Layout Structure
```
┌─────────────────────────────────────────────┐
│ Public Nav (unified navbar)                 │
├─────────────────────────────────────────────┤
│ Page Header                                 │
│ - Breadcrumb: Profil                        │
│ - Title: "Profil Saya"                      │
│ - Action: [Edit Profil] button             │
├─────────────────────────────────────────────┤
│ Profile Card (bg-surface-raised)            │
│ ┌─────────────────────────────────────────┐ │
│ │ Avatar (circle, 96×96) + Name Header    │ │
│ │ - First + Last Name (text-xl bold)      │ │
│ │ - Email (text-sm muted)                 │ │
│ │ - Role Badge (color-coded)              │ │
│ ├─────────────────────────────────────────┤ │
│ │ Detail Rows (border-t, divide-y)        │ │
│ │ • Email + Verification Status Badge     │ │
│ │ • Phone Number                          │ │
│ │ • First Name                            │ │
│ │ • Last Name                             │ │
│ ├─────────────────────────────────────────┤ │
│ │ Footer Action (bg-surface)              │ │
│ │ [Edit Profil] button                    │ │
│ └─────────────────────────────────────────┘ │
└─────────────────────────────────────────────┘
```

**Container:** `max-w-3xl mx-auto` (centered, narrower than full-width)  
**Card:** `bg-surface-raised dark:bg-surface-raised-dark shadow-sm rounded-lg`

#### Components Used
- `<x-base-layout variant="full-width">` (§4.1)
- `<x-page-header>` — breadcrumb + title + action slot (§3.26)
- `<x-role-badge :role="$user->role">` — color-coded role badge (NEW component)
- Avatar: conditional render `avatar_path` vs initials circle (pattern from existing show.blade.php)
- Email verification badge: `<x-status-badge>` or inline badge (success/warning)
- Success message: session flash display (if redirected from edit)

#### Data Requirements
```php
// ProfileController@show
public function show(Request $request)
{
    return view('profile.show', [
        'user' => $request->user(), // Current authenticated user
    ]);
}
```

**User model fields displayed:**
- `first_name` (required)
- `last_name` (optional, show "Belum diisi" if null)
- `email` (required)
- `email_verified_at` (show badge: verified = success, not verified = warning + CTA)
- `phone` (optional, show "Belum diisi" if null)
- `role` (user|admin|superadmin → badge)
- `avatar_path` (optional, show initials circle if null)

#### User Flows

**Flow 1: View profile (happy path)**
1. User navigates to `/profile` from navbar dropdown
2. Page loads with current user data
3. User sees avatar, name, email, phone, role
4. Email verification status clearly visible with badge
5. User can click "Edit Profil" to modify information

**Flow 2: Unverified email prompt**
1. User with unverified email views profile
2. Email row shows warning badge "Belum Verifikasi"
3. CTA button "Verifikasi Email" next to badge
4. Clicking CTA → redirect to `/verify-email` (PAGE-006)

**Flow 3: After profile update**
1. User completes profile edit (PAGE-008)
2. Redirected to `/profile` with success message
3. Toast/alert shows "Profil berhasil diperbarui"
4. Updated data displayed

#### Edge Cases
- **No avatar uploaded:** Show initials circle with first letter of first_name
- **Missing optional fields:** Display "Belum diisi" (gray, muted)
- **Soft-deleted user:** Middleware prevents access (redirect to login)
- **Session status message:** Display success/info alert at top if session flash exists

#### Accessibility Notes
- **Avatar:** `alt="Avatar"` or `aria-label="Inisial [Name]"` for initials circle
- **Role badge:** Use semantic color with sufficient contrast (DESIGN.md §3.4)
- **Email verification badge:** 
  - Verified: `<span role="status" aria-label="Email terverifikasi">✓ Terverifikasi</span>`
  - Not verified: `<span role="status" aria-label="Email belum diverifikasi">⚠ Belum Verifikasi</span>`
- **Edit button:** Clear label "Edit Profil", visible focus indicator
- **Keyboard navigation:** All interactive elements (edit button, verify email CTA) tabbable

---

### PAGE-008: Profile Edit

**URL:** `/profile/edit`  
**Route Name:** `profile.edit`  
**Method:** GET  
**Auth:** Authenticated  
**Controller:** `ProfileController@edit`  
**FR Reference:** FR-002 (User profile management), FR-003 (Email change requires re-verification)

#### Purpose
- Edit user profile information (name, email, phone, avatar)
- Update password
- Delete account permanently
- Four separate forms in sections: Avatar, Profile Info, Password, Delete Account

#### Layout Structure
```
┌─────────────────────────────────────────────┐
│ Public Nav (unified navbar)                 │
├─────────────────────────────────────────────┤
│ Page Header                                 │
│ - Breadcrumb: Profil > Edit                 │
│ - Title: "Edit Profil"                      │
│ - Action: [Kembali] button (outline)       │
├─────────────────────────────────────────────┤
│ Status Message (if session flash)           │
├─────────────────────────────────────────────┤
│ Section 1: Avatar Upload (id="avatar")      │
│ ┌─────────────────────────────────────────┐ │
│ │ Form Error Summary (if $errors->any())  │ │
│ │ Current Avatar + File Upload            │ │
│ │ - Preview on select                     │ │
│ │ - [Upload] button                       │ │
│ └─────────────────────────────────────────┘ │
├─────────────────────────────────────────────┤
│ Section 2: Profile Info (id="profile-info") │
│ ┌─────────────────────────────────────────┐ │
│ │ Form Error Summary (if $errors->any())  │ │
│ │ - First Name (required)                 │ │
│ │ - Last Name (optional)                  │ │
│ │ - Email (warning callout below)         │ │
│ │ - Phone (optional)                      │ │
│ │ - [Simpan] button                       │ │
│ └─────────────────────────────────────────┘ │
├─────────────────────────────────────────────┤
│ Section 3: Password (id="password")         │
│ ┌─────────────────────────────────────────┐ │
│ │ Form Error Summary (if $errors->any())  │ │
│ │ - Current Password                      │ │
│ │ - New Password                          │ │
│ │ - Confirm Password                      │ │
│ │ - [Simpan] button                       │ │
│ └─────────────────────────────────────────┘ │
├─────────────────────────────────────────────┤
│ Section 4: Delete Account (id="delete")     │
│ ┌─────────────────────────────────────────┐ │
│ │ Warning text + [Hapus Akun] button      │ │
│ │ → Opens enhanced confirmation modal     │ │
│ └─────────────────────────────────────────┘ │
└─────────────────────────────────────────────┘
```

**Container:** `max-w-7xl mx-auto` with 4 separate cards  
**Each section:** `bg-surface-raised dark:bg-surface-raised-dark shadow-sm rounded-lg p-4 sm:p-8`  
**Inner content:** `max-w-xl` (constrain form width)

#### Components Used
- `<x-base-layout variant="full-width">` (§4.1)
- `<x-page-header>` with back button using `<x-button variant="outline">` (NEW - C-2 fix)
- `<x-callout type="warning">` — email change warning (NEW - C-4 fix)
- Form error summary block with `role="alert" aria-live="assertive"` (NEW - C-7 fix)
- `<x-input-label>`, `<x-text-input>`, `<x-input-error>` (existing Breeze components)
- `<x-primary-button>` (existing)
- Avatar upload: Alpine.js preview + drag-drop zone (C-3 fix)
- Delete modal: Enhanced with checkbox + email confirmation (C-5 fix)

#### Data Requirements
```php
// ProfileController@edit
public function edit(Request $request)
{
    return view('profile.edit', [
        'user' => $request->user(),
    ]);
}
```

**Forms POST to:**
1. Avatar: `POST /profile/avatar` → `ProfileController@updateAvatar`
2. Profile info: `PATCH /profile` → `ProfileController@update`
3. Password: `PUT /profile/password` → `PasswordController@update`
4. Delete: `DELETE /profile` → `ProfileController@destroy`

#### Validation Rules

**Profile Info (ProfileUpdateRequest):**
```php
'first_name' => ['required', 'string', 'max:255'],
'last_name' => ['nullable', 'string', 'max:255'],
'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
'phone' => ['nullable', 'string', 'max:20'],
```

**Password Update:**
```php
'current_password' => ['required', 'current_password'],
'password' => ['required', 'confirmed', 'min:8'],
```

**Avatar Upload:**
```php
'avatar' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:2048'], // 2MB
```

**Account Deletion:**
```php
'password' => ['required', 'current_password'],
'email_confirmation' => ['required', 'email', 'in:' . $user->email], // NEW - must match user email
'confirmation_checkbox' => ['accepted'], // NEW - must check understanding
```

#### User Flows

**Flow 1: Edit profile info**
1. User clicks "Edit Profil" from profile show page
2. Page loads with 4 sections, profile info form pre-filled
3. User changes first name, clicks Save
4. Success: Redirect to `/profile` with toast "Profil berhasil diperbarui"
5. Error: Form re-displayed with error messages + error summary at top

**Flow 2: Change email (requires re-verification)**
1. User changes email in profile info form
2. Warning callout visible: "Mengubah email memerlukan verifikasi ulang. Akses fitur rental akan diblokir sampai email baru diverifikasi."
3. User submits form
4. Backend: Update email, set `email_verified_at = null`
5. Redirect to `/verify-email` (PAGE-006) to verify new email
6. OTP sent to new email address

**Flow 3: Upload avatar with preview**
1. User clicks file input or drags image to drop zone
2. Alpine.js immediately shows preview thumbnail
3. User sees preview, clicks "Upload" button
4. Form submits via POST to `/profile/avatar`
5. Success: Page reloads with new avatar visible
6. Error: Show error message below upload zone

**Flow 4: Change password**
1. User scrolls to password section
2. Fills current password, new password, confirm password
3. Clicks Save
4. Success: Toast "Password berhasil diperbarui", form cleared
5. Error: Show validation errors (e.g., "Current password salah")

**Flow 5: Delete account (enhanced confirmation)**
1. User clicks red "Hapus Akun" button
2. Modal opens with:
   - Checkbox: "Saya memahami tindakan ini tidak dapat dibatalkan"
   - Account data summary: "X rental aktif, Y reviews akan dihapus"
   - Email confirmation input: "Ketik email Anda untuk konfirmasi"
   - Password input
   - Submit button disabled until checkbox checked AND email matches
3. User checks box, types email, enters password
4. Submit button becomes enabled
5. User clicks "Hapus Akun"
6. Backend soft-deletes user account
7. Logout and redirect to homepage

**Flow 6: Focus management with URL hash**
1. User navigates to `/profile/edit#password`
2. Page loads and auto-scrolls to password section
3. Focus moves to first input in that section

#### Edge Cases
- **Email change while unverified:** Allow change, but immediately redirect to `/verify-email`
- **Avatar upload fails (file too large):** Show error "Ukuran file maksimal 2MB"
- **Incorrect current password:** Show error "Password saat ini salah"
- **Password too weak:** Show validation error "Password minimal 8 karakter"
- **Delete account with active rentals:** Show in modal: "Anda memiliki X rental aktif. Hubungi admin untuk batalkan rental sebelum menghapus akun" + disable submit
- **Email confirmation mismatch:** Submit button stays disabled, show helper text "Email tidak cocok"

#### Accessibility Notes (Critical - C-6, C-7 fixes)

**Form Error Announcements (C-7):**
- Each form section includes error summary block at top:
  ```blade
  @if ($errors->updateProfile->any())
  <div role="alert" aria-live="assertive" class="mb-6 rounded-md bg-error/10 border border-error/20 p-4">
    <h3 class="text-sm font-semibold text-error-700">
      Terdapat {{ $errors->updateProfile->count() }} kesalahan pada formulir
    </h3>
    <ul class="mt-2 text-sm text-error-700 space-y-1">
      @foreach ($errors->updateProfile->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
  @endif
  ```
- Screen reader announces errors immediately via `aria-live="assertive"`
- Per-field errors still shown below each input with `<x-input-error>`

**Focus Management (C-6):**
- Alpine.js `x-data` on page wrapper:
  ```blade
  <div x-data="{ 
    scrollToSection() { 
      if (window.location.hash) {
        const target = document.querySelector(window.location.hash);
        if (target) {
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
          const firstInput = target.querySelector('input, textarea, select');
          if (firstInput) firstInput.focus();
        }
      }
    } 
  }" x-init="scrollToSection()">
  ```
- Each section has `id` attribute: `id="avatar"`, `id="profile-info"`, `id="password"`, `id="delete"`
- URL hash support: `/profile/edit#password` auto-scrolls to password section
- After form submission success, focus returns to edited section

**Avatar Upload (C-3):**
- Drop zone is `<label>` wrapping hidden `<input type="file">`
- Keyboard accessible: Tab to drop zone, Enter to open file picker
- Alpine.js preview: `x-data="{ preview: null, fileName: null }"`, `@change` updates preview
- Preview has `alt` text describing image

**Email Change Warning (C-4):**
- `<x-callout type="warning">` immediately below email input
- Content: "**Perhatian:** Mengubah email memerlukan verifikasi ulang. Akses fitur rental akan diblokir sampai email baru diverifikasi."
- Always visible when email field is focused

**Delete Account Modal (C-5):**
- Modal `role="dialog"`, `aria-modal="true"`, `aria-labelledby="delete-title"`
- Checkbox: `<input type="checkbox" id="delete-confirm" required>` with clear label
- Email confirmation: `<input type="email" id="email-confirmation" aria-describedby="email-help">`
- Helper text: `<p id="email-help">Ketik email Anda: {{ $user->email }}</p>`
- Account data summary: "Anda memiliki **X rental aktif** dan **Y reviews** yang akan dihapus permanen."
- Submit button: `:disabled="!confirmed || emailMismatch"` with Alpine.js validation
- Focus trap within modal, Escape to close

**Keyboard Navigation:**
- All forms tabbable in logical order
- Submit buttons have visible focus ring
- Modal close button keyboard accessible
- Skip links for screen readers (optional enhancement)

---

### PAGE-010: Tenant — Rental Create (Booking)

**URL:** `/rentals/create` (GET), `/rentals` (POST)  
**Route Name:** `rentals.create`, `rentals.store`  
**Method:** GET, POST  
**Auth:** Authenticated, email verified (FR-062), role:user  
**Controller:** `Tenant\RentalController@create`, `@store`  
**FR Reference:** FR-061 (login required), FR-062 (email verified), FR-063 (pilih room type + price scheme), FR-064 (hanya room available), FR-065 (durasi), FR-066 (total otomatis), FR-067 (simpan rental pending + snapshot), FR-068 (payment deadline +48 jam)

> **Catatan route:** Route belum terdaftar di `routes/web.php` (masih komentar placeholder COMP-006) — definisi riil ada di ARCHITECTURE.md §6.1 baris 943-944: middleware `auth`, `verified`, `role:user`. URL query `?kost_id={id}&room_type_id={id}` berasal dari PAGE-003 Flow 3/4.

#### Purpose
- Form booking: pilih kamar/price scheme → tentukan tanggal + durasi → lihat total realtime → konfirmasi
- Menjamin email verified sebelum membuat rental (FR-006/FR-062) — middleware `verified` blok dulu via modal PAGE-006D
- Simpan record rental status `Pending` + snapshot data (FR-067) dan set payment deadline +48 jam (FR-068)

#### Layout Structure
```
2-column:
- Left (70%):
  - x-booking-form (§3.23):
    - Pilih Room Type (radio) — hanya kamar available (FR-064)
    - Price scheme aktif per room type + durasi (FR-063, FR-065)
    - Tanggal mulai (min today+4, max today+30 — ADR-016) + durasi bulan
    - Kalkulasi realtime: subtotal = price × duration; total = subtotal + security deposit (FR-066)
  - Informasi kost ringkas (nama, alamat, rating)
- Right (30%, sticky):
  - Ringkasan pesanan: kamar, tanggal, durasi, total
  - [Konfirmasi Booking] x-button primary
```

#### Components Used
- `<x-booking-form />` — radio kamar, min today+4/max today+30 (ADR-016), computed durasi/subtotal/total realtime, ringkasan sticky, submit loading (§3.23)
- `<x-confirm-dialog />` — konfirmasi akhir sebelum submit (ringkasan + snapshot harga) (§3.25)
- `<x-kost-card />` mini — info kost di sidebar (§3.3)
- `<x-rating />` — rating kost (§3.29)
- `<x-callout type="warning" />` — peringatan kamar penuh / harga berubah (§3.17)
- `<x-countdown />` tidak dipakai di halaman ini — deadline di-set setelah rental dibuat (FR-068) dan ditampilkan di PAGE-009

#### Data Requirements
```php
public function create(Request $request)
{
    $kost = Kost::findOrFail($request->query('kost_id'))
        ->load(['address', 'images' => fn($q) => $q->where('is_thumbnail', true)]);
    abort_if($kost->status !== 'active', 404); // FR-022

    $roomTypes = $kost->roomTypes()->with([
        'priceSchemes' => fn($q) => $q->where('is_active', true),
    ])->withCount(['rooms' => function ($q) {
        $q->where('status', 'available'); // availability real-time (ADR-017/018)
    }])->get();

    return view('tenant.rentals.create', compact('kost', 'roomTypes'));
}

// store: transaksional + SELECT ... FOR UPDATE (ADR-010)
// 1. Kunci room: Room::where('id', $roomId)->lockForUpdate()->first()
// 2. Validasi ulang availability + harga (dalam transaksi)
// 3. create rental (status pending, snapshot: room_type_id, price_scheme_id,
//    start_date, end_date, price × duration, security_deposit, grand_total) — FR-067
// 4. Set payment deadline: payments.expired_at = now() + 48 jam (FR-068)
// 5. Room status → reserved (FR-067)
```

**Eager Loading:** `address`, `images` (thumbnail), `roomTypes.priceSchemes` (active), `rooms` count available

#### Validation Rules
```php
'kost_id'         => ['required', 'exists:kosts,id'],
'room_type_id'    => ['required', 'exists:room_types,id'],
'room_id'         => ['required', 'exists:rooms,id'],
'price_scheme_id' => ['required', 'exists:price_schemes,id'],
'start_date'      => ['required', 'date', 'after_or_equal:today+4', 'before_or_equal:today+30'], // ADR-016
'duration'        => ['required', 'integer', 'min:1', 'max:24'],
```
- Form Request `CreateRentalRequest`; otorisasi: rental milik tenant + email verified (FR-062)
- Backend hitung total ulang — jangan percaya nilai client (FR-066)

#### User Flows

**Flow 1: Happy path (tenant verified)**
1. Tenant klik "Book Now" di PAGE-003 → `/rentals/create?kost_id={id}&room_type_id={rtid}`
2. Form pre-populated: kost + room type terpilih
3. Pilih kamar available (radio, FR-064) + price scheme
4. Pilih start_date (min today+4) + durasi
5. Total realtime tampil (FR-066)
6. Klik "Konfirmasi Booking" → x-confirm-dialog rangkum pesanan
7. Submit → rental `Pending`, payment deadline +48 jam (FR-068)
8. Redirect `/rentals/{id}` (PAGE-008) + toast "Rental berhasil dibuat. Selesaikan pembayaran sebelum {deadline}"

**Flow 2: Belum verified email**
1. Tenant belum verified mencoba akses `/rentals/create`
2. Middleware `verified` blok → redirect back + flash `verify_email_prompt`
3. Modal PAGE-006D → `/verify-email` (PAGE-006) → verified → ulangi create

**Flow 3: Room habis di tengah transaksi**
1. Kamar terlihat available, tenant pilih, kirim form
2. `SELECT...FOR UPDATE` (ADR-010) menemukan room sudah di-reserved tenant lain
3. Error inline: "Kamar sudah dipesan" → pilih kamar lain

#### Edge Cases
- Kamar penuh semua: form tampilkan "Kamar penuh" (FR-064); submit disabled
- start_date lewat (≤ today+3): input disabled oleh x-booking-form (ADR-016)
- Harga berubah antara buka form & submit: validasi ulang server; x-callout warning "Harga berubah" + konfirmasi ulang
- Kost non-active saat submit: 404 / "Kost tidak tersedia" (FR-022)
- Durasi > 24 bulan atau di luar skema price scheme: validasi `max:24`
- `room_type_id` tanpa kamar available: pilihan kosong + pesan

#### Accessibility Notes
- Radio kamar/price scheme: `fieldset` + `legend`, focus terlihat, Arrow keys antar opsi
- Input tanggal: label jelas + `aria-describedby` untuk constraint (min today+4, ADR-016)
- Ringkasan total: `aria-live="polite"` saat berubah akibat input durasi
- `x-confirm-dialog` (§3.25): focus trap, initial focus tombol batal, Esc close, restore focus ke trigger
- Error inline `text-error-700` terhubung input via `aria-describedby`; submit disabled saat loading (label "Menyimpan...")

---

### PAGE-013: Tenant — Review Create

**URL:** `/rentals/{rental}/reviews` (GET, POST)  
**Route Name:** `rentals.reviews.create`, `rentals.reviews.store`  
**Method:** GET, POST  
**Auth:** Authenticated, role:user, rental owner  
**Controller:** `Tenant\ReviewController@create`, `@store`  
**FR Reference:** FR-060 (display reviews), FR-105 (eligibility: rental Completed + milik tenant + belum ada review), FR-106 (submit review: kost_rating/room_rating 1-5, minimal 1), FR-107 (upload gambar → JSON array), FR-108 (validasi rating)

> **Catatan:** Route `rentals.reviews.*` lihat ARCHITECTURE.md §6.1 baris 950-951. Form review hanya untuk rental `completed`; jika sudah ada review, jangan tampilkan form (FR-105). Edit review TIDAK punya route/FR tersendiri di scope ini — enhancement; user salah input harus hubungi admin.

#### Purpose
- Form review pasca rental selesai: rating kost + rating kamar + komentar + foto bukti
- Minimal SATU rating (kost atau kamar) harus diisi (FR-106/FR-108)

#### Layout Structure
```
Tenant layout — centered card (max-w-2xl):
- Judul: "Beri Penilaian" + info ringkas kost/rental
- x-rating-input: Rating Kost (1-5) + Rating Kamar (1-5) — bintang terpisah
- Komentar kost (textarea) + Komentar kamar (textarea)
- Upload foto bukti (x-document-upload, max 5 gambar, JSON array — FR-107)
- [Kirim Review] x-button primary
```

#### Components Used
- `<x-rating-input />` — bintang interaktif 1-5, hover preview, `aria-pressed`, label "Berikan N bintang" (§3.29)
- Review form: pola form standar + `<x-button variant="primary" />` (§3.1/§3.2) — `x-review-form` masih draft, gunakan pola form + x-button
- `<x-document-upload />` — foto bukti (jpeg/png/jpg ≤5MB, max 5 file, preview) (§3.24)
- `<x-status-badge />` — status rental completed (§3.4)
- `<x-confirm-dialog />` — konfirmasi submit (§3.25)

#### Data Requirements
```php
public function create(Rental $rental)
{
    abort_if($rental->tenant_id !== auth()->id(), 403);  // owner only
    abort_if($rental->status !== 'completed', 403);      // FR-105
    abort_if($rental->review()->exists(), 403);          // sudah ada review (FR-105)
    return view('tenant.reviews.create', compact('rental'));
}

public function store(ReviewRequest $request, Rental $rental)
{   // FR-106: simpan kost_rating, kost_comment, room_rating, room_comment
    // FR-107: images disimpan sebagai JSON array (maks 5)
    // Redirect /rentals/{rental} + toast "Review berhasil dikirim"
}
```

**Eager Loading:** `rental.kost`, `rental.room` (untuk label)

#### Validation Rules
```php
'kost_rating'  => ['nullable', 'integer', 'min:1', 'max:5'],
'kost_comment' => ['nullable', 'string', 'max:2000'],
'room_rating'  => ['nullable', 'integer', 'min:1', 'max:5'],
'room_comment' => ['nullable', 'string', 'max:2000'],
'images'       => ['nullable', 'array', 'max:5'],
'images.*'     => ['image', 'mimes:jpeg,png,jpg', 'max:5120'],
// FR-108: minimal satu rating — rule custom required_without_all(kost_rating, room_rating)
```

#### User Flows

**Flow 1: Happy path**
1. Dari PAGE-008 (rental Completed) → "Tulis Review" → `/rentals/{rental}/reviews`
2. Isi rating kost / rating kamar (≥1 wajib) + komentar opsional
3. Upload foto bukti (opsional, max 5)
4. Submit → review tersimpan; tampil di kost detail (FR-060/FR-109) + avg rating dihitung ulang (FR-110)

**Flow 2: Hanya satu rating diisi**
1. User isi kost_rating saja (room_rating kosong)
2. Validasi lolos (minimal 1 rating, FR-108) → review tersimpan; room_rating null

#### Edge Cases
- Rental bukan `completed` → 403 "Review hanya untuk rental yang selesai" (FR-105)
- Rental bukan milik tenant → 403
- Sudah ada review → 403 / arahkan ke detail (1 review per rental, FR-105)
- Submit tanpa rating → error custom "Minimal isi satu rating (kost atau kamar)" (FR-108)
- Foto > 5 / > 5MB → error per file
- Edit review belum tersedia (tidak ada route/FR di scope ini) — enhancement

#### Accessibility Notes
- `x-rating-input` (§3.29): bintang adalah tombol (`aria-pressed`), label "Berikan N bintang"; preview hover dibaca via `aria-live`
- Setiap textarea berlabel + `aria-describedby` error; error inline `text-error-700`
- Upload foto: dropzone keyboard-accessible; error `role="alert"`
- Submit button disabled saat proses; label "Mengirim..."
- Heading h1 + struktur form semantik (label-field terhubung)

---

### PAGE-010A: Admin — Rental Verification (Detail) — REDESIGNED v2.0

**URL:** `/admin/rentals/{rental}`  
**Route Name:** `admin.rentals.show`  
**Method:** GET  
**Auth:** Authenticated, role:admin, kost owner  
**Controller:** `Admin\RentalManagementController@show`  
**FR Reference:** FR-098 (View rentals for own kost), FR-099 (View rental detail), FR-078 (Verify payment), FR-079 (Reject payment), FR-088 (Approve documents), FR-089 (Reject documents)

**Design Change:** Unified single-page verification experience. Inline approval/rejection actions eliminate modal fatigue (previously 4-6 modal-reload cycles per rental). Optimistic UI provides instant feedback without page reloads.

#### Purpose
- **Single-page rental verification:** Payment and document verification accessible from one unified view
- **Inline actions:** Approve/reject directly on document cards, no modals (except rejection reason textarea)
- **Optimistic UI:** Instant visual feedback, server validation happens in background
- **Mobile-first:** Touch-optimized verification, bulk actions on desktop

#### Design Principles
- **Inline verification:** No modal interruptions, rejection reason expands below card
- **Optimistic updates:** Show success/error immediately, rollback only on server error (<1% cases)
- **Bulk actions (desktop):** "Approve All" / "Reject Selected" for efficient multi-document verification
- **Section-based:** Payment verification → Document verification → Timeline → Actions

#### Layout Structure

**Mobile (< 640px):**
```
┌─────────────────────────────────────┐
│ ← Rentals          Rental #12345    │
├─────────────────────────────────────┤
│ ┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓ │
│ ┃ Verification: Pending Documents┃ │ ← Sticky status chip
│ ┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛ │
│ 🏠 Rental Info (tenant contact)     │
│ 💳 Payment Verification (if pending)│
│ 📋 Document Verification [2/3]      │
│    [Bulk Actions ▼] ← Dropdown      │
│    - Document cards (vertical)      │
│ 📜 Timeline (collapsible)           │
└─────────────────────────────────────┘
```

**Desktop (> 1024px):**
```
┌─────────────────────────────────────────────────────────────────┐
│ ← Rentals List                         Rental #12345 Verification│
├─────────────────────────────────────────────────┬───────────────┤
│ Main Content (70%)                              │ Sidebar (30%) │
│                                                 │ ┌───────────┐ │
│ 🏠 Rental Info                                  │ │Status     │ │
│ 💳 Payment Verification (if pending)            │ │Pending    │ │
│ 📋 Document Verification              [Actions] │ │Docs       │ │
│    [Approve All] [Reject Selected]              │ │           │ │
│    - Document cards (3-col grid)                │ │2 of 3     │ │
│                                                 │ │verified   │ │
│                                                 │ └───────────┘ │
│                                                 │ ┌───────────┐ │
│                                                 │ │Timeline   │ │
│                                                 │ │(scroll)   │ │
│                                                 │ └───────────┘ │
│                                                 │ ┌───────────┐ │
│                                                 │ │Contact    │ │
│                                                 │ │Tenant     │ │
│                                                 │ └───────────┘ │
└─────────────────────────────────────────────────┴───────────────┘
```

#### Components Used

**Existing Components:**
- `<x-status-badge />` — Verification status, document states (§3.4)
- `<x-button />` — Approve/reject actions (§3.1)
- `<x-alert />` — Success/error messages (§3.6)
- `<x-toast />` — Optimistic UI feedback (§3.6)

**New Components (to be added to DESIGN.md):**
- `<x-verification-card />` — Document card with inline approve/reject actions
- `<x-inline-rejection-form />` — Expandable textarea for rejection reason (no modal)

#### Section Organization

| Section | Payment Pending | Documents Pending | All Verified |
|---------|-----------------|-------------------|--------------|
| **Rental Info** | Visible (always) | Visible | Visible |
| **Payment Verification** | 🟢 ACTIVE | 🟡 Approved ✓ | 🟡 Approved ✓ |
| **Document Verification** | Hidden (locked) | 🟢 ACTIVE | 🟡 All Verified ✓ |
| **Timeline** | Visible | Visible | Visible |

**Visual States:**
- **🟢 Active (pending verification):** `border-yellow-300 bg-white` + approve/reject buttons enabled
- **🟡 Verified:** `border-green-500 bg-green-50` + ✓ badge, buttons hidden
- **🔴 Rejected:** `border-red-500 bg-red-50` + ✗ badge, rejection reason shown

#### Data Requirements

```php
// Admin\RentalManagementController@show
public function show(Rental $rental)
{
    // Authorization: Ensure rental belongs to admin's kost
    $this->authorize('manageRental', $rental);
    
    // Eager load for single-page verification view
    $rental->load([
        'room.roomType.kost', // Kost info
        'user', // Tenant info (name, email, phone)
        'payment', // Payment proof and status
        'rentalDocuments.documentRequirement', // All documents
        'statusHistories' => fn($q) => $q->latest(),
    ]);
    
    // Verification stats
    $verificationStats = [
        'payment_status' => $rental->payment?->verified_at ? 'verified' : 'pending',
        'documents_total' => $rental->rentalDocuments->count(),
        'documents_verified' => $rental->rentalDocuments->where('verified_at', '!=', null)->count(),
        'documents_pending' => $rental->rentalDocuments->where('verified_at', null)->where('file_path', '!=', null)->count(),
    ];
    
    return view('admin.rentals.show', compact('rental', 'verificationStats'));
}
```

#### User Flows

**Flow 1: Verify Payment (Status: pending with payment proof uploaded)**
1. Admin opens rental detail
2. See "Payment Verification" section (ACTIVE state, yellow border)
3. View payment proof image (click → lightbox modal for detailed view)
4. Read notes if tenant provided
5. Decision:
   
   **Option A - Approve:**
   - Tap "✓ Approve" button
   - Button shows spinner (optimistic UI)
   - AJAX POST to `/admin/rentals/{id}/payment/approve`
   - Success (instant, no page reload):
     - Badge changes to "✓ Approved"
     - Section border → green
     - Button disappears
     - Toast: "Payment approved"
     - Timeline updates (via Alpine.js reactivity)
     - Document section unlocks (if was locked)
   
   **Option B - Reject:**
   - Tap "✗ Reject" button
   - Rejection form expands inline below image (animated slide-down)
   - Form content:
     - Textarea: "Rejection reason" (min 10 chars, required)
     - Buttons: [Cancel] [Confirm Rejection]
   - Admin types reason: "Bank reference number invalid"
   - Tap "Confirm Rejection"
   - AJAX POST to `/admin/rentals/{id}/payment/reject` with reason
   - Success:
     - Badge changes to "✗ Rejected"
     - Section border → red
     - Rejection reason displayed below image
     - Approve/reject buttons disappear
     - Toast: "Payment rejected, tenant notified"
     - Timeline updates

**Flow 2: Verify Documents (Status: paid, documents uploaded)**
1. Payment already verified → Payment section in Preview state (collapsed)
2. Document Verification section now ACTIVE
3. See document cards (3 cards in grid on desktop, vertical stack on mobile)
4. Each card shows:
   - Document type label (KTP, Selfie, Family Card)
   - Image preview (click → lightbox)
   - Upload timestamp
   - Approve/Reject buttons (if pending)

5. **Individual verification (mobile + desktop):**
   - Admin reviews first document (KTP)
   - Image quality good, information matches
   - Tap "✓ Approve" button
   - Optimistic UI (instant):
     - Button → spinner → disappears
     - Card border → green
     - Badge "✓ Verified" appears top-right
     - Toast: "Document approved"
   - AJAX completes in background (200ms), no visible change
   - Repeat for remaining documents

6. **Bulk approval (desktop only):**
   - Admin reviews all 3 documents
   - All valid and clear
   - Tap "Approve All" button in section header
   - Confirmation prompt: "Approve all 3 documents?"
   - Tap "Confirm"
   - All cards instantly transition to verified state
   - Single toast: "All documents approved"
   - AJAX batch request to server

7. **Rejection flow (inline, no modal):**
   - Admin sees blurry KTP image
   - Tap "✗ Reject" button
   - Rejection form expands below card (animated)
   - Form has:
     - Textarea (auto-focused): "Rejection reason"
     - Character counter: "0/500"
     - Buttons: [Cancel] [Confirm Rejection]
   - Admin types: "Photo is blurry, ID number not visible. Please upload clearer image."
   - Tap "Confirm Rejection"
   - Optimistic UI:
     - Card border → red
     - Badge "✗ Rejected" appears
     - Rejection reason displayed below image
     - Buttons disappear
     - Toast: "Document rejected, tenant notified"
   - Form collapses
   - Server validates in background, sends email to tenant

**Flow 3: Mixed Verification (some approved, some rejected)**
1. Admin approves KTP ✓
2. Admin approves Selfie ✓
3. Admin rejects Family Card ✗ (reason: "Document expired")
4. Final state:
   - Verification stats: "[2/3 verified]"
   - Rental status remains "documents_pending"
   - Tenant sees rejected document with reason
   - Tenant re-uploads Family Card
   - Cycle repeats (admin verifies new upload)

**Flow 4: Contact Tenant**
1. See "Contact Tenant" card in sidebar (desktop) or section (mobile)
2. Tenant info displayed: Name, email, phone
3. Action buttons:
   - 📧 Email → Opens email client
   - ☎️ Phone → Opens phone dialer
   - 💬 WhatsApp → Opens WhatsApp chat

#### Edge Cases

**Payment Verification:**
- Payment proof missing: Section shows "Waiting for tenant to upload proof"
- Image failed to load: Show placeholder + "Image unavailable" message
- Network error during approval: Show error toast, button returns to enabled state
- Concurrent admin action: Server returns 409 Conflict, show "Already verified by another admin"

**Document Verification:**
- Document not uploaded yet: Card shows "Pending upload" badge (greyed, no actions)
- Invalid file format (backend validation failed): Show error "Document type not supported"
- All documents rejected: Rental status remains "documents_pending", tenant must re-upload all
- Tenant re-uploads during admin review: Show notification "Tenant uploaded new version. Refresh to review."

**Bulk Actions:**
- "Approve All" with mix of pending/verified: Only approve pending docs (skip already verified)
- "Reject Selected" with no selection: Disable button, show tooltip "Select documents to reject"
- Network timeout during bulk action: Rollback optimistic updates, show error "Some documents failed, please retry individually"

**Optimistic UI Rollback:**
- Server returns 500 error: Revert card to pending state, show error toast
- Validation failed (e.g., reason too short): Revert, show inline error below textarea
- User refreshes during AJAX: Page reloads with server state (correct source of truth)

#### Accessibility Notes

**Keyboard Navigation:**
- All buttons keyboard-focusable (tab order: Payment approve/reject → Documents → Timeline)
- Rejection textarea auto-focused when form expands
- Escape key collapses rejection form (returns focus to reject button)
- Enter in textarea submits rejection (if min length met)

**Screen Reader:**
- Document cards: `aria-label="KTP document: Pending verification"`
- Verification buttons: `aria-label="Approve KTP document"` (not just icon)
- Status badges: `aria-label="Document status: Verified"` (color + text)
- Rejection form: `aria-describedby="rejection-hint"` → "Minimum 10 characters required"
- Optimistic updates: `aria-live="polite"` announces "Document approved" (via toast)

**Color Contrast:**
- Pending state: `border-yellow-300` meets 3:1 for UI elements ✓
- Approved badge: `bg-green-50 text-green-700` = 4.6:1 ✓
- Rejected badge: `bg-red-50 text-red-700` = 4.6:1 ✓
- Disabled buttons: `opacity-50` with text-gray-600 = 3.2:1 (acceptable for disabled state)

**Touch Targets (Mobile):**
- Approve/Reject buttons: 48×48px min (stacked vertically on mobile)
- Document cards: Full-width tap area for image (opens lightbox)
- Rejection textarea: Auto-expands to fit content, min 80px height
- Bulk actions dropdown: 56px height trigger button

**Responsive Design:**
- Mobile (< 640px): Single column, vertical card stack, dropdown bulk actions
- Tablet (640-1024px): 2-column document grid, inline bulk actions
- Desktop (> 1024px): 3-column document grid, sidebar with stats/timeline/contact

#### Implementation Notes

**Alpine.js State Management:**
```javascript
x-data="{
  verifying: false,
  documents: @json($rental->rentalDocuments),
  
  async approveDocument(docId) {
    // Optimistic UI update
    const doc = this.documents.find(d => d.id === docId);
    doc.verified_at = new Date().toISOString();
    doc.verifying = true;
    
    try {
      const response = await fetch(`/admin/rentals/documents/${docId}/approve`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
      });
      
      if (!response.ok) throw new Error('Server error');
      
      // Success: Already updated optimistically
      this.$dispatch('toast', { type: 'success', message: 'Document approved' });
      
    } catch (error) {
      // Rollback optimistic update
      doc.verified_at = null;
      this.$dispatch('toast', { type: 'error', message: 'Approval failed. Try again.' });
    } finally {
      doc.verifying = false;
    }
  },
  
  rejectingDoc: null,
  rejectionReason: '',
  
  startReject(docId) {
    this.rejectingDoc = docId;
    this.rejectionReason = '';
    this.$nextTick(() => this.$refs.rejectionTextarea?.focus());
  },
  
  async confirmReject(docId) {
    if (this.rejectionReason.length < 10) {
      alert('Rejection reason must be at least 10 characters');
      return;
    }
    
    const doc = this.documents.find(d => d.id === docId);
    doc.rejected_at = new Date().toISOString();
    doc.rejection_reason = this.rejectionReason;
    
    try {
      const response = await fetch(`/admin/rentals/documents/${docId}/reject`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ reason: this.rejectionReason })
      });
      
      if (!response.ok) throw new Error('Server error');
      
      this.rejectingDoc = null;
      this.$dispatch('toast', { type: 'success', message: 'Document rejected' });
      
    } catch (error) {
      // Rollback
      doc.rejected_at = null;
      doc.rejection_reason = null;
      this.$dispatch('toast', { type: 'error', message: 'Rejection failed. Try again.' });
    }
  }
}"
```

**Backend Routes (AJAX endpoints):**
```php
// routes/web.php - Admin rental verification routes
Route::middleware(['auth', 'role:admin'])->prefix('admin/rentals')->name('admin.rentals.')->group(function () {
    Route::get('/', [RentalManagementController::class, 'index'])->name('index');
    Route::get('/{rental}', [RentalManagementController::class, 'show'])->name('show');
    
    // Payment verification (AJAX)
    Route::post('/{rental}/payment/approve', [RentalManagementController::class, 'approvePayment'])->name('payment.approve');
    Route::post('/{rental}/payment/reject', [RentalManagementController::class, 'rejectPayment'])->name('payment.reject');
    
    // Document verification (AJAX)
    Route::post('/documents/{document}/approve', [RentalManagementController::class, 'approveDocument'])->name('documents.approve');
    Route::post('/documents/{document}/reject', [RentalManagementController::class, 'rejectDocument'])->name('documents.reject');
    
    // Bulk actions (desktop)
    Route::post('/{rental}/documents/approve-all', [RentalManagementController::class, 'approveAllDocuments'])->name('documents.approve-all');
});
```

**Controller Actions:**
```php
// Admin\RentalManagementController
public function approveDocument(RentalDocument $document)
{
    $this->authorize('manageRental', $document->rental);
    
    $document->update([
        'verified_at' => now(),
        'verified_by' => auth()->id(),
    ]);
    
    // Auto-confirm rental if all documents verified
    if ($document->rental->allDocumentsVerified()) {
        $document->rental->update(['status' => 'confirmed']);
        event(new RentalConfirmed($document->rental));
    }
    
    return response()->json(['success' => true]);
}

public function rejectDocument(RentalDocument $document, Request $request)
{
    $request->validate([
        'reason' => 'required|string|min:10|max:500',
    ]);
    
    $this->authorize('manageRental', $document->rental);
    
    $document->update([
        'verified_at' => null,
        'rejection_reason' => $request->reason,
        'rejected_at' => now(),
        'rejected_by' => auth()->id(),
    ]);
    
    // Notify tenant via email
    event(new DocumentRejected($document));
    
    return response()->json(['success' => true]);
}
```

#### Testing Checklist

**Functional Tests:**
- [ ] Payment approval updates status without page reload
- [ ] Payment rejection shows inline reason form, submits correctly
- [ ] Document approval transitions card to verified state
- [ ] Document rejection shows reason, tenant receives email
- [ ] Bulk "Approve All" verifies all pending documents
- [ ] Optimistic UI rollback works on server error

**Responsive Tests:**
- [ ] Mobile (375px): Vertical stack, dropdown bulk actions
- [ ] Tablet (768px): 2-column grid, inline bulk actions
- [ ] Desktop (1280px): 3-column grid, sidebar visible

**Accessibility Tests:**
- [ ] Keyboard-only navigation works
- [ ] Screen reader announces approval/rejection
- [ ] Focus management in rejection forms
- [ ] Color contrast passes (axe DevTools)

**Edge Case Tests:**
- [ ] Concurrent verification shows conflict message
- [ ] Network timeout allows retry
- [ ] Mixed verification (some approved, some rejected) updates status correctly
- [ ] Tenant re-upload during admin review shows notification

---

## 5. Admin Interface Pages — 21 Pages

### PAGE-011: Admin — Kost Create/Edit (Submission)

**URL:** `/admin/kosts/create` (GET), `/admin/kosts` (POST), `/admin/kosts/{kost}/edit` (GET), `/admin/kosts/{kost}` (PATCH), `/admin/kosts/{kost}/submit` (POST)  
**Route Name:** `admin.kosts.create`, `admin.kosts.store`, `admin.kosts.edit`, `admin.kosts.update`, `admin.kosts.submit`  
**Method:** GET, POST, PATCH  
**Auth:** Authenticated, role:admin  
**Controller:** `Admin\KostController`  
**FR Reference:** FR-014 (create draft), FR-015 (update draft/rejected), FR-016 (submit for review), FR-017 (validasi data wajib sebelum submit), FR-019 (lihat rejected_reason), FR-020 (revisi rejected → draft, clear reason), FR-021 (publish approved)

> **Implementation note (2026-08-24):** Original spec described 4-step stepper submission flow. Actual COMP-003 implementation uses hub-and-spoke navigation pattern: kost show page (`/admin/kosts/{id}`, PAGE-020) displays 7 configuration section cards, each linking to dedicated config page (PAGE-014—PAGE-019). Stepper flow deferred future iteration. Current flow: Create kost draft → Configure via section cards → Submit review.

> **Catatan inventori:** PAGE-011 adalah referensi lengkap untuk entry "Kost CRUD (create, edit)" dalam 21 Admin pages (§1.1) — TIDAK menambah jumlah. Kost Configuration 8 halaman (info, address, images, categories, facilities/rules, QRIS, bank, documents) tetap summarized.

#### Purpose
- Alur pengajuan kost 4 langkah (DESIGN.md §5.1b): Detail Kost → Foto & Media → Fasilitas & Aturan → Review & Kirim
- Simpan draft (FR-014), update draft/rejected (FR-015/FR-020), submit `draft` → `pending_review` (FR-016)
- Saat edit kost Rejected: tampilkan alasan via x-callout + tombol "Perbaiki & Kirim Ulang"
- Kost Approved: tombol "Publikasikan Sekarang" (`PublishKost`, FR-021) → `active`

#### Layout Structure
```
Admin layout (sidebar):
- x-page-header: breadcrumb (Kost → Buat/Edit) + judul + aksi kontekstual
- x-callout error (jika rejected_reason ada): alasan penolakan + tombol "Perbaiki & Kirim Ulang" (FR-020)
- x-stepper horizontal 4 langkah (§5.1b):
  [Detail Kost] → [Foto & Media] → [Fasilitas & Aturan] → [Review & Kirim]
- Panel aktif per langkah (validasi per langkah; tombol Kembali/Lanjut)
```

**Navigation:** Admin sidebar (`layouts/admin.blade.php`) with role-specific menu items (Admin vs Super Admin).

#### Components Used
- `<x-page-header />` — breadcrumb + judul + aksi (§3.26)
- `<x-stepper />` horizontal — 4 langkah submission, `aria-current="step"`, done `bg-success-700`, validasi per langkah (§3.11 + pola §5.1b DESIGN.md 3708-3803)
- `<x-callout type="error" />` — rejected_reason saat edit kost Rejected (§3.17)
- `<x-document-upload />` — upload foto (jpeg/png/jpg ≤5MB/foto, preview + revoke) (§3.24)
- `<x-gallery-lightbox />` — preview media yang sudah diupload (§3.27)
- `<x-kost-card />` — preview kartu pada langkah Review & Kirim (§3.3)
- `<x-confirm-dialog />` — konfirmasi "Kirim Pengajuan" + hapus kost (soft delete) (§3.25)
- Form fields: `<x-input />`, textarea, `<x-select />` kategori (§3.2); fasilitas/aturan sebagai JSON array (ADR-013)

#### Data Requirements
```php
public function create()  { return view('admin.kosts.create'); }
public function store(KostRequest $request)
{   // Kost::create + pivot kategori; status draft (FR-014); redirect ke edit stepper
}
public function edit(Kost $kost)
{   $kost->load(['address', 'categories', 'images', 'facilities', 'rules']);
    return view('admin.kosts.edit', compact('kost'));
}
public function update(KostRequest $request, Kost $kost)
{   // Update (FR-015); jika status rejected → status draft + rejected_reason di-clear (FR-020)
}
public function submit(Kost $kost)
{   // SubmitKostForReview — validasi data wajib (FR-017); draft → pending_review (FR-016)
}
```

**Eager Loading:** `address`, `categories`, `images` (sort_order), `facilities`, `rules`

#### Validation Rules (per langkah; dikumpulkan di `KostRequest` + `SubmitKostForReview` FR-017)
```php
'name'             => ['required', 'string', 'max:255'],
'category_id'      => ['required', 'exists:categories,id'],
'full_address'     => ['required', 'string', 'max:500'],        // + city/district
'description'      => ['required', 'string', 'min:50'],
'images'           => ['required', 'array', 'min:1', 'max:10'], // minimal 1 foto
'images.*'         => ['image', 'mimes:jpeg,png,jpg', 'max:5120'],
'facilities'       => ['required', 'array', 'min:1'],           // JSON array (ADR-013)
'facilities.*.name'=> ['required', 'string', 'max:100'],
'rules'            => ['array'],
'rules.*.name'     => ['required', 'string', 'max:200'],
```

#### User Flows

**Flow 1: Buat kost baru (draft)**
1. Admin klik "Tambah Kost" → `/admin/kosts/create`
2. Isi stepper langkah 1-3 (validasi per langkah, "Lanjut" disabled sampai valid)
3. Langkah 4 Review & Kirim: preview x-kost-card + ringkasan
4. "Kirim Pengajuan" → x-confirm-dialog → `SubmitKostForReview` (FR-017) → `pending_review`
5. Toast success + kost masuk antrean Super Admin (PAGE-012)

**Flow 2: Revisi kost Rejected (FR-020)**
1. Admin buka `/admin/kosts/{id}/edit`
2. x-callout error menampilkan rejected_reason (FR-019)
3. Klik "Perbaiki & Kirim Ulang" → stepper aktif
4. Simpan → status `draft` + rejected_reason clear
5. Submit ulang → `pending_review`

**Flow 3: Publikasi kost Approved (FR-021)**
1. Kost berstatus `approved`
2. Halaman tampilkan tombol "Publikasikan Sekarang"
3. Klik → `PublishKost` → status `active` + published_at — kost muncul di marketplace (FR-022)

#### Edge Cases
- Data wajib kurang saat submit → tolak + daftar field kurang (FR-017)
- Foto > 10 / > 5MB → error per file (x-document-upload)
- Edit kost `pending_review` → tombol submit/simpan disabled (menunggu keputusan Super Admin)
- Soft delete kost → x-confirm-dialog; kost berhenti menerima rental baru (ADR-009)

#### Accessibility Notes
- Stepper: nav `aria-label="Progress pengajuan kost"`, step aktif `aria-current="step"`; fokus pindah ke `x-ref="panel"` (tabindex="-1") saat ganti langkah; Prev/Next adalah `<button>` keyboard-accessible (§5.1b)
- Callout rejected_reason: heading + teks alasan terbaca; tombol aksi label jelas
- Upload foto: dropzone keyboard-accessible, error `role="alert"` + `aria-describedby`
- Setiap input berlabel; error inline `text-error-700` + `aria-describedby`
- x-confirm-dialog: focus trap + Esc close + restore focus ke trigger

---

### PAGE-014: Admin — Kost Info & Address Configuration

**URL:** `/admin/kosts/{kost}/edit` (combined with basic edit)  
**Route Name:** `admin.kosts.edit`  
**Method:** GET, PATCH  
**Auth:** Authenticated, role:admin  
**Controller:** `Admin\KostController@edit`, `@update`  
**FR Reference:** FR-024 (basic info), FR-025 (address)

#### Purpose
Configure kost basic information (name, description, contact) and complete address details (8 fields: full_address, district, city, province, postal_code, country, latitude, longitude). Address embedded in kost edit form using updateOrCreate pattern.

#### Layout Structure
```
Admin layout (sidebar + main content):
- x-page-header: breadcrumb "Kost / Edit / Info & Address"
- Form sections:
  1. Basic Info: name (text), description (textarea), contact_phone (text), contact_email (email)
  2. Address: full_address (textarea), district (text), city (text), province (select), postal_code (text), country (select defaulting Indonesia), latitude (text), longitude (text)
  3. Map widget (future): Leaflet.js for lat/long selection
- Action buttons: "Save Changes" (primary), "Cancel" (secondary)
```

#### Components Used
- `<x-input />` for text fields (name, contact_phone, contact_email, district, city, postal_code, latitude, longitude)
- `<x-textarea />` for description, full_address
- `<x-select />` for province, country dropdowns
- Form validation error display via `@error` directives

#### Data Requirements
- **Eager load:** `$kost->load('address')`
- **Validation (UpdateKostRequest):**
  - `name` => required|string|max:200
  - `description` => required|string|max:2000
  - `contact_phone` => required|string|max:20
  - `contact_email` => required|email|max:100
  - `full_address` => required|string|max:500
  - `district` => required|string|max:100
  - `city` => required|string|max:100
  - `province` => required|string|max:100
  - `postal_code` => nullable|string|max:10
  - `country` => required|string|max:100
  - `latitude` => nullable|numeric|between:-90,90
  - `longitude` => nullable|numeric|between:-180,180

#### User Flows
**Flow 1: Edit basic info**
1. Admin clicks "Info & Address" card from kost show page (PAGE-020)
2. Form pre-filled with existing kost + address data
3. Admin edits fields → clicks "Save Changes"
4. Validation runs → success: flash message "Kost updated successfully" → redirect back to show page
5. Validation fails → error messages displayed inline

**Flow 2: Complete address for first time**
1. New kost draft has no address yet (address relation null)
2. Admin fills address fields
3. On save: `Address::updateOrCreate(['kost_id' => $kost->id], $addressData)`
4. Address record created → completeness checklist updated

#### Edge Cases
- Address already exists: updateOrCreate updates existing record
- Latitude/longitude optional: can save without map coordinates
- Province/city free text (no API integration in MVP)

#### Accessibility Notes
- Form labels properly associated with inputs (`for` attribute)
- Required fields marked with `*` and `aria-required="true"`
- Error messages announced via `role="alert"`

---

### PAGE-015: Admin — Kost Images Management

**URL:** `/admin/kosts/{kost}/images`  
**Route Name:** `admin.kosts.images.index`  
**Method:** GET (index), POST (store), DELETE (destroy), PATCH (setThumbnail, updateSortOrder)  
**Auth:** Authenticated, role:admin  
**Controller:** `Admin\KostImageController`  
**FR Reference:** FR-026

#### Purpose
Upload kost images (max 10 per kost), set one as thumbnail, reorder images via drag-and-drop. Images displayed in marketplace kost detail page gallery.

#### Layout Structure
```
Admin layout:
- x-page-header: breadcrumb "Kost / Images" + "Upload Images" button
- Image gallery grid (3 cols desktop, 2 cols tablet, 1 col mobile):
  - Each image card:
    - Image preview (aspect ratio 4:3)
    - Thumbnail badge (if is_thumbnail = true)
    - Actions: "Set as Thumbnail" button, "Delete" button
    - Drag handle icon for reordering
- Upload section:
  - File input (multiple, accept="image/jpeg,image/png,image/jpg")
  - Preview thumbnails before upload
  - "Upload" button (disabled if > 10 total images)
- Empty state: "No images uploaded yet. Upload your first image to showcase this kost."
```

#### Components Used
- `<x-document-upload />` for multi-file upload with preview
- `<x-gallery-lightbox />` for image preview modal
- Alpine.js drag-and-drop sortable list (Sortable.js integration)
- `<x-button />` for actions

#### Data Requirements
- **Eager load:** `$kost->kostImages()->orderBy('sort_order')`
- **Filename pattern:** `kost-{id}-img-{Ymd-His}-{seq}.{ext}`
- **Storage:** `storage/app/public/kost-images/`
- **Database fields:** kost_id, filename, original_filename, file_path, file_size, mime_type, is_thumbnail, sort_order, uploaded_at

#### Validation
- `images.*` => `image|mimes:jpeg,png,jpg|max:5120` (5MB per file)
- Max 10 images per kost (checked in controller before store)
- Thumbnail: only 1 per kost (unique partial index on `(kost_id, is_thumbnail) WHERE is_thumbnail = true`)
- Delete validation: cannot delete if it's the only image and kost status is `active`

#### User Flows
**Flow 1: Upload images**
1. Admin clicks "Upload Images" button
2. File picker opens → select 1-5 images (JPEG/PNG)
3. Preview thumbnails displayed
4. Click "Upload" → images uploaded via POST request
5. Success: flash message "3 images uploaded successfully" → gallery refreshed
6. Error (e.g., file too large): validation error displayed

**Flow 2: Set thumbnail**
1. Admin views image gallery (5 images, none set as thumbnail yet)
2. Click "Set as Thumbnail" on image #3
3. PATCH request to `/images/{image}/thumbnail`
4. Success: image #3 badge shows "Thumbnail", previous thumbnail badge removed
5. Gallery re-renders with updated thumbnail

**Flow 3: Reorder images**
1. Admin drags image #5 to position #2
2. Alpine.js updates sort_order array: [1,5,2,3,4,6,7,8,9,10]
3. On drag end: PATCH request to `/images/sort-order` with new array
4. Success: silent update (no flash message), gallery reflects new order

**Flow 4: Delete image**
1. Admin clicks "Delete" on image #7
2. Confirmation modal: "Are you sure you want to delete this image?"
3. Confirm → DELETE request to `/images/{image}`
4. Success: flash message "Image deleted successfully" → image removed from gallery
5. If thumbnail deleted: oldest remaining image auto-promoted to thumbnail

#### Edge Cases
- Upload when 10 images exist: button disabled, message "Maximum 10 images allowed"
- Delete last image: allowed if kost is draft/pending_review, blocked if active
- Set thumbnail on already-thumbnail: no-op, return success
- Concurrent upload: transaction lock on kost_images count check

#### Accessibility Notes
- Drag handles keyboard accessible: `tabindex="0"`, Enter/Space to grab, Arrow keys to move, Enter/Space to drop
- Image alt text: original filename or kost name
- Delete button confirmation via accessible modal (focus trap)

---

### PAGE-016: Admin — Kost Categories Assignment

**URL:** `/admin/kosts/{kost}/categories`  
**Route Name:** `admin.kosts.categories.edit`, `admin.kosts.categories.update`  
**Method:** GET, PATCH  
**Auth:** Authenticated, role:admin  
**Controller:** `Admin\KostController@editCategories`, `@updateCategories`  
**FR Reference:** FR-027

#### Purpose
Assign kost to one or more categories from master category list (managed by Super Admin). Many-to-many relationship via `category_kost` junction table.

#### Layout Structure
```
Admin layout:
- x-page-header: breadcrumb "Kost / Categories"
- Form:
  - Section title: "Select Categories" + helper text "Choose at least 1 category that best describes this kost."
  - Checkbox list (all categories from `Category::orderBy('name')->get()`):
    - [ ] Kost Putra
    - [x] Kost Putri
    - [x] Kost Campur
  - Action buttons: "Save Changes" (primary), "Cancel" (secondary)
- Validation error: "Please select at least 1 category." (if submitted with none checked)
```

#### Components Used
- `<x-checkbox />` for each category option
- `<x-label />` for section title
- Form validation error display

#### Data Requirements
- **Eager load:** `$kost->categories`, `Category::all()`
- **Sync method:** `$kost->categories()->sync($request->category_ids)`
- **Validation:** `category_ids` => `required|array|min:1`, `category_ids.*` => `exists:categories,id`

#### User Flows
**Flow 1: Assign categories**
1. Admin clicks "Categories" card from kost show page
2. Form displays: 3 categories available (Putra, Putri, Campur), none checked yet
3. Admin checks "Putri" and "Campur"
4. Click "Save Changes" → PATCH request
5. Success: flash message "Categories updated successfully" → redirect to show page
6. Completeness checklist updated (categories: ✓)

**Flow 2: Change categories**
1. Kost already has "Putra" category assigned
2. Admin unchecks "Putra", checks "Putri"
3. Save → sync method removes "Putra" pivot row, adds "Putri" row
4. Success message shown

#### Edge Cases
- Uncheck all categories: validation error "Please select at least 1 category"
- Category deleted by Super Admin while admin editing: gracefully handle missing category (checkbox not rendered)
- No categories exist in system: display message "No categories available. Contact Super Admin to create categories."

#### Accessibility Notes
- Checkbox group has `role="group"` and `aria-labelledby` pointing to section title
- Each checkbox has proper `<label>` association
- Validation error has `role="alert"`

---

### PAGE-017: Admin — Kost Facilities & Rules Configuration

**URL:** `/admin/kosts/{kost}/edit` (embedded section in edit form)  
**Method:** PATCH (via KostController@update)  
**FR Reference:** FR-028 (facilities), FR-029 (rules)

#### Purpose
Configure facilities (JSON array, max 20 items) and rules (JSON array, max 20 items) for the kost. ADR-013 decision: JSON storage, not normalized tables. Alpine.js dynamic list UI with textarea fallback for manual JSON editing.

#### Layout Structure
```
Section within admin/kosts/edit.blade.php:
- Section: "Facilities"
  - Dynamic list (Alpine.js):
    - [X] Wi-Fi
    - [X] AC
    - [X] Kamar Mandi Dalam
    - [+ Add Facility] button
  - OR textarea fallback: `<textarea name="facilities_text">` for manual JSON entry
- Section: "Rules"
  - Dynamic list (Alpine.js):
    - [X] No smoking
    - [X] No pets
    - [X] Quiet hours: 22:00 - 06:00
    - [+ Add Rule] button
  - OR textarea fallback: `<textarea name="rules_text">`
```

#### Components Used
- Alpine.js component: `x-data="facilitiesList"` with methods: `addItem()`, `removeItem(index)`, `items` array
- `<x-input />` for each list item
- `<x-textarea />` for fallback manual entry
- `<x-button />` for "Add" and "Remove" actions

#### Data Requirements
- **Database fields:** `facilities` (JSON), `rules` (JSON) in `kosts` table
- **Cast:** `'facilities' => 'array'`, `'rules' => 'array'` in Kost model
- **Controller fallback parsing:** If `facilities_text` submitted (JS disabled), parse JSON manually

#### Validation
- `facilities` => `nullable|array|max:20`
- `facilities.*` => `string|max:100|distinct`
- `rules` => `nullable|array|max:20`
- `rules.*` => `string|max:200|distinct`
- `facilities_text` => `nullable|string` (fallback field)
- `rules_text` => `nullable|string` (fallback field)

#### User Flows
**Flow 1: Add facilities via dynamic list**
1. Admin views edit form, facilities section shows 2 existing items: "Wi-Fi", "AC"
2. Click "+ Add Facility" → new empty input appears
3. Type "Kamar Mandi Dalam" → item added to Alpine.js array
4. Click "Save Changes" → JSON array sent: `["Wi-Fi", "AC", "Kamar Mandi Dalam"]`
5. Success: flash message, data saved

**Flow 2: Remove facility**
1. Admin clicks [X] icon next to "AC" item
2. Alpine.js removes item from array
3. Save form → JSON array sent: `["Wi-Fi", "Kamar Mandi Dalam"]`

**Flow 3: Manual JSON entry (fallback)**
1. JavaScript disabled user accesses form
2. Textarea displayed with current JSON: `["Wi-Fi", "AC"]`
3. Admin edits manually: `["Wi-Fi", "AC", "Parkir Motor"]`
4. Save → controller parses JSON, validates, saves

#### Edge Cases
- Max 20 items: "Add" button disabled when count >= 20, message shown "Maximum 20 facilities allowed"
- Empty array: allowed (nullable validation), displayed as "No facilities configured"
- Invalid JSON in fallback textarea: validation error "Invalid JSON format"
- Duplicate items: validation catches with `distinct` rule

#### Accessibility Notes
- Dynamic list: each input has `aria-label="Facility item {n}"`
- Remove buttons: `aria-label="Remove facility {item_name}"`
- Add button: clear label "Add new facility"

---

### PAGE-018: Admin — Kost Payment Configuration (QRIS + Bank)

**URL:** `/admin/kosts/{kost}/payment`  
**Route Name:** `admin.kosts.payment.edit`, `admin.kosts.payment.update`  
**Method:** GET, PATCH  
**Auth:** Authenticated, role:admin  
**Controller:** `Admin\KostController@editPayment`, `@updatePayment`  
**FR Reference:** FR-030 (QRIS), FR-031 (bank account)

#### Purpose
Upload QRIS static image for tenant payment and configure bank account information. ADR-014 decision: QRIS static, no Midtrans/payment gateway integration.

#### Layout Structure
```
Admin layout:
- x-page-header: breadcrumb "Kost / Payment Info"
- Form sections:
  1. QRIS Image:
     - Current QRIS preview (if exists): <img src="{{ asset($kost->qris_image_path) }}" />
     - File input: "Upload new QRIS image (JPEG/PNG, max 2MB)"
     - Helper text: "Tenants will scan this QR code to pay via mobile banking apps."
  2. Bank Account Info:
     - Bank name (text input)
     - Account number (text input)
     - Account holder name (text input)
     - Helper text: "Provide bank details as alternative payment method."
  3. Action buttons: "Save Changes" (primary), "Cancel" (secondary)
```

#### Components Used
- `<x-document-upload />` for QRIS image (single file, replace on re-upload)
- `<x-input />` for bank fields
- Image preview component

#### Data Requirements
- **Filename pattern:** `qris-kost-{id}-{Ymd-His}.{ext}`
- **Storage:** `storage/app/public/qris-images/`
- **Database fields:** `qris_image_path`, `bank_name`, `account_number`, `account_holder_name` in `kosts` table

#### Validation
- `qris_image` => `nullable|image|mimes:jpeg,png,jpg|max:2048` (2MB)
- `bank_name` => `required_with:account_number|string|max:100`
- `account_number` => `required_with:bank_name|string|max:50`
- `account_holder_name` => `required_with:account_number|string|max:150`
- At least one payment method required: custom rule checks `qris_image_path` OR (`bank_name` AND `account_number`)

#### User Flows
**Flow 1: Upload QRIS image**
1. Admin clicks "Payment Info" card from show page
2. No QRIS uploaded yet (empty state)
3. Click "Upload new QRIS image" → file picker opens
4. Select QRIS PNG file (1.2MB)
5. Preview displayed
6. Click "Save Changes" → file uploaded to storage
7. Success: flash message "Payment info updated successfully"
8. Completeness checklist updated (payment: ✓)

**Flow 2: Configure bank account (alternative)**
1. Admin chooses not to upload QRIS
2. Fills bank fields: BCA / 1234567890 / John Doe
3. Save → bank info stored
4. Completeness checklist updated (payment: ✓)

**Flow 3: Update existing QRIS**
1. QRIS already exists: `/storage/qris-images/qris-kost-5-20260824-153022.png`
2. Admin uploads new QRIS image
3. Old file deleted from storage, new file saved
4. Database updated with new path

#### Edge Cases
- Neither QRIS nor bank info provided: validation error "Please provide at least one payment method (QRIS or bank account)"
- Bank name provided without account number: validation error "Account number required when bank name is provided"
- QRIS file too large: validation error "Image must not exceed 2MB"
- Invalid file format (PDF): validation error "File must be an image (JPEG, PNG, JPG)"

#### Accessibility Notes
- File input has clear label and `aria-describedby` pointing to helper text
- Image preview has `alt` text: "QRIS payment code for {kost_name}"
- Required field indicator: visual `*` + `aria-required="true"`

---

### PAGE-019: Admin — Kost Document Requirements Configuration

**URL:** `/admin/kosts/{kost}/document-requirements`  
**Route Name:** `admin.kosts.document-requirements.index`, `.store`, `.update`, `.destroy`  
**Method:** GET, POST, PATCH, DELETE  
**Auth:** Authenticated, role:admin  
**Controller:** `Admin\DocumentRequirementController`  
**FR Reference:** FR-032 (configure), FR-033 (set required/optional), FR-034 (reason)

#### Purpose
Configure which documents tenants must upload when renting this kost. Document types from `config/kost.php`: ktp, selfie, student_card, family_card, reference_letter, other. Set required/optional flag and provide reason for each requirement.

#### Layout Structure
```
Admin layout:
- x-page-header: breadcrumb "Kost / Document Requirements" + "Add Requirement" button
- Table (inline editable with Alpine.js):
  | Document Type | Required? | Reason | Actions |
  |---|---|---|---|
  | KTP | ✓ | Identity verification | Edit, Delete |
  | Selfie with KTP | ✓ | Anti-fraud measure | Edit, Delete |
  | Student Card | - | Optional for students | Edit, Delete |
- Empty state: "No document requirements configured. Click 'Add Requirement' to specify what documents tenants must submit."
- Add/Edit modal:
  - Document type dropdown (config options)
  - Required checkbox
  - Reason textarea (max 500 chars)
  - Save / Cancel buttons
```

#### Components Used
- Inline editable table (Alpine.js)
- `<x-select />` for document_type
- `<x-checkbox />` for is_required
- `<x-textarea />` for reason
- `<x-modal />` for add/edit form

#### Data Requirements
- **Eager load:** `$kost->documentRequirements()->orderBy('created_at')`
- **Config:** `config('kost.document_types')` => `['ktp', 'selfie', 'student_card', 'family_card', 'reference_letter', 'other']`
- **Database table:** `kost_document_requirements` (kost_id, document_type, is_required, reason)

#### Validation
- `document_type` => `required|in:ktp,selfie,student_card,family_card,reference_letter,other`
- `is_required` => `boolean`
- `reason` => `nullable|string|max:500`
- Unique constraint: `(kost_id, document_type)` — cannot add duplicate document type for same kost

#### User Flows
**Flow 1: Add document requirement**
1. Admin clicks "Add Requirement" button
2. Modal opens with form
3. Select "KTP" from dropdown, check "Required" checkbox, enter reason: "Identity verification"
4. Click "Save" → POST request
5. Success: modal closes, flash message "Document requirement added", table row added
6. Completeness checklist updated (documents: ✓)

**Flow 2: Edit requirement**
1. Admin clicks "Edit" on "Student Card" row
2. Modal opens with pre-filled data: document_type=student_card, is_required=false, reason="Optional for students"
3. Admin changes is_required to true, updates reason: "Required for student discounts"
4. Click "Save" → PATCH request
5. Success: modal closes, table row updated

**Flow 3: Delete requirement**
1. Admin clicks "Delete" on "Reference Letter" row
2. Confirmation modal: "Are you sure you want to delete this document requirement?"
3. Confirm → DELETE request
4. Success: flash message "Document requirement deleted", row removed from table

#### Edge Cases
- Add duplicate document type: validation error "Document type already configured for this kost"
- Delete when tenant rentals exist with uploaded docs: allowed (does not delete tenant documents, only removes requirement for future rentals)
- No requirements configured: allowed (kost can have zero document requirements)
- "Other" document type: free text field for custom document name

#### Accessibility Notes
- Table has proper `<thead>` and scope attributes
- Add/Edit modal: focus trap, Esc to close, focus returned to trigger button on close
- Checkbox labels: "Required" (not just icon)
- Delete confirmation: clear message with document type name

---

### PAGE-020: Admin — Kost Configuration Hub (Show Page)

**URL:** `/admin/kosts/{kost}`  
**Route Name:** `admin.kosts.show`  
**Method:** GET  
**Auth:** Authenticated, role:admin  
**Controller:** `Admin\KostController@show`  
**FR Reference:** Navigation hub for FR-024—FR-034

#### Purpose
Central navigation hub for all kost configuration sections. Displays data completeness checklist with warning badges for incomplete sections. Quick actions: Submit for Review, Publish (if approved), Cancel Submission.

#### Layout Structure
```
Admin layout:
- x-page-header:
  - Breadcrumb: "Kost / {kost_name}"
  - Status badge: <x-status-badge :status="$kost->status" /> (draft/pending_review/approved/active/rejected)
  - Action buttons:
    - "Submit for Review" (primary, if draft + data complete)
    - "Edit Basic Info" (secondary)
    - "Cancel Submission" (danger, if pending_review)

- Data completeness checklist (x-callout type="warning"):
  - [ ] Basic Info Complete (name, description, contact, address 8 fields) ⚠️
  - [✓] Images Uploaded (min 1 image)
  - [✓] Categories Assigned (min 1 category)
  - [ ] Facilities & Rules Configured ⚠️
  - [✓] Payment Info Complete (QRIS or bank account)
  - [✓] Document Requirements Configured (min 1 requirement)
  
- 7-card grid navigation (3 cols desktop, 2 cols tablet, 1 col mobile):
  1. Info & Address
     - Icon: 📝
     - Status: Complete / Incomplete
     - Link: /admin/kosts/{id}/edit
  2. Images (3/10)
     - Icon: 🖼️
     - Status: 3 images uploaded
     - Link: /admin/kosts/{id}/images
  3. Categories
     - Icon: 🏷️
     - Status: 2 categories assigned
     - Link: /admin/kosts/{id}/categories
  4. Facilities & Rules
     - Icon: ⚙️
     - Status: Not configured ⚠️
     - Link: /admin/kosts/{id}/edit#facilities
  5. Payment Info
     - Icon: 💳
     - Status: QRIS uploaded
     - Link: /admin/kosts/{id}/payment
  6. Document Requirements
     - Icon: 📄
     - Status: 3 documents required
     - Link: /admin/kosts/{id}/document-requirements
  7. Room Types (stub)
     - Icon: 🛏️
     - Status: Not configured ⚠️
     - Link: /admin/kosts/{id}/room-types (COMP-004, not implemented yet)
```

**Navigation:** Admin sidebar (`layouts/admin.blade.php`) with role-specific menu items (Admin vs Super Admin).

#### Components Used
- `<x-status-badge />` for kost status (draft/pending_review/approved/active/rejected)
- `<x-callout type="warning" />` for completeness checklist
- Navigation cards (custom component, hover states, icon per section)
- Action buttons: `<x-button />` with variants (primary, secondary, danger)

#### Data Requirements
- **Eager load:** `$kost->with('address', 'categories', 'kostImages', 'documentRequirements')`
- **Completeness logic:**
  - Basic info complete: name, description, contact_phone, contact_email, address (8 fields all filled)
  - Images: `kostImages()->count() >= 1`
  - Categories: `categories()->count() >= 1`
  - Facilities: `facilities` array has >= 1 item
  - Payment: `qris_image_path` IS NOT NULL OR (`bank_name` AND `account_number` both filled)
  - Document requirements: `documentRequirements()->count() >= 1`

#### User Flows
**Flow 1: Navigate to configuration section**
1. Admin views kost show page (PAGE-020)
2. Completeness checklist shows 2 warnings: "Facilities & Rules Not Configured", "Basic Info Incomplete"
3. Admin clicks "Facilities & Rules" card
4. Redirected to `/admin/kosts/{id}/edit#facilities` (PAGE-017 section)
5. Admin completes facilities configuration
6. Returns to show page → warning badge removed, checklist updated: [✓] Facilities & Rules Configured

**Flow 2: Submit for review**
1. All required sections complete (no warnings in checklist)
2. Admin clicks "Submit for Review" button
3. Confirmation modal: "Submit this kost for Super Admin review?"
4. Confirm → POST request to SubmitKostForReview Action
5. Validation: FR-017 required data check (name, address, categories, payment, min 1 image)
6. Success: status changed to `pending_review`, flash message "Kost submitted for review successfully", redirect to `/admin/kosts`
7. Action buttons updated: "Submit for Review" hidden, "Cancel Submission" shown

**Flow 3: Cancel submission**
1. Kost status `pending_review`, waiting for Super Admin review
2. Admin clicks "Cancel Submission" button (danger variant)
3. Confirmation modal: "Are you sure you want to cancel this submission? Status will revert to Draft."
4. Confirm → DELETE request to `/admin/kosts/{id}/cancel`
5. Success: status changed to `draft`, flash message "Submission cancelled", redirect back to show page
6. Action buttons updated: "Submit for Review" shown again

#### Edge Cases
- Incomplete data on submit: validation error with specific missing fields listed: "Please complete: Facilities & Rules, Basic Info (missing: contact_email)"
- Kost status `pending_review`: all edit actions disabled, message shown "Waiting for Super Admin review. Cancel submission to make changes."
- Kost status `rejected`: rejection reason callout displayed (red), "Revise & Resubmit" button shown
- Kost status `approved`: "Publish" button shown (changes status to `active`)
- Kost status `active`: "Unpublish" button shown (confirmation required)

#### Accessibility Notes
- Navigation cards: keyboard accessible (`<a>` tags, not `<div onclick>`), focus visible with clear outline
- Completeness checklist: `role="status"` for live region updates when data changes
- Action buttons: clear labels, disabled state has `aria-disabled="true"` and visual opacity
- Status badge: `aria-label` includes status text for screen readers ("Status: Draft")

---

## 6. Super Admin Interface Pages — 11 Pages

### PAGE-012: Super Admin — Submission Review List & Detail

**URL:** `/superadmin/submissions` (GET), `/superadmin/submissions/{kost}` (GET), `/superadmin/submissions/{kost}/approve` (POST), `/superadmin/submissions/{kost}/reject` (POST)  
**Route Name:** `superadmin.submissions.index`, `superadmin.submissions.show`, `superadmin.submissions.approve`, `superadmin.submissions.reject`  
**Method:** GET, POST  
**Auth:** Authenticated, role:superadmin  
**Controller:** `SuperAdmin\KostSubmissionController`  
**FR Reference:** FR-018 (approve → `approved`), FR-019 (reject + reason → `rejected`), FR-022 (hanya kost active tampil di marketplace)

> **Catatan inventori:** PAGE-012 menambah Super Admin pages §1.1 dari 10 → 11. Publikasi akhir (`approved` → `active`) dilakukan Admin via `PublishKost` (FR-021, PAGE-011) — Super Admin hanya approve/reject.

#### Purpose
- Antrean kost `pending_review` dari semua Admin (FR-016)
- Review detail submission (info, media, fasilitas/aturan, room types, QRIS/bank)
- Approve (FR-018) → `approved` (menunggu publish Admin) atau Reject dengan alasan wajib (FR-019) → `rejected`

#### Layout Structure
```
List (/superadmin/submissions):
- x-page-header: judul + count pending
- Tabel (pola x-table §3.7): kost, admin pemilik, kategori, kota, submitted_at, badge status
- Row klik → detail

Detail (/superadmin/submissions/{kost}):
- x-page-header + badge status
- Ringkasan kost (x-kost-card + x-gallery-lightbox media)
- Info lengkap: deskripsi, fasilitas/aturan list, room types + harga, QRIS/bank, document requirements
- Aksi: [Approve] primary / [Reject] destructive (modal reason)
```

**Navigation:** Admin sidebar (`layouts/admin.blade.php`) with Super Admin-specific menu items.

#### Components Used
- `<x-page-header />` — judul + count pending (§3.26)
- Tabel pola §3.7: `<table>` semantic, header `scope`, zebra/divider, hover
- `<x-status-badge />` — status pending_review/approved/rejected (§3.4)
- `<x-confirm-dialog />` — modal reject: textarea alasan WAJIB (FR-019) (§3.25)
- `<x-callout type="success" />` — konfirmasi approve sukses (§3.17)
- `<x-gallery-lightbox />` — preview media submission (§3.27)
- `<x-kost-card />` — preview ringkas (§3.3)
- `<x-empty-state />` — tidak ada submission pending (§3.8)

#### Data Requirements
```php
public function index()
{
    $submissions = Kost::query()
        ->where('status', 'pending_review')
        ->with(['admin', 'address', 'categories'])
        ->latest('submitted_at')
        ->paginate(20); // eager loading, hindari N+1
    return view('superadmin.submissions.index', compact('submissions'));
}

public function show(Kost $kost)
{
    abort_if($kost->status !== 'pending_review', 404);
    $kost->load(['admin', 'address', 'categories', 'images', 'facilities',
                 'rules', 'roomTypes.priceSchemes', 'bankAccount', 'documentRequirements']);
    return view('superadmin.submissions.show', compact('kost'));
}

// approve: ApproveKost action — status approved, approved_by + approved_at (FR-018)
// reject:  RejectKost action   — status rejected, rejected_reason wajib (FR-019)
```

**Eager Loading:** `admin`, `address`, `categories`, `images`, `facilities`, `rules`, `roomTypes.priceSchemes`

#### Validation Rules
```php
// reject:
'reason' => ['required', 'string', 'min:10', 'max:1000'], // FR-019 wajib
```

#### User Flows

**Flow 1: Approve submission (FR-018)**
1. Super Admin buka list → klik submission pending
2. Review detail (media, fasilitas, room types, QRIS)
3. Klik "Approve" → konfirmasi singkat → `ApproveKost` → status `approved`
4. x-callout success: "Kost disetujui — Admin dapat mempublikasikan" (FR-021)
5. Kost hilang dari antrean pending

**Flow 2: Reject submission (FR-019)**
1. Super Admin klik "Reject"
2. x-confirm-dialog: textarea alasan (wajib, min 10 karakter)
3. Submit → `RejectKost` → status `rejected` + rejected_reason tersimpan
4. Admin melihat alasan di PAGE-011 dan merevisi (FR-019/FR-020)

#### Edge Cases
- Submission sudah diproses (status berubah): aksi approve/reject ditolak (state machine ADR-009)
- Reject tanpa alasan: tombol disabled sampai textarea valid (min 10)
- Kost di-soft-delete saat Pending Review: sembunyikan dari antrean
- List kosong: x-empty-state "Tidak ada submission pending"

#### Accessibility Notes
- Tabel: `<th>` + `scope`, caption/`aria-label`, sortable header `aria-sort`
- Status badge: teks status (bukan warna saja) — x-status-badge §3.4
- Reject modal (x-confirm-dialog): initial focus ke textarea reason, focus trap, Esc close, restore
- Umpan balik aksi: toast `aria-live="polite"` / x-callout success
- Preview media: lightbox x-gallery-lightbox (focus trap + Esc + arrow prev/next)

---

## 7. Email Templates — 8 Templates

### EMAIL-001: OTP Email Verification

**Trigger:** Halaman `/verify-email` diakses (on-demand/lazy) — OTP TIDAK dikirim saat registrasi (FR-003 diubah; lihat PAGE-005/006). Juga saat resend OTP (FR-005) dan email change re-verification (FR-129)  
**Recipient:** User (any role)  
**Subject:** `[SewaKost] Kode Verifikasi Email Anda`

**Content Structure:**
```
Header: SewaKost logo + brand color bar
Body:
- Greeting: "Halo {FirstName},"
- Instruction: "Gunakan kode berikut untuk verifikasi email Anda:"
- OTP Code (large, monospace, centered): 123456
- Expiry warning: "Kode ini berlaku selama 15 menit"
- Resend link: "Tidak menerima kode? Kirim ulang"
- Security notice: "Jika Anda tidak meminta kode ini, abaikan email ini"
Footer: © SewaKost | Contact | Privacy
```

> **Catatan alur on-demand:** Email ini hanya keluar saat user membuka `/verify-email` dan belum ada OTP valid, atau saat resend — bukan saat registrasi selesai (FR-003, ADR-023).

**Design Specs:**
- Max width: 600px
- Font: System sans-serif
- OTP code: 32px bold, letter-spacing 8px, primary color
- CTA button (resend): Primary color, 44px height

---

### EMAIL-002: Admin Account Created

**Trigger:** Super Admin creates Admin account (FR-111, FR-113)  
**Recipient:** New Admin  
**Subject:** `[SewaKost] Akun Admin Anda Telah Dibuat`

**Content:**
```
Greeting: "Selamat datang di SewaKost, {FirstName}!"
Body:
- "Akun Admin Anda telah dibuat oleh Super Admin"
- Credentials:
  - Email: {email}
  - Password sementara: {tempPassword}
- CTA: [Login Sekarang] button → /login
- Instruction: "Silakan login dan ubah password Anda"
Footer
```

---

### EMAIL-003: Payment Verified - Approved

**Trigger:** Admin approves payment (FR-072, FR-082)  
**Recipient:** Tenant  
**Subject:** `[SewaKost] Pembayaran Rental Anda Telah Diverifikasi`

**Content:**
```
Greeting
Body:
- "Pembayaran rental Anda telah diverifikasi oleh Admin"
- Rental details: Kost name, Room code, Dates
- Next steps: "Silakan upload dokumen administrasi sebelum {start_date}"
- Document checklist preview (required docs)
- CTA: [Upload Dokumen Sekarang] → /rentals/{id}
Footer
```

---

### EMAIL-004: Payment Rejected

**Trigger:** Admin rejects payment (FR-073, FR-082)  
**Recipient:** Tenant  
**Subject:** `[SewaKost] Pembayaran Rental Anda Ditolak`

**Content:**
```
Greeting
Body:
- "Maaf, pembayaran rental Anda ditolak"
- Rejection reason (callout box, red): {reason}
- Instruction: "Silakan upload ulang bukti pembayaran yang benar"
- Deadline reminder: "Deadline pembayaran: {deadline}"
- CTA: [Upload Ulang Bukti Bayar] → /rentals/{id}/payment
Footer
```

---

### EMAIL-005: Document Approved

**Trigger:** Admin approves document (FR-088, FR-095)  
**Recipient:** Tenant  
**Subject:** `[SewaKost] Dokumen {DocumentType} Disetujui`

**Content:**
```
Body:
- "Dokumen {documentType} Anda telah disetujui"
- Status dokumen lain: "{X} dari {Y} dokumen wajib sudah disetujui"
- If all approved: "Semua dokumen wajib sudah disetujui. Rental Anda akan aktif pada {start_date}"
- CTA: [Lihat Status Rental] → /rentals/{id}
```

---

### EMAIL-006: Document Rejected

**Trigger:** Admin rejects document (FR-089, FR-095)  
**Recipient:** Tenant  
**Subject:** `[SewaKost] Dokumen {DocumentType} Ditolak`

**Content:**
```
Body:
- "Dokumen {documentType} Anda ditolak"
- Rejection reason: {reason}
- Instruction: "Silakan upload ulang dokumen yang benar"
- CTA: [Upload Ulang Dokumen] → /rentals/{id}
```

---

### EMAIL-007: Rental Status Change

**Trigger:** Rental status transitions (FR-093, FR-101, FR-102, FR-126)  
**Recipient:** Tenant  
**Subject:** `[SewaKost] Rental Anda {StatusLabel}`

**Content (varies by status):**

**Confirmed:**
```
- "Selamat! Semua dokumen Anda telah diverifikasi"
- "Rental Anda akan aktif pada {start_date}"
- Preparation checklist (bring keys, contact info)
```

**Active:**
```
- "Rental Anda sekarang aktif"
- Contract dates, room info
- Contact admin for issues
```

**Completed:**
```
- "Rental Anda telah selesai. Terima kasih!"
- CTA: [Tulis Review] → /rentals/{id}/reviews/create
```

**Auto-Cancelled:**
```
- "Rental Anda dibatalkan: {reason}"
- Reason: "Dokumen tidak dilengkapi sebelum start date"
- Refund info: "Hubungi admin untuk refund"
```

---

### EMAIL-008: Password Reset OTP

**Trigger:** User request password reset (FR-130)  
**Recipient:** User  
**Subject:** `[SewaKost] Kode Reset Password Anda`

**Content Structure:**
```
Header: SewaKost logo + brand color bar
Body:
- Greeting: "Halo {FirstName},"
- Instruction: "Gunakan kode berikut untuk mengatur ulang password Anda:"
- OTP Code (large, monospace, centered): 123456
- Expiry warning: "Kode ini berlaku selama 15 menit"
- Security notice: "Jika Anda tidak meminta reset password, abaikan email ini"
Footer: © SewaKost | Contact | Privacy
```

**Design Specs:**
- Max width: 600px
- Font: System sans-serif
- OTP code: 32px bold, letter-spacing 8px, primary color
- Tidak ada CTA link (berbeda dari EMAIL-001) — user kembali ke aplikasi untuk input kode

**Implementation Note:**
- Dikirim via `OtpVerificationMail` dengan purpose `password-reset` — subject + instruksi dipilih berdasarkan purpose (lihat ARCHITECTURE.md ADR-022)

---

## 8. Summary & Next Steps

### Document Status
- **DESIGN.md:** ✅ COMPLETE (4340 lines)
  - 4 design principles
  - Complete design token system (colors, typography, spacing, shadows)
  - 38 components (§3.1–3.38, inventory §3.0)
  - Layout patterns (Public, Admin, Auth)
  - Responsive design guidelines
  - WCAG 2.1 AA accessibility targets
  - Implementation notes (Blade + Alpine.js + Tailwind)

- **PAGES.md:** ✅ COMPLETE (1928 lines, 57 page specs + 8 email templates)
  - 3 Public pages (Landing, Marketplace, Kost Detail) — FULLY SPECIFIED
  - 6 Auth pages (Login, Register, OTP Verify, Forgot Password, Reset OTP, Set New Password) — FULLY SPECIFIED; Verify Email Modal (PAGE-006D) = 17th fully-specified spec
  - 16 Tenant pages — 5 FULLY SPECIFIED (Dashboard, Rental Detail, Payment, Rental Create, Review Create) + 11 summarized
  - 21 Admin pages — 1 FULLY SPECIFIED (PAGE-011: Kost Create/Edit) + 20 summarized
  - 11 Super Admin pages — 1 FULLY SPECIFIED (PAGE-012: Submission Review) + 10 summarized
  - 8 Email templates (EMAIL-001..008) — COMPLETE with content structure
  - Total: 57 pages (17 fully specified + 40 summarized) + 8 emails = 65 interface specs

### Remaining Pages (Summarized Specifications)

Sample summarizing the 40 remaining page specs (full enumeration tracked in TODO.md task breakdowns).

**Admin Pages (21 total):**
- Admin Dashboard (stats, pending verifications count) — `x-page-header`, `x-stat-card`, `x-callout` (§3.26/§3.31/§3.17)
- Kost Create/Edit (PAGE-011, FULLY SPECIFIED): submission stepper 4 langkah (`x-stepper`): Detail → Foto & Media → Fasilitas & Aturan → Review & Kirim (§5.1b DESIGN). Reject flow → `x-callout` rejected_reason + tombol "Perbaiki & Kirim Ulang" (FR-020). Kost Approved → tombol "Publikasikan Sekarang" (`PublishKost`, FR-021) — publish adalah aksi state machine, bukan CRUD biasa. Soft delete kost → `x-confirm-dialog` (§3.25)
- Kost Configuration (8 pages: info, address, images, categories, facilities/rules, QRIS, bank, documents)
- Room Inventory (room types, price schemes, rooms CRUD)
- Rental Management (list, detail, payment verification, document verification) — `x-confirm-dialog` untuk reject payment/dokumen, `x-callout` alasan

**Super Admin Pages (11 total):**
- Submissions List + Detail (PAGE-012, FULLY SPECIFIED: review kosts pending approval; approve FR-018 → `approved`, reject FR-019 + alasan wajib)
- Admin Management (create, list, edit, soft delete) — `x-confirm-dialog` soft delete
- Category Management (CRUD categories)

**All follow same spec pattern:**
- URL, route, auth, controller, FR references
- Layout structure diagram
- Components used (reference DESIGN.md)
- Data requirements (queries, eager loading)
- User flows (happy path + edge cases)
- Responsive behavior
- Accessibility notes

### Implementation Priority
1. **Phase 1:** Auth pages (Login, Register, OTP) — TASK-001 to TASK-004
2. **Phase 2:** Public pages (Landing, Marketplace, Kost Detail) — TASK-xxx marketplace
3. **Phase 3:** Tenant pages (Dashboard, Rental CRUD, Payment, Documents)
4. **Phase 4:** Admin pages (Kost management, Rental verification)
5. **Phase 5:** Super Admin pages (Submissions, Admin/Category management)
6. **Phase 6:** Email templates (Mailables + Blade email views)

### Developer Workflow
```
TASK-xxx from TODO.md
  ↓
Read PAGES.md for page spec
  ↓
Reference DESIGN.md for components
  ↓
Create Blade view in resources/views/
  ↓
Implement controller logic
  ↓
Test user flows (manual + automated)
  ↓
Run accessibility audit (axe DevTools)
  ↓
Mark TASK Done
```

---

**END OF PAGES.MD**

> **Total Documentation:**
> - DESIGN.md: 4505 lines (complete design system)
> - PAGES.md: 2535 lines (24 fully specified page specs + 40 summarized + 8 email templates = 72 specs)
> - Combined: 7040 lines of comprehensive UI/UX documentation
>
> **Version 1.3.2 (2026-08-30):** Updated §0.1 navigation guidelines: replaced role-based dropdown mention with unified navbar spec (`<x-nav-public />` for all roles, Dashboard link routes via `auth()->user()->dashboardRoute()`, Admin/Super Admin pages use admin sidebar but navbar remains unified). Added breadcrumb guideline: no "Dashboard" link, start directly with section context.
>
> **Version 1.3.1 (2026-08-30):** Added role-based navigation documentation: §0.1 updated with navbar usage guidelines (public navbar for tenant pages, admin sidebar for admin/superadmin pages). Updated PAGE-007 (Tenant Dashboard), PAGE-011 (Admin Kost Create/Edit), PAGE-020 (Admin Kost Configuration Hub), PAGE-012 (Super Admin Submission Review) with navigation notes.
>
> **Version 1.3.0 (2026-08-24):** Added 7 COMP-003 Kost Configuration page specs (PAGE-014—PAGE-020): Info & Address, Images Management, Categories Assignment, Facilities & Rules, Payment Configuration (QRIS + Bank), Document Requirements, Configuration Hub. Updated page inventory: 57 → 64 pages total.
> **Next:** Update AGENTS.md with references to DESIGN.md + PAGES.md
