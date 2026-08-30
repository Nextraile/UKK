{{-- Public Navigation Bar Component --}}
{{-- Usage: <x-nav-public /> --}}
{{-- Specification: DESIGN.md §3.6 (line 1200-1287) --}}

<nav x-data="{ mobileMenuOpen: false }" 
  class="bg-white dark:bg-surface-raised-dark border-b border-gray-200 dark:border-border-dark sticky top-0 z-50 shadow-sm">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center h-16">
      <!-- Logo -->
      <div class="flex-shrink-0">
        <a href="/" class="flex items-center gap-2">
          <span class="text-xl font-bold text-gray-900 dark:text-text-strong-dark">SewaKost</span>
        </a>
      </div>
      
      <!-- Desktop Navigation Links -->
      <div class="hidden md:flex md:items-center md:gap-6">
        <a href="/marketplace" 
          class="text-gray-700 dark:text-text-dark hover:text-primary-600 px-3 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-lg">
          Cari Kost
        </a>
        
        @auth
          <a href="/profile" 
            class="text-gray-700 dark:text-text-dark hover:text-primary-600 px-3 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-lg">
            Profile
          </a>
          <a href="{{ auth()->user()->dashboardRoute() }}" 
            class="text-gray-700 dark:text-text-dark hover:text-primary-600 px-3 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-lg">
            Dashboard
          </a>
          <form method="POST" action="/logout" class="inline">
            @csrf
            <button type="submit" 
              class="text-error-700 hover:text-error-800 px-3 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-lg">
              Logout
            </button>
          </form>
        @else
          <a href="/login" 
            class="text-gray-700 dark:text-text-dark hover:text-primary-600 px-3 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-lg">
            Masuk
          </a>
          <a href="/register" 
            class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
            Daftar
          </a>
        @endauth
      </div>
      
      <!-- Mobile Menu Button -->
      <div class="md:hidden">
        <button @click="mobileMenuOpen = !mobileMenuOpen"
          aria-label="Buka menu navigasi"
          :aria-expanded="mobileMenuOpen"
          class="p-2 text-gray-600 dark:text-text-dark hover:text-gray-900 dark:hover:text-text-strong-dark focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-lg transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>
      </div>
    </div>
  </div>
  
  <!-- Mobile Menu -->
  <div x-show="mobileMenuOpen" 
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-1"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-1"
    class="md:hidden border-t border-gray-200 dark:border-border-dark">
    <div class="px-2 pt-2 pb-3 space-y-1">
      <a href="/marketplace" 
        class="block px-3 py-2 rounded-lg text-base font-medium text-gray-700 dark:text-text-dark hover:bg-gray-50 dark:hover:bg-surface-muted-dark transition-colors">
        Cari Kost
      </a>
      @auth
        <a href="/profile" 
          class="block px-3 py-2 rounded-lg text-base font-medium text-gray-700 dark:text-text-dark hover:bg-gray-50 dark:hover:bg-surface-muted-dark transition-colors">
          Profile
        </a>
        <a href="{{ auth()->user()->dashboardRoute() }}" 
          class="block px-3 py-2 rounded-lg text-base font-medium text-gray-700 dark:text-text-dark hover:bg-gray-50 dark:hover:bg-surface-muted-dark transition-colors">
          Dashboard
        </a>
        <form method="POST" action="/logout">
          @csrf
          <button type="submit" 
            class="w-full text-left px-3 py-2 rounded-lg text-base font-medium text-error-700 hover:bg-gray-50 dark:hover:bg-surface-muted-dark transition-colors">
            Logout
          </button>
        </form>
      @else
        <a href="/login" 
          class="block px-3 py-2 rounded-lg text-base font-medium text-gray-700 dark:text-text-dark hover:bg-gray-50 dark:hover:bg-surface-muted-dark transition-colors">
          Masuk
        </a>
        <a href="/register" 
          class="block px-3 py-2 rounded-lg text-base font-medium bg-primary-600 text-white hover:bg-primary-700 transition-colors">
          Daftar
        </a>
      @endauth
    </div>
  </div>
</nav>
