# DESIGN.md — UI/UX Design System

> **Status dokumen ini:** Single Source of Truth untuk DESAIN VISUAL & UX PATTERNS.
> Dokumen ini menjadi pedoman implementasi UI untuk semua halaman/antarmuka di aplikasi SewaKost.
> Setiap komponen, token, dan pattern di sini dirancang untuk Laravel 13 + Blade + Alpine.js + Tailwind CSS 4.0.

| Field | Value |
|---|---|
| Nama Proyek | SewaKost — Web Marketplace Kost Management & Rental System |
| Versi Dokumen | `v1.6.1` |
| Terakhir Diperbarui | `2026-08-20` |
| Tech Stack | Laravel 13 + Blade + Alpine.js 3.14 + Tailwind CSS 4.0 |

---

## 0. Cara Menggunakan Dokumen Ini

### 0.1 Untuk Agent/Developer
1. **Baca §1 Design Principles** sebelum implementasi halaman apa pun — prinsip ini berlaku universal
2. **Cek §2 Design Tokens** untuk semua nilai visual — token 3 lapis (primitive → semantic → komponen), warna + pasangan dark mode, spacing, typography, shadow; jangan hardcode nilai arbitrary
3. **Gunakan §3 Component Library** sebagai referensi HTML+Tailwind+Alpine.js — copy-paste dan modifikasi sesuai kebutuhan
4. **Ikuti §4 Layout Patterns** untuk struktur halaman sesuai role pengguna (Public, Tenant, Admin, Super Admin)
5. **Terapkan §6 Responsive Design** untuk semua halaman — mobile-first approach wajib
6. **Verifikasi §7 Accessibility** sebelum mark task Done — WCAG 2.1 AA adalah target minimum

### 0.2 Hubungan dengan Dokumen Lain
- **PRD.md** (§4 Persona) → Design principles disesuaikan dengan target pengguna: Rina (Tenant, 18-35yo), Budi (Admin Kost, 30-50yo), Pak Ahmad (Super Admin)
- **ARCHITECTURE.md** (§6.1 Routes) → Setiap route punya spesifikasi halaman di `PAGES.md`
- **PAGES.md** → Detail spesifikasi 57 halaman + 8 email templates (layout, komponen, user flow, data requirements)
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

Arsitektur token 3 lapis: **Primitive** (nilai hex mentah) → **Semantic** (peran fungsional + pasangan dark) → **Component** (konsumsi di §3). Dua keputusan ditetapkan: target Tailwind **v4** (`@theme`, `@custom-variant dark`) dan **dark mode didefinisikan sekarang** — setiap semantic token punya pasangan `-dark`, siap dipakai begitu migrasi v4 selesai. Nilai hex existing (primary/secondary/gray/gradient) TIDAK berubah; arsitektur ini hanya mengatur cara token diregistrasi & dipetakan.

Sub-section: §2.1 warna (3 lapis), §2.2 typography, §2.3 spacing, §2.4 border radius, §2.5 shadows, §2.6 transitions.

### 2.1 Color System — 3 Lapis Token

Arsitektur warna dibangun 3 lapis: **Primitive** (hex mentah, satu sumber nilai), **Semantic** (peran fungsional, pasangan light/dark), **Component** (konsumsi di §3). Komponen non-brand wajib lewat lapis semantic; brand color (primary/secondary/accent) boleh langsung karena belum ada semantic mapping — pemetaan persis ada di §3 per komponen.

```
┌────────────────────────────────────────────┐
│ Lapis 3 · Component (konsumsi — §3)        │
│   bg-surface text-text · bg-success/10     │
├────────────────────────────────────────────┤
│ Lapis 2 · Semantic (peran + dark mode)     │
│   surface/text/border/overlay · status     │
├────────────────────────────────────────────┤
│ Lapis 1 · Primitive (nilai mentah)         │
│   primary/secondary/accent/gray, gradient  │
└────────────────────────────────────────────┘
```

> **Target migrasi:** Blok `@theme` di bawah ditulis untuk Tailwind v4 (`@import "tailwindcss"`, `@custom-variant dark`). Build saat ini masih v3 — migrasi dilakukan di fase kode; nilai hex tidak berubah, hanya mekanisme registrasi token.

#### Lapis 1 — Primitive Colors (raw hex; jangan dipakai langsung di komponen)

```css
@import "tailwindcss";
@custom-variant dark (&:where(.dark, .dark *));

@theme {
  /* Primary — Soft Blue (Trust, Marketplace) */
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
  --color-primary: #3B82F6;      /* Alias bare — = primary-500 (primary button default) */

  /* Secondary — Soft Amber (Action, Pricing, Highlight) */
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

  /* Accent — Soft Purple (Premium, Highlights) */
  --color-accent-50: #FAF5FF;
  --color-accent-100: #F3E8FF;
  --color-accent-200: #E9D5FF;
  --color-accent-300: #D8B4FE;
  --color-accent-400: #C084FC;
  --color-accent-500: #A855F7;   /* Premium badge default */
  --color-accent-600: #9333EA;   /* Premium badge hover */
  --color-accent-700: #7E22CE;
  --color-accent-800: #6B21A8;
  --color-accent-900: #581C87;

  /* Neutral Gray (UI Base) */
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
  --color-gray-950: #030712;  /* Footer/sidebar gelap (jangkar dark mode) */

  /* Status — Red (Error/Danger) & Green (Success) — nilai primitif utk lapis semantic status */
  --color-error-500: #EF4444;   /* red-500 — = base error */
  --color-error-600: #DC2626;   /* red-600 — isian tombol solid danger */
  --color-error-800: #991B1B;   /* red-800 — teks error hover */
  --color-success-800: #065F46; /* green-800 — teks success hover */

  /* Background Gradients (dari reference design) */
  --gradient-hero: linear-gradient(135deg, #FEF3C7 0%, #DBEAFE 50%, #E0E7FF 100%);
  --gradient-card-hover: linear-gradient(135deg, #FFFFFF 0%, #F9FAFB 100%);
}
```

**Pemetaan peran (dikonsumsi komponen via §3):**
- Primary CTA: `bg-primary-600 hover:bg-primary-700`; links `text-primary-600 hover:text-primary-700`; focus ring `focus-visible:ring-2 ring-primary-500`; active nav `bg-primary-50 text-primary-700`
- Secondary CTA: `bg-secondary-500 hover:bg-secondary-600`; price highlight `text-secondary-600 font-bold`
- Accent (minimal): premium badge `bg-accent-500 text-white`; decorative gradient
- Gray: page bg `bg-gray-50`; card bg `bg-white`; body `text-gray-600`; headings `text-gray-900`; borders `border-gray-200`; disabled `text-gray-400 bg-gray-100`
- Gradient: hero `bg-(--gradient-hero)`; card hover `bg-(--gradient-card-hover)`

#### Lapis 2 — Semantic Colors (peran fungsional, pasangan light/dark)

Semantic token memetakan peran → nilai primitive. Tiap token punya pasangan `-dark`; variant `dark:` mengaktifkannya saat class `.dark` ada di `<html>` (strategy class — toggle via Alpine theme switcher; variant didaftarkan lewat `@custom-variant dark` di atas).

```css
/* Surface — background layer */
--color-surface: #F9FAFB;           /* halaman (gray-50) */
--color-surface-muted: #F3F4F6;     /* area redup: sidebar, table strip (gray-100) */
--color-surface-raised: #FFFFFF;    /* kartu di atas surface (elevasi via shadow) */
--color-surface-dark: #111827;
--color-surface-muted-dark: #1F2937;
--color-surface-raised-dark: #1F2937;

/* Text — ink layer */
--color-text: #4B5563;              /* body text (gray-600) */
--color-text-muted: #6B7280;        /* secondary text (gray-500) */
--color-text-strong: #111827;       /* headings (gray-900) */
--color-text-dark: #F9FAFB;
--color-text-muted-dark: #9CA3AF;
--color-text-strong-dark: #FFFFFF;

/* Border — hairline & dividers */
--color-border: #E5E7EB;            /* default dividers (gray-200) */
--color-border-strong: #D1D5DB;     /* input borders, emphasized (gray-300) */
--color-border-dark: #374151;
--color-border-strong-dark: #4B5563;

/* Overlay — modal scrim */
--color-overlay: rgba(2, 6, 23, 0.5);
--color-overlay-dark: rgba(2, 6, 23, 0.75);
```

**Pola penggunaan:** komponen menulis default light + override dark:

```html
<body class="bg-surface text-text dark:bg-surface-dark dark:text-text-dark">
```

`.dark` dipasang di elemen `<html>` (mis. `<html lang="id" class="dark">`); seluruh turunan berganti lewat variant `dark:` tanpa menyentuh komponen lain.

**Semantic Status — skala 3 nilai (light / base / strong):**

Status butuh 3 nilai agar teks di atas background berwarna memenuhi kontras ≥4.5:1 — `-light` untuk background isian, base untuk ikon/aksen besar, `-700` untuk teks. Error punya nilai ke-4 `-600` untuk isian tombol solid (danger).

```css
/* Success — Approved, Verified, Completed, Active */
--color-success: #10B981;   --color-success-light: #D1FAE5;   --color-success-700: #047857;
/* Warning — Pending, Review Needed */
--color-warning: #F59E0B;   --color-warning-light: #FEF3C7;   --color-warning-700: #B45309;
/* Error — Rejected, Cancelled, Failed, Danger */
--color-error: #EF4444;     --color-error-light: #FEE2E2;     --color-error-600: #DC2626;   --color-error-700: #B91C1C;
/* Info — Informational, Draft */
--color-info: #3B82F6;      --color-info-light: #DBEAFE;      --color-info-700: #1D4ED8;
```

