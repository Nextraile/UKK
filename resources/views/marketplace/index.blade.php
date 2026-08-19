<x-guest-layout>
    <div class="py-8 text-center">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Jelajahi Kost</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Temukan kost impian Anda di SewaKost.</p>
    </div>

    @if ($kosts->isNotEmpty())
        <div class="space-y-4">
            @foreach ($kosts as $kost)
                <article class="rounded-lg border border-gray-200 p-4">
                    <h2 class="font-semibold text-gray-900 dark:text-gray-100">{{ $kost->name }}</h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $kost->address }}</p>
                    <p class="mt-2 text-lg font-bold text-indigo-600">
                        Rp {{ number_format($kost->price, 0, ',', '.') }}<span class="text-sm font-normal text-gray-500">/bulan</span>
                    </p>
                </article>
            @endforeach
        </div>
    @else
        <div class="flex flex-col items-center justify-center px-4 py-12 text-center">
            <!-- Icon -->
            <svg class="w-16 h-16 mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>

            <!-- Message -->
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Belum ada kost yang tersedia saat ini.</h3>
            <p class="mt-1 max-w-sm text-sm text-gray-600 dark:text-gray-400">
                Kost yang terverifikasi akan tampil di sini. Silakan kembali lagi nanti.
            </p>

            <!-- CTA -->
            <a
                href="/"
                class="mt-6 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-6 py-3 font-semibold text-white transition-all hover:bg-indigo-700"
            >
                Kembali ke Beranda
            </a>
        </div>
    @endif
</x-guest-layout>