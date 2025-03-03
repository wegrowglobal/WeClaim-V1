<td 
    @class([
        'px-3 py-3 text-xs text-gray-600',
        $responsiveClass(),
        $alignClass(),
        $nowrap ? 'whitespace-nowrap' : '',
    ])
    {{ $attributes }}
>
    @if($truncate)
        <div @class([
            'truncate',
            $maxWidth ? "max-w-[$maxWidth]" : 'max-w-[100px] sm:max-w-none',
        ])>
            {{ $slot }}
        </div>
    @else
        {{ $slot }}
    @endif
</td>