<x-guest-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumb -->
        <nav class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            <a href="{{ route('marketplace.index') }}" class="hover:text-primary-600">Marketplace</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900 dark:text-gray-100">{{ $kost->name }}</span>
        </nav>
        
        <div class="lg:grid lg:grid-cols-3 lg:gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Images (placeholder - will add gallery in TASK-041 part 2) -->
                <div class="mb-6">
                    @php $firstImage = $kost->kostImages->first(); @endphp
                    @if ($firstImage)
                        <img 
                            src="{{ Storage::url($firstImage->image_path) }}" 
                            alt="{{ $kost->name }}"
                            class="w-full h-96 object-cover rounded-lg"
                        >
                    @else
                        <div class="w-full h-96 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                            <span class="text-gray-400">No image</span>
                        </div>
                    @endif
                </div>
                
                <!-- Kost Info -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-4">{{ $kost->name }}</h1>
                    
                    <!-- Location -->
                    <div class="flex items-center text-gray-600 dark:text-gray-400 mb-4">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        {{ $kost->address->full_address ?? 'Alamat tidak tersedia' }}
                    </div>
                    
                    <!-- Categories -->
                    @if($kost->categories->isNotEmpty())
                        <div class="flex flex-wrap gap-2 mb-4">
                            @foreach($kost->categories as $category)
                                <span class="px-3 py-1 bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 text-sm rounded-full">
                                    {{ $category->name }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                    
                    <!-- Description -->
                    @if($kost->description)
                        <div class="prose dark:prose-invert max-w-none mb-6">
                            <h3 class="text-lg font-semibold mb-2">Deskripsi</h3>
                            <p class="text-gray-600 dark:text-gray-400">{{ $kost->description }}</p>
                        </div>
                    @endif
                    
                    <!-- Facilities -->
                    @if($kost->facilities)
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Fasilitas</h3>
                            <ul class="grid grid-cols-2 gap-2">
                                    @foreach($kost->facilities as $facility)
                                        <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                            <svg class="w-4 h-4 mr-2 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            {{ $facility }}
                                        </li>
                                    @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <!-- Rules -->
                    @if($kost->rules)
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Peraturan</h3>
                            <ul class="space-y-1">
                                @foreach($kost->rules as $rule)
                                    <li class="flex items-start text-sm text-gray-600 dark:text-gray-400">
                                        <svg class="w-4 h-4 mr-2 mt-0.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                        {{ $rule }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <!-- Document Requirements -->
                    @if($kost->documentRequirements->isNotEmpty())
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Dokumen yang Dibutuhkan</h3>
                            <ul class="space-y-2">
                                @foreach($kost->documentRequirements as $doc)
                                    <li class="flex items-start text-sm">
                                        <svg class="w-4 h-4 mr-2 mt-0.5 {{ $doc->is_required ? 'text-error-500' : 'text-gray-400' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <div>
                                            <span class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ ucfirst(str_replace('_', ' ', $doc->document_type)) }}
                                            </span>
                                            @if($doc->is_required)
                                                <span class="text-error-600 text-xs ml-1">(Wajib)</span>
                                            @endif
                                            @if($doc->reason)
                                                <p class="text-gray-600 dark:text-gray-400 text-xs mt-1">{{ $doc->reason }}</p>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
                
                <!-- Room Types Section -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">Tipe Kamar</h2>
                    
                    @if($kost->roomTypes->isNotEmpty())
                        <div class="space-y-4">
                            @foreach($kost->roomTypes as $index => $roomType)
                                <div 
                                    x-data="{ open: {{ $index === 0 ? 'true' : 'false' }} }"
                                    class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden"
                                >
                                    <!-- Accordion Header -->
                                    <button 
                                        @click="open = !open"
                                        class="w-full px-4 py-4 flex items-center justify-between bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                                    >
                                        <div class="flex-1 text-left">
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                {{ $roomType->name }}
                                            </h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                @if($roomType->size)
                                                    {{ $roomType->size }} m² •
                                                @endif
                                                Max {{ $roomType->max_occupants }} orang
                                                •
                                                <span class="font-medium {{ $roomType->available_count > 0 ? 'text-success-600' : 'text-error-600' }}">
                                                    {{ $roomType->available_count }} kamar tersedia
                                                </span>
                                            </p>
                                        </div>
                                        <svg 
                                            x-bind:class="open ? 'rotate-180' : ''"
                                            class="w-5 h-5 text-gray-500 transition-transform"
                                            fill="none" 
                                            stroke="currentColor" 
                                            viewBox="0 0 24 24"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    
                                    <!-- Accordion Content -->
                                    <div 
                                        x-show="open"
                                        x-collapse
                                        class="px-4 py-4 bg-white dark:bg-gray-800"
                                    >
                                        <!-- Price Schemes -->
                                        @if($roomType->priceSchemes->isNotEmpty())
                                            <div class="mb-4">
                                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Pilihan Harga</h4>
                                                <div class="space-y-2">
                                                    @foreach($roomType->priceSchemes as $scheme)
                                                        <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-600 rounded-lg">
                                                            <div>
                                                                <p class="font-medium text-gray-900 dark:text-gray-100">
                                                                    {{ $scheme->duration_value }} 
                                                                    {{ $scheme->duration_unit === 'month' ? 'Bulan' : ($scheme->duration_unit === 'week' ? 'Minggu' : 'Hari') }}
                                                                </p>
                                                                @if($scheme->security_deposit > 0)
                                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                                        + Deposit: Rp {{ number_format($scheme->security_deposit, 0, ',', '.') }}
                                                                    </p>
                                                                @endif
                                                            </div>
                                            <div class="text-right">
                                                                <p class="text-xl font-bold text-primary-600">
                                                                    Rp {{ number_format($scheme->price, 0, ',', '.') }}
                                                                </p>
                                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                                    per {{ $scheme->duration_unit === 'month' ? 'bulan' : ($scheme->duration_unit === 'week' ? 'minggu' : 'hari') }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Tidak ada skema harga aktif</p>
                                        @endif
                                        
                                        <!-- Thumbnail (if exists) -->
                                        @php $thumbnail = $roomType->roomTypeImages->first(); @endphp
                                        @if($thumbnail)
                                            <div class="mb-4">
                                                <img 
                                                    src="{{ Storage::url($thumbnail->image_path) }}" 
                                                    alt="{{ $roomType->name }}"
                                                    class="w-full h-48 object-cover rounded-lg"
                                                >
                                            </div>
                                        @endif
                                        
                                        <!-- Action Button -->
                                        <a 
                                            href="{{ route('rentals.create', ['kost_id' => $kost->id, 'room_type_id' => $roomType->id]) }}"
                                            class="block w-full px-4 py-2 bg-primary-600 text-white text-center font-semibold rounded-lg hover:bg-primary-700 transition-colors {{ $roomType->available_count === 0 ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}"
                                        >
                                            {{ $roomType->available_count > 0 ? 'Pilih Kamar Ini' : 'Tidak Tersedia' }}
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400">Belum ada tipe kamar yang tersedia</p>
                    @endif
                </div>
                
                <!-- Reviews Section -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">Ulasan</h2>
                    
                    @if($reviewCount > 0)
                        <!-- Rating Summary -->
                        <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex flex-wrap gap-6">
                                @if($avgKostRating)
                                    <div class="flex items-center gap-3">
                                        <svg class="w-8 h-8 text-yellow-400 fill-current" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        <div>
                                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($avgKostRating, 1) }}</p>
                                            <p class="text-xs text-gray-600 dark:text-gray-400">Rating Kost</p>
                                        </div>
                                    </div>
                                @endif
                                
                                @if($avgRoomRating)
                                    <div class="flex items-center gap-3">
                                        <svg class="w-8 h-8 text-yellow-400 fill-current" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        <div>
                                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($avgRoomRating, 1) }}</p>
                                            <p class="text-xs text-gray-600 dark:text-gray-400">Rating Kamar</p>
                                        </div>
                                    </div>
                                @endif
                                
                                <div class="flex items-center">
                                    <div>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $reviewCount }} ulasan</p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">Dari penyewa yang telah menginap</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Review List -->
                        <div class="space-y-6">
                            @foreach($reviews as $review)
                                <article class="border-b border-gray-200 dark:border-gray-700 pb-6 last:border-0 last:pb-0">
                                    <!-- Reviewer Info -->
                                    <div class="flex items-center justify-between mb-3">
                                        <div>
                                            <p class="font-semibold text-gray-900 dark:text-gray-100">
                                                {{ $review->rental->user->first_name ?? 'Anonymous' }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $review->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Kost Rating & Comment -->
                                    @if($review->kost_rating)
                                        <div class="mb-3">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Rating Kost:</span>
                                                <div class="flex items-center gap-0.5">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <svg class="w-4 h-4 {{ $i <= $review->kost_rating ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }} fill-current" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                        </svg>
                                                    @endfor
                                                </div>
                                            </div>
                                            @if($review->kost_comment)
                                                <p class="text-gray-700 dark:text-gray-300 text-sm leading-relaxed ml-4">
                                                    {{ $review->kost_comment }}
                                                </p>
                                            @endif
                                        </div>
                                    @endif
                                    
                                    <!-- Room Rating & Comment -->
                                    @if($review->room_rating)
                                        <div class="mt-3 pl-4 border-l-2 border-gray-200 dark:border-gray-700">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Rating Kamar:</span>
                                                <div class="flex items-center gap-0.5">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <svg class="w-3 h-3 {{ $i <= $review->room_rating ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }} fill-current" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                        </svg>
                                                    @endfor
                                                </div>
                                            </div>
                                            @if($review->room_comment)
                                                <p class="text-xs text-gray-600 dark:text-gray-400">{{ $review->room_comment }}</p>
                                            @endif
                                        </div>
                                    @endif
                                    
                                    <!-- Review Images -->
                                    @if($review->review_images && count($review->review_images) > 0)
                                        <div class="mt-3 flex gap-2 overflow-x-auto">
                                            @foreach($review->review_images as $imagePath)
                                                <img src="{{ Storage::url($imagePath) }}" alt="Review image" class="w-20 h-20 object-cover rounded border border-gray-200 dark:border-gray-700">
                                            @endforeach
                                        </div>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                        
                        <!-- Pagination -->
                        @if($reviews->hasPages())
                            <div class="mt-6">
                                {{ $reviews->links() }}
                            </div>
                        @endif
                    @else
                        <!-- Empty State -->
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Belum ada ulasan</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Jadilah yang pertama memberikan ulasan untuk kost ini
                            </p>
                        </div>
                    @endif
                </div>
                
                <!-- Map Section -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">Lokasi</h2>
                    
                    @if($kost->address && $kost->address->latitude && $kost->address->longitude)
                        <!-- Leaflet Map -->
                        <div 
                            x-data="{
                                map: null,
                                init() {
                                    this.$nextTick(() => {
                                        this.map = L.map(this.$refs.mapContainer).setView([{{ $kost->address->latitude }}, {{ $kost->address->longitude }}], 15);
                                        
                                        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                            maxZoom: 19,
                                            attribution: '© OpenStreetMap contributors'
                                        }).addTo(this.map);
                                        
                                        L.marker([{{ $kost->address->latitude }}, {{ $kost->address->longitude }}])
                                            .addTo(this.map)
                                            .bindPopup('{{ $kost->name }}');
                                    });
                                    this.$cleanup(() => {
                                        if (this.map) {
                                            this.map.remove();
                                        }
                                    });
                                }
                            }"
                            class="mb-4"
                        >
                            <div 
                                x-ref="mapContainer" 
                                class="w-full h-64 md:h-96 rounded-lg border border-gray-200 dark:border-gray-700" 
                                style="z-index: 1;"
                            ></div>
                        </div>
                        
                        <!-- Address Text -->
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $kost->address->full_address }}
                        </p>
                    @else
                        <!-- Fallback: Text address only -->
                        <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <p class="text-gray-600 dark:text-gray-400 mb-2">
                                {{ $kost->address->full_address ?? 'Alamat tidak tersedia' }}
                            </p>
                            @if($kost->address && $kost->address->full_address)
                                <a 
                                    href="https://www.google.com/maps/search/?api=1&query={{ urlencode($kost->address->full_address) }}" 
                                    target="_blank" 
                                    rel="noopener noreferrer"
                                    class="text-sm text-primary-600 hover:underline"
                                >
                                    Lihat di Google Maps
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Sidebar -->
            <aside class="lg:col-span-1 mt-6 lg:mt-0">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 lg:sticky lg:top-4">
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Harga mulai dari</p>
                        <p class="text-3xl font-bold text-primary-600">Rp 1jt<span class="text-base text-gray-500">/bulan</span></p>
                    </div>
                    
                    @if($avgKostRating || $avgRoomRating)
                        <div class="flex items-center mb-4 text-sm">
                            <svg class="w-5 h-5 text-yellow-400 fill-current" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            <span class="ml-1 font-semibold text-gray-900 dark:text-gray-100">{{ number_format($avgKostRating ?? $avgRoomRating, 1) }}</span>
                            <span class="ml-1 text-gray-500">({{ $reviewCount }} ulasan)</span>
                        </div>
                    @endif
                    
                    <a 
                        href="{{ route('rentals.create', ['kost_id' => $kost->id]) }}"
                        class="block w-full px-6 py-3 bg-primary-600 text-white text-center font-semibold rounded-lg hover:bg-primary-700 transition-colors"
                    >
                        Booking Sekarang
                    </a>
                    
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Kontak</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $kost->contact_number }}</p>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</x-guest-layout>
