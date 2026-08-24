@extends('layouts.admin')

@section('title', 'Tambah Kategori')

@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <a href="{{ route('super-admin.categories.index') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali ke Daftar Kategori
        </a>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <form method="POST" action="{{ route('super-admin.categories.store') }}" x-data="{ name: '' }">
            @csrf

            {{-- Name --}}
            <div class="mb-6">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Kategori <span class="text-error-600">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       x-model="name"
                       value="{{ old('name') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('name') border-error-500 @enderror"
                       placeholder="Contoh: Putra, Putri, Campur"
                       required
                       autofocus>
                @error('name')
                    <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Slug --}}
            <div class="mb-6">
                <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
                    Slug <span class="text-gray-500 text-xs">(otomatis dibuat dari nama)</span>
                </label>
                <input type="text" 
                       name="slug" 
                       id="slug" 
                       x-model="name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '')"
                       value="{{ old('slug') }}"
                       readonly
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed font-mono text-sm @error('slug') border-error-500 @enderror"
                       placeholder="otomatis-dari-nama">
                @error('slug')
                    <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Deskripsi <span class="text-gray-500 text-xs">(opsional)</span>
                </label>
                <textarea name="description" 
                          id="description" 
                          rows="4"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('description') border-error-500 @enderror"
                          placeholder="Deskripsi singkat tentang kategori ini">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end space-x-3">
                <a href="{{ route('super-admin.categories.index') }}" 
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit" 
                        class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg">
                    Simpan Kategori
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
