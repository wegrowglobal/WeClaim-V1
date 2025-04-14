@php
    use App\Models\Claim\Claim;

    $colors = [
        Claim::STATUS_SUBMITTED => [
            'class' => 'bg-amber-50 text-amber-600 ring-1 ring-amber-500/10',
            'icon' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        ],
        Claim::STATUS_APPROVED_ADMIN => [
            'class' => 'bg-indigo-50 text-indigo-600 ring-1 ring-indigo-500/10',
            'icon' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        ],
        Claim::STATUS_APPROVED_MANAGER => [
            'class' => 'bg-teal-50 text-teal-600 ring-1 ring-teal-500/10',
            'icon' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        ],
        Claim::STATUS_APPROVED_DATUK => [
            'class' => 'bg-blue-50 text-blue-600 ring-1 ring-blue-500/10',
            'icon' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        ],
        Claim::STATUS_APPROVED_HR => [
            'class' => 'bg-purple-50 text-purple-600 ring-1 ring-purple-500/10',
            'icon' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        ],
        Claim::STATUS_APPROVED_FINANCE => [
            'class' => 'bg-emerald-50 text-emerald-600 ring-1 ring-emerald-500/10',
            'icon' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        ],
        Claim::STATUS_REJECTED => [
            'class' => 'bg-red-50 text-red-600 ring-1 ring-red-500/10',
            'icon' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        ],
        Claim::STATUS_DONE => [
            'class' => 'bg-green-50 text-green-600 ring-1 ring-green-500/10',
            'icon' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/></svg>',
        ],
        Claim::STATUS_CANCELLED => [
            'class' => 'bg-gray-50 text-gray-600 ring-1 ring-gray-500/10',
            'icon' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/></svg>',
        ],
        Claim::STATUS_PENDING_DATUK => [
            'class' => 'bg-indigo-50 text-indigo-600 ring-1 ring-indigo-500/10',
            'icon' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        ],
    ];

    if ($status === Claim::STATUS_APPROVED_ADMIN) {
        $statusText = 'Approved Admin';
    } elseif ($status === Claim::STATUS_APPROVED_FINANCE) {
        $statusText = 'Approved Finance';
    } elseif ($status === Claim::STATUS_APPROVED_HR) {
        $statusText = 'Approved HR';
    } elseif ($status === Claim::STATUS_APPROVED_DATUK) {
        $statusText = 'Approved Datuk';
    } elseif ($status === Claim::STATUS_APPROVED_MANAGER) {
        $statusText = 'Approved Manager';
    } else {
        $statusText = ucwords(str_replace('_', ' ', strtolower($status)));
        $statusText = str_replace('Hr', 'HR', $statusText);
    }
@endphp

<span data-status="{{ $status }}" class="status-badge inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium {{ $colors[$status]['class'] ?? '' }} {{ $attributes->get('class') }}">
    {!! $colors[$status]['icon'] ?? '' !!}
    <span>{{ $statusText }}</span>
</span>
