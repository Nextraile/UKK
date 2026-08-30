@props([
    'latitude' => null,
    'longitude' => null,
    'name' => '',
    'address' => '',
    'height' => 'h-64',
])

<div x-data="{
    lat: @js($latitude),
    lng: @js($longitude),
    name: @js($name),
    hasCoordinates: @js($latitude && $longitude),
    init() {
      if (!this.hasCoordinates) return;
      
      this.$nextTick(() => {
        if (typeof L === 'undefined') {
          console.error('Leaflet not loaded. Include Leaflet CSS/JS in layout.');
          return;
        }
        
        const map = L.map(this.$refs.mapEl).setView([this.lat, this.lng], 15);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 19,
          attribution: '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a>'
        }).addTo(map);
        
        L.marker([this.lat, this.lng]).addTo(map)
          .bindPopup('<strong>' + this.name + '</strong><br>' + this.$refs.address.textContent.trim())
          .openPopup();
      });
    }
  }" 
  x-cloak
  {{ $attributes->merge(['class' => '']) }}>
  
  <!-- Map Container (only rendered if coordinates exist) -->
  <div x-show="hasCoordinates" 
    x-ref="mapEl" 
    class="{{ $height }} w-full rounded-lg overflow-hidden z-0 border border-gray-200 dark:border-border-dark" 
    aria-hidden="true"></div>
  
  <!-- Fallback: Address Text + Google Maps Link -->
  <div x-ref="address" 
    :class="hasCoordinates ? 'mt-2' : ''"
    class="text-sm text-gray-600 dark:text-text-dark">
    <span class="font-medium">Alamat:</span> {{ $address }}
    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($address) }}"
      target="_blank" 
      rel="noopener noreferrer"
      class="ml-1 font-medium text-primary-600 dark:text-primary-500 hover:text-primary-700 dark:hover:text-primary-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 rounded transition-colors">
      Buka di Google Maps
      <svg class="inline w-3 h-3 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
      </svg>
    </a>
  </div>
  
  <!-- No coordinates fallback message -->
  <p x-show="!hasCoordinates" class="text-sm text-gray-500 dark:text-text-muted-dark italic">
    Koordinat lokasi belum tersedia untuk kost ini.
  </p>
</div>
