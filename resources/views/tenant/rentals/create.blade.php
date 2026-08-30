<x-app-layout>
    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <x-page-header 
                title="Buat Booking Rental"
                :breadcrumbs="[
                    ['label' => 'Dashboard', 'url' => route('dashboard')],
                    ['label' => 'Rental', 'url' => route('rentals.index')],
                    ['label' => 'Buat Booking'],
                ]"
            />
            @if($rooms->isEmpty())
                <!-- Empty State: No Available Rooms -->
                <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-12 text-center text-gray-900 dark:text-gray-100">
                        <svg class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <h3 class="mt-4 text-lg font-semibold">Tidak Ada Kamar Tersedia</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Maaf, saat ini tidak ada kamar yang tersedia untuk dibooking.
                        </p>
                        <div class="mt-6">
                            <a href="{{ route('marketplace.index') }}" 
                               class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-white hover:bg-primary-700 transition-colors">
                                Kembali ke Marketplace
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form method="POST" action="{{ route('rentals.store') }}" 
                              x-data="{
                                  price: 0,
                                  duration: 1,
                                  deposit: 0,
                                  selectedRoomId: @js(old('room_id')),
                                  availableSchemes: [],
                                  roomSchemes: @js($roomSchemes),
                                  get total() {
                                      return (this.price * this.duration) + this.deposit;
                                  },
                                  filterSchemes() {
                                      if (!this.selectedRoomId) {
                                          this.availableSchemes = [];
                                          this.price = 0;
                                          this.deposit = 0;
                                          return;
                                      }
                                      this.availableSchemes = this.roomSchemes[this.selectedRoomId] || [];
                                  }
                              }"
                              x-init="filterSchemes()">
                            @csrf

                            <!-- Room Selection -->
                            <div class="mb-6">
                                <x-input-label for="room_id" value="Pilih Kamar" />
                                <select id="room_id" name="room_id" required
                                        x-model.number="selectedRoomId"
                                        @change="filterSchemes(); price=0; deposit=0"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                    <option value="">-- Pilih Kamar --</option>
                                    @foreach($rooms as $room)
                                        <option value="{{ $room->id }}">
                                            {{ $room->roomType->name }} - {{ $room->code }} ({{ $room->free_slots }} slot tersedia)
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('room_id')" class="mt-2" />
                            </div>

                            <!-- Price Scheme Selection -->
                            <div class="mb-6">
                                <x-input-label for="price_scheme_id" value="Paket Harga" />
                                <select id="price_scheme_id" name="price_scheme_id" required
                                        x-on:change="price = parseFloat($event.target.selectedOptions[0].dataset.price || 0); deposit = parseFloat($event.target.selectedOptions[0].dataset.deposit || 0)"
                                        :disabled="!selectedRoomId || availableSchemes.length === 0"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <option value="">-- Pilih Paket --</option>
                                    <template x-for="scheme in availableSchemes" :key="scheme.id">
                                        <option :value="scheme.id" 
                                                :data-price="scheme.price"
                                                :data-deposit="scheme.deposit"
                                                :selected="scheme.id === @json(old('price_scheme_id'))"
                                                x-text="scheme.name"></option>
                                    </template>
                                </select>
                                <x-input-error :messages="$errors->get('price_scheme_id')" class="mt-2" />
                                <p x-show="selectedRoomId && availableSchemes.length === 0" 
                                   x-cloak
                                   class="mt-2 text-sm text-error-600 dark:text-error-400">
                                    Tidak ada paket harga tersedia untuk kamar ini.
                                </p>
                            </div>

                            <!-- Start Date -->
                            <div class="mb-6">
                                <x-input-label for="start_date" value="Tanggal Mulai (min {{ now()->addDays(4)->format('d M Y') }})" />
                                <x-text-input id="start_date" name="start_date" type="date" 
                                              class="mt-1 block w-full"
                                              min="{{ now()->addDays(4)->format('Y-m-d') }}"
                                              max="{{ now()->addDays(30)->format('Y-m-d') }}"
                                              :value="old('start_date')" required />
                                <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                            </div>

                            <!-- Duration -->
                            <div class="mb-6">
                                <x-input-label for="duration" value="Durasi" />
                                <x-text-input id="duration" name="duration" type="number" 
                                              class="mt-1 block w-full"
                                              min="1" max="24"
                                              x-model.number="duration"
                                              :value="old('duration', 1)" required />
                                <x-input-error :messages="$errors->get('duration')" class="mt-2" />
                            </div>

                            <!-- Total Display -->
                            <div class="mb-6 rounded-lg bg-gray-100 p-4 dark:bg-gray-800">
                                <h3 class="mb-3 text-lg font-semibold">Ringkasan Biaya</h3>
                                <div class="mb-2 flex justify-between">
                                    <span>Harga per unit:</span>
                                    <span x-text="'Rp ' + price.toLocaleString('id-ID')"></span>
                                </div>
                                <div class="mb-2 flex justify-between">
                                    <span>Durasi:</span>
                                    <span x-text="duration + ' unit'"></span>
                                </div>
                                <div class="mb-2 flex justify-between">
                                    <span>Deposit:</span>
                                    <span x-text="'Rp ' + deposit.toLocaleString('id-ID')"></span>
                                </div>
                                <div class="flex justify-between border-t border-gray-300 pt-2 text-xl font-bold dark:border-gray-700">
                                    <span>Total:</span>
                                    <span x-text="'Rp ' + total.toLocaleString('id-ID')"></span>
                                </div>
                            </div>

                            <div class="flex items-center justify-end">
                                <x-primary-button>
                                    Buat Booking
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
