<div>
    <!-- Knowing is not enough; we must apply. Being willing is not enough; we must do. - Leonardo da Vinci -->
</div>

<th 
    @class([
        'px-3 py-3 text-left text-xs font-medium text-gray-600',
        $responsiveClass(),
        $width ? "w-[$width]" : '',
    ])
    scope="col"
>
    @if($sortable && isset($attributes['wire:click']))
        <div {{ $attributes->merge(['class' => 'flex cursor-pointer items-center gap-1']) }}>
            {{ $label }}
            <span class="ml-1">{!! $sortIcon() !!}</span>
        </div>
    @else
        <div class="flex items-center gap-1">
            {{ $label }}
            {{ $slot }}
        </div>
    @endif
</th>