<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>SewaKost - Platform Marketplace Kost Terpercaya</title>
    <meta name="description" content="Temukan kost impian Anda dengan sistem booking online, verifikasi dokumen, dan pembayaran QRIS yang aman.">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- No-FOUC theme bootstrap -->
    <script>
        (function() {
            const stored = localStorage.getItem('theme');
            const dark = stored ? stored === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.classList.toggle('dark', dark);
        })();
    </script>
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-text-strong dark:text-text-strong-dark antialiased bg-surface dark:bg-surface-dark">
    <!-- Skip to main content -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-primary-600 focus:text-white focus:rounded-lg focus:shadow-lg transition-all">
        Skip to main content
    </a>
    
    <!-- Public Navigation -->
    <x-nav-public />
    
    <!-- Main Content -->
    <main id="main-content">
        <!-- Hero Section -->
        <section class="relative bg-gradient-to-br from-primary-50 via-white to-primary-50 dark:from-surface-dark dark:via-surface-raised-dark dark:to-surface-dark min-h-screen flex items-center">
            <div class="absolute top-4 right-4 z-10">
                <x-theme-toggle />
            </div>
            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center w-full">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 dark:text-text-strong-dark mb-6 leading-tight">
                    Temukan Kost Impian Anda
                </h1>
                <p class="text-lg sm:text-xl text-gray-600 dark:text-text-dark mb-8 max-w-3xl mx-auto leading-relaxed">
                    Platform marketplace kost terpercaya dengan sistem booking online, verifikasi dokumen, dan pembayaran QRIS yang aman.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('marketplace.index') }}" class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-semibold rounded-lg text-white bg-primary-600 hover:bg-primary-700 shadow-md hover:shadow-lg transition-all focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                        Cari Kost
                        <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-3 border-2 border-primary-600 text-base font-semibold rounded-lg text-primary-600 dark:text-primary-400 bg-white dark:bg-surface-dark hover:bg-primary-50 dark:hover:bg-surface-raised-dark transition-all focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                        Daftar Sekarang
                    </a>
                </div>
            </div>
        </section>
        
        <!-- Featured Kosts Section -->
        <section class="py-16 bg-white dark:bg-surface-dark">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-text-strong-dark mb-4">Kost Terpopuler</h2>
                    <p class="text-gray-600 dark:text-text-dark">Kost dengan rating tertinggi dari penyewa kami</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($featuredKosts as $kost)
                        <x-kost-card :kost="$kost" />
                    @empty
                        <div class="col-span-full text-center py-12 text-gray-500 dark:text-text-muted-dark">
                            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-text-muted-dark mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <p>Belum ada kost tersedia</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
        
        <!-- How It Works Section -->
        <section class="py-16 bg-gray-50 dark:bg-surface-raised-dark">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-text-strong-dark mb-4">Cara Kerja</h2>
                    <p class="text-gray-600 dark:text-text-dark">Proses booking kost yang mudah dan transparan</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Step 1 -->
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-text-strong-dark mb-2">1. Cari & Pilih Kost</h3>
                        <p class="text-gray-600 dark:text-text-dark leading-relaxed">Browse kost berdasarkan lokasi, harga, dan fasilitas yang Anda inginkan</p>
                    </div>
                    
                    <!-- Step 2 -->
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-text-strong-dark mb-2">2. Booking & Bayar</h3>
                        <p class="text-gray-600 dark:text-text-dark leading-relaxed">Pilih durasi rental, upload dokumen, dan bayar via QRIS atau transfer bank</p>
                    </div>
                    
                    <!-- Step 3 -->
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-text-strong-dark mb-2">3. Verifikasi & Mulai</h3>
                        <p class="text-gray-600 dark:text-text-dark leading-relaxed">Admin verifikasi pembayaran & dokumen, lalu Anda siap menghuni kost</p>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Testimonials Section -->
        <section class="py-16 bg-white dark:bg-surface-dark">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-text-strong-dark mb-4">Testimoni Penyewa</h2>
                    <p class="text-gray-600 dark:text-text-dark">Pengalaman nyata dari pengguna SewaKost</p>
                </div>
                
                <x-testimonial-slider :testimonials="$testimonials" />
            </div>
        </section>
    </main>
    
    <!-- Footer -->
    <x-footer />
</body>
</html>
