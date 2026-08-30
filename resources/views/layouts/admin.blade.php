<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SewaKost') }} - Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50" x-data="{ sidebarOpen: window.innerWidth >= 1024 }">
    <!-- Skip to main content link (WCAG 2.4.1) -->
    <a href="#main-content" 
       class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 
              focus:px-4 focus:py-2 focus:bg-primary-600 focus:text-white focus:rounded-lg 
              focus:shadow-lg focus:ring-2 focus:ring-primary-500 focus:ring-offset-2
              dark:bg-primary-700 dark:ring-offset-gray-900">
        Lewati ke konten utama
    </a>
    
    <!-- Unified Navbar -->
    <x-nav-public />
    
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
        
        <!-- Collapsible Sidebar -->
        <aside x-show="sidebarOpen"
               x-cloak
               :class="sidebarOpen ? 'w-64' : 'w-0'"
               class="bg-white border-r border-gray-200 transition-all duration-300 ease-in-out overflow-hidden 
                      fixed lg:sticky top-[4rem] h-[calc(100vh-4rem)] z-40 lg:z-10 flex flex-col">
            <!-- Sidebar Header with collapse button -->
            <div class="p-4 flex items-center justify-between border-b border-gray-200 flex-shrink-0">
                <h2 class="text-lg font-bold text-gray-800">Admin Panel</h2>
                
                <!-- Desktop collapse toggle -->
                <button @click="sidebarOpen = !sidebarOpen"
                        type="button"
                        class="hidden lg:block p-2 rounded-lg text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors"
                        :aria-label="sidebarOpen ? 'Collapse sidebar' : 'Expand sidebar'">
                    <svg x-show="sidebarOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    <svg x-show="!sidebarOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                
                <!-- Mobile close button -->
                <button @click="sidebarOpen = false"
                        type="button"
                        class="lg:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors"
                        aria-label="Close sidebar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <!-- Navigation Links -->
            <nav class="px-2 py-4 space-y-1 flex-1 overflow-y-auto">
                @if(auth()->user()->isAdmin())
                    {{-- Admin Menu --}}
                    <a href="{{ route('admin.kosts.index') }}" 
                       class="flex items-center gap-3 px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.kosts.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Kelola Kost
                    </a>
                    
                    <a href="{{ route('admin.rentals.index') }}" 
                       class="flex items-center gap-3 px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.rentals.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        <span>Rental Management</span>
                        @if(isset($pendingVerifications) && $pendingVerifications > 0)
                            <span class="ml-auto px-2 py-0.5 bg-warning-700 text-white text-xs font-semibold rounded-full"
                                  aria-label="{{ $pendingVerifications }} pending verifications">
                                {{ $pendingVerifications }}
                            </span>
                        @endif
                    </a>
                @endif
                
                @if(auth()->user()->isSuperAdmin())
                    {{-- Super Admin Menu --}}
                    <a href="{{ route('super-admin.kost-submissions.index') }}" 
                       class="flex items-center gap-3 px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('super-admin.kost-submissions.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Kost Submissions</span>
                        @if(isset($pendingSubmissions) && $pendingSubmissions > 0)
                            <span class="ml-auto px-2 py-0.5 bg-warning-700 text-white text-xs font-semibold rounded-full"
                                  aria-label="{{ $pendingSubmissions }} pending submissions">
                                {{ $pendingSubmissions }}
                            </span>
                        @endif
                    </a>
                    
                    <a href="{{ route('super-admin.admins.index') }}" 
                       class="flex items-center gap-3 px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('super-admin.admins.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        Admin Management
                    </a>
                    
                    <a href="{{ route('super-admin.categories.index') }}" 
                       class="flex items-center gap-3 px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('super-admin.categories.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        Categories
                    </a>
                @endif
            </nav>
        </aside>

        <!-- Main Content -->
        <main id="main-content" class="flex-1 transition-all duration-300 ease-in-out">
            <!-- Header with sidebar toggle -->
            <header class="bg-white border-b border-gray-200 sticky top-[4rem] z-20">
                <div class="px-4 sm:px-6 lg:px-8 py-4 flex items-center gap-4">
                    <!-- Sidebar toggle button -->
                    <button @click="sidebarOpen = !sidebarOpen"
                            type="button"
                            class="p-2 rounded-lg text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors"
                            :aria-label="sidebarOpen ? 'Close sidebar' : 'Open sidebar'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-800">@yield('title', 'Dashboard')</h1>
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
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
