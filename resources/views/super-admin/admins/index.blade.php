@extends('layouts.admin')

@section('title', 'Admin Management')

@section('content')
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
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($admins as $admin)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            {{ $admin->first_name }} {{ $admin->last_name }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-500">{{ $admin->email }}</div>
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
                        <form method="POST" action="{{ route('super-admin.admins.destroy', $admin) }}" 
                              class="inline" onsubmit="return confirm('Deactivate this admin account?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-error-600 hover:text-error-900">
                                Delete
                            </button>
                        </form>
                        @endunless
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                        Belum ada Admin terdaftar.
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
@endsection
