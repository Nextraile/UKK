<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-text-strong dark:text-text-strong-dark leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-surface-raised dark:bg-surface-raised-dark overflow-hidden shadow-xs sm:rounded-lg">
                <div class="p-6 text-text-strong dark:text-text-strong-dark">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
