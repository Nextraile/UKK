<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-text-strong dark:text-text-strong-dark">
            Ubah Password
        </h2>

        <p class="mt-1 text-sm text-text dark:text-text-muted-dark">
            Pastikan akun Anda menggunakan password yang kuat dan aman.
        </p>
    </header>

    @if ($errors->updatePassword->any())
        <div role="alert" aria-live="assertive" class="rounded-md bg-error/10 border border-error/20 p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-error-700 dark:text-error-300 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-error-700 dark:text-error-300">
                        Terdapat {{ $errors->updatePassword->count() }} kesalahan pada formulir
                    </h3>
                    <ul class="mt-2 text-sm text-error-700 dark:text-error-300 space-y-1">
                        @foreach ($errors->updatePassword->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div x-data="{ saving: false, ...passwordStrength() }">
        <form method="post" action="{{ route('password.update') }}" class="space-y-4" @submit="saving = true">
            @csrf
            @method('put')

            <div>
                <x-input-label for="update_password_current_password" :value="'Password Saat Ini'" />
                <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="update_password_password" :value="'Password Baru'" />
                <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" x-model="password" @input="checkStrength()" />
                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                
                {{-- Strength meter --}}
                <div class="mt-2" x-show="password.length > 0">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Kekuatan Password</span>
                        <span class="text-xs font-semibold" 
                              :class="{
                                'text-error-700 dark:text-error-400': strength === 'weak',
                                'text-warning-700 dark:text-warning-400': strength === 'fair',
                                'text-success-700 dark:text-success-400': strength === 'good' || strength === 'strong'
                              }"
                              x-text="strengthLabel"></span>
                    </div>
                    <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full transition-all duration-300"
                             :class="{
                               'bg-error-600 w-1/4': strength === 'weak',
                               'bg-warning-500 w-2/4': strength === 'fair',
                               'bg-success-500 w-3/4': strength === 'good',
                               'bg-success-600 w-full': strength === 'strong'
                             }"></div>
                    </div>
                </div>
                
                {{-- Requirements checklist --}}
                <ul class="mt-3 space-y-1 text-xs text-gray-600 dark:text-gray-400" x-show="password.length > 0">
                    <li :class="checks.length && 'text-success-600 dark:text-success-400'">
                        <svg class="w-3 h-3 inline mr-1" :class="checks.length ? 'text-success-600' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Minimal 8 karakter
                    </li>
                    <li :class="checks.uppercase && 'text-success-600 dark:text-success-400'">
                        <svg class="w-3 h-3 inline mr-1" :class="checks.uppercase ? 'text-success-600' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Huruf besar
                    </li>
                    <li :class="checks.number && 'text-success-600 dark:text-success-400'">
                        <svg class="w-3 h-3 inline mr-1" :class="checks.number ? 'text-success-600' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Angka
                    </li>
                    <li :class="checks.special && 'text-success-600 dark:text-success-400'">
                        <svg class="w-3 h-3 inline mr-1" :class="checks.special ? 'text-success-600' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Karakter spesial (!@#$%^&*)
                    </li>
                </ul>
            </div>

            <div>
                <x-input-label for="update_password_password_confirmation" :value="'Konfirmasi Password'" />
                <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="flex items-center gap-4">
                <button type="submit"
                        x-bind:disabled="saving"
                        class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150">
                    <span x-show="!saving">Simpan</span>
                    <span x-show="saving" class="inline-flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Menyimpan...
                    </span>
                </button>

                @if (session('status') === 'password-updated')
                    <p
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 2000)"
                        class="text-sm text-text dark:text-text-muted-dark"
                    >Tersimpan.</p>
                @endif
            </div>
        </form>
    </div>

    <script>
    function passwordStrength() {
      return {
        password: '',
        strength: '',
        strengthLabel: '',
        checks: {
          length: false,
          uppercase: false,
          number: false,
          special: false
        },
        checkStrength() {
          this.checks.length = this.password.length >= 8;
          this.checks.uppercase = /[A-Z]/.test(this.password);
          this.checks.number = /[0-9]/.test(this.password);
          this.checks.special = /[!@#$%^&*(),.?":{}|<>]/.test(this.password);
          
          const score = Object.values(this.checks).filter(v => v).length;
          
          if (score === 0 || this.password.length === 0) {
            this.strength = '';
            this.strengthLabel = '';
          } else if (score === 1) {
            this.strength = 'weak';
            this.strengthLabel = 'Lemah';
          } else if (score === 2) {
            this.strength = 'fair';
            this.strengthLabel = 'Cukup';
          } else if (score === 3) {
            this.strength = 'good';
            this.strengthLabel = 'Baik';
          } else {
            this.strength = 'strong';
            this.strengthLabel = 'Kuat';
          }
        }
      }
    }
    </script>
</section>
