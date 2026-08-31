<x-base-layout 
    title="Kelola Gambar Kost - Admin - SewaKost"
    variant="admin-sidebar"
    page-title="Kelola Gambar Kost">
    
<div class="container mx-auto px-4 py-6">
    <x-page-header 
        title="Kelola Gambar Kost"
        :subtitle="$kost->name"
        :breadcrumbs="[
            ['label' => 'Kost', 'url' => route('admin.kosts.index')],
            ['label' => $kost->name, 'url' => route('admin.kosts.show', $kost)],
            ['label' => 'Gambar'],
        ]"
    >
        <x-slot:actions>
            <a href="{{ route('admin.kosts.show', $kost) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
                ← Kembali
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-white rounded-lg shadow-md p-6">

        @if (session('success'))
            <div class="bg-success/10 border border-success-400 text-success-700 px-4 py-3 rounded mb-4" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-error/10 border border-error-400 text-error-700 px-4 py-3 rounded mb-4" role="alert">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Upload Form -->
        <div class="mb-8 p-4 border-2 border-dashed border-gray-300 rounded-lg">
            <h2 class="text-lg font-semibold mb-3">Upload Gambar Baru</h2>
            <form action="{{ route('admin.kosts.images.store', $kost) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="flex items-center gap-4">
                    <input 
                        type="file" 
                        name="image" 
                        id="image" 
                        accept="image/jpeg,image/jpg,image/png,image/webp"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
                        required
                    >
                    <button 
                        type="submit" 
                        class="px-6 py-2 bg-primary-600 text-white rounded hover:bg-primary-700 whitespace-nowrap"
                    >
                        Upload
                    </button>
                </div>
                <p class="text-sm text-gray-500 mt-2">
                    Format: JPEG, JPG, PNG, WebP. Maksimal 5MB.
                </p>
            </form>
        </div>

        <!-- Image Gallery -->
        <div>
            <h2 class="text-lg font-semibold mb-4">Gambar Tersimpan ({{ $kost->kostImages->count() }})</h2>
            
            @if ($kost->kostImages->isEmpty())
                <p class="text-gray-500 text-center py-8">Belum ada gambar. Upload gambar pertama Anda.</p>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($kost->kostImages->sortBy('sort_order') as $image)
                        <div class="relative border rounded-lg overflow-hidden group">
                            <!-- Image -->
                            <img 
                                src="{{ $image->image_url }}" 
                                alt="Gambar Kost" 
                                class="w-full h-48 object-cover"
                            >
                            
                            <!-- Thumbnail Badge -->
                            @if ($image->is_thumbnail)
                                <div class="absolute top-2 left-2 bg-success-600 text-white text-xs font-semibold px-2 py-1 rounded">
                                    THUMBNAIL
                                </div>
                            @endif

                            <!-- Sort Order Badge -->
                            <div class="absolute top-2 right-2 bg-gray-800 bg-opacity-75 text-white text-xs font-semibold px-2 py-1 rounded">
                                #{{ $image->sort_order }}
                            </div>

                            <!-- Action Buttons -->
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                <div class="flex gap-2">
                                    <!-- Set Thumbnail -->
                                    @if (!$image->is_thumbnail)
                                        <form action="{{ route('admin.kosts.images.set-thumbnail', [$kost, $image]) }}" method="POST" class="flex-1">
                                            @csrf
                                            @method('PATCH')
                                            <button 
                                                type="submit" 
                                                class="w-full px-3 py-1 bg-success-600 text-white text-sm rounded hover:bg-success-700"
                                            >
                                                Set Thumbnail
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Delete -->
                                    <form action="{{ route('admin.kosts.images.destroy', [$kost, $image]) }}" method="POST" onsubmit="return confirm('Hapus gambar ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="submit" 
                                            class="px-3 py-1 bg-error-600 text-white text-sm rounded hover:bg-error-700"
                                        >
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Sort Order Instructions -->
                <div class="mt-6 p-4 bg-info-light border border-info-200 rounded">
                    <p class="text-sm text-info-700">
                        <strong>Catatan:</strong> Urutan gambar (#1, #2, #3...) ditentukan saat upload. 
                        Untuk mengubah urutan, hapus dan upload ulang gambar sesuai urutan yang diinginkan.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
</x-base-layout>
