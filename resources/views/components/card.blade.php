<div {{ $attributes->merge(['class' => 'overflow-hidden bg-white shadow-sm sm:rounded-lg']) }}>
    <div class="p-6 text-gray-900">
        {{ $slot }}
    </div>
</div>