**Kontras (wajib):** base `text-success` (#10B981 ≈2.7:1), `text-warning` (#F59E0B ≈2.0:1), `text-error` (#EF4444 ≈3.4:1), `text-info` (#3B82F6 ≈3.7:1) GAGAL ≥4.5:1 untuk body text. Di atas background `-light` atau `*/10`, gunakan `text-*-700` (semua ≥4.5:1). Base `text-*` hanya untuk elemen non-teks besar (ikon, border — minimum 3:1).

**Usage (update):**
- Success badge: `bg-success/10 text-success-700` (10% opacity background untuk subtle fill)
- Warning badge: `bg-warning/10 text-warning-700`
- Error text: `text-error-700`
- Info callout: `bg-info-light text-info-700 border-info-700/30`

> **Secondary vs Warning (fase merah — jangan digabung):** `secondary-500` dan `warning` sama-sama amber hue (#F59E0B) tapi ROLE berbeda — `secondary` = aksi (harga, CTA), `warning` = status (pending, review). Nilai keduanya TETAP; jangan rename atau gabung token.

#### Lapis 3 — Component Tokens (konsumsi)

Lapis komponen = §3 Component Library: komponen memakai token Lapis 2 (surface/text/border/status) + brand primitive (primary/secondary/accent) via utility class; TIDAK mendefinisikan warna baru sendiri. Catatan sinkronisasi (Fase 3 selesai): §3 telah diselaraskan — badge/teks status memakai `text-*-700`, tombol solid `bg-error-600`, callout `text-*-700` + border `border-*-700/30`, dark pair pada contoh struktural (§3.3, §3.5, §3.10, §3.18, §4); sisa contoh ditandai "dark pair menyusul".

---

### 2.2 Typography Scale

#### Font Families
```css
/* Primary — Figtree (already in project via Laravel Breeze) */
--font-sans: 'Figtree', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif;

/* Monospace — for OTP codes, room codes, technical identifiers */
--font-mono: ui-monospace, 'SF Mono', Consolas, 'Liberation Mono', Menlo, monospace;
```

**Tailwind Config (status migrasi):** Font `Figtree` SUDAH terdaftar via Laravel Breeze (`fontFamily.sans`). Token warna & type scale BELUM diregistrasi — target Tailwind v4 `@theme` (lihat §2.1); registrasi penuh jadi handover fase kode.
```js
fontFamily: {
  sans: ['Figtree', ...defaultTheme.fontFamily.sans],
  mono: [...defaultTheme.fontFamily.mono],
}
```

#### Type Scale (Fixed + Fluid Hybrid)
```css
/* Body & label pakai nilai FIXED — keterbacaan form & presisi layout > scaling terus-menerus.
   Fluid clamp() HANYA untuk display (text-3xl ke atas): judul besar menyesuaikan viewport lebar. */
--text-xs: 0.75rem;     /* 12px — helper text, badges */
--text-sm: 0.875rem;    /* 14px — labels, captions */
--text-base: 1rem;      /* 16px — body default */
--text-lg: 1.125rem;    /* 18px — card titles */
--text-xl: 1.25rem;     /* 20px — section subheads */
--text-2xl: 1.5rem;     /* 24px — section headings */
--text-3xl: clamp(1.875rem, 1.65rem + 1.125vw, 2.25rem);  /* 30px → 36px — page headlines */
--text-4xl: clamp(2.25rem, 1.95rem + 1.5vw, 3rem);        /* 36px → 48px */
--text-5xl: clamp(3rem, 2.5rem + 2.5vw, 4rem);            /* 48px → 64px — Hero headlines only */
```

**Tailwind Usage:**
- Body text: `text-base` (16px fixed)
- Small text (labels, captions): `text-sm` (14px)
- Tiny text (helper text, badges): `text-xs` (12px)
- Card titles: `text-lg font-semibold`
- Section headings: `text-2xl font-bold`
- Page headlines: `text-3xl md:text-4xl font-bold` (fluid display)
- Hero headlines: `text-4xl md:text-5xl font-bold` (fluid display)

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

### 2.3 Spacing System (4px Base Grid)

```css
/* Base unit: 0.25rem = 4px; skala 4n; kelipatan 8px untuk jarak antar-bagian besar */
--space-0: 0;
--space-1: 0.25rem;  /* 4px — tight spacing (icon + text gap) */
--space-2: 0.5rem;   /* 8px — 2× base, standard gap */
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
- Badges, tags: `rounded-sm` (4px)
- Buttons, inputs: `rounded-md` (8px)
- Cards: `rounded-lg` (12px)
- Large modals, large cards: `rounded-xl` (16px)
- Hero sections: `rounded-2xl` (24px)
- Avatars, status pills: `rounded-full`

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

## 3. Component Library (38 Components)

### 3.0 Komponen & API Tunggal

Semua komponen UI dikonsumsi via **Blade component** `<x-nama-komponen>` (file di `resources/views/components/`), BUKAN `@include`. `@include` hanya untuk partial view spesifik-halaman (mis. `admin.kosts.partials.*`) — komponen reusable wajib lewat `x-*`. Lihat §9.1 untuk struktur direktori.

```blade
<x-status-badge :status="$rental->status" />
<x-button variant="primary" size="lg">Submit</x-button>
<x-kost-card :kost="$kost" />
```

#### Component Inventory

| Komponen | Nama Blade `<x-*>` | Status | Variants | Sizes | States | A11y notes |
|---|---|---|---|---|---|---|
| Buttons (§3.1) | `<x-button>` | stable | primary, secondary, outline, ghost, danger, link | sm/md/lg | default, hover, focus, disabled, loading | focus ring visible; label wajib (teks atau `aria-label`) |
| Form Inputs (§3.2) | `<x-input>`, `<x-textarea>`, `<x-select>`, `<x-checkbox>`, `<x-radio-group>`, `<x-file-upload>` | stable | default, error, disabled | sm/md/lg | valid, invalid, disabled | label via `for`; error `aria-describedby`; `*` wajib `aria-label="required"` |
| Cards (§3.3) | `<x-kost-card>`, `<x-room-card>`, `<x-rental-card>` | stable | default, hover | — | default, hover | gambar `alt`; badge ikut aturan kontras status |
| Status Badges (§3.4) | `<x-status-badge>` | stable | draft, pending, approved, active, rejected, completed, cancelled | — | — | bg `-light`/`/10` → `text-*-700` |
| Modal (§3.5) | `<x-modal>` | stable | default, confirm | — | open, closed | `aria-modal`, `aria-labelledby`, initial focus + focus trap + restore |
| Navigation (§3.6) | `<x-nav-public>`, `<x-nav-admin-sidebar>` | stable | public, admin | — | active, hover | icon-only button `aria-label`; dropdown `aria-expanded`/`aria-haspopup` |
| Tables (§3.7) | `<x-table>` | stable | default | — | row hover | `scope="col"`; status via `<x-status-badge>` |
| Empty States (§3.8) | `<x-empty-state>` | stable | empty, no-results, permission-denied | — | — | teks deskriptif + CTA |
| Loading (§3.9) | `<x-skeleton>` (loading), `<x-spinner>` | stable | skeleton, inline, full-page | — | loading | `role="status"` + `aria-label`; isi dekoratif `aria-hidden` |
| Toast (§3.10) | `<x-toast>` | stable | success, error, warning, info | — | show, hide | `role="status"` + `aria-live="polite"`; close button `aria-label` |
| Progress/Timeline (§3.11) | `<x-stepper>`, `<x-progress-steps>` | stable | vertical, horizontal, submission (kost) | — | current, completed | ikon status ≥3:1 (base token OK); connecting line `aria-hidden`; submission: `aria-current="step"` + validasi per langkah (§5.1b) |
| Filters & Search (§3.12) | `<x-filter-panel>` | draft | default | — | — | semua field berlabel |
| Breadcrumbs (§3.13) | `<x-breadcrumbs>` | stable | default | — | — | `aria-label="Breadcrumb"`, `aria-current="page"` |
| Tabs (§3.14) | `<x-tabs>` | draft | default | — | active, inactive | `role="tablist"/"tab"/"tabpanel"`, `aria-selected`, keyboard panah |
| Pagination (§3.15) | `<x-pagination>` | stable | default | — | current, disabled | arrow `aria-label`; current page `aria-current="page"` |
| Accordion (§3.16) | `<x-accordion>`, `<x-accordion-item>` | draft | default | — | open, closed | `aria-expanded`, `aria-controls`, region `aria-labelledby` |
| Callout (§3.17) | `<x-callout>` | stable | info, warning, error, success | — | — | teks `text-*-700`; ikon boleh base |
| Verify Email Modal (§3.18) | `<x-verify-email-modal>` | stable | default | — | open, closed | sama dgn Modal; CTA terima initial focus |
| OTP Input (§3.19) | `<x-otp-input>` | spec | 6-digit | — | error, disabled | `role="group"` + `aria-label` per digit; `inputmode="numeric"` + `autocomplete="one-time-code"`; error `role="alert"` |
| Countdown (§3.20) | `<x-countdown>` | spec | default | — | running, low (<60s), expired | `aria-live` polite (update per menit, jam offscreen); `text-error-700` saat low |
| Password + Strength (§3.21) | `<x-password-strength>` | spec | default | — | 4 level (lemah→kuat) | toggle `aria-pressed`; meter `aria-hidden`, label `aria-live="polite"` |
| QRIS Payment (§3.22) | `<x-qris-payment>` | spec | qris, bank-transfer | — | copied, expired | tab `role="tablist"` (§3.14); copy feedback via toast `role="status"` |
| Booking Form (§3.23) | `<x-booking-form>` | spec | default | — | computed durasi/total, loading | radio native + label wrapper; tanggal `min`/`max` dinamis (ADR-016) |
| Document Upload (§3.24) | `<x-document-upload>` | spec | per-document | — | uploading, approved, rejected | drop zone label + input `sr-only`; progress `role="progressbar"` |
| Confirm Dialog (§3.25) | `<x-confirm-dialog>` | spec | destructive | — | open, loading | pola §3.18: initial focus + trap + restore; `aria-describedby` |
| Page Header (§3.26) | `<x-page-header>` | spec | default | — | — | breadcrumb `aria-label` + `aria-current="page"` |
| **Visual (F4b-1)** | `<x-gallery-lightbox>`, `<x-map>`, `<x-rating>`, `<x-review-card>`, `<x-stat-card>`, `<x-mobile-filter-drawer>` | spec | display, input (rating); default (lainnya) | — | lightbox open/closed; rating chosen/hover | lightbox focus trap + Esc + restore; map fallback alamat + link; rating `aria-pressed` + label dinamis; drawer focus trap + scroll lock |
| **Utility (F4b-2)** | `<x-sticky-action-bar>`, `<x-testimonial-slider>`, `<x-footer>`, `<x-search>`, `<x-tooltip>`, `<x-skeleton>` (extensions) | spec | top/bottom (tooltip); table/avatar/list (skeleton) | — | visible (scroll), open/closed (search), show/hide (tooltip) | bar CTA label eksplisit + `pb-[env(safe-area-inset-bottom)]`; slider `aria-live` + dots `aria-current` + pause hover; search combobox `aria-expanded` + listbox; tooltip `role="tooltip"` + `aria-describedby`; skeleton `role="status"` per grup |
| **Domain Form (F4b-3)** | `<x-kost-form>`, `<x-kost-media>`, `<x-room-form>`, `<x-payment-proof>`, `<x-document-checklist>`, `<x-review-form>`, `<x-review-list>` | draft | — | — | — | spesifikasi menyusul F4b-3 |

> Komponen transaksi & auth **F4a** (§3.19–§3.26), komponen visual **F4b-1** (§3.27–§3.32), dan komponen utility **F4b-2** (§3.33–§3.38) sudah `spec` di tabel di atas. Komponen domain form **F4b-3** (kost form, media, room, payment proof, document checklist, review) masih rencana (`draft`); JANGAN diimplementasi sebelum DESIGN.md diperbarui.

#### Kontras Pasangan Token (verifikasi cepat — §7.1)

| Pasangan | Ratio | Keterangan |
|---|---|---|
| `bg-primary-600` + `text-white` | 4.6:1 | primary CTA |
| `bg-success-700` + `text-white` | 5.4:1 | verified badge solid |
| `bg-success/10` + `text-success-700` | 6.3:1 | success badge subtle |
| `bg-warning/10` + `text-warning-700` | 7.1:1 | warning badge subtle |
| `bg-error/10` + `text-error-700` | 5.9:1 | error badge subtle |
| `bg-error-600` + `text-white` | ≥4.5:1 | danger button |

> Ratio dihitung dari nilai token §2.1 (akurasi ±10% wajar — semua pasangan di atas label "≥ 4.5:1"). Verifikasi final tetap pakai contrast checker (WebAIM) saat implementasi.

---

### 3.1 Buttons

API tunggal: `<x-button>` — variant valid `primary`, `secondary`, `outline`, `ghost`, `danger`, `link`; size `sm`/`md`/`lg`. Implementasi: `resources/views/components/button.blade.php`. Semua variant: `rounded-md` (§2.4), `font-semibold`, `transition-all duration-200`, `focus-visible:ring-2 ring-primary-500 ring-offset-2`, `disabled:opacity-50 disabled:cursor-not-allowed`.

| Variant | Class dasar | Penggunaan |
|---|---|---|
| `primary` | `bg-primary-600 text-white hover:bg-primary-700 active:bg-primary-800` | CTA utama |
| `secondary` | `bg-secondary-500 text-white hover:bg-secondary-600` | aksi sekunder berwarna |
| `outline` | `border border-gray-300 text-gray-700 hover:border-primary-500 hover:text-primary-600` | aksi sekunder netral |
| `ghost` | `text-gray-600 hover:bg-gray-50 hover:text-primary-600` | aksi tersier |
| `danger` | `bg-error-600 text-white hover:bg-error-700` | destruktif (≥4.5:1) |
| `link` | `text-primary-600 hover:text-primary-700 hover:underline` | navigasi berbentuk teks |

#### Primary Button (Main CTA)
```blade
<x-button variant="primary" size="lg">Book Now</x-button>
```

Kelas dasar (untuk konteks tanpa komponen):
```html
<button type="submit"
  class="px-6 py-3 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white font-semibold rounded-md shadow-md hover:shadow-lg transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
  Book Now
</button>
```

#### Secondary / Outline / Ghost / Danger / Link
```blade
<x-button variant="secondary">View Details</x-button>
<x-button variant="outline">Cancel</x-button>
<x-button variant="ghost">Skip</x-button>
<x-button variant="danger">Reject Document</x-button>
<x-button variant="link">Browse Marketplace</x-button>
```

Kelas dasar per variant:
```html
<!-- Secondary -->
<button class="px-6 py-3 bg-secondary-500 hover:bg-secondary-600 text-white font-semibold rounded-md shadow-md transition-all duration-200">
  View Details
</button>

<!-- Outline -->
<button class="px-6 py-3 border border-gray-300 hover:border-primary-500 text-gray-700 hover:text-primary-600 font-semibold rounded-md transition-all duration-200 focus-visible:ring-2 focus-visible:ring-primary-500">
  Cancel
</button>

<!-- Ghost -->
<button class="px-4 py-2 text-gray-600 hover:text-primary-600 hover:bg-gray-50 rounded-md transition-all duration-200">
  Skip
</button>

<!-- Danger -->
<button class="px-6 py-3 bg-error-600 hover:bg-error-700 text-white font-semibold rounded-md shadow-md focus-visible:ring-2 focus-visible:ring-error-600 focus-visible:ring-offset-2">
  Reject Document
</button>

<!-- Link -->
<a href="/marketplace" class="inline-block px-6 py-3 text-primary-600 hover:text-primary-700 font-semibold hover:underline transition-colors duration-200">
  Browse Marketplace
</a>
```

#### With Icon
```html
<button class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-md shadow-md transition-all duration-200">
  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
  </svg>
  Add Kost
</button>
```

#### Loading State (Alpine)
```html
<button x-data="{ loading: false }"
  @click="loading = true"
  :disabled="loading"
  class="px-6 py-3 bg-primary-600 text-white rounded-md disabled:opacity-50 disabled:cursor-not-allowed">
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

#### Sizes
```blade
<x-button size="sm">Small Button</x-button>
<x-button size="md">Medium Button</x-button>   <!-- md = default -->
<x-button size="lg">Large Button</x-button>
```

Kelas dasar: `sm` = `px-4 py-2 text-sm`; `md` = `px-6 py-3 text-base`; `lg` = `px-8 py-4 text-lg` (semua `rounded-md`).

#### Full Width
```blade
<x-button variant="primary" class="w-full">Full Width Button</x-button>
```

#### Disabled
```blade
<x-button variant="primary" :disabled="true">Disabled</x-button>
```
`disabled` → `disabled:opacity-50 disabled:cursor-not-allowed` (built-in semua variant).

---

### 3.2 Form Inputs

**Required marker `*`:** pakai `<span class="text-error-700" aria-label="required">*</span>` — `*` saja tidak dibaca screen reader; beri `aria-label="required"` atau teks `sr-only` "wajib diisi". Input wajib juga memakai atribut `required` (atau `aria-required="true"` bila `required` tidak memungkinkan).

#### Text Input (Standard)
```html
<div class="space-y-2">
  <label for="name" class="block text-sm font-medium text-gray-700">
    Nama Lengkap <span class="text-error-700" aria-label="required">*</span>
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
    Email <span class="text-error-700" aria-label="required">*</span>
  </label>
  <input type="email" 
    id="email" 
    name="email" 
    class="w-full px-4 py-3 border-2 border-error rounded-lg focus:ring-2 focus:ring-error"
    aria-invalid="true"
    aria-describedby="email-error">
  <p id="email-error" class="text-sm text-error-700 flex items-center gap-1">
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
    Deskripsi Kost <span class="text-error-700" aria-label="required">*</span>
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
    Kategori Kost <span class="text-error-700" aria-label="required">*</span>
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
    Upload Gambar Kost <span class="text-error-700" aria-label="required">*</span>
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
    Durasi Sewa <span class="text-error-700" aria-label="required">*</span>
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
    Berapa lama? <span class="text-error-700" aria-label="required">*</span>
  </label>
  <div class="flex items-center gap-2">
    <button type="button" 
      @click="$refs.duration.stepDown()"
      aria-label="Kurangi durasi"
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
      aria-label="Tambah durasi"
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
    Password <span class="text-error-700" aria-label="required">*</span>
  </label>
  <div class="relative">
    <input :type="showPassword ? 'text' : 'password'" 
      id="password" 
      name="password" 
      class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
      placeholder="Minimal 8 karakter">
    <button type="button" 
      @click="showPassword = !showPassword"
      :aria-pressed="showPassword"
      :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
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
<article class="bg-white dark:bg-surface-raised-dark rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group cursor-pointer">
  <a href="/marketplace/kosts/kost-mawar-indah" class="block">
    <!-- Image -->
    <div class="relative h-48 overflow-hidden bg-gray-100 dark:bg-surface-muted-dark">
      <img src="/storage/kosts/thumb-1.jpg" 
        alt="Kost Mawar Indah" 
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
      <!-- Verified Badge -->
      <div class="absolute top-3 right-3 px-3 py-1 bg-success-700 text-white text-xs font-semibold rounded-full flex items-center gap-1 shadow-lg">
        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        Verified
      </div>
    </div>
    
    <!-- Content -->
    <div class="p-5">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-text-strong-dark line-clamp-1 group-hover:text-primary-600 transition-colors">
        Kost Mawar Indah
      </h3>
      <p class="text-sm text-gray-600 dark:text-text-dark mt-1 flex items-center">
        <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        Bandung, Jawa Barat
      </p>
      
      <div class="mt-4 flex items-baseline justify-between">
        <div>
          <span class="text-2xl font-bold text-gray-900 dark:text-text-strong-dark">Rp 1.2jt</span>
          <span class="text-sm text-gray-500">/bulan</span>
        </div>
        <div class="flex items-center text-sm">
          <svg class="w-5 h-5 text-warning fill-current" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
          </svg>
          <span class="ml-1 font-semibold text-gray-700 dark:text-text-dark">4.8</span>
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

> **Dark pair:** Kost Card di atas memakai token semantik (`dark:bg-surface-raised-dark`, `dark:text-text-strong-dark`, dll). Room Type Card & Rental Card: dark pair menyusul — ikuti pola yang sama (surface-raised / text-strong / text-dark / border-dark).
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
    <span class="px-3 py-1 bg-success/10 text-success-700 text-xs font-semibold rounded-full whitespace-nowrap">
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
    <span class="px-3 py-1 bg-warning/10 text-warning-700 text-xs font-semibold rounded-full whitespace-nowrap">
      Pending Payment
    </span>
  </div>
  
  <div class="flex items-center justify-between pt-3 border-t border-gray-100">
    <span class="text-sm text-gray-600">Deadline pembayaran:</span>
    <span class="text-sm font-semibold text-error-700">23 jam lagi</span>
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

**Aturan kontras badge:** background `-light` atau `*/10` → teks wajib `text-*-700` (lihat tabel pasangan kontras §3.0). Solid badge (verified) pakai `bg-success-700 text-white`. Ikon di dalam badge memakai `currentColor` dari teks.

```html
<!-- Draft -->
<span class="inline-flex items-center gap-1 px-3 py-1 bg-gray-100 text-gray-700 text-xs font-semibold rounded-full">
  <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
  </svg>
  Draft
</span>

<!-- Pending Review -->
<span class="inline-flex items-center gap-1 px-3 py-1 bg-warning/10 text-warning-700 text-xs font-semibold rounded-full">
  <svg class="w-3 h-3 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
  </svg>
  Pending Review
</span>

<!-- Approved -->
<span class="inline-flex items-center gap-1 px-3 py-1 bg-success/10 text-success-700 text-xs font-semibold rounded-full">
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
<span class="inline-flex items-center gap-1 px-3 py-1 bg-error/10 text-error-700 text-xs font-semibold rounded-full">
  <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
  </svg>
  Rejected
</span>

<!-- Completed -->
<span class="inline-flex items-center gap-1 px-3 py-1 bg-success/10 text-success-700 text-xs font-semibold rounded-full">
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

### 3.5 Modal/Dialog

```html
<!-- Modal Container -->
<div x-data="{ open: false }" x-cloak>
  <!-- Trigger Button -->
  <button x-ref="trigger" @click="open = true; $nextTick(() => $refs.panel?.querySelector('[data-autofocus]')?.focus())"
    class="px-6 py-3 bg-error-600 text-white rounded-lg">
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
    @keydown.escape.window="open = false; $refs.trigger?.focus()"      <!-- restore focus ke trigger -->
    @keydown.tab.prevent="                                            
      /* focus trap aktif: cycle elemen fokusable di dalam panel */
      const f = $refs.panel.querySelectorAll('button, [href], input, textarea, select, [tabindex]:not([tabindex="-1"])');
      const first = f[0], last = f[f.length - 1];
      if ($event.shiftKey && document.activeElement === first) { last.focus(); }
      else if (!$event.shiftKey && document.activeElement === last) { first.focus(); }
    "
    class="fixed inset-0 z-50 overflow-y-auto" 
    aria-labelledby="modal-title" 
    role="dialog" 
    aria-modal="true">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-gray-900 bg-opacity-75 dark:bg-overlay-dark transition-opacity" @click="open = false; $refs.trigger?.focus()"></div>
    
    <!-- Modal Content -->
    <div class="flex min-h-full items-center justify-center p-4">
      <div x-ref="panel"
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="relative transform overflow-hidden rounded-xl bg-white dark:bg-surface-raised-dark shadow-2xl transition-all w-full max-w-lg"
        @click.stop>
        
        <!-- Header -->
        <div class="px-6 pt-6 pb-4 border-b border-gray-100 dark:border-border-dark">
          <div class="flex items-start justify-between">
            <h3 id="modal-title" class="text-xl font-semibold text-gray-900 dark:text-text-strong-dark">
              Konfirmasi Pembatalan Rental
            </h3>
            <button @click="open = false; $refs.trigger?.focus()" 
              aria-label="Tutup modal"
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
              <p class="text-sm text-gray-600 dark:text-text-dark">
                Apakah Anda yakin ingin membatalkan rental ini? Tindakan ini tidak dapat dibatalkan.
              </p>
              <div class="mt-4">
                <label for="cancel_reason" class="block text-sm font-medium text-gray-700 dark:text-text-dark mb-2">
                  Alasan pembatalan <span class="text-error-700" aria-label="required">*</span>
                </label>
                <textarea id="cancel_reason" 
                  data-autofocus
                  rows="3" 
                  class="w-full px-4 py-3 border border-gray-300 dark:border-border-strong-dark dark:bg-surface-muted-dark rounded-lg focus:ring-2 focus:ring-primary-500"
                  placeholder="Jelaskan alasan Anda membatalkan rental..."></textarea>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Footer -->
        <div class="px-6 py-4 bg-gray-50 dark:bg-surface-muted-dark flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
          <button @click="open = false; $refs.trigger?.focus()" 
            class="px-4 py-2 border border-gray-300 dark:border-border-dark text-gray-700 dark:text-text-dark rounded-lg hover:bg-gray-50 transition-all">
            Batal
          </button>
          <button class="px-4 py-2 bg-error-600 text-white rounded-lg hover:brightness-90 transition-all">
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
<nav x-data="{ mobileMenuOpen: false }" class="bg-white dark:bg-surface-raised-dark shadow-sm sticky top-0 z-40 border-b border-gray-200 dark:border-border-dark">
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
              aria-label="Menu akun"
              aria-haspopup="menu"
              :aria-expanded="open"
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
              @keydown.escape.window="open = false"       <!-- focus management dasar: Esc tutup, Tab keluar -->
              x-transition:enter="transition ease-out duration-200"
              x-transition:enter-start="opacity-0 scale-95"
              x-transition:enter-end="opacity-100 scale-100"
              role="menu"
              class="absolute right-0 mt-2 w-48 bg-white dark:bg-surface-raised-dark rounded-lg shadow-lg ring-1 ring-gray-900/5 dark:ring-border-dark py-1">
              <a href="/profile" role="menuitem" class="block px-4 py-2 text-sm text-gray-700 dark:text-text-dark hover:bg-gray-50">
                Profil Saya
              </a>
              <a href="/rentals" role="menuitem" class="block px-4 py-2 text-sm text-gray-700 dark:text-text-dark hover:bg-gray-50">
                Rental Saya
              </a>
              <hr class="my-1 border-gray-200">
              <form method="POST" action="/logout">
                @csrf
                <button type="submit" role="menuitem" class="w-full text-left px-4 py-2 text-sm text-error-700 hover:bg-gray-50">
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
          aria-label="Buka menu navigasi"
          :aria-expanded="mobileMenuOpen"
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
    class="md:hidden border-t border-gray-200 dark:border-border-dark">
    <div class="px-2 pt-2 pb-3 space-y-1">
      <a href="/marketplace" class="block px-3 py-2 rounded-lg text-base font-medium text-gray-700 dark:text-text-dark hover:bg-gray-50 dark:hover:bg-surface-muted-dark">
        Cari Kost
      </a>
      @auth
        <a href="/rentals" class="block px-3 py-2 rounded-lg text-base font-medium text-gray-700 dark:text-text-dark hover:bg-gray-50 dark:hover:bg-surface-muted-dark">
          Rental Saya
        </a>
        <a href="/profile" class="block px-3 py-2 rounded-lg text-base font-medium text-gray-700 dark:text-text-dark hover:bg-gray-50 dark:hover:bg-surface-muted-dark">
          Profil Saya
        </a>
        <form method="POST" action="/logout">
          @csrf
          <button type="submit" class="w-full text-left px-3 py-2 rounded-lg text-base font-medium text-error-700 hover:bg-gray-50 dark:hover:bg-surface-muted-dark">
            Logout
          </button>
        </form>
      @else
        <a href="/login" class="block px-3 py-2 rounded-lg text-base font-medium text-gray-700 dark:text-text-dark hover:bg-gray-50 dark:hover:bg-surface-muted-dark">
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
        <span class="ml-auto px-2 py-0.5 bg-warning-700 text-white text-xs font-semibold rounded-full">
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
            <x-status-badge :status="$rental->status" />
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
              <button class="text-success-700 hover:text-success-800">
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
<div role="status" aria-label="Memuat data..." class="bg-white dark:bg-surface-raised-dark rounded-xl shadow-md p-5 animate-pulse">
  <!-- Image Skeleton -->
  <div class="h-48 bg-gray-200 dark:bg-surface-muted-dark rounded-lg mb-4" aria-hidden="true"></div>
  
  <!-- Title Skeleton -->
  <div class="h-6 bg-gray-200 dark:bg-surface-muted-dark rounded w-3/4 mb-2" aria-hidden="true"></div>
  
  <!-- Subtitle Skeleton -->
  <div class="h-4 bg-gray-200 dark:bg-surface-muted-dark rounded w-1/2 mb-4" aria-hidden="true"></div>
  
  <!-- Price Skeleton -->
  <div class="flex justify-between items-center">
    <div class="h-8 bg-gray-200 dark:bg-surface-muted-dark rounded w-1/3" aria-hidden="true"></div>
    <div class="h-4 bg-gray-200 dark:bg-surface-muted-dark rounded w-1/4" aria-hidden="true"></div>
  </div>
  <span class="sr-only">Mohon tunggu, data sedang dimuat...</span>
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
<!-- Teks utama dapat di-override per konteks melalui slot -->
<div class="fixed inset-0 bg-white bg-opacity-90 z-50 flex items-center justify-center">
  <div class="text-center">
    <svg class="animate-spin h-16 w-16 text-primary-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    <p class="text-lg font-medium text-gray-900">{{ $slot ?? 'Mohon tunggu...' }}</p>
    <p class="text-sm text-gray-600 mt-1">Proses sedang berjalan</p>
  </div>
</div>
```

---

### 3.10 Toast/Alert Notifications

```html
<!-- Toast Container (membaca global Alpine store dari app.js — API: show({type, message, duration})) -->
<div x-cloak>
  <div x-show="$store.toast.visible" 
    role="status"
    aria-live="polite"
    x-transition:enter="transition ease-out duration-300 transform"
    x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
    x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-y-0 opacity-100"
    x-transition:leave-end="translate-y-2 opacity-0"
    class="fixed top-4 right-4 z-50 max-w-sm w-full bg-white dark:bg-surface-raised-dark shadow-lg rounded-lg pointer-events-auto ring-1 ring-gray-900/5 dark:ring-border-dark">
    <div class="p-4">
      <div class="flex items-start">
        <!-- Icon (dynamic based on type) -->
        <div class="flex-shrink-0">
          <svg x-show="$store.toast.type === 'success'" class="h-6 w-6 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg x-show="$store.toast.type === 'error'" class="h-6 w-6 text-error" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg x-show="$store.toast.type === 'warning'" class="h-6 w-6 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <svg x-show="$store.toast.type === 'info'" class="h-6 w-6 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        
        <!-- Content -->
        <div class="ml-3 flex-1">
          <p x-text="$store.toast.message" class="text-sm font-medium text-gray-900 dark:text-text-strong-dark"></p>
        </div>
        
        <!-- Close Button -->
        <button @click="$store.toast.hide()" 
          aria-label="Tutup"
          class="ml-4 flex-shrink-0 text-gray-400 hover:text-gray-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 rounded-lg">
          <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Usage: panggil global store dari mana saja (Blade/JS/Alpine)
Alpine.store('toast').show({
  type: 'success',
  message: 'Payment berhasil diverifikasi — silakan upload dokumen administrasi',
  duration: 5000
});
-->
```

### 3.11 Progress/Timeline

**Ikon status:** lingkaran stepper memakai base token (`bg-success`, `bg-warning`) — elemen non-teks besar, minimum kontras 3:1 terpenuhi (icon ≥3:1 OK). Teks status di sampingnya memakai `text-gray-900`/`text-gray-500` (≥4.5:1).

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
          {{ $currentStep > $index ? 'bg-success-700 text-white' : ($currentStep === $index ? 'bg-warning-700 text-white' : 'bg-gray-200 text-gray-700') }} 
          font-semibold text-sm">
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
              <svg class="w-4 h-4 text-warning fill-current" viewBox="0 0 20 20">
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
<div x-data="{ activeTab: 'info', tabs: ['info', 'rooms', 'facilities', 'payment'] }">
  <!-- Tab Navigation -->
  <div class="border-b border-gray-200 dark:border-border-dark">
    <nav role="tablist" aria-label="Tabs"
      class="-mb-px flex space-x-8"
      @keydown.arrow-right.prevent="activeTab = tabs[(tabs.indexOf(activeTab) + 1) % tabs.length]; $nextTick(() => $el.querySelector('[aria-selected=\'true\']')?.focus())"
      @keydown.arrow-left.prevent="activeTab = tabs[(tabs.indexOf(activeTab) - 1 + tabs.length) % tabs.length]; $nextTick(() => $el.querySelector('[aria-selected=\'true\']')?.focus())">
      <button id="tab-info" role="tab" aria-controls="tab-panel-info"
        @click="activeTab = 'info'" :tabindex="activeTab === 'info' ? 0 : -1"
        :aria-selected="activeTab === 'info'"
        :class="activeTab === 'info' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" 
        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 rounded-t-lg">
        Informasi Dasar
      </button>
      <button id="tab-rooms" role="tab" aria-controls="tab-panel-rooms"
        @click="activeTab = 'rooms'" :tabindex="activeTab === 'rooms' ? 0 : -1"
        :aria-selected="activeTab === 'rooms'"
        :class="activeTab === 'rooms' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" 
        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all focus-visible:ring-2 focus-visible:ring-primary-500 rounded-t-lg">
        Tipe Kamar
        <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600">
          {{ $roomTypesCount }}
        </span>
      </button>
      <button id="tab-facilities" role="tab" aria-controls="tab-panel-facilities"
        @click="activeTab = 'facilities'" :tabindex="activeTab === 'facilities' ? 0 : -1"
        :aria-selected="activeTab === 'facilities'"
        :class="activeTab === 'facilities' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" 
        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all focus-visible:ring-2 focus-visible:ring-primary-500 rounded-t-lg">
        Fasilitas & Aturan
      </button>
      <button id="tab-payment" role="tab" aria-controls="tab-panel-payment"
        @click="activeTab = 'payment'" :tabindex="activeTab === 'payment' ? 0 : -1"
        :aria-selected="activeTab === 'payment'"
        :class="activeTab === 'payment' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" 
        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all focus-visible:ring-2 focus-visible:ring-primary-500 rounded-t-lg">
        Payment & Dokumen
      </button>
    </nav>
  </div>
  
  <!-- Tab Panels -->
  <div class="mt-6">
    <div id="tab-panel-info" role="tabpanel" aria-labelledby="tab-info" tabindex="0"
      x-show="activeTab === 'info'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
      <!-- Konten: partial spesifik halaman admin/kosts/partials/info-tab.blade.php dirender di sini -->
    </div>
    
    <div id="tab-panel-rooms" role="tabpanel" aria-labelledby="tab-rooms" tabindex="0"
      x-show="activeTab === 'rooms'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
      <!-- Konten: partial spesifik halaman admin/kosts/partials/rooms-tab.blade.php dirender di sini -->
    </div>
    
    <div id="tab-panel-facilities" role="tabpanel" aria-labelledby="tab-facilities" tabindex="0"
      x-show="activeTab === 'facilities'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
      <!-- Konten: partial spesifik halaman admin/kosts/partials/facilities-tab.blade.php dirender di sini -->
    </div>
    
    <div id="tab-panel-payment" role="tabpanel" aria-labelledby="tab-payment" tabindex="0"
      x-show="activeTab === 'payment'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
      <!-- Konten: partial spesifik halaman admin/kosts/partials/payment-tab.blade.php dirender di sini -->
    </div>
  </div>
</div>
```

**Keyboard (roving tabindex):** hanya tab aktif yang tabbable (`:tabindex` 0/-1); ArrowLeft/ArrowRight memindahkan `activeTab` + focus ke tab baru; Tab masuk ke tablist, Tab lagi pindah ke panel.

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
            aria-label="Halaman sebelumnya"
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
            aria-label="Halaman berikutnya"
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
    <div class="border border-gray-200 dark:border-border-dark rounded-lg overflow-hidden">
      <!-- Accordion Header -->
      <button id="acc-btn-{{ $index }}" type="button"
        @click="open = open === {{ $index }} ? null : {{ $index }}"
        :aria-expanded="open === {{ $index }}"
        aria-controls="acc-panel-{{ $index }}"
        class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
        <span class="text-sm font-semibold text-gray-900 dark:text-text-strong-dark">
          {{ $faq->question }}
        </span>
        <svg :class="{ 'rotate-180': open === {{ $index }} }"
          class="w-5 h-5 text-gray-500 transition-transform duration-200" 
          fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
      </button>
      
      <!-- Accordion Body -->
      <div id="acc-panel-{{ $index }}" role="region" aria-labelledby="acc-btn-{{ $index }}"
        x-show="open === {{ $index }}"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 max-h-0"
        x-transition:enter-end="opacity-100 max-h-96"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 max-h-96"
        x-transition:leave-end="opacity-0 max-h-0"
        class="px-6 py-4 bg-gray-50 dark:bg-surface-muted-dark border-t border-gray-200 dark:border-border-dark">
        <p class="text-sm text-gray-600 dark:text-text-dark leading-relaxed">
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
<div class="flex items-start gap-3 p-4 bg-info-light border-l-4 border-info-700/30 rounded-lg">
  <svg class="w-5 h-5 text-info-700 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
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
<div class="flex items-start gap-3 p-4 bg-warning-light border-l-4 border-warning-700/30 rounded-lg">
  <svg class="w-5 h-5 text-warning-700 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
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
<div class="flex items-start gap-3 p-4 bg-error-light border-l-4 border-error-700/30 rounded-lg">
  <svg class="w-5 h-5 text-error-700 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
  </svg>
  <div class="flex-1">
    <h4 class="text-sm font-semibold text-gray-900">Submission Ditolak</h4>
    <p class="text-sm text-gray-600 mt-1">
      {{ $rejectionReason }}
    </p>
    <a href="/admin/kosts/{{ $kostId }}/edit" class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-error-700 hover:text-error-800 hover:underline">
      Revisi Kost
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
      </svg>
    </a>
  </div>
</div>

<!-- Success Callout -->
<div class="flex items-start gap-3 p-4 bg-success-light border-l-4 border-success-700/30 rounded-lg">
  <svg class="w-5 h-5 text-success-700 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
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
    @keydown.escape.window="open = false; document.body.classList.remove('overflow-hidden'); document.querySelector('main')?.focus()"   <!-- restore focus: dipicu flash, tidak ada trigger button → kembali ke <main> (perlu tabindex="-1") -->
    @keydown.tab.prevent="
      /* focus trap aktif: cycle elemen fokusable di dalam panel */
      const f = $refs.panel.querySelectorAll('button, [href], input, textarea, select, [tabindex]:not([tabindex="-1"])');
      const first = f[0], last = f[f.length - 1];
      if ($event.shiftKey && document.activeElement === first) { last.focus(); }
      else if (!$event.shiftKey && document.activeElement === last) { first.focus(); }
    "
    class="fixed inset-0 z-50 overflow-y-auto"
    role="dialog"
    aria-modal="true"
    aria-labelledby="verify-email-modal-title">

    <!-- Backdrop -->
    <div class="fixed inset-0 bg-gray-900 bg-opacity-75 dark:bg-overlay-dark transition-opacity" @click="open = false; document.body.classList.remove('overflow-hidden')"></div>

    <!-- Modal Content -->
    <div class="flex min-h-full items-center justify-center p-4">
      <div x-ref="panel"
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="relative transform overflow-hidden rounded-xl bg-white dark:bg-surface-raised-dark shadow-2xl transition-all w-full max-w-md text-center"
        @click.stop>

        <!-- Close button -->
        <button @click="open = false; document.body.classList.remove('overflow-hidden'); document.querySelector('main')?.focus()"
          class="absolute top-4 right-4 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-lg p-1"
          aria-label="Tutup modal">
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

          <h3 id="verify-email-modal-title" class="text-xl font-semibold text-gray-900 dark:text-text-strong-dark">
            Email Anda Belum Diverifikasi
          </h3>

          <p class="mt-2 text-sm text-gray-600 dark:text-text-dark">
            Verifikasi email diperlukan untuk mengakses fitur ini. Kode OTP akan dikirim ke email Anda saat Anda membuka halaman verifikasi.
          </p>

          <!-- CTA -->
          <a href="{{ route('verification.notice') }}"
            data-autofocus
            class="mt-6 inline-flex w-full items-center justify-center px-6 py-3 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
            Verifikasi Email
          </a>

          <!-- Dismiss -->
          <button @click="open = false; document.body.classList.remove('overflow-hidden'); document.querySelector('main')?.focus()"
            class="mt-3 inline-flex w-full items-center justify-center px-6 py-3 text-sm font-semibold text-gray-600 dark:text-text-dark hover:text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-300">
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
- **Initial focus** → CTA "Verifikasi Email" (`data-autofocus`); **focus trap** aktif (Tab cycle dalam panel); **restore focus** → `main` saat close (modal dipicu flash session, tidak ada trigger button — `<main>` perlu `tabindex="-1"`)
- `Escape` menutup modal; tombol tutup punya `aria-label="Tutup modal"`

### 3.19 OTP Input (x-otp-input)

Input kode OTP 6 digit untuk verifikasi email (PAGE-006) & reset password (PAGE-006B): 6 kotak single-digit, auto-advance (fokus pindah ke kotak berikutnya), backspace ke kotak sebelumnya, paste 6 digit terbagi otomatis, auto-submit saat digit ke-6 terisi. Implementasi: `components/otp-input.blade.php`.

```html
<form method="POST" action="/verify-email" x-ref="form" x-cloak
  x-data="{
    digits: Array(6).fill(''),
    error: @js($errors->first('otp')),
    get code() { return this.digits.join(''); },
    focusAt(i) { this.$refs.inputs[i]?.focus(); },
    onInput(i, e) {
      const v = e.target.value.replace(/\D/g, '').slice(-1);
      this.digits[i] = v;
      this.error = '';
      if (v && i < 5) this.focusAt(i + 1);
      if (this.code.length === 6) this.$refs.form.submit();   // auto-submit (native, tidak picu @submit)
    },
    onBackspace(i, e) { if (e.target.value === '' && i > 0) this.focusAt(i - 1); },
    onPaste(e) {
      const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
      if (!text) return;
      e.preventDefault();
      [...text].forEach((ch, j) => { this.digits[j] = ch; });
      this.focusAt(Math.min(text.length - 1, 5));
      if (this.code.length === 6) this.$refs.form.submit();
    }
  }">
  <input type="hidden" name="otp" :value="code">

  <div class="flex justify-center gap-2 sm:gap-3" role="group" aria-label="Kode OTP 6 digit">
    <template x-for="(d, i) in digits" :key="i">
      <input type="text" x-ref="inputs" :name="'digit-' + (i + 1)"
        :value="d" :aria-label="'Digit ' + (i + 1)"
        :aria-invalid="error ? 'true' : 'false'"
        inputmode="numeric" autocomplete="one-time-code" maxlength="1" required
        @input="onInput(i, $event)" @keydown.backspace="onBackspace(i, $event)"
        @keydown.arrow-left="focusAt(i - 1)" @keydown.arrow-right="focusAt(i + 1)"
        @paste="onPaste($event)"
        class="w-12 h-14 text-center text-xl font-bold border rounded-md focus:ring-2 focus:ring-primary-500 transition-all"
        :class="error ? 'border-error' : 'border-border-strong dark:border-border-strong-dark'">
    </template>
  </div>

  <p x-show="error" x-text="error" class="mt-3 text-sm text-error-700 text-center" role="alert"></p>

  <button type="submit" :disabled="code.length !== 6"
    class="mt-6 w-full inline-flex items-center justify-center px-4 py-3 bg-primary-600 text-white font-semibold rounded-md hover:bg-primary-700 focus:ring-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
    Verifikasi
  </button>
</form>
```

**A11y:**
- Setiap kotak `aria-label="Digit 1..6"`; wrapper `role="group"` + label "Kode OTP 6 digit"; error `aria-invalid` + `role="alert"`
- `inputmode="numeric"` → keypad angka mobile; `autocomplete="one-time-code"` → autofill OTP dari SMS/email oleh browser/OS
- Auto-advance/backspace tetap bisa di-override keyboard (panah kiri/kanan); paste didukung; fokus tidak pernah hilang dari deretan input
- **`x-cloak`:** wajib pada root komponen — tanpa CSS `[x-cloak]{display:none}`, HTML mentah tampil sekejap sebelum Alpine mount (berlaku semua komponen Alpine di dokumen ini)

**Dark pair:** border `dark:border-border-strong-dark`; background kotak ikut surface (tambahkan `bg-surface-raised dark:bg-surface-raised-dark` bila kotak berada di atas surface non-putih).

---

### 3.20 Countdown (x-countdown)

Countdown hh:mm:ss untuk batas waktu transaksi — OTP expiry (PAGE-006/006B, 15 menit) & payment deadline (PAGE-009, 48 jam). Implementasi: `components/countdown.blade.php`.

```html
<div x-data="{
    deadline: @js($expiresAt->toIso8601String()),   // Carbon dari server — jangan hitung deadline di client
    now: Date.now(),
    timer: null,
    expired: false,
    get diff() { return Math.max(0, new Date(this.deadline).getTime() - this.now); },
    get total() { return Math.floor(this.diff / 1000); },
    get hh() { return String(Math.floor(this.total / 3600)).padStart(2, '0'); },
    get mm() { return String(Math.floor((this.total % 3600) / 60)).padStart(2, '0'); },
    get ss() { return String(this.total % 60).padStart(2, '0'); },
    get low() { return this.total < 60; },            // < 60s → merah
    init() {
      this.timer = setInterval(() => {
        this.now = Date.now();
        if (this.diff <= 0) { clearInterval(this.timer); this.expired = true; }
      }, 1000);
    },
    destroy() { clearInterval(this.timer); }
  }" x-cloak>

  <p class="text-sm font-medium" :class="low ? 'text-error-700' : 'text-gray-700 dark:text-text-dark'">
    <span aria-hidden="true" x-text="`${mm}:${ss}`"></span>
    <span class="sr-only" x-text="`${hh} jam, ${mm} menit, ${ss} detik`"></span>
  </p>

  <template x-if="expired">
    <p class="mt-1 text-sm text-error-700" role="status">Waktu habis — silakan kirim ulang.</p>
  </template>
