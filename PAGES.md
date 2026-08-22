# PAGES.md — Page & Interface Specifications

> **Status dokumen ini:** Single Source of Truth untuk SPESIFIKASI HALAMAN & EMAIL.
> Dokumen ini menyediakan detail lengkap untuk semua 57 halaman + 8 email template di aplikasi SewaKost.
> Setiap page spec mencakup: URL, auth requirement, layout, components, data, validation, user flows, accessibility.

| Field | Value |
|---|---|
| Nama Proyek | SewaKost — Web Marketplace Kost Management & Rental System |
| Versi Dokumen | `1.2.0` |
| Terakhir Diperbarui | `2026-08-20` |
| Total Pages | 57 pages + 8 email templates |

---

## 0. Cara Menggunakan Dokumen Ini

### 0.1 Untuk Agent/Developer
1. **Baca DESIGN.md terlebih dahulu** untuk memahami design tokens, components, layout patterns
2. **Cari page spec** berdasarkan URL atau FR-xxx yang direferensikan dari TODO.md
3. **Implementasi Blade view** berdasarkan spec: layout, components, data requirements, validation rules
4. **Follow user flows** untuk memahami interaksi dan edge cases
5. **Test accessibility** sesuai catatan aksesibilitas per page

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
| **Admin Interface** | 21 pages | Dashboard, Kost CRUD (PAGE-011), Config, Room Inventory, Rental Verification |
| **Super Admin Interface** | 11 pages | Submissions Review (PAGE-012), Admin Management, Category Management |
| **Email Templates** | 8 templates | OTP, Reset OTP, Admin Account, Payment/Document Verifications, Rental Status |

