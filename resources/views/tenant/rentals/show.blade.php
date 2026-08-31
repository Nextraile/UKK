{{-- PAGE-008: Tenant Rental Detail (Unified Single-Page Experience) --}}
<x-base-layout 
    title="Rental #{{ $rental->id }} - SewaKost"
    variant="full-width">
    
    <div class="min-h-screen bg-gray-50 pb-24 lg:pb-12"
        x-data="{
            currentStep: {{ $currentStep }},
            sections: {
                payment: { 
                    state: '{{ $paymentState }}',
                    expanded: {{ $paymentState === 'active' ? 'true' : 'false' }}
                },
                documents: { 
                    state: '{{ $documentsState }}',
                    expanded: {{ $documentsState === 'active' ? 'true' : 'false' }}
                },
                timeline: { expanded: false }
            },
            paymentModal: false,
            cancelPaymentModal: false,
            paymentFile: null,
            paymentPreview: null,
            uploading: false,
            showQris: false,
            cancelModal: false,
            contactDropdown: false,
            modalTriggerElement: null,
            
            selectPaymentFile(event) {
                const file = event.target.files[0];
                if (!file) return;
                
                // Validate size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File terlalu besar. Maksimal 5MB.');
                    event.target.value = '';
                    return;
                }
                
                // Validate type
                if (!['image/jpeg', 'image/png', 'application/pdf'].includes(file.type)) {
                    alert('Hanya file JPG, PNG, atau PDF yang diterima.');
                    event.target.value = '';
                    return;
                }
                
                this.paymentFile = file;
                
                // Show preview for images
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.paymentPreview = e.target.result;
                    };
                    reader.readAsDataURL(file);
                } else {
                    this.paymentPreview = null;
                }
            },
            
            openPaymentModal() {
                this.modalTriggerElement = document.activeElement;
                this.paymentModal = true;
                this.$nextTick(() => {
                    this.$refs.paymentFileInput?.focus();
                });
            },
            
            closePaymentModal() {
                this.paymentModal = false;
                this.$nextTick(() => {
                    this.modalTriggerElement?.focus();
                });
            },
            
            async uploadPayment() {
                if (!this.paymentFile) return;
                
                this.uploading = true;
                
                const formData = new FormData();
                formData.append('payment_proof', this.paymentFile);
                
                try {
                    const response = await fetch('{{ route('tenant.rentals.payment.upload', $rental) }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (response.ok) {
                        // Close modal
                        this.closePaymentModal();
                        
                        // Show success message
                        alert('✓ Bukti pembayaran berhasil diupload. Menunggu verifikasi admin.');
                        
                        // Reload page to show updated data
                        setTimeout(() => window.location.reload(), 500);
                    } else {
                        alert(data.message || 'Upload gagal. Silakan coba lagi.');
                    }
                } catch (error) {
                    console.error('Upload error:', error);
                    alert('Terjadi kesalahan. Silakan coba lagi.');
                } finally {
                    this.uploading = false;
                }
            },
            
            async cancelPaymentUpload() {
                if (!confirm('Apakah Anda yakin ingin membatalkan upload ini? Status rental akan kembali ke pending dan Anda perlu upload ulang.')) {
                    return;
                }
                
                try {
                    const response = await fetch('{{ route('tenant.rentals.payment.cancel', $rental) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (response.ok) {
                        alert('✓ Upload dibatalkan. Silakan upload ulang bukti pembayaran yang benar.');
                        window.location.reload();
                    } else {
                        alert(data.message || 'Gagal membatalkan. Silakan coba lagi.');
                    }
                } catch (error) {
                    console.error('Cancel error:', error);
                    alert('Terjadi kesalahan. Silakan coba lagi.');
                }
            }
        }"
        x-init="
            $watch('paymentModal', (open) => {
                if (open) {
                    $nextTick(() => $refs.paymentFileInput?.focus());
                }
            });
        ">
        
        {{-- Mobile Header --}}
        <div class="sticky top-0 z-30 bg-white border-b border-gray-200 px-4 py-3 lg:hidden">
            <div class="flex items-center justify-between">
                <a href="{{ route('rentals.index') }}" class="flex items-center text-gray-600 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 rounded">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    <span class="text-sm font-medium">Kembali</span>
                </a>
                <h1 class="text-sm font-semibold text-gray-900">Rental #{{ $rental->id }}</h1>
                <button 
                    @click="contactDropdown = !contactDropdown"
                    class="p-2 text-gray-600 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Progress Stepper (Collapsed Chip) --}}
        <x-progress-stepper 
            :steps="$steps" 
            :current="$currentStep" 
            variant="collapsed"
            class="sticky top-14 z-20"
        />

        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            {{-- Desktop Header --}}
            <div class="hidden lg:block mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <a href="{{ route('rentals.index') }}" class="flex items-center text-gray-600 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 rounded">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            <span class="text-sm font-medium">Back to Rentals</span>
                        </a>
                        <h1 class="text-2xl font-bold text-gray-900">Rental #{{ $rental->id }}</h1>
                    </div>
                    <x-status-badge :status="$rental->status" type="rental" size="lg" />
                </div>
            </div>

            {{-- Main Layout: 70/30 Desktop, Single Column Mobile --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- Main Content (70% Desktop) --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    {{-- Rental Summary Card --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h2 class="text-xl font-bold text-gray-900 mb-1">
                                    {{ $rental->room->roomType->kost->name }}
                                </h2>
                                <p class="text-sm text-gray-600">
                                    {{ $rental->room->roomType->name }} - Kamar {{ $rental->room->code }}
                                </p>
                            </div>
                            <div class="lg:hidden">
                                <x-status-badge :status="$rental->status" type="rental" size="md" />
                            </div>
                        </div>

                        <dl class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <dt class="text-gray-600 mb-1">Durasi</dt>
                                <dd class="font-semibold text-gray-900">
                                    {{ $rental->duration_value }} {{ __($rental->duration_unit) }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-gray-600 mb-1">Tanggal Mulai</dt>
                                <dd class="font-semibold text-gray-900">
                                    {{ $rental->start_date->format('d M Y') }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-gray-600 mb-1">Tanggal Selesai</dt>
                                <dd class="font-semibold text-gray-900">
                                    {{ $rental->end_date->format('d M Y') }}
                                </dd>
                            </div>
                            <div class="text-gray-600 mb-1">
                                <dt class="text-gray-600">ID Rental</dt>
                                <dd class="font-semibold text-gray-900">#{{ $rental->id }}</dd>
                            </div>
                            <div class="text-gray-600 mb-1">
                                <dt class="text-gray-600">Dibuat</dt>
                                <dd class="font-semibold text-gray-900">{{ $rental->created_at->format('d M Y') }}</dd>
                            </div>
                            <div class="text-gray-600 mb-1">
                                <dt class="text-gray-600">Update Terakhir</dt>
                                <dd class="font-semibold text-gray-900">{{ $rental->updated_at->diffForHumans() }}</dd>
                            </div>
                        </dl>

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

                    {{-- Timeline Section (Mobile Collapsible) --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <button 
                            @click="sections.timeline.expanded = !sections.timeline.expanded"
                            class="w-full p-6 flex items-center justify-between lg:cursor-default"
                            :class="{ 'lg:pointer-events-none': true }">
                            <h3 class="text-lg font-bold text-gray-900">Riwayat Status</h3>
                            <svg 
                                class="w-5 h-5 text-gray-600 transition-transform lg:hidden"
                                :class="{ 'rotate-180': sections.timeline.expanded }"
                                fill="none" 
                                stroke="currentColor" 
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div 
                            x-show="sections.timeline.expanded || window.innerWidth >= 1024"
                            x-collapse.duration.300ms
                            class="px-6 pb-6">
                            <div class="space-y-4">
                                @foreach($rental->statusHistories->sortByDesc('created_at') as $history)
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
                                            <p class="font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $history->status)) }}</p>
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
                        </div>
                    </div>

                    {{-- Payment Section --}}
                    <div 
                        class="rounded-xl transition-all"
                        :class="{
                            'bg-white shadow-md': sections.payment.state === 'active',
                            'bg-gray-50 border border-gray-300': sections.payment.state === 'preview',
                            'bg-gray-100 border border-dashed border-gray-400 opacity-60': sections.payment.state === 'locked'
                        }">
                        
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                    Pembayaran
                                </h3>
                                @if($paymentState === 'preview' && in_array($rental->status, ['paid', 'confirmed', 'active', 'completed']))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-success-100 text-success-800">
                                        Terverifikasi
                                    </span>
                                @endif
                            </div>

                            {{-- ACTIVE State: Pending Payment --}}
                            @if($rental->status === 'pending')
                                @if($rental->payment->expired_at->isFuture())
                                    <div class="mb-4 rounded-lg bg-warning-50 border border-warning-200 p-4">
                                        <p class="text-sm font-semibold text-warning-800">
                                            Selesaikan pembayaran sebelum {{ $rental->payment->expired_at->format('d M Y H:i') }}
                                            <span class="text-warning-600">({{ $rental->payment->expired_at->diffForHumans() }})</span>
                                        </p>
                                    </div>
                                    
                                    <div class="space-y-4">
                                        <div class="mb-4">
                                            <p class="text-sm font-medium text-gray-700 mb-2">Total Pembayaran:</p>
                                            <p class="text-2xl font-bold text-gray-900">
                                                Rp {{ number_format((float) $rental->grand_total, 0, ',', '.') }}
                                            </p>
                                        </div>

                                        {{-- QRIS Code --}}
                                        @if($rental->room->roomType->kost->qris_image_path)
                                            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                                <p class="text-sm font-medium text-gray-700 mb-3">Scan QRIS untuk pembayaran:</p>
                                                <div class="flex justify-center">
                                                    <img src="{{ image_url($rental->room->roomType->kost->qris_image_path) }}" 
                                                         alt="QRIS Code" 
                                                         class="w-48 h-48 object-contain border border-gray-300 rounded-lg bg-white">
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Bank Account Information --}}
                                        @if($rental->room->roomType->kost->bank_name && $rental->room->roomType->kost->account_number)
                                            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                                <p class="text-sm font-medium text-gray-700 mb-3">Atau transfer ke rekening:</p>
                                                <div class="space-y-2">
                                                    <div class="flex justify-between items-center">
                                                        <span class="text-sm text-gray-600">Bank:</span>
                                                        <span class="text-sm font-semibold text-gray-900">{{ $rental->room->roomType->kost->bank_name }}</span>
                                                    </div>
                                                    <div class="flex justify-between items-center">
                                                        <span class="text-sm text-gray-600">No. Rekening:</span>
                                                        <span class="text-sm font-semibold text-gray-900">{{ $rental->room->roomType->kost->account_number }}</span>
                                                    </div>
                                                    <div class="flex justify-between items-center">
                                                        <span class="text-sm text-gray-600">Atas Nama:</span>
                                                        <span class="text-sm font-semibold text-gray-900">{{ $rental->room->roomType->kost->account_holder_name }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Preview Bukti Pembayaran (tepat setelah info rekening) --}}
                                        @if($rental->payment->proof_of_payment_path && !$rental->payment->verified_at)
                                            <div class="border border-blue-200 rounded-lg p-4 bg-blue-50">
                                                <div class="flex items-center gap-2 mb-3">
                                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    <p class="text-sm font-semibold text-blue-900">Bukti Pembayaran Telah Diupload</p>
                                                </div>
                                                <div class="mb-3">
                                                    @php
                                                        $extension = strtolower(pathinfo($rental->payment->proof_of_payment_path, PATHINFO_EXTENSION));
                                                        $isPdf = $extension === 'pdf';
                                                    @endphp
                                                    
                                                    @if($isPdf)
                                                        {{-- PDF Preview --}}
                                                        <div class="flex items-center justify-center p-8 bg-white rounded-lg border border-gray-300">
                                                            <div class="text-center">
                                                                <svg class="w-16 h-16 mx-auto mb-3 text-error-500" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                                                </svg>
                                                                <p class="text-sm font-medium text-gray-700 mb-2">Dokumen PDF</p>
                                                                <a href="{{ Storage::url($rental->payment->proof_of_payment_path) }}" 
                                                                   target="_blank"
                                                                   class="text-sm text-primary-600 hover:text-primary-700 underline">
                                                                    Lihat PDF
                                                                </a>
                                                            </div>
                                                        </div>
                                                    @else
                                                        {{-- Image Preview --}}
                                                        <a href="{{ Storage::url($rental->payment->proof_of_payment_path) }}" 
                                                           target="_blank"
                                                           class="block">
                                                            <img src="{{ Storage::url($rental->payment->proof_of_payment_path) }}" 
                                                                 alt="Bukti Pembayaran" 
                                                                 class="w-full rounded-lg border border-gray-300 shadow-sm hover:shadow-md transition-shadow cursor-pointer">
                                                        </a>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-2 text-sm text-blue-700 mb-3">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    <span>Menunggu verifikasi admin</span>
                                                </div>
                                                <p class="text-xs text-blue-600">
                                                    Uploaded: {{ $rental->payment->paid_at?->format('d M Y H:i') ?? 'N/A' }}
                                                </p>
                                            </div>
                                        @endif

                                        {{-- Payment Rejection Alert (FR-074) --}}
                                        @if($rental->payment->rejection_reason)
                                            <div class="border border-error-200 rounded-lg p-4 bg-error-50 mb-4">
                                                <div class="flex items-start gap-3">
                                                    <svg class="w-5 h-5 text-error-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    <div class="flex-1">
                                                        <p class="text-sm font-semibold text-error-900 mb-1">Bukti Pembayaran Ditolak</p>
                                                        <p class="text-sm text-error-800">{{ $rental->payment->rejection_reason }}</p>
                                                        <p class="text-xs text-error-600 mt-2">Silakan upload ulang bukti pembayaran dengan file yang sesuai.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        
                                        {{-- Button: Upload atau Batalkan --}}
                                        @if($rental->payment->proof_of_payment_path && !$rental->payment->verified_at)
                                            {{-- Tombol Batalkan Upload --}}
                                            <button 
                                                @click="cancelPaymentUpload()"
                                                type="button"
                                                class="w-full lg:w-auto px-6 py-3 text-sm font-semibold text-error-600 bg-white border-2 border-error-600 rounded-lg hover:bg-error-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-error-500 transition-colors">
                                                Batalkan Upload & Upload Ulang
                                            </button>
                                        @else
                                            {{-- Tombol Upload Bukti Pembayaran --}}
                                            <x-touch-button 
                                                variant="primary" 
                                                size="lg" 
                                                :fullWidthOnMobile="true"
                                                href="#"
                                                @click.prevent="openPaymentModal()">
                                                Upload Bukti Pembayaran
                                            </x-touch-button>
                                        @endif
                                    </div>
                                @else
                                    <div class="rounded-lg bg-error-50 border border-error-200 p-4">
                                        <p class="text-sm font-semibold text-error-800">
                                            Deadline pembayaran terlewati. Rental akan dibatalkan otomatis oleh sistem.
                                        </p>
                                    </div>
                                @endif
                            @endif

                            {{-- PREVIEW State: Payment Completed --}}
                            @if(in_array($rental->status, ['paid', 'documents_pending', 'confirmed', 'active', 'completed']))
                                <div class="space-y-4">

                                    {{-- Display Verified Payment Proof --}}
                                    @if($rental->payment->proof_of_payment_path)
                                        <div class="bg-white border border-gray-200 rounded-lg p-4">
                                            <p class="text-sm font-medium text-gray-700 mb-3">Bukti Pembayaran:</p>
                                            <div class="mb-3">
                                                @php
                                                    $extension = strtolower(pathinfo($rental->payment->proof_of_payment_path, PATHINFO_EXTENSION));
                                                    $isPdf = $extension === 'pdf';
                                                @endphp
                                                
                                                @if($isPdf)
                                                    {{-- PDF Preview --}}
                                                    <div class="flex items-center justify-center p-8 bg-white rounded-lg border border-gray-300">
                                                        <div class="text-center">
                                                            <svg class="w-16 h-16 mx-auto mb-3 text-error-500" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                                            </svg>
                                                            <p class="text-sm font-medium text-gray-700 mb-2">Dokumen PDF</p>
                                                            <a href="{{ Storage::url($rental->payment->proof_of_payment_path) }}" 
                                                               target="_blank"
                                                               class="text-sm text-primary-600 hover:text-primary-700 underline">
                                                                Lihat PDF
                                                            </a>
                                                        </div>
                                                    </div>
                                                @else
                                                    {{-- Image Preview --}}
                                                    <a href="{{ Storage::url($rental->payment->proof_of_payment_path) }}" 
                                                       target="_blank"
                                                       class="block">
                                                        <img src="{{ Storage::url($rental->payment->proof_of_payment_path) }}" 
                                                             alt="Bukti Pembayaran" 
                                                             class="w-full rounded-lg border border-gray-300 shadow-sm hover:shadow-md transition-shadow cursor-pointer">
                                                    </a>
                                                @endif
                                            </div>
                                            @if($rental->payment->paid_at)
                                                <p class="text-xs text-gray-600">
                                                    Uploaded: {{ $rental->payment->paid_at->format('d M Y H:i') }}
                                                </p>
                                            @endif
                                        </div>
                                    @endif
                                    </div>
                                    
                                {{-- Collapsible Details (Mobile) - Only show if payment is verified --}}
                                @if($rental->payment->verified_at)
                                <button 
                                    @click="sections.payment.expanded = !sections.payment.expanded"
                                    class="lg:hidden mt-3 w-full text-center text-sm text-primary-600 hover:text-primary-700 font-medium focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 rounded">
                                    <span x-show="!sections.payment.expanded">Lihat Detail ▼</span>
                                    <span x-show="sections.payment.expanded">Sembunyikan ▲</span>
                                </button>

                                <div 
                                    x-show="sections.payment.expanded"
                                    x-collapse
                                    class="mt-4 pt-4 border-t border-gray-200">
                                    @if($rental->payment->proof_path)
                                        <img 
                                            src="{{ Storage::url($rental->payment->proof_path) }}" 
                                            alt="Payment proof"
                                            class="w-full max-w-sm rounded-lg border border-gray-200 mb-3">
                                    @endif
                                    @if($rental->payment->notes)
                                        <p class="text-sm text-gray-600">
                                            <span class="font-medium">Catatan:</span> {{ $rental->payment->notes }}
                                        </p>
                                    @endif
                                </div>
                                @endif
                            @endif
                        </div>
                    </div>

                    {{-- Documents Section --}}
                    <div 
                        class="rounded-xl transition-all"
                        :class="{
                            'bg-white border-2 border-primary-500 shadow-md': sections.documents.state === 'active',
                            'bg-gray-50 border border-gray-300': sections.documents.state === 'preview',
                            'bg-gray-100 border border-dashed border-gray-400 opacity-60': sections.documents.state === 'locked'
                        }">
                        
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                    Dokumen Administrasi
                                </h3>
                                @if($documentsState === 'active')
                                    <span class="text-sm font-semibold text-primary-600">
                                        {{ $docProgress['uploaded'] }}/{{ $docProgress['total'] }}
                                    </span>
                                @endif
                            </div>

                            {{-- LOCKED State --}}
                            @if($documentsState === 'locked')
                                {{-- Preview: Dokumen yang Dibutuhkan --}}
                                @if($rental->room->roomType->kost->documentRequirements->count() > 0)
                                    <div class="mt-6 pt-6 border-t border-gray-300">
                                        <h4 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Dokumen yang Akan Dibutuhkan
                                        </h4>
                                        <div class="space-y-3">
                                            @foreach($rental->room->roomType->kost->documentRequirements as $requirement)
                                                <div class="flex items-start gap-3 p-3 bg-white rounded-lg border border-gray-200">
                                                    <div class="flex-shrink-0 mt-0.5">
                                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center gap-2 mb-1">
                                                            <h5 class="text-sm font-semibold text-gray-900">
                                                                {{ ucfirst(str_replace('_', ' ', $requirement->document_type)) }}
                                                            </h5>
                                                            @if($requirement->is_required)
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-error-100 text-error-800">
                                                                    Wajib
                                                                </span>
                                                            @else
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                                                    Opsional
                                                                </span>
                                                            @endif
                                                        </div>
                                                        @if($requirement->description)
                                                            <p class="text-xs text-gray-600 leading-relaxed">{{ $requirement->description }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                            <p class="text-xs text-blue-700">
                                                <strong>Catatan:</strong> Siapkan dokumen-dokumen di atas dalam format JPG, PNG, atau PDF (maksimal 5MB per file). Anda dapat menguploadnya setelah pembayaran terverifikasi.
                                            </p>
                                        </div>
                                    </div>
                                @endif
                            @endif

                            {{-- ACTIVE State: Upload Documents --}}
                            @if(in_array($rental->status, ['paid', 'documents_pending']))
                                <div class="space-y-4" x-data="documentUploader(@js($rental->room->roomType->kost->documentRequirements), @js($rental->rentalDocuments->keyBy('document_type')))">
                                    
                                    {{-- Unified Document Form (handles upload, replace, and delete) --}}
                                    <div>
                                        <p class="text-sm text-gray-600 mb-4">
                                            Upload atau kelola dokumen administrasi rental Anda. Centang "Hapus" untuk menghapus dokumen yang sudah diupload.
                                        </p>
                                        
                                        <form @submit.prevent="submitDocuments()" class="space-y-4">
                                            <template x-for="(req, index) in requirements" :key="req.document_type">
                                                <div class="border-2 rounded-xl p-4" 
                                                     :class="{
                                                         'border-success-500 bg-success-50': uploadedDocs[req.document_type]?.verified_at,
                                                         'border-error-500 bg-error-50': uploadedDocs[req.document_type]?.rejection_reason && !filesToDelete.includes(req.document_type),
                                                         'border-gray-300 bg-white': !uploadedDocs[req.document_type]?.verified_at && !uploadedDocs[req.document_type]?.rejection_reason,
                                                         'opacity-50': filesToDelete.includes(req.document_type)
                                                     }">
                                                    {{-- Header --}}
                                                    <div class="flex items-start justify-between gap-3 mb-3">
                                                        <div class="flex-1">
                                                            <h4 class="text-sm font-semibold text-gray-900 mb-1" x-text="req.document_type"></h4>
                                                            <p class="text-xs text-gray-600">
                                                                <span class="text-error-600 font-medium" x-show="req.is_required">Wajib</span>
                                                                <span class="text-gray-500" x-show="!req.is_required">Opsional</span>
                                                                · JPEG/PNG/PDF ≤ 5MB
                                                            </p>
                                                            <p class="text-xs text-gray-500 mt-1" x-show="req.description" x-text="req.description"></p>
                                                        </div>
                                                        
                                                        {{-- Status Badge --}}
                                                        <span x-show="uploadedDocs[req.document_type]?.verified_at" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-success-100 text-success-800">
                                                            ✓ Verified
                                                        </span>
                                                        <span x-show="uploadedDocs[req.document_type]?.rejection_reason && !filesToDelete.includes(req.document_type)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-error-100 text-error-800">
                                                            ✗ Rejected
                                                        </span>
                                                        <span x-show="uploadedDocs[req.document_type]?.document_path && !uploadedDocs[req.document_type]?.verified_at && !uploadedDocs[req.document_type]?.rejection_reason && !filesToDelete.includes(req.document_type)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-warning-100 text-warning-800">
                                                            Uploaded
                                                        </span>
                                                    </div>
                                                    
                                                    {{-- Rejection Reason --}}
                                                    <div x-show="uploadedDocs[req.document_type]?.rejection_reason && !filesToDelete.includes(req.document_type)" x-cloak class="mb-3 p-3 rounded-lg bg-error-50 border border-error-200">
                                                        <p class="text-xs text-error-800">
                                                            <span class="font-semibold">Alasan Ditolak:</span>
                                                            <span x-text="uploadedDocs[req.document_type]?.rejection_reason"></span>
                                                        </p>
                                                    </div>
                                                    
                                                    {{-- Current Document Preview (if exists and not marked for deletion) --}}
                                                    <div x-show="uploadedDocs[req.document_type]?.document_path && !filesToDelete.includes(req.document_type)" class="mb-3">
                                                        <p class="text-xs text-gray-600 mb-2">Dokumen saat ini:</p>
                                                        <a :href="'/storage/' + uploadedDocs[req.document_type]?.document_path" target="_blank" class="block">
                                                            <img :src="'/storage/' + uploadedDocs[req.document_type]?.document_path" 
                                                                 :alt="req.document_type"
                                                                 class="w-full h-32 object-cover rounded-lg border border-gray-200 hover:opacity-90 transition-opacity">
                                                        </a>
                                                    </div>
                                                    
                                                    {{-- New File Preview (if user selected new file) --}}
                                                    <div x-show="files[req.document_type]" x-cloak class="mb-3">
                                                        <p class="text-xs font-semibold text-primary-600 mb-2">
                                                            <span x-show="uploadedDocs[req.document_type]?.document_path">File baru (akan mengganti dokumen lama):</span>
                                                            <span x-show="!uploadedDocs[req.document_type]?.document_path">File dipilih:</span>
                                                        </p>
                                                        <div x-show="previews[req.document_type]">
                                                            <img :src="previews[req.document_type]" alt="Preview" class="w-full h-32 object-cover rounded-lg border-2 border-primary-500">
                                                        </div>
                                                        <div x-show="!previews[req.document_type]" class="flex items-center justify-center h-32 bg-gray-50 border-2 border-primary-500 rounded-lg">
                                                            <div class="text-center">
                                                                <svg class="w-8 h-8 mx-auto text-error-500 mb-1" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                                                </svg>
                                                                <p class="text-xs text-gray-600">Dokumen PDF</p>
                                                            </div>
                                                        </div>
                                                        <div class="mt-2 text-xs text-gray-600">
                                                            <span x-text="files[req.document_type]?.name"></span>
                                                            <span x-text="' (' + (files[req.document_type]?.size / 1024 / 1024).toFixed(2) + ' MB)'"></span>
                                                        </div>
                                                    </div>
                                                    
                                                    {{-- No document state (neither uploaded nor new file selected) --}}
                                                    <div x-show="!uploadedDocs[req.document_type]?.document_path && !files[req.document_type]" class="mb-3">
                                                        <div class="flex items-center justify-center h-32 bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg">
                                                            <div class="text-center">
                                                                <svg class="w-8 h-8 mx-auto text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                                </svg>
                                                                <p class="text-xs text-gray-500">Belum ada dokumen</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    {{-- File Input + Delete Checkbox (disabled for verified docs) --}}
                                                    <div x-show="!uploadedDocs[req.document_type]?.verified_at" class="space-y-2">
                                                        {{-- File Input --}}
                                                        <input 
                                                            type="file"
                                                            @change="selectFile($event, req.document_type)"
                                                            accept="image/jpeg,image/png,application/pdf"
                                                            capture="environment"
                                                            class="hidden"
                                                            :id="'doc-input-' + req.document_type"
                                                            :disabled="filesToDelete.includes(req.document_type)">
                                                        
                                                        <label 
                                                            :for="'doc-input-' + req.document_type"
                                                            class="block w-full text-center px-4 py-2 bg-white border-2 border-gray-300 text-gray-700 font-medium rounded-lg cursor-pointer hover:bg-gray-50 hover:border-gray-400 transition-colors"
                                                            :class="{ 'opacity-50 cursor-not-allowed': filesToDelete.includes(req.document_type) }">
                                                            <span x-show="!files[req.document_type] && !uploadedDocs[req.document_type]?.document_path">Pilih File</span>
                                                            <span x-show="files[req.document_type] || uploadedDocs[req.document_type]?.document_path">Ganti File</span>
                                                        </label>
                                                        
                                                        {{-- Delete Checkbox (only show if document exists) --}}
                                                        <label x-show="uploadedDocs[req.document_type]?.document_path" class="flex items-center gap-2 p-3 border-2 border-error-300 rounded-lg cursor-pointer hover:bg-error-50 transition-colors">
                                                            <input 
                                                                type="checkbox"
                                                                :value="req.document_type"
                                                                @change="toggleDelete(req.document_type)"
                                                                :checked="filesToDelete.includes(req.document_type)"
                                                                class="w-4 h-4 text-error-600 border-gray-300 rounded focus:ring-error-500">
                                                            <span class="text-sm font-medium text-error-700">Hapus dokumen ini</span>
                                                        </label>
                                                    </div>
                                                    
                                                    {{-- Verified document info --}}
                                                    <div x-show="uploadedDocs[req.document_type]?.verified_at" class="p-3 bg-success-100 border border-success-300 rounded-lg">
                                                        <p class="text-xs text-success-800">
                                                            Dokumen telah diverifikasi. Tidak dapat diubah atau dihapus.
                                                        </p>
                                                    </div>
                                                </div>
                                            </template>
                                            
                                            {{-- Submit Button --}}
                                            <button 
                                                type="submit"
                                                :disabled="uploading || (!hasChanges() && !allRequiredFilled())"
                                                class="w-full px-6 py-3 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                                <span x-show="!uploading">Simpan Perubahan</span>
                                                <span x-show="uploading" class="flex items-center justify-center gap-2">
                                                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    Menyimpan...
                                                </span>
                                            </button>
                                            
                                            <p class="text-xs text-gray-500 text-center">
                                                Perubahan akan diterapkan setelah menekan tombol "Simpan Perubahan"
                                            </p>
                                        </form>
                                        
                                        @if($docProgress['uploaded'] === $docProgress['total'] && $docProgress['total'] > 0)
                                            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                                <p class="text-sm text-blue-700">
                                                    Semua dokumen telah diupload. Menunggu verifikasi admin.
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            {{-- PREVIEW State: Documents Verified --}}
                            @if(in_array($rental->status, ['confirmed', 'active', 'completed']))
                                {{-- Document Preview Grid --}}
                                <div class="mt-4 space-y-3">
                                    @foreach($rental->rentalDocuments->where('verified_at', '!=', null) as $document)
                                        <div class="border-2 bg-white border-gray-200 border-success-200 bg-success-50 rounded-xl p-4">
                                            {{-- Header --}}
                                            <div class="flex items-start justify-between gap-3 mb-3">
                                                <div class="flex-1">
                                                    <h4 class="text-sm font-semibold text-gray-900 mb-1">{{ $document->document_type }}</h4>
                                                    <p class="text-xs text-gray-600">
                                                        Diverifikasi {{ $document->verified_at->diffForHumans() }}
                                                    </p>
                                                </div>
                                                
                                                {{-- Status Badge --}}
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-success-100 text-success-800">
                                                    Terverifikasi
                                                </span>
                                            </div>
                                            
                                            {{-- Document Preview --}}
                                            <div class="mb-3">
                                                <a href="/storage/{{ $document->document_path }}" target="_blank" class="block group relative">
                                                    @if(in_array(pathinfo($document->document_path, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png']))
                                                        <img src="/storage/{{ $document->document_path }}" 
                                                             alt="{{ $document->document_type }}"
                                                             class="w-full h-48 object-cover rounded-lg border border-success-300 group-hover:opacity-90 transition-opacity">
                                                    @else
                                                        <div class="flex items-center justify-center h-48 bg-white border-2 border-success-300 rounded-lg group-hover:bg-success-50 transition-colors">
                                                            <div class="text-center">
                                                                <svg class="w-12 h-12 mx-auto text-error-500 mb-2" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                                                </svg>
                                                                <p class="text-sm font-medium text-gray-700">Dokumen PDF</p>
                                                                <p class="text-xs text-gray-500 mt-1">Klik untuk melihat</p>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    
                                                    {{-- Hover Overlay --}}
                                                    <div class="absolute inset-0 bg-opacity-0 group-hover:bg-opacity-10 transition-all rounded-lg flex items-center justify-center">
                                                        <svg class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                        </svg>
                                                    </div>
                                                </a>
                                            </div>
                                            
                                            {{-- Download Button --}}
                                            <a href="/storage/{{ $document->document_path }}" 
                                               download
                                               class="block w-full text-center px-4 py-2 bg-white border-gray-200 border-2 border-success-300 font-medium rounded-lg hover:bg-success-50 transition-colors">
                                                <span class="flex items-center justify-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                    </svg>
                                                    Download Dokumen
                                                </span>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Review Section (Completed Status Only) --}}
                    @if($rental->status === 'completed')
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            @if($rental->review)
                                <h3 class="text-lg font-bold text-gray-900 mb-4">⭐ Ulasan Anda</h3>
                                
                                <div class="space-y-4">
                                    @if($rental->review->kost_rating)
                                        <div>
                                            <p class="text-sm font-medium text-gray-700 mb-2">Rating Kost</p>
                                            <div class="flex items-center gap-2">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg class="w-5 h-5 {{ $i <= $rental->review->kost_rating ? 'text-yellow-400' : 'text-gray-300' }} fill-current" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                @endfor
                                                <span class="text-sm text-gray-600">({{ $rental->review->kost_rating }}/5)</span>
                                            </div>
                                            @if($rental->review->kost_comment)
                                                <p class="text-sm text-gray-700 mt-2">{{ $rental->review->kost_comment }}</p>
                                            @endif
                                        </div>
                                    @endif
                                    
                                    <div class="flex gap-3">
                                        <x-touch-button 
                                            variant="secondary" 
                                            size="md" 
                                            :href="route('rentals.reviews.edit', $rental)">
                                            Edit Ulasan
                                        </x-touch-button>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                    <h3 class="text-lg font-bold text-gray-900 mb-2">Bagaimana pengalaman Anda?</h3>
                                    <p class="text-sm text-gray-600 mb-4">
                                        Bantu calon penyewa lain dengan memberikan ulasan
                                    </p>
                                    <x-touch-button 
                                        variant="primary" 
                                        size="md" 
                                        :href="route('rentals.reviews.create', $rental)">
                                        Tulis Ulasan
                                    </x-touch-button>
                                </div>
                            @endif
                        </div>
                    @endif

                </div>

                {{-- Sidebar (30% Desktop, Below Main on Mobile) --}}
                <div class="space-y-6 lg:sticky lg:top-6 lg:self-start">
                    
                    {{-- Progress Tracker (Desktop Only) --}}
                    <div class="hidden lg:block bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-4">Progress Rental</h3>
                        
                        <x-progress-stepper 
                            :steps="$steps" 
                            :current="$currentStep" 
                            variant="vertical" 
                        />
                    </div>

                    {{-- Contact Owner Card --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-4">Hubungi Pemilik</h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $rental->room->roomType->kost->owner->first_name }} {{ $rental->room->roomType->kost->owner->last_name }}
                                </p>
                                <p class="text-xs text-gray-600">{{ $rental->room->roomType->kost->owner->email }}</p>
                            </div>
                            
                            @if($rental->room->roomType->kost->owner->phone)
                                <a 
                                    href="tel:{{ $rental->room->roomType->kost->owner->phone }}"
                                    class="inline-flex items-center gap-2 text-sm text-primary-600 hover:text-primary-700 font-medium focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 rounded">
                                    {{ $rental->room->roomType->kost->owner->phone }}
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Actions Card --}}
                    @if($rental->canBeCancelled())
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h3 class="text-sm font-semibold text-gray-700 mb-4">Tindakan</h3>
                            <form method="GET" action="{{ route('rentals.cancel.form', $rental) }}">
                                <button 
                                    type="submit"
                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-white text-error-700 font-medium rounded-lg border-2 border-error-300 hover:bg-error-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-error-500 focus-visible:ring-offset-2 transition-colors">
                                    Batalkan Rental
                                </button>
                            </form>
                        </div>
                    @endif

                </div>

            </div>
        </div>

        {{-- Payment Upload Modal --}}
        <div x-show="paymentModal" 
             x-cloak
             x-trap.inert.noscroll="paymentModal"
             @keydown.escape.window="paymentModal = false"
             class="fixed inset-0 z-50 overflow-y-auto"
             role="dialog"
             aria-modal="true"
             aria-labelledby="payment-modal-title">
            
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-gray-900/50 transition-opacity"
                 @click="paymentModal = false"></div>
            
            {{-- Modal content --}}
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative w-full max-w-xl bg-white rounded-lg shadow-xl"
                     @click.stop>
                    
                    {{-- Header --}}
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <h3 id="payment-modal-title" class="text-lg font-semibold text-gray-900">Upload Bukti Pembayaran <span class="text-error-500">*</span></h3>
                            <button @click="closePaymentModal()" 
                                    class="text-gray-400 hover:text-gray-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 rounded-lg p-1"
                                    aria-label="Close modal">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <p class="mt-1 text-sm text-gray-600">
                            Total: <span class="font-semibold text-gray-900">Rp {{ number_format((float) $rental->grand_total, 0, ',', '.') }}</span>
                        </p>
                    </div>
                    
                    {{-- Body --}}
                    <div class="px-6 py-4 space-y-4">
                        {{-- File Upload --}}
                        <div>
                            <input type="file" 
                                   x-ref="paymentFileInput"
                                   @change="selectPaymentFile($event)"
                                   accept="image/*"
                                   capture="environment"
                                   required
                                   aria-required="true"
                                   class="hidden"
                                   id="payment-file-input">
                            <label for="payment-file-input"
                                   class="flex items-center justify-center w-full px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-primary-500 hover:bg-primary-50 transition-colors focus-within:ring-2 focus-within:ring-primary-500">
                                <span class="text-sm text-gray-600">Ambil Foto atau Upload</span>
                            </label>
                            
                            {{-- Preview --}}
                            <div x-show="paymentPreview" class="mt-3">
                                <img :src="paymentPreview" alt="Preview" class="w-full h-48 object-cover rounded-lg border border-gray-200">
                            </div>
                            
                            {{-- File info --}}
                            <div x-show="paymentFile" class="mt-2 text-xs text-gray-600">
                                <span x-text="paymentFile ? paymentFile.name : ''"></span>
                                <span x-text="paymentFile ? ' (' + (paymentFile.size / 1024 / 1024).toFixed(2) + ' MB)' : ''"></span>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Footer --}}
                    <div class="px-6 py-4 flex items-center justify-end gap-3">
                        <button @click="closePaymentModal()"
                                type="button"
                                :disabled="uploading"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            Batal
                        </button>
                        <button @click="uploadPayment()"
                                type="button"
                                :disabled="!paymentFile || uploading"
                                class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <span x-show="!uploading">Upload</span>
                            <span x-show="uploading" role="status" aria-live="polite" aria-atomic="true" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Mengupload...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- FAB: Contact Owner (Mobile Only) --}}
        <button 
            @click="contactDropdown = !contactDropdown"
            @keydown.enter.prevent="contactDropdown = !contactDropdown"
            @keydown.space.prevent="contactDropdown = !contactDropdown"
            class="lg:hidden fixed bottom-20 right-4 z-40 w-14 h-14 bg-primary-600 text-white rounded-full shadow-lg hover:bg-primary-700 focus:outline-none focus-visible:ring-4 focus-visible:ring-primary-300 flex items-center justify-center"
            aria-label="Contact owner">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
        </button>

        {{-- Mobile Bottom Action Bar --}}
        @if($rental->canBeCancelled())
            <div class="lg:hidden fixed bottom-0 left-0 right-0 z-30 bg-white border-t border-gray-200 px-4 py-3 safe-area-inset-bottom">
                <form method="GET" action="{{ route('rentals.cancel.form', $rental) }}" class="w-full">
                    <button 
                        type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-white text-error-700 font-semibold rounded-lg border-2 border-error-300 hover:bg-error-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-error-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Batalkan Rental
                    </button>
                </form>
            </div>
        @endif
        </div>

    @push('scripts')
    <script>
        function documentUploader(requirements, existingDocs) {
            return {
                requirements: requirements || [],
                uploadedDocs: existingDocs || {},
                files: {},
                previews: {},
                filesToDelete: [],
                uploading: false,
                
                init() {
                    console.log('Document uploader initialized', this.requirements, this.uploadedDocs);
                },
                
                allRequiredFilled() {
                    for (let req of this.requirements) {
                        if (req.is_required) {
                            const hasExisting = this.uploadedDocs[req.document_type]?.document_path && !this.filesToDelete.includes(req.document_type);
                            const hasNew = this.files[req.document_type];
                            if (!hasExisting && !hasNew) {
                                return false;
                            }
                        }
                    }
                    return true;
                },
                
                hasChanges() {
                    return Object.keys(this.files).length > 0 || this.filesToDelete.length > 0;
                },
                
                selectFile(event, docType) {
                    const file = event.target.files[0];
                    if (!file) return;
                    
                    // Validate size (5MB)
                    if (file.size > 5 * 1024 * 1024) {
                        alert('File terlalu besar. Maksimal 5MB.');
                        event.target.value = '';
                        return;
                    }
                    
                    // Validate type
                    const validTypes = ['image/jpeg', 'image/png', 'application/pdf'];
                    if (!validTypes.includes(file.type)) {
                        alert('Hanya file JPG, PNG, atau PDF yang diterima.');
                        event.target.value = '';
                        return;
                    }
                    
                    this.files[docType] = file;
                    
                    // Show preview for images
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.previews[docType] = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    } else {
                        this.previews[docType] = null;
                    }
                    
                    // If user selects new file, remove from delete list
                    const index = this.filesToDelete.indexOf(docType);
                    if (index > -1) {
                        this.filesToDelete.splice(index, 1);
                    }
                },
                
                toggleDelete(docType) {
                    const index = this.filesToDelete.indexOf(docType);
                    if (index > -1) {
                        this.filesToDelete.splice(index, 1);
                    } else {
                        this.filesToDelete.push(docType);
                        // Clear selected file if any
                        delete this.files[docType];
                        delete this.previews[docType];
                    }
                },
                
                async submitDocuments() {
                    if (!this.allRequiredFilled()) {
                        alert('Harap lengkapi semua dokumen wajib atau batalkan penghapusan dokumen wajib');
                        return;
                    }
                    
                    if (!this.hasChanges()) {
                        alert('Tidak ada perubahan untuk disimpan');
                        return;
                    }
                    
                    this.uploading = true;
                    const formData = new FormData();
                    
                    // Add new/replacement files
                    for (let [docType, file] of Object.entries(this.files)) {
                        formData.append('documents[' + docType + ']', file);
                    }
                    
                    // Add documents to delete
                    this.filesToDelete.forEach((docType, index) => {
                        formData.append('delete[' + index + ']', docType);
                    });
                    
                    try {
                        const response = await fetch(@json(route('tenant.rentals.documents.bulk-upload', $rental)), {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': @json(csrf_token()),
                                'Accept': 'application/json'
                            }
                        });
                        
                        const data = await response.json();
                        
                        if (response.ok) {
                            alert('✓ Perubahan dokumen berhasil disimpan');
                            window.location.reload();
                        } else {
                            alert(data.message || 'Simpan gagal. Silakan coba lagi.');
                        }
                    } catch (error) {
                        console.error('Submit error:', error);
                        alert('Terjadi kesalahan jaringan. Silakan coba lagi.');
                    } finally {
                        this.uploading = false;
                    }
                }
            }
        }
    </script>
    @endpush

</x-base-layout>
