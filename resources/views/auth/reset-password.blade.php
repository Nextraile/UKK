<x-guest-layout>
    @if ($emailUnknown)
        {{-- State A: email unknown / no reset in progress — neutral message, no form --}}
        <div class="mb-6 text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-indigo-100">
                <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                </svg>
            </div>
            <h2 class="text-lg font-semibold text-gray-900">Reset Password</h2>
        </div>

        <div class="mb-4 rounded-md bg-gray-50 p-3 text-sm font-medium text-gray-600">
            Kode OTP telah dikirim ke email Anda jika alamat tersebut terdaftar.
        </div>

        <div class="mt-4">
            <a href="{{ route('password.request') }}"
               class="inline-flex w-full justify-center rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                Kembali
            </a>
        </div>
    @else
        {{-- State B: OTP entry --}}
        <div class="mb-6 text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-indigo-100">
                <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                </svg>
            </div>
            <h2 class="text-lg font-semibold text-gray-900">Reset Password</h2>
            <p class="mt-2 text-sm text-gray-600">
                Masukkan kode OTP 6 digit yang dikirim ke <span class="font-medium text-gray-900">{{ $maskedEmail }}</span>
            </p>
        </div>

        @if (session('status'))
            <div class="mb-4 rounded-md bg-green-50 p-3 text-sm font-medium text-green-600">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 rounded-md bg-red-50 p-3 text-sm font-medium text-red-600">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-md bg-red-50 p-3 text-sm font-medium text-red-600">
                {{ $errors->first() }}
            </div>
        @endif

        <div
            x-data="otpForm({
                expiresAt: @if(isset($expiresAt) && $expiresAt) '{{ $expiresAt->toIso8601String() }}' @else null @endif
            })"
            x-init="startCountdown()"
        >
            <form method="POST" action="{{ route('password.otp.verify') }}" @submit="submitForm($event)">
                @csrf

                {{-- 6 OTP digit inputs --}}
                <div class="mb-4 flex justify-between gap-2" role="group" aria-label="Kode OTP">
                    <template x-for="(digit, index) in 6" :key="index">
                        <input
                            type="text"
                            inputmode="numeric"
                            maxlength="1"
                            x-bind:aria-label="'Digit ' + (index + 1)"
                            class="w-12 h-14 text-center text-2xl font-semibold text-gray-900 border-gray-300 rounded-md shadow-xs focus:border-indigo-500 focus:ring-indigo-500"
                            x-bind:x-ref="'otp-' + index"
                            x-model="digits[index]"
                            @input="handleInput(index, $event)"
                            @keydown.backspace="handleBackspace(index, $event)"
                            @paste="handlePaste($event)"
                            @keydown.arrow-left="focusPrev(index)"
                            @keydown.arrow-right="focusNext(index)"
                            autocomplete="one-time-code"
                        />
                    </template>
                </div>

                {{-- Hidden combined OTP code field --}}
                <input type="hidden" name="otp_code" x-bind:value="combinedCode()" />

                {{-- Submit button --}}
                <div class="mt-6">
                    <button
                        type="submit"
                        class="inline-flex w-full justify-center rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150"
                        :disabled="!isComplete()"
                    >
                        Verifikasi
                    </button>
                </div>
            </form>

            {{-- Countdown timer --}}
            <div class="mt-4 text-center" aria-live="polite">
                <p x-show="countdown > 0" x-cloak class="text-sm text-gray-600">
                    Kode akan expired dalam <span x-text="formatTime(countdown)" class="font-medium text-gray-900"></span>
                </p>
                <p x-show="countdown <= 0" x-cloak class="text-sm text-red-600 font-medium">
                    Kode OTP telah expired
                </p>
            </div>

            {{-- Resend area: no dedicated resend route for reset flow --}}
            <div class="mt-4 text-center">
                <div x-show="countdown <= 0" x-cloak class="text-sm text-gray-600">
                    Kode expired? Silakan muat ulang halaman
                    <a href="{{ route('password.request') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                        /forgot-password
                    </a>
                    untuk mengirim kode baru.
                </div>
            </div>
        </div>
    @endif

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <script>
        function otpForm(data) {
            return {
                digits: ['', '', '', '', '', ''],
                countdown: 0,
                intervalId: null,

                init() {
                    if (data.expiresAt) {
                        this.startCountdown();
                    }
                    this.$nextTick(() => {
                        if (this.$refs['otp-0']) {
                            this.$refs['otp-0'].focus();
                        }
                    });
                },

                startCountdown() {
                    if (!data.expiresAt) {
                        return;
                    }
                    const target = new Date(data.expiresAt).getTime();
                    this.updateCountdown(target);
                    this.intervalId = setInterval(() => {
                        this.updateCountdown(target);
                        if (this.countdown <= 0) {
                            clearInterval(this.intervalId);
                        }
                    }, 1000);
                },

                updateCountdown(target) {
                    const diff = Math.floor((target - Date.now()) / 1000);
                    this.countdown = Math.max(0, diff);
                },

                formatTime(seconds) {
                    const m = Math.floor(seconds / 60).toString().padStart(2, '0');
                    const s = (seconds % 60).toString().padStart(2, '0');
                    return m + ':' + s;
                },

                handleInput(index, event) {
                    const value = event.target.value.replace(/\D/g, '');
                    this.digits[index] = value;
                    if (value && index < 5) {
                        this.focusNext(index);
                    }
                    if (this.isComplete()) {
                        this.$nextTick(() => {
                            event.target.form.requestSubmit();
                        });
                    }
                },

                handleBackspace(index, event) {
                    if (event.target.value === '' && index > 0) {
                        event.preventDefault();
                        this.focusPrev(index);
                    }
                },

                handlePaste(event) {
                    event.preventDefault();
                    const pasted = (event.clipboardData || window.clipboardData).getData('text');
                    const cleaned = pasted.replace(/\D/g, '').slice(0, 6);
                    for (let i = 0; i < 6; i++) {
                        this.digits[i] = cleaned[i] || '';
                    }
                    const lastFilled = Math.min(cleaned.length, 5);
                    this.$nextTick(() => {
                        if (this.$refs['otp-' + lastFilled]) {
                            this.$refs['otp-' + lastFilled].focus();
                        }
                    });
                    if (this.isComplete()) {
                        this.$nextTick(() => {
                            const form = event.target.closest('form');
                            form.requestSubmit();
                        });
                    }
                },

                focusNext(index) {
                    if (index < 5) {
                        const next = this.$refs['otp-' + (index + 1)];
                        if (next) next.focus();
                    }
                },

                focusPrev(index) {
                    if (index > 0) {
                        const prev = this.$refs['otp-' + (index - 1)];
                        if (prev) prev.focus();
                    }
                },

                combinedCode() {
                    return this.digits.join('');
                },

                isComplete() {
                    return this.digits.every(d => d !== '');
                },

                submitForm(event) {
                    if (!this.isComplete()) {
                        event.preventDefault();
                    }
                }
            };
        }
    </script>
</x-guest-layout>