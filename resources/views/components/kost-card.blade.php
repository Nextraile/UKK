{{-- Kost Card Component --}}
{{-- Usage: <x-kost-card :kost="$kost" /> --}}
{{-- Specification: DESIGN.md §3.3 (line 860-902) --}}

@props(['kost'])

<article class="group bg-white dark:bg-surface-raised-dark rounded-xl shadow-md hover:shadow-xl transition-all overflow-hidden border border-gray-100 dark:border-border-dark">
  <a href="{{ route('marketplace.show', $kost->slug) }}" class="block">
    {{-- Thumbnail --}}
    <div class="aspect-video bg-gray-200 dark:bg-surface-muted-dark overflow-hidden relative">
      @if($kost->thumbnail_url)
        <img src="{{ $kost->thumbnail_url }}" 
          alt="{{ $kost->name }}" 
          class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
          loading="lazy">
      @else
        <div class="w-full h-full flex items-center justify-center">
          <svg class="w-16 h-16 text-gray-400 dark:text-text-muted-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
          </svg>
        </div>
      @endif
    </div>
    
    {{-- Content --}}
    <div class="p-5">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-text-strong-dark line-clamp-1 group-hover:text-primary-600 transition-colors">
        {{ $kost->name }}
      </h3>
      <p class="text-sm text-gray-600 dark:text-text-dark mt-1 flex items-center">
        <svg class="w-4 h-4 mr-1 text-gray-400 dark:text-text-muted-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        {{ $kost->city ?? 'Lokasi tidak tersedia' }}
      </p>
      
      {{-- Kategori Badges --}}
      @if($kost->categories->isNotEmpty())
        <div class="mt-2 flex flex-wrap gap-1">
          @foreach($kost->categories as $category)
            <span class="inline-flex items-center px-2 py-0.5 bg-gray-100 dark:bg-surface-muted-dark text-gray-600 dark:text-text-muted-dark text-xs rounded-full">
              {{ $category->name }}
            </span>
          @endforeach
        </div>
      @endif
      
      <div class="mt-4 flex items-baseline justify-between">
        <div>
          @if($kost->min_price)
            <span class="text-2xl font-bold text-gray-900 dark:text-text-strong-dark">
              Mulai dari Rp {{ number_format($kost->min_price / 1000, 1, ',', '.') }}jt
            </span>
            <span class="text-sm text-gray-500 dark:text-text-muted-dark">/bulan</span>
          @else
            <span class="text-lg font-medium text-gray-600 dark:text-text-muted-dark">
              Hubungi Admin
            </span>
          @endif
        </div>
        @if($kost->average_rating)
          <div class="flex items-center text-sm">
            <svg class="w-5 h-5 text-warning fill-current" viewBox="0 0 20 20" aria-hidden="true">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            <span class="ml-1 font-semibold text-gray-700 dark:text-text-dark">{{ number_format($kost->average_rating, 1) }}</span>
            <span class="ml-1 text-gray-500 dark:text-text-muted-dark">({{ $kost->reviews_count }})</span>
          </div>
        @endif
      </div>
    </div>
  </a>
</article>
