<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-page-header 
                title="Kelola Informasi Pembayaran"
                :subtitle="$kost->name . ' — Upload gambar QRIS dan isi informasi rekening bank untuk pembayaran.'"
                :breadcrumbs="[
                    ['label' => 'Kost', 'url' => route('admin.kosts.index')],
                    ['label' => $kost->name, 'url' => route('admin.kosts.show', $kost)],
                    ['label' => 'Pembayaran'],
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

                    <form method="POST" action="{{ route('admin.kosts.payment.update', $kost) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <div class="space-y-6">
                            <!-- QRIS Image Upload -->
                            <div>
                                <label for="qris_image" class="block text-sm font-medium text-gray-700">
                                    Gambar QRIS <span class="text-error-500">*</span>
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
                                        file:bg-primary-50 file:text-primary-700
                                        hover:file:bg-primary-100"
                                >
                                @error('qris_image')
                                    <p class="mt-2 text-sm text-error-600">{{ $message }}</p>
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
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                >
                                @error('bank_name')
                                    <p class="mt-2 text-sm text-error-600">{{ $message }}</p>
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
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                >
                                @error('account_number')
                                    <p class="mt-2 text-sm text-error-600">{{ $message }}</p>
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
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                >
                                @error('account_holder_name')
                                    <p class="mt-2 text-sm text-error-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-between">
                            <a href="{{ route('admin.kosts.show', $kost) }}" class="text-gray-600 hover:text-gray-900">
                                Kembali
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Simpan Informasi Pembayaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
