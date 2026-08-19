# DESIGN.md — UI/UX Design System

> **Status dokumen ini:** Single Source of Truth untuk DESAIN VISUAL & UX PATTERNS.
> Dokumen ini menjadi pedoman implementasi UI untuk semua halaman/antarmuka di aplikasi SewaKost.
> Setiap komponen, token, dan pattern di sini dirancang untuk Laravel 13 + Blade + Alpine.js + Tailwind CSS 4.0.

| Field | Value |
|---|---|
| Nama Proyek | SewaKost — Web Marketplace Kost Management & Rental System |
| Versi Dokumen | `1.0.1` |
| Terakhir Diperbarui | `2026-08-16` |
| Tech Stack | Laravel 13 + Blade + Alpine.js 3.14 + Tailwind CSS 4.0 |

---

## 0. Cara Menggunakan Dokumen Ini

### 0.1 Untuk Agent/Developer
1. **Baca §1 Design Principles** sebelum implementasi halaman apa pun — prinsip ini berlaku universal
2. **Cek §2 Design Tokens** untuk semua nilai visual (warna, spacing, typography, shadow) — jangan hardcode nilai arbitrary
3. **Gunakan §3 Component Library** sebagai referensi HTML+Tailwind+Alpine.js — copy-paste dan modifikasi sesuai kebutuhan
4. **Ikuti §4 Layout Patterns** untuk struktur halaman sesuai role pengguna (Public, Tenant, Admin, Super Admin)
5. **Terapkan §6 Responsive Design** untuk semua halaman — mobile-first approach wajib
6. **Verifikasi §7 Accessibility** sebelum mark task Done — WCAG 2.1 AA adalah target minimum

### 0.2 Hubungan dengan Dokumen Lain
- **PRD.md** (§4 Persona) → Design principles disesuaikan dengan target pengguna: Rina (Tenant, 18-35yo), Budi (Admin Kost, 30-50yo), Pak Ahmad (Super Admin)
- **ARCHITECTURE.md** (§6.1 Routes) → Setiap route punya spesifikasi halaman di `PAGES.md`
- **PAGES.md** → Detail spesifikasi 54 halaman + 7 email templates (layout, komponen, user flow, data requirements)
- **AGENTS.md** → Workflow implementasi: baca DESIGN.md → baca PAGES.md → implementasi Blade view → test

### 0.3 Design Reference
Design system ini diekstrak dari referensi visual landing page (soft gradients, card-based layout, clear hierarchy) dan disesuaikan dengan:
- **Brand identity:** Professional yet warm, trustworthy, efficient
- **Use case:** Marketplace kost dengan approval workflow, status-based UI, verification transparency
- **Tech constraints:** Server-rendered (Blade), progressive enhancement (Alpine.js), utility-first CSS (Tailwind 4.0)

---

## 1. Design Principles

### 1.1 Clear Information Hierarchy
**Apa:** Informasi paling penting harus menonjol secara visual (size, color, position).  
**Kenapa:** User perlu cepat memahami status rental, harga, availability tanpa membaca detail.  
**Bagaimana:**
- **Status rental** selalu visible via colored badge di atas fold (Pending = warning, Active = success, dll)
- **Harga** ditampilkan dengan font size 2x lebih besar dari body text (text-2xl bold)
- **Call-to-action utama** menggunakan primary button (solid color, high contrast) di posisi sticky/fixed (sidebar atau bottom bar mobile)
- **Document requirements checklist** front-and-center di rental detail page — progress bar visual
- **Timeline rental** (pending → paid → confirmed → active → completed) ditampilkan sebagai stepper horizontal dengan status saat ini highlighted

**Contoh:** Kost card di marketplace → Thumbnail (largest element) → Nama kost (text-lg bold) → Harga (text-2xl bold primary color) → Location (text-sm gray) → Rating (icon + number).

---

### 1.2 Trust & Transparency
**Apa:** System harus menunjukkan proses verification/approval secara eksplisit, tidak menyembunyikan rejection reason.  
**Kenapa:** Trust adalah kunci marketplace — Tenant perlu yakin kost terverifikasi, Admin perlu transparansi kenapa submission ditolak.  
**Bagaimana:**
- **Verified badge** untuk kost approved (green checkmark icon + "Verified" text)
- **Approval workflow visualization:** Draft → Pending Review → Approved/Rejected → Active (stepper UI dengan icon per state)
- **Rejection reason** ditampilkan dengan clear callout box (warning color, icon, reason text, re-submit CTA)
- **Real-time occupancy status:** "3 kamar tersedia dari 10 total" (bukan cache 1 jam lalu) — calculated on-the-fly
- **Admin identity:** Saat payment/document diverifikasi, tampilkan "Diverifikasi oleh Admin [Nama] pada [Tanggal]"

**Contoh:** Kost submission rejected → Callout box berwarna red/50 dengan icon ⚠, "Submission ditolak: [alasan dari Super Admin]", button "Revisi Kost".

---

### 1.3 Efficiency & Speed
**Apa:** Minimize jumlah klik/step untuk complete task utama.  
**Kenapa:** User (terutama Admin yang proses banyak verification) butuh workflow cepat, tidak banyak navigasi bolak-balik.  
**Bagaimana:**
- **Primary actions** selalu accessible dalam 1-2 klik dari dashboard (contoh: Admin dashboard → "Verifikasi Payment" badge count → klik langsung buka modal verification tanpa perlu navigate ke halaman lain)
- **Smart defaults:** Contract start date default = today + 4 days (FR-122), duration default = 1 month
- **Bulk actions:** Multi-select documents untuk verify sekaligus (future enhancement, bukan MVP tapi desain harus siap scale)
- **Keyboard shortcuts:** Admin/Super Admin context → `/` untuk focus search, `Esc` untuk close modal, `Tab` navigation optimized
- **Inline editing:** Admin edit kost basic info tidak perlu redirect ke halaman edit terpisah — inline editable fields dengan save button

**Contoh:** Payment verification → Admin buka rental detail → Section "Payment Proof" dengan thumbnail → Button "Approve" dan "Reject" langsung di samping thumbnail → Modal confirmation → Done (tidak perlu navigate ke halaman payment terpisah).

---

### 1.4 Accessibility First
**Apa:** Semua user (including screen reader users, keyboard-only users, low vision) dapat menggunakan aplikasi dengan baik.  
**Kenapa:** NFR requirement + ethical responsibility. WCAG 2.1 AA adalah target minimum.  
**Bagaimana:**
- **Semantic HTML:** Gunakan `<nav>`, `<main>`, `<article>`, `<aside>`, `<button>` sesuai fungsi (bukan `<div>` untuk semua)
- **Keyboard navigation:** Semua interactive element (button, link, input) harus tabbable, focus indicator visible (`focus-visible:ring-2 ring-primary-500`)
- **Screen reader support:** ARIA labels untuk icon-only buttons, `aria-live` untuk dynamic content (toast notification, countdown timer), `aria-describedby` untuk error messages
- **Color contrast:** Text minimum 4.5:1, UI components minimum 3:1. Gunakan contrast checker online atau browser devtools
- **Focus management:** Saat modal open, focus trap dalam modal, `Esc` untuk close modal, focus kembali ke trigger button saat close
- **Form labels:** Setiap input wajib punya `<label>` yang properly associated via `for` attribute, atau `aria-label` untuk inline input

**Testing:** Manual test dengan keyboard saja (tanpa mouse), test dengan screen reader (NVDA/JAWS Windows, VoiceOver macOS/iOS), automated test dengan axe DevTools browser extension.

---

## 2. Design Tokens (Tailwind CSS 4.0 Compatible)

### 2.1 Color Palette

#### Primary Colors — Soft Blue (Trust, Marketplace)
```css
--color-primary-50: #EFF6FF;   /* Lightest background */
--color-primary-100: #DBEAFE;  /* Hover state light */
--color-primary-200: #BFDBFE;  /* Disabled state */
--color-primary-300: #93C5FD;  /* Borders */
--color-primary-400: #60A5FA;  /* Icons */
--color-primary-500: #3B82F6;  /* Primary button default */
--color-primary-600: #2563EB;  /* Primary button hover */
--color-primary-700: #1D4ED8;  /* Primary button active */
--color-primary-800: #1E40AF;  /* Dark mode primary */
--color-primary-900: #1E3A8A;  /* Darkest */
```

**Usage:**
- Primary CTA buttons: `bg-primary-600 hover:bg-primary-700`
- Links: `text-primary-600 hover:text-primary-700`
- Focus rings: `focus-visible:ring-2 ring-primary-500`
- Active navigation: `bg-primary-50 text-primary-700`

#### Secondary Colors — Soft Amber (Action, Pricing, Highlight)
```css
--color-secondary-50: #FFFBEB;
--color-secondary-100: #FEF3C7;
--color-secondary-200: #FDE68A;
--color-secondary-300: #FCD34D;
--color-secondary-400: #FBBF24;
--color-secondary-500: #F59E0B;  /* Secondary button default */
--color-secondary-600: #D97706;  /* Secondary button hover */
--color-secondary-700: #B45309;
--color-secondary-800: #92400E;
--color-secondary-900: #78350F;
```

**Usage:**
- Secondary CTA: `bg-secondary-500 hover:bg-secondary-600`
- Price highlight: `text-secondary-600 font-bold`
- Warning accents: `border-secondary-400`

#### Accent Color — Soft Purple (Premium, Highlights)
```css
--color-accent-400: #C084FC;
--color-accent-500: #A855F7;
--color-accent-600: #9333EA;
```

**Usage (minimal):**
- Premium badge: `bg-accent-500 text-white`
- Decorative gradient accents

