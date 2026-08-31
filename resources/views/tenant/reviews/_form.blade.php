{{-- Shared review form partial --}}
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" x-data="{
    kostRating: {{ old('kost_rating', $review->kost_rating ?? 0) }},
    roomRating: {{ old('room_rating', $review->room_rating ?? 0) }},
    previews: [],
    existingImages: @js(isset($review) ? $review->review_images ?? [] : [])
}">
    @csrf
    @if($method ?? null)
        @method($method)
    @endif

    <div class="space-y-6">
        {{-- Rental Info Summary --}}
        <div class="rounded-lg bg-gray-50 dark:bg-gray-700 p-4">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Detail Rental</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                <strong>Kost:</strong> {{ $rental->room->roomType->kost->name }}
            </p>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                <strong>Kamar:</strong> {{ $rental->room->roomType->name }} - Kamar {{ $rental->room->code }}
            </p>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                <strong>Periode:</strong> {{ $rental->start_date->format('d M Y') }} - {{ $rental->end_date->format('d M Y') }}
            </p>
        </div>

        {{-- Kost Rating Section --}}
        <div class="space-y-2">
            <x-input-label for="kost_rating" value="Rating Kost" />
            <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">
                Bagaimana penilaian Anda terhadap kost secara keseluruhan (lokasi, fasilitas umum, kebersihan)?
            </p>
            
            <div class="flex gap-1">
                <template x-for="i in [1,2,3,4,5]" :key="i">
                    <button type="button" 
                            @click="kostRating = i"
                            class="text-4xl transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 rounded"
                            :class="kostRating >= i ? 'text-yellow-500' : 'text-gray-300 dark:text-gray-600'"
                            :aria-label="'Rating ' + i">
                        ★
                    </button>
                </template>
            </div>
            <input type="hidden" name="kost_rating" :value="kostRating > 0 ? kostRating : ''">
            <x-input-error :messages="$errors->get('kost_rating')" class="mt-2" />
        </div>

        {{-- Kost Comment Section --}}
        <div class="space-y-2">
            <x-input-label for="kost_comment" value="Ulasan Kost (Opsional)" />
            <textarea 
                id="kost_comment" 
                name="kost_comment"
                rows="4"
                maxlength="2000"
                placeholder="Ceritakan pengalaman Anda menginap di kost ini..."
                class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 placeholder:text-gray-400 rounded-md shadow-xs focus:border-primary-500 focus:ring-2 focus:ring-primary-500 transition-colors"
            >{{ old('kost_comment', $review->kost_comment ?? '') }}</textarea>
            <x-input-error :messages="$errors->get('kost_comment')" class="mt-2" />
        </div>

        {{-- Divider --}}
        <hr class="border-gray-200 dark:border-gray-700">

        {{-- Room Rating Section --}}
        <div class="space-y-2">
            <x-input-label for="room_rating" value="Rating Kamar" />
            <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">
                Bagaimana penilaian Anda terhadap kamar yang Anda tempati?
            </p>
            
            <div class="flex gap-1">
                <template x-for="i in [1,2,3,4,5]" :key="i">
                    <button type="button" 
                            @click="roomRating = i"
                            class="text-4xl transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 rounded"
                            :class="roomRating >= i ? 'text-yellow-500' : 'text-gray-300 dark:text-gray-600'"
                            :aria-label="'Rating ' + i">
                        ★
                    </button>
                </template>
            </div>
            <input type="hidden" name="room_rating" :value="roomRating > 0 ? roomRating : ''">
            <x-input-error :messages="$errors->get('room_rating')" class="mt-2" />
        </div>

        {{-- Room Comment Section --}}
        <div class="space-y-2">
            <x-input-label for="room_comment" value="Ulasan Kamar (Opsional)" />
            <textarea 
                id="room_comment" 
                name="room_comment"
                rows="4"
                maxlength="2000"
                placeholder="Ceritakan kondisi kamar, kenyamanan, kebersihan, dll..."
                class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 placeholder:text-gray-400 rounded-md shadow-xs focus:border-primary-500 focus:ring-2 focus:ring-primary-500 transition-colors"
            >{{ old('room_comment', $review->room_comment ?? '') }}</textarea>
            <x-input-error :messages="$errors->get('room_comment')" class="mt-2" />
        </div>

        {{-- Divider --}}
        <hr class="border-gray-200 dark:border-gray-700">

        {{-- Image Upload Section --}}
        <div class="space-y-2">
            <x-input-label for="images" value="Upload Foto (Opsional)" />
            <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">
                Maksimal 5 foto, format JPEG/PNG, ukuran maksimal 2MB per foto
            </p>
            
            <input 
                type="file" 
                id="images"
                name="images[]" 
                multiple 
                accept="image/jpeg,image/png,image/jpg"
                @change="previews = Array.from($event.target.files).slice(0, 5).map(f => URL.createObjectURL(f))"
                class="block w-full text-sm text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
            <x-input-error :messages="$errors->get('images')" class="mt-2" />
            <x-input-error :messages="$errors->get('images.*')" class="mt-2" />
            
            {{-- Image Previews --}}
            <div x-show="previews.length > 0 || existingImages.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2 mt-3">
                {{-- Existing images (edit mode) --}}
                <template x-for="(img, index) in existingImages" :key="'existing-' + index">
                    <div class="relative">
                        <img :src="img" class="w-full h-24 object-cover rounded-lg border border-gray-200 dark:border-gray-700">
                        <span class="absolute top-1 right-1 bg-black bg-opacity-50 text-white text-xs px-1.5 py-0.5 rounded">Existing</span>
                    </div>
                </template>
                
                {{-- New image previews --}}
                <template x-for="(url, index) in previews" :key="index">
                    <img :src="url" class="w-full h-24 object-cover rounded-lg border border-gray-200 dark:border-gray-700">
                </template>
            </div>
        </div>

        {{-- Form Actions --}}
        <div class="flex items-center justify-end gap-3 pt-4">
            <x-secondary-button type="button" onclick="window.history.back()">
                Batal
            </x-secondary-button>
            <x-primary-button type="submit">
                {{ isset($review) ? 'Update Review' : 'Kirim Review' }}
            </x-primary-button>
        </div>
    </div>
</form>
