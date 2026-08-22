@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-gray-700 dark:text-text-muted-dark']) }}>
    {{ $value ?? $slot }}
    @if($attributes->get('required'))
        <span class="text-error-700" aria-label="required">*</span>
    @endif
</label>