</div>
```

- `deadline` wajib dari server (Carbon) — jam client bisa salah/ubah; sesuaikan `now` dengan selisih waktu server bila perlu
- Reset countdown (mis. resend OTP): ganti nilai `deadline` — interval tetap berjalan, `diff` otomatis terhitung ulang
- Callback expired: komponen dispatch `CustomEvent('expired')` dari `init` (`this.expired` dipantau parent via `@expired.window`) — dipakai untuk enable tombol "Kirim ulang OTP" / disable submit bukti bayar

**A11y:**
- Region `aria-live="polite"` (**via `role="status"`** pada teks countdown) — update per detik bisa bising; solusi: `mm:ss` dibungkus `aria-hidden="true"`, region live hanya menyebut menit (bandingkan nilai `mm` sebelumnya di interval), jam disimpan offscreen `sr-only`
- Warna `text-error-700` (≥4.5:1) saat <60s sebagai sinyal kedua selain angka
- Teks expired `role="status"` agar dibacakan SR

**Dark pair:** teks normal `text-gray-700 dark:text-text-dark`; `text-error-700` berlaku sama di kedua mode (kontras cukup).

---

### 3.21 Password Input + Strength Meter (x-password-strength)

Gabungan **toggle visibility (§3.2 Password Input)** + **strength meter 4 level** untuk register (PAGE-005) & change password (PAGE-006C). Satu komponen lengkap — §3.2 tetap sebagai referensi toggle sederhana. Implementasi: `components/password-strength.blade.php`.

```html
<div x-data="{
    value: '',
    show: false,
    get score() {            // 0 kosong, 1 lemah … 4 kuat
      const v = this.value;
      if (!v) return 0;
      let s = 1;
      if (v.length >= 8) s++;
      if (/[a-z]/.test(v) && /[A-Z]/.test(v)) s++;
      if (/\d/.test(v) && /[^A-Za-z0-9]/.test(v)) s++;
      return Math.min(4, s);
    },
    get label() { return ['', 'Lemah', 'Cukup', 'Kuat', 'Sangat kuat'][this.score]; },
    get bar() { return ['', 'bg-error-700', 'bg-warning-700', 'bg-info-700', 'bg-success-700'][this.score]; },
    get labelClass() { return ['', 'text-error-700', 'text-warning-700', 'text-info-700', 'text-success-700'][this.score]; }
  }" x-cloak class="space-y-2">

  <label for="password" class="block text-sm font-medium text-gray-700 dark:text-text-strong-dark">
    Password <span class="text-error-700" aria-label="required">*</span>
  </label>

  <div class="relative">
    <input :type="show ? 'text' : 'password'" id="password" name="password" x-model="value"
      placeholder="Minimal 8 karakter" autocomplete="new-password"
      aria-describedby="password-hint password-strength"
      class="w-full px-4 py-3 pr-12 border border-gray-300 dark:border-border-strong-dark rounded-md focus:ring-2 focus:ring-primary-500 transition-all">
    <button type="button" @click="show = !show" :aria-pressed="show"
      :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'"
      class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-md focus:ring-2 focus:ring-primary-500">
      <!-- ikon mata / mata tercoret: reuse §3.2 Password Input -->
    </button>
  </div>

  <div class="flex gap-1" aria-hidden="true">
    <template x-for="i in 4" :key="i">
      <div class="h-1.5 flex-1 rounded-full transition-colors"
        :class="i <= score ? bar : 'bg-gray-200 dark:bg-border-dark'"></div>
    </template>
  </div>

  <p id="password-strength" class="text-xs font-medium" :class="labelClass" aria-live="polite" x-text="label"></p>

  <ul id="password-hint" class="text-xs text-gray-500 dark:text-text-muted-dark space-y-0.5">
    <li>Minimal 8 karakter</li>
    <li>Kombinasi huruf besar & kecil, angka, simbol</li>
  </ul>
