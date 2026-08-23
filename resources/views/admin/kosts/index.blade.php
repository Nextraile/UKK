@extends('layouts.admin')

@section('title', 'Kelola Kost')

@section('content')
<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex justify-between items-center">
        <p class="text-sm text-gray-600">Total: {{ $kosts->total() }} kost</p>
        <a href="{{ route('admin.kosts.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">
            Buat Kost Baru
        </a>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kost</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dibuat</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($kosts as $kost)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $kost->name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-500">
                            {{ $kost->address->city ?? '-' }}, {{ $kost->address->district ?? '-' }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                            @if($kost->status === 'draft') bg-gray-100 text-gray-800
                            @elseif($kost->status === 'pending_review') bg-yellow-100 text-yellow-800
                            @elseif($kost->status === 'approved') bg-green-100 text-green-800
                            @elseif($kost->status === 'active') bg-blue-100 text-blue-800
                            @elseif($kost->status === 'rejected') bg-red-100 text-red-800
                            @endif">
                            {{ ucfirst(str_replace('_', ' ', $kost->status)) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $kost->created_at->format('d M Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                        <a href="{{ route('admin.kosts.show', $kost) }}" class="text-primary-600 hover:text-primary-900">Lihat</a>
                        @can('update', $kost)
                        <a href="{{ route('admin.kosts.edit', $kost) }}" class="text-primary-600 hover:text-primary-900">Edit</a>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">
                        Belum ada kost. <a href="{{ route('admin.kosts.create') }}" class="text-primary-600 hover:text-primary-900">Buat kost pertama Anda</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div>
        {{ $kosts->links() }}
    </div>
</div>
@endsection
