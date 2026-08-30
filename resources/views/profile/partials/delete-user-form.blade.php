<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-text-strong dark:text-text-strong-dark">
            Hapus Akun
        </h2>

        <p class="mt-1 text-sm text-text dark:text-text-muted-dark">
            Semua data akun Anda akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Hapus Akun</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6" x-data="{ 
            confirmed: false, 
            emailConfirmation: '', 
            userEmail: '{{ $user->email }}',
            get emailMatches() {
                return this.emailConfirmation.toLowerCase() === this.userEmail.toLowerCase();
            },
            get canSubmit() {
                return this.confirmed && this.emailMatches;
            }
        }">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-text-strong dark:text-text-strong-dark">
                Apakah Anda yakin ingin menghapus akun?
            </h2>

            <div class="mt-4 rounded-md bg-error/10 border border-error/20 p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-error-700 dark:text-error-300 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.72-1.36 3.485 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-error-700 dark:text-error-300">
                            Tindakan ini tidak dapat dibatalkan
                        </p>
                        <p class="mt-1 text-sm text-error-700 dark:text-error-300">
                            Semua data akun Anda akan dihapus permanen, termasuk rental dan review yang terkait.
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-6 space-y-4">
                {{-- Confirmation Checkbox --}}
                <label class="flex items-start space-x-3 cursor-pointer group">
                    <input 
                        type="checkbox" 
                        x-model="confirmed"
                        name="confirmation_checkbox"
                        class="mt-1 w-5 h-5 text-error-600 border-border dark:border-border-dark rounded focus:ring-2 focus:ring-error-500 transition-all">
                    <span class="text-sm text-text dark:text-text-dark">
                        Saya memahami bahwa tindakan ini <strong>tidak dapat dibatalkan</strong> dan semua data saya akan dihapus permanen.
                    </span>
                </label>

                {{-- Email Confirmation --}}
                <div>
                    <x-input-label for="email_confirmation" value="Konfirmasi Email" />
                    <x-text-input
                        id="email_confirmation"
                        name="email_confirmation"
                        type="email"
                        class="mt-1 block w-full"
                        placeholder="Ketik email Anda untuk konfirmasi"
                        x-model="emailConfirmation"
                        aria-describedby="email-help"
                    />
                    <p id="email-help" class="mt-1 text-xs text-text-muted dark:text-text-muted-dark">
                        Ketik email Anda: <strong>{{ $user->email }}</strong>
                    </p>
                    <p class="mt-1 text-xs text-error-700 dark:text-error-300" x-show="emailConfirmation && !emailMatches" x-cloak>
                        Email tidak cocok
                    </p>
                </div>

                {{-- Password --}}
                <div>
                    <x-input-label for="password" value="Password" />
                    <x-text-input
                        id="password"
                        name="password"
                        type="password"
                        class="mt-1 block w-full"
                        placeholder="Masukkan password Anda"
                    />
                    <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Batal
                </x-secondary-button>

                <x-danger-button 
                    :disabled="true"
                    x-bind:disabled="!canSubmit"
                    x-bind:class="{ 'opacity-50 cursor-not-allowed': !canSubmit }">
                    Hapus Akun
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
