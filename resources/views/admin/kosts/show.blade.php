<x-base-layout 
    :title="$kost->name . ' - Admin - SewaKost'"
    variant="admin-sidebar"
    :page-title="$kost->name">
    
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
