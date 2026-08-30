{{-- Site Footer Component --}}
{{-- Usage: <x-footer /> --}}
{{-- Specification: DESIGN.md §3.35 (line 3215+) --}}

<footer class="bg-gray-900 text-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
      {{-- Brand Column --}}
      <div class="md:col-span-1">
        <div class="flex items-center gap-2 mb-4">
          <span class="text-xl font-bold">SewaKost</span>
        </div>
        <p class="text-sm text-gray-400 leading-relaxed">
          Platform marketplace kost terpercaya dengan sistem booking dan pembayaran yang aman.
        </p>
      </div>
    
    {{-- Bottom Bar --}}
    <div class="mt-12 pt-8 border-t border-gray-800">
      <div class="flex flex-col md:flex-row justify-between items-center gap-4">
        <p class="text-sm text-gray-400">
          © {{ date('Y') }} SewaKost. All rights reserved.
        </p>
        <div class="flex gap-6">
        </div>
      </div>
    </div>
  </div>
</footer>
