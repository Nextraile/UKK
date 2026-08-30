@props([
    'items' => [],
])

@php
// Items format: [['label' => 'Home', 'url' => '/'], ['label' => 'Kost', 'url' => '/kost'], ['label' => 'Detail']]
// Last item (no url) is current page
@endphp

<nav aria-label="Breadcrumb" {{ $attributes->merge(['class' => '']) }}>
  <ol class="flex flex-wrap items-center gap-1 text-sm" itemscope itemtype="https://schema.org/BreadcrumbList">
    @foreach ($items as $index => $item)
      <li class="flex items-center gap-1" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
        @if (isset($item['url']) && !$loop->last)
          <a href="{{ $item['url'] }}" 
            class="text-gray-500 dark:text-text-muted-dark hover:text-primary-600 dark:hover:text-primary-500 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 rounded px-1"
            itemprop="item">
            <span itemprop="name">{{ $item['label'] }}</span>
          </a>
          <meta itemprop="position" content="{{ $index + 1 }}">
        @else
          <span aria-current="page" class="font-medium text-gray-900 dark:text-text-strong-dark px-1" itemprop="name">
            {{ $item['label'] }}
          </span>
          <meta itemprop="position" content="{{ $index + 1 }}">
        @endif
        
        @if (!$loop->last)
          <svg class="w-4 h-4 text-gray-400 dark:text-text-muted-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        @endif
      </li>
    @endforeach
  </ol>
</nav>
