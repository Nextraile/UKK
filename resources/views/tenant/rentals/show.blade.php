<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Detail Rental #{{ $rental->id }}
            </h2>
            <a href="{{ route('rentals.index') }}" class="text-sm text-primary-600 hover:text-primary-700">
                ← Kembali ke Daftar Rental
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Left Column (2/3 width) -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Rental Info Card -->
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                                {{ $rental->room->roomType->kost->name }}
                            </h3>
                            <!-- Status Badge -->
                            <span class="inline-flex rounded-full px-4 py-2 text-sm font-semibold
                                @if($rental->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($rental->status === 'paid') bg-blue-100 text-blue-800
                                @elseif($rental->status === 'confirmed') bg-purple-100 text-purple-800
                                @elseif($rental->status === 'active') bg-green-100 text-green-800
                                @elseif($rental->status === 'completed') bg-gray-100 text-gray-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst($rental->status) }}
                            </span>
                        </div>

                        <dl class="grid grid-cols-2 gap-4">
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
                                        <a href="{{ route('rentals.cancel.form', $rental) }}"
                                           class="inline-flex items-center px-4 py-2 border border-red-300 rounded-lg text-red-700 hover:bg-red-50 transition">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            Batalkan Rental
                                        </a>
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
                                <div class="mb-4 rounded-lg bg-yellow-50 p-4 dark:bg-yellow-900/20">
                                    <p class="text-sm font-semibold text-yellow-800 dark:text-yellow-200">
                                        Selesaikan pembayaran sebelum {{ $rental->payment->expired_at->format('d M Y H:i') }}
                                        ({{ $rental->payment->expired_at->diffForHumans() }})
                                    </p>
                                </div>
                                
                                <div class="text-center">
                                    <a href="{{ route('rentals.payment.show', $rental) }}" class="inline-flex items-center rounded-md bg-primary-600 px-6 py-3 text-sm font-semibold text-white hover:bg-primary-700">
                                        Upload Bukti Pembayaran
                                    </a>
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                        Transfer ke rekening yang tertera, kemudian upload bukti transfer
                                    </p>
                                </div>
                            @else
                                <div class="rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
                                    <p class="text-sm font-semibold text-red-800 dark:text-red-200">
                                        Deadline pembayaran terlewati. Rental akan dibatalkan otomatis oleh sistem.
                                    </p>
                                </div>
                            @endif
                        </div>
                    @elseif(in_array($rental->status, ['paid', 'confirmed', 'active', 'completed']))
                        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                            <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-gray-100">Status Pembayaran</h3>
                            <div class="flex items-center rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="ml-3 text-sm font-semibold text-green-800 dark:text-green-200">
                                    @if($rental->payment->verified_at)
                                        Pembayaran terverifikasi pada {{ $rental->payment->verified_at->format('d M Y H:i') }}
                                    @else
                                        Pembayaran telah diterima, menunggu verifikasi admin
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endif

                    <!-- Document Section (Stub) -->
                    @if($rental->status === 'paid')
                        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                            <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-gray-100">Dokumen Administrasi</h3>
                            <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                                Upload dokumen yang diperlukan untuk melengkapi proses rental
                            </p>
                            
                            <div class="space-y-2">
                                @foreach($rental->room->roomType->kost->documentRequirements as $requirement)
                                    <div class="flex items-center justify-between rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                                        <div>
                                            <p class="font-semibold text-gray-900 dark:text-gray-100">
                                                {{ $requirement->document_type }}
                                                @if($requirement->is_required)
                                                    <span class="ml-1 text-red-600">*</span>
                                                @endif
                                            </p>
                                            <p class="text-xs text-gray-600 dark:text-gray-400">{{ $requirement->reason }}</p>
                                        </div>
                                        <button class="text-sm text-primary-600 hover:text-primary-700">Upload</button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Right Column (1/3 width) — Sidebar -->
                <div class="space-y-6">
                    <!-- Quick Actions Card -->
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-gray-100">Aksi Cepat</h3>
                        <div class="space-y-3">
                            @if($rental->status === 'pending')
                                <a href="#" class="block w-full rounded-md bg-primary-600 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-primary-700">
                                    Upload Bukti Bayar
                                </a>
                            @elseif($rental->status === 'paid')
                                <button class="block w-full rounded-md bg-primary-600 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-primary-700">
                                    Upload Dokumen
                                </button>
                            @elseif($rental->status === 'completed')
                                <a href="#" class="block w-full rounded-md bg-primary-600 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-primary-700">
                                    Tulis Review
                                </a>
                            @endif
                            
                            @if(!in_array($rental->status, ['completed', 'cancelled']))
                                <button class="block w-full rounded-md border border-red-600 px-4 py-2 text-center text-sm font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                                    Batalkan Rental
                                </button>
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
