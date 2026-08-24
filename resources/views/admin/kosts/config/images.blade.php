@extends('layouts.admin')

@section('title', 'Kelola Gambar Kost')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <a href="{{ route('admin.kosts.show', $kost) }}" class="text-blue-600 hover:text-blue-800">
            ← Kembali ke Detail Kost
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold mb-2">Kelola Gambar Kost</h1>
        <p class="text-gray-600 mb-6">{{ $kost->name }}</p>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
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
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                        required
                    >
                    <button 
                        type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 whitespace-nowrap"
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
                                src="{{ Storage::url($image->image_path) }}" 
                                alt="Gambar Kost" 
                                class="w-full h-48 object-cover"
                            >
                            
                            <!-- Thumbnail Badge -->
                            @if ($image->is_thumbnail)
                                <div class="absolute top-2 left-2 bg-green-600 text-white text-xs font-semibold px-2 py-1 rounded">
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
                                                class="w-full px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700"
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
                                            class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700"
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
                <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded">
                    <p class="text-sm text-blue-800">
                        <strong>Catatan:</strong> Urutan gambar (#1, #2, #3...) ditentukan saat upload. 
                        Untuk mengubah urutan, hapus dan upload ulang gambar sesuai urutan yang diinginkan.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
