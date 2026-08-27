<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Batalkan Rental
        </h2>
    </x-slot>

    <div class="max-w-2xl mx-auto py-8 px-4">
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-2xl font-bold mb-6">Batalkan Rental</h1>
            
            {{-- Rental Summary --}}
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h2 class="font-semibold mb-2">Detail Rental</h2>
                <dl class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Kost:</dt>
                        <dd class="font-medium">{{ $rental->room->roomType->kost->name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Tipe Kamar:</dt>
                        <dd class="font-medium">{{ $rental->room->roomType->name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Kamar:</dt>
                        <dd class="font-medium">{{ $rental->room->name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Periode:</dt>
                        <dd class="font-medium">
                            {{ $rental->start_date->format('d M Y') }} - {{ $rental->end_date->format('d M Y') }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Durasi:</dt>
                        <dd class="font-medium">{{ $rental->duration_value }} {{ __($rental->duration_unit) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Total Biaya:</dt>
                        <dd class="font-medium text-lg">Rp {{ number_format((float) $rental->grand_total, 0, ',', '.') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Status:</dt>
                        <dd>
                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full
                                @if($rental->status === 'confirmed') bg-green-100 text-green-800
                                @elseif($rental->status === 'paid') bg-blue-100 text-blue-800
                                @elseif($rental->status === 'documents_pending') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $rental->status)) }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>
            
            {{-- Warning --}}
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Peringatan</h3>
                        <div class="text-sm text-yellow-700 mt-1 space-y-1">
                            <p>Tindakan ini tidak dapat dibatalkan. Setelah rental dibatalkan, Anda perlu membuat booking baru jika berubah pikiran.</p>
                            @if($rental->status === 'paid' || $rental->status === 'confirmed')
                                <p class="font-medium">Proses pengembalian dana (refund) dapat memakan waktu 3-7 hari kerja sesuai kebijakan admin kost.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Cancellation Form --}}
            <form action="{{ route('rentals.cancel', $rental) }}" method="POST">
                @csrf
                
                <div class="mb-6">
                    <label for="cancellation_reason" class="block text-sm font-medium text-gray-700 mb-2">
                        Alasan Pembatalan <span class="text-gray-500 font-normal">(Opsional)</span>
                    </label>
                    <textarea 
                        id="cancellation_reason"
                        name="cancellation_reason"
                        rows="4"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('cancellation_reason') border-red-500 @enderror"
                        placeholder="Bantu kami memahami mengapa Anda membatalkan rental ini...">{{ old('cancellation_reason') }}</textarea>
                    @error('cancellation_reason')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Maksimal 1000 karakter</p>
                </div>
                
                @if($errors->has('error'))
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700">{{ $errors->first('error') }}</p>
                            </div>
                        </div>
                    </div>
                @endif
                
                <div class="flex gap-3">
                    <a href="{{ route('rentals.show', $rental) }}" 
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-center hover:bg-gray-50 transition">
                        Kembali
                    </a>
                    <button type="submit"
                            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium">
                        Konfirmasi Pembatalan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
