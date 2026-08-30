<x-base-layout 
    title="Profil Saya - SewaKost"
    variant="full-width">
    
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <x-page-header 
                title="Profil Saya"
                :breadcrumbs="[
                    ['label' => 'Profil'],
                ]"
            >
                <x-slot:actions>
                    <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
                        Edit Profil
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

            <div class="bg-surface-raised dark:bg-surface-raised-dark shadow-sm sm:rounded-lg overflow-hidden">
                {{-- Avatar and name header --}}
                <div class="px-4 py-6 sm:px-8 flex flex-col items-center sm:flex-row sm:items-center gap-6">
                    @if ($user->avatar_path)
                        <img src="{{ asset('storage/' . $user->avatar_path) }}"
                             alt="Avatar"
                             class="h-24 w-24 rounded-full object-cover border-2 border-border dark:border-border-dark" />
                    @else
                        <div class="h-24 w-24 rounded-full bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center border-2 border-border dark:border-border-dark">
                            <span class="text-3xl font-bold text-primary-600 dark:text-primary-400">
                                {{ strtoupper(substr($user->first_name ?? $user->email, 0, 1)) }}
                            </span>
                        </div>
                    @endif

                    <div class="text-center sm:text-left">
                        <h3 class="text-xl font-bold text-text-strong dark:text-text-strong-dark">
                            {{ $user->first_name }} {{ $user->last_name }}
                        </h3>
                        <p class="text-sm text-text dark:text-text-muted-dark mt-1">{{ $user->email }}</p>
                        <x-role-badge :role="$user->role" class="mt-2" />
                    </div>
                </div>

                {{-- Detail rows --}}
                <div class="border-t border-border dark:border-border-dark divide-y divide-border dark:divide-border-dark">
                    {{-- Email --}}
                    <div class="px-4 sm:px-8 py-4 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-text dark:text-text-muted-dark">Email</p>
                            <p class="mt-1 text-sm text-text-strong dark:text-text-strong-dark">{{ $user->email }}</p>
                        </div>
                        @if ($user->email_verified_at)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900/40 dark:text-success-300">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                Terverifikasi
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-warning-100 text-warning-800 dark:bg-warning-900/40 dark:text-warning-300">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.72-1.36 3.485 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0V8a1 1 0 112 0v5zm-1 3a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                                Belum Verifikasi
                            </span>
                            <x-verify-email-button class="ml-2" />
                        @endif
                    </div>

                    {{-- Phone --}}
                    <div class="px-4 sm:px-8 py-4">
                        <p class="text-sm font-medium text-text dark:text-text-muted-dark">Nomor Telepon</p>
                        @if ($user->phone)
                            <p class="mt-1 text-sm text-text-strong dark:text-text-strong-dark">{{ $user->phone }}</p>
                        @else
                            <p class="mt-1 text-sm text-text-muted dark:text-text-muted-dark italic">
                                Belum diisi — 
                                <a href="{{ route('profile.edit') }}#profile-info" 
                                   class="font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                                    Tambah nomor
                                </a>
                            </p>
                        @endif
                    </div>

                    {{-- First Name --}}
                    <div class="px-4 sm:px-8 py-4">
                        <p class="text-sm font-medium text-text dark:text-text-muted-dark">Nama Depan</p>
                        <p class="mt-1 text-sm text-text-strong dark:text-text-strong-dark">{{ $user->first_name }}</p>
                    </div>

                    {{-- Last Name --}}
                    <div class="px-4 sm:px-8 py-4">
                        <p class="text-sm font-medium text-text dark:text-text-muted-dark">Nama Belakang</p>
                        @if ($user->last_name)
                            <p class="mt-1 text-sm text-text-strong dark:text-text-strong-dark">{{ $user->last_name }}</p>
                        @else
                            <p class="mt-1 text-sm text-text-muted dark:text-text-muted-dark italic">
                                Belum diisi — 
                                <a href="{{ route('profile.edit') }}#profile-info" 
                                   class="font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                                    Tambah nama belakang
                                </a>
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Action (Mobile only) --}}
                <div class="lg:hidden px-4 sm:px-8 py-4 bg-surface dark:bg-surface-dark/50 border-t border-border dark:border-border-dark">
                    <a href="{{ route('profile.edit') }}"
                       class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit Profil
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-base-layout>
