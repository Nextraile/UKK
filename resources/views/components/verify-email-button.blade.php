{{-- Usage: <x-verify-email-button class="ml-2" /> --}}
@props(['size' => 'sm'])

@php
$sizeClasses = [
  'xs' => 'px-2 py-1 text-xs',
  'sm' => 'px-3 py-1.5 text-xs',
  'md' => 'px-4 py-2 text-sm',
];
@endphp

<a href="{{ route('verification.notice') }}"
   {{ $attributes->merge(['class' => 'inline-flex items-center font-semibold rounded-md bg-primary-600 text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors ' . $sizeClasses[$size]]) }}>
  Verifikasi Email
</a>
