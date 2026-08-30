{{--
  Star Rating Component
  
  Design Exception: This component uses semantic warning-700 token for filled stars.
  Inline star ratings in views may use text-yellow-400 (UI convention for gold stars).
  This is the ONLY approved hardcoded color exception per DESIGN.md §2.1.
--}}
@props([
    'value' => 0,
    'count' => null,
    'size' => 'md', // sm, md, lg
])

@php
$sizeClasses = [
    'sm' => 'w-3 h-3',
    'md' => 'w-4 h-4',
    'lg' => 'w-5 h-5',
];
$starSize = $sizeClasses[$size] ?? $sizeClasses['md'];
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}
  role="img" aria-label="{{ number_format($value, 1, ',', '.') }} dari 5{{ $count ? ' (' . $count . ' ulasan)' : '' }}">
  <span class="flex items-center gap-0.5 text-warning-700" aria-hidden="true">
    @for ($i = 1; $i <= 5; $i++)
      <span class="relative inline-block {{ $starSize }}">
        <!-- Base star (empty) -->
        <svg class="{{ $starSize }} text-gray-300 dark:text-border-strong-dark" viewBox="0 0 20 20" fill="currentColor">
          <path d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.07 3.29a1 1 0 0 0 .95.69h3.46c.97 0 1.37 1.24.59 1.81l-2.8 2.03a1 1 0 0 0-.36 1.12l1.07 3.29c.3.92-.76 1.69-1.54 1.12l-2.8-2.03a1 1 0 0 0-1.18 0l-2.8 2.03c-.78.57-1.84-.2-1.54-1.12l1.07-3.29a1 1 0 0 0-.36-1.12L2.98 8.72c-.78-.57-.38-1.81.59-1.81h3.46a1 1 0 0 0 .95-.69l1.07-3.29Z"/>
        </svg>
        
        <!-- Overlay fill (partial width based on value) -->
        @php
          $fillPercent = min(max($value - ($i - 1), 0), 1) * 100;
        @endphp
        @if ($fillPercent > 0)
          <span class="absolute inset-0 overflow-hidden" style="width: {{ $fillPercent }}%">
            <svg class="{{ $starSize }}" viewBox="0 0 20 20" fill="currentColor">
              <path d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.07 3.29a1 1 0 0 0 .95.69h3.46c.97 0 1.37 1.24.59 1.81l-2.8 2.03a1 1 0 0 0-.36 1.12l1.07 3.29c.3.92-.76 1.69-1.54 1.12l-2.8-2.03a1 1 0 0 0-1.18 0l-2.8 2.03c-.78.57-1.84-.2-1.54-1.12l1.07-3.29a1 1 0 0 0-.36-1.12L2.98 8.72c-.78-.57-.38-1.81.59-1.81h3.46a1 1 0 0 0 .95-.69l1.07-3.29Z"/>
            </svg>
          </span>
        @endif
      </span>
    @endfor
  </span>
  
  <span class="text-sm font-semibold text-gray-900 dark:text-text-strong-dark">{{ number_format($value, 1, ',', '.') }}</span>
  
  @if ($count !== null)
    <span class="text-sm text-gray-500 dark:text-text-muted-dark">({{ $count }} ulasan)</span>
  @endif
</div>
