<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Foto Profil') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Upload foto profil Anda. Format JPEG, PNG, atau WebP. Maksimal 2MB.') }}
        </p>
    </header>

    <div class="mt-6 flex items-center gap-6">
        @if ($user->avatar_path)
            <img src="{{ asset('storage/' . $user->avatar_path) }}"
                 alt="Avatar"
                 class="h-20 w-20 rounded-full object-cover border-2 border-gray-200 dark:border-gray-700" />
        @else
            <div class="h-20 w-20 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center border-2 border-gray-200 dark:border-gray-700">
                <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                    {{ strtoupper(substr($user->first_name, 0, 1)) }}
                </span>
            </div>
        @endif

        <form method="post" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" class="flex-1">
            @csrf
            <div>
                <x-input-label for="avatar" :value="__('Pilih File')" class="sr-only" />
                <input
                    id="avatar"
                    type="file"
                    name="avatar"
                    accept="image/jpeg,image/png,image/webp"
                    class="block w-full text-sm text-gray-500 dark:text-gray-400
                           file:mr-4 file:py-2 file:px-4
                           file:rounded-md file:border-0
                           file:text-sm file:font-semibold
                           file:bg-indigo-50 file:text-indigo-700
                           hover:file:bg-indigo-100
                           dark:file:bg-indigo-900/30 dark:file:text-indigo-300"
                />
                <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
            </div>

            <div class="mt-4">
                <x-primary-button>{{ __('Upload') }}</x-primary-button>
            </div>
        </form>
    </div>
</section>
