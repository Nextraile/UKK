<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Jelajahi Kost - SewaKost</title>
    <meta name="description" content="Temukan kost yang sesuai dengan kebutuhan Anda. Cari berdasarkan lokasi, harga, kategori, dan rating.">
    
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
        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Jelajahi Kost</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Temukan kost yang sesuai dengan kebutuhan Anda</p>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        <!-- Search Bar (FR-051) -->
        <div class="mb-6">
            <form method="GET" action="{{ route('marketplace.index') }}" class="flex gap-2">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Cari kost berdasarkan nama atau lokasi..."
                    aria-label="Cari kost berdasarkan nama atau lokasi"
                    class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-800 dark:text-gray-100"
                >
                <button 
                    type="submit"
                    class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors"
                >
                    Cari
                </button>
                @if(request('search'))
                    <a 
                        href="{{ route('marketplace.index') }}"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- Mobile Filter Drawer --}}
        <x-mobile-filter-drawer :categories="$allCategories" />

        <!-- Filter + Grid Layout -->
        <div class="lg:grid lg:grid-cols-4 lg:gap-8">
            <!-- Filter Sidebar (Desktop only) -->
            <aside class="hidden lg:block lg:col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 lg:sticky lg:top-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Filter</h3>
                    
                    <form method="GET" action="{{ route('marketplace.index') }}" class="space-y-6">
                        <!-- Preserve search param -->
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        
                        <!-- Price Range Filter (FR-052) -->
                        <fieldset>
                            <legend class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Harga (per bulan)</legend>
                            <div class="space-y-2">
                                <input 
                                    type="number" 
                                    name="price_min" 
                                    value="{{ request('price_min') }}"
                                    placeholder="Min"
                                    min="0"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-gray-100"
                                >
                                <input 
                                    type="number" 
                                    name="price_max" 
                                    value="{{ request('price_max') }}"
                                    placeholder="Max"
                                    min="0"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-gray-100"
                                >
                            </div>
                        </fieldset>
                        
                        <!-- Category Filter (FR-053) -->
                        <fieldset>
                            <legend class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Kategori</legend>
                            <div class="space-y-2">
                                @foreach($allCategories as $category)
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input 
                                            type="checkbox" 
                                            name="categories[]" 
                                            value="{{ $category->id }}"
                                            {{ in_array($category->id, request('categories', [])) ? 'checked' : '' }}
                                            class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
                                        >
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $category->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                        
                        <!-- Rating Filter (FR-054) -->
                        <fieldset>
                            <legend class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Rating Minimum</legend>
                            <select 
                                name="rating_min"
                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-gray-100"
                            >
                                <option value="">Semua Rating</option>
                                @for($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}" {{ request('rating_min') == $i ? 'selected' : '' }}>
                                        {{ $i }} ★ ke atas
                                    </option>
                                @endfor
                            </select>
                        </fieldset>
                        
                        <!-- Apply Filter Button -->
                        <button 
                            type="submit"
                            class="w-full px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors"
                        >
                            Terapkan Filter
                        </button>
                        
                        <!-- Reset Filter (if any filter active) -->
                        @if(request('price_min') || request('price_max') || request('categories') || request('rating_min'))
                            <a 
                                href="{{ route('marketplace.index', ['search' => request('search')]) }}"
                                class="block w-full text-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                            >
                                Reset Filter
                            </a>
                        @endif
                    </form>
                </div>
            </aside>
            
            <!-- Main Content (Grid + Pagination) -->
            <main class="lg:col-span-3">
                <!-- Result count (if search active) -->
                @if(request('search'))
                    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400" aria-live="polite">
                        Menampilkan {{ $kosts->total() }} kost untuk <strong>"{{ request('search') }}"</strong>
                    </div>
                @endif

                @if ($kosts->isNotEmpty())
                    <!-- Grid adjusted for sidebar layout -->
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        @foreach ($kosts as $kost)
                            <x-kost-card :kost="$kost" />
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-8">
                        {{ $kosts->links() }}
                    </div>
                @else
                    <!-- Empty state -->
                    <div class="flex flex-col items-center justify-center px-4 py-12 text-center">
                        <svg class="w-16 h-16 mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Tidak ada kost ditemukan</h3>
                        <p class="mt-1 max-w-sm text-sm text-gray-600 dark:text-gray-400">
                            Coba ubah filter atau kata kunci pencarian Anda.
                        </p>
                    </div>
                @endif
            </main>
        </div>
    </main>
    
    <!-- Footer -->
    <x-footer />
</body>
</html>