</div>
```

- Skor sederhana (panjang + variasi) — cukup untuk UX; kebijakan password final tetap di server (Form Request `min:8`, dsb.)
- Level warna memakai `-700` (≥4.5:1) — TIDAK pernah base `text-success` dll (§2.1)

**A11y:**
- Meter segmen `aria-hidden="true"` (dekoratif); label teks "Kuat" yang `aria-live="polite"` yang dibaca SR — id dihubungkan via `aria-describedby`
- Toggle `aria-pressed` + label dinamis ("Tampilkan/Sembunyikan password") — reuse §3.2
- Touch target toggle ≥44px (`p-2` + input `py-3`)

**Dark pair:** label/border/hint pakai `-dark` pairs; segmen kosong `dark:bg-border-dark`.

---

### 3.22 QRIS Payment (x-qris-payment)

Section pembayaran PAGE-009: nominal + payment ref, QRIS statis + merchant, tab bank transfer (BCA/BNI/Mandiri) + copy rekening, deadline countdown, instruksi upload bukti. Implementasi: `components/qris-payment.blade.php`.

```html
<div x-data="{
    bank: 'bca',
    copied: false,
    banks: @js($banks),   // [{code:'bca', name:'BCA', number:'1234567890', holder:'PT SewaKost Indonesia'}, ...]
    async copy() {
      const target = this.banks.find(b => b.code === this.bank);
      try { await navigator.clipboard.writeText(target.number); }
      catch {   // fallback: non-secure context / permission denied
        const t = document.createElement('textarea');
        t.value = target.number; t.style.position = 'fixed'; t.style.opacity = '0';
        document.body.appendChild(t); t.select(); document.execCommand('copy'); t.remove();
      }
      this.copied = true;
      Alpine.store('toast').show({ type: 'success', message: 'Nomor rekening disalin' });
      setTimeout(() => this.copied = false, 2000);
    }
  }" x-cloak class="space-y-6">

  <!-- Nominal -->
  <div class="text-center">
    <p class="text-sm text-gray-500 dark:text-text-muted-dark">Total pembayaran</p>
    <p class="text-2xl font-bold text-gray-900 dark:text-text-strong-dark">Rp 4.500.000</p>
    <p class="text-xs text-gray-500 dark:text-text-muted-dark">Ref: <span class="font-mono">SK-2026-000123</span></p>
  </div>

  <!-- Deadline -->
  <x-countdown :expires-at="$payment->expired_at" />   <!-- §3.20 -->

  <!-- QRIS -->
  <div class="mx-auto max-w-xs rounded-lg border border-gray-200 dark:border-border-dark bg-surface-raised dark:bg-surface-raised-dark p-4 text-center shadow-sm">
    <!-- Placeholder QR dummy — produksi ganti <img :src="..." alt="QRIS"> dari server -->
    <svg class="mx-auto h-40 w-40 text-gray-800 dark:text-white" viewBox="0 0 40 40" fill="currentColor" aria-hidden="true">
      <rect x="1" y="1" width="12" height="12"/><rect x="4" y="4" width="6" height="6" fill="var(--color-surface-raised)"/>
      <rect x="27" y="1" width="12" height="12"/><rect x="30" y="4" width="6" height="6" fill="var(--color-surface-raised)"/>
      <rect x="1" y="27" width="12" height="12"/><rect x="4" y="30" width="6" height="6" fill="var(--color-surface-raised)"/>
      <rect x="16" y="8" width="3" height="3"/><rect x="22" y="16" width="3" height="3"/><rect x="16" y="22" width="3" height="3"/>
      <rect x="28" y="28" width="3" height="3"/><rect x="8" y="16" width="3" height="3"/><rect x="24" y="24" width="3" height="3"/>
    </svg>
    <p class="mt-3 text-sm font-semibold text-gray-900 dark:text-text-strong-dark">Kost Griya Asri</p>
    <p class="text-xs text-gray-500 dark:text-text-muted-dark">Scan QRIS via e-wallet / m-banking</p>
    <a href="{{ route('qris.download') }}" class="mt-2 inline-block text-sm font-semibold text-primary-600 hover:text-primary-700 focus:ring-2 focus:ring-primary-500 rounded-md px-2 py-1.5">Download QR</a>
  </div>

  <!-- Tab Bank Transfer -->
  <div>
    <div class="flex gap-2" role="tablist" aria-label="Metode transfer bank">
      <template x-for="b in banks" :key="b.code">
        <button type="button" role="tab" :aria-selected="bank === b.code" :aria-controls="'tab-' + b.code"
          @click="bank = b.code"
          class="px-4 py-2 text-sm font-semibold rounded-md focus:ring-2 focus:ring-primary-500 transition-all"
          :class="bank === b.code ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-surface-muted-dark dark:text-text-dark'">
          <span x-text="b.name"></span>
        </button>
      </template>
    </div>
    <template x-for="b in banks" :key="b.code">
      <div x-show="bank === b.code" :id="'tab-' + b.code" role="tabpanel"
        class="mt-3 rounded-lg border border-gray-200 dark:border-border-dark bg-surface-raised dark:bg-surface-raised-dark p-4">
        <p class="text-sm text-gray-600 dark:text-text-dark">
          <span x-text="b.holder"></span> — <span class="font-mono font-semibold" x-text="b.number"></span>
        </p>
        <button type="button" @click="copy()" :aria-label="'Salin nomor rekening ' + b.name"
          class="mt-2 inline-flex items-center gap-1 text-sm font-semibold text-primary-600 hover:text-primary-700 focus:ring-2 focus:ring-primary-500 rounded-md px-2 py-1.5">
          <span x-text="copied ? 'Disalin ✓' : 'Salin nomor rekening'"></span>
        </button>
      </div>
    </template>
  </div>

  <!-- Instruksi upload bukti -->
  <div class="rounded-lg bg-info-light dark:bg-surface-muted-dark p-4 text-sm text-info-700 dark:text-text-dark space-y-1">
    <p class="font-semibold">Setelah transfer:</p>
    <ol class="list-decimal list-inside">
      <li>Screenshot / simpan bukti transfer</li>
      <li>Upload di form bukti bayar bawah (jpeg/png, ≤5MB)</li>
      <li>Klik "Kirim Bukti Bayar" — admin verifikasi ≤48 jam</li>
    </ol>
  </div>
</div>
```

- **Copy feedback:** toast store (§3.10, `role="status"`) + teks tombol berubah "Disalin ✓" (2 detik); `navigator.clipboard` butuh secure context — fallback `execCommand` wajib; catatan: Alpine 3.14 punya magic `$clipboard` yang membungkus keduanya, boleh dipakai
- Payment ref (`x-payment-ref`) ditampilkan font-mono agar mudah dicocokkan saat verifikasi admin
- Tab aktif `bg-primary-600` (kontras 4.6:1 dengan teks putih, §3.0)

**A11y:** tab `role="tablist"/"tab"/"tabpanel"` + `aria-selected`/`aria-controls` + keyboard panah (§3.14); copy button berlabel teks (bukan icon-only), feedback via toast; QR `alt` saat diganti `<img>` produksi.

**Dark pair:** kartu QRIS & panel tab `bg-surface-raised dark:bg-surface-raised-dark` + `border-border dark:border-border-dark`; tab inactive `dark:bg-surface-muted-dark dark:text-text-dark`; callout info `dark:bg-surface-muted-dark dark:text-text-dark`.

---

### 3.23 Booking Form (x-booking-form)

Form create rental (PAGE-010, FR-061—FR-068, FR-122, ADR-016): pilih kamar (radio cards), tanggal mulai (min today+4, max today+30) & selesai, durasi otomatis + kalkulasi harga realtime, ringkasan sticky di desktop, loading saat submit. Implementasi: `components/booking-form.blade.php`.

```html
<form method="POST" action="/rentals" @submit="submitting = true" x-cloak
  x-data="{
    rooms: @js($rooms),            // [{id, code, name, price, deposit, available}]
    selectedRoomId: @js($selectedRoomId ?? null),
    startDate: '',
    endDate: '',
    submitting: false,
    minStart: new Date(Date.now() + 4 * 86400000).toISOString().split('T')[0],   // today+4 (ADR-016)
    maxStart: new Date(Date.now() + 30 * 86400000).toISOString().split('T')[0],  // today+30 (FR-122)
    get room() { return this.rooms.find(r => r.id === this.selectedRoomId) ?? null; },
    get durasi() {   // hari — computed otomatis dari rentang tanggal
      if (!this.startDate || !this.endDate) return 0;
      return Math.max(0, Math.round((new Date(this.endDate) - new Date(this.startDate)) / 86400000));
    },
    get subtotal() { return this.room ? this.room.price * this.durasi : 0; },
    get total() { return this.subtotal + (this.room?.deposit ?? 0); },
    minEnd() {
      if (!this.startDate) return '';
      const d = new Date(this.startDate); d.setDate(d.getDate() + 1);
      return d.toISOString().split('T')[0];
    }
  }">

  <input type="hidden" name="kost_id" value="{{ $kost->id }}">
  <div class="grid gap-8 lg:grid-cols-[1fr_320px]">

    <div class="space-y-8">
      <!-- Pilih kamar -->
      <fieldset>
        <legend class="mb-3 block text-sm font-medium text-gray-700 dark:text-text-strong-dark">Pilih Kamar <span class="text-error-700" aria-label="required">*</span></legend>
        <div class="grid gap-3 sm:grid-cols-2">
          <template x-for="r in rooms" :key="r.id">
            <label class="flex cursor-pointer items-start gap-3 rounded-lg border p-4 transition-all hover:border-primary-400"
              :class="selectedRoomId === r.id ? 'border-primary-600 ring-2 ring-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-border-dark'">
              <input type="radio" name="room_id" :value="r.id" x-model="selectedRoomId" :disabled="!r.available"
                class="mt-1 h-5 w-5 text-primary-600 border-gray-300 focus:ring-2 focus:ring-primary-500">
              <div class="flex-1">
                <p class="text-sm font-semibold text-gray-900 dark:text-text-strong-dark" x-text="`${r.code} — ${r.name}`"></p>
                <p class="text-xs text-gray-500 dark:text-text-muted-dark"
                  x-text="r.available ? `Rp ${r.price.toLocaleString('id-ID')}/hari` : 'Tidak tersedia'"></p>
              </div>
            </label>
          </template>
        </div>
      </fieldset>

      <!-- Tanggal -->
      <fieldset class="grid gap-4 sm:grid-cols-2">
        <div>
          <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-text-strong-dark">Tanggal Mulai <span class="text-error-700" aria-label="required">*</span></label>
          <input type="date" id="start_date" name="start_date" x-model="startDate" :min="minStart" :max="maxStart" required
            class="mt-1 w-full px-4 py-3 border border-gray-300 dark:border-border-strong-dark rounded-md focus:ring-2 focus:ring-primary-500">
          <p class="mt-1 text-xs text-gray-500 dark:text-text-muted-dark">Paling cepat H+4 (waktu verifikasi pembayaran & dokumen — ADR-016)</p>
        </div>
        <div>
          <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-text-strong-dark">Tanggal Selesai <span class="text-error-700" aria-label="required">*</span></label>
          <input type="date" id="end_date" name="end_date" x-model="endDate" :min="minEnd()" required
            class="mt-1 w-full px-4 py-3 border border-gray-300 dark:border-border-strong-dark rounded-md focus:ring-2 focus:ring-primary-500">
        </div>
      </fieldset>
    </div>

    <!-- Ringkasan sticky -->
    <aside class="self-start rounded-lg border border-gray-200 dark:border-border-dark bg-surface-raised dark:bg-surface-raised-dark p-5 shadow-sm space-y-3 lg:sticky lg:top-24">
      <h3 class="text-sm font-semibold text-gray-900 dark:text-text-strong-dark">Ringkasan Sewa</h3>
      <p class="text-xs text-gray-500 dark:text-text-muted-dark" x-text="room ? `${room.code} — ${room.name}` : 'Belum pilih kamar'"></p>
      <dl class="space-y-1 text-sm">
        <div class="flex justify-between"><dt class="text-gray-500 dark:text-text-muted-dark">Durasi</dt><dd class="font-medium text-gray-900 dark:text-text-strong-dark" x-text="`${durasi} hari`"></dd></div>
        <div class="flex justify-between"><dt class="text-gray-500 dark:text-text-muted-dark">Harga kamar</dt><dd x-text="`Rp ${subtotal.toLocaleString('id-ID')}`"></dd></div>
        <div class="flex justify-between"><dt class="text-gray-500 dark:text-text-muted-dark">Deposit</dt><dd x-text="`Rp ${(room?.deposit ?? 0).toLocaleString('id-ID')}`"></dd></div>
        <div class="flex justify-between border-t border-gray-200 dark:border-border-dark pt-2"><dt class="font-semibold text-gray-900 dark:text-text-strong-dark">Total</dt><dd class="font-bold text-secondary-600" x-text="`Rp ${total.toLocaleString('id-ID')}`"></dd></div>
      </dl>
      <p class="text-xs text-gray-500 dark:text-text-muted-dark">Kode promo? Hubungi admin kost.</p>
      <button type="submit" :disabled="submitting || !room || durasi === 0"
        class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-primary-600 text-white font-semibold rounded-md hover:bg-primary-700 focus:ring-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
        <span x-show="submitting" class="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin" aria-hidden="true"></span>
        <span x-text="submitting ? 'Memproses...' : 'Buat Booking'"></span>
      </button>
    </aside>
  </div>
</form>
```

- **Kalkulasi realtime** via computed Alpine (`durasi`, `subtotal`, `total`) — angka client hanya estimasi; server menghitung ulang & menyimpan snapshot di Action `CreateRental` (ADR-010, `SELECT...FOR UPDATE` room lock)
- `minEnd()` memaksa end > start (min +1 hari); submit disabled saat kamar belum dipilih atau durasi 0
- Promo note: teks statis — mekanisme promo di luar MVP (jangan bangun UI-nya dulu)
- Mobile: ringkasan jatuh di bawah form (grid 1 kolom); sticky bar bottom opsional menyusul F4b

**A11y:** radio native + label wrapper (target ≥44px); tanggal pakai `min`/`max` dinamis (validasi browser + server Form Request); ringkasan semantik `dl/dt/dd`; error submit → pola §5.2 (inline + scroll ke error pertama).

**Dark pair:** card/ringkasan `bg-surface-raised dark:bg-surface-raised-dark`; kamar terpilih `dark:bg-primary-900/20`; input & border `-dark` pairs.

---

### 3.24 Document Upload (x-document-upload)

Checklist dokumen administrasi rental (KTP/selfie/kartu pelajar — §5.3, state Paid PAGE-008): drag-drop + file picker per dokumen, validasi tipe (jpeg/png/pdf) & ukuran (≤5MB), preview thumbnail, progress bar, hapus, status verifikasi. Implementasi: `components/document-upload.blade.php`.

```html
<div x-data="{
    docs: @js($documents),      // [{key:'ktp', label:'KTP', required:true, status:'approved', reason:null}]
    uploads: {},                // key → {name, size, type, preview}
    progress: {},               // key → 0..100
    dragOver: false,
    accept: ['image/jpeg', 'image/png', 'application/pdf'],
    validate(file) { return this.accept.includes(file.type) && file.size <= 5 * 1024 * 1024; },
    pick(key, e) { this.add(key, e.target.files[0]); e.target.value = ''; },
    add(key, file) {
      if (!file) return;
      if (!this.validate(file)) { Alpine.store('toast').show({ type: 'error', message: 'File harus JPEG/PNG/PDF ≤ 5MB' }); return; }
      this.uploads[key] = { name: file.name, size: file.size, type: file.type,
        preview: file.type.startsWith('image/') ? URL.createObjectURL(file) : null };
      this.progress[key] = 0;
      const t = setInterval(() => {                     // simulasi — produksi: XHR upload progress / fetch streaming
        this.progress[key] = Math.min(100, (this.progress[key] || 0) + 20);
        if (this.progress[key] >= 100) clearInterval(t);
      }, 120);
    },
    remove(key) { if (this.uploads[key]?.preview) URL.revokeObjectURL(this.uploads[key].preview); delete this.uploads[key]; delete this.progress[key]; }
  }" x-cloak class="space-y-4">

  <template x-for="d in docs" :key="d.key">
    <div class="rounded-lg border border-gray-200 dark:border-border-dark bg-surface-raised dark:bg-surface-raised-dark p-4">
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="text-sm font-semibold text-gray-900 dark:text-text-strong-dark" x-text="d.label"></p>
          <p class="text-xs text-gray-500 dark:text-text-muted-dark">
            <span x-text="d.required ? 'Wajib' : 'Opsional'"></span> · JPEG/PNG/PDF ≤ 5MB
          </p>
        </div>
        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold"
          :class="d.status === 'approved' ? 'bg-success/10 text-success-700' : d.status === 'rejected' ? 'bg-error/10 text-error-700' : 'bg-warning/10 text-warning-700'"
          x-text="d.status === 'approved' ? 'Disetujui' : d.status === 'rejected' ? 'Ditolak' : 'Menunggu'"></span>
      </div>

      <template x-if="uploads[d.key]">
        <div class="mt-3 flex items-center gap-3">
          <img x-show="uploads[d.key].preview" :src="uploads[d.key].preview" alt="" class="h-14 w-14 rounded-md border border-gray-200 dark:border-border-dark object-cover">
          <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-medium text-gray-900 dark:text-text-strong-dark" x-text="uploads[d.key].name"></p>
            <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-border-dark" role="progressbar" :aria-valuenow="progress[d.key] ?? 100" aria-valuemin="0" aria-valuemax="100">
              <div class="h-full bg-primary-600 transition-all" :style="`width: ${progress[d.key] ?? 100}%`"></div>
            </div>
          </div>
          <button type="button" @click="remove(d.key)" :aria-label="`Hapus ${d.label}`"
            class="p-2 text-gray-400 hover:text-error-700 rounded-md focus:ring-2 focus:ring-error-500">
            <!-- ikon trash: svg standard 24x24 -->
          </button>
        </div>
      </template>

      <template x-if="!uploads[d.key]">
        <label class="mt-3 flex cursor-pointer flex-col items-center justify-center rounded-md border-2 border-dashed p-4 text-center transition-all"
          :class="dragOver ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-300 dark:border-border-strong-dark hover:border-primary-400'"
          @dragover.prevent="dragOver = true" @dragleave.prevent="dragOver = false"
          @drop.prevent="dragOver = false; add(d.key, $event.dataTransfer.files[0])">
          <input type="file" :name="d.key" accept="image/jpeg,image/png,application/pdf" class="sr-only" @change="pick(d.key, $event)">
          <span class="text-sm font-semibold text-primary-600">Upload file</span>
          <span class="text-xs text-gray-500 dark:text-text-muted-dark">atau drag & drop</span>
        </label>
      </template>

      <p x-show="d.status === 'rejected'" class="mt-2 text-xs text-error-700">Ditolak: <span x-text="d.reason ?? 'file tidak terbaca'"></span> — silakan upload ulang.</p>
    </div>
  </template>