#### Semantic Colors
```css
/* Success — Approved, Verified, Completed, Active */
--color-success: #10B981;      /* green-500 */
--color-success-light: #D1FAE5; /* green-100 */

/* Warning — Pending, Review Needed */
--color-warning: #F59E0B;       /* amber-500 */
--color-warning-light: #FEF3C7; /* amber-100 */

/* Error — Rejected, Cancelled, Failed, Danger */
--color-error: #EF4444;         /* red-500 */
--color-error-light: #FEE2E2;   /* red-100 */

/* Info — Informational, Draft */
--color-info: #3B82F6;          /* blue-500 */
--color-info-light: #DBEAFE;    /* blue-100 */
```

**Usage:**
- Success badge: `bg-success/10 text-success` (10% opacity background for subtle fill)
- Warning badge: `bg-warning/10 text-warning`
- Error text: `text-error`
- Info callout: `bg-info-light border-info`

#### Neutral Gray (UI Base)
```css
--color-gray-50: #F9FAFB;   /* Page background */
--color-gray-100: #F3F4F6;  /* Card background hover */
--color-gray-200: #E5E7EB;  /* Borders */
--color-gray-300: #D1D5DB;  /* Input borders */
--color-gray-400: #9CA3AF;  /* Placeholder text */
--color-gray-500: #6B7280;  /* Secondary text */
--color-gray-600: #4B5563;  /* Body text */
--color-gray-700: #374151;  /* Headings */
--color-gray-800: #1F2937;  /* Dark headings */
--color-gray-900: #111827;  /* Darkest text */
```

**Usage:**
- Page background: `bg-gray-50`
- Card background: `bg-white`
- Body text: `text-gray-600`
- Headings: `text-gray-900`
- Borders: `border-gray-200`
- Disabled state: `text-gray-400 bg-gray-100`

#### Background Gradients (from reference design)
```css
/* Hero gradient — soft pastel beige → lavender → sky blue */
--gradient-hero: linear-gradient(135deg, #FEF3C7 0%, #DBEAFE 50%, #E0E7FF 100%);

/* Card hover — subtle lift effect */
--gradient-card-hover: linear-gradient(135deg, #FFFFFF 0%, #F9FAFB 100%);
```

**Usage Tailwind:**
```html
<div class="bg-gradient-to-br from-amber-100 via-blue-100 to-indigo-100">
  <!-- Hero section -->
</div>
```

---

### 2.2 Typography Scale

#### Font Families
```css
/* Primary — Figtree (already in project via Laravel Breeze) */
--font-sans: 'Figtree', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif;

/* Monospace — for OTP codes, room codes, technical identifiers */
--font-mono: ui-monospace, 'SF Mono', Consolas, 'Liberation Mono', Menlo, monospace;
```

**Tailwind Config (sudah setup):**
```js
fontFamily: {
  sans: ['Figtree', ...defaultTheme.fontFamily.sans],
  mono: [...defaultTheme.fontFamily.mono],
}
```

#### Type Scale (Fluid Responsive)
```css
/* Fluid typography menggunakan clamp() untuk smooth scaling antar breakpoint */
--text-xs: clamp(0.75rem, 0.7rem + 0.25vw, 0.875rem);     /* 12px → 14px */
--text-sm: clamp(0.875rem, 0.8rem + 0.375vw, 1rem);       /* 14px → 16px */
--text-base: clamp(1rem, 0.95rem + 0.25vw, 1.125rem);     /* 16px → 18px */
--text-lg: clamp(1.125rem, 1.05rem + 0.375vw, 1.25rem);   /* 18px → 20px */
--text-xl: clamp(1.25rem, 1.15rem + 0.5vw, 1.5rem);       /* 20px → 24px */
--text-2xl: clamp(1.5rem, 1.35rem + 0.75vw, 1.875rem);    /* 24px → 30px */
--text-3xl: clamp(1.875rem, 1.65rem + 1.125vw, 2.25rem);  /* 30px → 36px */
--text-4xl: clamp(2.25rem, 1.95rem + 1.5vw, 3rem);        /* 36px → 48px */
--text-5xl: clamp(3rem, 2.5rem + 2.5vw, 4rem);            /* 48px → 64px — Hero headlines only */
```

**Tailwind Usage:**
- Body text: `text-base` (16-18px fluid)
- Small text (labels, captions): `text-sm`
- Tiny text (helper text, badges): `text-xs`
- Card titles: `text-lg font-semibold`
- Section headings: `text-2xl font-bold`
- Page headlines: `text-3xl md:text-4xl font-bold`
- Hero headlines: `text-4xl md:text-5xl font-bold`

#### Font Weights
```css
--font-normal: 400;      /* Body text */
--font-medium: 500;      /* Emphasized text, labels */
--font-semibold: 600;    /* Subheadings, card titles, buttons */
--font-bold: 700;        /* Headings, CTAs, pricing */
```

**Tailwind:**
- Default: `font-normal` (400)
- Labels: `font-medium`
- Buttons, card titles: `font-semibold`
- Headings, prices: `font-bold`

#### Line Heights
```css
--leading-tight: 1.25;    /* Headings (compact) */
--leading-snug: 1.375;    /* Subheadings */
--leading-normal: 1.5;    /* Body text (default) */
--leading-relaxed: 1.625; /* Long-form content (descriptions) */
```

**Tailwind:**
- Headings: `leading-tight`
- Body text: `leading-normal` (default)
- Descriptions: `leading-relaxed`

---

### 2.3 Spacing System (8px Base Grid)

```css
/* Base unit: 0.25rem = 4px, scale 4n untuk alignment grid 8px */
--space-0: 0;
--space-1: 0.25rem;  /* 4px — tight spacing (icon + text gap) */
--space-2: 0.5rem;   /* 8px — base unit */
--space-3: 0.75rem;  /* 12px */
--space-4: 1rem;     /* 16px — default spacing (padding, margin) */
--space-5: 1.25rem;  /* 20px */
--space-6: 1.5rem;   /* 24px — card padding */
--space-8: 2rem;     /* 32px — section spacing */
--space-10: 2.5rem;  /* 40px */
--space-12: 3rem;    /* 48px — large section spacing */
--space-16: 4rem;    /* 64px — hero vertical spacing */
--space-20: 5rem;    /* 80px */
--space-24: 6rem;    /* 96px — extra large spacing */
```

**Usage Guidelines:**
- Inline elements (icon + text): `gap-1` atau `gap-2` (4-8px)
- Form field vertical spacing: `space-y-4` (16px)
- Card internal padding: `p-5` atau `p-6` (20-24px)
- Section vertical spacing: `py-12` atau `py-16` (48-64px)
- Hero section: `py-20` atau `py-24` (80-96px)

---

### 2.4 Border Radius

```css
--radius-sm: 0.25rem;   /* 4px — badges, tags */
--radius-md: 0.5rem;    /* 8px — buttons, inputs (default) */
--radius-lg: 0.75rem;   /* 12px — cards */
--radius-xl: 1rem;      /* 16px — modals, large cards */
--radius-2xl: 1.5rem;   /* 24px — hero sections */
--radius-full: 9999px;  /* circular — avatars, pill buttons */
```

**Tailwind:**
- Badges: `rounded-sm`
- Buttons, inputs: `rounded-lg` (8px)
- Cards: `rounded-xl` (16px)
- Avatars: `rounded-full`
- Status pills: `rounded-full`

---

### 2.5 Shadows (Soft, Layered — from reference design)

```css
/* Elevation scale — semakin tinggi elevation, semakin tebal shadow */
--shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  /* Subtle border alternative, hover state */

--shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  /* Default card elevation */

--shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  /* Hover card elevation */

--shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  /* Modal/dialog */

--shadow-2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  /* Large modal, sticky nav */

--shadow-inner: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06);
  /* Input focus state alternative */
```

**Tailwind Usage:**
- Cards: `shadow-md hover:shadow-lg transition-shadow`
- Buttons: `shadow-sm hover:shadow-md`
- Modals: `shadow-xl`
- Sticky nav: `shadow-sm`

---

### 2.6 Transitions & Animation

```css
--transition-fast: 150ms ease-in-out;   /* Micro-interactions (hover) */
--transition-base: 200ms ease-in-out;   /* Default (most UI changes) */
--transition-slow: 300ms ease-in-out;   /* Modal enter/exit, page transitions */
```

**Tailwind:**
```html
<!-- Button hover -->
<button class="transition-all duration-200 hover:shadow-lg">

<!-- Card hover -->
<div class="transition-transform duration-300 hover:scale-105">

<!-- Modal enter/exit -->
<div x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 scale-95"
     x-transition:enter-end="opacity-100 scale-100">
```

**Easing Functions:**
- `ease-in-out` — default (most cases)
- `ease-out` — enter animations (modal, dropdown)
- `ease-in` — exit animations
- `ease-linear` — loading spinners

---

## 3. Component Library (35+ Components)

### 3.1 Buttons

#### Primary Button (Main CTA)
```html
<button type="submit" 
  class="px-6 py-3 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
  Book Now
</button>
```

**Variants:**
```html
<!-- With icon -->
<button class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg shadow-md transition-all duration-200">
  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
  </svg>
  Add Kost
</button>

<!-- Loading state (Alpine.js) -->
<button x-data="{ loading: false }" 
  @click="loading = true"
  :disabled="loading"
  class="px-6 py-3 bg-primary-600 text-white rounded-lg disabled:opacity-50">
  <span x-show="!loading">Submit</span>
  <span x-show="loading" class="inline-flex items-center gap-2">
    <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    Processing...
  </span>
</button>
```

#### Secondary Button
```html
<button class="px-6 py-3 bg-secondary-500 hover:bg-secondary-600 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
  View Details
</button>
```

#### Outline Button (Secondary Action)
```html
<button class="px-6 py-3 border-2 border-gray-300 hover:border-primary-500 text-gray-700 hover:text-primary-600 font-semibold rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
  Cancel
</button>
```

#### Ghost Button (Tertiary Action)
```html
<button class="px-4 py-2 text-gray-600 hover:text-primary-600 hover:bg-gray-50 rounded-lg transition-all duration-200">
  Skip
</button>
```

