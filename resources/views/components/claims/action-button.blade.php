<button {{ $attributes->merge([
    'type' => 'button', 
    'class' => 'inline-flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2'
]) }}>
    {{ $slot }}
</button>
