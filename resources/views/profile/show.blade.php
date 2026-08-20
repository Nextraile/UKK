<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-text-strong dark:text-text-strong-dark leading-tight">
            {{ __('Profil Saya') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            {{-- Status message --}}
            @if (session('status'))
                <div class="mb-6 rounded-md bg-green-50 p-4 text-sm font-medium text-green-600 dark:bg-green-900/30 dark:text-green-400">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-surface-raised dark:bg-surface-raised-dark shadow-sm sm:rounded-lg overflow-hidden">
                {{-- Avatar and name header --}}
                <div class="px-4 py-6 sm:px-8 flex flex-col items-center sm:flex-row sm:items-center gap-6">
                    @if ($user->avatar_path)
                        <img src="{{ asset('storage/' . $user->avatar_path) }}"
                             alt="Avatar"
                             class="h-24 w-24 rounded-full object-cover border-4 border-indigo-100 dark:border-border-dark" />
                    @else
                        <div class="h-24 w-24 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center border-4 border-indigo-50 dark:border-border-dark">
                            <span class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">
                                {{ strtoupper(substr($user->first_name, 0, 1)) }}
                            </span>
                        </div>
                    @endif

                    <div class="text-center sm:text-left">
                        <h3 class="text-xl font-bold text-text-strong dark:text-text-strong-dark">
                            {{ $user->first_name }} {{ $user->last_name }}
                        </h3>
                        <p class="text-sm text-text dark:text-text-muted-dark mt-1">{{ $user->email }}</p>
                        <span class="inline-flex items-center mt-2 px-3 py-1 rounded-full text-xs font-semibold
                            @if ($user->isSuperAdmin()) bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300
                            @elseif ($user->isAdmin()) bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300
                            @else bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300
                            @endif">
                            @if ($user->isSuperAdmin())
                                Super Admin
                            @elseif ($user->isAdmin())
                                Admin
                            @else
                                Tenant
                            @endif
                        </span>
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
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                Terverifikasi
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.72-1.36 3.485 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0V8a1 1 0 112 0v5zm-1 3a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                                Belum Verifikasi
                            </span>
                            <a href="{{ route('verification.notice') }}"
                               class="inline-flex items-center ml-2 px-3 py-1.5 text-xs font-semibold rounded-md bg-indigo-600 text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Verifikasi Email
                            </a>
                        @endif
                    </div>

                    {{-- Phone --}}
                    <div class="px-4 sm:px-8 py-4">
                        <p class="text-sm font-medium text-text dark:text-text-muted-dark">Nomor Telepon</p>
                        <p class="mt-1 text-sm text-text-strong dark:text-text-strong-dark">
                            {{ $user->phone ?: 'Belum diisi' }}
                        </p>
                    </div>

                    {{-- First Name --}}
                    <div class="px-4 sm:px-8 py-4">
                        <p class="text-sm font-medium text-text dark:text-text-muted-dark">Nama Depan</p>
                        <p class="mt-1 text-sm text-text-strong dark:text-text-strong-dark">{{ $user->first_name }}</p>
                    </div>

                    {{-- Last Name --}}
                    <div class="px-4 sm:px-8 py-4">
                        <p class="text-sm font-medium text-text dark:text-text-muted-dark">Nama Belakang</p>
                        <p class="mt-1 text-sm text-text-strong dark:text-text-strong-dark">
                            {{ $user->last_name ?: 'Belum diisi' }}
                        </p>
                    </div>
                </div>

                {{-- Action --}}
                <div class="px-4 sm:px-8 py-4 bg-surface dark:bg-surface-dark/50 border-t border-border dark:border-border-dark">
                    <a href="{{ route('profile.edit') }}"
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit Profil
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