#### Danger Button (Destructive Action)
```html
<button class="px-6 py-3 bg-error hover:bg-red-600 active:bg-red-700 text-white font-semibold rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
  Reject Document
</button>
```

#### Link Button (Text Link Styled as Button)
```html
<a href="/marketplace" class="inline-block px-6 py-3 text-primary-600 hover:text-primary-700 font-semibold hover:underline transition-colors duration-200">
  Browse Marketplace
</a>
```

**Size Variants:**
```html
<!-- Small (sm) -->
<button class="px-4 py-2 text-sm bg-primary-600 text-white rounded-lg">
  Small Button
</button>

<!-- Medium (default) -->
<button class="px-6 py-3 text-base bg-primary-600 text-white rounded-lg">
  Medium Button
</button>

<!-- Large (lg) -->
<button class="px-8 py-4 text-lg bg-primary-600 text-white rounded-lg">
  Large Button
</button>
```

**Full Width:**
```html
<button class="w-full px-6 py-3 bg-primary-600 text-white rounded-lg">
  Full Width Button
</button>
```

---

### 3.2 Form Inputs

#### Text Input (Standard)
```html
<div class="space-y-2">
  <label for="name" class="block text-sm font-medium text-gray-700">
    Nama Lengkap <span class="text-error">*</span>
  </label>
  <input type="text" 
    id="name" 
    name="name" 
    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all placeholder:text-gray-400" 
    placeholder="Masukkan nama lengkap"
    required>
  <p class="text-xs text-gray-500">Sesuai dengan dokumen identitas</p>
</div>
```

#### Text Input with Error State
```html
<div class="space-y-2">
  <label for="email" class="block text-sm font-medium text-gray-700">
    Email <span class="text-error">*</span>
  </label>
  <input type="email" 
    id="email" 
    name="email" 
    class="w-full px-4 py-3 border-2 border-error rounded-lg focus:ring-2 focus:ring-error"
    aria-invalid="true"
    aria-describedby="email-error">
  <p id="email-error" class="text-sm text-error flex items-center gap-1">
    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
    </svg>
    Format email tidak valid
  </p>
</div>
```

#### Textarea
```html
<div class="space-y-2">
  <label for="description" class="block text-sm font-medium text-gray-700">
    Deskripsi Kost <span class="text-error">*</span>
  </label>
  <textarea id="description" 
    name="description" 
    rows="4" 
    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all resize-none"
    placeholder="Jelaskan keunggulan kost Anda..."></textarea>
  <p class="text-xs text-gray-500">Minimum 50 karakter</p>
</div>
```

#### Select Dropdown
```html
<div class="space-y-2">
  <label for="category" class="block text-sm font-medium text-gray-700">
    Kategori Kost <span class="text-error">*</span>
  </label>
  <select id="category" 
    name="category" 
    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white">
    <option value="">Pilih kategori...</option>
    <option value="putra">Kost Putra</option>
    <option value="putri">Kost Putri</option>
    <option value="campur">Kost Campur</option>
  </select>
</div>
```

#### File Upload (Drag & Drop)
```html
<div class="space-y-2">
  <label class="block text-sm font-medium text-gray-700">
    Upload Gambar Kost <span class="text-error">*</span>
  </label>
  <div x-data="{ dragging: false }" 
    @dragover.prevent="dragging = true"
    @dragleave.prevent="dragging = false"
    @drop.prevent="dragging = false; /* handle file drop */"
    :class="dragging ? 'border-primary-500 bg-primary-50' : 'border-gray-300'"
    class="border-2 border-dashed rounded-lg p-8 text-center hover:border-primary-400 transition-all cursor-pointer">
    <input type="file" id="images" name="images[]" multiple accept="image/*" class="sr-only">
    <label for="images" class="cursor-pointer">
      <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      <p class="mt-2 text-sm text-gray-600">
        <span class="text-primary-600 font-semibold">Upload file</span>
        atau drag & drop
      </p>
      <p class="text-xs text-gray-500 mt-1">PNG, JPG hingga 2MB (max 10 gambar)</p>
    </label>
  </div>
</div>
```

#### Checkbox
```html
<label class="flex items-start space-x-3 cursor-pointer group">
  <input type="checkbox" 
    name="agree" 
    class="mt-1 w-5 h-5 text-primary-600 border-gray-300 rounded focus:ring-2 focus:ring-primary-500 transition-all">
  <span class="text-sm text-gray-700 group-hover:text-gray-900">
    Saya menyetujui <a href="/terms" class="text-primary-600 hover:underline">syarat dan ketentuan</a> yang berlaku
  </span>
</label>
```

#### Radio Button Group
```html
<fieldset class="space-y-3">
  <legend class="block text-sm font-medium text-gray-700 mb-2">
    Durasi Sewa <span class="text-error">*</span>
  </legend>
  
  <label class="flex items-center space-x-3 cursor-pointer p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-all">
    <input type="radio" 
      name="duration_unit" 
      value="month" 
      class="w-5 h-5 text-primary-600 border-gray-300 focus:ring-2 focus:ring-primary-500"
      checked>
    <div class="flex-1">
      <span class="text-sm font-medium text-gray-900">Per Bulan</span>
      <p class="text-xs text-gray-500">Cicilan bulanan</p>
    </div>
  </label>
  
  <label class="flex items-center space-x-3 cursor-pointer p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-all">
    <input type="radio" 
      name="duration_unit" 
      value="week" 
      class="w-5 h-5 text-primary-600 border-gray-300 focus:ring-2 focus:ring-primary-500">
    <div class="flex-1">
      <span class="text-sm font-medium text-gray-900">Per Minggu</span>
      <p class="text-xs text-gray-500">Pembayaran mingguan</p>
    </div>
  </label>
</fieldset>
```

#### Number Input with Step Buttons
```html
<div class="space-y-2">
  <label for="duration_value" class="block text-sm font-medium text-gray-700">
    Berapa lama? <span class="text-error">*</span>
  </label>
  <div class="flex items-center gap-2">
    <button type="button" 
      @click="$refs.duration.stepDown()"
      class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50">
      <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
      </svg>
    </button>
    <input type="number" 
      id="duration_value"
      name="duration_value" 
      x-ref="duration"
      min="1" 
      max="12" 
      value="3"
      class="flex-1 px-4 py-3 border border-gray-300 rounded-lg text-center text-lg font-semibold focus:ring-2 focus:ring-primary-500">
    <button type="button" 
      @click="$refs.duration.stepUp()"
      class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50">
      <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
      </svg>
    </button>
  </div>
  <p class="text-xs text-gray-500">Pilih durasi 1-12 bulan</p>
</div>
```

#### Password Input with Toggle Visibility
```html
<div class="space-y-2" x-data="{ showPassword: false }">
  <label for="password" class="block text-sm font-medium text-gray-700">
    Password <span class="text-error">*</span>
  </label>
  <div class="relative">
    <input :type="showPassword ? 'text' : 'password'" 
      id="password" 
      name="password" 
      class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
      placeholder="Minimal 8 karakter">
    <button type="button" 
      @click="showPassword = !showPassword"
      class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
      <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
      </svg>
      <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
      </svg>
    </button>
  </div>
  <p class="text-xs text-gray-500">Gunakan kombinasi huruf, angka, dan simbol</p>
</div>
```

---

### 3.3 Cards

#### Kost Card (Marketplace Grid)
```html
<article class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group cursor-pointer">
  <a href="/marketplace/kosts/kost-mawar-indah" class="block">
    <!-- Image -->
    <div class="relative h-48 overflow-hidden bg-gray-100">
      <img src="/storage/kosts/thumb-1.jpg" 
        alt="Kost Mawar Indah" 
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
      <!-- Verified Badge -->
      <div class="absolute top-3 right-3 px-3 py-1 bg-success text-white text-xs font-semibold rounded-full flex items-center gap-1 shadow-lg">
        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        Verified
      </div>
    </div>
    
    <!-- Content -->
    <div class="p-5">
      <h3 class="text-lg font-semibold text-gray-900 line-clamp-1 group-hover:text-primary-600 transition-colors">
        Kost Mawar Indah
      </h3>
      <p class="text-sm text-gray-600 mt-1 flex items-center">
        <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        Bandung, Jawa Barat
      </p>
      
      <div class="mt-4 flex items-baseline justify-between">
        <div>
          <span class="text-2xl font-bold text-gray-900">Rp 1.2jt</span>
          <span class="text-sm text-gray-500">/bulan</span>
        </div>
        <div class="flex items-center text-sm">
          <svg class="w-5 h-5 text-yellow-400 fill-current" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
          </svg>
          <span class="ml-1 font-semibold text-gray-700">4.8</span>
          <span class="ml-1 text-gray-500">(32)</span>
        </div>
      </div>
      
      <!-- Optional: Tags -->
      <div class="mt-3 flex flex-wrap gap-2">
        <span class="px-2 py-1 bg-primary-50 text-primary-700 text-xs font-medium rounded-full">
          WiFi
        </span>
        <span class="px-2 py-1 bg-primary-50 text-primary-700 text-xs font-medium rounded-full">
          AC
        </span>
        <span class="px-2 py-1 bg-primary-50 text-primary-700 text-xs font-medium rounded-full">
          Kamar Mandi Dalam
        </span>
      </div>
    </div>
  </a>
</article>
```

