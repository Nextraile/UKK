<x-base-layout 
    title="Daftar - SewaKost"
    variant="centered-card">
    
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- First Name -->
        <div>
            <x-input-label for="first_name" :value="__('Nama Depan')" />
            <x-text-input id="first_name" class="block mt-1 w-full" type="text" name="first_name" :value="old('first_name')" required autofocus autocomplete="given-name" aria-describedby="first_name-error" />
            <x-input-error id="first_name-error" :messages="$errors->get('first_name')" class="mt-2" />
        </div>

        <!-- Last Name -->
        <div class="mt-4">
            <x-input-label for="last_name" :value="__('Nama Belakang (Opsional)')" />
            <x-text-input id="last_name" class="block mt-1 w-full" type="text" name="last_name" :value="old('last_name')" autocomplete="family-name" aria-describedby="last_name-error" />
            <x-input-error id="last_name-error" :messages="$errors->get('last_name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" aria-describedby="email-error" />
            <x-input-error id="email-error" :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-password-strength 
                inputId="password" 
                name="password" 
                label="Password" 
                :required="true" 
                autocomplete="new-password"
                :errors="$errors" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4" x-data="{ show: false }">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />

            <div class="relative mt-1">
                <input id="password_confirmation"
                       class="border-border-strong dark:border-border-dark dark:bg-surface-dark dark:text-text-dark focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-xs block w-full pr-10"
                       x-bind:type="show ? 'text' : 'password'"
                       name="password_confirmation"
                       required
                       autocomplete="new-password"
                       aria-describedby="password_confirmation-error" />

                <button
                    type="button"
                    class="absolute inset-y-0 right-0 flex items-center px-3 text-text hover:text-text-strong dark:text-text-muted-dark dark:hover:text-text-strong-dark focus:outline-none"
                    @click="show = !show"
                    x-bind:aria-label="show ? 'Hide password' : 'Show password'"
                >
                    <svg x-show="!show" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <svg x-show="show" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228L21 21" />
                    </svg>
                </button>
            </div>

            <x-input-error id="password_confirmation-error" :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Terms -->
        <div class="block mt-4">
            <label for="terms" class="inline-flex items-center">
                <input id="terms" type="checkbox" class="rounded dark:bg-surface-dark border-border-strong dark:border-border-dark text-primary-600 shadow-xs focus:ring-primary-500 dark:focus:ring-primary-600 dark:focus:ring-offset-surface-raised-dark" name="terms" required aria-describedby="terms-error" />
                <span class="ms-2 text-sm text-text dark:text-text-muted-dark">Setuju syarat dan ketentuan</span>
            </label>
            <x-input-error id="terms-error" :messages="$errors->get('terms')" class="mt-2" />
        </div>

        <!-- Submit -->
        <div class="mt-6">
            <x-primary-button class="w-full justify-center">
                Daftar
            </x-primary-button>
        </div>

        <!-- Links -->
        <div class="mt-6 text-center text-sm text-text dark:text-text-muted-dark">
            Sudah punya akun?
            <a class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300" href="{{ route('login') }}">
                Masuk
            </a>
        </div>
    </form>
</x-base-layout>
