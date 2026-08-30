@props([
    'title' => null,
    'subtitle' => null,
    'breadcrumbs' => [],
])

@php
// Breadcrumbs format: [['label' => 'Dashboard', 'url' => route('...')], ['label' => 'Current']]
// Actions slot: optional buttons/links on the right side
@endphp

<header {{ $attributes->merge(['class' => 'mb-6']) }}>
  <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    {{-- Title & breadcrumb section --}}
    <div class="min-w-0 flex-1">
      @if (!empty($breadcrumbs))
        <x-breadcrumbs :items="$breadcrumbs" class="mb-2" />
      @endif

      @if ($title)
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-text-strong-dark">
          {{ $title }}
        </h1>
      @endif

      @if ($subtitle)
        <p class="mt-1 text-sm text-gray-500 dark:text-text-muted-dark">
          {{ $subtitle }}
        </p>
      @endif

      {{-- Default slot for custom title content if needed --}}
      @if (!$title && $slot->isNotEmpty())
        {{ $slot }}
      @endif
    </div>

    {{-- Actions section (right side) --}}
    @if (isset($actions) && $actions->isNotEmpty())
      <div class="flex flex-wrap items-center gap-2 shrink-0">
        {{ $actions }}
      </div>
    @endif
  </div>
</header>