#### Room Type Card
```html
<div class="border border-gray-200 rounded-lg p-5 hover:border-primary-300 hover:shadow-md transition-all cursor-pointer">
  <div class="flex justify-between items-start">
    <div class="flex-1">
      <h4 class="font-semibold text-gray-900">Kamar Standard</h4>
      <p class="text-sm text-gray-600 mt-1">3x4 meter • Max 2 orang</p>
      
      <!-- Facilities -->
      <div class="mt-3 flex flex-wrap gap-2">
        <span class="inline-flex items-center gap-1 text-xs text-gray-600">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          WiFi
        </span>
        <span class="inline-flex items-center gap-1 text-xs text-gray-600">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          AC
        </span>
      </div>
    </div>
    
    <!-- Availability Badge -->
    <span class="px-3 py-1 bg-success/10 text-success text-xs font-semibold rounded-full whitespace-nowrap">
      3 kamar tersedia
    </span>
  </div>
  
  <div class="mt-4 pt-4 border-t border-gray-100 flex items-baseline justify-between">
    <div>
      <span class="text-xl font-bold text-gray-900">Rp 1.5jt</span>
      <span class="text-sm text-gray-500">/bulan</span>
    </div>
    <button class="px-4 py-2 text-sm font-semibold text-primary-600 hover:bg-primary-50 rounded-lg transition-all">
      Pilih Kamar
    </button>
  </div>
</div>
```

#### Rental Card (Tenant Dashboard)
```html
<div class="bg-white border-l-4 border-warning rounded-lg shadow-sm p-5 hover:shadow-md transition-shadow">
  <div class="flex justify-between items-start mb-3">
    <div class="flex-1">
      <h3 class="font-semibold text-gray-900">Kost Mawar Indah - Kamar A1</h3>
      <p class="text-sm text-gray-600 mt-1 flex items-center gap-2">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        1 Jan 2026 - 31 Mar 2026
      </p>
    </div>
    <span class="px-3 py-1 bg-warning/10 text-warning text-xs font-semibold rounded-full whitespace-nowrap">
      Pending Payment
    </span>
  </div>
  
  <div class="flex items-center justify-between pt-3 border-t border-gray-100">
    <span class="text-sm text-gray-600">Deadline pembayaran:</span>
    <span class="text-sm font-semibold text-error">23 jam lagi</span>
  </div>
  
  <div class="mt-4 flex gap-2">
    <a href="/rentals/123/payment" class="flex-1 px-4 py-2 bg-primary-600 text-white text-sm font-semibold rounded-lg text-center hover:bg-primary-700 transition-all">
      Upload Bukti Bayar
    </a>
    <button class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition-all">
      Detail
    </button>
  </div>
</div>
```

---

### 3.4 Status Badges

```html
<!-- Draft -->
<span class="inline-flex items-center gap-1 px-3 py-1 bg-gray-100 text-gray-700 text-xs font-semibold rounded-full">
  <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
  </svg>
  Draft
</span>

<!-- Pending Review -->
<span class="inline-flex items-center gap-1 px-3 py-1 bg-warning/10 text-warning text-xs font-semibold rounded-full">
  <svg class="w-3 h-3 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
  </svg>
  Pending Review
</span>

<!-- Approved -->
<span class="inline-flex items-center gap-1 px-3 py-1 bg-success/10 text-success text-xs font-semibold rounded-full">
  <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
  </svg>
  Approved
</span>

<!-- Active -->
<span class="inline-flex items-center gap-1 px-3 py-1 bg-primary/10 text-primary-600 text-xs font-semibold rounded-full">
  <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
  </svg>
  Active
</span>

<!-- Rejected -->
<span class="inline-flex items-center gap-1 px-3 py-1 bg-error/10 text-error text-xs font-semibold rounded-full">
  <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
  </svg>
  Rejected
</span>

<!-- Completed -->
<span class="inline-flex items-center gap-1 px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">
  <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
  </svg>
  Completed
</span>

<!-- Cancelled -->
<span class="inline-flex items-center gap-1 px-3 py-1 bg-gray-200 text-gray-600 text-xs font-semibold rounded-full">
  <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/>
  </svg>
  Cancelled
</span>
```

Apakah saya lanjutkan menulis sisa DESIGN.md (sections 3.5-9) atau Anda ingin saya finalize file ini dulu lalu lanjut ke PAGES.md?
### 3.5 Modal/Dialog

```html
<!-- Modal Container -->
<div x-data="{ open: false }" x-cloak>
  <!-- Trigger Button -->
  <button @click="open = true" class="px-6 py-3 bg-error text-white rounded-lg">
    Cancel Rental
  </button>
  
  <!-- Modal Overlay + Content -->
  <div x-show="open" 
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @keydown.escape.window="open = false"
    class="fixed inset-0 z-50 overflow-y-auto" 
    aria-labelledby="modal-title" 
    role="dialog" 
    aria-modal="true">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="open = false"></div>
    
    <!-- Modal Content -->
    <div class="flex min-h-full items-center justify-center p-4">
      <div x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="relative transform overflow-hidden rounded-xl bg-white shadow-2xl transition-all w-full max-w-lg"
        @click.stop>
        
        <!-- Header -->
        <div class="px-6 pt-6 pb-4 border-b border-gray-100">
          <div class="flex items-start justify-between">
            <h3 id="modal-title" class="text-xl font-semibold text-gray-900">
              Konfirmasi Pembatalan Rental
            </h3>
            <button @click="open = false" 
              class="ml-3 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-lg p-1">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>
        
        <!-- Body -->
        <div class="px-6 py-5">
          <div class="flex items-start gap-4">
            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-error/10 flex items-center justify-center">
              <svg class="w-6 h-6 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
            </div>
            <div class="flex-1">
              <p class="text-sm text-gray-600">
                Apakah Anda yakin ingin membatalkan rental ini? Tindakan ini tidak dapat dibatalkan.
              </p>
              <div class="mt-4">
                <label for="cancel_reason" class="block text-sm font-medium text-gray-700 mb-2">
                  Alasan pembatalan <span class="text-error">*</span>
                </label>
                <textarea id="cancel_reason" 
                  rows="3" 
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                  placeholder="Jelaskan alasan Anda membatalkan rental..."></textarea>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Footer -->
        <div class="px-6 py-4 bg-gray-50 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
          <button @click="open = false" 
            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all">
            Batal
          </button>
          <button class="px-4 py-2 bg-error text-white rounded-lg hover:bg-red-600 transition-all">
            Ya, Batalkan Rental
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
```

**Alpine.js x-cloak Setup (add to CSS):**
```css
[x-cloak] { display: none !important; }
```

---

### 3.6 Navigation

#### Public Navigation (Marketplace)
```html
<nav class="bg-white shadow-sm sticky top-0 z-40 border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center h-16">
      <!-- Logo -->
      <div class="flex items-center">
        <a href="/" class="flex items-center group">
          <img src="/logo.svg" alt="SewaKost" class="h-8 w-auto">
          <span class="ml-2 text-xl font-bold text-gray-900 group-hover:text-primary-600 transition-colors">
            SewaKost
          </span>
        </a>
      </div>
      
      <!-- Desktop Navigation -->
      <div class="hidden md:flex items-center space-x-8">
        <a href="/marketplace" 
          class="text-gray-700 hover:text-primary-600 px-3 py-2 text-sm font-medium transition-colors">
          Cari Kost
        </a>
        @auth
          <a href="/rentals" 
            class="text-gray-700 hover:text-primary-600 px-3 py-2 text-sm font-medium transition-colors">
            Rental Saya
          </a>
          <!-- User Dropdown -->
          <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" 
              class="flex items-center gap-2 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-lg p-1">
              <img src="{{ auth()->user()->avatar_url }}" 
                alt="{{ auth()->user()->first_name }}" 
                class="w-8 h-8 rounded-full ring-2 ring-gray-200">
              <svg class="w-4 h-4 text-gray-600" :class="{ 'rotate-180': open }" 
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>
            <!-- Dropdown Menu -->
            <div x-show="open" 
              @click.away="open = false"
              x-transition:enter="transition ease-out duration-200"
              x-transition:enter-start="opacity-0 scale-95"
              x-transition:enter-end="opacity-100 scale-100"
              class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 py-1">
              <a href="/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                Profil Saya
              </a>
              <a href="/rentals" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                Rental Saya
              </a>
              <hr class="my-1 border-gray-200">
              <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-error hover:bg-gray-50">
                  Logout
                </button>
              </form>
            </div>
          </div>
        @else
          <a href="/login" 
            class="text-gray-700 hover:text-primary-600 px-3 py-2 text-sm font-medium transition-colors">
            Masuk
          </a>
          <a href="/register" 
            class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg transition-all">
            Daftar
          </a>
        @endauth
      </div>
      
      <!-- Mobile Menu Button -->
      <div class="md:hidden">
        <button @click="mobileMenuOpen = !mobileMenuOpen" 
          class="p-2 text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-lg">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>
      </div>
    </div>
  </div>
  
  <!-- Mobile Menu -->
  <div x-show="mobileMenuOpen" 
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-1"
    x-transition:enter-end="opacity-100 translate-y-0"
    class="md:hidden border-t border-gray-200">
    <div class="px-2 pt-2 pb-3 space-y-1">
      <a href="/marketplace" class="block px-3 py-2 rounded-lg text-base font-medium text-gray-700 hover:bg-gray-50">
        Cari Kost
      </a>
      @auth
        <a href="/rentals" class="block px-3 py-2 rounded-lg text-base font-medium text-gray-700 hover:bg-gray-50">
          Rental Saya
        </a>
        <a href="/profile" class="block px-3 py-2 rounded-lg text-base font-medium text-gray-700 hover:bg-gray-50">
          Profil Saya
        </a>
        <form method="POST" action="/logout">
          @csrf
          <button type="submit" class="w-full text-left px-3 py-2 rounded-lg text-base font-medium text-error hover:bg-gray-50">
            Logout
          </button>
        </form>
      @else
        <a href="/login" class="block px-3 py-2 rounded-lg text-base font-medium text-gray-700 hover:bg-gray-50">
          Masuk
        </a>
        <a href="/register" class="block px-3 py-2 rounded-lg text-base font-medium bg-primary-600 text-white hover:bg-primary-700">
          Daftar
        </a>
      @endauth
    </div>
  </div>
</nav>
```

