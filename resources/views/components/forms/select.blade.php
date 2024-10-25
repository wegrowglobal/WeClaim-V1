@props(['name', 'label', 'options', 'selected' => '', 'required' => false])

<div class="relative">
    <select name="{{ $name }}" id="{{ $name }}" class="form-input text-wgg-black-950 @error($name) is-invalid @enderror w-full px-4 py-2 pt-6 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-wgg-black-950 focus:border-wgg-border transition duration-150 ease-in-out appearance-none bg-white" {{ $required ? 'required' : '' }}>
        <option value="">Select an Option</option>
        @foreach($options as $value => $optionLabel)
            <option value="{{ $value }}" {{ $selected == $value ? 'selected' : '' }}>{{ $optionLabel }}</option>
        @endforeach
    </select>
    <label for="{{ $name }}" class="absolute text-sm text-wgg-black-400 font-normal duration-300 transform -translate-y-3 scale-75 top-4 z-10 origin-[0] left-4 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-3">
        {{ $label }}
    </label>
    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
            <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
        </svg>
    </div>
</div>
@error($name)
    <span class="error-text">{{ $message }}</span>
@enderror
