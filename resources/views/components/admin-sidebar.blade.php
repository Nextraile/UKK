{{-- Admin Sidebar Component --}}
{{-- Usage: <x-admin-sidebar /> --}}
{{-- Extracted from layouts/admin.blade.php --}}

<aside x-show="sidebarOpen"
       x-cloak
       :class="sidebarOpen ? 'w-64' : 'w-0'"
       class="bg-white dark:bg-surface-raised-dark border-r border-gray-200 dark:border-border-dark transition-all duration-300 ease-in-out overflow-hidden 
              fixed lg:sticky top-[4rem] h-[calc(100vh-4rem)] z-40 lg:z-10 flex flex-col">
    <!-- Sidebar Header with collapse button -->
    <div class="p-4 flex items-center justify-between border-b border-gray-200 dark:border-border-dark flex-shrink-0">
        <h2 class="text-lg font-bold text-gray-800 dark:text-text-strong-dark">Admin Panel</h2>
        
        <!-- Desktop collapse toggle -->
        <button @click="sidebarOpen = !sidebarOpen"
                type="button"
                class="hidden lg:block p-2 rounded-lg text-gray-600 dark:text-text-dark hover:bg-gray-100 dark:hover:bg-surface-muted-dark focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors"
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
                class="lg:hidden p-2 rounded-lg text-gray-600 dark:text-text-dark hover:bg-gray-100 dark:hover:bg-surface-muted-dark focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors"
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
               class="flex items-center gap-3 px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.kosts.*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/20 dark:text-primary-400' : 'text-gray-700 dark:text-text-dark hover:bg-gray-100 dark:hover:bg-surface-muted-dark' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Kelola Kost
            </a>
            
            <a href="{{ route('admin.rentals.index') }}" 
               class="flex items-center gap-3 px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.rentals.*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/20 dark:text-primary-400' : 'text-gray-700 dark:text-text-dark hover:bg-gray-100 dark:hover:bg-surface-muted-dark' }}">
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
               class="flex items-center gap-3 px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('super-admin.kost-submissions.*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/20 dark:text-primary-400' : 'text-gray-700 dark:text-text-dark hover:bg-gray-100 dark:hover:bg-surface-muted-dark' }}">
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
               class="flex items-center gap-3 px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('super-admin.admins.*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/20 dark:text-primary-400' : 'text-gray-700 dark:text-text-dark hover:bg-gray-100 dark:hover:bg-surface-muted-dark' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                Admin Management
            </a>
            
            <a href="{{ route('super-admin.categories.index') }}" 
               class="flex items-center gap-3 px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('super-admin.categories.*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/20 dark:text-primary-400' : 'text-gray-700 dark:text-text-dark hover:bg-gray-100 dark:hover:bg-surface-muted-dark' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                Categories
            </a>
        @endif
    </nav>
</aside>
