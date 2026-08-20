<button
    type="button"
    x-data="{ dark: document.documentElement.classList.contains('dark') }"
    @click="dark = !dark; document.documentElement.classList.toggle('dark', dark); localStorage.setItem('theme', dark ? 'dark' : 'light')"
    :aria-pressed="dark ? 'true' : 'false'"
    aria-label="Ganti tema"
    {{ $attributes->merge(['class' => 'inline-flex items-center justify-center p-2 min-w-11 min-h-11 rounded-lg text-text-muted dark:text-text-muted-dark hover:text-text-strong dark:hover:text-text-strong-dark hover:bg-surface-muted dark:hover:bg-surface-muted-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 transition-colors']) }}
>
    {{-- Sun — shown in dark mode (click switches to light) --}}
    <svg x-show="dark" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <circle cx="12" cy="12" r="4"></circle>
        <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"></path>
    </svg>

    {{-- Moon — shown in light mode (click switches to dark) --}}
    <svg x-show="!dark" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
    </svg>
</button>