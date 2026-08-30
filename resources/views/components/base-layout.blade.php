@props([
    'title' => config('app.name', 'SewaKost'),
    'variant' => 'full-width', // 'centered-card' | 'admin-sidebar' | 'full-width'
    'pageTitle' => null,
    'hideNavigation' => false,
    'hideFooter' => false,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- No-FOUC theme bootstrap (runs pre-paint, before Vite/JS) -->
    <script>
        (function() {
            const stored = localStorage.getItem('theme');
            const dark = stored ? stored === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.classList.toggle('dark', dark);
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans antialiased {{ $variant === 'centered-card' ? 'bg-surface dark:bg-surface-dark' : 'bg-gray-50 dark:bg-surface-dark' }}"
      @if($variant === 'admin-sidebar') x-data="{ sidebarOpen: window.innerWidth >= 1024 }" @endif>
    
    <!-- Skip to main content link (WCAG 2.4.1) -->
    <a href="#main-content" 
       class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 
              focus:px-4 focus:py-2 focus:bg-primary-600 focus:text-white focus:rounded-lg 
              focus:shadow-lg focus:ring-2 focus:ring-primary-500 focus:ring-offset-2
              dark:bg-primary-700 dark:ring-offset-gray-900 transition-all">
        Lewati ke konten utama
    </a>

    @if($variant === 'centered-card')
        {{-- Centered Card Variant (Auth pages) --}}
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 relative">
            <!-- Theme toggle (top right) -->
            <div class="absolute top-4 right-4">
                <x-theme-toggle />
            </div>

            <!-- Logo -->
            <div class="mb-6 text-center">
                <a href="/">
                    <div class="flex items-center justify-center gap-2">
                        <x-application-logo class="w-10 h-10 fill-current text-primary-600 dark:text-primary-500" />
                        <span class="text-2xl font-bold text-text-strong dark:text-text-strong-dark">SewaKost</span>
                    </div>
                </a>
            </div>

            <!-- Card content -->
            <main id="main-content" class="w-full sm:max-w-md px-6 py-8 bg-surface-raised dark:bg-surface-raised-dark shadow-xl rounded-xl overflow-hidden">
                {{ $slot }}
            </main>
        </div>

    @elseif($variant === 'admin-sidebar')
        {{-- Admin Sidebar Variant --}}
        
        <!-- Unified Navbar -->
        @if(!$hideNavigation)
            <x-nav-public />
        @endif

        <div class="flex min-h-[calc(100vh-4rem)]">
            <!-- Mobile backdrop overlay -->
            <div x-show="sidebarOpen && window.innerWidth < 1024" 
                 @click="sidebarOpen = false"
                 x-cloak
                 class="fixed inset-0 bg-gray-900/50 z-30 lg:hidden"
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"></div>

            <!-- Sidebar -->
            <x-admin-sidebar />

            <!-- Main Content -->
            <main id="main-content" class="flex-1 transition-all duration-300 ease-in-out">
                <!-- Header with sidebar toggle -->
                <header class="bg-white dark:bg-surface-raised-dark border-b border-gray-200 dark:border-border-dark sticky top-[4rem] z-20">
                    <div class="px-4 sm:px-6 lg:px-8 py-4 flex items-center gap-4">
                        <!-- Sidebar toggle button -->
                        <button @click="sidebarOpen = !sidebarOpen"
                                type="button"
                                class="p-2 rounded-lg text-gray-600 dark:text-text-dark hover:bg-gray-100 dark:hover:bg-surface-muted-dark focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors"
                                :aria-label="sidebarOpen ? 'Close sidebar' : 'Open sidebar'">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-text-strong-dark">
                            {{ $pageTitle ?? 'Dashboard' }}
                        </h1>
                    </div>
                </header>

                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="mx-4 sm:mx-6 lg:mx-8 mt-4">
                        <x-alert-banner variant="success" dismissible>
                            {{ session('success') }}
                        </x-alert-banner>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mx-4 sm:mx-6 lg:mx-8 mt-4">
                        <x-alert-banner variant="error" dismissible>
                            {{ session('error') }}
                        </x-alert-banner>
                    </div>
                @endif

                <!-- Content -->
                <div class="p-4 sm:p-6 lg:p-8">
                    {{ $slot }}
                </div>
            </main>
        </div>

    @else
        {{-- Full-Width Variant (Default - Marketplace, Tenant, Profile) --}}
        
        <!-- Navbar -->
        @if(!$hideNavigation)
            <x-nav-public />
        @endif

        <div class="min-h-screen bg-surface dark:bg-surface-dark">
            <!-- Optional page header -->
            @isset($header)
                <header class="bg-surface-raised dark:bg-surface-raised-dark shadow-sm">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Flash Messages -->
            @if(session('success'))
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                    <x-alert-banner variant="success" dismissible>
                        {{ session('success') }}
                    </x-alert-banner>
                </div>
            @endif

            @if(session('error'))
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                    <x-alert-banner variant="error" dismissible>
                        {{ session('error') }}
                    </x-alert-banner>
                </div>
            @endif

            <!-- Main Content -->
            <main id="main-content">
                {{ $slot }}
            </main>
        </div>

        <!-- Footer -->
        @if(!$hideFooter)
            <x-footer />
        @endif
    @endif

    @stack('scripts')
</body>
</html>
