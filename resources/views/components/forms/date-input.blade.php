@props(['name', 'label', 'value' => '', 'required' => false])

<div class="relative">
    <input 
        value="{{ $value }}" 
        class="form-input text-wgg-black-950 @error($name) is-invalid @enderror w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-wgg-black-950 focus:border-wgg-border transition duration-150 ease-in-out peer" 
        type="date" 
        name="{{ $name }}" 
        id="{{ $name }}" 
        placeholder=" " 
        {{ $required ? 'required' : '' }}
        onfocus="(this.type='date')" 
        onblur="if(!this.value)this.type='text'"
    >
    <label for="{{ $name }}" class="absolute left-2 top-2 text-xs text-wgg-black-400 font-normal transition-all duration-300 transform -translate-y-4 scale-75 z-10 origin-[0] bg-white px-2 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:-translate-y-4 peer-focus:scale-75">{{ $label }}</label>
</div>
<x-forms.error :name="$name" />