@props(['name'])

@error($name)
    <p {{ $attributes->merge(['class' => 'text-red-500 text-xs mt-1']) }} role="alert">
        {{ $message }}
    </p>
@enderror