**Total:** 57 pages + 8 email templates = **65 interface specifications**

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
- `<x-nav-public>` (§3.6)
- `<x-search />` — Search bar (nama/lokasi) + submit-on-Enter (§3.36)
- `<x-mobile-filter-drawer />` — Filter drawer mobile (harga min-max + kategori + rating, chips ringkasan filter aktif) (§3.32). Desktop: panel filter inline (sidebar kiri, sticky) memakai pola yang sama: `fieldset` + `legend` per grup filter, tombol "Terapkan Filter" `x-button` primary — lihat §3.32 untuk struktur field + a11y
- `<x-kost-card />` x N (§3.3)
- `<x-pagination />` (§3.15, Laravel default styled)
- `<x-empty-state />` — No results state (§3.8)
- `<x-skeleton variant="card" />` x 12 — Loading state (§3.38) (skeleton card pattern — DESIGN §3.9)

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
- `<x-nav-public>` (§3.6)
- `<x-breadcrumbs />` (§3.13)
- `<x-gallery-lightbox />` — Hero image + thumbnail grid + lightbox (focus trap, Esc, arrow prev/next) (§3.27)
- Kost Info Card (custom, display facilities/rules as list)
- Document Requirements: `<x-document-upload />` (per dokumen wajib, status upload/terverifikasi) (§3.24) + checklist display (`x-empty-state`/badge pola §3.4 untuk required vs optional) — lihat §3.24 status verifikasi
- `<x-room-card />` x N — Room types accordion (size, occupancy, price schemes, availability real-time) (DESIGN §3.3)
- `<x-review-card />` x N (§3.30)
- `<x-map />` — Map Widget (Leaflet.js, read-only, fallback alamat teks + link Google Maps) (§3.28)
- `<x-rating :value="4.8" :count="32" />` — Rating summary di booking sidebar (§3.29)
- Booking Sidebar (sticky): `<x-booking-form />` panel summary (§3.23); di mobile → `<x-sticky-action-bar />` (fixed bottom) (§3.33)

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
        'images' => fn($q) => $q->orderBy('sort_order'),
        'documentRequirements',
        'roomTypes' => fn($q) => $q->with([
            'priceSchemes' => fn($q) => $q->where('is_active', true),
            'images' => fn($q) => $q->where('is_thumbnail', true),
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
- `images` (gallery, sorted by sort_order)
- `documentRequirements` (checklist display)
- `roomTypes.priceSchemes` (active only)
- `roomTypes.images` (thumbnail)
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

### PAGE-007: Tenant Dashboard

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
- `<x-nav-public>` with authenticated state (§3.6)
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

### PAGE-008: Rental Detail (Tenant View)

**URL:** `/rentals/{rental}`  
**Route Name:** `rentals.show`  
**Method:** GET  
**Auth:** Authenticated, role:user, owner  
**Controller:** `Tenant\RentalController@show`  
**FR Reference:** FR-097 (View rental detail), FR-103 (Timeline display)

#### Purpose
- Complete rental information: kost, room, dates, pricing, status, timeline
- Access to action buttons based on status (upload payment, documents, review)

#### Layout Structure
```
2-column layout:
- Left (70%):
  - Rental info card (kost name, room, dates, price, status badge)
  - Timeline (status progression stepper)
  - Payment section (QRIS, proof, verification status)
  - Documents section (requirements checklist, upload status)
  - Review section (if completed, show review or prompt)
- Right (30%, sticky):
  - Quick actions sidebar
  - Contact admin button
  - Cancel rental button (if allowed)
```

#### Components Used
- `<x-status-badge />` (§3.4)
- `<x-stepper />` varian vertical — rental timeline/status history (pending → paid → confirmed → active → completed/cancelled) dengan `aria-current="step"` (§3.11)
- Payment section: `<x-qris-payment />` untuk display QRIS + info bank + deadline (read-only di sini; form upload di PAGE-009) (§3.22)
- Document checklist: `<x-document-upload />` per dokumen wajib (status upload/terverifikasi) (§3.24)
- `<x-button />` variants untuk actions
- `<x-confirm-dialog />` — "Cancel Rental" modal (destructive, reason input wajib per FR-123) (§3.25)

#### User Flow (varies by status)
**Pending status:**
- Primary action: "Upload Bukti Bayar" → `/rentals/{id}/payment`
- Secondary: "Cancel Rental" → `<x-confirm-dialog>` modal confirmation

**Paid status:**
- Primary: "Upload Dokumen" → scroll to documents section
- View payment proof (approved timestamp)

**Confirmed/Active:**
- View rental details (read-only mostly)
- Optional: "Cancel Rental" (if before 50% duration)

**Completed:**
- Primary: "Tulis Review" → `/rentals/{id}/reviews/create`
- Or view existing review

#### Accessibility Notes
- Timeline `x-stepper` vertical: step aktif `aria-current="step"`; setiap step punya teks status (bukan hanya ikon/angka) agar terbaca SR
- Status badge: warna (`x-status-badge` §3.4) bukan satu-satunya indikator — teks status selalu ada
- QRIS image: `alt` deskriptif (mis. "QRIS pembayaran Rp 4.500.000"); tombol copy/rekening punya `aria-label` jelas
- Document checklist: status tiap dokumen berupa teks + badge (mis. "Diverifikasi"), hasil upload diumumkan `aria-live="polite"`
- Cancel rental via `<x-confirm-dialog>` (§3.25): focus trap, initial focus ke input reason, Esc close, restore focus ke trigger

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

## 5. Admin Interface Pages — 21 Pages

### PAGE-011: Admin — Kost Create/Edit (Submission)

**URL:** `/admin/kosts/create` (GET), `/admin/kosts` (POST), `/admin/kosts/{kost}/edit` (GET), `/admin/kosts/{kost}` (PATCH), `/admin/kosts/{kost}/submit` (POST)  
**Route Name:** `admin.kosts.create`, `admin.kosts.store`, `admin.kosts.edit`, `admin.kosts.update`, `admin.kosts.submit`  
**Method:** GET, POST, PATCH  
**Auth:** Authenticated, role:admin  
**Controller:** `Admin\KostController`  
**FR Reference:** FR-014 (create draft), FR-015 (update draft/rejected), FR-016 (submit for review), FR-017 (validasi data wajib sebelum submit), FR-019 (lihat rejected_reason), FR-020 (revisi rejected → draft, clear reason), FR-021 (publish approved)

> **Catatan inventori:** PAGE-011 adalah referensi lengkap untuk entry "Kost CRUD (create, edit)" dalam 21 Admin pages (§1.1) — TIDAK menambah jumlah. Kost Configuration 8 halaman (info, address, images, categories, facilities/rules, QRIS, bank, documents) tetap summarized.

#### Purpose
- Alur pengajuan kost 4 langkah (DESIGN.md §5.1b): Detail Kost → Foto & Media → Fasilitas & Aturan → Review & Kirim
- Simpan draft (FR-014), update draft/rejected (FR-015/FR-020), submit `draft` → `pending_review` (FR-016)
- Saat edit kost Rejected: tampilkan alasan via x-callout + tombol "Perbaiki & Kirim Ulang"
- Kost Approved: tombol "Publikasikan Sekarang" (`PublishKost`, FR-021) → `active`

#### Layout Structure
```
Admin layout (sidebar x-nav-admin-sidebar):
- x-page-header: breadcrumb (Kost → Buat/Edit) + judul + aksi kontekstual
- x-callout error (jika rejected_reason ada): alasan penolakan + tombol "Perbaiki & Kirim Ulang" (FR-020)
- x-stepper horizontal 4 langkah (§5.1b):
  [Detail Kost] → [Foto & Media] → [Fasilitas & Aturan] → [Review & Kirim]
- Panel aktif per langkah (validasi per langkah; tombol Kembali/Lanjut)
```

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
> - DESIGN.md: 4340 lines (complete design system)
> - PAGES.md: 1928 lines (17 fully specified page specs + 40 summarized + 8 email templates = 65 specs)
> - Combined: 6268 lines of comprehensive UI/UX documentation
>
> **Next:** Update AGENTS.md with references to DESIGN.md + PAGES.md