</div>
```

- Validasi client (tipe + ukuran) hanya UX — server wajib validasi ulang (Form Request); reject reason wajib tampil agar tenant tahu perbaikan (§1.2)
- Progress bar `bg-primary-600`; preview pakai `URL.createObjectURL` — revoke saat remove untuk hindari memory leak
- Status verifikasi badge: pending `bg-warning/10 text-warning-700`, approved `bg-success/10 text-success-700`, rejected `bg-error/10 text-error-700` (aturan §3.4)

**A11y:** drop zone = `<label>` + input `sr-only` (bisa keyboard, target besar); remove icon-only `aria-label`; progress `role="progressbar"` + `aria-valuenow`; badge teks `-700`.

**Dark pair:** card `bg-surface-raised dark:bg-surface-raised-dark`, border/track `-dark`; drop zone active `dark:bg-primary-900/20`.

---

### 3.25 Confirm Dialog (x-confirm-dialog)

Varian destructive modal (pola §3.18): overlay, ikon warning `text-error-700`, judul, deskripsi, tombol `variant="danger"` + cancel, focus trap + initial focus + restore, loading saat submit. Untuk aksi berisiko: cancel rental, reject dokumen/bukti bayar, hapus data. Implementasi: `components/confirm-dialog.blade.php`.

```html
<div x-data="{ open: @js($show), loading: false }" x-cloak>
  <button type="button" x-ref="trigger" @click="open = true"
    class="inline-flex items-center justify-center px-4 py-3 bg-error-600 text-white font-semibold rounded-md hover:bg-error-700 focus:ring-2 focus:ring-error-500 transition-all">
    Batalkan Sewa
  </button>

  <div x-show="open" role="dialog" aria-modal="true" aria-labelledby="confirm-title" aria-describedby="confirm-desc"
    @keydown.escape.window="open = false; $refs.trigger?.focus()"
    @keydown.tab.prevent="
      const f = $refs.panel.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex=\'-1\'])');
      const first = f[0], last = f[f.length - 1];
      if ($event.shiftKey && document.activeElement === first) last.focus();
      else if (!$event.shiftKey && document.activeElement === last) first.focus();
    "
    class="fixed inset-0 z-50 overflow-y-auto">
    <div class="fixed inset-0 bg-gray-900 bg-opacity-75 dark:bg-overlay-dark transition-opacity" @click="open = false; $refs.trigger?.focus()"></div>

    <div class="flex min-h-full items-center justify-center p-4">
      <div x-ref="panel" x-show="open"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        class="relative w-full max-w-sm transform overflow-hidden rounded-xl bg-white dark:bg-surface-raised-dark p-6 shadow-2xl transition-all">

        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-error-light">
          <svg class="h-6 w-6 text-error-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
          </svg>
        </div>

        <h3 id="confirm-title" class="mt-4 text-center text-lg font-semibold text-gray-900 dark:text-text-strong-dark">Batalkan Sewa?</h3>
        <p id="confirm-desc" class="mt-2 text-center text-sm text-gray-600 dark:text-text-dark">Rental akan dibatalkan dan tidak dapat dilanjutkan. Tindakan ini tidak bisa dibatalkan.</p>

        <form method="POST" :action="cancelUrl" class="mt-6 space-y-2" @submit="loading = true">
          <button type="submit" :disabled="loading" data-autofocus
            class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-error-600 text-white font-semibold rounded-md hover:bg-error-700 focus:ring-2 focus:ring-error-500 disabled:opacity-50 transition-all">
            <span x-show="loading" class="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin" aria-hidden="true"></span>
            <span x-text="loading ? 'Memproses...' : 'Ya, Batalkan'"></span>
          </button>
          <button type="button" @click="open = false; $refs.trigger?.focus()"
            class="w-full px-4 py-3 text-sm font-semibold text-gray-600 dark:text-text-dark rounded-md hover:bg-gray-50 dark:hover:bg-surface-muted-dark focus:ring-2 focus:ring-gray-300">Tidak, Kembali</button>
        </form>
      </div>
    </div>
  </div>
</div>
```

- Reuse pola §3.18: initial focus (`data-autofocus` pada tombol danger), focus trap (Tab cycle), restore focus ke `$refs.trigger` saat close
- Ikon warning + judul/deskripsi memakai `text-error-700`/`bg-error-light`; aksi `variant="danger"` (§3.1: solid `bg-error-600`)
- Form `POST` + `loading` state — cegah double-click; loading spinner reuse §3.9
- Variant non-destructive (info/confirm): ganti ikon `text-info-700`, tombol `primary`, warna `-700` sesuai peran

**A11y:** `role="dialog"` + `aria-modal` + `aria-labelledby` + `aria-describedby`; Esc tutup; fokus tidak lolos ke belakang overlay; backdrop klik = cancel.

**Dark pair:** panel `dark:bg-surface-raised-dark`, backdrop `dark:bg-overlay-dark`, teks `-dark` pairs; `bg-error-light` cukup kontras di kedua mode (hanya wadah ikon).

---

### 3.26 Page Header (x-page-header)

Header halaman konsisten: breadcrumb (§3.13) + judul + deskripsi opsional + area aksi kanan (tombol). Implementasi: `components/page-header.blade.php`.

```html
<header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
  <div class="min-w-0">
    <nav aria-label="Breadcrumb" class="mb-1">
      <ol class="flex flex-wrap items-center gap-1 text-sm">
        <li><a href="{{ route('dashboard') }}" class="text-gray-500 dark:text-text-muted-dark hover:text-primary-600">Dashboard</a></li>
        <li aria-hidden="true" class="text-gray-400 dark:text-text-muted-dark">/</li>
        <li><a href="{{ route('rentals.index') }}" class="text-gray-500 dark:text-text-muted-dark hover:text-primary-600">Rental</a></li>
        <li aria-hidden="true" class="text-gray-400 dark:text-text-muted-dark">/</li>
        <li aria-current="page" class="font-medium text-gray-900 dark:text-text-strong-dark">Detail Rental</li>
      </ol>
    </nav>
    <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-text-strong-dark">Detail Rental #SK-2026-000123</h1>
    <p class="mt-1 text-sm text-gray-500 dark:text-text-muted-dark">Kost Griya Asri — Kamar A2 · 1 Sep 2026 – 1 Okt 2026</p>
  </div>

  <div class="flex flex-wrap items-center gap-2 shrink-0">
    <x-button variant="outline" size="md">Cetak</x-button>
    <x-button variant="primary" size="md">Upload Bukti Bayar</x-button>
  </div>
</header>
```

- Judul `text-2xl font-bold` (heading halaman, §2.2); aksi sejajar bawah di sm+, turun ke baris sendiri di mobile
- Breadcrumb: `nav aria-label="Breadcrumb"` + `aria-current="page"` pada item aktif (§3.13) — separator `/` dekoratif `aria-hidden`
- Komponen Blade expose slot `{{ $slot }}` untuk judul & `<x-slot name="actions">` untuk area tombol — per halaman tinggal isi konten

**A11y:** struktur heading satu `h1` per halaman (tidak boleh lebih); link breadcrumb target ≥44px; aksi pakai `x-button` (§3.1) yang sudah handle focus ring & label.

**Dark pair:** semua teks `-dark` pairs; aksi memakai `x-button` yang sudah menangani dark sendiri.

---

### 3.27 Gallery Lightbox (x-gallery-lightbox)

Galeri foto kost di detail page (PAGE-003): thumbnail grid + main image; klik thumbnail → lightbox overlay ukuran penuh dengan prev/next arrow, counter `1 / N`, Esc untuk tutup. Implementasi: `components/gallery-lightbox.blade.php`.

```html
<div x-data="{
    images: @js($kost->images),              // [{url, alt}] dari controller — setiap foto WAJIB punya alt
    index: 0,
    open: false,
    lastTrigger: null,
    get current() { return this.images[this.index]; },
    openAt(i) {
      this.index = i;
      this.lastTrigger = document.activeElement;
      this.open = true;
      this.$nextTick(() => $refs.panel?.querySelector('[data-autofocus]')?.focus());
    },
    close() { this.open = false; this.lastTrigger?.focus(); },   // restore fokus ke thumbnail asal
    next() { this.index = (this.index + 1) % this.images.length; },
    prev() { this.index = (this.index - 1 + this.images.length) % this.images.length; }
  }" x-cloak>

  <!-- Main image -->
  <img :src="current.url" :alt="current.alt" loading="lazy"
    class="w-full aspect-[4/3] object-cover rounded-lg">

  <!-- Thumbnail grid -->
  <div class="mt-3 grid grid-cols-4 sm:grid-cols-5 gap-2" role="group" aria-label="Pilih foto galeri">
    <template x-for="(img, i) in images" :key="img.url">
      <button type="button" @click="openAt(i)"
        :aria-label="'Lihat foto ' + (i + 1) + ' dari ' + images.length"
        :aria-current="i === index ? 'true' : 'false'"
        class="rounded-md overflow-hidden ring-2 transition-all focus:outline-none focus-visible:ring-primary-500"
        :class="i === index ? 'ring-primary-600' : 'ring-transparent hover:ring-primary-300'">
        <img :src="img.url" :alt="img.alt" loading="lazy" class="w-full aspect-[4/3] object-cover">
      </button>
    </template>
  </div>

  <!-- Lightbox overlay -->
  <div x-show="open"
    @keydown.escape.window="close()"
    @keydown.arrow-right.window="next()"
    @keydown.arrow-left.window="prev()"
    @keydown.tab.prevent="
      const f = $refs.panel.querySelectorAll('button, [href], [tabindex]:not([tabindex=\"-1\"])');
      const first = f[0], last = f[f.length - 1];
      if ($event.shiftKey && document.activeElement === first) last.focus();
      else if (!$event.shiftKey && document.activeElement === last) first.focus();
    "
    class="fixed inset-0 z-50 bg-overlay dark:bg-overlay-dark"
    role="dialog" aria-modal="true" :aria-label="'Galeri foto ' + images.length + ' gambar'">
    <div class="flex h-full items-center justify-center p-4">
      <button type="button" @click="close()" data-autofocus
        aria-label="Tutup galeri"
        class="absolute top-4 right-4 p-2 rounded-md bg-white/10 text-white hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
      <button type="button" @click="prev()" aria-label="Foto sebelumnya"
        class="absolute left-4 p-2 rounded-md bg-white/10 text-white hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
      </button>
      <img :src="current.url" :alt="current.alt" class="max-h-[80vh] max-w-[85vw] object-contain rounded-xl">
      <button type="button" @click="next()" aria-label="Foto berikutnya"
        class="absolute right-4 p-2 rounded-md bg-white/10 text-white hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
      </button>
    </div>
    <p class="absolute bottom-6 inset-x-0 text-center text-sm font-medium text-white" aria-live="polite">
      <span x-text="(index + 1) + ' / ' + images.length"></span>
    </p>
  </div>
</div>
```

**A11y:**
- Overlay `role="dialog"` + `aria-modal="true"`; initial focus ke tombol tutup (`data-autofocus`), Esc close, focus trap manual (pola §3.5 — tanpa plugin tambahan), fokus kembali ke thumbnail asal saat close
- Tombol icon-only: `aria-label` di tutup/prev/next (wajib — §7.3); counter `aria-live="polite"` agar perubahan terumumkan
- Semua `<img>` `loading="lazy"`; thumbnail `button` + `aria-current="true"` pada aktif — bukan `<div>` yang diklik

**Dark pair:** overlay `bg-overlay dark:bg-overlay-dark`; kontrol di atas overlay tetap putih (`text-white`) — kontras dijamin oleh scrim gelap.

---

### 3.28 Map (x-map)

Peta lokasi kost (PAGE-003 detail, halaman admin) via **Leaflet** (sudah di package.json — tidak perlu dependency baru). Inisialisasi di lifecycle `init()` Alpine + `$nextTick` agar DOM siap; konten fallback (alamat teks + link Google Maps) **selalu dirender** sebelum JS jalan — progressive enhancement. Implementasi: `components/map.blade.php`.

```html
<div x-data="{
    lat: @js($kost->latitude),
    lng: @js($kost->longitude),
    name: @js($kost->name),
    init() {
      this.$nextTick(() => {
        const map = L.map(this.$refs.mapEl).setView([this.lat, this.lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 19,
          attribution: '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a>'
        }).addTo(map);
        L.marker([this.lat, this.lng]).addTo(map)
          .bindPopup('<strong>' + this.name + '</strong><br>' + this.$refs.address.textContent.trim())
          .openPopup();
      });
    }
  }" x-cloak>
  <div x-ref="mapEl" class="h-64 w-full rounded-lg overflow-hidden z-0" aria-hidden="true"></div>
  <p x-ref="address" class="mt-2 text-sm text-gray-600 dark:text-text-dark">
    Alamat: {{ $kost->full_address }}
    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($kost->full_address) }}"
      target="_blank" rel="noopener"
      class="ml-1 font-medium text-primary-600 hover:text-primary-700 focus-visible:ring-2 ring-primary-500 rounded">
      Buka di Google Maps
    </a>
  </p>
</div>
```

**A11y:**
- Peta (`<div>` Leaflet) dekoratif → `aria-hidden="true"`; informasi lokasi disampaikan lewat alamat teks + link fallback (setara — WCAG 1.1.1 alternatif teks)
- Link Google Maps: teks deskriptif + `target="_blank"` dengan `rel="noopener"`; target ≥44px (§6.3)
- Jika koordinat kosong (kost tanpa lat/lng — mis. draft): render hanya blok alamat, `x-ref="mapEl"` diberi `x-show="lat && lng"` — jangan inisialisasi map dengan koordinat 0,0

**Dark pair:** tiles Leaflet bawaan terang — di dark mode peta tetap terang (kontras foto tidak terpengaruh theme). **Catatan dark tiles opsional:** ganti `tileLayer` ke `https://tiles.stadiamaps.com/tiles/alidade_smooth_dark/{z}/{x}/{y}.png` bila ingin tiles gelap (butuh API key — keputusan final lewat ADR); `z-0` pada kontainer mencegah tile set menutupi elemen lain.

---

### 3.29 Rating (x-rating, x-rating-input)

Dua varian: **display** `<x-rating :value="4.5" :count="12">` — bintang statis fill/parsial + angka, dipakai di kost card (PAGE-001/002) & review; **input** `<x-rating-input>` — 5 tombol bintang untuk form ulasan (PAGE-013). Implementasi: `components/rating.blade.php` & `components/rating-input.blade.php`.

