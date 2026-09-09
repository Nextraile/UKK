<x-base-layout 
    title="Manajemen Kategori - Super Admin - SewaKost"
    variant="admin-sidebar"
    page-title="Manajemen Kategori">
    
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex justify-between items-center">
        <div>
            <p class="text-sm text-gray-600">Kelola kategori kost (Putra, Putri, Campur)</p>
        </div>
        <a href="{{ route('super-admin.categories.create') }}" 
           class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Kategori
        </a>
    </div>

    {{-- Table --}}
    @if ($categories->count() > 0)
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <caption class="sr-only">Daftar kategori kost</caption>
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Kost</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($categories as $category)
                        <tr x-data="{ showDeleteModal: false }">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ $category->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $category->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-mono">{{ $category->slug }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ Str::limit($category->description ?? '-', 50) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $category->kosts_count }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                <a href="{{ route('super-admin.categories.edit', $category) }}" 
                                   class="text-primary-600 hover:text-primary-900"
                                   aria-label="Edit {{ $category->name }}">
                                    Edit
                                </a>
                                <button @click="showDeleteModal = true"
                                        type="button" 
                                        class="text-error-600 hover:text-error-900"
                                        aria-label="Hapus {{ $category->name }}">
                                    Hapus
                                </button>

                                {{-- Delete Confirmation Modal --}}
                                <div x-show="showDeleteModal" 
                                     x-cloak
                                     class="fixed inset-0 z-50 overflow-y-auto" 
                                     aria-labelledby="modal-title-{{ $category->id }}" 
                                     role="dialog" 
                                     aria-modal="true"
                                     @keydown.escape.window="showDeleteModal = false">
                                    
                                    {{-- Backdrop --}}
                                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                                         @click="showDeleteModal = false"></div>
                                    
                                    {{-- Modal Content --}}
                                    <div class="flex items-center justify-center min-h-screen p-4">
                                        <div class="relative bg-white rounded-lg max-w-md w-full p-6"
                                             @click.stop>
                                            {{-- Icon --}}
                                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                                                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                </svg>
                                            </div>
                                            
                                            {{-- Title --}}
                                            <h3 class="text-lg font-semibold text-gray-900 text-center mb-2" id="modal-title-{{ $category->id }}">
                                                Hapus Kategori?
                                            </h3>
                                            
                                            {{-- Message --}}
                                            <p class="text-sm text-gray-600 text-center mb-6">
                                                Kategori <strong>{{ $category->name }}</strong> digunakan oleh 
                                                <strong>{{ $category->kosts_count }} kost</strong>. 
                                                Soft delete tidak akan menghapus kost yang sudah menggunakan kategori ini.
                                            </p>
                                            
                                            {{-- Actions --}}
                                            <div class="flex gap-3">
                                                <button @click="showDeleteModal = false" 
                                                        type="button" 
                                                        class="flex-1 px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                                    Batal
                                                </button>
                                                <form method="POST" action="{{ route('super-admin.categories.destroy', $category) }}" class="flex-1">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                                        Hapus Kategori
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div>
            {{ $categories->links() }}
        </div>
    @else
        <div class="bg-white rounded-lg border border-gray-200 p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada kategori</h3>
            <p class="mt-1 text-sm text-gray-500">Mulai dengan membuat kategori pertama.</p>
        </div>
    @endif
</div>
</x-base-layout>
