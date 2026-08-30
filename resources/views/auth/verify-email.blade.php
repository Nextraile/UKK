<x-base-layout 
    title="Verifikasi Email - SewaKost"
    variant="centered-card">
    
    @if (isset($alreadyVerified) && $alreadyVerified)
        {{-- Already verified state --}}
        <div class="mb-6 text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-success-100">
                <svg class="h-6 w-6 text-success-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            </div>
            <h2 class="text-lg font-semibold text-text-strong dark:text-text-strong-dark">Email Anda sudah terverifikasi</h2>
            <p class="mt-2 text-sm text-text dark:text-text-dark">Akun Anda telah aktif sepenuhnya.</p>
        </div>

        <div class="mt-4">
            <a href="{{ auth()->user()->dashboardRoute() }}"
               class="inline-flex w-full justify-center rounded-md bg-gray-800 dark:bg-gray-200 px-4 py-2 text-sm font-semibold text-white dark:text-gray-800 hover:bg-gray-700 dark:hover:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                Lanjut ke Dashboard
            </a>
        </div>
    @else
        {{-- OTP entry state --}}
        <div class="mb-6 text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-primary-100">
                <svg class="h-6 w-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                </svg>
            </div>
            <h2 class="text-lg font-semibold text-text-strong dark:text-text-strong-dark">Verifikasi Email</h2>
            <p class="mt-2 text-sm text-text dark:text-text-dark">
                Kami telah mengirim kode OTP ke <span class="font-medium text-text-strong dark:text-text-strong-dark">{{ $maskedEmail ?? auth()->user()->email }}</span>
            </p>
        </div>

        @if (session('status'))
            <x-alert-banner variant="success" class="mb-4" dismissible>
                {{ session('status') }}
            </x-alert-banner>
        @endif

        @if (session('error'))
            <x-alert-banner variant="error" class="mb-4" dismissible>
                {{ session('error') }}
            </x-alert-banner>
        @endif

        @if ($errors->any())
            <x-alert-banner variant="error" class="mb-4" dismissible>
                {{ $errors->first() }}
            </x-alert-banner>
        @endif

        <div
            x-data="{
                otpCode: '',
                countdown: 60,
                intervalId: null,
                
                init() {
                    this.startCountdown();
                },
                
                startCountdown() {
                    this.intervalId = setInterval(() => {
                        if (this.countdown > 0) {
                            this.countdown--;
                        } else {
                            clearInterval(this.intervalId);
                        }
                    }, 1000);
                }
            }"
        >
            <form method="POST" action="{{ route('verification.verify') }}" @submit="submitForm($event)" x-ref="form">
                @csrf

                {{-- Helper text --}}
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 text-center">
                    Masukkan kode 6 digit
                </p>

                {{-- Single OTP input with letter spacing --}}
                <div class="mb-4">
                    <input
                        type="text"
                        inputmode="numeric"
                        maxlength="6"
                        x-model="otpCode"
                        @input="otpCode = $event.target.value.replace(/\D/g, '').slice(0, 6); if (otpCode.length === 6) { $nextTick(() => $refs.form.submit()); }"
                        autocomplete="one-time-code"
                        placeholder="● ● ● ● ● ●"
                        aria-label="Kode OTP 6 digit"
                        class="w-full max-w-sm mx-auto block h-14 px-4 text-center text-2xl font-semibold tracking-[0.75rem] 
                               text-text-strong dark:text-text-strong-dark 
                               bg-white dark:bg-gray-800 
                               border-2 border-border-strong dark:border-border-dark 
                               rounded-md shadow-xs 
                               focus:border-primary-500 focus:ring-2 focus:ring-primary-500 
                               placeholder:tracking-normal placeholder:text-gray-400"
                    />
                </div>

                {{-- Hidden field for form submission --}}
                <input type="hidden" name="otp_code" x-bind:value="otpCode" />
            </form>

            {{-- Resend OTP throttle --}}
            <div class="mt-4 text-center">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button
                        type="submit"
                        x-show="countdown <= 0"
                        x-cloak
                        class="text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300"
                    >
                        Kirim ulang OTP
                    </button>
                    <p x-show="countdown > 0" x-cloak class="text-sm text-gray-500 dark:text-gray-400" role="status" aria-live="polite" aria-atomic="true">
                        Kirim ulang tersedia dalam <span x-text="countdown" class="font-medium"></span> detik
                    </p>
                </form>
            </div>
        </div>
    @endif

    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-base-layout>
