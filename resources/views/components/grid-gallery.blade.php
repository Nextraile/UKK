@props([
    'images',
    'galleryId',
])

@if($images && $images->count() > 0)
    <div x-data class="grid-gallery">
        @php
            $count = $images->count();
        @endphp

        @if($count === 1)
            <div class="grid grid-cols-1 gap-1">
                <div class="aspect-[4/3] overflow-hidden rounded-lg cursor-pointer hover:opacity-90 transition"
                     @click="$dispatch('open-lightbox-{{ $galleryId }}', { index: 0 })">
                    <img src="{{ $images->first()->image_url }}" 
                         class="w-full h-full object-cover" 
                         alt="Image 1" />
                </div>
            </div>
        @elseif($count === 2)
            <div class="grid grid-cols-2 gap-1">
                @foreach($images as $index => $image)
                    <div class="aspect-square overflow-hidden rounded-lg cursor-pointer hover:opacity-90 transition"
                         @click="$dispatch('open-lightbox-{{ $galleryId }}', { index: {{ $index }} })">
                        <img src="{{ $image->image_url }}" 
                             class="w-full h-full object-cover" 
                             alt="Image {{ $index + 1 }}" />
                    </div>
                @endforeach
            </div>
        @elseif($count === 3)
            <div class="grid grid-cols-5 gap-1">
                @foreach($images as $index => $image)
                    <div class="{{ $index === 0 ? 'col-span-3 row-span-2' : 'col-span-2' }} aspect-square overflow-hidden rounded-lg cursor-pointer hover:opacity-90 transition"
                         @click="$dispatch('open-lightbox-{{ $galleryId }}', { index: {{ $index }} })">
                        <img src="{{ $image->image_url }}" 
                             class="w-full h-full object-cover" 
                             alt="Image {{ $index + 1 }}" />
                    </div>
                @endforeach
            </div>
        @elseif($count === 4)
            <div class="grid grid-cols-2 gap-1">
                @foreach($images as $index => $image)
                    <div class="aspect-square overflow-hidden rounded-lg cursor-pointer hover:opacity-90 transition"
                         @click="$dispatch('open-lightbox-{{ $galleryId }}', { index: {{ $index }} })">
                        <img src="{{ $image->image_url }}" 
                             class="w-full h-full object-cover" 
                             alt="Image {{ $index + 1 }}" />
                    </div>
                @endforeach
            </div>
        @else
            <div class="grid grid-cols-2 gap-1">
                @foreach($images->take(4) as $index => $image)
                    <div class="aspect-square overflow-hidden rounded-lg cursor-pointer relative hover:opacity-90 transition {{ $index === 3 && $images->count() > 4 ? 'group' : '' }}"
                         @click="$dispatch('open-lightbox-{{ $galleryId }}', { index: {{ $index }} })">
                        <img src="{{ $image->image_url }}" 
                             class="w-full h-full object-cover" 
                             alt="Image {{ $index + 1 }}" />
                        
                        @if($index === 3 && $images->count() > 4)
                            <div class="absolute inset-0 bg-black/60 flex items-center justify-center text-white text-2xl font-bold group-hover:bg-black/50 transition">
                                +{{ $images->count() - 4 }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <x-gallery-lightbox-modal 
        :images="$images->map(fn($img, $idx) => ['url' => $img->image_url, 'alt' => 'Image ' . ($idx + 1)])->values()->toArray()" 
        :lightbox-id="$galleryId" 
    />
@endif
