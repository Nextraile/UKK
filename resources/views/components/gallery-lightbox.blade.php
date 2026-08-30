{{-- Gallery Lightbox Component --}}
{{-- Usage: <x-gallery-lightbox :images="$images" /> --}}
{{-- Specification: DESIGN.md §3.27 (line 2645-2764) --}}
{{-- $images = array of ['url' => '...', 'alt' => '...'] --}}

@props(['images' => []])

<div x-data="{
  open: false,
  index: 0,
  images: @js($images),
  get current() { return this.images[this.index] || {}; },
  next() { this.index = (this.index + 1) % this.images.length; },
  prev() { this.index = (this.index - 1 + this.images.length) % this.images.length; },
  goto(i) { this.index = i; this.open = true; },
  close() { this.open = false; },
  init() {
    this.$watch('open', value => {
      document.body.classList.toggle('overflow-hidden', value);
    });
  }
}" 
@keydown.escape.window="if (open) close()"
@keydown.arrow-left.window="if (open) prev()"
@keydown.arrow-right.window="if (open) next()"
x-cloak>
  
  {{-- Thumbnail Grid (Trigger) --}}
  <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
    @foreach($images as $idx => $image)
      <button 
        type="button"
        @click="goto({{ $idx }})"
        class="aspect-square overflow-hidden rounded-lg bg-gray-200 dark:bg-surface-muted-dark hover:opacity-90 transition-opacity focus:outline-none focus:ring-2 focus:ring-primary-500">
        <img 
          src="{{ $image['url'] }}" 
          alt="{{ $image['alt'] ?? 'Gambar ' . ($idx + 1) }}"
          class="w-full h-full object-cover"
          loading="lazy">
      </button>
    @endforeach
  </div>
  
  {{-- Lightbox Modal --}}
  <div 
    x-show="open"
    x-trap.noscroll="open"
    role="dialog"
    aria-modal="true"
    aria-label="Galeri foto"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/95"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0">
    
    {{-- Close Button --}}
    <button 
      type="button"
      @click="close()"
      aria-label="Tutup galeri"
      class="absolute top-4 right-4 z-10 p-2 text-white hover:bg-white/10 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-white">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>
    
    {{-- Image Counter --}}
    <div class="absolute top-4 left-4 z-10 px-3 py-1.5 bg-black/50 text-white text-sm font-medium rounded-lg backdrop-blur-sm">
      <span x-text="index + 1"></span> / <span x-text="images.length"></span>
    </div>
    
    {{-- Main Image --}}
    <div class="relative w-full h-full flex items-center justify-center p-4 md:p-8">
      <img 
        :src="current.url" 
        :alt="current.alt || 'Gambar ' + (index + 1)"
        class="max-w-full max-h-full object-contain"
        @click.stop>
    </div>
    
    {{-- Previous Button --}}
    <button 
      type="button"
      @click="prev()"
      aria-label="Gambar sebelumnya"
      class="absolute left-4 top-1/2 -translate-y-1/2 p-3 text-white bg-black/30 hover:bg-black/50 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-white">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
      </svg>
    </button>
    
    {{-- Next Button --}}
    <button 
      type="button"
      @click="next()"
      aria-label="Gambar berikutnya"
      class="absolute right-4 top-1/2 -translate-y-1/2 p-3 text-white bg-black/30 hover:bg-black/50 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-white">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
      </svg>
    </button>
    
    {{-- Thumbnail Navigation (Bottom) --}}
    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 max-w-full overflow-x-auto px-4">
      <div class="flex gap-2">
        <template x-for="(img, idx) in images" :key="idx">
          <button 
            type="button"
            @click="goto(idx)"
            :aria-label="'Lihat gambar ' + (idx + 1)"
            :class="idx === index ? 'ring-2 ring-primary-500' : 'opacity-60 hover:opacity-100'"
            class="flex-shrink-0 w-16 h-16 rounded-md overflow-hidden transition-opacity focus:outline-none focus:ring-2 focus:ring-primary-500">
            <img 
              :src="img.url" 
              :alt="img.alt || 'Thumbnail ' + (idx + 1)"
              class="w-full h-full object-cover">
          </button>
        </template>
      </div>
    </div>
  </div>
</div>
