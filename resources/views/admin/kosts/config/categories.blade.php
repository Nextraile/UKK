<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Kategori Kost') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900">{{ $kost->name }}</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Pilih minimal 1 kategori untuk kost ini.
                        </p>
                    </div>

                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.kosts.categories.update', $kost) }}">
                        @csrf
                        @method('PATCH')

                        <div class="space-y-4">
                            @forelse ($categories as $category)
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input 
                                            id="category_{{ $category->id }}" 
                                            name="category_ids[]" 
                                            type="checkbox" 
                                            value="{{ $category->id }}"
                                            @if($kost->categories->pluck('id')->contains($category->id)) checked @endif
                                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                        >
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="category_{{ $category->id }}" class="font-medium text-gray-700">
                                            {{ $category->name }}
                                        </label>
                                        @if($category->description)
                                            <p class="text-gray-500">{{ $category->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-500">Tidak ada kategori yang tersedia.</p>
                            @endforelse

                            @error('category_ids')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-6 flex items-center justify-between">
                            <a href="{{ route('admin.kosts.show', $kost) }}" class="text-gray-600 hover:text-gray-900">
                                Kembali
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Simpan Kategori
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
