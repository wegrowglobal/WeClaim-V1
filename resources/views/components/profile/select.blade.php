@props(['name', 'label', 'options', 'selected' => '', 'required' => false])

<div class="relative">
    <select id="{{ $name }}" name="{{ $name }}" class="form-input text-wgg-black-950 w-full px-4 py-2 pt-6 bg-wgg-white border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-wgg-black-950 focus:border-wgg-border transition duration-150 ease-in-out" {{ $required ? 'required' : '' }}>
        <option value="">Select a {{ strtolower($label) }}</option>
        @foreach($options as $value => $optionLabel)
            <option value="{{ $value }}" {{ $selected == $value ? 'selected' : '' }}>{{ $optionLabel }}</option>
        @endforeach
    </select>
    <label for="{{ $name }}" class="absolute text-sm text-wgg-black-400 font-normal duration-300 transform -translate-y-3 scale-75 top-4 z-10 origin-[0] left-4 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-3">{{ $label }}</label>
</div>