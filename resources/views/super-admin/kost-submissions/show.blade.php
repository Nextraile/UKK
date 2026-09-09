<x-base-layout 
    title="Kost Submission Detail - Super Admin - SewaKost"
    variant="admin-sidebar"
    page-title="Kost Submission Detail">

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
      crossorigin=""/>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
@endpush
    
<div class="space-y-6">
    {{-- Breadcrumb --}}
    <nav class="text-sm" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2 text-gray-500">
            <li><a href="{{ route('super-admin.kost-submissions.index') }}" class="hover:text-gray-700">Kost Submissions</a></li>
            <li><span aria-hidden="true">/</span></li>
            <li class="text-gray-900 font-medium" aria-current="page">{{ $submission->name }}</li>
        </ol>
    </nav>

    <div class="bg-white rounded-lg border border-gray-200 p-6">
        {{-- Header --}}
        <div class="flex justify-between items-start mb-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">{{ $submission->name }}</h2>
                <p class="mt-1 text-sm text-gray-600">Submitted {{ $submission->updated_at->diffForHumans() }}</p>
            </div>
            <x-status-badge status="pending_review" type="kost" />
        </div>

        {{-- Kost Details --}}
        <div class="space-y-6">
            {{-- Basic Info --}}
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-3">Basic Information</h3>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-3 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Owner</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $submission->owner->first_name }} {{ $submission->owner->last_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Category</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $submission->categories->pluck('name')->join(', ') }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Description</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $submission->description ?? '-' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Address</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($submission->address)
                                {{ $submission->address->full_address ?? '' }}
                                {{ $submission->address->district ? ', ' . $submission->address->district : '' }}
                                {{ $submission->address->city ? ', ' . $submission->address->city : '' }}
                                {{ $submission->address->province ? ', ' . $submission->address->province : '' }}
                                {{ $submission->address->postal_code ?? '' }}
                            @else
                                <span class="text-gray-400">Address not provided</span>
                            @endif
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 mb-2">Location Map</dt>
                        <dd class="mt-1">
                            @if($submission->latitude && $submission->longitude)
                                <div id="map" 
                                     class="h-64 rounded-lg border border-gray-300"
                                     x-data="{
                                         map: null,
                                         initMap() {
                                             this.map = L.map('map').setView([{{ $submission->latitude }}, {{ $submission->longitude }}], 15);
                                             L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                                 attribution: '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors'
                                             }).addTo(this.map);
                                             L.marker([{{ $submission->latitude }}, {{ $submission->longitude }}])
                                                 .addTo(this.map)
                                                 .bindPopup('{{ addslashes($submission->name) }}');
                                         }
                                     }"
                                     x-init="initMap()"
                                     x-on:destroy="if (map) { map.remove(); map = null; }">
                                </div>
                            @else
                                <div class="flex items-center gap-2 text-sm text-gray-600">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span>Coordinates not available</span>
                                    @if($submission->address && $submission->address->full_address)
                                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($submission->address->full_address . ', ' . $submission->address->city) }}" 
                                           target="_blank"
                                           class="text-primary-600 hover:text-primary-700 underline">
                                            View on Google Maps
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Kost Images --}}
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-3">
                    Kost Images ({{ $submission->kostImages->count() }})
                </h3>
                @if($submission->kostImages->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" x-data="{ lightbox: false, currentImage: '' }">
                        @foreach($submission->kostImages as $image)
                            <div class="relative aspect-square rounded-lg overflow-hidden bg-gray-100 hover:opacity-90 transition cursor-pointer"
                                 @click="lightbox = true; currentImage = '/storage/{{ $image->image_path }}'">
                                <img src="/storage/{{ $image->image_path }}" 
                                     alt="Kost image {{ $loop->iteration }}"
                                     class="w-full h-full object-cover">
                            </div>
                        @endforeach
                        
                        {{-- Lightbox Modal --}}
                        <div x-show="lightbox" 
                             x-cloak
                             @click="lightbox = false"
                             class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-90 p-4">
                            <img :src="currentImage" 
                                 class="max-w-full max-h-full rounded-lg"
                                 @click.stop>
                            <button @click="lightbox = false" 
                                    class="absolute top-4 right-4 text-white hover:text-gray-300"
                                    aria-label="Close">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-12 px-4 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50">
                        <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-sm text-gray-500">No kost images uploaded</p>
                    </div>
                @endif
            </div>

            {{-- Payment Configuration --}}
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-3">Payment Configuration</h3>
                
                @if($submission->qris_image_path || $submission->bank_name)
                    <div class="space-y-4">
                        {{-- QRIS --}}
                        @if($submission->qris_image_path)
                            <div>
                                <h4 class="text-sm font-semibold text-gray-700 mb-2">QRIS</h4>
                                <img src="/storage/{{ $submission->qris_image_path }}" 
                                     alt="QRIS Code"
                                     class="max-w-xs border-2 border-gray-300 rounded-lg">
                            </div>
                        @endif
                        
                        {{-- Bank Transfer (Legacy single bank account from kosts table) --}}
                        @if($submission->bank_name)
                            <div>
                                <h4 class="text-sm font-semibold text-gray-700 mb-2">Bank Transfer</h4>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Bank</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Account Number</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Account Holder</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr>
                                                <td class="px-4 py-3 text-sm text-gray-900">{{ $submission->bank_name }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-900 font-mono">{{ $submission->account_number }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-900">{{ $submission->account_holder_name }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-8 px-4 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50">
                        <svg class="w-10 h-10 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        <p class="text-sm text-gray-500">No payment methods configured</p>
                    </div>
                @endif
            </div>

            {{-- Document Requirements --}}
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-3">
                    Document Requirements ({{ $submission->documentRequirements->count() }})
                </h3>
                
                @if($submission->documentRequirements->isNotEmpty())
                    <ul class="space-y-3">
                        @foreach($submission->documentRequirements as $docReq)
                            <li class="flex items-start gap-3 p-3 border border-gray-200 rounded-lg bg-gray-50">
                                <div class="flex-shrink-0 mt-0.5">
                                    @if($docReq->is_required)
                                        <svg class="w-5 h-5 text-error-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-sm font-semibold text-gray-900">
                                            {{ Str::title(str_replace('_', ' ', $docReq->document_type)) }}
                                        </span>
                                        @if($docReq->is_required)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-error-100 text-error-800">
                                                Wajib
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-200 text-gray-700">
                                                Optional
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-600">{{ $docReq->reason }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="flex flex-col items-center justify-center py-8 px-4 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50">
                        <svg class="w-10 h-10 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-sm text-gray-500">No document requirements configured</p>
                    </div>
                @endif
            </div>

            {{-- Room Types --}}
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-3">Room Types</h3>
                @if($submission->roomTypes->isNotEmpty())
                    <ul class="space-y-4">
                        @foreach($submission->roomTypes as $roomType)
                            <li class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                <div class="flex gap-4">
                                    {{-- Room Type Image --}}
                                    @if($roomType->roomTypeImages->first())
                                        <div class="flex-shrink-0">
                                            <img src="/storage/{{ $roomType->roomTypeImages->first()->image_path }}" 
                                                 alt="{{ $roomType->name }}"
                                                 class="w-24 h-24 object-cover rounded-lg">
                                        </div>
                                    @endif
                                    
                                    {{-- Room Type Details --}}
                                    <div class="flex-1">
                                        <h4 class="text-base font-semibold text-gray-900 mb-1">{{ $roomType->name }}</h4>
                                        <p class="text-sm text-gray-600 mb-3">
                                            @if($roomType->room_size)
                                                {{ $roomType->room_size }}
                                            @endif
                                            @if($roomType->room_size && $roomType->max_occupants)
                                                •
                                            @endif
                                            @if($roomType->max_occupants)
                                                Max {{ $roomType->max_occupants }} orang
                                            @endif
                                            @if(($roomType->room_size || $roomType->max_occupants) && $roomType->rooms_count)
                                                •
                                            @endif
                                            @if($roomType->rooms_count)
                                                {{ $roomType->rooms_count }} kamar
                                            @endif
                                        </p>
                                        
                                        {{-- Price Schemes --}}
                                        @if($roomType->priceSchemes->isNotEmpty())
                                            <div>
                                                <p class="text-xs font-medium text-gray-500 uppercase mb-2">Price Schemes:</p>
                                                <div class="space-y-1">
                                                    @foreach($roomType->priceSchemes as $scheme)
                                                        <div class="flex items-baseline gap-2 text-sm">
                                                            <span class="font-medium text-gray-700">
                                                                {{ $scheme->name }}:
                                                            </span>
                                                            <span class="text-gray-900 font-semibold">
                                                                Rp {{ number_format($scheme->price, 0, ',', '.') }}
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            <p class="text-sm text-gray-500">No price schemes configured</p>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="flex flex-col items-center justify-center py-8 px-4 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50">
                        <svg class="w-10 h-10 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <p class="text-sm text-gray-500">No room types defined</p>
                    </div>
                @endif
            </div>

            {{-- Facilities --}}
            @if (!empty($submission->facilities))
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-3">Facilities</h3>
                    <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach ($submission->facilities as $facility)
                            <li class="flex items-center text-sm text-gray-700">
                                <svg class="h-4 w-4 text-success-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                {{ $facility }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Rules --}}
            @if (!empty($submission->rules))
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-3">Rules</h3>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($submission->rules as $rule)
                            <li class="text-sm text-gray-700">{{ $rule }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        {{-- Action Buttons --}}
        <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end space-x-3" x-data="{ showApproveModal: false, showRejectModal: false }">
            <button type="button" 
                    @click="showApproveModal = true"
                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition">
                Approve
            </button>

            <button type="button" 
                    @click="showRejectModal = true"
                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition">
                Reject
            </button>

            {{-- Approve Confirmation Modal --}}
            <div x-show="showApproveModal" 
                 x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto" 
                 aria-labelledby="approve-modal-title" 
                 role="dialog" 
                 aria-modal="true"
                 @keydown.escape.window="showApproveModal = false">
                
                {{-- Backdrop --}}
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                     @click="showApproveModal = false"></div>
                
                {{-- Modal Content --}}
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="relative bg-white rounded-lg max-w-md w-full p-6"
                         @click.stop>
                        {{-- Icon --}}
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        
                        {{-- Title --}}
                        <h3 class="text-lg font-semibold text-gray-900 text-center mb-2" id="approve-modal-title">
                            Approve Submission?
                        </h3>
                        
                        {{-- Message --}}
                        <p class="text-sm text-gray-600 text-center mb-6">
                            Kost <strong>{{ $submission->name }}</strong> akan disetujui dan Admin dapat mempublikasikannya.
                        </p>
                        
                        {{-- Actions --}}
                        <div class="flex gap-3">
                            <button @click="showApproveModal = false" 
                                    type="button" 
                                    class="flex-1 px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                Batal
                            </button>
                            <form method="POST" action="{{ route('super-admin.kost-submissions.approve', $submission) }}" class="flex-1">
                                @csrf
                                <button type="submit" 
                                        class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                    Approve Kost
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Reject Modal --}}
            <div x-show="showRejectModal" 
                 x-cloak
                 @click.self="showRejectModal = false"
                 class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black bg-opacity-50" 
                 aria-labelledby="reject-modal-title" 
                 role="dialog" 
                 aria-modal="true"
                 @keydown.escape.window="showRejectModal = false"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                <div class="bg-white rounded-lg shadow-xl max-w-lg w-full"
                     @click.stop
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-90"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-90">
                    <form method="POST" action="{{ route('super-admin.kost-submissions.reject', $submission) }}" x-data="{ reason: '', charCount: 0 }">
                        @csrf
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900" id="reject-modal-title">
                                    Reject Kost Submission
                                </h3>
                                <button type="button" 
                                        @click="showRejectModal = false" 
                                        class="text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 rounded"
                                        aria-label="Close">
                                    <span class="sr-only">Close</span>
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="px-6 py-4">
                            <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">
                                Rejection Reason <span class="text-error-600">*</span>
                            </label>
                            <textarea 
                                id="rejection_reason" 
                                name="rejection_reason" 
                                rows="4" 
                                required
                                minlength="10"
                                maxlength="1000"
                                x-model="reason"
                                @input="charCount = reason.length"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-error-500 focus:ring-error-500 sm:text-sm"
                                placeholder="Explain why this kost is being rejected (minimum 10 characters)"></textarea>
                            <p class="mt-2 text-sm text-gray-500">
                                <span x-text="charCount"></span> / 1000 characters
                                <span x-show="charCount < 10" class="text-error-600">(minimum 10 required)</span>
                            </p>
                            @error('rejection_reason')
                                <p class="mt-2 text-sm text-error-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3 rounded-b-lg">
                            <button type="submit" 
                                    x-bind:disabled="charCount < 10"
                                    x-bind:class="charCount < 10 ? 'opacity-50 cursor-not-allowed' : ''"
                                    class="px-4 py-2 text-sm font-medium text-white bg-error-600 border border-transparent rounded-lg hover:bg-error-700 focus:outline-none focus:ring-2 focus:ring-error-500 focus:ring-offset-2">
                                Submit Rejection
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</x-base-layout>
