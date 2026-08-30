{{-- Sticky Action Bar Component --}}
{{-- Usage: <x-sticky-action-bar price="1200000" :route="route('bookings.create', $kost)" /> --}}
{{-- Specification: DESIGN.md §3.33 (line 3246-3278) --}}

@props(['price', 'route' => '#booking-form'])

<div x-data="{ visible: false }"
  @scroll.window="visible = window.scrollY > 400"
  x-cloak 
  x-show="visible"
  x-transition:enter="transition ease-out duration-300"
  x-transition:enter-start="translate-y-full" 
  x-transition:enter-end="translate-y-0"
  x-transition:leave="transition ease-in duration-200"
  x-transition:leave-start="translate-y-0" 
  x-transition:leave-end="translate-y-full"
  class="fixed inset-x-0 bottom-0 z-40 border-t border-gray-200 dark:border-border-dark bg-white dark:bg-surface-raised-dark px-4 pt-3 pb-[env(safe-area-inset-bottom)] shadow-[0_-4px_12px_rgba(0,0,0,0.08)] lg:hidden">
  
  <div class="flex items-center gap-4">
    {{-- Price Info --}}
    <div class="min-w-0">
      <p class="text-xs text-gray-500 dark:text-text-muted-dark">Mulai dari</p>
      <p class="text-lg font-bold text-secondary-600">
        Rp {{ number_format($price / 1000, 0, ',', '.') }}rb
        <span class="text-xs font-normal text-gray-500 dark:text-text-muted-dark">/bulan</span>
      </p>
    </div>
    
    {{-- CTA Button --}}
    @if(is_string($route) && str_starts_with($route, '#'))
      {{-- Scroll to anchor --}}
      <button 
        type="button"
        @click="document.querySelector('{{ $route }}')?.scrollIntoView({ behavior: 'smooth' })"
        class="flex-1 inline-flex items-center justify-center px-4 py-3 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors min-h-[44px]">
        Booking Sekarang
      </button>
    @else
      {{-- Link to booking page --}}
      <a 
        href="{{ $route }}"
        class="flex-1 inline-flex items-center justify-center px-4 py-3 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors min-h-[44px]">
        Booking Sekarang
      </a>
    @endif
  </div>
</div>
