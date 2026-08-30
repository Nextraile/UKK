<x-base-layout 
    :title="'Paket Harga - ' . $roomType->name . ' - Admin - SewaKost'"
    variant="admin-sidebar"
    page-title="Paket Harga">
    
    <div class="space-y-6" x-data="{
        showModal: false,
        editing: false,
        form: {
            id: null,
            name: '',
            description: '',
            price: '',
            duration_value: '',
            duration_unit: 'month',
            is_active: true
        },
        openCreate() {
            this.editing = false;
            this.form = {
                id: null,
                name: '',
                description: '',
                price: '',
                duration_value: '',
                duration_unit: 'month',
                is_active: true
            };
            this.showModal = true;
        },
        openEdit(priceScheme) {
            this.editing = true;
            this.form = {
                id: priceScheme.id,
                name: priceScheme.name,
                description: priceScheme.description || '',
                price: priceScheme.price,
                duration_value: priceScheme.duration_value,
                duration_unit: priceScheme.duration_unit,
                is_active: priceScheme.is_active
            };
            this.showModal = true;
        },
        closeModal() {
            this.showModal = false;
        },
        async toggleActive(id, currentStatus) {
            if (!confirm('Yakin ingin mengubah status paket harga ini?')) {
                return;
            }
            
            try {
                const response = await fetch(`{{ route('admin.price-schemes.toggle-active', [$roomType, ':id']) }}`.replace(':id', id), {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                
                if (response.ok) {
                    window.location.reload();
                } else {
                    alert('Gagal mengubah status paket harga');
                }
            } catch (error) {
                alert('Terjadi kesalahan saat mengubah status');
            }
        }
    }">
        <x-page-header 
            title="Paket Harga - {{ $roomType->name }}"
            subtitle="Atur paket harga sewa untuk tipe kamar ini"
            :breadcrumbs="[
                ['label' => 'Kost', 'url' => route('admin.kosts.index')],
                ['label' => $roomType->kost->name, 'url' => route('admin.kosts.show', $roomType->kost)],
                ['label' => $roomType->name, 'url' => route('admin.room-types.show', [$roomType->kost, $roomType])],
                ['label' => 'Paket Harga'],
            ]"
        >
            <x-slot:actions>
                <a href="{{ route('admin.room-types.show', [$roomType->kost, $roomType]) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
                    ← Kembali
                </a>
            </x-slot:actions>
        </x-page-header>

        <!-- Success Message -->
        @if (session('success'))
            <x-alert-banner variant="success" class="mb-4" dismissible>
                {{ session('success') }}
            </x-alert-banner>
        @endif

        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
                <!-- Add Button -->
                <div class="mb-6 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Daftar Paket Harga</h3>
                    <button @click="openCreate()" type="button"
                        class="inline-flex items-center rounded-md border border-transparent bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                        aria-label="Tambah paket harga baru">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Paket Harga
                    </button>
                </div>

                <!-- Price Schemes Table -->
                @if($roomType->priceSchemes->isEmpty())
                    <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada paket harga</h3>
                        <p class="mt-1 text-sm text-gray-500">Tambahkan paket harga menggunakan tombol di atas.</p>
                    </div>
                @else
                    <div class="overflow-hidden rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Nama Paket
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Harga
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Durasi
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach($roomType->priceSchemes as $priceScheme)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $priceScheme->name }}</div>
                                            @if($priceScheme->description)
                                                <div class="text-sm text-gray-500">{{ $priceScheme->description }}</div>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                            Rp {{ number_format((float) $priceScheme->price, 0, ',', '.') }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                            {{ $priceScheme->duration_value }} 
                                            @if($priceScheme->duration_unit === 'day')
                                                Hari
                                            @elseif($priceScheme->duration_unit === 'week')
                                                Minggu
                                            @else
                                                Bulan
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                                            <button @click="toggleActive({{ $priceScheme->id }}, {{ $priceScheme->is_active ? 'true' : 'false' }})" 
                                                type="button"
                                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $priceScheme->is_active ? 'bg-success/10 text-success-700' : 'bg-gray-100 text-gray-800' }}">
                                                {{ $priceScheme->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </button>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                            <button @click="openEdit({
                                                id: {{ $priceScheme->id }},
                                                name: '{{ addslashes($priceScheme->name) }}',
                                                description: '{{ addslashes($priceScheme->description ?? '') }}',
                                                price: {{ $priceScheme->price }},
                                                duration_value: {{ $priceScheme->duration_value }},
                                                duration_unit: '{{ $priceScheme->duration_unit }}',
                                                is_active: {{ $priceScheme->is_active ? 'true' : 'false' }}
                                            })" type="button" class="text-primary-600 hover:text-primary-900">
                                                Edit
                                            </button>
                                            <span class="text-gray-300">|</span>
                                            <form method="POST" action="{{ route('admin.price-schemes.destroy', [$roomType, $priceScheme]) }}" 
                                                class="inline"
                                                onsubmit="return confirm('Yakin ingin menghapus paket harga ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-error-600 hover:text-error-900">
                                                    Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Modal -->
        <div x-show="showModal" 
            x-cloak
            x-trap.noscroll="showModal"
            class="fixed inset-0 z-50 overflow-y-auto" 
            aria-labelledby="modal-title" 
            role="dialog" 
            aria-modal="true">
            <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div x-show="showModal" 
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click="closeModal()"
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                    aria-hidden="true"></div>

                <!-- Center modal -->
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

                <!-- Modal panel -->
                <div x-show="showModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:align-middle">
                    
                    <form :action="editing ? 
                        '{{ route('admin.price-schemes.update', [$roomType, ':id']) }}'.replace(':id', form.id) : 
                        '{{ route('admin.price-schemes.store', $roomType) }}'"
                        method="POST">
                        @csrf
                        <input type="hidden" name="_method" x-bind:value="editing ? 'PUT' : 'POST'">

                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 w-full text-center sm:mt-0 sm:text-left">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">
                                        <span x-show="!editing">Tambah Paket Harga</span>
                                        <span x-show="editing">Edit Paket Harga</span>
                                    </h3>
                                    
                                    <div class="mt-4 space-y-4">
                                        <!-- Name -->
                                        <div>
                                            <label for="name" class="block text-sm font-medium text-gray-700">
                                                Nama Paket <span class="text-error-500">*</span>
                                            </label>
                                            <input type="text" 
                                                name="name" 
                                                id="name" 
                                                x-model="form.name"
                                                required
                                                maxlength="100"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('name') border-error-300 @enderror"
                                                placeholder="contoh: Bulanan, Tahunan, 6 Bulan">
                                            @error('name')
                                                <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <!-- Description -->
                                        <div>
                                            <label for="description" class="block text-sm font-medium text-gray-700">
                                                Deskripsi
                                            </label>
                                            <textarea 
                                                name="description" 
                                                id="description" 
                                                x-model="form.description"
                                                rows="2"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('description') border-error-300 @enderror"
                                                placeholder="Opsional - keterangan tambahan tentang paket ini"></textarea>
                                            @error('description')
                                                <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <!-- Price -->
                                        <div>
                                            <label for="price" class="block text-sm font-medium text-gray-700">
                                                Harga (Rp) <span class="text-error-500">*</span>
                                            </label>
                                            <input type="number" 
                                                name="price" 
                                                id="price" 
                                                x-model="form.price"
                                                required
                                                min="0"
                                                step="1"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('price') border-error-300 @enderror"
                                                placeholder="contoh: 1500000">
                                            @error('price')
                                                <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <!-- Duration -->
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label for="duration_value" class="block text-sm font-medium text-gray-700">
                                                    Durasi <span class="text-error-500">*</span>
                                                </label>
                                                <input type="number" 
                                                    name="duration_value" 
                                                    id="duration_value" 
                                                    x-model="form.duration_value"
                                                    required
                                                    min="1"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('duration_value') border-error-300 @enderror"
                                                    placeholder="contoh: 1">
                                                @error('duration_value')
                                                    <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div>
                                                <label for="duration_unit" class="block text-sm font-medium text-gray-700">
                                                    Satuan <span class="text-error-500">*</span>
                                                </label>
                                                <select 
                                                    name="duration_unit" 
                                                    id="duration_unit" 
                                                    x-model="form.duration_unit"
                                                    required
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('duration_unit') border-error-300 @enderror">
                                                    <option value="day">Hari</option>
                                                    <option value="week">Minggu</option>
                                                    <option value="month">Bulan</option>
                                                </select>
                                                @error('duration_unit')
                                                    <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Is Active -->
                                        <div class="flex items-center">
                                            <input type="hidden" name="is_active" value="0">
                                            <input type="checkbox" 
                                                name="is_active" 
                                                id="is_active" 
                                                value="1"
                                                x-model="form.is_active"
                                                class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                                Aktif (paket dapat dipilih penyewa)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="submit"
                                class="inline-flex w-full justify-center rounded-md border border-transparent bg-primary-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">
                                <span x-show="!editing">Tambah</span>
                                <span x-show="editing">Simpan</span>
                            </button>
                            <button @click="closeModal()" type="button"
                                class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-base-layout>
