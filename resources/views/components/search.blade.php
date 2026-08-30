@props([
    'placeholder' => 'Cari...',
    'name' => 'search',
    'value' => '',
    'action' => null,
    'method' => 'GET',
])

<form 
  @if ($action) action="{{ $action }}" @endif 
  method="{{ strtoupper($method) }}"
  {{ $attributes->except(['placeholder', 'name', 'value']) }}
  class="relative">
  
  @if (strtoupper($method) !== 'GET')
    @csrf
  @endif
  
  <div class="relative">
    <!-- Search Icon -->
    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
      <svg class="w-5 h-5 text-gray-400 dark:text-text-muted-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
      </svg>
    </div>
    
    <!-- Search Input -->
    <input 
      type="text" 
      id="{{ $name }}"
      name="{{ $name }}" 
      value="{{ old($name, $value) }}"
      placeholder="{{ $placeholder }}" 
      class="block w-full pl-10 pr-10 py-3 border border-gray-300 dark:border-border-strong-dark rounded-lg bg-white dark:bg-surface-raised-dark text-gray-900 dark:text-text-strong-dark placeholder:text-gray-400 dark:placeholder:text-text-muted-dark focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
      aria-label="Pencarian">
    
    <!-- Clear Button (shown when input has value) -->
    <button 
      type="button"
      x-data="{ show: false }"
      x-init="show = $el.previousElementSibling.value.length > 0"
      @input.window="show = $el.previousElementSibling.value.length > 0"
      x-show="show"
      @click="$el.previousElementSibling.value = ''; show = false; $el.previousElementSibling.focus();"
      class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-text-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 rounded"
      aria-label="Hapus pencarian">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>
  </div>
  
  {{ $slot }}
</form>