> **Pilihan token:** bintang aktif `text-warning-700` (#B45309, kontras 7.1:1 di atas putih — §7.1), BUKAN `text-warning` base (#F59E0B, kontras hanya 2.0:1 — gagal). Konsisten dengan aturan `text-*-700` utk teks & ikon kecil (§2.1).

**Display (parsial fill — overlay dipotong per persen):**

```html
<div x-data="{ value: 4.5, count: 12 }" class="flex items-center gap-2"
  role="img" :aria-label="value.toLocaleString('id-ID') + ' dari 5'">
  <span class="flex items-center gap-0.5 text-warning-700" aria-hidden="true">
    <template x-for="i in 5" :key="i">
      <span class="relative inline-block w-4 h-4">
        <!-- bintang dasar kosong -->
        <svg class="w-4 h-4 text-gray-300 dark:text-border-strong-dark" viewBox="0 0 20 20" fill="currentColor">
          <path d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.07 3.29a1 1 0 0 0 .95.69h3.46c.97 0 1.37 1.24.59 1.81l-2.8 2.03a1 1 0 0 0-.36 1.12l1.07 3.29c.3.92-.76 1.69-1.54 1.12l-2.8-2.03a1 1 0 0 0-1.18 0l-2.8 2.03c-.78.57-1.84-.2-1.54-1.12l1.07-3.29a1 1 0 0 0-.36-1.12L2.98 8.72c-.78-.57-.38-1.81.59-1.81h3.46a1 1 0 0 0 .95-.69l1.07-3.29Z"/>
        </svg>
        <!-- overlay isian: lebar = persen fill bintang ke-i -->
        <span class="absolute inset-0 overflow-hidden"
          :style="{ width: (Math.min(Math.max(value - (i - 1), 0), 1) * 100) + '%' }">
          <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
            <path d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.07 3.29a1 1 0 0 0 .95.69h3.46c.97 0 1.37 1.24.59 1.81l-2.8 2.03a1 1 0 0 0-.36 1.12l1.07 3.29c.3.92-.76 1.69-1.54 1.12l-2.8-2.03a1 1 0 0 0-1.18 0l-2.8 2.03c-.78.57-1.84-.2-1.54-1.12l1.07-3.29a1 1 0 0 0-.36-1.12L2.98 8.72c-.78-.57-.38-1.81.59-1.81h3.46a1 1 0 0 0 .95-.69l1.07-3.29Z"/>
          </svg>
        </span>
      </span>
    </template>
  </span>
  <span class="text-sm font-semibold text-gray-900 dark:text-text-strong-dark" x-text="value.toLocaleString('id-ID')"></span>
  <span class="text-sm text-gray-500 dark:text-text-muted-dark" x-text="'(' + count + ' ulasan)'"></span>
</div>
```

**Input (tombol + hover preview):**

```html
<div x-data="{ value: 0, hovered: 0 }" x-cloak @mouseleave="hovered = 0"
  role="group" :aria-label="value ? 'Rating dipilih: ' + value + ' bintang' : 'Rating belum dipilih'">
  <input type="hidden" name="rating" :value="value">
  <div class="flex items-center gap-0.5">
    <template x-for="i in 5" :key="i">
      <button type="button" @click="value = i" @mouseenter="hovered = i"
        :aria-label="'Berikan ' + i + ' bintang'"
        :aria-pressed="value === i ? 'true' : 'false'"
        class="p-1 rounded-md transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500"
        :class="(hovered || value) >= i ? 'text-warning-700' : 'text-gray-300 dark:text-border-strong-dark'">
        <svg class="w-6 h-6" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
          <path d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.07 3.29a1 1 0 0 0 .95.69h3.46c.97 0 1.37 1.24.59 1.81l-2.8 2.03a1 1 0 0 0-.36 1.12l1.07 3.29c.3.92-.76 1.69-1.54 1.12l-2.8-2.03a1 1 0 0 0-1.18 0l-2.8 2.03c-.78.57-1.84-.2-1.54-1.12l1.07-3.29a1 1 0 0 0-.36-1.12L2.98 8.72c-.78-.57-.38-1.81.59-1.81h3.46a1 1 0 0 0 .95-.69l1.07-3.29Z"/>
        </svg>
      </button>
    </template>
  </div>
  <p class="mt-1 text-sm text-gray-500 dark:text-text-muted-dark" aria-live="polite"
    x-text="value ? 'Anda memberi ' + value + ' bintang' : 'Klik bintang untuk memberi nilai'"></p>
</div>
```

**A11y:**
- Display: wrapper `role="img"` + `aria-label="4,5 dari 5"` (koma desimal `id-ID`); bintang dekoratif `aria-hidden="true"` — screen reader tidak membaca 5 ikon
- Input: tombol asli (bukan div/span) → tabbable + `aria-pressed` = pilihan saat ini; `aria-label` dinamis "Berikan N bintang"; nilai tersimpan di `input type="hidden"` → ikut submit form
- Fokus ring `focus-visible:ring-primary-500` di kedua varian; touch target tombol bintang ≥44px (§6.3) via padding

**Dark pair:** bintang kosong `dark:text-border-strong-dark`; teks angka/ulasan memakai `-dark` pairs.

---

### 3.30 Review Card (x-review-card)

Kartu ulasan tenant (PAGE-003 detail kost, PAGE-013 daftar ulasan): avatar, nama, rating kecil (pola §3.29), tanggal, teks ulasan, verified badge, balasan pemilik opsional, empty state. Implementasi: `components/review-card.blade.php`.

```blade
@php $star = 'M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.07 3.29a1 1 0 0 0 .95.69h3.46c.97 0 1.37 1.24.59 1.81l-2.8 2.03a1 1 0 0 0-.36 1.12l1.07 3.29c.3.92-.76 1.69-1.54 1.12l-2.8-2.03a1 1 0 0 0-1.18 0l-2.8 2.03c-.78.57-1.84-.2-1.54-1.12l1.07-3.29a1 1 0 0 0-.36-1.12L2.98 8.72c-.78-.57-.38-1.81.59-1.81h3.46a1 1 0 0 0 .95-.69l1.07-3.29Z'; @endphp

<article class="bg-surface-raised dark:bg-surface-raised-dark rounded-lg shadow-sm p-5">
  <div class="flex items-start gap-3">
    <img src="{{ $review->user->avatar_url ?? asset('img/avatar-default.svg') }}"
      alt="Foto profil {{ $review->user->name }}" class="w-10 h-10 rounded-full object-cover">
    <div class="flex-1 min-w-0">
      <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
        <span class="font-semibold text-gray-900 dark:text-text-strong-dark">{{ $review->user->name }}</span>
        <span class="inline-flex items-center gap-1 rounded-full bg-success-700 text-white text-xs font-medium px-2 py-0.5">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
          </svg>
          Verified
        </span>
      </div>
      <div class="mt-1 flex items-center gap-2">
        <!-- rating kecil: pola display §3.29, nilai integer 1-5 dari DB -->
        <span class="flex items-center gap-0.5 text-warning-700" role="img" aria-label="{{ $review->rating }} dari 5">
          @for($i = 1; $i <= 5; $i++)
            <svg class="w-3.5 h-3.5 {{ $i <= $review->rating ? '' : 'text-gray-300 dark:text-border-strong-dark' }}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="{{ $star }}"/></svg>
          @endfor
        </span>
        <time class="text-xs text-gray-500 dark:text-text-muted-dark" datetime="{{ $review->created_at->toDateString() }}">
          {{ $review->created_at->translatedFormat('d M Y') }}
        </time>
      </div>
      <p class="mt-2 text-sm text-gray-600 dark:text-text-dark">{{ $review->body }}</p>

      @if($review->owner_reply)
      <blockquote class="mt-3 border-l-4 border-primary-600 pl-3">
        <p class="text-sm font-medium text-gray-700 dark:text-text-dark">Balasan pemilik:</p>
        <p class="text-sm text-gray-500 dark:text-text-muted-dark">{{ $review->owner_reply }}</p>
      </blockquote>
      @endif
    </div>
  </div>
</article>
```

**Empty state (daftar ulasan kosong):**

```html
<x-empty-state icon="chat" title="Belum ada ulasan">
  <p class="text-sm text-gray-500 dark:text-text-muted-dark">Ulasan pertama muncul setelah tenant menyelesaikan rental.</p>
</x-empty-state>
```

**A11y:** struktur `<article>` (konten independen); avatar `alt` berisi nama; teks ulasan body text kontras `text-gray-600`/`-dark`; verified badge solid `bg-success-700 text-white` (5.4:1 — §7.1); balasan pemilik `blockquote` — makna semantik kutipan, bukan sekadar styling. Rating kecil pakai pola display §3.29 — bintang tampil + `aria-label`.

**Dark pair:** kartu `bg-surface-raised dark:bg-surface-raised-dark`; seluruh teks `-dark` pairs; badge solid tidak berubah (konsisten kedua theme).

---

### 3.31 Stat Card (x-stat-card)

Kartu ringkasan metrik di dashboard tenant/admin: ikon, label, nilai besar, delta naik/turun, link opsional. Implementasi: `components/stat-card.blade.php`.

> **Keputusan token:** kartu memakai `bg-surface-raised dark:bg-surface-raised-dark` (bukan `bg-surface`) — main area dashboard admin = `bg-surface dark:bg-surface-dark` (§4.2); kartu `bg-surface` akan sama persis dengan latar → tidak terlihat. `bg-surface-raised` konsisten dengan kartu lain (§3.3, §3.5, §3.10).

```html
<div class="bg-surface-raised dark:bg-surface-raised-dark rounded-lg shadow-sm p-5">
  <div class="flex items-center justify-between">
    <p class="text-sm font-medium text-gray-500 dark:text-text-muted-dark">{{ $label }}</p>
    <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-primary/10 text-primary-600" aria-hidden="true">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
    </span>
  </div>
  <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 dark:text-text-strong-dark">{{ $value }}</p>

  <p class="mt-2 flex items-center gap-1 text-sm font-medium"
    :class="@js($delta) >= 0 ? 'text-success-700' : 'text-error-700'">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"
      :class="@js($delta) >= 0 ? '' : 'rotate-180'">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
    </svg>
    <span x-text="'{{ $delta }}' >= 0 ? '+' + '{{ $delta }}' : '{{ $delta }}'"></span>
    <span class="text-gray-500 dark:text-text-muted-dark">vs bulan lalu</span>
  </p>

  @if(isset($link))
  <a href="{{ $link }}" class="mt-3 inline-block text-sm font-medium text-primary-600 hover:text-primary-700 focus-visible:ring-2 ring-primary-500 rounded">
    Lihat detail →
  </a>
  @endif
</div>
```

**A11y:** ikon dekoratif `aria-hidden="true"` (label teks sudah menyampaikan makna); delta `text-success-700`/`text-error-700` (keduanya ≥4.5:1 — §7.1); arah delta tidak hanya lewat warna — panah ↑/↓ (`rotate-180`) + tanda `+`/`-` agar terbaca tanpa warna; asosiasi nilai–label lewat struktur & posisi (label di atas nilai, pola dashboard umum).

**Dark pair:** kartu `dark:bg-surface-raised-dark`; teks nilai `dark:text-text-strong-dark`, label & satuan `dark:text-text-muted-dark`.

---

### 3.32 Mobile Filter Drawer (x-mobile-filter-drawer)

Drawer filter kanan untuk halaman pencarian kost (PAGE-002) di mobile: backdrop click/Esc close, focus trap, form harga + fasilitas, tombol "Terapkan Filter", chips ringkasan filter aktif, scroll lock body. Desktop tetap pakai `<x-filter-panel>` (§3.12) — drawer hanya `lg:hidden`. Implementasi: `components/mobile-filter-drawer.blade.php`.

```html
<div x-data="{
    open: false,
    priceMin: '',
    priceMax: '',
    facilities: [],
    get activeCount() { return this.facilities.length + ((this.priceMin || this.priceMax) ? 1 : 0); },
    get chips() {
      let c = [];
      if (this.priceMin || this.priceMax) c.push('Harga ' + (this.priceMin || '0') + 'K–' + (this.priceMax || '∞'));
      this.facilities.forEach(f => c.push(f));
      return c;
    },
    removeChip(i) { if (i === 0 && (this.priceMin || this.priceMax)) { this.priceMin = ''; this.priceMax = ''; } else this.facilities.splice(i - ((this.priceMin || this.priceMax) ? 1 : 0), 1); },
    init() { this.$watch('open', v => document.body.classList.toggle('overflow-hidden', v)); },
    close() { this.open = false; this.$refs.trigger?.focus(); }
  }" x-cloak class="lg:hidden">

  <!-- Trigger -->
  <button type="button" x-ref="trigger" @click="open = true; $nextTick(() => $refs.panel?.querySelector('[data-autofocus]')?.focus())"
    class="inline-flex items-center gap-2 px-4 py-2 border border-border-strong dark:border-border-strong-dark rounded-md text-sm font-medium text-gray-700 dark:text-text-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
    </svg>
    Filter
    <span x-show="activeCount" x-text="activeCount"
      class="ml-1 inline-flex items-center justify-center w-5 h-5 rounded-full bg-primary-600 text-white text-xs font-semibold"></span>
  </button>

  <!-- Backdrop -->
  <div x-show="open" @click="close()" x-transition.opacity
    class="fixed inset-0 z-40 bg-overlay dark:bg-overlay-dark lg:hidden"></div>

  <!-- Drawer -->
  <div x-show="open" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
    @keydown.escape.window="close()"
    @keydown.tab.prevent="
      const f = $refs.panel.querySelectorAll('button, input, [tabindex]:not([tabindex=\"-1\"])');
      const first = f[0], last = f[f.length - 1];
      if ($event.shiftKey && document.activeElement === first) last.focus();
      else if (!$event.shiftKey && document.activeElement === last) first.focus();
    "
    class="fixed inset-y-0 right-0 z-50 w-full max-w-sm bg-surface-raised dark:bg-surface-raised-dark shadow-2xl lg:hidden"
    role="dialog" aria-modal="true" aria-label="Filter pencarian">
    <div x-ref="panel" class="flex h-full flex-col">
      <!-- Header -->
      <div class="flex items-center justify-between px-5 py-4 border-b border-border dark:border-border-dark">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-text-strong-dark">Filter</h2>
        <button type="button" @click="close()" data-autofocus
          aria-label="Tutup filter"
          class="p-2 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-text-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Body: form -->
      <div class="flex-1 overflow-y-auto px-5 py-4 pb-[env(safe-area-inset-bottom)]">
        <fieldset class="space-y-4">
          <legend class="text-sm font-medium text-gray-900 dark:text-text-strong-dark">Rentang Harga (Rp/bln)</legend>
          <div class="grid grid-cols-2 gap-3">
            <label class="block text-sm">
              <span class="text-gray-600 dark:text-text-muted-dark">Min</span>
              <input type="number" inputmode="numeric" x-model="priceMin" min="0" placeholder="500.000"
                class="mt-1 w-full px-3 py-2 border border-border-strong dark:border-border-strong-dark rounded-md text-sm focus:ring-2 focus:ring-primary-500">
            </label>
            <label class="block text-sm">
              <span class="text-gray-600 dark:text-text-muted-dark">Max</span>
              <input type="number" inputmode="numeric" x-model="priceMax" min="0" placeholder="2.000.000"
                class="mt-1 w-full px-3 py-2 border border-border-strong dark:border-border-strong-dark rounded-md text-sm focus:ring-2 focus:ring-primary-500">
            </label>
          </div>
        </fieldset>

        <fieldset class="mt-5 space-y-2.5">
          <legend class="text-sm font-medium text-gray-900 dark:text-text-strong-dark">Fasilitas</legend>
          <template x-for="fac in ['AC', 'WiFi', 'Kamar Mandi Dalam', 'Laundry']" :key="fac">
            <label class="flex items-center gap-2.5 text-sm text-gray-700 dark:text-text-dark">
              <input type="checkbox" :value="fac" x-model="facilities"
                class="w-4 h-4 rounded border-border-strong text-primary-600 focus:ring-primary-500">
              <span x-text="fac"></span>
            </label>
          </template>
        </fieldset>

        <!-- Chips ringkasan filter aktif -->
        <div x-show="chips.length" class="mt-5" aria-label="Filter aktif">
          <p class="text-xs font-medium text-gray-500 dark:text-text-muted-dark">Filter aktif:</p>
          <div class="mt-2 flex flex-wrap gap-2">
            <template x-for="(chip, i) in chips" :key="chip">
              <button type="button" @click="removeChip(i)"
                class="inline-flex items-center gap-1 rounded-full bg-primary/10 text-primary-700 text-xs font-medium px-2.5 py-1 hover:bg-primary/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
                <span x-text="chip"></span>
                <span aria-hidden="true">&times;</span>
              </button>
            </template>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="px-5 py-4 border-t border-border dark:border-border-dark pb-[env(safe-area-inset-bottom)] bg-surface-raised dark:bg-surface-raised-dark">
        <x-button variant="primary" size="lg" class="w-full" @click="open = false">Terapkan Filter</x-button>
      </div>
    </div>
  </div>
</div>
```

**A11y:**
- Drawer `role="dialog"` + `aria-modal="true"`; initial focus tutup (`data-autofocus`), Esc close, focus trap manual (pola §3.5), fokus kembali ke trigger; backdrop click close
- Scroll lock body via `$watch('open')` → `document.body.classList.toggle('overflow-hidden')` — dicegah saat drawer tertutup (init cleanup: kondisi `close()` tidak meninggalkan class)
- Semua input berlabel (wrapper `<label>` granular — §7.4); checkbox `x-model` array terikat langsung ke fasilitas
- Chips ringkasan = tombol (dapat dihapus keyboard); `aria-live` tidak wajib — perubahan terlihat via tombol hapus & badge counter trigger

**Dark pair:** drawer `bg-surface-raised dark:bg-surface-raised-dark`; border `dark:border-border-dark`; label & teks `-dark` pairs; `pb-[env(safe-area-inset-bottom)]` pada body & footer agar konten tidak terpotong home-indicator iPhone.

### 3.33 Sticky Action Bar (x-sticky-action-bar)

Bar aksi bawah untuk halaman detail kost (PAGE-003) di mobile: harga mulai + tombol booking. Muncul setelah user scroll melewati hero (`scrollY > 400`) — harga & CTA tetap terjangkau tanpa scroll balik ke atas. Hanya `lg:hidden`; desktop memakai sidebar summary (komposisi dengan `x-booking-form` §3.23 — aside `lg:sticky lg:top-24`). Implementasi: `components/sticky-action-bar.blade.php`.

```html
<div x-data="{ visible: false }"
  @scroll.window="visible = window.scrollY > 400"
  x-cloak x-show="visible"
  x-transition:enter="transition ease-out duration-300"
  x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
  x-transition:leave="transition ease-in duration-200"
  x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
  class="fixed inset-x-0 bottom-0 z-40 border-t border-border dark:border-border-dark bg-surface-raised dark:bg-surface-raised-dark px-4 pt-3 pb-[env(safe-area-inset-bottom)] shadow-[0_-4px_12px_rgba(0,0,0,0.08)] lg:hidden">
  <div class="flex items-center gap-4">
    <div class="min-w-0">
      <p class="text-xs text-gray-500 dark:text-text-muted-dark">Mulai dari</p>
      <p class="text-lg font-bold text-secondary-600">Rp 850.000<span class="text-xs font-normal text-gray-500 dark:text-text-muted-dark">/bulan</span></p>
    </div>
    <x-button variant="primary" size="lg" class="flex-1"
      @click="document.querySelector('#booking-form')?.scrollIntoView({ behavior: 'smooth' })">Booking Sekarang</x-button>
  </div>
</div>
```

**A11y:**
- Konten harga tetap ada di halaman (bagian detail) — bar hanya akselerator; `x-show` mengeluarkan bar dari accessibility tree saat tidak tampil
- Muncul via scroll TIDAK memindahkan fokus (tanpa `autofocus`) — tidak mengganggu konteks baca; tombol punya label teks eksplisit
- Konten utama diberi padding bawah `pb-24 lg:pb-0` agar tidak tertutup bar saat tampil
- `pb-[env(safe-area-inset-bottom)]` menjaga tombol dari home-indicator iPhone

**Dark pair:** `bg-surface-raised dark:bg-surface-raised-dark` + `border-border dark:border-border-dark`; teks `-dark` pairs.

> **Upgrade path:** desktop → bar disembunyikan (`lg:hidden`) dan digantikan sidebar summary `x-booking-form` (§3.23) — ringkasan harga + pilihan kamar + total realtime; di mobile bar bisa dirampingkan (`text-sm`) setelah `scrollY > 800`.

---

### 3.34 Testimonial Slider (x-testimonial-slider)

Slider kartu testimoni untuk landing (PAGE-001): satu kartu aktif + tombol prev/next + dots navigasi. Auto-rotate 5 detik (opsional, mati saat hover/focus — aktivitas user tidak boleh diganggu). Implementasi: `components/testimonial-slider.blade.php`.

```html
<div x-data="{
    index: 0,
    paused: false,
    timer: null,
    items: [
      { name: 'Rina', kost: 'Kost Griya Asri — Malang', text: 'Proses booking cepat, kamar sesuai foto. Rekomendasi untuk anak rantau!' },
      { name: 'Budi', kost: 'Kost Putri Melati — Yogyakarta', text: 'Pembayaran QRIS praktis, admin responsif saat ada kendala.' },
      { name: 'Sari', kost: 'Kost Mahasiswa UGM — Sleman', text: 'Sistem verifikasi kost bikin tenang, tidak ada kost abal-abal.' }
    ],
    next() { this.index = (this.index + 1) % this.items.length; },
    prev() { this.index = (this.index - 1 + this.items.length) % this.items.length; },
    go(i) { this.index = i; },
    init() { this.timer = setInterval(() => { if (!this.paused) this.next(); }, 5000); },
    destroy() { clearInterval(this.timer); }
  }"
  @mouseenter="paused = true" @mouseleave="paused = false"
  @focusin="paused = true" @focusout="paused = false"
  class="relative mx-auto max-w-2xl">

  <!-- Region hidup: hanya kartu aktif yang dirender & dibacakan -->
  <div class="rounded-xl bg-surface-raised dark:bg-surface-raised-dark p-6 shadow-md sm:p-8" aria-live="polite">
    <svg class="mb-4 h-8 w-8 text-primary-600" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
      <path d="M9.983 3v7.391c0 5.704-3.731 9.57-8.983 10.609l-.995-2.151c2.432-.917 3.995-3.638 3.995-5.849H0V3h9.983zm14.017 0v7.391c0 5.704-3.748 9.571-9 10.609l-.996-2.151c2.433-.917 3.996-3.638 3.996-5.849h-3.983V3h9.983z"/>
    </svg>
    <blockquote class="text-lg leading-relaxed text-gray-700 dark:text-text-dark" x-text="items[index].text"></blockquote>
    <footer class="mt-5 flex items-center gap-3">
      <span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 font-semibold text-primary-700" aria-hidden="true" x-text="items[index].name.charAt(0)"></span>
      <div>
        <p class="text-sm font-semibold text-gray-900 dark:text-text-strong-dark" x-text="items[index].name"></p>
        <p class="text-xs text-gray-500 dark:text-text-muted-dark" x-text="items[index].kost"></p>
      </div>
    </footer>
  </div>

  <!-- Prev / Dots / Next -->
  <div class="mt-4 flex items-center justify-between gap-4">
    <button type="button" @click="prev()" aria-label="Testimoni sebelumnya"
      class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-border-strong dark:border-border-strong-dark text-gray-600 hover:bg-surface-muted focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 dark:text-text-dark dark:hover:bg-surface-muted-dark">
      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </button>
    <div class="flex items-center gap-2" role="group" aria-label="Pilih testimoni">
      <template x-for="(item, i) in items" :key="i">
        <button type="button" group @click="go(i)" :aria-current="i === index ? 'true' : null"
          :aria-label="`Tampilkan testimoni ${item.name}`"
          class="flex h-6 w-6 items-center justify-center rounded-full focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
          <span aria-hidden="true"
            :class="i === index ? 'w-6 bg-primary-600' : 'w-2.5 bg-gray-300 group-hover:bg-gray-400 dark:bg-border-dark dark:group-hover:bg-border-strong-dark'"
            class="block h-2.5 rounded-full transition-all duration-300"></span>
        </button>
      </template>
    </div>
    <button type="button" @click="next()" aria-label="Testimoni berikutnya"
      class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-border-strong dark:border-border-strong-dark text-gray-600 hover:bg-surface-muted focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 dark:text-text-dark dark:hover:bg-surface-muted-dark">
      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </button>
  </div>
</div>
```

**A11y:**
- Kartu aktif dalam region `aria-live="polite"` — perubahan konten diumumkan; auto-rotate di-pause saat hover/focus (juga `@focusin/@focusout` container) agar pengumuman tidak membombardir pembaca layar
- Tombol prev/next `aria-label` Bahasa Indonesia; dots = tombol reachable keyboard, hit area 24px (`h-6 w-6`) memenuhi WCAG 2.5.8; dot aktif `aria-current="true"`
- Fokus tetap di tombol yang ditekan (tidak dipindahkan paksa); hanya satu kartu dirender → tidak ada fokus terperangkap di konten tersembunyi
- Ikon quote dekoratif `aria-hidden="true"`; `destroy()` membersihkan interval saat komponen dilepas

**Dark pair:** kartu `bg-surface-raised dark:bg-surface-raised-dark`; dots inactive `bg-gray-300 dark:bg-border-dark`; teks `-dark` pairs.

---

### 3.35 Footer (x-footer)

Footer multi-kolom untuk halaman publik (PAGE-001/002): brand + tagline, tautan navigasi (`aria-label="Tautan footer"`), kontak; bottom bar hak cipta + tahun dinamis. **Keputusan warna:** footer TETAP gelap di kedua mode (`bg-gray-900`, dark: `bg-gray-950` + border) — konsisten dengan sidebar admin §4.2 (`bg-gray-900` di light & dark); landing berkonten terang di `bg-surface` butuh jangkar gelap di bawah agar hierarki halaman jelas, dan teks putih/gray-400 di atas gray-900 menjamin kontras ≥4.5:1 tanpa pasangan `-dark` tambahan. Token surface `-dark` (#1F2937) terlalu dekat dengan `surface-dark` (#111827) — footer akan tenggelam di dark mode; `bg-gray-950` (#030712) mempertahankan pemisahan. Implementasi: `components/footer.blade.php`.

```html
<footer class="border-t border-gray-800 bg-gray-900 text-gray-300 dark:border-border-dark dark:bg-gray-950 dark:text-text-dark">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-4">
      <!-- Brand + tagline -->
      <div class="lg:col-span-2">
        <p class="text-lg font-bold text-white">SewaKost</p>
        <p class="mt-3 max-w-sm text-sm leading-relaxed text-gray-400 dark:text-text-muted-dark">
          Temukan kost terverifikasi, bandingkan harga, dan booking langsung — semua transparan, semua aman.
        </p>
      </div>
      <!-- Navigasi -->
      <nav aria-label="Tautan footer">
        <h2 class="text-sm font-semibold text-white">Jelajahi</h2>
        <ul class="mt-4 space-y-2.5 text-sm">
          <li><a href="/kost" class="rounded text-gray-400 transition-colors hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-400">Cari Kost</a></li>
          <li><a href="/kost?filter=verified" class="rounded text-gray-400 transition-colors hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-400">Kost Terverifikasi</a></li>
          <li><a href="/faq" class="rounded text-gray-400 transition-colors hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-400">Bantuan</a></li>
        </ul>
      </nav>
      <!-- Kontak -->
      <div>
        <h2 class="text-sm font-semibold text-white">Kontak</h2>
        <ul class="mt-4 space-y-2.5 text-sm text-gray-400 dark:text-text-muted-dark">
          <li>WhatsApp: 0812-3456-7890</li>
          <li>Email: halo@sewakost.id</li>
          <li>Senin–Jumat 09.00–17.00 WIB</li>
        </ul>
      </div>
    </div>
  </div>
  <!-- Bottom bar -->
  <div class="border-t border-gray-800 dark:border-border-dark">
    <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-2 px-4 py-4 text-xs text-gray-400 dark:text-text-muted-dark sm:flex-row sm:px-6 lg:px-8">
      <p>&copy; {{ date('Y') }} SewaKost. Hak cipta dilindungi.</p>
      <p>Dibuat di Indonesia untuk anak rantau.</p>
    </div>
  </div>
</footer>
```

**A11y:**
- `nav aria-label="Tautan footer"` membedakan dari navigasi utama (dua landmark nav di satu halaman wajib `aria-label` unik)
- Judul kolom pakai `h2` (footer di luar `<main>`, hierarki tetap utuh); link punya visible focus ring (`focus-visible:ring-primary-400` — ring terang agar kontras di atas gray-900)
- Tahun hak cipta via Blade `{{ date('Y') }}` (server-rendered — tidak bergantung JS)

**Dark pair:** `bg-gray-900 dark:bg-gray-950` + `border-gray-800 dark:border-border-dark`; teks `text-gray-400 dark:text-text-muted-dark` (kontras ≥7:1 di kedua mode).

---

### 3.36 Search (x-search)

Pencarian dengan saran (suggestion) untuk header publik & dashboard: input + ikon, shortcut `/` untuk focus, tombol clear, dropdown saran (`role="listbox"`), Enter submit via form GET. Mobile: kolaps ke tombol ikon yang melebar saat diketuk. Implementasi: `components/search.blade.php`.

```html
<div x-data="{
    query: '',
    open: false,
    expanded: false,
    suggestions: ['Kost Griya Asri', 'Kost Putri Melati', 'Kost Mahasiswa UGM'],
    filtered() { return this.query ? this.suggestions.filter(s => s.toLowerCase().includes(this.query.toLowerCase())).slice(0, 5) : []; },
    select(s) { this.query = s; this.open = false; },
    toggle() { this.expanded = !this.expanded; if (this.expanded) this.$nextTick(() => this.$refs.input.focus()); }
  }"
  @keyup.window.slash.prevent="if (!['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName)) { window.innerWidth < 1024 ? toggle() : $refs.input.focus(); }"
  @keydown.escape.window="open = false; expanded = false"
  class="relative w-full max-w-md">

  <!-- Mobile: tombol pembuka -->
  <button type="button" @click="toggle()" aria-label="Buka pencarian"
    :class="expanded ? 'hidden' : 'flex lg:hidden'"
    class="w-full items-center gap-2 rounded-md border border-border-strong dark:border-border-strong-dark bg-surface-raised px-4 py-2.5 text-sm text-gray-500 dark:bg-surface-raised-dark dark:text-text-muted-dark">
    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    <span>Cari...</span>
  </button>

  <!-- Form pencarian -->
  <form action="/kost" method="GET" role="search" aria-label="Cari kost"
    :class="expanded ? 'block' : 'hidden lg:block'">
    <div class="relative">
      <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      <input x-ref="input" type="search" name="q" x-model="query" autocomplete="off"
        role="combobox" :aria-expanded="open" aria-controls="search-suggestions" aria-label="Cari"
        @focus="open = filtered().length > 0" @blur="setTimeout(() => open = false, 150)"
        placeholder="Cari kost atau lokasi..."
        class="w-full rounded-md border border-border-strong dark:border-border-strong-dark bg-surface-raised py-2.5 pl-10 pr-12 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-surface-raised-dark">
      <button type="button" x-show="query" @click="query = ''; $refs.input.focus()" aria-label="Bersihkan pencarian"
        class="absolute right-2.5 top-1/2 -translate-y-1/2 rounded-md p-1 text-gray-400 hover:text-gray-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 dark:hover:text-text-dark">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
      <kbd aria-hidden="true" :class="query ? 'hidden' : 'lg:block'"
        class="pointer-events-none absolute right-3 top-1/2 hidden -translate-y-1/2 rounded border border-border px-1.5 py-0.5 text-[10px] text-gray-400 dark:border-border-dark">/</kbd>
    </div>

    <!-- Dropdown saran -->
    <ul x-show="open && filtered().length" id="search-suggestions" role="listbox" aria-label="Saran pencarian"
      x-transition:enter="transition ease-out duration-150" x-transition:enter-start="-translate-y-1 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
      class="absolute z-30 mt-2 w-full overflow-hidden rounded-lg bg-surface-raised py-1 shadow-lg ring-1 ring-gray-900/5 dark:bg-surface-raised-dark dark:ring-border-dark">
      <template x-for="(s, i) in filtered()" :key="s">
        <li role="option" :id="`search-opt-${i}`" :aria-selected="false" tabindex="-1"
          @mousedown.prevent @click="select(s)"
          class="list-none cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-text-dark dark:hover:bg-surface-muted-dark"
          x-text="s"></li>
      </template>
    </ul>
  </form>
</div>
```

**A11y:**
- Input `role="combobox"` + `aria-expanded` + `aria-controls` mengarah ke listbox — `aria-expanded` wajib pada combobox, bukan wrapper div (pola ARIA APG); saran `tabindex="-1"` (bukan tab stop — hanya input yang difokus)
- Shortcut `/` di-skip saat fokus sudah di input/textarea/select — tidak membajak pengetikan; di desktop hanya focus, di mobile toggle expand; indikator kbd `/` dekoratif `aria-hidden`
- Clear button `aria-label="Bersihkan pencarian"`; tombol ikon mobile `aria-label="Buka pencarian"`; `@mousedown.prevent` mencegah blur menutup dropdown sebelum klik saran
- Enter submit via form GET native (`name="q"`); Esc tutup dropdown & kolaps mobile (`expanded = false` tidak berefek di desktop karena `lg:block`)

**Dark pair:** input & dropdown `bg-surface-raised dark:bg-surface-raised-dark`; border `-dark`; saran hover `dark:bg-surface-muted-dark`.

> **Upgrade path:** navigasi saran dengan panah (active-descendant) — tambahkan `aria-activedescendant` + index aktif bila kebutuhan keyboard lanjutan muncul (saat ini Enter submit & klik sudah cukup).

---

### 3.37 Tooltip (x-tooltip)

Wrapper tooltip Alpine: trigger hover/focus-visible → panel `role="tooltip"` terhubung `aria-describedby`. Posisi top/bottom via class toggle; keyboard reachable (focus memunculkan tooltip); Esc menutup; fallback teks via `<noscript>` saat JS nonaktif. Untuk info singkat pelengkap (ikon info, badge) — jangan untuk konten esensial (konten esensial wajib visible, lihat §1.2). Implementasi: `components/tooltip.blade.php`.

```html
<div x-data="{ show: false, pos: 'top' }" x-cloak class="relative inline-flex">
  <button type="button" aria-label="Info kapasitas kamar"
    @mouseenter="show = true" @mouseleave="show = false"
    @focus="show = true" @blur="show = false"
    @keydown.escape.window="show = false"
    :aria-describedby="show ? 'tt-1' : null"
    class="inline-flex h-6 w-6 items-center justify-center rounded-full text-gray-400 hover:text-gray-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 dark:hover:text-text-dark">
    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
  </button>

  <span x-show="show" role="tooltip" id="tt-1"
    :class="pos === 'top' ? 'bottom-full mb-2' : 'top-full mt-2'"
    x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    class="absolute left-1/2 z-50 max-w-xs -translate-x-1/2 rounded-md bg-gray-900 px-3 py-1.5 text-xs font-medium text-white shadow-lg pointer-events-none dark:bg-surface-dark">
    Maksimal 2 orang per kamar
  </span>

  <!-- Fallback non-JS -->
  <noscript>
    <span class="ml-1 text-xs text-gray-500 dark:text-text-muted-dark">(Maksimal 2 orang per kamar)</span>
  </noscript>
</div>
```

**A11y:**
- `role="tooltip"` + `id` terhubung via `aria-describedby` hanya saat tampil — pembaca layar mengumumkan sekali saat trigger difokus, tanpa kebisingan berulang
- Keyboard: `@focus/@blur` menampilkan/menutup (reachable tanpa mouse); `@keydown.escape.window` menutup; `pointer-events-none` pada panel mencegah area mati antara trigger & panel
- Ikon dekoratif `aria-hidden` + trigger `aria-label` deskriptif; fallback `<noscript>` menjamin info tidak hilang saat JS mati

**Dark pair:** panel `bg-gray-900 dark:bg-surface-dark` — di dark mode surface-dark (#111827) tetap lebih gelap dari card sehingga tooltip menonjol; teks putih `text-white`/`dark:text-text-dark`.

> **Upgrade path:** posisi left/right via `pos` ('top'|'bottom'|'left'|'right') — tambahkan mapping class; flip otomatis saat dekat viewport edge bila diperlukan.

---

### 3.38 Skeleton Extensions (x-skeleton)

Varian skeleton tambahan selain Skeleton Card §3.9 Loading States: **table rows**, **avatar circle**, **list item**. Satu `role="status"` + `aria-label="Memuat data..."` per grup (bukan per baris), inner `aria-hidden="true"` + `animate-pulse`. Token isian distandarkan ke `bg-surface-muted dark:bg-surface-muted-dark` (light gray-100 vs gray-200 di §3.9 — perbedaan minimal, prefer token semantik). Komponen: `components/skeleton.blade.php`.

```html
<!-- Table rows: 1 baris header + 3 baris data (ulangi per jumlah row) -->
<div role="status" aria-label="Memuat data tabel..." class="animate-pulse">
  <div class="overflow-hidden rounded-lg border border-border dark:border-border-dark bg-surface-raised dark:bg-surface-raised-dark" aria-hidden="true">
    <div class="grid grid-cols-4 gap-4 border-b border-border px-4 py-3 dark:border-border-dark">
      <div class="h-2.5 rounded bg-surface-muted dark:bg-surface-muted-dark"></div>
      <div class="h-2.5 rounded bg-surface-muted dark:bg-surface-muted-dark"></div>
      <div class="h-2.5 rounded bg-surface-muted dark:bg-surface-muted-dark"></div>
      <div class="h-2.5 rounded bg-surface-muted dark:bg-surface-muted-dark"></div>
    </div>
    <div class="grid grid-cols-4 gap-4 border-b border-border px-4 py-4 dark:border-border-dark">
      <div class="h-2.5 w-3/4 rounded bg-surface-muted dark:bg-surface-muted-dark"></div>
      <div class="h-2.5 w-1/2 rounded bg-surface-muted dark:bg-surface-muted-dark"></div>
      <div class="h-2.5 w-2/3 rounded bg-surface-muted dark:bg-surface-muted-dark"></div>
      <div class="h-2.5 w-1/3 rounded bg-surface-muted dark:bg-surface-muted-dark"></div>
    </div>
    <!-- baris 3 & 4: pola sama -->
  </div>
  <span class="sr-only">Mohon tunggu, data sedang dimuat...</span>
</div>

<!-- Avatar circle: profil / review -->
<div role="status" aria-label="Memuat profil..." class="flex animate-pulse items-center gap-3">
  <div class="h-12 w-12 rounded-full bg-surface-muted dark:bg-surface-muted-dark" aria-hidden="true"></div>
  <div class="flex-1 space-y-2" aria-hidden="true">
    <div class="h-3 w-32 rounded bg-surface-muted dark:bg-surface-muted-dark"></div>
    <div class="h-3 w-20 rounded bg-surface-muted dark:bg-surface-muted-dark"></div>
  </div>
  <span class="sr-only">Memuat profil...</span>
</div>

<!-- List item: daftar kamar / notifikasi (ulangi per item) -->
<div role="status" aria-label="Memuat daftar..." class="animate-pulse space-y-3">
  <div class="flex items-center justify-between rounded-lg bg-surface-raised p-4 dark:bg-surface-raised-dark" aria-hidden="true">
    <div class="space-y-2">
      <div class="h-3 w-40 rounded bg-surface-muted dark:bg-surface-muted-dark"></div>
      <div class="h-3 w-24 rounded bg-surface-muted dark:bg-surface-muted-dark"></div>
    </div>
    <div class="h-8 w-20 rounded-md bg-surface-muted dark:bg-surface-muted-dark"></div>
  </div>
  <span class="sr-only">Mohon tunggu, daftar sedang dimuat...</span>
</div>
```

**A11y:**
- Satu `role="status"` per grup skeleton — pembaca layar mengumumkan sekali ("Memuat data...") bukan per baris (hindari kebisingan)
- Seluruh baris dekoratif `aria-hidden="true"`; teks status sr-only sebagai fallback announcement; `animate-pulse` murni visual (tidak memengaruhi aksesibilitas)
- `aria-label` status spesifik konteks: "Memuat data tabel...", "Memuat profil...", "Memuat daftar..."

**Dark pair:** isian `bg-surface-muted dark:bg-surface-muted-dark`; container `bg-surface-raised dark:bg-surface-raised-dark` + border `-dark` — konsisten dengan pola §3.9.

---

## 4. Layout Patterns

Pola layout per role akses — struktur halaman, breakpoints, dan kontainer maksimum. Semua layout memakai token §2 dan komponen §3.

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

**Token (dark pair):**
```html
<body class="bg-surface text-text dark:bg-surface-dark dark:text-text-dark">
  <nav class="bg-surface-raised dark:bg-surface-raised-dark border-b border-border dark:border-border-dark">
  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
```

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

**Token (dark pair):**
```html
<aside class="w-64 bg-gray-900 dark:bg-surface-dark text-white ...">  <!-- sidebar tetap gelap di light & dark -->
<main class="ml-64 min-h-screen bg-surface dark:bg-surface-dark">
```

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

**Centered Container:** `min-h-screen flex items-center justify-center bg-surface dark:bg-surface-dark`
**Card:** `max-w-md w-full bg-white dark:bg-surface-raised-dark shadow-xl rounded-xl p-8`

---

## 5. Interaction Patterns

### 5.1 State Machine UI (Rental Lifecycle)

**Rental States:** Pending → Paid → Confirmed → Active → Completed (or Cancelled)

**UI Pattern per State:**

| State | Badge Color | Primary Action | Secondary Info |
|---|---|---|---|
| Pending | warning (yellow) | "Upload Bukti Bayar" | Payment deadline countdown (`<x-countdown>` §3.20) |
| Paid | info (blue) | "Upload Dokumen" | Document checklist progress (`<x-document-upload>` §3.24) |
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

### 5.1b State Machine UI (Kost Lifecycle)

**Kost States:** Draft → Pending Review → Approved/Rejected → Active — sesuai `kosts.status` ENUM di ARCHITECTURE.md DM-002: `draft`, `pending_review`, `approved`, `active`, `rejected` (ADR-009).

> **Tidak ada state `inactive`/`archived`/`blocked`.** Sesuai dokumen arsitektur, penghentian kost (tidak lagi tampil di marketplace) dilakukan via **soft delete** (`deleted_at`) — kost yang pernah `active` tidak boleh di-hard delete (COMP-002). Aksi ini destruktif → wajib `<x-confirm-dialog>` (§3.25). Jangan menambah state di luar enum database.

**UI Pattern per State** (template §5.1 rental):

| State | Badge (§3.4) | Aksi — Pemilik Kost (Admin) | Aksi — Super Admin | Transisi Next |
|---|---|---|---|---|
| Draft | gray (`bg-gray-100 text-gray-700`) | "Ajukan Review" (primary, disabled sampai data wajib lengkap: nama, alamat, kategori, minimal 1 room type — FR-017); "Edit" (outline) | — (tidak muncul di antrean review) | → `pending_review` |
| Pending Review | warning (`bg-warning/10 text-warning-700`) | "Lihat Status" (outline) | "Tinjau" (primary) → detail kost + Approve/Reject | → `approved` / `rejected` |
| Approved | success (`bg-success/10 text-success-700`) | "Publikasikan" (primary — `PublishKost`, set `published_at` FR-021); "Edit" (outline) | "Lihat" (outline) | → `active` |
| Active | primary (`bg-primary/10 text-primary-600`) | "Kelola" (primary → dashboard kost/rooms); "Hapus" (danger → soft delete via `<x-confirm-dialog>` §3.25) | "Lihat" (outline); nonaktif via soft delete juga di sini | — (terminal; berhenti saat soft delete) |
| Rejected | error (`bg-error/10 text-error-700`) | "Perbaiki & Kirim Ulang" (primary → kembali `draft`, `rejected_reason` di-clear); alasan via `<x-callout>` error (§3.17) | "Tinjau Ulang" (muncul setelah pemilik resubmit) | → `draft` |

**Transitions** (via Action class, ADR-009 — bukan `$model->update(['status' => ...])`):

```
draft ──SubmitKostForReview──▶ pending_review ──ApproveKost──▶ approved ──PublishKost──▶ active
                                     │                          (approved_by/approved_at,   (published_at, FR-021)
                                     └──RejectKost──▶ rejected ──revise──▶ draft
                                                      (rejected_reason wajib; clear saat revise)
```

- Action classes sesuai COMP-002: `SubmitKostForReview`, `ApproveKost`, `RejectKost`, `PublishKost`. Validasi data wajib sebelum submit (FR-017) dilakukan di `SubmitKostForReview`.
- **Hubungan dengan §5.1 rental:** rental hanya bisa dibuat untuk kost berstatus `active` (COMP-005, FR-022). Kost yang di-soft-delete berhenti menerima rental baru; rental berjalan (`pending` → `completed`) tidak terpengaruh — snapshot data tetap ada.
- **Referensi silang:** badge warna mengikuti §3.4 (semantic token + `text-*-700`); konfirmasi hapus/nonaktif via §3.25; stepper submission di bawah memakai varian horizontal `<x-stepper>` §3.11.

#### Submission Stepper (Pemilik menambah kost)

Flow 4 langkah: **[Detail Kost] → [Foto & Media] → [Fasilitas & Aturan] → [Review & Kirim]** — varian horizontal `<x-stepper>` (§3.11) dengan `aria-current="step"`, validasi per langkah, navigasi prev/next. Memenuhi janji §1.2 "Approval workflow visualization" + §1.1 "stepper horizontal dengan status saat ini highlighted".

```html
<div x-data="kostSubmissionStepper()" x-cloak>
  <!-- Indikator langkah -->
  <nav aria-label="Progress pengajuan kost" class="flex items-center justify-between">
    <template x-for="(step, i) in steps" :key="step.key">
      <div class="flex items-center" :class="i < steps.length - 1 ? 'flex-1' : ''">
        <div class="relative flex items-center justify-center">
          <div x-show="i < current"
            class="flex items-center justify-center w-10 h-10 rounded-full bg-success-700 text-white font-semibold text-sm">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div x-show="i === current"
            class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-600 text-white font-semibold text-sm"
            aria-current="step">
            <span x-text="i + 1"></span>
          </div>
          <div x-show="i > current"
            class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-200 text-gray-500 font-semibold text-sm">
            <span x-text="i + 1"></span>
          </div>
        </div>
        <div class="ml-3">
          <p class="text-sm font-medium" :class="i <= current ? 'text-gray-900' : 'text-gray-500'" x-text="step.label"></p>
        </div>
        <div x-show="i < steps.length - 1" aria-hidden="true" class="flex-1 h-0.5 mx-4"
          :class="i < current ? 'bg-success-700' : 'bg-gray-200'"></div>
      </div>
    </template>
  </nav>

  <!-- Panel langkah aktif -->
  <section class="mt-8 bg-white dark:bg-surface-raised-dark rounded-xl shadow-sm p-6" x-ref="panel" tabindex="-1">
    <!-- Isi per langkah: form field sesuai step.key (detail kost / media / fasilitas & aturan / ringkasan review) -->
    <p class="text-sm text-gray-600 dark:text-text-dark" x-text="steps[current].description"></p>
    <p class="mt-2 text-sm text-error-700" x-show="stepError" x-text="stepError" role="status"></p>
  </section>

  <!-- Navigasi -->
  <div class="mt-6 flex items-center justify-between">
    <button type="button" @click="go(current - 1)" :disabled="current === 0"
      class="inline-flex items-center px-4 py-3 text-sm font-semibold text-gray-600 dark:text-text-dark rounded-md border border-border dark:border-border-dark hover:bg-gray-50 dark:hover:bg-surface-muted-dark disabled:opacity-50 transition-all">
      Kembali
    </button>
    <button type="button" @click="next()" :disabled="!isCurrentStepValid()"
      class="inline-flex items-center px-4 py-3 bg-primary-600 text-white font-semibold rounded-md hover:bg-primary-700 disabled:opacity-50 focus:ring-2 focus:ring-primary-500 transition-all">
      <span x-text="current === steps.length - 1 ? 'Kirim Pengajuan' : 'Lanjut'"></span>
    </button>
  </div>
</div>
```

**Alpine state (pola `x-stepper` §3.11 + validasi per langkah):**

```html
<script>
  function kostSubmissionStepper() {
    return {
      steps: [
        { key: 'detail',     label: 'Detail Kost',      description: 'Nama, kategori, alamat, kontak, deskripsi.' },
        { key: 'media',      label: 'Foto & Media',     description: 'Thumbnail + galeri (jpeg/png/jpg, max 5MB per gambar).' },
        { key: 'facilities', label: 'Fasilitas & Aturan', description: 'List fasilitas & aturan — JSON array (ADR-013).' },
        { key: 'review',     label: 'Review & Kirim',   description: 'Ringkasan lengkap sebelum SubmitKostForReview.' }
      ],
      current: 0,
      stepError: null,
      go(i) { if (i >= 0 && i < this.steps.length) this.current = i; this.stepError = null; },
      next() {
        if (!this.isCurrentStepValid()) return;
        if (this.current === this.steps.length - 1) return this.submit();
        this.current++;
        this.stepError = null;
      },
      isCurrentStepValid() {
        // Validasi per langkah — contoh: langkah detail butuh nama + kategori + alamat.
        if (this.current === 0) return this.form.name && this.form.category_id && this.form.full_address;
        if (this.current === 1) return this.form.images.length > 0;        // minimal 1 foto
        if (this.current === 2) return this.form.facilities.length >= 1;   // minimal 1 fasilitas
        return true; // langkah review — backend memvalidasi ulang (FR-017)
      },
      submit() { this.$refs.panel.closest('form')?.submit(); }
    };
  }
</script>
```

**Aturan:**
- Indikator: done `bg-success-700` (pola solid badge §3.4), active `bg-primary-600` + `aria-current="step"`, upcoming muted (`bg-gray-200 text-gray-500`); connecting line done `bg-success-700`, else `bg-gray-200`, `aria-hidden="true"`
- Tombol "Lanjut" disabled sampai langkah valid (`isCurrentStepValid()`) — pencegahan dini; backend tetap validasi ulang (FR-017) saat "Kirim Pengajuan"
- Prev/next adalah `<button>` (bukan link) — keyboard Tab + Enter; saat langkah ganti, fokus pindah ke `x-ref="panel"` (heading panel `tabindex="-1"`)
- Submit → `draft` → `pending_review` (`SubmitKostForReview`); sukses → toast success (§3.10) + kost masuk antrean Super Admin

#### Rejection Flow

**Pemilik (kost `rejected`):** panel alasan penolakan tampil di atas form edit — `<x-callout>` error (§3.17) + tombol "Perbaiki & Kirim Ulang":

```html
<div class="flex items-start gap-3 p-4 bg-error-light border-l-4 border-error-700/30 rounded-lg">
  <svg class="w-5 h-5 text-error-700 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
  </svg>
  <div class="flex-1">
    <h4 class="text-sm font-semibold text-gray-900">Kost Ditolak</h4>
    <p class="mt-1 text-sm text-gray-600">{{ $kost->rejected_reason }}</p>
    <button type="submit" form="kost-edit-form"
      class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-error-700 hover:text-error-800 hover:underline">
      Perbaiki & Kirim Ulang
    </button>
  </div>
</div>
```

- Klik "Perbaiki & Kirim Ulang" → simpan perubahan → status kembali `draft`, `rejected_reason` di-clear (TASK-014) → "Ajukan Review" → `pending_review` lagi. Alur sama persis submission stepper di atas.
- **Super Admin (saat reject):** modal `<x-confirm-dialog>` (§3.25, varian danger) atau form inline di detail submission — textarea alasan wajib (validation: `rejected_reason` tidak boleh kosong, COMP-002) sebelum `RejectKost` dijalankan; alasan inilah yang ditampilkan ke pemilik.
- **Kasus approve:** setelah `ApproveKost`, tampilkan banner sukses (toast §3.10 atau success callout §3.17) + tombol "Publikasikan Sekarang" (primary) → `PublishKost` → status `active`, `published_at` di-set (FR-021), kost tampil di marketplace.

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
  <p class="mt-1 text-sm text-error-700">{{ $message }}</p>
@enderror
```

**Komponen terkait:** `<x-otp-input>` (§3.19) auto-submit saat 6 digit terisi; `<x-booking-form>` (§3.23) validasi tanggal + hitung total realtime sebelum submit; `<x-password-strength>` (§3.21) meter kekuatan password saat register/change password.

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

> **Komponen:** `<x-document-upload>` (§3.24) — checklist per dokumen, validasi tipe/ukuran (jpeg/png/pdf ≤5MB), preview, progress bar, status verifikasi. Upload bukti bayar memakai pola sama di `<x-qris-payment>` (§3.22).

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

**Usage (API tunggal — konsisten dengan §3.0 & §3.1):**
```blade
<x-button variant="primary" size="lg">
  Submit
</x-button>
<x-button variant="danger" size="sm" :disabled="$loading">
  Reject
</x-button>

<x-status-badge :status="$rental->status" />

<x-kost-card :kost="$kost" />
```

> **Larangan:** komponen reusable TIDAK dikonsumsi via `@include('components.*')` — selalu `<x-nama-komponen>`. `@include` hanya untuk partial spesifik-halaman (`admin.kosts.partials.*`).

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
  timeout: null,

  show({ type = 'info', message = '', duration = 5000 }) {
    this.type = type
    this.message = message
    this.visible = true
    clearTimeout(this.timeout)
    this.timeout = setTimeout(() => this.visible = false, duration)
  },

  hide() {
    clearTimeout(this.timeout)
    this.visible = false
  }
})

Alpine.start()
```

**Usage (satu-satunya API toast — konsisten dengan §3.10):**
```javascript
Alpine.store('toast').show({
  type: 'success',
  message: 'Payment berhasil diverifikasi — silakan upload dokumen administrasi',
  duration: 5000
});
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
> **Catatan migrasi:** Config `theme.extend` di atas adalah registrasi Tailwind v3 (legacy); target final — token diregistrasi via `@theme` §2.1 (v4). Blok di atas dipertahankan sebagai referensi nilai; jangan ditambah.

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
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

**Purge Unused CSS:**
Tailwind automatically purges in production based on `content` config.

**Image Optimization:**
Gunakan package optimasi gambar Laravel (mis. `spatie/laravel-image-optimizer`).
> **WAJIB:** Dependency baru (termasuk package optimasi gambar) harus melalui ADR dan didaftarkan di `ARCHITECTURE.md` §3.1 sesuai hard rule AGENTS.md — TIDAK boleh langsung dipasang tanpa keputusan arsitektur.

---

## 10. Changelog

| Version | Date | Changes | Author |
|---|---|---|---|
| v1.0.0 | 2026-08-16 | Initial design system creation. Extracted from reference design (soft gradients, card-based layout). Design tokens, 17 component categories, layout patterns, responsive guidelines, accessibility WCAG 2.1 AA targets, animation specs, Blade+Alpine.js implementation notes. Total 35+ components documented. | OpenCode |
| v1.0.1 | 2026-08-18 | Tambah §3.18 Verify Email Modal (Popup): modal on-demand untuk user belum verified (FR-006), dipicu flash `verify_email_prompt` dari middleware `verified`, reuse pattern §3.5, CTA → `verification.notice`. 18 component categories. | OpenCode |
| v1.1.0 | 2026-08-18 | Revisi Fase 1 (struktur & slop cleanup): hapus AI-slop, tambah `## 4. Layout Patterns` induk, unifikasi API toast ke store-based `Alpine.store('toast').show({type, message, duration})` (§3.10 = §9.2), konsistensi metadata, fix argumen `@vite`, catatan ADR wajib untuk dependency §9.5, konversi raw hex → token semantik §2.1 (error/warning/success/gradient/ring), Full Page Loader generik via slot. | OpenCode |
| v1.2.0 | 2026-08-19 | Revisi Fase 3 (a11y canonical + format spec + sinkronisasi token §3): tambah §3.0 (API tunggal `<x-*>`, Component Inventory 18 section + domain draft F4, tabel kontras pasangan token), definisi `x-button` variants/sizes/disabled/loading, badge status → `text-*-700` + solid `bg-success-700`, tombol solid `bg-error-600`, callout `text-*-700` + border `border-*-700/30`, `*` marker `aria-label="required"`, a11y tabs (tablist/aria-selected/keyboard + roving tabindex), accordion (aria-expanded/controls/region), modal (initial focus → focus trap → restore + data-autofocus), toast (role="status" + aria-live), skeleton (role="status" + aria-hidden), icon-only buttons aria-label (modal close, hamburger, toast close, pagination arrows, password toggle + aria-pressed, stepper ±), dropdown menu (aria-expanded/haspopup/menu/menuitem), dark pair pada contoh struktural (§3.3, §3.5, §3.10, §3.18, §4), aturan required marker `aria-label="required"` → §3.2 (selaras §7.4). | OpenCode |
| v1.3.0 | 2026-08-19 | Fase 4a: komponen transaksi & auth — §3.19 `x-otp-input` (6 digit, auto-advance/backspace/paste, auto-submit, `inputmode`+`autocomplete="one-time-code"`), §3.20 `x-countdown` (hh:mm:ss, `aria-live` per menit + jam offscreen, `text-error-700` <60s, expired callback), §3.21 `x-password-strength` (merge toggle §3.2 + meter 4 level `-700`), §3.22 `x-qris-payment` (QRIS + merchant + payment ref, tab bank BCA/BNI/Mandiri, copy-to-clipboard + fallback + toast, deadline, instruksi bukti), §3.23 `x-booking-form` (radio kamar, min today+4/max today+30 ADR-016, computed durasi/subtotal/total realtime, ringkasan sticky, submit loading), §3.24 `x-document-upload` (drag-drop + picker, jpeg/png/pdf ≤5MB, preview + revoke, progress `bg-primary-600`, remove, status verifikasi), §3.25 `x-confirm-dialog` (destructive, pola §3.18 initial focus + trap + restore, loading), §3.26 `x-page-header` (breadcrumb + judul + aksi). Inventory §3.0: 8 komponen `spec` (26 section). Referensi singkat §5.1/5.2/5.3. | OpenCode |
| v1.4.0 | 2026-08-19 | Fase 4b-1: komponen visual — §3.27 `x-gallery-lightbox` (thumbnail grid + main image, lightbox: focus trap + Esc + arrow prev/next, counter `1 / N`, `aria-label="Tutup galeri"`, `aria-current` thumbnail, `loading="lazy"`), §3.28 `x-map` (Leaflet via `init()`+`$nextTick`, marker + popup, fallback alamat teks + link Google Maps always-on — progressive enhancement, catatan dark tiles opsional), §3.29 `x-rating` + `x-rating-input` (bintang fill/parsial via overlay clip, `text-warning-700` ikon aktif — keputusan token dicatat, `aria-label="4,5 dari 5"`, tombol `aria-pressed`, hover preview, label "Berikan N bintang"), §3.30 `x-review-card` (avatar, verified badge `bg-success-700 text-white`, rating kecil reuse pola §3.29, balasan pemilik `blockquote border-l-4 border-primary-600`, empty state via `x-empty-state`), §3.31 `x-stat-card` (ikon + label + nilai besar, delta naik/turun `text-success-700`/`text-error-700` + panah, `bg-surface-raised dark:bg-surface-raised-dark rounded-lg shadow-sm`, link opsional), §3.32 `x-mobile-filter-drawer` (drawer kanan mobile, backdrop click/Esc close, focus trap, harga min-max + fasilitas checkbox, `x-button` primary "Terapkan Filter", chips ringkasan filter aktif, body scroll lock, `pb-[env(safe-area-inset-bottom)]`). Inventory §3.0: F4b split — visual → `spec` (32 section), domain form → F4b-2 `draft`. | OpenCode |
| v1.5.0 | 2026-08-19 | Fase 4b-2: komponen utility — §3.33 `x-sticky-action-bar` (bar bawah mobile harga + CTA booking, muncul `scrollY > 400`, `pb-[env(safe-area-inset-bottom)]`, `lg:hidden`, upgrade path sidebar §3.23), §3.34 `x-testimonial-slider` (kartu aktif + prev/next `aria-label` + dots `aria-current` 24px hit area, `aria-live="polite"`, pause hover/focus, auto-rotate 5s + `destroy()`, ikon quote `aria-hidden`), §3.35 `x-footer` (brand + nav `aria-label="Tautan footer"` + kontak, bottom bar `{{ date('Y') }}`, keputusan `bg-gray-900` konsisten §4.2 — jelaskan di section), §3.36 `x-search` (combobox `aria-expanded` + `aria-controls` + listbox saran, shortcut `/` guard input, clear `aria-label`, Enter GET submit, mobile expandable), §3.37 `x-tooltip` (hover/focus trigger → panel `role="tooltip"` + `aria-describedby`, posisi top/bottom, Esc, fallback `<noscript>`), §3.38 skeleton extensions (table rows, avatar circle, list item; `role="status"` + `aria-label` per grup, token `bg-surface-muted dark:bg-surface-muted-dark`, konsisten §3.9). Inventory §3.0: F4b-2 utility → `spec` (38 section); domain form dipindah ke F4b-3 `draft`. | OpenCode |
| v1.6.0 | 2026-08-19 | Fase 5: state machine kost lengkap — §5.1b tabel state→badge→action per role (Pemilik Kost/Super Admin; state sesuai DM-002: draft/pending_review/approved/active/rejected, tanpa inactive/archived/blocked — nonaktif via soft delete + `<x-confirm-dialog>` §3.25), submission stepper 4 langkah ([Detail Kost] → [Foto & Media] → [Fasilitas & Aturan] → [Review & Kirim]; varian `x-stepper` §3.11: done `bg-success-700`, active `bg-primary-600` + `aria-current="step"`, upcoming muted, validasi per langkah + prev/next), rejection flow (`<x-callout>` error + "Perbaiki & Kirim Ulang" → `draft` clear rejected_reason; approve → banner sukses + "Publikasikan Sekarang" → `active`). Inventory §3.0: Progress/Timeline tambah varian submission. | OpenCode |
| v1.6.1 | 2026-08-20 | Fix referensi silang PAGE (PAGE-008→010, PAGE-002→003, PAGE-004→003, drawer→PAGE-002); sinkron count PAGES 57 halaman. | OpenCode |
---

**END OF DESIGN.md**

> **Next Steps:**
> 1. Reference this document when implementing Blade views (TASK-002+)
> 2. Create Blade components in `resources/views/components/` following §9.1
> 3. Check PAGES.md for specific page layouts and component compositions
> 4. Run accessibility audit with axe DevTools after implementation
> 5. Test responsive behavior on real devices (mobile, tablet, desktop)
