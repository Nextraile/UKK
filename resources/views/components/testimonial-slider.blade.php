@props([
    'testimonials' => [],
    'autoplay' => true,
    'interval' => 5000,
])

@php
// Testimonials format: [['quote' => '...', 'name' => 'John', 'location' => 'Kost Name — City', 'avatar' => 'url' (optional)]]
@endphp

<div x-data="{
    index: 0,
    paused: false,
    timer: null,
    items: @js($testimonials),
    next() { this.index = (this.index + 1) % this.items.length; },
    prev() { this.index = (this.index - 1 + this.items.length) % this.items.length; },
    go(i) { this.index = i; },
    init() {
      @if ($autoplay)
      this.timer = setInterval(() => { 
        if (!this.paused) this.next(); 
      }, {{ $interval }});
      @endif
    },
    destroy() { 
      if (this.timer) clearInterval(this.timer); 
    }
  }"
  @mouseenter="paused = true" 
  @mouseleave="paused = false"
  @focusin="paused = true" 
  @focusout="paused = false"
  {{ $attributes->merge(['class' => 'relative mx-auto max-w-2xl']) }}>

  <!-- Testimonial Card (aria-live announces changes) -->
  <div class="rounded-xl bg-white dark:bg-surface-raised-dark p-6 shadow-md sm:p-8" 
    aria-live="polite" 
    aria-atomic="true">
    <!-- Quote Icon -->
    <svg class="mb-4 h-8 w-8 text-primary-600" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
      <path d="M9.983 3v7.391c0 5.704-3.731 9.57-8.983 10.609l-.995-2.151c2.432-.917 3.995-3.638 3.995-5.849H0V3h9.983zm14.017 0v7.391c0 5.704-3.748 9.571-9 10.609l-.996-2.151c2.433-.917 3.996-3.638 3.996-5.849h-3.983V3h9.983z"/>
    </svg>
    
    <!-- Quote Text -->
    <blockquote class="text-lg leading-relaxed text-gray-700 dark:text-text-dark" x-text="items[index].quote"></blockquote>
    
    <!-- Author -->
    <footer class="mt-5 flex items-center gap-3">
      <template x-if="items[index].avatar">
        <img :src="items[index].avatar" :alt="items[index].name" class="h-10 w-10 rounded-full object-cover">
      </template>
      <template x-if="!items[index].avatar">
        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900/30 font-semibold text-primary-700 dark:text-primary-400" 
          aria-hidden="true" 
          x-text="items[index].name.charAt(0)"></span>
      </template>
      <div>
        <p class="text-sm font-semibold text-gray-900 dark:text-text-strong-dark" x-text="items[index].name"></p>
        <p class="text-xs text-gray-500 dark:text-text-muted-dark" x-text="items[index].location"></p>
      </div>
    </footer>
  </div>

  <!-- Navigation Controls -->
  <div class="mt-4 flex items-center justify-between gap-4">
    <!-- Previous Button -->
    <button type="button" 
      @click="prev()" 
      aria-label="Testimoni sebelumnya"
      class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-300 dark:border-border-strong-dark text-gray-600 dark:text-text-dark hover:bg-gray-50 dark:hover:bg-surface-muted-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 transition-colors">
      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
      </svg>
    </button>
    
    <!-- Dots Indicator -->
    <div class="flex items-center gap-2" role="group" aria-label="Pilih testimoni">
      <template x-for="(item, i) in items" :key="i">
        <button type="button" 
          @click="go(i)" 
          :aria-current="i === index ? 'true' : null"
          :aria-label="'Tampilkan testimoni ' + item.name"
          class="group flex h-6 w-6 items-center justify-center rounded-full focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
          <span aria-hidden="true"
            :class="i === index ? 'w-6 bg-primary-600' : 'w-2.5 bg-gray-300 dark:bg-border-dark group-hover:bg-gray-400 dark:group-hover:bg-border-strong-dark'"
            class="block h-2.5 rounded-full transition-all duration-300"></span>
        </button>
      </template>
    </div>
    
    <!-- Next Button -->
    <button type="button" 
      @click="next()" 
      aria-label="Testimoni berikutnya"
      class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-300 dark:border-border-strong-dark text-gray-600 dark:text-text-dark hover:bg-gray-50 dark:hover:bg-surface-muted-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 transition-colors">
      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
      </svg>
    </button>
  </div>
</div>
