@if (session('verify_email_prompt'))
    <x-modal name="verify-email" :show="true" focusable>
        <div class="relative px-6 py-8 text-center" role="dialog" aria-modal="true" aria-labelledby="verify-email-modal-title">
            <!-- Close -->
            <button
                type="button"
                x-on:click="$dispatch('close')"
                class="absolute top-4 right-4 rounded-lg p-1 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                aria-label="Tutup"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <!-- Icon -->
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>

            <h3 id="verify-email-modal-title" class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                Email Anda Belum Diverifikasi
            </h3>

            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Beberapa fitur, seperti membuat pemesanan, memerlukan email terverifikasi. Verifikasi sekarang untuk membuka seluruh fitur SewaKost.
            </p>

            <!-- CTA -->
            <a
                href="{{ route('verification.notice') }}"
                class="mt-6 inline-flex w-full items-center justify-center rounded-lg bg-indigo-600 px-6 py-3 font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                Verifikasi Email
            </a>

            <!-- Dismiss -->
            <button
                type="button"
                x-on:click="$dispatch('close')"
                class="mt-3 inline-flex w-full items-center justify-center rounded-lg px-6 py-3 text-sm font-semibold text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-300"
            >
                Nanti Saja
            </button>
        </div>
    </x-modal>
@endif