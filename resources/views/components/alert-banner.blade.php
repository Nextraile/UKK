@props([
    'variant' => 'info', // success|error|warning|info
    'title' => null,
    'dismissible' => false,
])

@php
$variants = [
    'success' => [
        'bg' => 'bg-success-light dark:bg-success-900/30',
        'border' => 'border-success-700/30 dark:border-success-700',
        'text' => 'text-success-700 dark:text-success-400',
        'textStrong' => 'text-success-900 dark:text-success-300',
        'icon' => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>',
    ],
    'error' => [
        'bg' => 'bg-error-light dark:bg-error-900/30',
        'border' => 'border-error-700/30 dark:border-error-700',
        'text' => 'text-error-700 dark:text-error-400',
        'textStrong' => 'text-error-900 dark:text-error-300',
        'icon' => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>',
    ],
    'warning' => [
        'bg' => 'bg-warning-light dark:bg-warning-900/30',
        'border' => 'border-warning-700/30 dark:border-warning-700',
        'text' => 'text-warning-700 dark:text-warning-400',
        'textStrong' => 'text-warning-900 dark:text-warning-300',
        'icon' => '<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>',
    ],
    'info' => [
        'bg' => 'bg-info-light dark:bg-info-900/30',
        'border' => 'border-info-700/30 dark:border-info-700',
        'text' => 'text-info-700 dark:text-info-400',
        'textStrong' => 'text-info-900 dark:text-info-300',
        'icon' => '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>',
    ],
];

$config = $variants[$variant] ?? $variants['info'];
@endphp

<div 
    @if($dismissible)
        x-data="{ show: true }" 
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    @endif
    class="flex items-start gap-3 p-4 {{ $config['bg'] }} border-l-4 {{ $config['border'] }} rounded-lg"
    role="alert"
    aria-live="polite"
    aria-atomic="true"
    {{ $attributes }}
>
    {{-- Icon --}}
    <svg class="w-5 h-5 {{ $config['text'] }} shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
        {!! $config['icon'] !!}
    </svg>

    {{-- Content --}}
    <div class="flex-1 min-w-0">
        @if($title)
            <h4 class="text-sm font-semibold {{ $config['textStrong'] }}">{{ $title }}</h4>
        @endif
        <div class="text-sm {{ $config['text'] }} {{ $title ? 'mt-1' : '' }}">
            {{ $slot }}
        </div>
    </div>

    {{-- Dismiss button --}}
    @if($dismissible)
        <button 
            type="button"
            @click="show = false"
            class="shrink-0 {{ $config['text'] }} hover:{{ $config['textStrong'] }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-{{ $variant }}-500 rounded"
            aria-label="Tutup notifikasi"
        >
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>
    @endif
</div>
