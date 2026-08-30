<x-app-layout>
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <x-page-header 
                title="Persyaratan Dokumen - {{ $kost->name }}"
                subtitle="Atur dokumen apa saja yang harus disiapkan penyewa saat menyewa kost ini"
                :breadcrumbs="[
                    ['label' => 'Dashboard', 'url' => route('dashboard')],
                    ['label' => 'Kost', 'url' => route('admin.kosts.index')],
                    ['label' => $kost->name, 'url' => route('admin.kosts.show', $kost)],
                    ['label' => 'Persyaratan Dokumen'],
                ]"
            >
                <x-slot:actions>
                    <a href="{{ route('admin.kosts.show', $kost) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
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
                    <!-- Add New Requirement Form -->
                    <div class="mb-8 rounded-lg border border-gray-200 bg-gray-50 p-6">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">Tambah Persyaratan Dokumen</h3>
                        <form method="POST" action="{{ route('admin.kosts.document-requirements.store', $kost) }}" class="space-y-4">
                            @csrf

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <!-- Document Type -->
                                <div>
                                    <label for="document_type" class="block text-sm font-medium text-gray-700">
                                        Jenis Dokumen <span class="text-error-500">*</span>
                                    </label>
                                    <select id="document_type" name="document_type" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('document_type') border-error-300 @enderror">
                                        <option value="">-- Pilih Jenis Dokumen --</option>
                                        @foreach($documentTypes as $key => $label)
                                            <option value="{{ $key }}" {{ old('document_type') === $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('document_type')
                                        <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Is Required -->
                                <div>
                                    <label for="is_required" class="block text-sm font-medium text-gray-700">
                                        Status <span class="text-error-500">*</span>
                                    </label>
                                    <select id="is_required" name="is_required" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('is_required') border-error-300 @enderror">
                                        <option value="1" {{ old('is_required') === '1' ? 'selected' : '' }}>Wajib</option>
                                        <option value="0" {{ old('is_required') === '0' ? 'selected' : '' }}>Opsional</option>
                                    </select>
                                    @error('is_required')
                                        <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Reason -->
                                <div>
                                    <label for="reason" class="block text-sm font-medium text-gray-700">
                                        Alasan/Keterangan
                                    </label>
                                    <input type="text" id="reason" name="reason" value="{{ old('reason') }}" maxlength="500"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('reason') border-error-300 @enderror"
                                        placeholder="Opsional">
                                    @error('reason')
                                        <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit"
                                    class="inline-flex items-center rounded-md border border-transparent bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                                    Tambah Persyaratan
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Existing Requirements List -->
                    <div>
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">Daftar Persyaratan Dokumen</h3>

                        @if($kost->documentRequirements->isEmpty())
                            <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-8 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada persyaratan dokumen</h3>
                                <p class="mt-1 text-sm text-gray-500">Tambahkan persyaratan dokumen menggunakan form di atas.</p>
                                <p class="mt-2 text-xs text-warning-600">
                                    <strong>Catatan:</strong> Minimal 1 dokumen wajib diperlukan untuk submit kost.
                                </p>
                            </div>
                        @else
                            <div class="overflow-hidden rounded-lg border border-gray-200">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Jenis Dokumen
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Status
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Alasan/Keterangan
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Aksi
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        @foreach($kost->documentRequirements as $requirement)
                                            <tr x-data="{ 
                                                editing: false, 
                                                documentType: '{{ $requirement->document_type }}',
                                                isRequired: {{ $requirement->is_required ? 'true' : 'false' }},
                                                reason: '{{ addslashes($requirement->reason ?? '') }}'
                                            }">
                                                <!-- View Mode -->
                                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900" x-show="!editing">
                                                    {{ $requirement->document_type_label }}
                                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500" x-show="!editing">
                                    @if($requirement->is_required)
                                        <span class="inline-flex rounded-full bg-error/10 px-2 text-xs font-semibold leading-5 text-error-700">
                                            Wajib
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-gray-100 px-2 text-xs font-semibold leading-5 text-gray-800">
                                            Opsional
                                        </span>
                                    @endif
                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-500" x-show="!editing">
                                                    {{ $requirement->reason ?? '-' }}
                                                </td>

                                                <!-- Edit Mode -->
                                                <td colspan="3" x-show="editing" class="px-6 py-4">
                                                    <form method="POST" action="{{ route('admin.kosts.document-requirements.update', [$kost, $requirement]) }}" class="space-y-3">
                                                        @csrf
                                                        @method('PATCH')

                                                        <div class="grid grid-cols-3 gap-4">
                                                            <div>
                                                                <label class="block text-xs font-medium text-gray-700">Jenis Dokumen</label>
                                                                <select name="document_type" x-model="documentType" required
                                                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                                                    @foreach($documentTypes as $key => $label)
                                                                        <option value="{{ $key }}">{{ $label }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                            <div>
                                                                <label class="block text-xs font-medium text-gray-700">Status</label>
                                                                <select name="is_required" x-model="isRequired" required
                                                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                                                    <option value="1">Wajib</option>
                                                                    <option value="0">Opsional</option>
                                                                </select>
                                                            </div>

                                                            <div>
                                                                <label class="block text-xs font-medium text-gray-700">Alasan</label>
                                                                <input type="text" name="reason" x-model="reason" maxlength="500"
                                                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                                                    placeholder="Opsional">
                                                            </div>
                                                        </div>

                                                        <div class="flex justify-end space-x-2">
                                                            <button type="button" @click="editing = false"
                                                                class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                                                                Batal
                                                            </button>
                                                            <button type="submit"
                                                                class="rounded-md border border-transparent bg-primary-600 px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-primary-700">
                                                                Simpan
                                                            </button>
                                                        </div>
                                                    </form>
                                                </td>

                                <!-- Actions -->
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium" x-show="!editing">
                                    <button @click="editing = true" type="button"
                                        class="text-primary-600 hover:text-primary-900">
                                        Edit
                                    </button>
                                    <span class="text-gray-300">|</span>
                                    <form method="POST" action="{{ route('admin.kosts.document-requirements.destroy', [$kost, $requirement]) }}" 
                                        class="inline"
                                        onsubmit="return confirm('Yakin ingin menghapus persyaratan dokumen ini?');">
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

                            <div class="mt-4 rounded-md bg-warning-light p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-warning-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-warning-700">Catatan Penting</h3>
                                        <div class="mt-2 text-sm text-warning-700">
                                            <ul class="list-disc space-y-1 pl-5">
                                                <li>Minimal 1 dokumen dengan status <strong>Wajib</strong> diperlukan sebelum kost dapat disubmit untuk review.</li>
                                                <li>Persyaratan dokumen hanya bisa diubah saat status kost adalah <strong>Draft</strong> atau <strong>Rejected</strong>.</li>
                                                <li>Setiap jenis dokumen hanya boleh ditambahkan sekali per kost.</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
