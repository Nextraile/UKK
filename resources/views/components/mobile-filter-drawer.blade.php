{{-- Mobile Filter Drawer Component --}}
{{-- Usage: <x-mobile-filter-drawer :categories="$allCategories" /> --}}
{{-- Specification: DESIGN.md §3.32, PAGES.md line 216-316 --}}

@props(['categories'])

<div x-data="{ open: false }" x-on:keydown.escape.window="open = false">
    {{-- Trigger Button (Mobile only, sticky FAB bottom-right) --}}
    <button 
        type="button"
        x-on:click="open = true"
        class="lg:hidden fixed bottom-4 right-4 z-40 flex items-center gap-2 px-4 py-3 bg-primary-600 text-white rounded-full shadow-lg hover:bg-primary-700 transition-all"
        aria-label="Buka filter"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
        </svg>
        Filter
        {{-- Active filter count badge --}}
        @php
            $activeFilterCount = collect([
                request('price_min') ? 1 : 0,
                request('price_max') ? 1 : 0,
                request('categories') ? count(request('categories')) : 0,
                request('rating_min') ? 1 : 0
            ])->sum();
        @endphp
        @if($activeFilterCount > 0)
            <span class="flex items-center justify-center w-5 h-5 bg-white text-primary-600 text-xs font-bold rounded-full">
                {{ $activeFilterCount }}
            </span>
        @endif
    </button>

    {{-- Backdrop --}}
    <div 
        x-show="open"
        x-transition:enter="transition-opacity ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-on:click="open = false"
        class="lg:hidden fixed inset-0 bg-black/50 z-40"
        style="display: none;"
        aria-hidden="true"
    ></div>

    {{-- Drawer (slide from right) --}}
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        x-trap.noscroll.inert="open"
        class="lg:hidden fixed right-0 top-0 bottom-0 w-80 max-w-full bg-white dark:bg-surface-raised-dark shadow-2xl z-50 overflow-y-auto"
        style="display: none;"
        role="dialog"
        aria-modal="true"
        aria-labelledby="drawer-title"
    >
        {{-- Header --}}
        <div class="sticky top-0 bg-white dark:bg-surface-raised-dark border-b border-border dark:border-border-dark px-4 py-4 flex items-center justify-between z-10">
            <h2 id="drawer-title" class="text-lg font-semibold text-gray-900 dark:text-text-strong-dark">Filter</h2>
            <button 
                type="button"
                x-on:click="open = false"
                class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-surface-muted-dark transition-colors"
                aria-label="Tutup filter"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Filter Form (same structure as desktop sidebar) --}}
        <div class="p-4">
            <form method="GET" action="{{ route('marketplace.index') }}" class="space-y-6">
                {{-- Preserve search param --}}
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                
                {{-- Price Range Filter (FR-052) --}}
                <fieldset>
                    <legend class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Harga (per bulan)</legend>
                    <div class="space-y-2">
                        <input 
                            type="number" 
                            name="price_min" 
                            value="{{ request('price_min') }}"
                            placeholder="Min"
                            min="0"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-gray-100"
                        >
                        <input 
                            type="number" 
                            name="price_max" 
                            value="{{ request('price_max') }}"
                            placeholder="Max"
                            min="0"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-gray-100"
                        >
                    </div>
                </fieldset>
                
                {{-- Category Filter (FR-053) --}}
                <fieldset>
                    <legend class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Kategori</legend>
                    <div class="space-y-2">
                        @foreach($categories as $category)
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    name="categories[]" 
                                    value="{{ $category->id }}"
                                    {{ in_array($category->id, request('categories', [])) ? 'checked' : '' }}
                                    class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
                                >
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $category->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
                
                {{-- Rating Filter (FR-054) --}}
                <fieldset>
                    <legend class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Rating Minimum</legend>
                    <select 
                        name="rating_min"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-gray-100"
                    >
                        <option value="">Semua Rating</option>
                        @for($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}" {{ request('rating_min') == $i ? 'selected' : '' }}>
                                {{ $i }} ★ ke atas
                            </option>
                        @endfor
                    </select>
                </fieldset>
                
                {{-- Apply Filter Button --}}
                <button 
                    type="submit"
                    class="w-full px-4 py-3 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition-colors"
                    x-on:click="open = false"
                >
                    Terapkan Filter
                </button>
                
                {{-- Reset Filter --}}
                @if(request('price_min') || request('price_max') || request('categories') || request('rating_min'))
                    <a 
                        href="{{ route('marketplace.index', ['search' => request('search')]) }}"
                        class="block w-full text-center px-4 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        x-on:click="open = false"
                    >
                        Reset Filter
                    </a>
                @endif
            </form>
        </div>
    </div>
</div>
