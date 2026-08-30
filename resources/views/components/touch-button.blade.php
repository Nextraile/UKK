@props([
    'variant' => 'primary',
    'size' => 'md',
    'fullWidthOnMobile' => false,
    'type' => 'button',
    'href' => null,
])

@php
// WCAG 2.5.5 compliant touch targets (minimum 44px height on mobile)
$sizes = [
    'sm' => 'px-4 py-3 text-sm',    // 44px height minimum
    'md' => 'px-6 py-3 text-base',  // 48px height
    'lg' => 'px-8 py-4 text-lg',    // 56px height
];

$variants = [
    'primary' => 'bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500',
    'secondary' => 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:ring-gray-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700',
    'danger' => 'bg-error-600 text-white hover:bg-error-700 focus:ring-error-500',
    'ghost' => 'text-gray-700 hover:bg-gray-100 focus:ring-gray-500 dark:text-gray-300 dark:hover:bg-gray-800',
];

$baseClasses = 'inline-flex items-center justify-center font-semibold rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
$sizeClass = $sizes[$size] ?? $sizes['md'];
$variantClass = $variants[$variant] ?? $variants['primary'];
$widthClass = $fullWidthOnMobile ? 'w-full sm:w-auto' : '';

$classes = trim("{$baseClasses} {$sizeClass} {$variantClass} {$widthClass}");
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
