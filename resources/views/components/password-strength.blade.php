@props([
    'inputId' => 'password',
    'name' => 'password',
    'label' => 'Password',
    'required' => true,
    'autocomplete' => 'new-password',
    'placeholder' => 'Minimal 8 karakter',
    'value' => '',
    'errors' => null,
])

<div x-data="{
    value: '{{ old($name, $value) }}',
    show: false,
    get score() {
        const v = this.value;
        if (!v) return 0;
        let s = 1;
        if (v.length >= 8) s++;
        if (/[a-z]/.test(v) && /[A-Z]/.test(v)) s++;
        if (/\d/.test(v) && /[^A-Za-z0-9]/.test(v)) s++;
        return Math.min(4, s);
    },
    get label() { return ['', 'Lemah', 'Cukup', 'Kuat', 'Sangat kuat'][this.score]; },
    get bar() { return ['', 'bg-error-700', 'bg-warning-700', 'bg-info-700', 'bg-success-700'][this.score]; },
    get labelClass() { return ['', 'text-error-700', 'text-warning-700', 'text-info-700', 'text-success-700'][this.score]; }
}" x-cloak class="space-y-2">

    <label for="{{ $inputId }}" class="block text-sm font-medium text-gray-700 dark:text-text-strong-dark">
        {{ $label }}
        @if ($required)
            <span class="text-error-700" aria-label="required">*</span>
        @endif
    </label>

    <div class="relative">
        <input :type="show ? 'text' : 'password'" 
               id="{{ $inputId }}" 
               name="{{ $name }}" 
               x-model="value"
               placeholder="{{ $placeholder }}" 
               autocomplete="{{ $autocomplete }}"
               aria-describedby="{{ $inputId }}-hint {{ $inputId }}-strength {{ $inputId }}-error"
               {{ $required ? 'required' : '' }}
               class="w-full px-4 py-3 pr-12 border border-gray-300 dark:border-border-strong-dark rounded-md focus:ring-2 focus:ring-primary-500 transition-all @error($name) border-error-500 @enderror">
        
        <button type="button" 
                @click="show = !show" 
                :aria-pressed="show ? 'true' : 'false'"
                :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'"
                class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
            <!-- Eye icon (show password) -->
            <svg x-show="!show" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <!-- Eye slash icon (hide password) -->
            <svg x-show="show" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228L21 21" />
            </svg>
        </button>
    </div>

    <!-- Strength meter (4 segments) -->
    <div class="flex gap-1" aria-hidden="true" x-show="value.length > 0">
        <template x-for="i in 4" :key="i">
            <div class="h-1.5 flex-1 rounded-full transition-colors"
                 :class="i <= score ? bar : 'bg-gray-200 dark:bg-border-dark'"></div>
        </template>
    </div>

    <!-- Strength label (announced to screen readers) -->
    <p id="{{ $inputId }}-strength" 
       class="text-xs font-medium" 
       :class="labelClass" 
       aria-live="polite" 
       x-text="label"
       x-show="value.length > 0"></p>

    <!-- Password hints -->
    <ul id="{{ $inputId }}-hint" class="text-xs text-gray-500 dark:text-text-muted-dark space-y-0.5">
        <li>Minimal 8 karakter</li>
        <li>Kombinasi huruf besar & kecil, angka, simbol</li>
    </ul>

    <!-- Validation errors -->
    @if ($errors)
        <x-input-error id="{{ $inputId }}-error" :messages="$errors->get($name)" class="mt-2" />
    @else
        @error($name)
            <p id="{{ $inputId }}-error" class="text-sm text-error-600 mt-2">{{ $message }}</p>
        @enderror
    @endif
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
