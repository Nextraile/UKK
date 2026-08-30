<x-base-layout 
    title="Kelola Kost - Admin - SewaKost"
    variant="admin-sidebar"
    page-title="Kost Saya">
    
<div class="space-y-6">
            <x-page-header 
                title="Kost Saya"
                subtitle="Kelola properti kost yang Anda miliki"
                :breadcrumbs="[
                    ['label' => 'Kost'],
                ]"
            >
        <x-slot:actions>
            <a href="{{ route('admin.kosts.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
                Buat Kost Baru
            </a>
        </x-slot:actions>
    </x-page-header>

    <!-- Table -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kost</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dibuat</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
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
                        <x-status-badge :status="$kost->status" type="kost" />
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $kost->created_at->format('d M Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                        <x-touch-button variant="ghost" size="sm" href="{{ route('admin.kosts.show', $kost) }}" aria-label="Lihat detail {{ $kost->name }}">
                            Lihat
                        </x-touch-button>
                        @can('update', $kost)
                        <x-touch-button variant="ghost" size="sm" href="{{ route('admin.kosts.edit', $kost) }}" aria-label="Edit {{ $kost->name }}">
                            Edit
                        </x-touch-button>
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
    </div>

    <!-- Pagination -->
    <div>
        {{ $kosts->links() }}
    </div>
</div>
</x-base-layout>