#### Admin Sidebar Navigation
```html
<aside class="w-64 bg-gray-900 text-white min-h-screen fixed left-0 top-0 flex flex-col">
  <!-- Brand -->
  <div class="p-6 border-b border-gray-800">
    <h2 class="text-xl font-bold">SewaKost Admin</h2>
    <p class="text-sm text-gray-400 mt-1">{{ auth()->user()->first_name }}</p>
  </div>
  
  <!-- Navigation Links -->
  <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
    <a href="/admin/dashboard" 
      class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->is('admin/dashboard') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition-all">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
      </svg>
      <span class="font-medium">Dashboard</span>
    </a>
    
    <a href="/admin/kosts" 
      class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->is('admin/kosts*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition-all">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
      </svg>
      <span class="font-medium">Kost Saya</span>
    </a>
    
    <a href="/admin/rentals" 
      class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->is('admin/rentals*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition-all">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
      </svg>
      <span class="font-medium">Rental Management</span>
      @if($pendingVerifications > 0)
        <span class="ml-auto px-2 py-0.5 bg-warning text-white text-xs font-semibold rounded-full">
          {{ $pendingVerifications }}
        </span>
      @endif
    </a>
  </nav>
  
  <!-- Footer -->
  <div class="p-4 border-t border-gray-800">
    <form method="POST" action="/logout">
      @csrf
      <button type="submit" class="flex items-center gap-3 w-full px-3 py-2 text-gray-300 hover:bg-gray-800 hover:text-white rounded-lg transition-all">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
        </svg>
        <span class="font-medium">Logout</span>
      </button>
    </form>
  </div>
</aside>

<!-- Main Content Area (with sidebar offset) -->
<div class="ml-64 min-h-screen bg-gray-50">
  <!-- Content here -->
</div>
```

---

### 3.7 Tables

```html
<div class="overflow-hidden shadow-sm ring-1 ring-gray-200 rounded-lg">
  <table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
      <tr>
        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
          Rental ID
        </th>
        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
          Tenant
        </th>
        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
          Kost
        </th>
        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
          Status
        </th>
        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
          Start Date
        </th>
        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
          Actions
        </th>
      </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
      @forelse($rentals as $rental)
        <tr class="hover:bg-gray-50 transition-colors">
          <td class="px-6 py-4 whitespace-nowrap">
            <a href="/admin/rentals/{{ $rental->id }}" 
              class="text-sm font-medium text-primary-600 hover:text-primary-700 hover:underline">
              #{{ $rental->rental_number }}
            </a>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="flex items-center">
              <img src="{{ $rental->tenant->avatar_url }}" 
                alt="{{ $rental->tenant->full_name }}" 
                class="w-8 h-8 rounded-full mr-3">
              <div class="text-sm">
                <div class="font-medium text-gray-900">{{ $rental->tenant->full_name }}</div>
                <div class="text-gray-500">{{ $rental->tenant->email }}</div>
              </div>
            </div>
          </td>
          <td class="px-6 py-4">
            <div class="text-sm text-gray-900">{{ $rental->kost->name }}</div>
            <div class="text-sm text-gray-500">{{ $rental->room->code }}</div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            @include('components.status-badge', ['status' => $rental->status])
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
            {{ $rental->start_date->format('d M Y') }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            <a href="/admin/rentals/{{ $rental->id }}" 
              class="text-primary-600 hover:text-primary-700 mr-4">
              View
            </a>
            @if($rental->payment->status === 'pending')
              <button class="text-success hover:text-green-700">
                Verify
              </button>
            @endif
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="px-6 py-12 text-center text-gray-500">
            Belum ada data rental
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

<!-- Pagination -->
<div class="mt-4">
  {{ $rentals->links() }}
</div>
```

---

### 3.8 Empty States

```html
<div class="flex flex-col items-center justify-center py-12 px-4">
  <!-- Icon -->
  <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
  </svg>
  
  <!-- Message -->
  <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada rental</h3>
  <p class="text-sm text-gray-600 text-center max-w-sm mb-6">
    Mulai cari kost impian Anda di marketplace dan lakukan booking pertama Anda
  </p>
  
  <!-- CTA -->
  <a href="/marketplace" 
    class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition-all">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
    </svg>
    Cari Kost Sekarang
  </a>
</div>
```

**Variants:**
```html
<!-- No Search Results -->
<div class="text-center py-12">
  <svg class="mx-auto w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
  </svg>
  <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada hasil ditemukan</h3>
  <p class="text-sm text-gray-600 mb-4">Coba ubah filter atau kata kunci pencarian Anda</p>
  <button @click="clearFilters()" class="text-primary-600 hover:underline text-sm font-medium">
    Reset Filter
  </button>
</div>

<!-- Permission Denied -->
<div class="text-center py-12">
  <svg class="mx-auto w-16 h-16 text-error mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
  </svg>
  <h3 class="text-lg font-medium text-gray-900 mb-2">Akses Ditolak</h3>
  <p class="text-sm text-gray-600">Anda tidak memiliki izin untuk mengakses halaman ini</p>
</div>
```

---

### 3.9 Loading States

#### Skeleton Card (for lazy loading)
```html
<div class="bg-white rounded-xl shadow-md p-5 animate-pulse">
  <!-- Image Skeleton -->
  <div class="h-48 bg-gray-200 rounded-lg mb-4"></div>
  
  <!-- Title Skeleton -->
  <div class="h-6 bg-gray-200 rounded w-3/4 mb-2"></div>
  
  <!-- Subtitle Skeleton -->
  <div class="h-4 bg-gray-200 rounded w-1/2 mb-4"></div>
  
  <!-- Price Skeleton -->
  <div class="flex justify-between items-center">
    <div class="h-8 bg-gray-200 rounded w-1/3"></div>
    <div class="h-4 bg-gray-200 rounded w-1/4"></div>
  </div>
</div>
```

#### Spinner (Inline)
```html
<div class="flex justify-center items-center py-12">
  <svg class="animate-spin h-10 w-10 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
  </svg>
  <span class="ml-3 text-gray-600">Loading...</span>
</div>
```

#### Full Page Loader
```html
<div class="fixed inset-0 bg-white bg-opacity-90 z-50 flex items-center justify-center">
  <div class="text-center">
    <svg class="animate-spin h-16 w-16 text-primary-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    <p class="text-lg font-medium text-gray-900">Memproses pembayaran...</p>
    <p class="text-sm text-gray-600 mt-1">Mohon tunggu sebentar</p>
  </div>
</div>
```

---

### 3.10 Toast/Alert Notifications

```html
<!-- Toast Container (Alpine.js Component) -->
<div x-data="toast()" 
  @toast.window="show($event.detail)"
  x-cloak>
  <div x-show="visible" 
    x-transition:enter="transition ease-out duration-300 transform"
    x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
    x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-y-0 opacity-100"
    x-transition:leave-end="translate-y-2 opacity-0"
    class="fixed top-4 right-4 z-50 max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5">
    <div class="p-4">
      <div class="flex items-start">
        <!-- Icon (dynamic based on type) -->
        <div class="flex-shrink-0">
          <svg x-show="type === 'success'" class="h-6 w-6 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg x-show="type === 'error'" class="h-6 w-6 text-error" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg x-show="type === 'warning'" class="h-6 w-6 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <svg x-show="type === 'info'" class="h-6 w-6 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        
        <!-- Content -->
        <div class="ml-3 flex-1">
          <p x-text="title" class="text-sm font-medium text-gray-900"></p>
          <p x-text="message" x-show="message" class="mt-1 text-sm text-gray-600"></p>
        </div>
        
        <!-- Close Button -->
        <button @click="visible = false" 
          class="ml-4 flex-shrink-0 text-gray-400 hover:text-gray-500 focus:outline-none">
          <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</div>

<script>
function toast() {
  return {
    visible: false,
    type: 'info',
    title: '',
    message: '',
    timeout: null,
    
    show(data) {
      this.type = data.type || 'info';
      this.title = data.title || '';
      this.message = data.message || '';
      this.visible = true;
      
      clearTimeout(this.timeout);
      this.timeout = setTimeout(() => {
        this.visible = false;
      }, data.duration || 5000);
    }
  }
}

// Usage: dispatch toast event
// window.dispatchEvent(new CustomEvent('toast', {
//   detail: {
//     type: 'success',
//     title: 'Payment berhasil diverifikasi',
//     message: 'Silakan upload dokumen administrasi',
//     duration: 5000
//   }
// }));
</script>
```

### 3.11 Progress/Timeline

#### Rental Status Timeline (Stepper)
```html
<div class="space-y-8">
  @foreach($statusHistory as $index => $history)
    <div class="relative flex items-start">
      <!-- Connecting Line (skip for last item) -->
      @if(!$loop->last)
        <div class="absolute top-0 left-5 -ml-px h-full w-0.5 bg-gray-300" aria-hidden="true"></div>
      @endif
      
      <!-- Icon Circle -->
      <div class="relative flex items-center justify-center">
        @if($history->status === 'completed' || $history->is_current)
          <div class="flex items-center justify-center w-10 h-10 rounded-full {{ $history->is_current ? 'bg-warning animate-pulse' : 'bg-success' }} text-white shadow-lg">
            @if($history->status === 'completed')
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
            @else
              <div class="w-3 h-3 bg-white rounded-full"></div>
            @endif
          </div>
        @else
          <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-200 text-gray-500">
            <div class="w-3 h-3 bg-gray-400 rounded-full"></div>
          </div>
        @endif
      </div>
      
      <!-- Content -->
      <div class="ml-4 flex-1 pb-8">
        <div class="flex items-center justify-between">
          <h4 class="text-sm font-semibold {{ $history->is_current ? 'text-gray-900' : 'text-gray-500' }}">
            {{ $history->status_label }}
          </h4>
          @if($history->created_at)
            <span class="text-xs text-gray-500">
              {{ $history->created_at->format('d M Y, H:i') }}
            </span>
          @endif
        </div>
        
        @if($history->note)
          <p class="mt-1 text-sm text-gray-600">{{ $history->note }}</p>
        @endif
        
        <!-- Progress Bar (for current step with sub-steps) -->
        @if($history->is_current && $history->progress_percentage)
          <div class="mt-3 w-full bg-gray-200 rounded-full h-2">
            <div class="bg-warning h-2 rounded-full transition-all duration-300" 
              style="width: {{ $history->progress_percentage }}%"></div>
          </div>
          <p class="mt-1 text-xs text-gray-600">
            {{ $history->progress_label }}
          </p>
        @endif
      </div>
    </div>
  @endforeach
</div>
```

