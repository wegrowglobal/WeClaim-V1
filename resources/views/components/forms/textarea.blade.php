@props(['name', 'label', 'value' => ''])

<div class="relative">
    <textarea
        class="form-input text-wgg-black-950 @error($name) is-invalid @enderror w-full px-4 py-2 pt-6 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-wgg-black-950 focus:border-wgg-border transition duration-150 ease-in-out"
        name="{{ $name }}"
        id="{{ $name }}"
        cols="30"
        rows="5"
        placeholder=" "
    >{{ $value }}</textarea>

    <label
        for="{{ $name }}"
        class="absolute text-sm text-wgg-black-400 font-normal duration-300 transform -translate-y-3 scale-75 top-4 z-10 origin-[0] left-4 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-3"
    >
        {{ $label }}
    </label>
</div>
<x-forms.error :name="$name" />