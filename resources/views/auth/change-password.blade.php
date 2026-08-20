<x-guest-layout>
    @if (session('status'))
        <div class="mb-4 rounded-md bg-green-50 p-3 text-sm font-medium text-green-600">
            {{ session('status') }}
        </div>
    @endif

    {{-- Halaman ini hanya dapat diakses setelah OTP terverifikasi
         (controller guard session password_reset_verified). Email ditampilkan
         read-only sebagai konteks; nilai submit dikirim lewat hidden input
         agar tidak bisa diedit pengguna, karena backend memaksa email harus
         cocok dengan session password_reset_email. --}}
    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        {{-- Hidden email untuk submit (wajib cocok dengan session di backend) --}}
        <input type="hidden" name="email" value="{{ old('email', session('password_reset_email', '')) }}" />

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="block mt-1 w-full" type="email"
                          value="{{ session('password_reset_email', old('email')) }}"
                          disabled autocomplete="username" />
            <p class="mt-1 text-xs text-gray-500">Email penerima reset password ini tidak dapat diubah.</p>
        </div>

        <!-- Password -->
        <div class="mt-4" x-data="{ show: false }">
            <x-input-label for="password" value="Password Baru" />

            <div class="relative mt-1">
                <input id="password"
                       class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-xs block w-full pr-10"
                       x-bind:type="show ? 'text' : 'password'"
                       name="password"
                       required
                       autocomplete="new-password"
                       aria-describedby="password-error" />

                <button
                    type="button"
                    class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 focus:outline-none"
                    @click="show = !show"
                    x-bind:aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'"
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

            <x-input-error id="password-error" :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4" x-data="{ show: false }">
            <x-input-label for="password_confirmation" value="Konfirmasi Password Baru" />

            <div class="relative mt-1">
                <input id="password_confirmation"
                       class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-xs block w-full pr-10"
                       x-bind:type="show ? 'text' : 'password'"
                       name="password_confirmation"
                       required
                       autocomplete="new-password"
                       aria-describedby="password_confirmation-error" />

                <button
                    type="button"
                    class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 focus:outline-none"
                    @click="show = !show"
                    x-bind:aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'"
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

        <div class="mt-6">
            <x-primary-button class="w-full justify-center">
                {{ __('Simpan Password Baru') }}
            </x-primary-button>
        </div>
    </form>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-guest-layout>