{{-- Kost Card Component --}}
{{-- Usage: <x-kost-card :kost="$kost" /> --}}
{{-- Specification: DESIGN.md §3.3 (line 860-902) --}}

@props(['kost'])

<article class="group bg-white rounded-xl shadow-md hover:shadow-xl transition-all overflow-hidden border border-gray-100">
  <a href="{{ route('marketplace.show', $kost->slug) }}" class="block">
    {{-- Thumbnail --}}
    <div class="aspect-video bg-gray-200 overflow-hidden relative">
      @if($kost->thumbnail_url)
        <img src="{{ $kost->thumbnail_url }}" 
          alt="{{ $kost->name }}" 
          class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
          loading="lazy">
      @else
        <div class="w-full h-full flex items-center justify-center">
          <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
          </svg>
        </div>
      @endif
    </div>
    
    {{-- Content --}}
    <div class="p-5">
      <h3 class="text-lg font-semibold text-gray-900 line-clamp-1 group-hover:text-primary-600 transition-colors">
        {{ $kost->name }}
      </h3>
      <p class="text-sm text-gray-600 mt-1 flex items-center">
        <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        @if($kost->address)
          {{ $kost->address->district }}, {{ $kost->address->city }}, {{ $kost->address->province }}
          @if($kost->address->postal_code)
            ({{ $kost->address->postal_code }})
          @endif
        @else
          Lokasi tidak tersedia
        @endif
      </p>
      
      {{-- Kategori Badges --}}
      @if($kost->categories->isNotEmpty())
        <div class="mt-2 flex flex-wrap gap-1">
          @foreach($kost->categories as $category)
            <span class="inline-flex items-center px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded-full">
              {{ $category->name }}
            </span>
          @endforeach
        </div>
      @endif
      
      <div class="mt-4 flex items-baseline justify-between">
        @if($kost->review_count > 0)
          <x-rating :value="$kost->average_kost_rating" :count="$kost->review_count" />
        @else
          <span class="text-sm text-gray-500">Belum ada rating</span>
        @endif
      </div>
    </div>
  </a>
</article>
