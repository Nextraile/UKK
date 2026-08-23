@extends('layouts.admin')

@section('title', 'Edit Kost')

@section('content')
<div class="max-w-3xl">
    <form method="POST" action="{{ route('admin.kosts.update', $kost) }}" class="space-y-6">
        @csrf
        @method('PATCH')

        <!-- Name -->
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Nama Kost <span class="text-error-600">*</span></label>
            <input type="text" name="name" id="name" value="{{ old('name', $kost->name) }}" required
                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
            @error('name')
                <p class="mt-1 text-sm text-error-700">{{ $message }}</p>
            @enderror
        </div>

        <!-- Contact Number -->
        <div>
            <label for="contact_number" class="block text-sm font-medium text-gray-700">Nomor Kontak <span class="text-error-600">*</span></label>
            <input type="text" name="contact_number" id="contact_number" value="{{ old('contact_number', $kost->contact_number) }}" required
                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
            @error('contact_number')
                <p class="mt-1 text-sm text-error-700">{{ $message }}</p>
            @enderror
        </div>

        <!-- Description -->
        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
            <textarea name="description" id="description" rows="4"
                      class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">{{ old('description', $kost->description) }}</textarea>
            @error('description')
                <p class="mt-1 text-sm text-error-700">{{ $message }}</p>
            @enderror
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-3">
            <a href="{{ route('admin.kosts.show', $kost) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                Batal
            </a>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
