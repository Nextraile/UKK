<x-base-layout 
    :title="$kost->name . ' - Admin - SewaKost'"
    variant="admin-sidebar"
    :page-title="$kost->name">

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
      crossorigin=""/>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
@endpush
    
<div class="max-w-4xl space-y-6">
    <x-page-header 
        :title="$kost->name"
        subtitle="Dibuat {{ $kost->created_at->format('d M Y') }}"
        :breadcrumbs="[
            ['label' => 'Kost', 'url' => route('admin.kosts.index')],
            ['label' => $kost->name],
        ]"
    >
        <x-slot:actions>
            <x-status-badge :status="$kost->status" type="kost" size="md" />
        </x-slot:actions>
    </x-page-header>

    <!-- Rejection Reason (if rejected) -->
    @if($kost->isRejected() && $kost->rejected_reason)
    <div class="p-4 bg-error-50 border border-error-200 rounded-lg">
        <h3 class="text-sm font-medium text-error-800">Alasan Penolakan:</h3>
        <p class="mt-1 text-sm text-error-700">{{ $kost->rejected_reason }}</p>
    </div>
    @endif

    <!-- Data Completeness Warning (Draft/Rejected only) -->
    @if(($kost->isDraft() || $kost->isRejected()) && (
        empty($kost->name) || 
        !$kost->address()->exists() || 
        $kost->categories->isEmpty() || 
        empty($kost->qris_image_path) || 
        $kost->documentRequirements->isEmpty()
    ))
    <div class="bg-warning-50 border border-warning-200 rounded-lg p-4">
        <h4 class="font-medium text-warning-800 mb-2 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            Data Belum Lengkap untuk Review
        </h4>
        <p class="text-sm text-warning-700 mb-3">Lengkapi data berikut sebelum submit untuk review:</p>
        
        <ul class="space-y-1 text-sm text-warning-700">
            @if(empty($kost->name))
            <li class="flex items-center">
                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke-width="2"/>
                </svg>
                Nama kost
            </li>
            @else
            <li class="flex items-center text-success-700">
                <svg class="w-5 h-5 text-success-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                Nama kost
            </li>
            @endif
            
            @if(!$kost->address()->exists())
            <li class="flex items-center">
                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke-width="2"/>
                </svg>
                Alamat lengkap kost
            </li>
            @else
            <li class="flex items-center text-success-700">
                <svg class="w-5 h-5 text-success-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                Alamat lengkap kost
            </li>
            @endif
            
            @if($kost->categories->isEmpty())
            <li class="flex items-center">
                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke-width="2"/>
                </svg>
                Kategori kost (minimal 1)
            </li>
            @else
            <li class="flex items-center text-success-700">
                <svg class="w-5 h-5 text-success-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                Kategori kost ({{ $kost->categories->count() }})
            </li>
            @endif
            
            @if(empty($kost->qris_image_path))
            <li class="flex items-center">
                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke-width="2"/>
                </svg>
                Gambar QRIS pembayaran
            </li>
            @else
            <li class="flex items-center text-success-700">
                <svg class="w-5 h-5 text-success-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                Gambar QRIS pembayaran
            </li>
            @endif
            
            @if($kost->documentRequirements->isEmpty())
            <li class="flex items-center">
                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke-width="2"/>
                </svg>
                Persyaratan dokumen (minimal 1)
            </li>
            @else
            <li class="flex items-center text-success-700">
                <svg class="w-5 h-5 text-success-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                Persyaratan dokumen ({{ $kost->documentRequirements->count() }})
            </li>
            @endif
        </ul>
    </div>
    @endif

    <!-- Details -->
    <div class="bg-white rounded-lg border border-gray-200 p-6 space-y-4">
        <div>
            <h3 class="text-sm font-medium text-gray-500">Nomor Kontak</h3>
            <p class="mt-1 text-sm text-gray-900">{{ $kost->contact_number }}</p>
        </div>

        @if($kost->description)
        <div>
            <h3 class="text-sm font-medium text-gray-500">Deskripsi</h3>
            <p class="mt-1 text-sm text-gray-900">{{ $kost->description }}</p>
        </div>
        @endif

        @if($kost->address)
        <div>
            <h3 class="text-sm font-medium text-gray-500">Alamat</h3>
            <p class="mt-1 text-sm text-gray-900">
                {{ $kost->address->full_address }}, {{ $kost->address->district }}, {{ $kost->address->city }}, {{ $kost->address->province }}
            </p>
        </div>
        @endif

        @if($kost->categories->isNotEmpty())
        <div>
            <h3 class="text-sm font-medium text-gray-500">Kategori</h3>
            <div class="mt-1 flex gap-2">
                @foreach($kost->categories as $category)
                    <span class="inline-flex px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded">{{ $category->name }}</span>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Location Map -->
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-3">Lokasi Kost</h3>
        @if($kost->latitude && $kost->longitude)
            <div id="map" 
                 class="h-64 rounded-lg border border-gray-300"
                 x-data="{
                     map: null,
                     initMap() {
                         this.map = L.map('map').setView([{{ $kost->latitude }}, {{ $kost->longitude }}], 15);
                         L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                             attribution: '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors'
                         }).addTo(this.map);
                         L.marker([{{ $kost->latitude }}, {{ $kost->longitude }}])
                             .addTo(this.map)
                             .bindPopup('{{ addslashes($kost->name) }}');
                     }
                 }"
                 x-init="initMap()"
                 x-on:destroy="if (map) { map.remove(); map = null; }">
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-8 px-4 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50">
                <svg class="w-10 h-10 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="text-sm text-gray-500">Koordinat lokasi belum diatur</p>
                <a href="{{ route('admin.kosts.edit', $kost) }}" class="mt-2 text-sm text-primary-600 hover:text-primary-700">
                    Atur koordinat
                </a>
            </div>
        @endif
    </div>

    <!-- Kost Images Gallery -->
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-3">
            Foto Kost ({{ $kost->kostImages->count() }})
        </h3>
        @if($kost->kostImages->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-4" x-data="{ lightbox: false, currentImage: '' }">
                @foreach($kost->kostImages as $image)
                    <div class="relative aspect-square rounded-lg overflow-hidden bg-gray-100 hover:opacity-90 transition cursor-pointer"
                         @click="lightbox = true; currentImage = '/storage/{{ $image->image_path }}'">
                        <img src="/storage/{{ $image->image_path }}" 
                             alt="Kost image {{ $loop->iteration }}"
                             class="w-full h-full object-cover">
                        @if($image->is_thumbnail)
                            <span class="absolute top-2 right-2 inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-primary-600 text-white">
                                Thumbnail
                            </span>
                        @endif
                    </div>
                @endforeach
                
                <!-- Lightbox Modal -->
                <div x-show="lightbox" 
                     x-cloak
                     @click="lightbox = false"
                     class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-90 p-4">
                    <img :src="currentImage" 
                         class="max-w-full max-h-full rounded-lg"
                         @click.stop>
                    <button @click="lightbox = false" 
                            class="absolute top-4 right-4 text-white hover:text-gray-300"
                            aria-label="Close">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            <a href="{{ route('admin.kosts.images.index', $kost) }}" 
               class="inline-flex items-center text-sm text-primary-600 hover:text-primary-700">
                Kelola Foto (Upload, Hapus, Atur Urutan)
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        @else
            <div class="flex flex-col items-center justify-center py-12 px-4 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50">
                <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-sm text-gray-500 mb-2">Belum ada foto kost</p>
                <a href="{{ route('admin.kosts.images.index', $kost) }}" 
                   class="text-sm text-primary-600 hover:text-primary-700">
                    Upload Foto
                </a>
            </div>
        @endif
    </div>

    <!-- Payment Configuration -->
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-3">Konfigurasi Pembayaran</h3>
        
        @if($kost->qris_image_path || $kost->bank_name)
            <div class="space-y-4">
                <!-- QRIS -->
                @if($kost->qris_image_path)
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-2">QRIS</h4>
                        <img src="/storage/{{ $kost->qris_image_path }}" 
                             alt="QRIS Code"
                             class="max-w-xs border-2 border-gray-300 rounded-lg">
                    </div>
                @endif
                
                <!-- Bank Account -->
                @if($kost->bank_name)
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-2">Transfer Bank</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Bank</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nomor Rekening</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama Pemilik</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $kost->bank_name }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 font-mono">{{ $kost->account_number }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $kost->account_holder_name }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
                
                <a href="{{ route('admin.kosts.payment.edit', $kost) }}" 
                   class="inline-flex items-center text-sm text-primary-600 hover:text-primary-700">
                    Edit Konfigurasi Pembayaran
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-8 px-4 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50">
                <svg class="w-10 h-10 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                <p class="text-sm text-gray-500 mb-2">Belum ada metode pembayaran</p>
                <a href="{{ route('admin.kosts.payment.edit', $kost) }}" 
                   class="text-sm text-primary-600 hover:text-primary-700">
                    Atur Pembayaran
                </a>
            </div>
        @endif
    </div>

    <!-- Document Requirements -->
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-3">
            Persyaratan Dokumen ({{ $kost->documentRequirements->count() }})
        </h3>
        
        @if($kost->documentRequirements->isNotEmpty())
            <ul class="space-y-3 mb-4">
                @foreach($kost->documentRequirements as $docReq)
                    <li class="flex items-start gap-3 p-3 border border-gray-200 rounded-lg bg-gray-50">
                        <div class="flex-shrink-0 mt-0.5">
                            @if($docReq->is_required)
                                <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            @else
                                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-sm font-semibold text-gray-900">
                                    {{ Str::title(str_replace('_', ' ', $docReq->document_type)) }}
                                </span>
                                @if($docReq->is_required)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                        Wajib
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-200 text-gray-700">
                                        Optional
                                    </span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-600">{{ $docReq->reason }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
            <a href="{{ route('admin.kosts.document-requirements.index', $kost) }}" 
               class="inline-flex items-center text-sm text-primary-600 hover:text-primary-700">
                Kelola Persyaratan Dokumen
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        @else
            <div class="flex flex-col items-center justify-center py-8 px-4 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50">
                <svg class="w-10 h-10 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-sm text-gray-500 mb-2">Belum ada persyaratan dokumen</p>
                <a href="{{ route('admin.kosts.document-requirements.index', $kost) }}" 
                   class="text-sm text-primary-600 hover:text-primary-700">
                    Tambah Persyaratan
                </a>
            </div>
        @endif
    </div>

    <!-- Facilities & Rules -->
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-3">Fasilitas & Peraturan</h3>
        
        @if(!empty($kost->facilities) || !empty($kost->rules))
            <div class="space-y-4">
                <!-- Facilities -->
                @if(!empty($kost->facilities))
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-2">Fasilitas</h4>
                        <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($kost->facilities as $facility)
                                <li class="flex items-center text-sm text-gray-700">
                                    <svg class="h-4 w-4 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $facility }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <!-- Rules -->
                @if(!empty($kost->rules))
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-2">Peraturan</h4>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($kost->rules as $rule)
                                <li class="text-sm text-gray-700">{{ $rule }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <a href="{{ route('admin.kosts.edit', $kost) }}" 
                   class="inline-flex items-center text-sm text-primary-600 hover:text-primary-700">
                    Edit Fasilitas & Peraturan
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-8 px-4 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50">
                <svg class="w-10 h-10 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-sm text-gray-500 mb-2">Belum ada fasilitas atau peraturan</p>
                <a href="{{ route('admin.kosts.edit', $kost) }}" 
                   class="text-sm text-primary-600 hover:text-primary-700">
                    Tambah Fasilitas & Peraturan
                </a>
            </div>
        @endif
    </div>

    <!-- Room Types & Rooms -->
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-3">
            Tipe Kamar & Kamar ({{ $kost->roomTypes->count() }} tipe, {{ $kost->rooms_count }} kamar)
        </h3>
        
        @if($kost->roomTypes->isNotEmpty())
            <div class="space-y-4" x-data="{ openRoomType: null }">
                @foreach($kost->roomTypes as $roomType)
                    <div class="border border-gray-200 rounded-lg">
                        <!-- Accordion Header -->
                        <button @click="openRoomType = openRoomType === {{ $roomType->id }} ? null : {{ $roomType->id }}"
                                class="w-full p-4 flex items-center justify-between hover:bg-gray-50 transition rounded-lg">
                            <div class="flex items-center gap-4">
                                @if($roomType->roomTypeImages->first())
                                    <img src="/storage/{{ $roomType->roomTypeImages->first()->image_path }}" 
                                         alt="{{ $roomType->name }}"
                                         class="w-20 h-20 object-cover rounded-lg flex-shrink-0">
                                @endif
                                <div class="text-left">
                                    <h4 class="font-semibold text-gray-900">{{ $roomType->name }}</h4>
                                    <p class="text-sm text-gray-600 mt-1">
                                        @if($roomType->room_size)
                                            {{ $roomType->room_size }}m²
                                        @endif
                                        @if($roomType->room_size && $roomType->max_occupants)
                                            •
                                        @endif
                                        @if($roomType->max_occupants)
                                            Max {{ $roomType->max_occupants }} orang
                                        @endif
                                        @if(($roomType->room_size || $roomType->max_occupants) && $roomType->rooms->count())
                                            •
                                        @endif
                                        {{ $roomType->rooms->count() }} kamar
                                    </p>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 transition-transform flex-shrink-0"
                                 :class="{ 'rotate-180': openRoomType === {{ $roomType->id }} }"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        
                        <!-- Accordion Content -->
                        <div x-show="openRoomType === {{ $roomType->id }}" 
                             x-collapse
                             class="border-t border-gray-200">
                            <div class="p-4 space-y-4">
                                
                                <!-- Price Schemes -->
                                @if($roomType->priceSchemes->isNotEmpty())
                                    <div>
                                        <h5 class="text-sm font-semibold text-gray-700 mb-2">Skema Harga</h5>
                                        <div class="space-y-1">
                                            @foreach($roomType->priceSchemes as $scheme)
                                                <div class="flex items-baseline gap-2 text-sm">
                                                    <span class="font-medium text-gray-700">{{ $scheme->name }}:</span>
                                                    <span class="text-gray-900 font-semibold">
                                                        Rp {{ number_format($scheme->price, 0, ',', '.') }}
                                                    </span>
                                                    @if($scheme->security_deposit)
                                                        <span class="text-xs text-gray-500">
                                                            (+ Rp {{ number_format($scheme->security_deposit, 0, ',', '.') }} deposit)
                                                        </span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500">Belum ada skema harga</p>
                                @endif
                                
                                <!-- Room Type Images -->
                                @if($roomType->roomTypeImages->count() > 1)
                                    <div>
                                        <h5 class="text-sm font-semibold text-gray-700 mb-2">Foto Tipe Kamar ({{ $roomType->roomTypeImages->count() }})</h5>
                                        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2">
                                            @foreach($roomType->roomTypeImages as $image)
                                                <img src="/storage/{{ $image->image_path }}" 
                                                     alt="Room type image"
                                                     class="w-full aspect-square object-cover rounded cursor-pointer hover:opacity-75 transition">
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Rooms List -->
                                @if($roomType->rooms->isNotEmpty())
                                    <div>
                                        <h5 class="text-sm font-semibold text-gray-700 mb-2">Daftar Kamar ({{ $roomType->rooms->count() }})</h5>
                                        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2">
                                            @foreach($roomType->rooms as $room)
                                                <div class="p-2 border rounded text-center text-sm font-medium
                                                    {{ $room->status === 'active' ? 'bg-green-50 border-green-200 text-green-700' : 'bg-gray-50 border-gray-200 text-gray-500' }}">
                                                    {{ $room->room_number }}
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500">Belum ada kamar</p>
                                @endif
                                
                                <!-- Actions -->
                                <div class="flex flex-wrap gap-2 pt-2 border-t border-gray-200">
                                    <a href="{{ route('admin.room-types.edit', [$kost, $roomType]) }}" 
                                       class="inline-flex items-center px-3 py-1.5 text-sm bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                        Edit Tipe Kamar
                                    </a>
                                    <a href="{{ route('admin.price-schemes.index', $roomType) }}" 
                                       class="inline-flex items-center px-3 py-1.5 text-sm bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                        Kelola Harga
                                    </a>
                                    <a href="{{ route('admin.rooms.index', ['kost' => $kost, 'room_type_id' => $roomType->id]) }}" 
                                       class="inline-flex items-center px-3 py-1.5 text-sm bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                        Kelola Kamar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-4">
                <a href="{{ route('admin.room-types.create', $kost) }}" 
                   class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Tipe Kamar
                </a>
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-12 px-4 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50">
                <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <p class="text-sm text-gray-500 mb-2">Belum ada tipe kamar</p>
                <a href="{{ route('admin.room-types.create', $kost) }}" 
                   class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    Buat Tipe Kamar Pertama
                </a>
            </div>
        @endif
    </div>

    <!-- Configuration Section -->
    @if($kost->isDraft() || $kost->isRejected())
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Konfigurasi Kost</h3>
        <p class="text-sm text-gray-600 mb-4">Lengkapi konfigurasi berikut sebelum submit untuk review</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Images -->
            <a href="{{ route('admin.kosts.images.index', $kost) }}" 
               class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-primary-300 transition">
                <div class="flex items-start justify-between">
                    <div>
                        <h4 class="font-medium text-gray-900">Foto Kost</h4>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ $kost->kostImages->count() }} foto
                            @if($kost->kostImages->isEmpty())
                                <span class="text-warning-600">(Opsional)</span>
                            @endif
                        </p>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>

            <!-- Categories -->
            <a href="{{ route('admin.kosts.categories.edit', $kost) }}" 
               class="p-4 border rounded-lg hover:bg-gray-50 hover:border-primary-300 transition {{ $kost->categories->isEmpty() ? 'border-warning-300 bg-warning-50' : 'border-gray-200' }}">
                <div class="flex items-start justify-between">
                    <div>
                        <h4 class="font-medium text-gray-900">Kategori</h4>
                        <p class="text-sm mt-1 {{ $kost->categories->isEmpty() ? 'text-warning-700 font-medium' : 'text-gray-600' }}">
                            @if($kost->categories->isEmpty())
                                Belum ada kategori (Wajib min. 1)
                            @else
                                {{ $kost->categories->count() }} kategori
                            @endif
                        </p>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>

            <!-- Payment (QRIS) -->
            <a href="{{ route('admin.kosts.payment.edit', $kost) }}" 
               class="p-4 border rounded-lg hover:bg-gray-50 hover:border-primary-300 transition {{ empty($kost->qris_image_path) ? 'border-warning-300 bg-warning-50' : 'border-gray-200' }}">
                <div class="flex items-start justify-between">
                    <div>
                        <h4 class="font-medium text-gray-900">Pembayaran (QRIS)</h4>
                        <p class="text-sm mt-1 {{ empty($kost->qris_image_path) ? 'text-warning-700 font-medium' : 'text-gray-600' }}">
                            @if(empty($kost->qris_image_path))
                                Belum ada QRIS (Wajib)
                            @else
                                QRIS terkonfigurasi
                            @endif
                        </p>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>

            <!-- Document Requirements -->
            <a href="{{ route('admin.kosts.document-requirements.index', $kost) }}" 
               class="p-4 border rounded-lg hover:bg-gray-50 hover:border-primary-300 transition {{ $kost->documentRequirements->count() === 0 ? 'border-warning-300 bg-warning-50' : 'border-gray-200' }}">
                <div class="flex items-start justify-between">
                    <div>
                        <h4 class="font-medium text-gray-900">Persyaratan Dokumen</h4>
                        <p class="text-sm mt-1 {{ $kost->documentRequirements->count() === 0 ? 'text-warning-700 font-medium' : 'text-gray-600' }}">
                            @if($kost->documentRequirements->count() === 0)
                                Belum ada dokumen (Wajib min. 1)
                            @else
                                {{ $kost->documentRequirements->count() }} dokumen
                            @endif
                        </p>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
        </div>
    </div>
    @endif

    <!-- Actions -->
    <div class="flex justify-between">
        <a href="{{ route('admin.kosts.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
            Kembali ke Daftar
        </a>
        
        <div class="flex space-x-3">
            @can('submit', $kost)
            <form method="POST" action="{{ route('admin.kosts.submit', $kost) }}" onsubmit="return confirm('Yakin ingin submit kost ini untuk review? Pastikan semua data sudah lengkap (nama, alamat, kategori, minimal 1 tipe kamar).')">
                @csrf
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-success-600 rounded-lg hover:bg-success-700">
                    Submit untuk Review
                </button>
            </form>
            @endcan

            @can('cancel', $kost)
            <div x-data="{ showCancelModal: false }">
                <button type="button"
                    @click="showCancelModal = true"
                    class="px-4 py-2 text-sm font-medium text-white bg-warning-600 rounded-lg hover:bg-warning-700 focus:outline-none focus:ring-2 focus:ring-warning-500 focus:ring-offset-2 transition-colors">
                    Batalkan Pengajuan
                </button>

                <!-- Cancel Confirmation Modal -->
                <div x-show="showCancelModal" 
                     x-cloak
                     @click.self="showCancelModal = false"
                     @keydown.escape.window="showCancelModal = false"
                     class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black bg-opacity-50" 
                     aria-labelledby="cancel-modal-title" 
                     role="dialog" 
                     aria-modal="true"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0">
                    
                    <div class="bg-white rounded-lg shadow-xl max-w-lg w-full"
                         @click.stop
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-90"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-90">
                        
                        <!-- Header -->
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900" id="cancel-modal-title">
                                    Batalkan Pengajuan Kost
                                </h3>
                                <button type="button" 
                                        @click="showCancelModal = false" 
                                        aria-label="Tutup modal"
                                        class="text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-warning-500 rounded">
                                    <span class="sr-only">Close</span>
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Body -->
                        <div class="px-6 py-5">
                            <div class="flex items-start gap-4">
                                <div class="shrink-0 w-12 h-12 rounded-full bg-warning-100 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-700 leading-relaxed">
                                        Apakah Anda yakin ingin membatalkan pengajuan kost ini?
                                    </p>
                                    <p class="mt-2 text-sm text-gray-600">
                                        Kost akan kembali ke status <span class="font-semibold">Draft</span> dan Anda dapat mengeditnya kembali sebelum mengajukan ulang untuk review.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Footer -->
                        <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3 rounded-b-lg">
                            <button type="button" 
                                    @click="showCancelModal = false"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                                Batal
                            </button>
                            
                            <form method="POST" action="{{ route('admin.kosts.cancel', $kost) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="px-4 py-2 text-sm font-medium text-white bg-warning-600 border border-transparent rounded-lg hover:bg-warning-700 focus:outline-none focus:ring-2 focus:ring-warning-500 focus:ring-offset-2 transition-colors">
                                    Ya, Batalkan Pengajuan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endcan

            @can('publish', $kost)
            <form method="POST" action="{{ route('admin.kosts.publish', $kost) }}" onsubmit="return confirm('Publikasikan kost ini? Kost akan terlihat oleh tenant di marketplace.')">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-success-600 rounded-lg hover:bg-success-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Publish Kost
                </button>
            </form>
            @endcan

            @can('update', $kost)
            <a href="{{ route('admin.kosts.edit', $kost) }}" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700">
                Edit Kost
            </a>
            @endcan

            @can('delete', $kost)
            <form method="POST" action="{{ route('admin.kosts.destroy', $kost) }}" onsubmit="return confirm('Yakin ingin menghapus kost ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-error-600 rounded-lg hover:bg-error-700">
                    Hapus
                </button>
            </form>
            @endcan
        </div>
    </div>
</div>
</x-base-layout>
