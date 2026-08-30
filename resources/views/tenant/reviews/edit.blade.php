<x-app-layout>
    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <x-page-header 
                title="Edit Review untuk {{ $rental->room->roomType->kost->name }}"
                :breadcrumbs="[
                    ['label' => 'Rental', 'url' => route('rentals.index')],
                    ['label' => 'Detail Rental', 'url' => route('rentals.show', $rental)],
                    ['label' => 'Edit Review'],
                ]"
            >
                <x-slot:actions>
                    <a href="{{ route('rentals.show', $rental) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
                        ← Kembali
                    </a>
                </x-slot:actions>
            </x-page-header>
            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                        Edit Ulasan Anda
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Perbarui penilaian Anda untuk rental ini.
                    </p>
                </div>

                @include('tenant.reviews._form', [
                    'action' => route('rentals.reviews.update', $rental),
                    'method' => 'PATCH'
                ])
            </div>
        </div>
    </div>
</x-app-layout>
