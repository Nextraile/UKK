<x-base-layout 
    title="Detail Rental #{{ $rental->id }} - SewaKost"
    variant="full-width">
    
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <x-page-header 
                title="Detail Rental #{{ $rental->id }}"
                :breadcrumbs="[
                    ['label' => 'Rental', 'url' => route('admin.rentals.index')],
                    ['label' => 'Detail'],
                ]"
            >
                <x-slot:actions>
                    <a href="{{ route('admin.rentals.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
                        ← Kembali
                    </a>
                </x-slot:actions>
            </x-page-header>
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="mb-6 rounded-lg bg-success/10 p-4 dark:bg-success-900/20">
                    <p class="text-sm font-semibold text-success-700 dark:text-success-200">
                        {{ session('success') }}
                    </p>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 rounded-lg bg-error/10 p-4 dark:bg-error-900/20">
                    <p class="text-sm font-semibold text-error-700 dark:text-error-200">
                        {{ $errors->first() }}
                    </p>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Left Column (2/3 width) -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Rental Info Card -->
                    <x-card>
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                                Informasi Rental
                            </h3>
                            <x-status-badge :status="$rental->status" type="rental" size="md" />
                        </div>

                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                            
                            <div class="mb-4 rounded-lg bg-warning-light p-4 dark:bg-warning-900/20">
                                <p class="text-sm font-semibold text-warning-700 dark:text-warning-200">
                                    Tenant telah mengupload bukti pembayaran. Silakan verifikasi.
                                </p>
                            </div>

                            <div class="mb-4">
                                <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Bukti Pembayaran:</p>
                                <img src="{{ route('rentals.payment.proof', $rental) }}" 
                                     alt="Bukti pembayaran" 
                                     class="h-auto max-w-md rounded-lg border border-gray-300">
                            </div>

                            <div class="space-y-3">
                                <!-- Approve Form -->
                                <form method="POST" action="{{ route('admin.payments.approve', $rental->payment) }}">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full rounded-md bg-success-600 px-4 py-2 text-sm font-semibold text-white hover:bg-success-700"
                                            onclick="return confirm('Approve pembayaran ini?')">
                                        ✓ Approve Payment
                                    </button>
                                </form>

                                <!-- Reject Form (Modal trigger) -->
                                <button type="button"
                                        x-data
                                        @click="$dispatch('open-modal', 'reject-payment-{{ $rental->payment->id }}')"
                                        class="w-full rounded-md border border-error-600 px-4 py-2 text-sm font-semibold text-error-600 hover:bg-error-50 dark:hover:bg-error-900/20">
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
                            <div class="flex items-center rounded-lg bg-success/10 p-4 dark:bg-success-900/20">
                                <svg class="h-6 w-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="ml-3 text-sm font-semibold text-success-700 dark:text-success-200">
                                    Pembayaran terverifikasi pada {{ $rental->payment->verified_at->format('d M Y H:i') }}
                                </p>
                            </div>
                        </x-card>
                    @endif

                    <!-- Document Verification Section (Stub) -->
                    @if($rental->status === 'paid' || $rental->status === 'documents_pending')
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
                                    <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="font-semibold text-gray-900 dark:text-gray-100">
                                                    {{ $requirement->document_type }}
                                                    @if($requirement->is_required)
                                                        <span class="ml-1 text-error-600">*</span>
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
                                                <div class="flex gap-2">
                                                    <form method="POST" action="{{ route('admin.documents.approve', $document) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="text-sm text-primary-600 hover:text-primary-700 font-medium"
                                                                onclick="return confirm('Approve dokumen {{ $requirement->document_type }}?')">
                                                            Approve
                                                        </button>
                                                    </form>
                                                    <button type="button"
                                                            x-data
                                                            @click="$dispatch('open-modal', 'reject-document-{{ $document->id }}')"
                                                            class="text-sm text-error-600 hover:text-error-700 font-medium">
                                                        Reject
                                                    </button>
                                                </div>
                                            @endif
                                        </div>

                                        @if($document)
                                            <!-- Document Preview -->
                                            <div class="mt-3 border-t border-gray-200 pt-3 dark:border-gray-700">
                                                @if(str_ends_with($document->document_path, '.pdf'))
                                                    <a href="{{ route('admin.rentals.documents.show', $document) }}" 
                                                       target="_blank" 
                                                       class="inline-flex items-center gap-2 text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400">
                                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                                        </svg>
                                                        View PDF Document
                                                    </a>
                                                @else
                                                    <img src="{{ route('admin.rentals.documents.show', $document) }}" 
                                                         alt="{{ $requirement->document_type }}" 
                                                         class="max-w-xs rounded border border-gray-300 dark:border-gray-600">
                                                @endif
                                            </div>
                                        @endif

                                        @if($document && $document->verification_status === 'pending')
                                            <!-- Reject Document Modal -->
                                            <x-modal name="reject-document-{{ $document->id }}" focusable>
                                                <form method="POST" action="{{ route('admin.documents.reject', $document) }}" class="p-6">
                                                    @csrf
                                                    
                                                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                                        Reject Dokumen: {{ $requirement->document_type }}
                                                    </h2>
                                                    
                                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                        Berikan alasan penolakan dokumen (minimal 10 karakter).
                                                    </p>
                                                    
                                                    <div class="mt-6">
                                                        <x-input-label for="rejection_reason_{{ $document->id }}" value="Alasan Penolakan" />
                                                        <textarea id="rejection_reason_{{ $document->id }}" 
                                                                  name="rejection_reason" 
                                                                  rows="4" 
                                                                  required
                                                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                                                  placeholder="Contoh: Foto KTP tidak jelas / Data tidak sesuai / ..."></textarea>
                                                        <x-input-error :messages="$errors->get('rejection_reason')" class="mt-2" />
                                                    </div>
                                                    
                                                    <div class="mt-6 flex justify-end space-x-3">
                                                        <x-secondary-button x-on:click="$dispatch('close')">
                                                            Batal
                                                        </x-secondary-button>
                                                        
                                                        <x-danger-button type="submit">
                                                            Reject Document
                                                        </x-danger-button>
                                                    </div>
                                                </form>
                                            </x-modal>
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
                                <button class="block w-full rounded-md bg-success-600 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-success-700">
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
                                    <dd class="font-semibold text-error-600">{{ $rental->payment->expired_at->format('d M Y H:i') }}</dd>
                                </div>
                            @endif
                        </dl>
                    </x-card>
                </div>
            </div>
        </div>
    </div>
</x-base-layout>
