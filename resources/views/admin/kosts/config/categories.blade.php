<x-base-layout 
    title="Kelola Kategori Kost - SewaKost"
    variant="full-width">
    
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-page-header 
                title="Kelola Kategori Kost"
                :subtitle="$kost->name . ' — Pilih minimal 1 kategori untuk kost ini.'"
                :breadcrumbs="[
                    ['label' => 'Kost', 'url' => route('admin.kosts.index')],
                    ['label' => $kost->name, 'url' => route('admin.kosts.show', $kost)],
                    ['label' => 'Kategori'],
                ]"
            >
                <x-slot:actions>
                    <a href="{{ route('admin.kosts.show', $kost) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
                        ← Kembali
                    </a>
                </x-slot:actions>
            </x-page-header>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if (session('success'))
                        <x-alert-banner variant="success" class="mb-4" dismissible>
                            {{ session('success') }}
                        </x-alert-banner>
                    @endif

                    @if (session('error'))
                        <x-alert-banner variant="error" class="mb-4" dismissible>
                            {{ session('error') }}
                        </x-alert-banner>
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
                                            class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-gray-300 rounded"
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
                                <p class="mt-2 text-sm text-error-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-6 flex items-center justify-between">
                            <a href="{{ route('admin.kosts.show', $kost) }}" class="text-gray-600 hover:text-gray-900">
                                Kembali
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Simpan Kategori
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-base-layout>
