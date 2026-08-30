@props(['status', 'type', 'size' => 'sm'])

@php
/**
 * Status Badge Component
 * 
 * Displays a colored badge for entity statuses (Rental, Kost, Document, Payment)
 * 
 * @param string $status - Status value (e.g., 'pending_payment', 'approved')
 * @param string $type - Entity type ('rental', 'kost', 'document', 'payment')
 * @param string $size - Badge size ('sm', 'md', 'lg') - default: 'sm'
 */

// Status mappings per entity type
$statusMappings = [
    'rental' => [
        'pending' => ['label' => 'Menunggu Pembayaran', 'color' => 'warning'],
        'pending_payment' => ['label' => 'Menunggu Pembayaran', 'color' => 'warning'],
        'paid' => ['label' => 'Sudah Bayar', 'color' => 'info'],
        'confirmed' => ['label' => 'Dikonfirmasi', 'color' => 'success'],
        'active' => ['label' => 'Aktif', 'color' => 'success'],
        'completed' => ['label' => 'Selesai', 'color' => 'gray'],
        'cancelled' => ['label' => 'Dibatalkan', 'color' => 'error'],
    ],
    'kost' => [
        'draft' => ['label' => 'Draft', 'color' => 'gray'],
        'pending_review' => ['label' => 'Menunggu Review', 'color' => 'warning'],
        'approved' => ['label' => 'Disetujui', 'color' => 'success'],
        'rejected' => ['label' => 'Ditolak', 'color' => 'error'],
        'active' => ['label' => 'Aktif', 'color' => 'info'],
    ],
    'document' => [
        'pending' => ['label' => 'Menunggu Verifikasi', 'color' => 'warning'],
        'verified' => ['label' => 'Terverifikasi', 'color' => 'success'],
        'approved' => ['label' => 'Terverifikasi', 'color' => 'success'],
        'rejected' => ['label' => 'Ditolak', 'color' => 'error'],
    ],
    'payment' => [
        'pending' => ['label' => 'Menunggu Verifikasi', 'color' => 'warning'],
        'verified' => ['label' => 'Terverifikasi', 'color' => 'success'],
        'success' => ['label' => 'Terverifikasi', 'color' => 'success'],
        'rejected' => ['label' => 'Ditolak', 'color' => 'error'],
        'failed' => ['label' => 'Ditolak', 'color' => 'error'],
    ],
];

// Get status config
$config = $statusMappings[$type][$status] ?? ['label' => ucfirst($status), 'color' => 'gray'];
$label = $config['label'];
$color = $config['color'];

// Color mapping (DESIGN.md §3.4)
$colorClasses = [
    'warning' => 'bg-warning/10 text-warning-700 dark:bg-warning-900/20 dark:text-warning-200',
    'success' => 'bg-success/10 text-success-700 dark:bg-success-900/20 dark:text-success-200',
    'error' => 'bg-error/10 text-error-700 dark:bg-error-900/20 dark:text-error-200',
    'info' => 'bg-info/10 text-info-700 dark:bg-info-900/20 dark:text-info-200',
    'gray' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
];

$colorClass = $colorClasses[$color] ?? $colorClasses['gray'];

// Size variants (DESIGN.md §3.4)
$sizeClasses = [
    'sm' => 'text-xs py-0.5 px-2.5',
    'md' => 'text-sm py-1 px-3',
    'lg' => 'text-base py-1.5 px-4',
];

$sizeClass = $sizeClasses[$size] ?? $sizeClasses['sm'];

// Icon SVG per color (optional - can be extended)
$icons = [
    'warning' => '<svg class="w-3 h-3 animate-pulse" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>',
    'success' => '<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>',
    'error' => '<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>',
    'info' => '<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>',
    'gray' => '<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>',
];

$icon = $icons[$color] ?? '';
@endphp

<span 
    {{ $attributes->merge(['class' => "inline-flex items-center gap-1 rounded-full font-semibold {$colorClass} {$sizeClass}"]) }}
    role="status"
    aria-label="{{ $label }}"
>
    @if($icon)
        {!! $icon !!}
    @endif
    <span>{{ $label }}</span>
</span>
