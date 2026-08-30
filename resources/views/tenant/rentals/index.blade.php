<x-base-layout 
    title="Rental Saya - SewaKost"
    variant="full-width">
    
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <x-page-header 
                title="Rental Saya"
                subtitle="Halo, {{ auth()->user()->first_name }} — Kelola rental kost Anda di sini"
                :breadcrumbs="[
                    ['label' => 'Rental'],
                ]"
            />

            <!-- Stat Cards -->
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <!-- Active Rentals -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Rentals</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['active'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- Pending Actions -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending Actions</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['pending_actions'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- Completed -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Completed</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['completed'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters/Tabs (Alpine.js) -->
            <div class="mb-6" x-data="{
                filter: 'all',
                shouldShowRental(status) {
                    if (this.filter === 'all') return true;
                    if (this.filter === status) return true;
                    if (this.filter === 'pending') {
                        return ['pending', 'paid', 'confirmed'].includes(status);
                    }
                    return false;
                }
            }">
                <!-- Tab filters with horizontal scroll on mobile -->
                <div class="overflow-x-auto -mx-6 px-6 sm:mx-0 sm:px-0">
                    <div class="flex gap-2 min-w-max border-b border-gray-200 dark:border-gray-700">
                        <button @click="filter = 'all'" 
                                :class="filter === 'all' ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-600 hover:border-gray-300 hover:text-gray-800 dark:text-gray-400'"
                                class="border-b-2 px-4 py-2 text-sm font-medium whitespace-nowrap">
                            Semua
                        </button>
                        <button @click="filter = 'active'" 
                                :class="filter === 'active' ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-600 hover:border-gray-300 hover:text-gray-800 dark:text-gray-400'"
                                class="border-b-2 px-4 py-2 text-sm font-medium whitespace-nowrap">
                            Active
                        </button>
                        <button @click="filter = 'pending'" 
                                :class="filter === 'pending' ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-600 hover:border-gray-300 hover:text-gray-800 dark:text-gray-400'"
                                class="border-b-2 px-4 py-2 text-sm font-medium whitespace-nowrap">
                            Pending
                        </button>
                        <button @click="filter = 'completed'" 
                                :class="filter === 'completed' ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-600 hover:border-gray-300 hover:text-gray-800 dark:text-gray-400'"
                                class="border-b-2 px-4 py-2 text-sm font-medium whitespace-nowrap">
                            Completed
                        </button>
                    </div>
                </div>

                <!-- Rental List -->
                <div class="mt-6 space-y-4">
                    @forelse($rentals as $rental)
                        <div x-show="shouldShowRental('{{ $rental->status }}')"
                             class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3">
                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $rental->room->roomType->kost->name }}
                                        </h4>
                                        <x-status-badge :status="$rental->status" type="rental" />
                                    </div>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $rental->room->roomType->name }} - {{ $rental->room->name }}
                                    </p>
                                    <div class="mt-2 flex items-center space-x-4 text-sm text-gray-600 dark:text-gray-400">
                                        <span>
                                            <strong>Mulai:</strong> {{ $rental->start_date->format('d M Y') }}
                                        </span>
                                        <span>
                                            <strong>Durasi:</strong> {{ $rental->duration_value }} {{ __($rental->duration_unit) }}
                                        </span>
                                        <span>
                                            <strong>Total:</strong> Rp {{ number_format((float) $rental->grand_total, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    
                    @if($rental->status === 'pending' && $rental->payment->expired_at->isFuture())
                        <p class="mt-2 text-sm text-error-700">
                            <strong>Deadline pembayaran:</strong> {{ $rental->payment->expired_at->diffForHumans() }}
                        </p>
                    @endif
                                </div>
                                <div>
                                    <x-touch-button 
                                        variant="primary" 
                                        size="md" 
                                        :href="route('rentals.show', $rental)">
                                        Lihat Detail
                                    </x-touch-button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <!-- Empty State -->
                        <div class="rounded-lg bg-white p-12 text-center shadow dark:bg-gray-800">
                            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Belum ada rental</h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Mulai cari kost dan buat booking pertama Anda</p>
                            <a href="{{ route('marketplace.index') }}" 
                               class="mt-4 inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">
                                Cari Kost
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-base-layout>
