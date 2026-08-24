<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Informasi Pembayaran') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900">{{ $kost->name }}</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Upload gambar QRIS dan isi informasi rekening bank untuk pembayaran.
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

                    <form method="POST" action="{{ route('admin.kosts.payment.update', $kost) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <div class="space-y-6">
                            <!-- QRIS Image Upload -->
                            <div>
                                <label for="qris_image" class="block text-sm font-medium text-gray-700">
                                    Gambar QRIS <span class="text-red-500">*</span>
                                </label>
                                <p class="mt-1 text-sm text-gray-500">
                                    Format: JPEG, PNG, JPG. Maksimal 2MB.
                                </p>

                                @if($kost->qris_image_path)
                                    <div class="mt-3 mb-3">
                                        <p class="text-sm text-gray-600 mb-2">QRIS saat ini:</p>
                                        <img 
                                            src="{{ Storage::url($kost->qris_image_path) }}" 
                                            alt="QRIS {{ $kost->name }}"
                                            class="max-w-xs border border-gray-300 rounded-lg shadow-sm"
                                        >
                                    </div>
                                @endif

                                <input 
                                    type="file" 
                                    id="qris_image" 
                                    name="qris_image" 
                                    accept="image/*"
                                    class="mt-2 block w-full text-sm text-gray-500
                                        file:mr-4 file:py-2 file:px-4
                                        file:rounded-md file:border-0
                                        file:text-sm file:font-semibold
                                        file:bg-indigo-50 file:text-indigo-700
                                        hover:file:bg-indigo-100"
                                >
                                @error('qris_image')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Bank Name -->
                            <div>
                                <label for="bank_name" class="block text-sm font-medium text-gray-700">
                                    Nama Bank
                                </label>
                                <input 
                                    type="text" 
                                    id="bank_name" 
                                    name="bank_name" 
                                    value="{{ old('bank_name', $kost->bank_name) }}"
                                    maxlength="100"
                                    placeholder="Contoh: BCA, Mandiri, BNI"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                @error('bank_name')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Account Number -->
                            <div>
                                <label for="account_number" class="block text-sm font-medium text-gray-700">
                                    Nomor Rekening
                                </label>
                                <input 
                                    type="text" 
                                    id="account_number" 
                                    name="account_number" 
                                    value="{{ old('account_number', $kost->account_number) }}"
                                    maxlength="50"
                                    placeholder="Contoh: 1234567890"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                @error('account_number')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Account Holder Name -->
                            <div>
                                <label for="account_holder_name" class="block text-sm font-medium text-gray-700">
                                    Nama Pemilik Rekening
                                </label>
                                <input 
                                    type="text" 
                                    id="account_holder_name" 
                                    name="account_holder_name" 
                                    value="{{ old('account_holder_name', $kost->account_holder_name) }}"
                                    maxlength="150"
                                    placeholder="Nama sesuai rekening bank"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                @error('account_holder_name')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-between">
                            <a href="{{ route('admin.kosts.show', $kost) }}" class="text-gray-600 hover:text-gray-900">
                                Kembali
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Simpan Informasi Pembayaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
