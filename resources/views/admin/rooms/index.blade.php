<x-base-layout 
    :title="'Kelola Kamar - ' . $kost->name . ' - Admin - SewaKost'"
    variant="admin-sidebar"
    page-title="Kelola Kamar">
    
    <div class="space-y-6" x-data="{
        activeRoomType: null,
        editingRoom: null,
        form: {
            room_type_id: '',
            code: '',
            internal_notes: ''
        },
        toggleRoomType(id) {
            this.activeRoomType = this.activeRoomType === id ? null : id;
        },
        startEdit(room) {
            this.editingRoom = room.id;
            this.form = {
                room_type_id: room.room_type_id,
                code: room.code,
                internal_notes: room.internal_notes || ''
            };
        },
        cancelEdit() {
            this.editingRoom = null;
            this.form = {
                room_type_id: '',
                code: '',
                internal_notes: ''
            };
        },
        async toggleStatus(roomId, currentStatus) {
            const newStatus = currentStatus === 'available' ? 'unavailable' : 'available';
            const confirmMsg = newStatus === 'unavailable' 
                ? 'Yakin ingin menonaktifkan kamar ini? Kamar tidak akan tersedia untuk sewa baru.'
                : 'Yakin ingin mengaktifkan kamar ini?';
            
            if (!confirm(confirmMsg)) {
                return;
            }
            
            try {
                const response = await fetch(`{{ route('admin.rooms.set-status', [$kost, ':id']) }}`.replace(':id', roomId), {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ status: newStatus })
                });
                
                if (response.ok) {
                    window.location.reload();
                } else {
                    const data = await response.json();
                    alert(data.message || 'Gagal mengubah status kamar');
                }
            } catch (error) {
                alert('Terjadi kesalahan saat mengubah status kamar');
            }
        }
    }">
        <x-page-header 
            title="Kelola Kamar - {{ $kost->name }}"
            subtitle="Atur unit kamar untuk setiap tipe kamar"
            :breadcrumbs="[
                ['label' => 'Kost', 'url' => route('admin.kosts.index')],
                ['label' => $kost->name, 'url' => route('admin.kosts.show', $kost)],
                ['label' => 'Kamar'],
            ]"
        >
            <x-slot:actions>
                <a href="{{ route('admin.kosts.show', $kost) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
                    ← Kembali
                </a>
            </x-slot:actions>
        </x-page-header>

        <!-- Success/Error Messages -->
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

        @if ($roomTypes->isEmpty())
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-center text-gray-900">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada tipe kamar</h3>
                    <p class="mt-1 text-sm text-gray-500">Buat tipe kamar terlebih dahulu sebelum menambah unit kamar.</p>
                    <div class="mt-6">
                        <a href="{{ route('admin.room-types.index', $kost) }}" class="inline-flex items-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500">
                            <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Kelola Tipe Kamar
                        </a>
                    </div>
                </div>
            </div>
        @else
            <!-- Room Types Accordion -->
            <div class="space-y-4">
                @foreach ($roomTypes as $roomType)
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <!-- Accordion Header -->
                        <button 
                            @click="toggleRoomType({{ $roomType->id }})"
                            class="flex w-full items-center justify-between p-6 text-left hover:bg-gray-50"
                        >
                            <div class="flex items-center space-x-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $roomType->name }}</h3>
                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ $roomType->rooms->count() }} kamar
                                        @if($roomType->rooms->count() > 0)
                                            • Total kapasitas: {{ $roomType->rooms->count() * $roomType->max_occupants }} orang
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <svg 
                                class="h-5 w-5 text-gray-400 transition-transform"
                                :class="{ 'rotate-180': activeRoomType === {{ $roomType->id }} }"
                                fill="none" 
                                viewBox="0 0 24 24" 
                                stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Accordion Content -->
                        <div 
                            x-show="activeRoomType === {{ $roomType->id }}"
                            x-collapse
                            class="border-t border-gray-200"
                        >
                            <div class="p-6">
                                <!-- Add Room Form -->
                                <div class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-4">
                                    <h4 class="mb-3 text-sm font-semibold text-gray-700">Tambah Kamar Baru</h4>
                                    <form action="{{ route('admin.rooms.store', $kost) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="room_type_id" value="{{ $roomType->id }}">
                                        <input type="hidden" name="status" value="available">
                                        
                                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                            <div>
                                                <x-input-label for="code_{{ $roomType->id }}" value="Kode Kamar" />
                                                <x-text-input 
                                                    id="code_{{ $roomType->id }}" 
                                                    name="code" 
                                                    type="text" 
                                                    class="mt-1 block w-full" 
                                                    placeholder="Contoh: A101"
                                                    required 
                                                />
                                                <x-input-error :messages="$errors->get('code')" class="mt-2" />
                                            </div>
                                            
                                            <div class="sm:col-span-2">
                                                <x-input-label for="internal_notes_{{ $roomType->id }}" value="Catatan Internal (Opsional)" />
                                                <x-text-input 
                                                    id="internal_notes_{{ $roomType->id }}" 
                                                    name="internal_notes" 
                                                    type="text" 
                                                    class="mt-1 block w-full" 
                                                    placeholder="Catatan khusus untuk kamar ini..."
                                                />
                                                <x-input-error :messages="$errors->get('internal_notes')" class="mt-2" />
                                            </div>
                                        </div>
                                        
                                        <div class="mt-3">
                                            <button type="submit" class="inline-flex items-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500">
                                                <svg class="-ml-0.5 mr-1.5 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                                </svg>
                                                Tambah Kamar
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Rooms Table -->
                                @if($roomType->rooms->isEmpty())
                                    <p class="text-center text-sm text-gray-500 py-4">Belum ada kamar untuk tipe ini.</p>
                                @else
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Kode Kamar</th>
                                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Okupansi</th>
                                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Catatan Internal</th>
                                                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200 bg-white">
                                                @foreach($roomType->rooms as $room)
                                                    <tr x-data="{ editing: false }">
                                                        <!-- Code Column -->
                                                        <td class="whitespace-nowrap px-4 py-4">
                                                            <div x-show="!editing">
                                                                <span class="font-medium text-gray-900">{{ $room->code }}</span>
                                                            </div>
                                                            <div x-show="editing" style="display: none;">
                                                                <x-text-input 
                                                                    name="code" 
                                                                    type="text" 
                                                                    class="w-32" 
                                                                    value="{{ $room->code }}"
                                                                    required 
                                                                    form="edit-room-{{ $room->id }}"
                                                                />
                                                            </div>
                                                        </td>

                                                        <!-- Status Column -->
                                                        <td class="whitespace-nowrap px-4 py-4">
                                                            @if($room->status === 'available')
                                                                <span class="inline-flex rounded-full bg-success/10 px-2 text-xs font-semibold leading-5 text-success-700">Tersedia</span>
                                                            @else
                                                                <span class="inline-flex rounded-full bg-gray-100 px-2 text-xs font-semibold leading-5 text-gray-800">Nonaktif</span>
                                                            @endif
                                                        </td>

                                                        <!-- Occupancy Column -->
                                                        <td class="px-4 py-4">
                                                            <div class="text-sm text-gray-900">
                                                                <span class="font-medium">{{ $room->free_slots }}</span> / {{ $roomType->max_occupants }} slot
                                                            </div>
                                                            <div class="text-xs text-gray-500">
                                                                Reserved: {{ $room->reserved_count }} • Occupied: {{ $room->occupied_count }}
                                                            </div>
                                                        </td>

                                                        <!-- Internal Notes Column -->
                                                        <td class="px-4 py-4">
                                                            <div x-show="!editing">
                                                                <span class="text-sm text-gray-600">{{ $room->internal_notes ?: '-' }}</span>
                                                            </div>
                                                            <div x-show="editing" style="display: none;">
                                                                <x-text-input 
                                                                    name="internal_notes" 
                                                                    type="text" 
                                                                    class="w-full" 
                                                                    value="{{ $room->internal_notes }}"
                                                                    form="edit-room-{{ $room->id }}"
                                                                />
                                                            </div>
                                                        </td>

                                                        <!-- Actions Column -->
                                                        <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-medium">
                                                            <div x-show="!editing" class="flex items-center justify-end space-x-2">
                                                                <!-- Edit Button -->
                                                                <button 
                                                                    @click="editing = true"
                                                                    class="text-primary-600 hover:text-primary-900"
                                                                    title="Edit"
                                                                    aria-label="Edit kamar {{ $room->code }}"
                                                                >
                                                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                                    </svg>
                                                                </button>

                                                                <!-- Toggle Status Button -->
                                                                <button 
                                                                    @click="toggleStatus({{ $room->id }}, '{{ $room->status }}')"
                                                                    class="text-gray-600 hover:text-gray-900"
                                                                    title="Ubah Status"
                                                                    aria-label="Ubah status kamar {{ $room->code }}"
                                                                >
                                                                    @if($room->status === 'available')
                                                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                                        </svg>
                                                                    @else
                                                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                        </svg>
                                                                    @endif
                                                                </button>

                                                                <!-- Delete Button -->
                                                                <form action="{{ route('admin.rooms.destroy', [$kost, $room]) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus kamar {{ $room->code }}? Aksi ini tidak dapat dibatalkan.');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="text-error-600 hover:text-error-900" title="Hapus" aria-label="Hapus kamar {{ $room->code }}">
                                                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                        </svg>
                                                                    </button>
                                                                </form>
                                                            </div>

                                                            <!-- Edit Mode Actions -->
                                                            <div x-show="editing" style="display: none;" class="flex items-center justify-end space-x-2">
                                                                <!-- Hidden form for edit -->
                                                                <form id="edit-room-{{ $room->id }}" action="{{ route('admin.rooms.update', [$kost, $room]) }}" method="POST" style="display: none;">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <input type="hidden" name="room_type_id" value="{{ $room->room_type_id }}">
                                                                    <input type="hidden" name="status" value="{{ $room->status }}">
                                                                </form>
                                                                
                                                                <button 
                                                                    type="submit"
                                                                    form="edit-room-{{ $room->id }}"
                                                                    class="text-success-600 hover:text-success-900"
                                                                    title="Simpan"
                                                                >
                                                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                                    </svg>
                                                                </button>
                                                                <button 
                                                                    @click="editing = false"
                                                                    class="text-gray-600 hover:text-gray-900"
                                                                    title="Batal"
                                                                >
                                                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-base-layout>
