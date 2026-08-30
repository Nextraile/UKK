<section x-data="{ 
    preview: null, 
    fileName: null,
    handleFileSelect(event) {
        const file = event.target.files[0];
        if (file && file.type.startsWith('image/')) {
            this.fileName = file.name;
            const reader = new FileReader();
            reader.onload = (e) => {
                this.preview = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
}">
    <header>
        <h2 class="text-lg font-medium text-text-strong dark:text-text-strong-dark">
            {{ __('Foto Profil') }}
        </h2>

        <p class="mt-1 text-sm text-text dark:text-text-muted-dark">
            {{ __('Upload foto profil Anda. Format JPEG, PNG, atau WebP. Maksimal 2MB.') }}
        </p>
    </header>

    @if ($errors->avatar->any())
        <div role="alert" aria-live="assertive" class="mt-4 rounded-md bg-error/10 border border-error/20 p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-error-700 dark:text-error-300 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-error-700 dark:text-error-300">
                        Terdapat {{ $errors->avatar->count() }} kesalahan
                    </h3>
                    <ul class="mt-2 text-sm text-error-700 dark:text-error-300 space-y-1">
                        @foreach ($errors->avatar->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div x-data="{ uploading: false }">
        <form method="post" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" class="mt-6" @submit="uploading = true">
            @csrf
            
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                {{-- Current/Preview Avatar --}}
                <div class="flex-shrink-0">
                    <template x-if="preview">
                        <img :src="preview" 
                             alt="Preview avatar baru"
                             class="h-24 w-24 rounded-full object-cover border-2 border-primary-300 dark:border-primary-700" />
                    </template>
                    <template x-if="!preview">
                        @if ($user->avatar_path)
                            <img src="{{ asset('storage/' . $user->avatar_path) }}"
                                 alt="Avatar saat ini"
                                 class="h-24 w-24 rounded-full object-cover border-2 border-border dark:border-border-dark" />
                        @else
                            <div class="h-24 w-24 rounded-full bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center border-2 border-border dark:border-border-dark">
                                <span class="text-3xl font-bold text-primary-600 dark:text-primary-400">
                                    {{ strtoupper(substr($user->first_name ?? $user->email, 0, 1)) }}
                                </span>
                            </div>
                        @endif
                    </template>
                </div>

                {{-- Upload Zone --}}
                <div class="flex-1 w-full">
                    <label for="avatar" 
                           class="flex flex-col items-center justify-center w-full px-6 py-8 border-2 border-dashed rounded-lg cursor-pointer transition-colors
                                  border-border dark:border-border-dark 
                                  hover:border-primary-400 dark:hover:border-primary-600
                                  hover:bg-primary-50 dark:hover:bg-primary-900/10
                                  focus-within:ring-2 focus-within:ring-primary-500 focus-within:border-primary-500">
                        <div class="flex flex-col items-center text-center">
                            <svg class="w-10 h-10 text-text-muted dark:text-text-muted-dark mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-sm text-text dark:text-text-dark">
                                <span class="font-semibold text-primary-600 dark:text-primary-400">Klik untuk upload</span>
                                <span x-show="!fileName"> atau drag & drop</span>
                            </p>
                            <p class="text-xs text-text-muted dark:text-text-muted-dark mt-1" x-show="!fileName">
                                JPEG, PNG, atau WebP (maks. 2MB)
                            </p>
                            <p class="text-sm text-text-strong dark:text-text-strong-dark mt-2 font-medium" x-show="fileName" x-text="fileName"></p>
                        </div>
                        <input 
                            id="avatar"
                            type="file"
                            name="avatar"
                            accept="image/jpeg,image/png,image/webp"
                            class="sr-only"
                            @change="handleFileSelect($event)"
                        />
                    </label>
                    <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
                </div>
            </div>

            <div class="mt-6 flex items-center gap-4">
                <button type="submit"
                        x-show="preview || fileName"
                        x-bind:disabled="uploading"
                        class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150">
                    <span x-show="!uploading">{{ __('Upload') }}</span>
                    <span x-show="uploading" class="inline-flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Mengupload...
                    </span>
                </button>
                <button 
                    type="button" 
                    @click="preview = null; fileName = null; uploading = false; $el.closest('form').reset()"
                    x-show="preview"
                    class="text-sm text-text-muted dark:text-text-muted-dark hover:text-text dark:hover:text-text-dark">
                    Batal
                </button>
            </div>
        </form>
    </div>
</section>