#### Horizontal Progress Steps
```html
<div class="flex items-center justify-between">
  @foreach(['Pending', 'Paid', 'Confirmed', 'Active', 'Completed'] as $index => $step)
    <div class="flex items-center {{ $loop->last ? '' : 'flex-1' }}">
      <!-- Step Circle -->
      <div class="relative flex items-center justify-center">
        <div class="flex items-center justify-center w-10 h-10 rounded-full 
          {{ $currentStep > $index ? 'bg-success' : ($currentStep === $index ? 'bg-warning' : 'bg-gray-200') }} 
          text-white font-semibold text-sm">
          @if($currentStep > $index)
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
          @else
            {{ $index + 1 }}
          @endif
        </div>
      </div>
      
      <!-- Step Label -->
      <div class="ml-3 text-sm">
        <p class="font-medium {{ $currentStep >= $index ? 'text-gray-900' : 'text-gray-500' }}">
          {{ $step }}
        </p>
      </div>
      
      <!-- Connecting Line -->
      @if(!$loop->last)
        <div class="flex-1 h-0.5 mx-4 {{ $currentStep > $index ? 'bg-success' : 'bg-gray-200' }}"></div>
      @endif
    </div>
  @endforeach
</div>
```

---

### 3.12 Filters & Search

```html
<div class="bg-white rounded-lg shadow-sm p-6">
  <h3 class="text-lg font-semibold text-gray-900 mb-4">Filter Kost</h3>
  
  <form action="/marketplace" method="GET" class="space-y-4">
    <!-- Search -->
    <div>
      <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
        Cari
      </label>
      <div class="relative">
        <input type="text" 
          id="search"
          name="search" 
          value="{{ request('search') }}"
          placeholder="Nama kost atau lokasi..." 
          class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
      </div>
    </div>
    
    <!-- Price Range -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">
        Harga per bulan
      </label>
      <div class="flex items-center gap-3">
        <input type="number" 
          name="price_min" 
          value="{{ request('price_min') }}"
          placeholder="Min" 
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
        <span class="text-gray-500">—</span>
        <input type="number" 
          name="price_max" 
          value="{{ request('price_max') }}"
          placeholder="Max" 
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
      </div>
    </div>
    
    <!-- Category Filter (Checkboxes) -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">
        Kategori
      </label>
      <div class="space-y-2">
        @foreach($categories as $category)
          <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition-colors">
            <input type="checkbox" 
              name="categories[]" 
              value="{{ $category->id }}"
              {{ in_array($category->id, request('categories', [])) ? 'checked' : '' }}
              class="w-5 h-5 rounded text-primary-600 border-gray-300 focus:ring-2 focus:ring-primary-500">
            <span class="ml-3 text-sm text-gray-700">{{ $category->name }}</span>
            <span class="ml-auto text-xs text-gray-500">({{ $category->kosts_count }})</span>
          </label>
        @endforeach
      </div>
    </div>
    
    <!-- Rating Filter -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">
        Rating minimum
      </label>
      <div class="flex items-center gap-2">
        @for($i = 5; $i >= 1; $i--)
          <label class="flex items-center cursor-pointer">
            <input type="radio" 
              name="rating_min" 
              value="{{ $i }}"
              {{ request('rating_min') == $i ? 'checked' : '' }}
              class="sr-only">
            <div class="flex items-center gap-1 px-3 py-2 border-2 rounded-lg transition-all
              {{ request('rating_min') == $i ? 'border-primary-500 bg-primary-50' : 'border-gray-200 hover:border-gray-300' }}">
              <svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
              </svg>
              <span class="text-sm font-medium">{{ $i }}+</span>
            </div>
          </label>
        @endfor
      </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="flex gap-3 pt-4">
      <button type="submit" 
        class="flex-1 px-4 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition-all">
        Terapkan Filter
      </button>
      <a href="/marketplace" 
        class="px-4 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-all">
        Reset
      </a>
    </div>
  </form>
</div>
```

---

### 3.13 Breadcrumbs

```html
<nav class="flex mb-6" aria-label="Breadcrumb">
  <ol class="flex items-center space-x-2 text-sm">
    <li>
      <a href="/" class="text-gray-500 hover:text-gray-700 transition-colors">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
          <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
        </svg>
      </a>
    </li>
    <li>
      <div class="flex items-center">
        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
        </svg>
        <a href="/admin/kosts" class="ml-2 text-gray-500 hover:text-gray-700 transition-colors">
          Kost Saya
        </a>
      </div>
    </li>
    <li>
      <div class="flex items-center">
        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
        </svg>
        <span class="ml-2 font-medium text-gray-900" aria-current="page">
          Edit Kost Mawar Indah
        </span>
      </div>
    </li>
  </ol>
</nav>
```

---

### 3.14 Tabs

```html
<div x-data="{ activeTab: 'info' }">
  <!-- Tab Navigation -->
  <div class="border-b border-gray-200">
    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
      <button @click="activeTab = 'info'" 
        :class="activeTab === 'info' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" 
        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-t-lg">
        Informasi Dasar
      </button>
      <button @click="activeTab = 'rooms'" 
        :class="activeTab === 'rooms' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" 
        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all">
        Tipe Kamar
        <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600">
          {{ $roomTypesCount }}
        </span>
      </button>
      <button @click="activeTab = 'facilities'" 
        :class="activeTab === 'facilities' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" 
        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all">
        Fasilitas & Aturan
      </button>
      <button @click="activeTab = 'payment'" 
        :class="activeTab === 'payment' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" 
        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all">
        Payment & Dokumen
      </button>
    </nav>
  </div>
  
  <!-- Tab Panels -->
  <div class="mt-6">
    <div x-show="activeTab === 'info'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
      @include('admin.kosts.partials.info-tab')
    </div>
    
    <div x-show="activeTab === 'rooms'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
      @include('admin.kosts.partials.rooms-tab')
    </div>
    
    <div x-show="activeTab === 'facilities'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
      @include('admin.kosts.partials.facilities-tab')
    </div>
    
    <div x-show="activeTab === 'payment'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
      @include('admin.kosts.partials.payment-tab')
    </div>
  </div>
</div>
```

---

### 3.15 Pagination

```html
<!-- Laravel Pagination (Tailwind Styled) -->
<div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
  <!-- Mobile -->
  <div class="flex flex-1 justify-between sm:hidden">
    @if ($paginator->onFirstPage())
      <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-100 cursor-not-allowed">
        Previous
      </span>
    @else
      <a href="{{ $paginator->previousPageUrl() }}" 
        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
        Previous
      </a>
    @endif
    
    @if ($paginator->hasMorePages())
      <a href="{{ $paginator->nextPageUrl() }}" 
        class="relative ml-3 inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
        Next
      </a>
    @else
      <span class="relative ml-3 inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-100 cursor-not-allowed">
        Next
      </span>
    @endif
  </div>
  
  <!-- Desktop -->
  <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
    <div>
      <p class="text-sm text-gray-700">
        Showing
        <span class="font-medium">{{ $paginator->firstItem() }}</span>
        to
        <span class="font-medium">{{ $paginator->lastItem() }}</span>
        of
        <span class="font-medium">{{ $paginator->total() }}</span>
        results
      </p>
    </div>
    <div>
      <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
        <!-- Previous -->
        @if ($paginator->onFirstPage())
          <span class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 cursor-not-allowed">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
            </svg>
          </span>
        @else
          <a href="{{ $paginator->previousPageUrl() }}" 
            class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
            </svg>
          </a>
        @endif
        
        <!-- Page Numbers -->
        @foreach ($elements as $element)
          @if (is_string($element))
            <span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300">{{ $element }}</span>
          @endif
          
          @if (is_array($element))
            @foreach ($element as $page => $url)
              @if ($page == $paginator->currentPage())
                <span class="relative z-10 inline-flex items-center px-4 py-2 text-sm font-semibold text-primary-600 bg-primary-50 ring-1 ring-inset ring-primary-600 focus:z-20">
                  {{ $page }}
                </span>
              @else
                <a href="{{ $url }}" 
                  class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20">
                  {{ $page }}
                </a>
              @endif
            @endforeach
          @endif
        @endforeach
        
        <!-- Next -->
        @if ($paginator->hasMorePages())
          <a href="{{ $paginator->nextPageUrl() }}" 
            class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
            </svg>
          </a>
        @else
          <span class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 cursor-not-allowed">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
            </svg>
          </span>
        @endif
      </nav>
    </div>
  </div>
</div>
```

---

### 3.16 Accordion

```html
<div x-data="{ open: null }" class="space-y-3">
  @foreach($faqs as $index => $faq)
    <div class="border border-gray-200 rounded-lg overflow-hidden">
      <!-- Accordion Header -->
      <button @click="open = open === {{ $index }} ? null : {{ $index }}"
        class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
        <span class="text-sm font-semibold text-gray-900">
          {{ $faq->question }}
        </span>
        <svg :class="{ 'rotate-180': open === {{ $index }} }"
          class="w-5 h-5 text-gray-500 transition-transform duration-200" 
          fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
      </button>
      
      <!-- Accordion Body -->
      <div x-show="open === {{ $index }}"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 max-h-0"
        x-transition:enter-end="opacity-100 max-h-96"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 max-h-96"
        x-transition:leave-end="opacity-0 max-h-0"
        class="px-6 py-4 bg-gray-50 border-t border-gray-200">
        <p class="text-sm text-gray-600 leading-relaxed">
          {{ $faq->answer }}
        </p>
      </div>
    </div>
  @endforeach
</div>
```

