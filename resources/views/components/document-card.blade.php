{{-- 
    Document Upload Card Component
    DESIGN.md §3.41 (lines 3859-4005)
    
    Props:
    - $requirement: KostDocumentRequirement model
    - $document: RentalDocument model (nullable)
    - $type: 'tenant' | 'admin' (determines interaction mode)
    - $rentalId: int (for upload route)
--}}

@props([
    'requirement',
    'document' => null,
    'type' => 'tenant',
    'rentalId' => null,
    'docIndex' => 0
])

@php
    $documentExists = $document && $document->document_path;
    $isVerified = $document && $document->verified_at;
    $isRejected = $document && $document->rejection_reason;
    $isPending = $documentExists && !$isVerified && !$isRejected;
    
    // Card border/background states
    $cardClasses = match(true) {
        $isVerified => 'border-success-500 bg-success-50',
        $isRejected => 'border-error-500 bg-error-50',
        default => 'border-gray-300 bg-white',
    };
    
    // Badge configuration
    $badgeConfig = match(true) {
        $isVerified => ['text' => '✓ Verified', 'class' => 'bg-success-100 text-success-800'],
        $isRejected => ['text' => '✗ Rejected', 'class' => 'bg-error-100 text-error-800'],
        $isPending => ['text' => 'Uploaded', 'class' => 'bg-warning-100 text-warning-800'],
        default => null,
    };
    
    // Button text
    $buttonText = match(true) {
        $isRejected => 'Re-upload',
        $documentExists => 'Replace Photo',
        default => 'Take Photo or Upload',
    };
@endphp

