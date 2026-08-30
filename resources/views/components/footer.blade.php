{{-- Site Footer Component --}}
{{-- Usage: <x-footer /> --}}
{{-- Specification: DESIGN.md §3.35 (line 3215+) --}}

<footer class="bg-gray-900 text-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex flex-col items-center text-center">
      
      {{-- Copyright --}}
      <div class="mt-8 pt-8 border-t border-gray-800 w-full">
        <p class="text-sm text-gray-400">
          © {{ date('Y') }} SewaKost. All rights reserved.
        </p>
      </div>
    </div>
  </div>
</footer>
