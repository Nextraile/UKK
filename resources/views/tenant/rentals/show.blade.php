<x-app-layout>
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <x-page-header 
                title="Detail Rental #{{ $rental->id }}"
                :breadcrumbs="[
                    ['label' => 'Dashboard', 'url' => route('dashboard')],
                    ['label' => 'Rental', 'url' => route('rentals.index')],
                    ['label' => 'Detail'],
                ]"
            >
                <x-slot:actions>
                    <x-touch-button variant="secondary" size="md" :href="route('rentals.index')">
                        ← Kembali
                    </x-touch-button>
                </x-slot:actions>
            </x-page-header>
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Left Column (2/3 width on desktop) -->
                <div class="space-y-6 lg:col-span-2 order-2 lg:order-1">
                    <!-- Rental Info Card -->
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                                {{ $rental->room->roomType->kost->name }}
                            </h3>
                            <x-status-badge :status="$rental->status" type="rental" size="md" />
                        </div>

                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Kamar</dt>
                                <dd class="mt-1 text-base font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $rental->room->roomType->name }} - {{ $rental->room->name }}
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
                    <div class="col-span-1 sm:col-span-2">
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
                    </div>

                    {{-- Cancel Button --}}
                    @if(!in_array($rental->status, ['cancelled', 'completed', 'active']) && !$rental->start_date->isPast())
                        @can('cancel', $rental)
                            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                                            Batalkan Rental
                                        </h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                            Anda dapat membatalkan rental ini sebelum tanggal mulai ({{ $rental->start_date->format('d M Y') }}).
                                        </p>
                                        <x-touch-button 
                                            variant="danger" 
                                            size="md" 
                                            :href="route('rentals.cancel.form', $rental)">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            Batalkan Rental
                                        </x-touch-button>
                                    </div>
                                </div>
                            </div>
                        @endcan
                    @endif

                    <!-- Status History Timeline (Vertical Stepper) -->
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-gray-100">Riwayat Status</h3>
                        <div class="relative">
                            @foreach($rental->statusHistories->reverse() as $history)
                                <div class="mb-4 flex">
                                    <!-- Timeline Line -->
                                    <div class="relative flex flex-col items-center">
                                        <div class="flex h-10 w-10 sm:h-10 sm:w-10 items-center justify-center rounded-full
                                            @if($loop->last) bg-primary-600 text-white
                                            @else bg-gray-300 text-gray-600
                                            @endif">
                                            <svg class="h-6 w-6 sm:h-5 sm:w-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        @if(!$loop->first)
                                            <div class="h-full w-0.5 bg-gray-300"></div>
                                        @endif
                                    </div>
                                    <!-- Content -->
                                    <div class="ml-4 flex-1 pb-4">
                                        <p class="font-semibold text-gray-900 dark:text-gray-100">
                                            {{ ucfirst($history->status) }}
                                        </p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $history->created_at->format('d M Y H:i') }}
                                            @if($history->changed_by && $history->changed_by !== 1)
                                                oleh {{ $history->user->first_name }}
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
                    </div>

                    <!-- Payment Section -->
                    @if($rental->status === 'pending')
                        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                            <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-gray-100">Pembayaran</h3>
                            
                            @if($rental->payment->expired_at->isFuture())
                                <div class="mb-4 rounded-lg bg-warning-light p-4 dark:bg-warning-900/20">
                                    <p class="text-sm font-semibold text-warning-700 dark:text-warning-200">
                                        Selesaikan pembayaran sebelum {{ $rental->payment->expired_at->format('d M Y H:i') }}
                                        ({{ $rental->payment->expired_at->diffForHumans() }})
                                    </p>
                                </div>
                                
                                <div class="text-center">
                                    <x-touch-button 
                                        variant="primary" 
                                        size="md" 
                                        :href="route('rentals.payment.show', $rental)">
                                        Upload Bukti Pembayaran
                                    </x-touch-button>
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                        Transfer ke rekening yang tertera, kemudian upload bukti transfer
                                    </p>
                                </div>
                            @else
                                <div class="rounded-lg bg-error-light p-4 dark:bg-error-900/20">
                                    <p class="text-sm font-semibold text-error-700 dark:text-error-200">
                                        Deadline pembayaran terlewati. Rental akan dibatalkan otomatis oleh sistem.
                                    </p>
                                </div>
                            @endif
                        </div>
                    @elseif(in_array($rental->status, ['paid', 'confirmed', 'active', 'completed']))
                        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                            <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-gray-100">Status Pembayaran</h3>
                            <div class="flex items-center rounded-lg bg-success-light p-4 dark:bg-success-900/20">
                                <svg class="h-6 w-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="ml-3 text-sm font-semibold text-success-700 dark:text-success-200">
                                    @if($rental->payment->verified_at)
                                        Pembayaran terverifikasi pada {{ $rental->payment->verified_at->format('d M Y H:i') }}
                                    @else
                                        Pembayaran telah diterima, menunggu verifikasi admin
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endif

                    <!-- Review Section (only for completed rentals) -->
                    @if($rental->status === 'completed')
                        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                            @if($rental->review)
                                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Ulasan Anda</h3>
                                
                                <div class="space-y-3">
                                    @if($rental->review->kost_rating)
                                        <div>
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rating Kost</p>
                                            <div class="flex items-center gap-1">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg class="w-5 h-5 {{ $i <= $rental->review->kost_rating ? 'text-yellow-400' : 'text-gray-300' }} fill-current" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                @endfor
                                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">({{ $rental->review->kost_rating }}/5)</span>
                                            </div>
                                            @if($rental->review->kost_comment)
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ $rental->review->kost_comment }}</p>
                                            @endif
                                        </div>
                                    @endif
                                    
                                    @if($rental->review->room_rating)
                                        <div>
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rating Kamar</p>
                                            <div class="flex items-center gap-1">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg class="w-5 h-5 {{ $i <= $rental->review->room_rating ? 'text-yellow-400' : 'text-gray-300' }} fill-current" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                @endfor
                                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">({{ $rental->review->room_rating }}/5)</span>
                                            </div>
                                            @if($rental->review->room_comment)
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ $rental->review->room_comment }}</p>
                                            @endif
                                        </div>
                                    @endif
                                    
                                    @if($rental->review->review_images && count($rental->review->review_images) > 0)
                                        <div>
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Foto</p>
                                            <!-- Horizontal scroll with proper spacing on mobile -->
                                            <div class="overflow-x-auto -mx-6 px-6 sm:mx-0 sm:px-0 scroll-smooth">
                                                <div class="flex gap-3 pb-3 min-w-max">
                                                    @foreach($rental->review->review_images as $imagePath)
                                                        <img src="{{ Storage::url($imagePath) }}" alt="Review image" class="w-20 h-20 flex-shrink-0 object-cover rounded border border-gray-200 dark:border-gray-700">
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="mt-4 flex gap-2">
                                    <x-touch-button 
                                        variant="secondary" 
                                        size="md" 
                                        :href="route('rentals.reviews.edit', $rental)">
                                        Edit Ulasan
                                    </x-touch-button>
                                    <form method="POST" action="{{ route('rentals.reviews.destroy', $rental) }}" onsubmit="return confirm('Yakin ingin menghapus ulasan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-touch-button variant="danger" size="md" type="submit">
                                            Hapus Ulasan
                                        </x-touch-button>
                                    </form>
                                </div>
                            @else
                                <div class="text-center py-6">
                                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">Bagaimana pengalaman Anda?</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                        Bantu calon penyewa lain dengan memberikan ulasan tentang kost ini
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

                    <!-- Document Section -->
                    @if($rental->status === 'paid' || $rental->status === 'documents_pending')
                        <div id="document-section" class="rounded-lg bg-white p-6 shadow dark:bg-gray-800"
                            x-data="{
                                docs: @js($rental->room->roomType->kost->documentRequirements->map(function($req) use ($rental) {
                                    $doc = $rental->rentalDocuments->firstWhere('document_type', $req->document_type);
                                    return [
                                        'key' => $req->document_type,
                                        'label' => $req->document_type,
                                        'required' => $req->is_required,
                                        'reason' => $req->reason,
                                        'status' => $doc ? $doc->verification_status : 'pending',
                                        'rejection_reason' => $doc ? $doc->rejection_reason : null
                                    ];
                                })),
                                uploads: {},
                                progress: {},
                                uploading: false,
                                dragOver: false,
                                accept: ['image/jpeg', 'image/png', 'application/pdf'],
                                maxSize: 5 * 1024 * 1024,
                                validate(file) {
                                    return this.accept.includes(file.type) && file.size <= this.maxSize;
                                },
                                pick(key, e) {
                                    this.add(key, e.target.files[0]);
                                    e.target.value = '';
                                },
                                add(key, file) {
                                    if (!file) return;
                                    if (!this.validate(file)) {
                                        this.showError('File harus JPEG/PNG/PDF dan maksimal 5MB');
                                        return;
                                    }
                                    this.uploads[key] = {
                                        name: file.name,
                                        size: file.size,
                                        type: file.type,
                                        file: file,
                                        preview: file.type.startsWith('image/') ? URL.createObjectURL(file) : null
                                    };
                                    // Auto-upload removed - files stay in preview until uploadAll() called
                                },
                                uploadFile(key) {
                                    return new Promise((resolve, reject) => {
                                        const formData = new FormData();
                                        formData.append('document_type', key);
                                        formData.append('file', this.uploads[key].file);
                                        
                                        this.progress[key] = 0;
                                        
                                        const xhr = new XMLHttpRequest();
                                        
                                        xhr.upload.addEventListener('progress', (e) => {
                                            if (e.lengthComputable) {
                                                this.progress[key] = Math.round((e.loaded / e.total) * 100);
                                            }
                                        });
                                        
                                        xhr.addEventListener('load', () => {
                                            if (xhr.status === 200 || xhr.status === 302) {
                                                resolve();
                                            } else {
                                                this.showError(`Upload ${this.uploads[key].name} gagal. Silakan coba lagi.`);
                                                delete this.uploads[key];
                                                delete this.progress[key];
                                                reject(new Error('Upload failed'));
                                            }
                                        });
                                        
                                        xhr.addEventListener('error', () => {
                                            this.showError(`Terjadi kesalahan pada ${this.uploads[key].name}. Silakan coba lagi.`);
                                            delete this.uploads[key];
                                            delete this.progress[key];
                                            reject(new Error('Network error'));
                                        });
                                        
                                        xhr.open('POST', '{{ route('rentals.documents.upload', $rental) }}');
                                        xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
                                        xhr.send(formData);
                                    });
                                },
                                async uploadAll() {
                                    this.uploading = true;
                                    const keys = Object.keys(this.uploads);
                                    
                                    try {
                                        for (const key of keys) {
                                            await this.uploadFile(key);
                                        }
                                        // All uploads complete
                                        window.location.reload();
                                    } catch (error) {
                                        // Error already shown in uploadFile, just stop uploading
                                        this.uploading = false;
                                    }
                                },
                                remove(key) {
                                    if (this.uploads[key]?.preview) {
                                        URL.revokeObjectURL(this.uploads[key].preview);
                                    }
                                    delete this.uploads[key];
                                    delete this.progress[key];
                                },
                                showError(msg) {
                                    alert(msg);
                                }
                            }"
                            x-cloak>
                            <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-gray-100">Dokumen Administrasi</h3>
                            <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                                Upload dokumen yang diperlukan untuk melengkapi proses rental
                            </p>
                            
                            <div class="space-y-4">
                                <template x-for="d in docs" :key="d.key">
                                    <div class="rounded-lg border border-gray-200 p-3 sm:p-4 dark:border-gray-700">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100" x-text="d.label"></p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    <span x-text="d.required ? 'Wajib' : 'Opsional'"></span> · JPEG/PNG/PDF ≤ 5MB
                                                </p>
                                                <template x-if="d.reason">
                                                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-400" x-text="d.reason"></p>
                                                </template>
                                            </div>
                                            <span 
                                                x-data="{ getStatusBadge(status) {
                                                    const badges = {
                                                        'approved': { class: 'bg-success/10 text-success-700 dark:bg-success-900/20 dark:text-success-200', label: 'Disetujui' },
                                                        'rejected': { class: 'bg-error/10 text-error-700 dark:bg-error-900/20 dark:text-error-200', label: 'Ditolak' },
                                                        'pending': { class: 'bg-warning/10 text-warning-700 dark:bg-warning-900/20 dark:text-warning-200', label: 'Menunggu' }
                                                    };
                                                    return badges[status] || badges['pending'];
                                                }}"
                                                :class="getStatusBadge(d.status).class"
                                                x-text="getStatusBadge(d.status).label"
                                                class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                                role="status">
                                            </span>
                                        </div>

                                        <!-- File preview (when uploading) -->
                                        <template x-if="uploads[d.key]">
                                            <div class="mt-3 flex items-center gap-3">
                                                <img x-show="uploads[d.key].preview" :src="uploads[d.key].preview" alt="" class="h-14 w-14 rounded-md border border-gray-200 object-cover dark:border-gray-700">
                                                <div class="min-w-0 flex-1">
                                                    <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100" x-text="uploads[d.key].name"></p>
                                                    <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700" 
                                                        role="progressbar" 
                                                        :aria-valuenow="progress[d.key] ?? 100" 
                                                        aria-valuemin="0" 
                                                        aria-valuemax="100">
                                                        <div class="h-full bg-primary-600 transition-all" :style="`width: ${progress[d.key] ?? 100}%`"></div>
                                                    </div>
                                                </div>
                                                <button type="button" 
                                                    @click="remove(d.key)" 
                                                    :aria-label="`Hapus ${d.label}`"
                                                    class="rounded-md p-2 text-gray-400 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>

                                        <!-- Upload zone (when no file uploading) -->
                                        <template x-if="!uploads[d.key]">
                                            <label class="mt-3 flex cursor-pointer flex-col items-center justify-center rounded-md border-2 border-dashed p-4 text-center transition-all"
                                                :class="dragOver ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-300 hover:border-primary-400 dark:border-gray-600'"
                                                @dragover.prevent="dragOver = true" 
                                                @dragleave.prevent="dragOver = false"
                                                @drop.prevent="dragOver = false; add(d.key, $event.dataTransfer.files[0])">
                                                <input type="file" 
                                                    :name="d.key" 
                                                    accept="image/jpeg,image/png,application/pdf" 
                                                    class="sr-only" 
                                                    @change="pick(d.key, $event)">
                                                <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                                </svg>
                                                <span class="mt-2 text-sm font-semibold text-primary-600">Upload file</span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">atau drag & drop</span>
                                            </label>
                                        </template>

                                        <!-- Rejection reason -->
                                        <p x-show="d.status === 'rejected' && d.rejection_reason" 
                                            class="mt-2 text-xs text-error-700 dark:text-error-400">
                                            Ditolak: <span x-text="d.rejection_reason ?? 'file tidak terbaca'"></span> — silakan upload ulang.
                                        </p>
                                    </div>
                                </template>

                                <!-- Empty state -->
                                <template x-if="docs.length === 0 && Object.keys(uploads).length === 0">
                                    <div class="rounded-lg bg-gray-50 p-6 text-center dark:bg-gray-900">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Tidak ada dokumen yang diperlukan untuk rental ini.</p>
                                    </div>
                                </template>
                            </div>

                            <!-- Batch Upload Button -->
                            <div x-show="Object.keys(uploads).length > 0" 
                                x-cloak
                                class="mt-6 flex items-center justify-between border-t border-gray-200 pt-6 dark:border-gray-700">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <span x-text="Object.keys(uploads).length"></span> dokumen siap diupload
                                </p>
                                <button type="button" 
                                    @click="uploadAll()" 
                                    :disabled="uploading"
                                    class="inline-flex items-center gap-2 rounded-md bg-primary-600 px-6 py-3 text-sm font-semibold text-white transition-all hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                                    <svg x-show="uploading" 
                                        class="h-5 w-5 animate-spin" 
                                        fill="none" 
                                        viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span x-text="uploading ? 'Mengupload...' : 'Upload Semua Dokumen'"></span>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Right Column (1/3 width) — Sidebar -->
                <div class="space-y-6 order-1 lg:order-2">
                    <!-- Quick Actions Card -->
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-gray-100">Aksi Cepat</h3>
                        <div class="space-y-3">
                            @if($rental->status === 'pending')
                                <x-touch-button 
                                    variant="primary" 
                                    size="md" 
                                    :fullWidthOnMobile="true"
                                    href="#payment-section">
                                    Upload Bukti Bayar
                                </x-touch-button>
                            @elseif($rental->status === 'paid' || $rental->status === 'documents_pending')
                                <x-touch-button 
                                    variant="primary" 
                                    size="md" 
                                    :fullWidthOnMobile="true"
                                    @click="document.querySelector('#document-section')?.scrollIntoView({ behavior: 'smooth' })">
                                    Upload Dokumen
                                </x-touch-button>
                            @elseif($rental->status === 'completed')
                                @if(!$rental->review)
                                    <x-touch-button 
                                        variant="primary" 
                                        size="md" 
                                        :fullWidthOnMobile="true"
                                        :href="route('rentals.reviews.create', $rental)">
                                        Tulis Ulasan
                                    </x-touch-button>
                                @else
                                    <x-touch-button 
                                        variant="secondary" 
                                        size="md" 
                                        :fullWidthOnMobile="true"
                                        :href="route('rentals.reviews.edit', $rental)">
                                        Edit Ulasan
                                    </x-touch-button>
                                @endif
                            @endif
                            
                            @if(!in_array($rental->status, ['completed', 'cancelled']))
                                <x-touch-button 
                                    variant="danger" 
                                    size="md" 
                                    :fullWidthOnMobile="true"
                                    href="#">
                                    Batalkan Rental
                                </x-touch-button>
                            @endif
                        </div>
                    </div>

                    <!-- Contact Admin Card -->
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-gray-100">Hubungi Pemilik Kost</h3>
                        <div class="space-y-2 text-sm">
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $rental->room->roomType->kost->owner->first_name }} {{ $rental->room->roomType->kost->owner->last_name }}</p>
                            <p class="text-gray-600 dark:text-gray-400">{{ $rental->room->roomType->kost->owner->email }}</p>
                        </div>
                    </div>

                    <!-- Rental Info Summary -->
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
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
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
