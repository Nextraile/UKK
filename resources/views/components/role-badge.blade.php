@props(['role'])

@php
/**
 * Role Badge Component
 * 
 * Displays a colored badge for user roles with proper semantic colors
 * 
 * @param string $role - User role (user|admin|superadmin)
 */

$roleConfig = [
    'superadmin' => [
        'label' => 'Super Admin',
        'class' => 'bg-secondary-100 text-secondary-800'
    ],
    'admin' => [
        'label' => 'Admin',
        'class' => 'bg-primary-100 text-primary-800'
    ],
    'user' => [
        'label' => 'Tenant',
        'class' => 'bg-success-100 text-success-800'
    ],
];

$config = $roleConfig[$role] ?? $roleConfig['user'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {$config['class']}"]) }}>
    {{ $config['label'] }}
</span>
