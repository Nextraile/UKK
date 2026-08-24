@extends('layouts.admin')

@section('title', 'Detail Kategori')

@section('content')
<div class="max-w-3xl">
    <div class="mb-6">
        <a href="{{ route('super-admin.categories.index') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali ke Daftar Kategori
        </a>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        {{-- Header --}}
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">{{ $category->name }}</h2>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary-100 text-primary-800">
                    {{ $category->kosts_count }} Kost
                </span>
            </div>
        </div>

        {{-- Content --}}
        <div class="px-6 py-4 space-y-4">
            <div>
                <dt class="text-sm font-medium text-gray-500">ID</dt>
                <dd class="mt-1 text-sm text-gray-900">#{{ $category->id }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">Nama</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $category->name }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">Slug</dt>
                <dd class="mt-1 text-sm text-gray-900 font-mono bg-gray-50 px-3 py-1 rounded inline-block">{{ $category->slug }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">Deskripsi</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $category->description ?? '-' }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">Jumlah Kost Menggunakan Kategori Ini</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $category->kosts_count }} kost</dd>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Dibuat</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $category->created_at->format('d M Y, H:i') }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Terakhir Diperbarui</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $category->updated_at->format('d M Y, H:i') }}</dd>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3">
            <a href="{{ route('super-admin.categories.edit', $category) }}" 
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
            </a>
            <form method="POST" action="{{ route('super-admin.categories.destroy', $category) }}" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-error-600 hover:bg-error-700 rounded-lg"
                        onclick="return confirm('Yakin ingin menghapus kategori {{ $category->name }}?\n\nKategori yang dihapus tidak akan muncul di dropdown Admin, tapi kost yang sudah menggunakan kategori ini tidak akan terpengaruh.')">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Hapus
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
