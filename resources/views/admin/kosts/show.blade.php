@extends('layouts.admin')

@section('title', $kost->name)

@section('content')
<div class="max-w-4xl space-y-6">
    <!-- Header with Actions -->
    <div class="flex justify-between items-start">
        <div>
            <h2 class="text-xl font-bold text-gray-900">{{ $kost->name }}</h2>
            <p class="mt-1 text-sm text-gray-500">Dibuat {{ $kost->created_at->format('d M Y') }}</p>
        </div>
        <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full
            @if($kost->status === 'draft') bg-gray-100 text-gray-800
            @elseif($kost->status === 'pending_review') bg-yellow-100 text-yellow-800
            @elseif($kost->status === 'approved') bg-green-100 text-green-800
            @elseif($kost->status === 'active') bg-blue-100 text-blue-800
            @elseif($kost->status === 'rejected') bg-red-100 text-red-800
            @endif">
            {{ ucfirst(str_replace('_', ' ', $kost->status)) }}
        </span>
    </div>

    <!-- Rejection Reason (if rejected) -->
    @if($kost->isRejected() && $kost->rejected_reason)
    <div class="p-4 bg-error-50 border border-error-200 rounded-lg">
        <h3 class="text-sm font-medium text-error-800">Alasan Penolakan:</h3>
        <p class="mt-1 text-sm text-error-700">{{ $kost->rejected_reason }}</p>
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

    <!-- Actions -->
    <div class="flex justify-between">
        <a href="{{ route('admin.kosts.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
            Kembali ke Daftar
        </a>
        
        <div class="flex space-x-3">
            @can('submit', $kost)
            <form method="POST" action="{{ route('admin.kosts.submit', $kost) }}" onsubmit="return confirm('Yakin ingin submit kost ini untuk review? Pastikan semua data sudah lengkap (nama, alamat, kategori, minimal 1 tipe kamar).')">
                @csrf
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
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
                <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
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
@endsection
