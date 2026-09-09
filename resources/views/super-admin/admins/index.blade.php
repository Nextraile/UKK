<x-base-layout 
    title="Admin Management - Super Admin - SewaKost"
    variant="admin-sidebar"
    page-title="Admin Management">
    
    {{-- Page Header --}}
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Admin Management</h1>
        <a href="{{ route('super-admin.admins.create') }}">
            <button class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                Buat Admin Baru
            </button>
        </a>
    </div>

    {{-- Filter Toggle --}}
    <div x-data="{ showDeleted: {{ $showDeleted ? 'true' : 'false' }} }" class="mb-4">
        <label class="flex items-center cursor-pointer">
            <input type="checkbox" x-model="showDeleted" 
                   @change="window.location.href = '{{ route('super-admin.admins.index') }}' + (showDeleted ? '?show_deleted=1' : '')"
                   class="w-4 h-4 text-primary-600 rounded">
            <span class="ml-2 text-sm">Show Deleted Admins</span>
        </label>
    </div>

    {{-- Admin Table --}}
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($admins as $admin)
                <tr class="hover:bg-gray-50" x-data="{ showDeleteModal: false }">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            {{ $admin->first_name }} {{ $admin->last_name }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-500">{{ $admin->email }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-500">{{ $admin->phone }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-500">{{ $admin->created_at->format('d M Y') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($admin->trashed())
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-error-100 text-error-800">
                                Deleted
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-success-100 text-success-800">
                                Active
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('super-admin.admins.edit', $admin) }}" class="text-primary-600 hover:text-primary-900 mr-3">
                            Edit
                        </a>
                        @unless($admin->trashed())
                        <button @click="showDeleteModal = true" type="button" class="text-error-600 hover:text-error-900">
                            Delete
                        </button>

                        {{-- Delete Confirmation Modal --}}
                        <div x-show="showDeleteModal" 
                             x-cloak
                             class="fixed inset-0 z-50 overflow-y-auto" 
                             aria-labelledby="modal-title-{{ $admin->id }}" 
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
                                    <h3 class="text-lg font-semibold text-gray-900 text-center mb-2" id="modal-title-{{ $admin->id }}">
                                        Hapus Akun Admin?
                                    </h3>
                                    
                                    {{-- Message --}}
                                    <p class="text-sm text-gray-600 text-center mb-6">
                                        Admin <strong>{{ $admin->first_name }} {{ $admin->last_name }}</strong> akan kehilangan akses ke sistem. Tindakan ini dapat dibatalkan dengan restore soft delete.
                                    </p>
                                    
                                    {{-- Actions --}}
                                    <div class="flex gap-3">
                                        <button @click="showDeleteModal = false" 
                                                type="button" 
                                                class="flex-1 px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                            Batal
                                        </button>
                                        <form method="POST" action="{{ route('super-admin.admins.destroy', $admin) }}" class="flex-1">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                                Hapus Admin
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endunless
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada Admin terdaftar</h3>
                        <p class="mt-1 text-sm text-gray-500">Buat admin pertama untuk mengelola kost.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $admins->links() }}
    </div>
</x-base-layout>
