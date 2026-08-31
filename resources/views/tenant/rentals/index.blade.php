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
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Active Rentals -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Aktif</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['active'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- Pending Actions -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Perlu Tindakan</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['pending_actions'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- Completed -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Selesai</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['completed'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- Cancelled (1C) -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-error-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Dibatalkan</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['cancelled'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters/Tabs (Alpine.js) - Phase 1A, 1E, 2A, 2B -->
            <div class="mb-6" x-data="{
                filter: 'all',
                // Phase 1A: Fixed filter logic - pending tab excludes confirmed, added cancelled case
                shouldShowRental(status) {
                    if (this.filter === 'all') return true;
                    if (this.filter === status) return true;
                    if (this.filter === 'pending') {
                        return ['pending', 'paid'].includes(status); // Fixed: exclude confirmed
                    }
                    if (this.filter === 'cancelled') {
                        return status === 'cancelled';
                    }
                    return false;
                },
                // Phase 2B: Computed properties for dynamic badge counts
                rentals: {{ Js::from($rentals->map(fn($r) => ['status' => $r->status])->toArray()) }},
                get allCount() { return this.rentals.length },
                get activeCount() { return this.rentals.filter(r => r.status === 'active').length },
                get pendingCount() { return this.rentals.filter(r => ['pending','paid'].includes(r.status)).length },
                get completedCount() { return this.rentals.filter(r => r.status === 'completed').length },
                get cancelledCount() { return this.rentals.filter(r => r.status === 'cancelled').length },
                // Phase 1E: Keyboard navigation for tabs
                focusedTab: 0,
                tabs: ['all', 'active', 'pending', 'completed', 'cancelled'],
                handleArrowKey(direction) {
                    if (direction === 'right') {
                        this.focusedTab = (this.focusedTab + 1) % this.tabs.length;
                    } else {
                        this.focusedTab = (this.focusedTab - 1 + this.tabs.length) % this.tabs.length;
                    }
                    this.filter = this.tabs[this.focusedTab];
                }
            }">
                <!-- Phase 2A: Mobile Dropdown Filter (visible < 640px) -->
                <div class="mb-4 sm:hidden">
                    <label for="rental-filter-mobile" class="sr-only">Filter Rental</label>
                    <select 
                        id="rental-filter-mobile"
                        x-model="filter"
                        class="block w-full rounded-lg border-gray-300 py-2.5 pl-3 pr-10 text-base focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                        <option value="all">Semua Rental (<span x-text="allCount"></span>)</option>
                        <option value="active">Aktif (<span x-text="activeCount"></span>)</option>
                        <option value="pending">Perlu Tindakan (<span x-text="pendingCount"></span>)</option>
                        <option value="completed">Selesai (<span x-text="completedCount"></span>)</option>
                        <option value="cancelled">Dibatalkan (<span x-text="cancelledCount"></span>)</option>
                    </select>
                </div>

                <!-- Phase 1E: Desktop Tab filters with ARIA pattern (visible >= 640px) -->
                <div class="hidden sm:block overflow-x-auto -mx-6 px-6 sm:mx-0 sm:px-0">
                    <div role="tablist" 
                         aria-label="Filter rental berdasarkan status"
                         class="flex gap-2 min-w-max border-b border-gray-200 dark:border-gray-700">
                        <!-- All Tab -->
                        <button 
                            role="tab"
                            :aria-selected="filter === 'all'"
                            :tabindex="filter === 'all' ? 0 : -1"
                            @click="filter = 'all'; focusedTab = 0" 
                            @keydown.arrow-right.prevent="handleArrowKey('right')"
                            @keydown.arrow-left.prevent="handleArrowKey('left')"
                            :class="filter === 'all' ? 'border-primary-600 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-600 hover:border-gray-300 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="border-b-2 px-4 py-2.5 text-sm font-medium whitespace-nowrap focus:outline-none focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                            Semua
                            <span x-text="'(' + allCount + ')'" class="ml-1.5 text-xs text-gray-500 dark:text-gray-400"></span>
                        </button>
                        
                        <!-- Active Tab -->
                        <button 
                            role="tab"
                            :aria-selected="filter === 'active'"
                            :tabindex="filter === 'active' ? 0 : -1"
                            @click="filter = 'active'; focusedTab = 1" 
                            @keydown.arrow-right.prevent="handleArrowKey('right')"
                            @keydown.arrow-left.prevent="handleArrowKey('left')"
                            :class="filter === 'active' ? 'border-primary-600 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-600 hover:border-gray-300 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="border-b-2 px-4 py-2.5 text-sm font-medium whitespace-nowrap focus:outline-none focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                            Aktif
                            <span x-text="'(' + activeCount + ')'" class="ml-1.5 text-xs text-gray-500 dark:text-gray-400"></span>
                        </button>
                        
                        <!-- Pending Tab -->
                        <button 
                            role="tab"
                            :aria-selected="filter === 'pending'"
                            :tabindex="filter === 'pending' ? 0 : -1"
                            @click="filter = 'pending'; focusedTab = 2" 
                            @keydown.arrow-right.prevent="handleArrowKey('right')"
                            @keydown.arrow-left.prevent="handleArrowKey('left')"
                            :class="filter === 'pending' ? 'border-primary-600 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-600 hover:border-gray-300 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="border-b-2 px-4 py-2.5 text-sm font-medium whitespace-nowrap focus:outline-none focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                            Perlu Tindakan
                            <span x-text="'(' + pendingCount + ')'" class="ml-1.5 text-xs text-gray-500 dark:text-gray-400"></span>
                        </button>
                        
                        <!-- Completed Tab -->
                        <button 
                            role="tab"
                            :aria-selected="filter === 'completed'"
                            :tabindex="filter === 'completed' ? 0 : -1"
                            @click="filter = 'completed'; focusedTab = 3" 
                            @keydown.arrow-right.prevent="handleArrowKey('right')"
                            @keydown.arrow-left.prevent="handleArrowKey('left')"
                            :class="filter === 'completed' ? 'border-primary-600 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-600 hover:border-gray-300 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="border-b-2 px-4 py-2.5 text-sm font-medium whitespace-nowrap focus:outline-none focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                            Selesai
                            <span x-text="'(' + completedCount + ')'" class="ml-1.5 text-xs text-gray-500 dark:text-gray-400"></span>
                        </button>
                        
                        <!-- Phase 1A: Cancelled Tab -->
                        <button 
                            role="tab"
                            :aria-selected="filter === 'cancelled'"
                            :tabindex="filter === 'cancelled' ? 0 : -1"
                            @click="filter = 'cancelled'; focusedTab = 4" 
                            @keydown.arrow-right.prevent="handleArrowKey('right')"
                            @keydown.arrow-left.prevent="handleArrowKey('left')"
                            :class="filter === 'cancelled' ? 'border-primary-600 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-600 hover:border-gray-300 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="border-b-2 px-4 py-2.5 text-sm font-medium whitespace-nowrap focus:outline-none focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                            Dibatalkan
                            <span x-text="'(' + cancelledCount + ')'" class="ml-1.5 text-xs text-gray-500 dark:text-gray-400"></span>
                        </button>
                    </div>
                </div>

                <!-- Phase 1E: Rental List with tabpanel role and aria-live -->
                <div role="tabpanel" 
                     aria-live="polite" 
                     aria-atomic="false"
                     class="mt-6 space-y-4">
                    @forelse($rentals as $rental)
                        <div x-show="shouldShowRental('{{ $rental->status }}')"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $rental->room->roomType->kost->name }}
                                        </h4>
                                        <x-status-badge :status="$rental->status" type="rental" />
                                    </div>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $rental->room->roomType->name }} - Kamar {{ $rental->room->code }}
                                    </p>
                                    <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-gray-600 dark:text-gray-400">
                                        <span class="flex items-center gap-1">
                                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <strong>Mulai:</strong> {{ $rental->start_date->format('d M Y') }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <strong>Durasi:</strong> {{ $rental->duration_value }} {{ __($rental->duration_unit) }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            <strong>Total:</strong> Rp {{ number_format((float) $rental->grand_total, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    
                                    {{-- Phase 2C: Payment Deadline Indicator --}}
                                    @if(in_array($rental->status, ['pending', 'paid']))
                                        @php
                                            $hoursUntilExpiry = now()->diffInHours($rental->payment->expired_at, false);
                                            $isExpired = $hoursUntilExpiry < 0;
                                            $isNearDeadline = $hoursUntilExpiry > 0 && $hoursUntilExpiry <= 24;
                                        @endphp
                                        
                                        @if($isExpired)
                                            {{-- Expired payment --}}
                                            <div class="mt-3 inline-flex items-center gap-2 rounded-lg bg-error-50 px-3 py-2 dark:bg-error-900/20">
                                                <svg class="h-5 w-5 text-error-600 dark:text-error-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span class="text-sm font-medium text-error-700 dark:text-error-400">
                                                    Pembayaran Kedaluwarsa
                                                </span>
                                            </div>
                                        @elseif($isNearDeadline)
                                            {{-- Near deadline (within 24 hours) --}}
                                            <div class="mt-3 inline-flex items-center gap-2 rounded-lg bg-warning-50 px-3 py-2 dark:bg-warning-900/20">
                                                <svg class="h-5 w-5 text-warning-600 dark:text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span class="text-sm font-medium text-warning-700 dark:text-warning-400">
                                                    <strong>Deadline pembayaran:</strong> {{ $rental->payment->expired_at->diffForHumans() }}
                                                </span>
                                            </div>
                                        @else
                                            {{-- Normal deadline display (>24 hours) --}}
                                            <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                                                <strong>Deadline pembayaran:</strong> {{ $rental->payment->expired_at->format('d M Y, H:i') }} WIB
                                                <span class="text-xs text-gray-500 dark:text-gray-500">
                                                    ({{ $rental->payment->expired_at->diffForHumans() }})
                                                </span>
                                            </p>
                                        @endif
                                    @endif

                                    {{-- Phase 2C: Cancellation Reason (FR-127) --}}
                                    @if($rental->status === 'cancelled' && $rental->cancelled_reason)
                                        <div class="mt-3 rounded-lg bg-gray-50 p-3 dark:bg-gray-700/50">
                                            <p class="text-sm text-error-600 dark:text-error-400">
                                                <strong>Alasan Pembatalan:</strong> {{ $rental->cancelled_reason }}
                                            </p>
                                            @if($rental->cancelled_at)
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                    Dibatalkan pada: {{ $rental->cancelled_at->format('d M Y, H:i') }} WIB
                                                </p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                <div class="shrink-0">
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
                        {{-- Phase 1D: Contextual Empty States --}}
                        <div class="rounded-lg bg-white p-12 text-center shadow dark:bg-gray-800">
                            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Belum ada rental</h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Mulai cari kost dan buat booking pertama Anda</p>
                            <a href="{{ route('marketplace.index') }}" 
                               class="mt-4 inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700 focus:outline-none focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                                Cari Kost
                            </a>
                        </div>
                    @endforelse

                    {{-- Phase 1D: Per-filter empty states (shown when filtered results are empty) --}}
                    @if($rentals->isNotEmpty())
                        <template x-if="filter === 'active' && activeCount === 0">
                            <div class="rounded-lg bg-white p-12 text-center shadow dark:bg-gray-800">
                                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Tidak ada rental aktif saat ini</h3>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Rental aktif akan muncul di sini setelah pembayaran dan dokumen diverifikasi</p>
                            </div>
                        </template>

                        <template x-if="filter === 'pending' && pendingCount === 0">
                            <div class="rounded-lg bg-white p-12 text-center shadow dark:bg-gray-800">
                                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Tidak ada rental yang perlu tindakan</h3>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Semua rental Anda sudah diproses</p>
                            </div>
                        </template>

                        <template x-if="filter === 'completed' && completedCount === 0">
                            <div class="rounded-lg bg-white p-12 text-center shadow dark:bg-gray-800">
                                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Belum ada rental selesai</h3>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Rental yang sudah berakhir akan muncul di sini</p>
                            </div>
                        </template>

                        <template x-if="filter === 'cancelled' && cancelledCount === 0">
                            <div class="rounded-lg bg-white p-12 text-center shadow dark:bg-gray-800">
                                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Tidak ada rental dibatalkan</h3>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Rental yang dibatalkan akan muncul di sini</p>
                            </div>
                        </template>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-base-layout>
