{{-- 
    Progress Stepper Component (DESIGN.md §3.40)
    
    Usage:
    <x-progress-stepper 
        :steps="[
            ['label' => 'Payment', 'status' => 'completed', 'timestamp' => '2026-08-30 10:15'],
            ['label' => 'Upload Documents', 'status' => 'active', 'progress' => '2/3'],
            ['label' => 'Confirmation', 'status' => 'locked', 'message' => 'Available after...'],
        ]"
        :current="2"
        variant="vertical|collapsed"
    />
    
    Props:
    - steps (array, required): Array of step objects
    - current (int, required): Current step number (1-indexed)
    - variant (string): 'vertical' (default) or 'collapsed'
--}}

@props(['steps', 'current', 'variant' => 'vertical'])

@if($variant === 'collapsed')
    {{-- Mobile Collapsed Variant --}}
    <div 
        x-data="{ expanded: false }" 
        class="lg:hidden {{ $attributes->get('class') }}"
        x-init="$watch('expanded', value => {
            if (value) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        })">
        
        {{-- Collapsed chip (sticky top) --}}
        <button 
            @click="expanded = true"
            aria-expanded="false"
            x-bind:aria-expanded="expanded.toString()"
            class="sticky top-0 z-10 w-full bg-white border-b border-gray-200 px-4 py-3 text-left shadow-sm hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary-500 transition-colors">
            <div class="flex items-center justify-between">
                <span class="font-semibold text-gray-900">
                    Step {{ $current }} of {{ count($steps) }}: {{ $steps[$current - 1]['label'] ?? 'Unknown' }}
                </span>
                <svg class="h-5 w-5 text-gray-400 transition-transform" 
                     :class="{ 'rotate-180': expanded }"
                     fill="none" 
                     stroke="currentColor" 
                     viewBox="0 0 24 24"
                     aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
            <div class="mt-2 flex items-center gap-1" aria-hidden="true">
                @foreach($steps as $index => $step)
                    <span class="h-2 flex-1 rounded-full 
                        @if($step['status'] === 'completed') bg-green-500
                        @elseif($step['status'] === 'active') bg-primary-500
                        @else bg-gray-300
                        @endif">
                    </span>
                @endforeach
            </div>
        </button>
        
        {{-- Expanded modal (full screen) --}}
        <div 
            x-show="expanded" 
            x-cloak
            x-trap.inert.noscroll="expanded"
            @keydown.escape.window="expanded = false"
            class="fixed inset-0 z-50 bg-white overflow-y-auto"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            
            <div class="sticky top-0 bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between z-10">
                <h2 class="text-lg font-semibold text-gray-900">Rental Progress</h2>
                <button 
                    @click="expanded = false" 
                    class="p-2 rounded-lg hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 transition-colors"
                    aria-label="Close progress details">
                    <svg class="h-6 w-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="p-4">
                {{-- Full vertical stepper inside modal --}}
                <ol role="list" aria-label="Rental completion progress" class="space-y-6">
                    @foreach($steps as $index => $step)
                        <li class="flex items-start gap-3 
                            @if($step['status'] === 'completed') text-green-700 dark:text-green-400
                            @elseif($step['status'] === 'active') text-primary-600 dark:text-primary-400
                            @else text-gray-400 dark:text-gray-500 opacity-60
                            @endif"
                            @if($step['status'] === 'active') aria-current="step" @endif>
                            
                            {{-- Step Icon --}}
                            <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full
                                @if($step['status'] === 'completed')
                                    bg-green-100 dark:bg-green-900/20 ring-2 ring-green-500 dark:ring-green-600 ring-offset-2
                                @elseif($step['status'] === 'active')
                                    bg-primary-100 dark:bg-primary-900/20 ring-2 ring-primary-500 dark:ring-primary-600 ring-offset-2 animate-pulse
                                @else
                                    bg-gray-100 dark:bg-gray-800 border-2 border-dashed border-gray-400 dark:border-gray-600
                                @endif">
                                
                                @if($step['status'] === 'completed')
                                    {{-- Checkmark icon --}}
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                @elseif($step['status'] === 'active')
                                    {{-- Filled dot --}}
                                    <span class="h-3 w-3 rounded-full bg-primary-600 dark:bg-primary-400"></span>
                                @else
                                    {{-- Lock icon --}}
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </span>
                            
                            {{-- Step Content --}}
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-base">{{ $step['label'] }}</h4>
                                
                                @if($step['status'] === 'completed' && isset($step['timestamp']))
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        Completed {{ $step['timestamp'] }}
                                    </p>
                                @elseif($step['status'] === 'active' && isset($step['progress']))
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        {{ $step['progress'] }}
                                    </p>
                                @elseif($step['status'] === 'locked' && isset($step['message']))
                                    <p class="text-sm mt-1">{{ $step['message'] }}</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>

@else
    {{-- Desktop Vertical Variant --}}
    <ol role="list" aria-label="Rental completion progress" class="space-y-4 {{ $attributes->get('class') }}">
        @foreach($steps as $index => $step)
            <li class="flex items-start gap-3 
                @if($step['status'] === 'completed') text-green-700 dark:text-green-400
                @elseif($step['status'] === 'active') text-primary-600 dark:text-primary-400
                @else text-gray-400 dark:text-gray-500 opacity-60
                @endif"
                @if($step['status'] === 'active') aria-current="step" @endif>
                
                {{-- Step Icon --}}
                <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full
                    @if($step['status'] === 'completed')
                        bg-green-100 dark:bg-green-900/20 ring-2 ring-green-500 dark:ring-green-600 ring-offset-2
                    @elseif($step['status'] === 'active')
                        bg-primary-100 dark:bg-primary-900/20 ring-2 ring-primary-500 dark:ring-primary-600 ring-offset-2 animate-pulse
                    @else
                        bg-gray-100 dark:bg-gray-800 border-2 border-dashed border-gray-400 dark:border-gray-600
                    @endif">
                    
                    @if($step['status'] === 'completed')
                        {{-- Checkmark icon --}}
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    @elseif($step['status'] === 'active')
                        {{-- Filled dot --}}
                        <span class="h-3 w-3 rounded-full bg-primary-600 dark:bg-primary-400"></span>
                    @else
                        {{-- Lock icon --}}
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                    @endif
                </span>
                
                {{-- Step Content --}}
                <div class="flex-1 min-w-0">
                    <h4 class="font-semibold text-sm">{{ $step['label'] }}</h4>
                    
                    @if($step['status'] === 'completed' && isset($step['timestamp']))
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                            Completed {{ $step['timestamp'] }}
                        </p>
                    @elseif($step['status'] === 'active' && isset($step['progress']))
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                            {{ $step['progress'] }}
                        </p>
                    @elseif($step['status'] === 'locked' && isset($step['message']))
                        <p class="text-xs mt-0.5">{{ $step['message'] }}</p>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
@endif