<div 
    class="border-2 rounded-xl p-4 transition-all {{ $cardClasses }}"
    x-data="{
        file: null,
        preview: null,
        uploading: false,
        uploaded: {{ $documentExists ? 'true' : 'false' }},
        
        selectFile(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            // Validate size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('File terlalu besar. Maksimal 5MB.');
                event.target.value = '';
                return;
            }
            
            // Validate type
            if (!file.type.match(/^image\/(jpeg|png)$/) && file.type !== 'application/pdf') {
                alert('Hanya file JPG, PNG, atau PDF yang diterima.');
                event.target.value = '';
                return;
            }
            
            this.file = file;
            
            // Show preview for images
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.preview = e.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                this.preview = null;
            }
        },
        
        async uploadDocument() {
            if (!this.file) return;
            
            this.uploading = true;
            
            const formData = new FormData();
            formData.append('document', this.file);
            formData.append('type', '{{ $requirement->document_type }}');
            
            try {
                const response = await fetch('{{ route('tenant.rentals.documents.upload', $rentalId) }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.uploaded = true;
                    
                    // Dispatch events for parent components
                    this.$dispatch('document-uploaded', {
                        type: '{{ $requirement->document_type }}',
                        uploadedCount: data.uploaded_count,
                        totalRequired: data.total_required
                    });
                    
                    // Show success message
                    alert('✓ Dokumen berhasil diupload');
                    
                    // Reload page to show updated state
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    alert(data.message || 'Upload gagal. Silakan coba lagi.');
                }
            } catch (error) {
                console.error('Upload error:', error);
                alert('Terjadi kesalahan jaringan. Silakan coba lagi.');
            } finally {
                this.uploading = false;
            }
        }
    }">
    
    {{-- Header: Document Name & Badge --}}
    <div class="flex items-start justify-between gap-3 mb-3">
        <div class="flex-1">
            <h4 class="text-sm font-semibold text-gray-900 mb-1">
                {{ $requirement->document_type_label ?? $requirement->document_type }}
            </h4>
            <p class="text-xs text-gray-600">
                @if($requirement->is_required)
                    <span class="text-error-600 font-medium">Wajib</span>
                @else
                    <span class="text-gray-500">Opsional</span>
                @endif
                · JPEG/PNG/PDF ≤ 5MB
            </p>
            @if($requirement->reason)
                <p class="text-xs text-gray-500 mt-1">{{ $requirement->reason }}</p>
            @endif
        </div>
        
        @if($badgeConfig)
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badgeConfig['class'] }}" aria-label="Document status: {{ $badgeConfig['text'] }}">
                {{ $badgeConfig['text'] }}
            </span>
        @endif
    </div>
    
    {{-- Preview Area --}}
    <div class="mb-3">
        @if($documentExists)
            {{-- Existing uploaded document --}}
            <div class="relative group">
                <img 
                    src="{{ Storage::url($document->document_path) }}" 
                    alt="{{ $requirement->document_type }}"
                    class="w-full h-48 object-cover rounded-lg border border-gray-200">
                
                {{-- Overlay on hover for desktop --}}
                <div class="hidden lg:flex absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg items-center justify-center">
                    <a 
                        href="{{ Storage::url($document->document_path) }}" 
                        target="_blank"
                        class="px-4 py-2 bg-white text-gray-900 rounded-lg font-medium hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                        Lihat Ukuran Penuh
                    </a>
                </div>
            </div>
        @elseif($type === 'tenant')
            {{-- Empty state with preview capability --}}
            <div 
                x-show="!preview"
                class="flex items-center justify-center h-48 bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg">
                <div class="text-center">
                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-xs text-gray-500">Belum ada file</p>
                </div>
            </div>
            
            {{-- File preview --}}
            <div x-show="preview" x-cloak>
                <img 
                    :src="preview" 
                    alt="Preview"
                    class="w-full h-48 object-cover rounded-lg border border-gray-200">
            </div>
        @endif
    </div>
    
    {{-- Rejection Reason (if rejected) --}}
    @if($isRejected)
        <div class="mb-3 p-3 rounded-lg bg-error-50 border border-error-200">
            <p class="text-xs text-error-800">
                <span class="font-semibold">Alasan Ditolak:</span> {{ $document->rejection_reason }}
            </p>
        </div>
    @endif
    
    {{-- Admin Verification Actions --}}
    @if($type === 'admin' && $documentExists)
        <div class="space-y-2">
            {{-- Verification Buttons (shown when pending) --}}
            <div x-show="documents[{{ $docIndex }}] && !documents[{{ $docIndex }}].verified_at && !documents[{{ $docIndex }}].rejected_at" 
                 class="flex gap-2">
                <button @click="approveDocument({{ $document->id }})"
                        :disabled="documents[{{ $docIndex }}]?.verifying"
                        class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-success-600 text-white text-sm font-semibold rounded-lg hover:bg-success-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-success-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <span x-show="!documents[{{ $docIndex }}]?.verifying">✓ Approve</span>
                    <span x-show="documents[{{ $docIndex }}]?.verifying" role="status" aria-live="polite" aria-atomic="true" class="flex items-center gap-1">
                        <svg class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Approving...
                    </span>
                </button>
                <button @click="startReject({{ $document->id }})"
                        class="flex-1 px-3 py-2 border-2 border-error-600 text-error-600 text-sm font-semibold rounded-lg hover:bg-error-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-error-500 transition-colors">
                    ✗ Reject
                </button>
            </div>

            {{-- Inline Rejection Form --}}
            <div x-show="rejectingDoc === {{ $document->id }}" 
                 x-collapse
                 class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                <textarea x-model="rejectionReason"
                          x-ref="rejectionTextarea{{ $document->id }}"
                          rows="2"
                          placeholder="Reason for rejection..."
                          class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                          minlength="10"></textarea>
                <p class="text-xs text-gray-500 mt-1">Minimum 10 characters</p>
                
                <div class="mt-2 flex gap-2">
                    <button @click="rejectingDoc = null; rejectionReason = ''"
                            class="flex-1 px-3 py-1.5 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-500">
                        Cancel
                    </button>
                    <button @click="confirmRejectDocument({{ $document->id }})"
                            :disabled="rejectionReason.length < 10"
                            class="flex-1 px-3 py-1.5 text-sm bg-error-600 text-white font-semibold rounded-lg hover:bg-error-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-error-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        Confirm
                    </button>
                </div>
            </div>

            {{-- Verified State --}}
            <div x-show="documents[{{ $docIndex }}]?.verified_at" 
                 x-cloak
                 class="p-3 bg-success-50 border border-success-200 rounded-lg">
                <p class="text-xs text-success-700 font-semibold">
                    ✓ Verified by admin
                </p>
            </div>

            {{-- Rejected State --}}
            <div x-show="documents[{{ $docIndex }}]?.rejected_at" 
                 x-cloak
                 class="p-3 bg-error-50 border border-error-200 rounded-lg">
                <p class="text-xs text-error-700 font-semibold">
                    ✗ Rejected
                </p>
                <p class="text-xs text-error-600 mt-1" x-text="'Reason: ' + documents[{{ $docIndex }}]?.rejection_reason"></p>
            </div>
        </div>
    @endif
    
    {{-- Upload Controls (Tenant only) --}}
    @if($type === 'tenant')
        <div class="space-y-2">
            {{-- File input (hidden, triggered by button) --}}
            <input 
                type="file"
                @change="selectFile($event)"
                accept="image/jpeg,image/png,application/pdf"
                capture="environment"
                class="hidden"
                :id="'file-input-{{ $requirement->id }}'">
            
            {{-- File selection button (shown when no file selected) --}}
            <label 
                x-show="!file"
                :for="'file-input-{{ $requirement->id }}'"
                class="block w-full text-center px-4 py-3 bg-white border-2 border-gray-300 text-gray-700 font-medium rounded-lg cursor-pointer hover:bg-gray-50 hover:border-gray-400 transition-colors focus-within:ring-2 focus-within:ring-primary-500">
                {{ $buttonText }}
            </label>
            
            {{-- Upload button (shown when file selected) --}}
            <button 
                x-show="file"
                x-cloak
                @click="uploadDocument()"
                :disabled="uploading"
                class="w-full inline-flex items-center justify-center px-4 py-3 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                <span x-show="!uploading">Upload {{ $requirement->document_type_label ?? $requirement->document_type }}</span>
                <span x-show="uploading" role="status" aria-live="polite" aria-atomic="true" class="flex items-center gap-2">
                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Mengupload...
                </span>
            </button>
            
            {{-- File info --}}
            <div x-show="file" x-cloak class="text-xs text-gray-600 text-center">
                <span x-text="file ? file.name : ''"></span>
                <span x-show="file" x-text="' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)'"></span>
            </div>
        </div>
    @endif
</div>
