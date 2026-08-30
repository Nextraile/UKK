<x-base-layout 
    title="Kost Submission Detail - Super Admin - SewaKost"
    variant="admin-sidebar"
    page-title="Kost Submission Detail">
    
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
                        <dd class="mt-1 text-sm text-gray-900">{{ $submission->owner->name }}</dd>
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
                                {{ $submission->address->street_address ?? '' }}
                                {{ $submission->address->district ? ', ' . $submission->address->district : '' }}
                                {{ $submission->address->city ? ', ' . $submission->address->city : '' }}
                                {{ $submission->address->province ? ', ' . $submission->address->province : '' }}
                                {{ $submission->address->postal_code ?? '' }}
                            @else
                                <span class="text-gray-400">Address not provided</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Room Types --}}
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-3">Room Types</h3>
                <ul class="divide-y divide-gray-200 border border-gray-200 rounded-lg">
                    @forelse ($submission->roomTypes as $roomType)
                        <li class="px-4 py-3">
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="text-sm font-medium text-gray-900">{{ $roomType->name }}</span>
                                    <span class="ml-2 text-xs text-gray-500">{{ $roomType->rooms_count ?? 0 }} rooms</span>
                                </div>
                                <span class="text-sm text-gray-700">Rp {{ number_format($roomType->price_per_month, 0, ',', '.') }}/bulan</span>
                            </div>
                        </li>
                    @empty
                        <li class="px-4 py-3 text-sm text-gray-500">No room types defined</li>
                    @endforelse
                </ul>
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
        <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end space-x-3" x-data="{ showRejectModal: false }">
            <form method="POST" action="{{ route('super-admin.kost-submissions.approve', $submission) }}" 
                  onsubmit="return confirm('Are you sure you want to approve this kost?')">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-success-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-success-700 focus:outline-none focus:ring-2 focus:ring-success-500 focus:ring-offset-2 transition">
                    Approve
                </button>
            </form>

            <button type="button" 
                    @click="showRejectModal = true"
                    class="inline-flex items-center px-4 py-2 bg-error-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-error-700 focus:outline-none focus:ring-2 focus:ring-error-500 focus:ring-offset-2 transition">
                Reject
            </button>

            {{-- Reject Modal --}}
            <div x-show="showRejectModal" 
                 x-cloak
                 @click.self="showRejectModal = false"
                 class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black bg-opacity-50" 
                 aria-labelledby="modal-title" 
                 role="dialog" 
                 aria-modal="true"
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
                                <h3 class="text-lg font-semibold text-gray-900" id="modal-title">
                                    Reject Kost Submission
                                </h3>
                                <button type="button" @click="showRejectModal = false" class="text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 rounded">
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
                            <button type="button" 
                                    @click="showRejectModal = false"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                Cancel
                            </button>
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
