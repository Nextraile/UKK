@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-primary-600 dark:border-primary-600 text-start text-base font-medium text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-primary-900/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 focus-visible:text-primary-800 dark:focus-visible:text-primary-200 focus-visible:bg-primary-100 dark:focus-visible:bg-primary-900/30 focus-visible:border-primary-700 dark:focus-visible:border-primary-300 transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 focus-visible:text-gray-800 dark:focus-visible:text-gray-200 focus-visible:bg-gray-50 dark:focus-visible:bg-gray-700 focus-visible:border-gray-300 dark:focus-visible:border-gray-600 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
