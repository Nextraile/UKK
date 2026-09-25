<x-base-layout 
    title="Detail Rental #{{ $rental->id }} - SewaKost"
    variant="full-width">
    
    <div class="py-12" x-data="rentalVerification()">
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
            <!-- Toast Notification Area -->
            <div x-show="toast.show" 
                 x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="mb-6 rounded-lg p-4"
                 :class="{
                     'bg-success-50 border border-success-200': toast.type === 'success',
                     'bg-error-50 border border-error-200': toast.type === 'error',
                     'bg-warning-50 border border-warning-200': toast.type === 'warning'
                 }">
                <p class="text-sm font-semibold"
                   :class="{
                       'text-success-700': toast.type === 'success',
                       'text-error-700': toast.type === 'error',
                       'text-warning-700': toast.type === 'warning'
                   }"
                   x-text="toast.message"></p>
            </div>

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
                                    {{ $rental->room->roomType->name }} - Kamar {{ $rental->room->code }}
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
                                {{-- Breakdown Biaya --}}
                        <div class="mt-6 pt-4 border-t border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-900 mb-3">Rincian Biaya</h3>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Harga Sewa</dt>
                                    <dd class="font-medium text-gray-900">
                                        Rp {{ number_format((float) $rental->room_price, 0, ',', '.') }}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Durasi</dt>
                                    <dd class="font-medium text-gray-900">
                                        × {{ $rental->duration_value }} {{ __($rental->duration_unit) }}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Subtotal Sewa</dt>
                                    <dd class="font-medium text-gray-900">
                                        Rp {{ number_format((float) $rental->room_price * $rental->duration_value, 0, ',', '.') }}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Deposit Keamanan</dt>
                                    <dd class="font-medium text-gray-900">
                                        Rp {{ number_format((float) $rental->security_deposit, 0, ',', '.') }}
                                    </dd>
                                </div>
                                <div class="flex justify-between pt-2 mt-2 border-t-2 border-gray-300">
                                    <dt class="text-base font-bold text-gray-900">Total Pembayaran</dt>
                                    <dd class="text-base font-bold text-primary-600">
                                        Rp {{ number_format((float) $rental->grand_total, 0, ',', '.') }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                            </div>
                        </dl>
                    </x-card>

                    <!-- Status History Timeline -->
                    <x-card>
                        <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-gray-100">Riwayat Status</h3>
                        <div class="relative">
                            @foreach($rental->statusHistories->reverse() as $history)
                                    <div class="flex gap-4">
                                        <div class="flex flex-col items-center">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full
                                                @if($loop->first) bg-primary-600 text-white
                                                @else bg-gray-300 text-gray-600
                                                @endif">
                                                @if($loop->first)
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <circle cx="10" cy="10" r="3"/>
                                                    </svg>
                                                @endif
                                            </div>
                                            @if(!$loop->last)
                                                <div class="w-0.5 h-full min-h-[2rem] bg-gray-300 mt-1"></div>
                                            @endif
                                        </div>
                                        <div class="flex-1 pb-4">
                                            <p class="font-semibold text-gray-900">{{ ucfirst($history->status) }}</p>
                                            <p class="text-sm text-gray-600">
                                                {{ $history->created_at->format('d M Y H:i') }}
                                            </p>
                                            @if($history->internal_notes)
                                                <p class="mt-1 text-sm text-gray-500 italic">{{ $history->internal_notes }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                        </div>
                    </x-card>

                    <!-- Payment Verification Section -->
                    @if($rental->status === 'payment_pending' && $rental->payment->proof_of_payment_path)
                        <div class="border-2 rounded-lg p-6 transition-all"
                             :class="payment.verified_at || payment.rejected_at ? 'border-gray-300 bg-gray-50' : 'border-primary-500 bg-white'">
                            <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-gray-100">
                                Verifikasi Pembayaran
                            </h3>
                            
                            <div x-show="!payment.verified_at && !payment.rejected_at" 
                                 class="mb-4 rounded-lg bg-warning-50 border border-warning-200 p-4">
                                <p class="text-sm font-semibold text-warning-700">
                                    Tenant telah mengupload bukti pembayaran. Silakan verifikasi.
                                </p>
                            </div>

                            <!-- Payment Proof Display -->
                            <div class="mb-4">
                                <div class="flex justify-center">
                                    <img src="{{ route('rentals.payment.proof', $rental) }}" 
                                         alt="Bukti pembayaran" 
                                         @click="openLightbox('{{ route('rentals.payment.proof', $rental) }}')"
                                         class="h-auto max-w-md rounded-lg border border-gray-300 cursor-pointer hover:opacity-90 transition-opacity">
                                </div>
                                @if($rental->payment->notes)
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                        <strong>Catatan dari tenant:</strong> {{ $rental->payment->notes }}
                                    </p>
                                @endif
                            </div>

                            <!-- Verification Actions -->
                            <div x-show="!payment.verified_at && !payment.rejected_at" 
                                 class="mt-4 flex flex-col sm:flex-row gap-3">
                                <button @click="approvePayment()"
                                        :disabled="payment.verifying"
                                        class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-emerald-600 text-white font-semibold rounded-lg hover:bg-emerald-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                    <span x-show="!payment.verifying">Approve Payment</span>
                                    <span x-show="payment.verifying" class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Approving...
                                    </span>
                                </button>
                                <button @click="rejectingPayment = !rejectingPayment"
                                        class="flex-1 px-4 py-2 border-2 border-error-600 text-error-600 font-semibold rounded-lg hover:bg-error-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-error-500 transition-colors">
                                    Reject Payment
                                </button>
                            </div>

                            <!-- Inline Rejection Form -->
                            <div x-show="rejectingPayment" 
                                 x-collapse
                                 class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Alasan Penolakan <span class="text-error-600">*</span>
                                </label>
                                <textarea x-model="paymentRejectionReason"
                                          x-ref="paymentRejectTextarea"
                                          rows="3"
                                          placeholder="Contoh: Bukti transfer tidak jelas, nominal tidak sesuai, referensi bank salah..."
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                          minlength="10"></textarea>
                                <p class="text-xs text-gray-500 mt-1">Minimum 10 karakter</p>
                                
                                <div class="mt-3 flex gap-2">
                                    <button @click="rejectingPayment = false; paymentRejectionReason = ''"
                                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-500">
                                        Cancel
                                    </button>
                                    <button @click="confirmRejectPayment()"
                                            :disabled="paymentRejectionReason.length < 10"
                                            class="px-4 py-2 bg-error-600 text-white font-semibold rounded-lg hover:bg-error-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-error-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                        Confirm Rejection
                                    </button>
                                </div>
                            </div>

                            <!-- Approved State -->
                            <div x-show="payment.status === 'approved'"
                                 x-cloak
                                 class="mt-4 p-4 bg-success-50 border border-success-200 rounded-lg">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-success-100 text-success-800">
                                    Approved
                                </span>
                                <p class="text-sm text-gray-600 mt-2">
                                    Payment diverifikasi. Menunggu tenant upload dokumen.
                                </p>
                            </div>

                            <!-- Rejected State -->
                            <div x-show="payment.status === 'rejected'"
                                 x-cloak
                                 class="mt-4 p-4 bg-error-50 border border-error-200 rounded-lg">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-error-100 text-error-800">
                                    Rejected
                                </span>
                                <p class="text-sm text-error-700 mt-2" x-text="'Alasan: ' + payment.rejection_reason"></p>
                            </div>
                        </div>
                    @elseif($rental->payment->status === 'success')
                        <div class="border-2 bg-white border-gray-300 bg-gray-50 rounded-lg p-6">
                            <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-gray-100">Status Pembayaran</h3>
                            <div class="flex items-start gap-3 rounded-lg bg-success-50 border border-success-200 p-4">
                                <svg class="h-6 w-6 text-success-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <p class="text-sm font-semibold text-success-700">
                                        Payment Approved
                                    </p>
                                    <p class="text-xs text-gray-600 mt-1">
                                        Diverifikasi pada {{ $rental->payment->verified_at->format('d M Y H:i') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Document Verification Section -->
                    @if($rental->status === 'paid' || $rental->status === 'documents_pending')
                        <div class="border-2 rounded-lg p-6 transition-all"
                             :class="allDocumentsVerified() ? 'border-gray-300 bg-gray-50' : 'border-primary-500 bg-white'">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                    Verifikasi Dokumen
                                </h3>
                                
                                <!-- Bulk Actions (Desktop only) -->
                                <button @click="approveAllDocuments()"
                                        x-show="verifiedDocCount() < totalDocCount() && hasPendingDocs()"
                                        class="hidden lg:inline-flex items-center px-4 py-2 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 transition-colors">
                                    Approve All
                                </button>
                            </div>
                            
                            <div x-show="!anyDocumentUploaded()" 
                                 class="mb-4 rounded-lg bg-gray-50 border border-gray-200 p-4">
                                <p class="text-sm text-gray-600">
                                    Menunggu tenant mengupload dokumen administrasi.
                                </p>
                            </div>

                            <!-- Document Cards Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($rental->room->roomType->kost->documentRequirements as $requirement)
                                    @php
                                        $document = $rental->rentalDocuments->firstWhere('document_type', $requirement->document_type);
                                        $docIndex = $loop->index;
                                    @endphp
                                    
                                    <x-document-card 
                                        :requirement="$requirement"
                                        :document="$document"
                                        type="admin"
                                        :rentalId="$rental->id"
                                        :docIndex="$docIndex"
                                    />
                                @endforeach
                            </div>
                        </div>
                    @elseif($rental->status === 'confirmed' || $rental->status === 'active' || $rental->status === 'completed')
                        <div class="border-2 border-gray-300 bg-gray-50 rounded-lg p-6">
                            <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-gray-100">Dokumen Terverifikasi</h3>
                            <div class="flex items-start gap-3 rounded-lg bg-success-50 border border-success-200 p-4">
                                <svg class="h-6 w-6 text-success-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <p class="text-sm font-semibold text-success-700">
                                        All Documents Approved
                                    </p>
                                    <p class="text-xs text-gray-600 mt-1">
                                        Semua dokumen telah diverifikasi. Rental sudah dikonfirmasi.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Right Column (1/3 width) — Actions Sidebar -->
                <div class="space-y-6 lg:sticky lg:top-6">
                    <!-- Verification Stats -->
                    @if($rental->status === 'paid' || $rental->status === 'documents_pending')
                        <x-card>
                            <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-gray-100">Verification Stats</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Documents</span>
                                    <span class="text-sm font-semibold" x-text="verifiedDocCount() + '/' + totalDocCount()"></span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-primary-600 h-2 rounded-full transition-all" 
                                         :style="'width: ' + (totalDocCount() > 0 ? (verifiedDocCount() / totalDocCount() * 100) : 0) + '%'"></div>
                                </div>
                            </div>
                        </x-card>
                    @endif

                    <!-- Rental Summary -->
                    <x-card>
                        <h3 class="text-sm font-semibold text-gray-700 mb-4">Ringkasan</h3>
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

                    <!-- Tenant Contact Info -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-4">Hubungi Tenant</h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $rental->user->first_name }} {{ $rental->user->last_name }}
                                </p>
                                <p class="text-xs text-gray-600">{{ $rental->user->email }}</p>
                            </div>
                            
                            <a href="mailto:{{ $rental->user->email }}" 
                               class="mt-3 block w-full text-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 transition-colors">
                                Contact Tenant
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Lightbox Modal -->
    <div x-show="lightbox.show"
         x-cloak
         x-trap.inert.noscroll="lightbox.show"
         @click="lightbox.show = false"
         @keydown.escape.window="lightbox.show = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 px-4"
         role="dialog"
         aria-modal="true"
         aria-label="Image preview"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100">
        <div @click.stop class="relative max-w-4xl w-full">
            <button @click="lightbox.show = false" 
                    class="absolute -top-10 right-0 text-white hover:text-gray-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-white rounded"
                    aria-label="Close image preview">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <img :src="lightbox.url" alt="Full size" class="w-full h-auto rounded-lg">
        </div>
    </div>

    @push('scripts')
    <script>
        function rentalVerification() {
            return {
                // Payment state
                payment: {
                    id: {{ $rental->payment->id }},
                    status: @json($rental->payment->status),
                    verified_at: @json($rental->payment->verified_at),
                    rejected_at: @json($rental->payment->rejection_reason ? $rental->payment->verified_at : null),
                    rejection_reason: @json($rental->payment->rejection_reason),
                    verifying: false
                },
                rejectingPayment: false,
                paymentRejectionReason: '',

                // Documents state
                documents: [
                    @foreach($rental->room->roomType->kost->documentRequirements as $req)
                        @php
                            $doc = $rental->rentalDocuments->firstWhere('document_type', $req->document_type);
                        @endphp
                        {
                            id: {{ $doc?->id ?? 'null' }},
                            type: @json($req->document_type),
                            uploaded: {{ $doc && $doc->document_path ? 'true' : 'false' }},
                            verification_status: @json($doc?->verification_status ?? 'pending'),
                            verified_at: @json($doc?->verified_at),
                            rejected_at: @json($doc && $doc->verification_status === 'rejected' ? $doc->verified_at : null),
                            rejection_reason: @json($doc?->rejection_reason),
                            verifying: false
                        }{{ $loop->last ? '' : ',' }}
                    @endforeach
                ],
                rejectingDoc: null,
                rejectionReason: '',

                // Toast notifications
                toast: {
                    show: false,
                    type: 'success',
                    message: ''
                },

                // Lightbox
                lightbox: {
                    show: false,
                    url: ''
                },

                // Initialize
                init() {
                    // Show flash message if exists
                    @if(session('success'))
                        this.showToast('success', '{{ session('success') }}');
                    @elseif($errors->any())
                        this.showToast('error', '{{ $errors->first() }}');
                    @endif

                    // Auto-focus rejection textarea when opened
                    this.$watch('rejectingPayment', (value) => {
                        if (value) {
                            this.$nextTick(() => this.$refs.paymentRejectTextarea?.focus());
                        }
                    });
                },

                // Toast helpers
                showToast(type, message) {
                    this.toast = { show: true, type, message };
                    setTimeout(() => {
                        this.toast.show = false;
                    }, 5000);
                },

                // Lightbox
                openLightbox(url) {
                    this.lightbox = { show: true, url };
                },

                // Payment verification
                async approvePayment() {
                    if (this.payment.verifying) return;

                    // Optimistic update
                    this.payment.verifying = true;
                    this.payment.status = 'approved';
                    this.payment.verified_at = new Date().toISOString();

                    try {
                        const response = await fetch('{{ route('admin.rentals.payment.approve', $rental) }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.showToast('success', 'Payment approved successfully');
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            throw new Error(data.message || 'Approval failed');
                        }
                    } catch (error) {
                        // Rollback optimistic update
                        this.payment.status = 'pending';
                        this.payment.verified_at = null;
                        this.showToast('error', error.message || 'Approval failed. Please try again.');
                    } finally {
                        this.payment.verifying = false;
                    }
                },

                async confirmRejectPayment() {
                    if (this.paymentRejectionReason.length < 10) {
                        alert('Rejection reason must be at least 10 characters');
                        return;
                    }

                    // Optimistic update
                    this.payment.status = 'rejected';
                    this.payment.rejected_at = new Date().toISOString();
                    this.payment.rejection_reason = this.paymentRejectionReason;
                    this.rejectingPayment = false;

                    try {
                        const response = await fetch('{{ route('admin.rentals.payment.reject', $rental) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ 
                                rejection_reason: this.paymentRejectionReason 
                            })
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.showToast('success', 'Payment rejected, tenant notified');
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            throw new Error(data.message || 'Rejection failed');
                        }
                    } catch (error) {
                        // Rollback
                        this.payment.status = 'pending';
                        this.payment.rejected_at = null;
                        this.payment.rejection_reason = null;
                        this.showToast('error', error.message || 'Rejection failed. Please try again.');
                    }
                },

                // Document verification helpers
                verifiedDocCount() {
                    return this.documents.filter(d => d.verification_status === 'approved').length;
                },

                totalDocCount() {
                    return this.documents.length;
                },

                allDocumentsVerified() {
                    return this.verifiedDocCount() === this.totalDocCount();
                },

                anyDocumentUploaded() {
                    return this.documents.some(d => d.uploaded);
                },

                hasPendingDocs() {
                    return this.documents.some(d => d.uploaded && d.verification_status === 'pending');
                },

                // Document verification actions
                async approveDocument(docId) {
                    const doc = this.documents.find(d => d.id === docId);
                    if (!doc || doc.verifying) return;

                    // Optimistic update
                    doc.verification_status = 'approved';
                    doc.verified_at = new Date().toISOString();
                    doc.verifying = true;

                    try {
                        const response = await fetch(`/admin/rentals/documents/${docId}/approve`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.showToast('success', 'Document approved');
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            throw new Error(data.message || 'Approval failed');
                        }
                    } catch (error) {
                        // Rollback
                        doc.verification_status = 'pending';
                        doc.verified_at = null;
                        this.showToast('error', error.message || 'Approval failed. Please try again.');
                    } finally {
                        doc.verifying = false;
                    }
                },

                startReject(docId) {
                    this.rejectingDoc = docId;
                    this.rejectionReason = '';
                    this.$nextTick(() => this.$refs['rejectionTextarea' + docId]?.focus());
                },

                async confirmRejectDocument(docId) {
                    if (this.rejectionReason.length < 10) {
                        alert('Rejection reason must be at least 10 characters');
                        return;
                    }

                    const doc = this.documents.find(d => d.id === docId);
                    if (!doc) return;

                    // Optimistic update
                    doc.verification_status = 'rejected';
                    doc.rejected_at = new Date().toISOString();
                    doc.rejection_reason = this.rejectionReason;
                    this.rejectingDoc = null;

                    try {
                        const response = await fetch(`/admin/rentals/documents/${docId}/reject`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ 
                                rejection_reason: this.rejectionReason 
                            })
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.showToast('success', 'Document rejected, tenant notified');
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            throw new Error(data.message || 'Rejection failed');
                        }
                    } catch (error) {
                        // Rollback
                        doc.verification_status = 'pending';
                        doc.rejected_at = null;
                        doc.rejection_reason = null;
                        this.showToast('error', error.message || 'Rejection failed. Please try again.');
                    }
                },

                async approveAllDocuments() {
                    const pendingDocs = this.documents.filter(d => d.uploaded && d.verification_status === 'pending');
                    
                    if (pendingDocs.length === 0) return;

                    if (!confirm(`Approve all ${pendingDocs.length} documents?`)) return;

                    // Optimistic update
                    pendingDocs.forEach(doc => {
                        doc.verification_status = 'approved';
                        doc.verified_at = new Date().toISOString();
                    });

                    try {
                        const response = await fetch('{{ route('admin.rentals.documents.approve-all', $rental) }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.showToast('success', 'All documents approved');
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            throw new Error(data.message || 'Bulk approval failed');
                        }
                    } catch (error) {
                        // Rollback
                        pendingDocs.forEach(doc => {
                            doc.verification_status = 'pending';
                            doc.verified_at = null;
                        });
                        this.showToast('error', error.message || 'Bulk approval failed. Try individually.');
                    }
                }
            }
        }
    </script>
    @endpush
</x-base-layout>
