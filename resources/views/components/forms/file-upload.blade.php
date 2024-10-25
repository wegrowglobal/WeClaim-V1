@props(['name', 'label', 'accept'])

<div class="col-span-1 flex flex-col justify-center items-center py-4 w-full border border-dotted border-wgg-border rounded-lg">
    <input class="hidden @error($name) is-invalid @enderror" type="file" name="{{ $name }}" id="{{ $name }}" accept="{{ $accept }}">
    <label for="{{ $name }}" class="text-xs text-wgg-black-400 font-normal">
        <span id="{{ $name }}_label" class="cursor-pointer">{{ $label }}</span>
    </label>
    @error($name)
        <span class="error-text">{{ $message }}</span>
    @enderror
    <div id="{{ $name }}_progress_container" class="hidden mt-2">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-wgg-black-950"></div>
    </div>
</div>