---

### 3.17 Callout / Alert Box

```html
<!-- Info Callout -->
<div class="flex items-start gap-3 p-4 bg-info-light border-l-4 border-info rounded-lg">
  <svg class="w-5 h-5 text-info flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
  </svg>
  <div class="flex-1">
    <h4 class="text-sm font-semibold text-gray-900">Informasi Penting</h4>
    <p class="text-sm text-gray-600 mt-1">
      Email verification wajib sebelum Anda dapat membuat booking rental.
    </p>
  </div>
</div>

<!-- Warning Callout -->
<div class="flex items-start gap-3 p-4 bg-warning-light border-l-4 border-warning rounded-lg">
  <svg class="w-5 h-5 text-warning flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
  </svg>
  <div class="flex-1">
    <h4 class="text-sm font-semibold text-gray-900">Deadline Pembayaran</h4>
    <p class="text-sm text-gray-600 mt-1">
      Anda hanya punya 48 jam untuk menyelesaikan pembayaran. Setelah itu rental akan otomatis dibatalkan.
    </p>
  </div>
</div>

<!-- Error/Rejection Callout -->
<div class="flex items-start gap-3 p-4 bg-error-light border-l-4 border-error rounded-lg">
  <svg class="w-5 h-5 text-error flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
  </svg>
  <div class="flex-1">
    <h4 class="text-sm font-semibold text-gray-900">Submission Ditolak</h4>
    <p class="text-sm text-gray-600 mt-1">
      {{ $rejectionReason }}
    </p>
    <a href="/admin/kosts/{{ $kostId }}/edit" class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-error hover:underline">
      Revisi Kost
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
      </svg>
    </a>
  </div>
</div>

<!-- Success Callout -->
<div class="flex items-start gap-3 p-4 bg-success-light border-l-4 border-success rounded-lg">
  <svg class="w-5 h-5 text-success flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
  </svg>
  <div class="flex-1">
    <h4 class="text-sm font-semibold text-gray-900">Payment Berhasil Diverifikasi</h4>
    <p class="text-sm text-gray-600 mt-1">
      Silakan upload dokumen administrasi untuk melanjutkan proses rental.
    </p>
  </div>
</div>
```

### 3.18 Verify Email Modal (Popup)

Modal untuk meminta user yang belum verified memverifikasi email saat mengakses fitur yang butuh email terverifikasi (FR-006). Reuse pattern Modal §3.5 — dipicu oleh flash session `verify_email_prompt=true` dari middleware `verified` (`EnsureEmailIsVerified`), di-render di layout app sebagai overlay tanpa route sendiri. CTA mengarah ke route `verification.notice` (`/verify-email`), tempat OTP dikirim on-demand (ADR-023). Implementasi: `components/verify-email-modal.blade.php`.

```html
<!-- Verify Email Modal — render di layout saat session flash verify_email_prompt=true -->
<div x-data="{ open: @json(session()->has('verify_email_prompt')) }" x-cloak>
  <div x-show="open"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @keydown.escape.window="open = false"
    class="fixed inset-0 z-50 overflow-y-auto"
    role="dialog"
    aria-modal="true"
    aria-labelledby="verify-email-modal-title">

    <!-- Backdrop -->
    <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="open = false"></div>

    <!-- Modal Content -->
    <div class="flex min-h-full items-center justify-center p-4">
      <div x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="relative transform overflow-hidden rounded-xl bg-white shadow-2xl transition-all w-full max-w-md text-center"
        @click.stop>

        <!-- Close button -->
        <button @click="open = false; document.body.classList.remove('overflow-hidden')"
          class="absolute top-4 right-4 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-lg p-1"
          aria-label="Tutup">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>

        <!-- Body -->
        <div class="px-6 py-8">
          <!-- Email/alert icon -->
          <div class="mx-auto flex items-center justify-center w-14 h-14 rounded-full bg-primary-50 text-primary-600 mb-4">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
          </div>

          <h3 id="verify-email-modal-title" class="text-xl font-semibold text-gray-900">
            Email Anda Belum Diverifikasi
          </h3>

          <p class="mt-2 text-sm text-gray-600">
            Verifikasi email diperlukan untuk mengakses fitur ini. Kode OTP akan dikirim ke email Anda saat Anda membuka halaman verifikasi.
          </p>

          <!-- CTA -->
          <a href="{{ route('verification.notice') }}"
            class="mt-6 inline-flex w-full items-center justify-center px-6 py-3 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
            Verifikasi Email
          </a>

          <!-- Dismiss -->
          <button @click="open = false; document.body.classList.remove('overflow-hidden')"
            class="mt-3 inline-flex w-full items-center justify-center px-6 py-3 text-sm font-semibold text-gray-600 hover:text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-300">
            Nanti Saja
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
```

**Accessibility:**
- `role="dialog"` + `aria-modal="true"` + `aria-labelledby` → judul modal
- Focus trap di dalam modal saat terbuka; focus kembali ke elemen pemicu saat ditutup
- `Escape` menutup modal; tombol tutup punya `aria-label="Tutup"`
- CTA "Verifikasi Email" menerima focus awal (primary action)

---

### 4.1 Public Layout (Marketplace)

**Structure:**
```
┌─────────────────────────────────────────┐
│ Sticky Navigation (white bg, shadow)    │
├─────────────────────────────────────────┤
│                                         │
│ Hero Section (gradient bg, centered)    │
│                                         │
├──────────┬──────────────────────────────┤
│ Sidebar  │                              │
│ Filters  │ Content Grid (3 cols)        │
│ (25%)    │ Kost Cards (75%)             │
│          │                              │
│ Sticky   │ Pagination                   │
├──────────┴──────────────────────────────┤
│ Footer (multi-column)                   │
└─────────────────────────────────────────┘
```

**Breakpoints:**
- Desktop (≥1024px): Sidebar visible, 3-column grid
- Tablet (768-1023px): Sidebar drawer, 2-column grid
- Mobile (<768px): Sidebar drawer, 1-column stack

**Max Width Container:** `max-w-7xl mx-auto` (1280px)

---

### 4.2 Admin Layout (Sidebar Navigation)

**Structure:**
```
┌────────┬────────────────────────────────┐
│        │ Top Bar (breadcrumbs)          │
│        ├────────────────────────────────┤
│ Sidebar│                                │
│ (64px  │ Main Content Area              │
│ fixed) │ (bg-gray-50)                   │
│        │                                │
│        │                                │
└────────┴────────────────────────────────┘
```

**Main Content Offset:** `ml-64` (256px sidebar width)

**Responsive:**
- Desktop: Sidebar always visible
- Mobile: Sidebar drawer overlay, hamburger menu

---

### 4.3 Auth Layout (Centered Card)

**Structure:**
```
┌─────────────────────────────────────────┐
│                                         │
│                                         │
│        ┌─────────────────┐              │
│        │  Logo + Title   │              │
│        │                 │              │
│        │  Form Card      │              │
│        │  (max-w-md)     │              │
│        │                 │              │
│        └─────────────────┘              │
│                                         │
│                                         │
└─────────────────────────────────────────┘
```

**Centered Container:** `min-h-screen flex items-center justify-center`
**Card:** `max-w-md w-full bg-white shadow-xl rounded-xl p-8`

---

## 5. Interaction Patterns

### 5.1 State Machine UI (Rental Lifecycle)

**Rental States:** Pending → Paid → Confirmed → Active → Completed (or Cancelled)

**UI Pattern per State:**

| State | Badge Color | Primary Action | Secondary Info |
|---|---|---|---|
| Pending | warning (yellow) | "Upload Bukti Bayar" | Payment deadline countdown |
| Paid | info (blue) | "Upload Dokumen" | Document checklist progress |
| Confirmed | success (green) | "Lihat Rental" | Start date countdown |
| Active | primary (blue) | "Detail Rental" | End date countdown |
| Completed | green-800 | "Tulis Review" | Review prompt |
| Cancelled | gray | - | Cancellation reason display |

**Action Buttons Visibility:**
```
Pending:
  - Upload Bukti Bayar (primary)
  - Cancel Rental (danger)

Paid:
  - Upload Dokumen (primary) [if not all uploaded]
  - View Documents (outline)
  - Cancel Rental (danger)

Confirmed/Active:
  - View Detail (outline)
  - Cancel Rental (danger) [hanya jika belum lewat 50% durasi]

Completed:
  - Write Review (primary) [jika belum review]
  - View Review (outline) [jika sudah review]
```

---

### 5.2 Form Validation Pattern

**Inline Validation (on blur):**
1. User mengisi input
2. User blur (fokus keluar)
3. Frontend validasi (Alpine.js)
4. Tampilkan error inline jika invalid
5. Clear error saat user mulai mengetik lagi

**Submit Validation:**
1. User klik submit
2. Disable button + loading state
3. Backend validasi
4. Success: redirect + toast success
5. Error: enable button + tampilkan error per-field + scroll to first error

**Error Display:**
```html
@error('field_name')
  <p class="mt-1 text-sm text-error">{{ $message }}</p>
@enderror
```

---

### 5.3 Document Upload Flow

1. **Display Requirements:**
   - Show checklist (Wajib/Opsional labels)
   - Show reason for each document

2. **Upload:**
   - Drag-drop zone or file picker
   - Preview thumbnail after upload
   - Show upload progress bar

3. **Verification Status:**
   - Pending: yellow badge, "Menunggu verifikasi"
   - Approved: green badge + checkmark icon
   - Rejected: red badge + rejection reason + re-upload button

4. **Progress Tracking:**
   - X of Y documents uploaded
   - X of Y wajib approved
   - Progress bar visual

---

## 6. Responsive Design

### 6.1 Breakpoints (Tailwind Defaults)

