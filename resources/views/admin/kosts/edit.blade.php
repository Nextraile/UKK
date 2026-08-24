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

        <!-- Facilities (Dynamic List with Alpine.js) -->
        <div x-data="{ items: @js(old('facilities', $kost->facilities ?? [])) }">
            <label class="block text-sm font-medium text-gray-700">Fasilitas Kost</label>
            <p class="mt-1 text-sm text-gray-500">Tambahkan fasilitas yang tersedia di kost Anda (WiFi, AC, Parkir, dll.)</p>
            
            <div class="mt-2 space-y-2">
                <template x-for="(item, index) in items" :key="index">
                    <div class="flex gap-2">
                        <input type="text" 
                               :name="'facilities[' + index + ']'" 
                               x-model="items[index]"
                               placeholder="Contoh: WiFi Gratis"
                               class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <button type="button" 
                                @click="items.splice(index, 1)"
                                class="px-3 py-2 text-sm font-medium text-error-600 hover:text-error-700 hover:bg-error-50 rounded-lg">
                            Hapus
                        </button>
                    </div>
                </template>
                
                <button type="button" 
                        @click="items.push('')"
                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-primary-600 hover:text-primary-700 hover:bg-primary-50 rounded-lg">
                    <span class="mr-1">+</span> Tambah Fasilitas
                </button>
            </div>
            
            @error('facilities')
                <p class="mt-1 text-sm text-error-700">{{ $message }}</p>
            @enderror
            @error('facilities.*')
                <p class="mt-1 text-sm text-error-700">{{ $message }}</p>
            @enderror
            
            <!-- Fallback for JS disabled -->
            <noscript>
                <div class="mt-2">
                    <textarea name="facilities_text" rows="5" 
                              placeholder="Tulis satu fasilitas per baris"
                              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">{{ is_array($kost->facilities) ? implode("\n", $kost->facilities) : '' }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">JavaScript tidak aktif. Tulis satu fasilitas per baris.</p>
                </div>
            </noscript>
        </div>

        <!-- Rules (Dynamic List with Alpine.js) -->
        <div x-data="{ items: @js(old('rules', $kost->rules ?? [])) }">
            <label class="block text-sm font-medium text-gray-700">Peraturan Kost</label>
            <p class="mt-1 text-sm text-gray-500">Tambahkan peraturan yang berlaku di kost Anda (Tidak merokok, Tidak bawa hewan, dll.)</p>
            
            <div class="mt-2 space-y-2">
                <template x-for="(item, index) in items" :key="index">
                    <div class="flex gap-2">
                        <input type="text" 
                               :name="'rules[' + index + ']'" 
                               x-model="items[index]"
                               placeholder="Contoh: Dilarang merokok di dalam kamar"
                               class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <button type="button" 
                                @click="items.splice(index, 1)"
                                class="px-3 py-2 text-sm font-medium text-error-600 hover:text-error-700 hover:bg-error-50 rounded-lg">
                            Hapus
                        </button>
                    </div>
                </template>
                
                <button type="button" 
                        @click="items.push('')"
                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-primary-600 hover:text-primary-700 hover:bg-primary-50 rounded-lg">
                    <span class="mr-1">+</span> Tambah Peraturan
                </button>
            </div>
            
            @error('rules')
                <p class="mt-1 text-sm text-error-700">{{ $message }}</p>
            @enderror
            @error('rules.*')
                <p class="mt-1 text-sm text-error-700">{{ $message }}</p>
            @enderror
            
            <!-- Fallback for JS disabled -->
            <noscript>
                <div class="mt-2">
                    <textarea name="rules_text" rows="5" 
                              placeholder="Tulis satu peraturan per baris"
                              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">{{ is_array($kost->rules) ? implode("\n", $kost->rules) : '' }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">JavaScript tidak aktif. Tulis satu peraturan per baris.</p>
                </div>
            </noscript>
        </div>

        <!-- Address Section -->
        <div class="border-t pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Alamat Kost</h3>
            
            <!-- Full Address -->
            <div class="mb-4">
                <label for="full_address" class="block text-sm font-medium text-gray-700">Alamat Lengkap</label>
                <textarea name="full_address" id="full_address" rows="3"
                          class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">{{ old('full_address', $kost->address->full_address ?? '') }}</textarea>
                @error('full_address')
                    <p class="mt-1 text-sm text-error-700">{{ $message }}</p>
                @enderror
            </div>

            <!-- District -->
            <div class="mb-4">
                <label for="district" class="block text-sm font-medium text-gray-700">Kecamatan</label>
                <input type="text" name="district" id="district" value="{{ old('district', $kost->address->district ?? '') }}"
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                @error('district')
                    <p class="mt-1 text-sm text-error-700">{{ $message }}</p>
                @enderror
            </div>

            <!-- City -->
            <div class="mb-4">
                <label for="city" class="block text-sm font-medium text-gray-700">Kota</label>
                <input type="text" name="city" id="city" value="{{ old('city', $kost->address->city ?? '') }}"
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                @error('city')
                    <p class="mt-1 text-sm text-error-700">{{ $message }}</p>
                @enderror
            </div>

            <!-- Province -->
            <div class="mb-4">
                <label for="province" class="block text-sm font-medium text-gray-700">Provinsi</label>
                <input type="text" name="province" id="province" value="{{ old('province', $kost->address->province ?? '') }}"
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                @error('province')
                    <p class="mt-1 text-sm text-error-700">{{ $message }}</p>
                @enderror
            </div>

            <!-- Postal Code -->
            <div class="mb-4">
                <label for="postal_code" class="block text-sm font-medium text-gray-700">Kode Pos</label>
                <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code', $kost->address->postal_code ?? '') }}"
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                @error('postal_code')
                    <p class="mt-1 text-sm text-error-700">{{ $message }}</p>
                @enderror
            </div>

            <!-- Country -->
            <div class="mb-4">
                <label for="country" class="block text-sm font-medium text-gray-700">Negara</label>
                <input type="text" name="country" id="country" value="{{ old('country', $kost->address->country ?? 'Indonesia') }}"
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                @error('country')
                    <p class="mt-1 text-sm text-error-700">{{ $message }}</p>
                @enderror
            </div>

            <!-- Coordinates -->
            <div class="grid grid-cols-2 gap-4">
                <!-- Latitude -->
                <div>
                    <label for="latitude" class="block text-sm font-medium text-gray-700">Latitude</label>
                    <input type="number" name="latitude" id="latitude" step="0.00000001" value="{{ old('latitude', $kost->address->latitude ?? '') }}"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <p class="mt-1 text-xs text-gray-500">Contoh: -6.917464</p>
                    @error('latitude')
                        <p class="mt-1 text-sm text-error-700">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Longitude -->
                <div>
                    <label for="longitude" class="block text-sm font-medium text-gray-700">Longitude</label>
                    <input type="number" name="longitude" id="longitude" step="0.00000001" value="{{ old('longitude', $kost->address->longitude ?? '') }}"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <p class="mt-1 text-xs text-gray-500">Contoh: 107.619123</p>
                    @error('longitude')
                        <p class="mt-1 text-sm text-error-700">{{ $message }}</p>
                    @enderror
                </div>
            </div>
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
