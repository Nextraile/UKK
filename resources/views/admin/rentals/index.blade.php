@extends('layouts.admin')

@section('title', 'Manajemen Rental')

@section('content')
<div class="space-y-6">
    <x-page-header 
        title="Manajemen Rental"
        subtitle="Total: {{ $rentals->total() }} rental"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Rental'],
        ]"
    />

    <!-- Filters -->
    <div class="flex items-center space-x-4">
        <form method="GET" action="{{ route('admin.rentals.index') }}" class="flex items-center space-x-4">
            <!-- Filter by Kost -->
            <div>
                <select name="kost_id" 
                        onchange="this.form.submit()"
                        class="rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500">
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
                        class="rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500">
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
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            ID
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Tenant
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Kost / Kamar
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Durasi
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Total
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($rentals as $rental)
                        <tr>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                #{{ $rental->id }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <div class="font-medium text-gray-900">{{ $rental->user->name }}</div>
                                <div class="text-gray-500">{{ $rental->user->email }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="font-medium text-gray-900">{{ $rental->room->roomType->kost->name }}</div>
                                <div class="text-gray-500">{{ $rental->room->roomType->name }} - {{ $rental->room->name }}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                {{ $rental->duration_value }} {{ __($rental->duration_unit) }}
                                <div class="text-xs text-gray-500">{{ $rental->start_date->format('d M Y') }}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-gray-900">
                                Rp {{ number_format((float) $rental->grand_total, 0, ',', '.') }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <x-status-badge :status="$rental->status" type="rental" />
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <x-touch-button variant="ghost" size="sm" href="{{ route('admin.rentals.show', $rental) }}" aria-label="Detail rental {{ $rental->user->name }}">
                                    Detail
                                </x-touch-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">
                                Belum ada rental untuk kost Anda
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($rentals->hasPages())
            <div class="border-t border-gray-200 px-6 py-4">
                {{ $rentals->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