```css
sm: 640px   /* Small devices (phones landscape) */
md: 768px   /* Medium devices (tablets) */
lg: 1024px  /* Large devices (desktops) */
xl: 1280px  /* Extra large (wide desktops) */
2xl: 1536px /* 2X Extra large */
```

### 6.2 Mobile-First Approach

**Base styles = mobile, progressively enhance:**

```html
<!-- Mobile (default): stack vertically -->
<div class="flex flex-col gap-4">

<!-- Tablet: 2 columns -->
<div class="flex flex-col md:flex-row gap-4">

<!-- Desktop: 3 columns -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
```

### 6.3 Touch Targets

**Minimum size:** 44x44px (Apple HIG, WCAG 2.5.5)

```html
<!-- Bad: too small -->
<button class="px-2 py-1 text-xs">X</button>

<!-- Good: adequate touch target -->
<button class="p-3 min-w-[44px] min-h-[44px]">
  <svg class="w-5 h-5">...</svg>
</button>
```

### 6.4 Responsive Typography

Use `clamp()` for fluid type scaling (already defined in §2.2):

```html
<h1 class="text-4xl md:text-5xl font-bold">
  Hero Headline
</h1>
```

### 6.5 Image Optimization

```html
<img src="/storage/kosts/thumb.jpg"
  srcset="/storage/kosts/thumb-320.jpg 320w,
          /storage/kosts/thumb-640.jpg 640w,
          /storage/kosts/thumb-1280.jpg 1280w"
  sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
  alt="Kost Mawar Indah"
  loading="lazy"
  class="w-full h-48 object-cover">
```

---

## 7. Accessibility Guidelines (WCAG 2.1 AA)

### 7.1 Color Contrast

**Text:**
- Body text (16px+): minimum 4.5:1
- Large text (24px+): minimum 3:1

**UI Components:**
- Buttons, borders, icons: minimum 3:1

**Testing:** Use browser DevTools or https://webaim.org/resources/contrastchecker/

### 7.2 Keyboard Navigation

**All interactive elements must be:**
1. **Tabbable** (via Tab key)
2. **Activatable** (via Enter/Space)
3. **Visible focus indicator** (`focus-visible:ring-2`)

**Focus Order:**
- Logical reading order (top → bottom, left → right)
- Skip navigation link at page top
- Focus trap in modals

**Example:**
```html
<button class="focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2">
  Clickable
</button>
```

### 7.3 Screen Reader Support

**Semantic HTML:**
```html
<header> <nav> <main> <aside> <footer>
<article> <section> <figure> <figcaption>
```

**ARIA Labels:**
```html
<!-- Icon-only button -->
<button aria-label="Close modal">
  <svg>...</svg>
</button>

<!-- Dynamic content announcement -->
<div aria-live="polite" aria-atomic="true">
  {{ $toastMessage }}
</div>

<!-- Form error association -->
<input aria-describedby="email-error" aria-invalid="true">
<span id="email-error">Format email tidak valid</span>
```

**Skip Links:**
```html
<a href="#main-content" 
  class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-primary-600 focus:text-white focus:rounded-lg">
  Skip to main content
</a>

<main id="main-content">
  <!-- Content -->
</main>
```

### 7.4 Form Accessibility

```html
<form>
  <!-- Label association -->
  <label for="email">Email</label>
  <input id="email" type="email">
  
  <!-- Required indicator -->
  <label for="name">
    Name <span aria-label="required">*</span>
  </label>
  
  <!-- Error messages -->
  <input aria-invalid="true" aria-describedby="name-error">
  <span id="name-error" role="alert">Name is required</span>
  
  <!-- Fieldset for radio groups -->
  <fieldset>
    <legend>Choose payment method</legend>
    <input type="radio" id="bank" name="method">
    <label for="bank">Bank Transfer</label>
  </fieldset>
</form>
```

### 7.5 Alt Text for Images

```html
<!-- Decorative images -->
<img src="decoration.svg" alt="" role="presentation">

<!-- Informative images -->
<img src="kost.jpg" alt="Kost Mawar Indah tampak depan dengan taman">

<!-- Functional images (button images) -->
<button>
  <img src="search-icon.svg" alt="Search">
</button>
```

---

## 8. Animation & Motion

### 8.1 Transition Guidelines

**Use animation for:**
- State changes (hover, focus, active)
- Modal/dropdown enter/exit
- Page transitions (optional)
- Loading states

**Avoid animation for:**
- Large layout shifts (causes CLS)
- Critical user actions without alternative
- Decorative animations (can distract)

### 8.2 Reduced Motion

**Respect `prefers-reduced-motion`:**

```css
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
```

**Tailwind utility:**
```html
<div class="transition-all motion-reduce:transition-none">
  Content
</div>
```

### 8.3 Common Animations

**Button Hover:**
```html
<button class="transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5">
  Hover me
</button>
```

**Card Hover:**
```html
<div class="transition-all duration-300 hover:shadow-xl hover:scale-105">
  Card content
</div>
```

**Fade In:**
```html
<div x-show="visible"
  x-transition:enter="transition ease-out duration-300"
  x-transition:enter-start="opacity-0"
  x-transition:enter-end="opacity-100">
  Content
</div>
```

**Slide Down:**
```html
<div x-show="open"
  x-transition:enter="transition ease-out duration-300"
  x-transition:enter-start="opacity-0 -translate-y-4"
  x-transition:enter-end="opacity-100 translate-y-0">
  Dropdown content
</div>
```

---

## 9. Implementation Notes (Blade + Alpine.js + Tailwind)

### 9.1 Blade Component Structure

**Location:** `resources/views/components/`

**Recommended components to create:**
```
components/
├── button.blade.php              (reusable button variants)
├── input.blade.php               (form input with error)
├── status-badge.blade.php        (rental/kost status badges)
├── kost-card.blade.php           (marketplace card)
├── rental-card.blade.php         (tenant dashboard card)
├── modal.blade.php               (reusable modal)
├── toast.blade.php               (notification toast)
├── nav/
│   ├── public.blade.php
│   ├── admin-sidebar.blade.php
│   └── breadcrumbs.blade.php
└── icons/
    ├── check.blade.php
    ├── x.blade.php
    └── ...
```

**Usage:**
```blade
<x-button variant="primary" size="lg">
  Submit
</x-button>

<x-status-badge :status="$rental->status" />

<x-kost-card :kost="$kost" />
```

### 9.2 Alpine.js Patterns

**Component state:**
```blade
<div x-data="{ open: false, loading: false }">
  <button @click="open = true">Open Modal</button>
  <x-modal x-show="open" @close="open = false">
    Content
  </x-modal>
</div>
```

**Form handling:**
```blade
<form x-data="{ submitting: false }" 
  @submit="submitting = true">
  <button type="submit" :disabled="submitting">
    <span x-show="!submitting">Submit</span>
    <span x-show="submitting">Processing...</span>
  </button>
</form>
```

**Global Alpine store (for toast):**
```javascript
// resources/js/app.js
import Alpine from 'alpinejs'

Alpine.store('toast', {
  visible: false,
  type: 'info',
  message: '',
  
  show(type, message) {
    this.type = type
    this.message = message
    this.visible = true
    setTimeout(() => this.visible = false, 5000)
  }
})

Alpine.start()
```

### 9.3 Tailwind Configuration

**Extend config (tailwind.config.js):**
```javascript
export default {
  content: [
    './resources/views/**/*.blade.php',
    './resources/js/**/*.js',
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#EFF6FF',
          // ... (use design tokens from §2.1)
          900: '#1E3A8A',
        },
        // Add custom semantic colors
        success: '#10B981',
        warning: '#F59E0B',
        error: '#EF4444',
      },
      fontFamily: {
        sans: ['Figtree', 'sans-serif'],
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}
```

### 9.4 Asset Organization

```
resources/
├── css/
│   └── app.css                (Tailwind directives)
├── js/
│   ├── app.js                 (Alpine.js setup)
│   └── components/
│       ├── toast.js
│       └── file-upload.js
└── views/
    ├── layouts/
    │   ├── app.blade.php      (authenticated layout)
    │   ├── guest.blade.php    (auth pages layout)
    │   └── admin.blade.php    (admin sidebar layout)
    ├── components/            (Blade components)
    ├── auth/                  (login, register, etc)
    ├── tenant/                (tenant pages)
    ├── admin/                 (admin pages)
    ├── superadmin/            (super admin pages)
    └── marketplace/           (public pages)
```

### 9.5 Performance Optimization

**Lazy Loading:**
```blade
<img src="..." loading="lazy">
```

**Defer Non-Critical JS:**
```blade
@vite(['resources/css/app.css'])
@vite(['resources/js/app.js'], 'defer')
```

**Purge Unused CSS:**
Tailwind automatically purges in production based on `content` config.

**Image Optimization:**
Use Laravel image optimization packages (e.g., `spatie/laravel-image-optimizer`)

---

## 10. Changelog

| Version | Date | Changes | Author |
|---|---|---|---|
| 1.0.0 | 2026-08-16 | Initial design system creation. Extracted from reference design (soft gradients, card-based layout). Design tokens, 17 component categories, layout patterns, responsive guidelines, accessibility WCAG 2.1 AA targets, animation specs, Blade+Alpine.js implementation notes. Total 35+ components documented. | OpenCode |
| 1.0.1 | 2026-08-18 | Tambah §3.18 Verify Email Modal (Popup): modal on-demand untuk user belum verified (FR-006), dipicu flash `verify_email_prompt` dari middleware `verified`, reuse pattern §3.5, CTA → `verification.notice`. 18 component categories. | OpenCode |

---

**END OF DESIGN.MD**

> **Next Steps:**
> 1. Reference this document when implementing Blade views (TASK-002+)
> 2. Create Blade components in `resources/views/components/` following §9.1
> 3. Check PAGES.md for specific page layouts and component compositions
> 4. Run accessibility audit with axe DevTools after implementation
> 5. Test responsive behavior on real devices (mobile, tablet, desktop)
