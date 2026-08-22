@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'block w-full px-4 py-3 border border-gray-300 dark:border-border-strong-dark dark:bg-surface-dark dark:text-text-dark placeholder:text-gray-400 rounded-md shadow-xs focus:border-primary-500 dark:focus:border-primary-500 focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-500 transition-colors']) }}>
