<x-base-layout 
    title="Edit Profil - SewaKost"
    variant="full-width">
    
    <div class="py-12" x-data="{ 
        openSection: 'profile',
        scrollToSection() { 
            if (window.location.hash) {
                const target = document.querySelector(window.location.hash);
                if (target) {
                    setTimeout(() => {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        const firstInput = target.querySelector('input, textarea, select');
                        if (firstInput) {
                            setTimeout(() => firstInput.focus(), 300);
                        }
                    }, 100);
                }
            }
        } 
    }" x-init="scrollToSection()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-page-header 
                title="Edit Profil"
                :breadcrumbs="[
                    ['label' => 'Profil', 'url' => route('profile.show')],
                    ['label' => 'Edit'],
                ]"
            >
                <x-slot:actions>
                    <a href="{{ route('profile.show') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-text dark:text-text-dark bg-surface-raised dark:bg-surface-raised-dark border border-border dark:border-border-dark rounded-lg hover:bg-surface-muted dark:hover:bg-surface-muted-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Kembali
                    </a>
                </x-slot:actions>
            </x-page-header>

            {{-- Status message --}}
            @if (session('status'))
                <div x-data="{ show: true }" 
                     x-show="show"
                     x-init="setTimeout(() => show = false, 5000)"
                     role="alert"
                     aria-live="polite"
                     class="mb-6 rounded-md bg-success-50 dark:bg-success-900/30 p-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-success-600 dark:text-success-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm font-medium text-success-600 dark:text-success-400">
                            {{ session('status') }}
                        </span>
                    </div>
                    <button @click="show = false" 
                            type="button"
                            aria-label="Tutup notifikasi"
                            class="text-success-600 dark:text-success-400 hover:text-success-700 dark:hover:text-success-300">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            @endif

            {{-- Accordion sections --}}
            <div class="space-y-3 max-w-3xl">
                {{-- Section 1: Profile Info --}}
                <div id="profile-info" class="border border-border dark:border-border-dark rounded-lg overflow-hidden">
                    <button type="button" 
                            @click="openSection = openSection === 'profile' ? null : 'profile'"
                            class="w-full px-6 py-4 flex items-center justify-between bg-surface-raised dark:bg-surface-raised-dark hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                            :aria-expanded="openSection === 'profile'">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <div class="text-left">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-text-strong-dark">Informasi Profil</h3>
                                <p class="text-sm text-gray-500 dark:text-text-muted-dark">Nama, email, nomor telepon</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" 
                             :class="openSection === 'profile' && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="openSection === 'profile'" x-collapse>
                        <div class="p-5 sm:p-8 border-t border-border dark:border-border-dark">
                            @include('profile.partials.update-profile-information-form')
                        </div>
                    </div>
                </div>

                {{-- Section 2: Avatar --}}
                <div id="avatar" class="border border-border dark:border-border-dark rounded-lg overflow-hidden">
                    <button type="button" 
                            @click="openSection = openSection === 'avatar' ? null : 'avatar'"
                            class="w-full px-6 py-4 flex items-center justify-between bg-surface-raised dark:bg-surface-raised-dark hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                            :aria-expanded="openSection === 'avatar'">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <div class="text-left">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-text-strong-dark">Foto Profil</h3>
                                <p class="text-sm text-gray-500 dark:text-text-muted-dark">Upload avatar Anda</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" 
                             :class="openSection === 'avatar' && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="openSection === 'avatar'" x-collapse>
                        <div class="p-5 sm:p-8 border-t border-border dark:border-border-dark">
                            @include('profile.partials.update-avatar-form')
                        </div>
                    </div>
                </div>

                {{-- Section 3: Password --}}
                <div id="password" class="border border-border dark:border-border-dark rounded-lg overflow-hidden">
                    <button type="button" 
                            @click="openSection = openSection === 'password' ? null : 'password'"
                            class="w-full px-6 py-4 flex items-center justify-between bg-surface-raised dark:bg-surface-raised-dark hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                            :aria-expanded="openSection === 'password'">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <div class="text-left">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-text-strong-dark">Ubah Password</h3>
                                <p class="text-sm text-gray-500 dark:text-text-muted-dark">Kelola keamanan akun</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" 
                             :class="openSection === 'password' && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="openSection === 'password'" x-collapse>
                        <div class="p-5 sm:p-8 border-t border-border dark:border-border-dark">
                            @include('profile.partials.update-password-form')
                        </div>
                    </div>
                </div>

                {{-- Section 4: Delete Account (Danger Zone) --}}
                <div id="delete" class="border-2 border-error/20 dark:border-error-700/30 rounded-lg overflow-hidden">
                    <button type="button" 
                            @click="openSection = openSection === 'delete' ? null : 'delete'"
                            class="w-full px-6 py-4 flex items-center justify-between bg-error/5 dark:bg-error-900/10 hover:bg-error/10 dark:hover:bg-error-900/20 transition-colors"
                            :aria-expanded="openSection === 'delete'">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-error-700 dark:text-error-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div class="text-left">
                                <h3 class="text-base font-semibold text-error-700 dark:text-error-400">Zona Bahaya</h3>
                                <p class="text-sm text-error-600 dark:text-error-400/80">Hapus akun permanen</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-error-600 dark:text-error-400 transition-transform" 
                             :class="openSection === 'delete' && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="openSection === 'delete'" x-collapse>
                        <div class="p-5 sm:p-8 border-t border-error/20 dark:border-error-700/30">
                            @include('profile.partials.delete-user-form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-base-layout>
