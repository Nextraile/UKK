# SewaKost

Platform marketplace kost modern berbasis Laravel 13 untuk memudahkan penyewaan kamar kost di Indonesia.

## Tentang Aplikasi

SewaKost adalah aplikasi web marketplace yang menghubungkan pemilik kost dengan pencari kost. Aplikasi ini menyediakan sistem manajemen lengkap mulai dari listing kost, booking kamar, verifikasi pembayaran, hingga review.

## Fitur Utama

### Untuk Pencari Kost (Tenant)
- Browse dan filter kost berdasarkan kota, harga, kategori, dan rating
- Booking kamar dengan sistem slot availability
- Upload bukti pembayaran (manual verification)
- Upload dokumen persyaratan (KTP, foto selfie)
- Review dan rating kost setelah selesai sewa
- Dashboard tracking status rental

### Untuk Pemilik Kost (Admin)
- Tambah dan kelola listing kost
- Manajemen room types, harga, dan availability
- Verifikasi pembayaran dan dokumen tenant
- Monitoring rental dan revenue
- Melihat review kost

### Untuk Super Admin
- Manajemen akun admin (pemilik kost)
- Review dan approve/reject submission kost baru
- Manajemen kategori kost
- Overview sistem

## Tech Stack

- **Framework:** Laravel 13 (PHP 8.5)
- **Database:** MySQL 8.0
- **Cache/Queue:** Redis 7
- **Frontend:** Blade Templates + Alpine.js + Tailwind CSS 4.0
- **Authentication:** Laravel Breeze (customized with OTP email verification)
- **Development:** Docker Sail
- **Testing:** PHPUnit
- **Code Quality:** PHPStan (Level 5), Laravel Pint

## Arsitektur

- **Modular Monolith** - Domain logic terorganisir di `app/Domain/`
- **Session-based Auth** - Tidak ada API routes, pure web application
- **State Machines** - Action classes untuk lifecycle transitions (Kost, Rental)
- **Manual Payment** - QRIS + manual verification (no automated payment gateway)
- **OTP Verification** - Email-based 6-digit OTP (15min expiry)

## Quick Start

### Prerequisites
- Docker Desktop (untuk Windows/Mac)
- WSL2 (untuk Windows)

### Installation

```bash
# Clone repository
git clone <repository-url>
cd SewaKost

# Copy environment file
cp .env.example .env

# Install dependencies & start containers
./vendor/bin/sail up -d

# Generate app key
./vendor/bin/sail artisan key:generate

# Download seed images (one-time, ~5 seconds)
./vendor/bin/sail artisan seed:download-images

# Run migrations & seed database
./vendor/bin/sail artisan migrate:fresh --seed

# Access application
# http://localhost
```

### Default Accounts (After Seeding)

**Super Admin:**
- Email: `superadmin@sewakost.local`
- Password: `password`

**Admin/Pemilik Kost:**
- Email: `admin1@sewakost.local` (admin2, admin3, dll.)
- Password: `password`

**Tenant:**
- Browse as guest atau register akun baru

## Development

### Running Tests

```bash
# Run all tests (737 tests)
wsl ./vendor/bin/sail artisan test

# Run specific test suite
wsl ./vendor/bin/sail artisan test --filter=MarketplaceTest
```

### Code Quality

```bash
# PHPStan (static analysis)
wsl ./vendor/bin/sail php vendor/bin/phpstan analyse

# Laravel Pint (code style)
wsl ./vendor/bin/sail pint
```

### Database Seeding Presets

Edit `.env` untuk mengubah volume data seeding:

```env
# Pilihan: minimal, development, large (default), stress
SEED_PRESET=large

# Atau override manual
SEED_KOSTS=300
SEED_RENTALS=500
SEED_ADMINS=30
```

**Presets:**
- `minimal` - 10 kosts, 10 rentals (~5 detik)
- `development` - 100 kosts, 100 rentals (~10 detik)
- `large` - 300 kosts, 500 rentals (~20 detik) ← **Default**
- `stress` - 1000 kosts, 2000 rentals (~60 detik)

## Project Structure

```
app/
├── Domain/              # Domain logic (Kost, RoomInventory, Rental, Review, Identity)
├── Http/
│   ├── Controllers/     # Organized by role (Admin/, Tenant/, SuperAdmin/)
│   ├── Middleware/
│   └── Requests/        # Form validation
├── Console/Commands/    # Artisan commands
└── View/Components/     # Blade components

database/
├── factories/           # 17 factories for testing
├── migrations/          # Schema definitions
└── seeders/            # Performance-optimized seeders

resources/views/
├── admin/              # Admin dashboard & kost management
├── tenant/             # Tenant rental management
├── super-admin/        # Super admin panel
├── marketplace/        # Public marketplace
└── components/         # Reusable UI components

tests/Feature/          # 737 integration tests
```

## Key Concepts

### Kost Workflow
1. **Draft** - Admin membuat listing kost
2. **Pending Review** - Submit untuk review super admin
3. **Approved** - Disetujui super admin
4. **Active** - Admin publish ke marketplace
5. **Rejected** - Ditolak dengan alasan

### Rental Workflow
1. **Payment Pending** - Tenant booking, upload bukti pembayaran
2. **Paid** - Admin verifikasi pembayaran
3. **Documents Pending** - Tenant upload dokumen (KTP, selfie)
4. **Confirmed** - Admin verifikasi dokumen
5. **Active** - Sewa dimulai (automated via scheduler)
6. **Completed** - Sewa selesai (automated via scheduler)

### Automated Jobs
- `CancelOverdueRentals` - Cancel rentals dengan pembayaran overdue (3 hari)
- `ActivateRentals` - Aktifkan rentals yang tanggal mulai sudah tiba
- `CompleteRentals` - Complete rentals yang tanggal selesai sudah lewat
