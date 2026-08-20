<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-text-strong dark:text-text-strong-dark leading-tight">
            {{ __('Edit Profil') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Back to profile link --}}
            <div class="max-w-xl">
                <a href="{{ route('profile.show') }}" class="inline-flex items-center text-sm text-text dark:text-text-muted-dark hover:text-text-strong dark:hover:text-text-strong-dark">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali ke Profil
                </a>
            </div>

            {{-- Status message --}}
            @if (session('status'))
                <div class="max-w-xl rounded-md bg-green-50 p-4 text-sm font-medium text-green-600 dark:bg-green-900/30 dark:text-green-400">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Avatar upload --}}
            <div class="p-4 sm:p-8 bg-surface-raised dark:bg-surface-raised-dark shadow-sm sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-avatar-form')
                </div>
            </div>

            {{-- Profile information --}}
            <div class="p-4 sm:p-8 bg-surface-raised dark:bg-surface-raised-dark shadow-sm sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            {{-- Password update --}}
            <div class="p-4 sm:p-8 bg-surface-raised dark:bg-surface-raised-dark shadow-sm sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            {{-- Delete account --}}
            <div class="p-4 sm:p-8 bg-surface-raised dark:bg-surface-raised-dark shadow-sm sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
