<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SewaKost') }} - Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50" x-data="{ sidebarOpen: false }">
    <!-- Skip to main content link (WCAG 2.4.1) -->
    <a href="#main-content" 
       class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 
              focus:px-4 focus:py-2 focus:bg-primary-600 focus:text-white focus:rounded-lg 
              focus:shadow-lg focus:ring-2 focus:ring-primary-500 focus:ring-offset-2
              dark:bg-primary-700 dark:ring-offset-gray-900">
        Lewati ke konten utama
    </a>
    
    <div class="min-h-screen flex">
        <!-- Mobile overlay -->
        <div x-show="sidebarOpen" 
             @click="sidebarOpen = false"
             x-cloak
             class="fixed inset-0 bg-gray-900/50 lg:hidden z-40"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>
        
        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed lg:static inset-y-0 left-0 w-64 bg-white border-r border-gray-200 transform transition-transform duration-300 ease-in-out lg:translate-x-0 z-50">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-800">Admin Panel</h2>
            </div>
            <nav class="px-4 space-y-1">
                <a href="{{ route('admin.kosts.index') }}" 
                   class="block px-4 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.kosts.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-100' }}">
                    Kelola Kost
                </a>
                
                {{-- TODO: Uncomment when COMP-001 (User roles) implemented --}}
                {{-- @if(auth()->user()->hasRole('super_admin'))
                    <a href="{{ route('super-admin.kost-submissions.index') }}" 
                       class="block px-4 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('super-admin.kost-submissions.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        Kost Submissions
                    </a>
                @endif --}}
                
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left block px-4 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-100">
                        Logout
                    </button>
                </form>
            </nav>
        </aside>

        <!-- Main Content -->
        <main id="main-content" class="flex-1">
            <!-- Header -->
            <header class="bg-white border-b border-gray-200">
                <div class="px-4 sm:px-8 py-4 flex items-center gap-4">
                    <!-- Mobile menu button -->
                    <button @click="sidebarOpen = !sidebarOpen"
                            type="button"
                            class="lg:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500"
                            aria-label="Toggle sidebar">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-800">@yield('title', 'Dashboard')</h1>
                </div>
            </header>

            <!-- Flash Messages -->
            @if(session('success'))
                <div class="mx-4 sm:mx-8 mt-4">
                    <x-alert-banner variant="success" dismissible>
                        {{ session('success') }}
                    </x-alert-banner>
                </div>
            @endif

            @if(session('error'))
                <div class="mx-4 sm:mx-8 mt-4">
                    <x-alert-banner variant="error" dismissible>
                        {{ session('error') }}
                    </x-alert-banner>
                </div>
            @endif

            <!-- Content -->
            <div class="p-4 sm:p-8">
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
