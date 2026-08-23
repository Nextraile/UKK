<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SewaKost') }} - Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200">
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
        <main class="flex-1">
            <!-- Header -->
            <header class="bg-white border-b border-gray-200">
                <div class="px-8 py-4">
                    <h1 class="text-2xl font-bold text-gray-800">@yield('title', 'Dashboard')</h1>
                </div>
            </header>

            <!-- Flash Messages -->
            @if(session('success'))
                <div class="mx-8 mt-4 p-4 bg-success-50 border border-success-200 text-success-800 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mx-8 mt-4 p-4 bg-error-50 border border-error-200 text-error-800 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Content -->
            <div class="p-8">
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
