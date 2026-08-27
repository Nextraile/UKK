<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Manajemen Rental
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="mb-6 flex items-center space-x-4">
                <form method="GET" action="{{ route('admin.rentals.index') }}" class="flex items-center space-x-4">
                    <!-- Filter by Kost -->
                    <div>
                        <select name="kost_id" 
                                onchange="this.form.submit()"
                                class="rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            <option value="">Semua Kost</option>
                            @foreach($kosts as $kost)
                                <option value="{{ $kost->id }}" {{ request('kost_id') == $kost->id ? 'selected' : '' }}>
                                    {{ $kost->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter by Status -->
                    <div>
                        <select name="status" 
                                onchange="this.form.submit()"
                                class="rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid (Perlu Verifikasi Dokumen)</option>
                            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    @if(request()->hasAny(['kost_id', 'status']))
                        <a href="{{ route('admin.rentals.index') }}" 
                           class="text-sm text-primary-600 hover:text-primary-700">
                            Reset Filter
                        </a>
                    @endif
                </form>
            </div>

            <!-- Rentals Table -->
            <x-card>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    ID
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Tenant
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Kost / Kamar
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Durasi
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Total
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                            @forelse($rentals as $rental)
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        #{{ $rental->id }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $rental->user->name }}</div>
                                        <div class="text-gray-500 dark:text-gray-400">{{ $rental->user->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $rental->room->roomType->kost->name }}</div>
                                        <div class="text-gray-500 dark:text-gray-400">{{ $rental->room->roomType->name }} - {{ $rental->room->name }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $rental->duration_value }} {{ __($rental->duration_unit) }}
                                        <div class="text-xs text-gray-500">{{ $rental->start_date->format('d M Y') }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        Rp {{ number_format((float) $rental->grand_total, 0, ',', '.') }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold
                                            @if($rental->status === 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($rental->status === 'paid') bg-blue-100 text-blue-800
                                            @elseif($rental->status === 'confirmed') bg-purple-100 text-purple-800
                                            @elseif($rental->status === 'active') bg-green-100 text-green-800
                                            @elseif($rental->status === 'completed') bg-gray-100 text-gray-800
                                            @else bg-red-100 text-red-800
                                            @endif">
                                            {{ ucfirst($rental->status) }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <a href="{{ route('admin.rentals.show', $rental) }}" 
                                           class="text-primary-600 hover:text-primary-900">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                        Belum ada rental untuk kost Anda
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($rentals->hasPages())
                    <div class="mt-4 border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                        {{ $rentals->links() }}
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</x-app-layout>
