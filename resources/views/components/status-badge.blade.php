@php
    use App\Models\Claim;
    
    $colors = [
        Claim::STATUS_SUBMITTED => [
            'class' => 'bg-amber-50 text-amber-700',
            'icon' => '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        ],
        Claim::STATUS_APPROVED_ADMIN => [
            'class' => 'bg-indigo-50 text-indigo-700',
            'icon' => '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        ],
        Claim::STATUS_APPROVED_DATUK => [
            'class' => 'bg-blue-50 text-blue-700',
            'icon' => '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        ],
        Claim::STATUS_APPROVED_HR => [
            'class' => 'bg-purple-50 text-purple-700',
            'icon' => '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        ],
        Claim::STATUS_APPROVED_FINANCE => [
            'class' => 'bg-emerald-50 text-emerald-700',
            'icon' => '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        ],
        Claim::STATUS_REJECTED => [
            'class' => 'bg-red-50 text-red-700',
            'icon' => '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        ],
        Claim::STATUS_DONE => [
            'class' => 'bg-green-50 text-green-700',
            'icon' => '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
        ],
        Claim::STATUS_CANCELLED => [
            'class' => 'bg-gray-50 text-gray-700',
            'icon' => '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        ]
    ];

    $statusText = collect(explode('_', strtolower($status)))
        ->map(function($word) {
            return $word === 'hr' ? 'HR' : ucfirst($word);
        })
        ->join(' ');
@endphp

<span class="status-badge inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colors[$status]['class'] ?? 'bg-gray-50 text-gray-700' }}">
    {!! $colors[$status]['icon'] ?? '' !!}
    {{ $statusText }}
</span>