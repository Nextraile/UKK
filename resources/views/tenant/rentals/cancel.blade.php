<x-base-layout 
    title="Pembatalan Rental - SewaKost"
    variant="full-width">
    
    <div class="max-w-2xl mx-auto py-8 px-4">
            <x-page-header 
                title="Pembatalan Rental"
            :breadcrumbs="[
                ['label' => 'Rental', 'url' => route('rentals.index')],
                ['label' => 'Detail Rental', 'url' => route('rentals.show', $rental)],
                ['label' => 'Pembatalan'],
            ]"
            >
            <x-slot:actions>
                <a href="{{ route('rentals.show', $rental) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
                    ← Kembali
                </a>
            </x-slot:actions>
        </x-page-header>

        <div class="bg-white rounded-lg shadow p-6">
            
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
                        <dd class="font-medium">Kamar {{ $rental->room->code }}</dd>
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
                            <x-status-badge :status="$rental->status" type="rental" />
                        </dd>
                    </div>
                </dl>
            </div>
            
            {{-- Warning --}}
            <x-alert-banner variant="warning" title="Peringatan" class="mb-6">
                <div class="space-y-1">
                    <p>Tindakan ini tidak dapat dibatalkan. Setelah rental dibatalkan, Anda perlu membuat booking baru jika berubah pikiran.</p>
                    @if($rental->status === 'paid' || $rental->status === 'confirmed')
                        <p class="font-medium">Proses pengembalian dana (refund) dapat memakan waktu 3-7 hari kerja sesuai kebijakan admin kost.</p>
                    @endif
                </div>
            </x-alert-banner>
            
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
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('cancellation_reason') border-error-500 @enderror"
                        placeholder="Bantu kami memahami mengapa Anda membatalkan rental ini...">{{ old('cancellation_reason') }}</textarea>
                    @error('cancellation_reason')
                        <p class="text-error-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Maksimal 1000 karakter</p>
                </div>
                
                @if($errors->has('error'))
                    <x-alert-banner variant="error" class="mb-6">
                        {{ $errors->first('error') }}
                    </x-alert-banner>
                @endif
                
                <div class="flex gap-3">
                    <a href="{{ route('rentals.show', $rental) }}" 
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-center hover:bg-gray-50 transition">
                        Kembali
                    </a>
                    <button type="submit"
                            class="flex-1 px-4 py-2 bg-error-600 text-white rounded-lg hover:bg-error-700 transition font-medium">
                        Konfirmasi Pembatalan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-base-layout>
