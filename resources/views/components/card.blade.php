<div {{ $attributes->merge(['class' => 'overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800']) }}>
    <div class="p-6 text-gray-900 dark:text-gray-100">
        {{ $slot }}
    </div>
</div>
