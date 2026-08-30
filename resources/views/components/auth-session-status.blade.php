@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-success-600 dark:text-success-400']) }}>
        {{ $status }}
    </div>
@endif
