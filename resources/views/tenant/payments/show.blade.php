<x-base-layout 
    title="Upload Bukti Pembayaran - SewaKost"
    variant="full-width">
    
    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <x-page-header 
                title="Upload Bukti Pembayaran"
                :breadcrumbs="[
                    ['label' => 'Rental', 'url' => route('rentals.index')],
                    ['label' => 'Detail Rental', 'url' => route('rentals.show', $rental)],
                    ['label' => 'Pembayaran'],
                ]"
            >
                <x-slot:actions>
                    <x-touch-button variant="secondary" size="md" :href="route('rentals.show', $rental)">
                        ← Kembali
                    </x-touch-button>
                </x-slot:actions>
            </x-page-header>
            <!-- Deadline Warning -->
            @if($rental->payment->expired_at->isFuture())
                <div class="mb-6 rounded-lg bg-warning-light p-3 sm:p-4 dark:bg-warning-900/20" role="status" aria-live="polite" aria-atomic="true">
                    <div class="flex">
                        <svg class="h-5 w-5 text-warning-700" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm font-semibold text-warning-700 dark:text-warning-200">
                                Selesaikan pembayaran sebelum {{ $rental->payment->expired_at->format('d M Y H:i') }}
                                ({{ $rental->payment->expired_at->diffForHumans() }})
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div class="mb-6 rounded-lg bg-error-light p-3 sm:p-4 dark:bg-error-900/20">
                    <p class="text-sm font-semibold text-error-700 dark:text-error-200">
                        Deadline pembayaran telah terlewati. Rental akan dibatalkan otomatis oleh sistem.
                    </p>
                </div>
            @endif

            <!-- Payment Amount -->
            <x-card class="mb-6">
                <div class="text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Pembayaran</p>
                    <p class="text-4xl font-bold text-primary-600">
                        Rp {{ number_format((float) $rental->grand_total, 0, ',', '.') }}
                    </p>
                </div>
            </x-card>

            <!-- QRIS & Bank Info -->
            <x-card class="mb-6">
                <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-gray-100">Informasi Pembayaran</h3>
                
                <!-- QRIS Image -->
                @if($rental->payment->qris_image_path)
                    <div class="mb-4 text-center">
                        <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Scan QRIS:</p>
                        <img src="{{ route('rentals.payment.qris', $rental) }}" 
                             alt="QRIS Payment" 
                             class="mx-auto w-full max-w-xs sm:w-64 h-auto rounded-lg border border-gray-300">
                    </div>
                @endif

                <!-- Bank Info (from kost config - FR-069) -->
                @php
                    $kost = $rental->room->roomType->kost;
                @endphp
                @if($kost->bank_name && $kost->account_number)
                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Atau transfer ke rekening:</p>
                        <div class="mt-2 space-y-1">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Bank</p>
                            <p class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $kost->bank_name }}</p>
                        </div>
                        <div class="mt-2 space-y-1">
                            <p class="text-sm text-gray-600 dark:text-gray-400">No. Rekening</p>
                            <p class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $kost->account_number }}</p>
                        </div>
                        @if($kost->account_holder_name)
                            <div class="mt-2 space-y-1">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Atas Nama</p>
                                <p class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $kost->account_holder_name }}</p>
                            </div>
                        @endif
                    </div>
                @endif
            </x-card>

            <!-- Upload Proof Section -->
            <x-card>
                <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-gray-100">Upload Bukti Pembayaran</h3>
                
                <!-- Show rejection reason if exists -->
                @if($rental->payment->rejection_reason)
                    <div class="mb-4 rounded-lg bg-error-light p-4 dark:bg-error-900/20">
                        <p class="text-sm font-semibold text-error-700 dark:text-error-200">
                            Bukti pembayaran ditolak:
                        </p>
                        <p class="mt-1 text-sm text-error-700 dark:text-error-300">
                            {{ $rental->payment->rejection_reason }}
                        </p>
                        <p class="mt-2 text-xs text-error-600 dark:text-error-400">
                            Silakan upload bukti pembayaran yang baru.
                        </p>
                    </div>
                @endif

                <!-- Show current proof if exists -->
                @if($rental->payment->proof_of_payment_path && !$rental->payment->rejection_reason)
                    <div class="mb-4 rounded-lg bg-primary-light p-4 dark:bg-primary-900/20">
                        <p class="text-sm font-semibold text-primary-700 dark:text-primary-200">
                            Bukti pembayaran sudah diupload. Menunggu verifikasi admin.
                        </p>
                        <img src="{{ route('rentals.payment.proof', $rental) }}" 
                             alt="Bukti Pembayaran" 
                             class="mt-2 h-48 w-auto rounded-lg border border-gray-300">
                    </div>
                @endif

                <!-- Upload Form -->
                <form method="POST" 
                      action="{{ route('rentals.payment.upload', $rental) }}" 
                      enctype="multipart/form-data"
                      class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="proof" value="Pilih file bukti transfer (JPEG, PNG, JPG, max 5MB)" />
                        <input type="file" 
                               id="proof" 
                               name="proof" 
                               accept="image/jpeg,image/png,image/jpg"
                               required
                               class="mt-1 block w-full text-sm text-gray-900 file:mr-4 file:rounded-md file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-700 hover:file:bg-primary-100 dark:text-gray-100">
                        <x-input-error :messages="$errors->get('proof')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end">
                        <x-touch-button variant="primary" size="md" type="submit">
                            {{ $rental->payment->proof_of_payment_path ? 'Upload Ulang' : 'Upload Bukti' }}
                        </x-touch-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-base-layout>
