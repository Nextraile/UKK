<x-base-layout 
    title="Buat Room Type - Admin - SewaKost"
    variant="admin-sidebar"
    page-title="Buat Room Type">
    
<div class="max-w-3xl">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-gray-900">Buat Room Type</h2>
        <p class="mt-1 text-sm text-gray-600">Kost: {{ $kost->name }}</p>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.room-types.store', $kost) }}" enctype="multipart/form-data" class="space-y-8">
            @csrf
            
            <!-- Section 1: Basic Info -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Dasar</h3>
                <div class="space-y-6">
                    <!-- Name -->
                    <div>
                        <x-input-label for="name" value="Nama Tipe Kamar" required />
                        <x-text-input 
                            id="name" 
                            name="name" 
                            type="text" 
                            class="mt-1" 
                            :value="old('name')" 
                            required 
                            autofocus 
                        />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        <p class="mt-1 text-xs text-gray-500">Contoh: Kamar Standard, Kamar Deluxe, Kamar VIP</p>
                    </div>

                    <!-- Description -->
                    <div>
                        <x-input-label for="description" value="Deskripsi" />
                        <textarea 
                            id="description" 
                            name="description" 
                            rows="3" 
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                        >{{ old('description') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        <p class="mt-1 text-xs text-gray-500">Jelaskan keunggulan dan karakteristik tipe kamar ini</p>
                    </div>

                    <!-- Room Size -->
                    <div>
                        <x-input-label for="room_size" value="Ukuran Kamar" required />
                        <x-text-input 
                            id="room_size" 
                            name="room_size" 
                            type="text" 
                            class="mt-1" 
                            :value="old('room_size')" 
                            required 
                            placeholder="Contoh: 3x4 m"
                        />
                        <x-input-error class="mt-2" :messages="$errors->get('room_size')" />
                        <p class="mt-1 text-xs text-gray-500">Format: panjang x lebar (dalam meter)</p>
                    </div>

                    <!-- Max Occupants -->
                    <div>
                        <x-input-label for="max_occupants" value="Kapasitas Maksimal" required />
                        <x-text-input 
                            id="max_occupants" 
                            name="max_occupants" 
                            type="number" 
                            min="1" 
                            max="255" 
                            class="mt-1" 
                            :value="old('max_occupants', 1)" 
                            required 
                        />
                        <x-input-error class="mt-2" :messages="$errors->get('max_occupants')" />
                        <p class="mt-1 text-xs text-gray-500">Jumlah maksimal penghuni yang dapat menempati kamar ini</p>
                    </div>

                    <!-- Security Deposit -->
                    <div>
                        <x-input-label for="security_deposit" value="Deposit Keamanan" required />
                        <div class="mt-1">
                            <input 
                                type="number" 
                                name="security_deposit" 
                                id="security_deposit" 
                                min="0" 
                                step="1000"
                                value="{{ old('security_deposit', '') }}"
                                required
                                class="block w-full rounded-lg border-gray-300 pr-4 focus:border-primary-500 focus:ring-primary-500"
                                placeholder="Masukkan nominal deposit"
                            />
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('security_deposit')" />
                        <p class="mt-1 text-xs text-gray-500">Jumlah deposit yang harus dibayar penyewa (biasanya setara 1 bulan sewa)</p>
                    </div>
                </div>
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-200"></div>

            <!-- Section 2: Images Upload -->
            <div x-data="{ images: @js(old('images', [])).length > 0 ? Array(@js(old('images', [])).length).fill(null) : [] }">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Gambar Room Type</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Upload 1-10 gambar untuk tipe kamar ini. Gambar pertama akan menjadi thumbnail.
                </p>
                
                <div class="space-y-3 mb-3">
                    <template x-for="(image, index) in images" :key="index">
                        <div class="flex items-center gap-3">
                            <div class="flex-1">
                                <input 
                                    type="file" 
                                    :name="'images[' + index + ']'" 
                                    accept="image/jpeg,image/jpg,image/png,image/webp"
                                    class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200"
                                >
                            </div>
                            <button 
                                type="button" 
                                @click="images.splice(index, 1)"
                                class="px-3 py-2 text-sm font-medium text-error-600 hover:text-error-700 hover:bg-error-50 rounded-lg transition-colors"
                            >
                                Hapus
                            </button>
                        </div>
                    </template>
                    
                    <button 
                        type="button" 
                        @click="images.push(null)"
                        :disabled="images.length >= 10"
                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-primary-600 hover:text-primary-700 hover:bg-primary-50 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-transparent"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Gambar
                    </button>
                    
                    <p class="text-xs text-gray-500">
                        Format: JPEG, JPG, PNG, WebP. Maksimal 5MB per file. Total maksimal 10 gambar.
                    </p>
                </div>
                
                <x-input-error class="mt-2" :messages="$errors->get('images')" />
                <x-input-error class="mt-2" :messages="$errors->get('images.*')" />
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-200"></div>

            <!-- Section 3: Facilities (Alpine.js dynamic list) -->
            <div x-data="{ facilities: @js(old('facilities', [])) }">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Fasilitas</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Tambahkan fasilitas khusus untuk tipe kamar ini (AC, TV, Lemari, Meja Belajar, dll.)
                </p>
                
                <div class="space-y-2 mb-3">
                    <template x-for="(facility, index) in facilities" :key="index">
                        <div class="flex gap-2">
                            <input 
                                type="text" 
                                :name="'facilities[' + index + ']'" 
                                x-model="facilities[index]"
                                placeholder="Contoh: AC, Kasur Single, Lemari Pakaian"
                                class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            >
                            <button 
                                type="button" 
                                @click="facilities.splice(index, 1)"
                                class="px-3 py-2 text-sm font-medium text-error-600 hover:text-error-700 hover:bg-error-50 rounded-lg transition-colors"
                            >
                                Hapus
                            </button>
                        </div>
                    </template>
                    
                    <button 
                        type="button" 
                        @click="facilities.push('')"
                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-primary-600 hover:text-primary-700 hover:bg-primary-50 rounded-lg transition-colors"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Fasilitas
                    </button>
                </div>
                
                <x-input-error class="mt-2" :messages="$errors->get('facilities')" />
                <x-input-error class="mt-2" :messages="$errors->get('facilities.*')" />
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-200"></div>

            <!-- Section 4: Rules (Alpine.js dynamic list) -->
            <div x-data="{ rules: @js(old('rules', [])) }">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Peraturan</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Tambahkan peraturan khusus untuk tipe kamar ini (jika ada aturan berbeda dari peraturan kost umum)
                </p>
                
                <div class="space-y-2 mb-3">
                    <template x-for="(rule, index) in rules" :key="index">
                        <div class="flex gap-2">
                            <input 
                                type="text" 
                                :name="'rules[' + index + ']'" 
                                x-model="rules[index]"
                                placeholder="Contoh: Maksimal jam malam 22:00, Dilarang bawa tamu"
                                class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            >
                            <button 
                                type="button" 
                                @click="rules.splice(index, 1)"
                                class="px-3 py-2 text-sm font-medium text-error-600 hover:text-error-700 hover:bg-error-50 rounded-lg transition-colors"
                            >
                                Hapus
                            </button>
                        </div>
                    </template>
                    
                    <button 
                        type="button" 
                        @click="rules.push('')"
                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-primary-600 hover:text-primary-700 hover:bg-primary-50 rounded-lg transition-colors"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Peraturan
                    </button>
                </div>
                
                <x-input-error class="mt-2" :messages="$errors->get('rules')" />
                <x-input-error class="mt-2" :messages="$errors->get('rules.*')" />
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end gap-3 pt-6 border-t">
                <a href="{{ route('admin.room-types.index', $kost) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700">
                    Buat Room Type
                </button>
            </div>
        </form>
    </div>
</div>
</x-base-layout>
