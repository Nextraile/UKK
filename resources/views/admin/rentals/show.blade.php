<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Detail Rental #{{ $rental->id }}
            </h2>
            <a href="{{ route('admin.rentals.index') }}" class="text-sm text-primary-600 hover:text-primary-700">
                ← Kembali ke Daftar
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Left Column (2/3 width) -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Rental Info Card -->
                    <x-card>
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                                Informasi Rental
                            </h3>
                            <span class="inline-flex rounded-full px-4 py-2 text-sm font-semibold
                                @if($rental->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($rental->status === 'paid') bg-blue-100 text-blue-800
                                @elseif($rental->status === 'confirmed') bg-purple-100 text-purple-800
                                @elseif($rental->status === 'active') bg-green-100 text-green-800
                                @elseif($rental->status === 'completed') bg-gray-100 text-gray-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst($rental->status) }}
                            </span>
                        </div>

                        <dl class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Kost</dt>
                                <dd class="mt-1 text-base font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $rental->room->roomType->kost->name }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Kamar</dt>
                                <dd class="mt-1 text-base font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $rental->room->roomType->name }} - {{ $rental->room->name }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Tenant</dt>
                                <dd class="mt-1 text-base font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $rental->user->name }}
                                    <div class="text-xs font-normal text-gray-600 dark:text-gray-400">{{ $rental->user->email }}</div>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Durasi</dt>
                                <dd class="mt-1 text-base font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $rental->duration_value }} {{ __($rental->duration_unit) }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Tanggal Mulai</dt>
                                <dd class="mt-1 text-base font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $rental->start_date->format('d M Y') }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Tanggal Selesai</dt>
                                <dd class="mt-1 text-base font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $rental->end_date->format('d M Y') }}
                                </dd>
                            </div>
                            <div class="col-span-2">
                                <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Pembayaran</dt>
                                <dd class="mt-1 text-2xl font-bold text-primary-600">
                                    Rp {{ number_format((float) $rental->grand_total, 0, ',', '.') }}
                                </dd>
                                <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                    Harga: Rp {{ number_format((float) $rental->room_price, 0, ',', '.') }} x {{ $rental->duration_value }} + 
                                    Deposit: Rp {{ number_format((float) $rental->security_deposit, 0, ',', '.') }}
                                </div>
                            </div>
                        </dl>
                    </x-card>

                    <!-- Payment Verification Card -->
                    @if($rental->status === 'pending' && $rental->payment->proof_of_payment_path)
                        <x-card>
                            <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-gray-100">
                                Verifikasi Pembayaran
                            </h3>
                            
                            <div class="mb-4 rounded-lg bg-yellow-50 p-4 dark:bg-yellow-900/20">
                                <p class="text-sm font-semibold text-yellow-800 dark:text-yellow-200">
                                    Tenant telah mengupload bukti pembayaran. Silakan verifikasi.
                                </p>
                            </div>

                            <div class="mb-4">
                                <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Bukti Pembayaran:</p>
                                <img src="{{ Storage::url($rental->payment->proof_of_payment_path) }}" 
                                     alt="Bukti pembayaran" 
                                     class="h-auto max-w-md rounded-lg border border-gray-300">
                            </div>

                            <div class="space-y-3">
                                <!-- Approve Form -->
                                <form method="POST" action="{{ route('admin.payments.approve', $rental->payment) }}">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700"
                                            onclick="return confirm('Approve pembayaran ini?')">
                                        ✓ Approve Payment
                                    </button>
                                </form>

                                <!-- Reject Form (Modal trigger) -->
                                <button type="button"
                                        x-data
                                        @click="$dispatch('open-modal', 'reject-payment-{{ $rental->payment->id }}')"
                                        class="w-full rounded-md border border-red-600 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                                    ✗ Reject Payment
                                </button>
                            </div>
                        </x-card>

                        <!-- Reject Payment Modal -->
                        <x-modal name="reject-payment-{{ $rental->payment->id }}" focusable>
                            <form method="POST" action="{{ route('admin.payments.reject', $rental->payment) }}" class="p-6">
                                @csrf

                                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    Reject Pembayaran
                                </h2>

                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Berikan alasan penolakan pembayaran (wajib, minimal 10 karakter).
                                </p>

                                <div class="mt-6">
                                    <x-input-label for="rejection_reason" value="Alasan Penolakan" />
                                    <textarea id="rejection_reason" 
                                              name="rejection_reason" 
                                              rows="4" 
                                              required
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                              placeholder="Contoh: Bukti transfer tidak jelas / Jumlah nominal tidak sesuai / ..."></textarea>
                                    <x-input-error :messages="$errors->get('rejection_reason')" class="mt-2" />
                                </div>

                                <div class="mt-6 flex justify-end space-x-3">
                                    <x-secondary-button x-on:click="$dispatch('close')">
                                        Batal
                                    </x-secondary-button>

                                    <x-danger-button type="submit">
                                        Reject Payment
                                    </x-danger-button>
                                </div>
                            </form>
                        </x-modal>
                    @elseif($rental->payment->status === 'success')
                        <x-card>
                            <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-gray-100">Status Pembayaran</h3>
                            <div class="flex items-center rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="ml-3 text-sm font-semibold text-green-800 dark:text-green-200">
                                    Pembayaran terverifikasi pada {{ $rental->payment->verified_at->format('d M Y H:i') }}
                                </p>
                            </div>
                        </x-card>
                    @endif

                    <!-- Document Verification Section (Stub) -->
                    @if($rental->status === 'paid')
                        <x-card>
                            <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-gray-100">
                                Verifikasi Dokumen
                            </h3>
                            
                            <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                                Tenant perlu mengupload dokumen administrasi. Status saat ini:
                            </p>

                            <div class="space-y-2">
                                @foreach($rental->room->roomType->kost->documentRequirements as $requirement)
                                    @php
                                        $document = $rental->rentalDocuments->firstWhere('document_type', $requirement->document_type);
                                    @endphp
                                    <div class="flex items-center justify-between rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                                        <div>
                                            <p class="font-semibold text-gray-900 dark:text-gray-100">
                                                {{ $requirement->document_type }}
                                                @if($requirement->is_required)
                                                    <span class="ml-1 text-red-600">*</span>
                                                @endif
                                            </p>
                                            @if($document)
                                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                                    Status: {{ ucfirst($document->verification_status) }}
                                                </p>
                                            @else
                                                <p class="text-xs text-gray-500 italic">Belum diupload</p>
                                            @endif
                                        </div>
                                        @if($document && $document->verification_status === 'pending')
                                            <button class="text-sm text-primary-600 hover:text-primary-700">Verifikasi</button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </x-card>
                    @endif

                    <!-- Status History Timeline -->
                    <x-card>
                        <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-gray-100">Riwayat Status</h3>
                        <div class="relative">
                            @foreach($rental->statusHistories->reverse() as $history)
                                <div class="mb-4 flex">
                                    <div class="relative flex flex-col items-center">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full
                                            @if($loop->last) bg-primary-600 text-white
                                            @else bg-gray-300 text-gray-600
                                            @endif">
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        @if(!$loop->first)
                                            <div class="h-full w-0.5 bg-gray-300"></div>
                                        @endif
                                    </div>
                                    <div class="ml-4 flex-1 pb-4">
                                        <p class="font-semibold text-gray-900 dark:text-gray-100">
                                            {{ ucfirst($history->status) }}
                                        </p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $history->created_at->format('d M Y H:i') }}
                                            @if($history->changed_by !== 1)
                                                oleh {{ $history->user->name }}
                                            @else
                                                (sistem)
                                            @endif
                                        </p>
                                        @if($history->internal_notes)
                                            <p class="mt-1 text-sm italic text-gray-500">{{ $history->internal_notes }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-card>
                </div>

                <!-- Right Column (1/3 width) — Actions Sidebar -->
                <div class="space-y-6">
                    <!-- Quick Actions -->
                    <x-card>
                        <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-gray-100">Aksi Admin</h3>
                        <div class="space-y-3">
                            @if($rental->status === 'pending' && $rental->payment->proof_of_payment_path)
                                <button class="block w-full rounded-md bg-green-600 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-green-700">
                                    Verifikasi Pembayaran
                                </button>
                            @endif
                            
                            @if($rental->status === 'paid')
                                <button class="block w-full rounded-md bg-primary-600 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-primary-700">
                                    Verifikasi Dokumen
                                </button>
                            @endif
                        </div>
                    </x-card>

                    <!-- Tenant Contact Info -->
                    <x-card>
                        <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-gray-100">Kontak Tenant</h3>
                        <div class="space-y-2 text-sm">
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $rental->user->name }}</p>
                            <p class="text-gray-600 dark:text-gray-400">{{ $rental->user->email }}</p>
                            @if($rental->user->phone)
                                <p class="text-gray-600 dark:text-gray-400">{{ $rental->user->phone }}</p>
                            @endif
                        </div>
                    </x-card>

                    <!-- Rental Summary -->
                    <x-card>
                        <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-gray-100">Ringkasan</h3>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-600 dark:text-gray-400">ID Rental</dt>
                                <dd class="font-semibold">#{{ $rental->id }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600 dark:text-gray-400">Dibuat</dt>
                                <dd class="font-semibold">{{ $rental->created_at->format('d M Y') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600 dark:text-gray-400">Terakhir Update</dt>
                                <dd class="font-semibold">{{ $rental->updated_at->diffForHumans() }}</dd>
                            </div>
                            @if($rental->payment->expired_at)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600 dark:text-gray-400">Payment Deadline</dt>
                                    <dd class="font-semibold text-red-600">{{ $rental->payment->expired_at->format('d M Y H:i') }}</dd>
                                </div>
                            @endif
                        </dl>
                    </x-card>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
