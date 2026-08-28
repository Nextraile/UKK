<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Beri Penilaian
            </h2>
            <a href="{{ route('rentals.show', $rental) }}" class="text-sm text-primary-600 hover:text-primary-700">
                ← Kembali ke Detail Rental
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                        Bagikan Pengalaman Anda
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Ulasan Anda akan membantu calon penyewa lain dalam memilih kost yang tepat.
                    </p>
                </div>

                @include('tenant.reviews._form', [
                    'action' => route('rentals.reviews.store', $rental),
                    'method' => null
                ])
            </div>
        </div>
    </div>
</x-app-layout>
