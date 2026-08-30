<section>
    <header>
        <h2 class="text-lg font-medium text-text-strong dark:text-text-strong-dark">
            {{ __('Informasi Profil') }}
        </h2>

        <p class="mt-1 text-sm text-text dark:text-text-muted-dark">
            {{ __('Perbarui informasi akun dan email Anda.') }}
        </p>
    </header>

    @if ($errors->updateProfile->any())
        <div role="alert" aria-live="assertive" class="mt-4 rounded-md bg-error/10 border border-error/20 p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-error-700 dark:text-error-300 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-error-700 dark:text-error-300">
                        Terdapat {{ $errors->updateProfile->count() }} kesalahan pada formulir
                    </h3>
                    <ul class="mt-2 text-sm text-error-700 dark:text-error-300 space-y-1">
                        @foreach ($errors->updateProfile->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div x-data="{ saving: false }">
        <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-4" @submit="saving = true">
            @csrf
            @method('patch')

            {{-- First Name --}}
            <div>
                <x-input-label for="first_name" :value="__('Nama Depan')" />
                <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full" :value="old('first_name', $user->first_name)" required autofocus autocomplete="given-name" />
                <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
            </div>

            {{-- Last Name --}}
            <div>
                <x-input-label for="last_name">
                    Nama Belakang <span class="text-text-muted dark:text-text-muted-dark font-normal text-xs">(opsional)</span>
                </x-input-label>
                <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full" :value="old('last_name', $user->last_name)" autocomplete="family-name" />
                <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
            </div>

            {{-- Email --}}
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />

                <x-callout type="warning" class="mt-3">
                    <strong>Perhatian:</strong> Mengubah email memerlukan verifikasi ulang. 
                    Akses fitur rental akan diblokir sampai email baru diverifikasi.
                </x-callout>

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="mt-2">
                        <p class="text-sm text-text-strong dark:text-text-strong-dark">
                            {{ __('Email Anda belum terverifikasi.') }}
                        </p>
                        <x-verify-email-button class="mt-1" />
                    </div>
                @endif
            </div>

            {{-- Phone --}}
            <div>
                <x-input-label for="phone">
                    Nomor Telepon <span class="text-text-muted dark:text-text-muted-dark font-normal text-xs">(opsional)</span>
                </x-input-label>
                <x-text-input id="phone" name="phone" type="tel" class="mt-1 block w-full" :value="old('phone', $user->phone)" autocomplete="tel" />
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>

            <div class="flex items-center gap-4">
                <button type="submit" 
                        x-bind:disabled="saving"
                        class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150">
                    <span x-show="!saving">{{ __('Simpan') }}</span>
                    <span x-show="saving" class="inline-flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Menyimpan...
                    </span>
                </button>
            </div>
        </form>
    </div>
</section>
