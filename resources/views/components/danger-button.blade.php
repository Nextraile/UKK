<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-6 py-3 bg-error-600 border border-transparent rounded-md font-semibold text-base text-white shadow-md hover:bg-error-700 active:bg-error-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-error-600 focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200']) }}>
    {{ $slot }}
</button>
