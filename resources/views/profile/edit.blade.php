<x-base-layout 
    title="Edit Profil - SewaKost"
    variant="full-width">
    
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-page-header 
                title="Edit Profil"
                :breadcrumbs="[
                    ['label' => 'Profil', 'url' => route('profile.show')],
                    ['label' => 'Edit'],
                ]"
            >
                <x-slot:actions>
                    <a href="{{ route('profile.show') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Kembali
                    </a>
                </x-slot:actions>
            </x-page-header>

            {{-- Status message --}}
            @if (session('status'))
                <div class="max-w-xl rounded-md bg-success-50 p-4 text-sm font-medium text-success-600 dark:bg-success-900/30 dark:text-success-400">
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
</x-base-layout>
