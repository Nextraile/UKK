@props(['type' => 'info'])

@php
/**
 * Callout Component
 * 
 * Alert-style callout box for important information (DESIGN.md §3.17 pattern)
 * 
 * @param string $type - Type of callout (info|warning|error|success)
 */

$typeConfig = [
    'info' => [
        'bg' => 'bg-info/10 dark:bg-info-900/20',
        'border' => 'border-info/20 dark:border-info-700/30',
        'icon' => 'text-info-700 dark:text-info-300',
        'text' => 'text-info-700 dark:text-info-200',
        'iconPath' => 'M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z'
    ],
    'warning' => [
        'bg' => 'bg-warning/10 dark:bg-warning-900/20',
        'border' => 'border-warning/20 dark:border-warning-700/30',
        'icon' => 'text-warning-700 dark:text-warning-300',
        'text' => 'text-warning-700 dark:text-warning-200',
        'iconPath' => 'M8.257 3.099c.765-1.36 2.72-1.36 3.485 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z'
    ],
    'error' => [
        'bg' => 'bg-error/10 dark:bg-error-900/20',
        'border' => 'border-error/20 dark:border-error-700/30',
        'icon' => 'text-error-700 dark:text-error-300',
        'text' => 'text-error-700 dark:text-error-200',
        'iconPath' => 'M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z'
    ],
    'success' => [
        'bg' => 'bg-success/10 dark:bg-success-900/20',
        'border' => 'border-success/20 dark:border-success-700/30',
        'icon' => 'text-success-700 dark:text-success-300',
        'text' => 'text-success-700 dark:text-success-200',
        'iconPath' => 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z'
    ],
];

$config = $typeConfig[$type] ?? $typeConfig['info'];
@endphp

<div {{ $attributes->merge(['class' => "rounded-lg border p-4 {$config['bg']} {$config['border']}"]) }} role="alert">
    <div class="flex items-start gap-3">
        <svg class="w-5 h-5 {{ $config['icon'] }} mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
            <path fill-rule="evenodd" d="{{ $config['iconPath'] }}" clip-rule="evenodd"/>
        </svg>
        <div class="flex-1 {{ $config['text'] }}">
            {{ $slot }}
        </div>
    </div>
</div>
