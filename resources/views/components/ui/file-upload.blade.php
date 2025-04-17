@props([
    'id',
    'name',
    'label' => '',
    'description' => '',
    'required' => false,
    'accept' => '.pdf,.jpg,.jpeg,.png', // Default accepted types
    'updatePreviewJs' => '', // JS function call for preview update
    'xRequired' => null, // Alpine.js variable name for conditional requirement
])

<div>
    <label class="block text-sm font-medium leading-6 text-gray-900">
        {{ $label }} 
        @if($required && !$xRequired)
            <span class="text-red-600">*</span>
        @endif
        {{-- Add asterisk dynamically if xRequired is set --}}
        @if($xRequired)
            <span class="text-red-600" x-show="{{ $xRequired }}">*</span>
        @endif
    </label>
    @if($description)
        <p class="text-sm text-gray-500 mb-2">{{ $description }}</p>
    @endif
    <div class="document-upload-area mt-1" id="{{ $id }}-upload-area">
        <input 
            type="file" 
            id="{{ $id }}" 
            name="{{ $name }}" 
            class="hidden" 
            accept="{{ $accept }}" 
            {{ $required && !$xRequired ? 'required' : '' }} 
            @if($xRequired) x-bind:required="{{ $xRequired }}" @endif
            @if($updatePreviewJs) onchange="{{ $updatePreviewJs }}" @endif
        />
        <label for="{{ $id }}"
            class="relative flex cursor-pointer items-center justify-center rounded-md border-2 border-dashed border-gray-300 bg-white p-4 text-center hover:border-gray-400 focus-within:outline-none focus-within:ring-2 focus-within:ring-black focus-within:ring-offset-2">
             <svg class="h-8 w-8 text-gray-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z" clip-rule="evenodd" />
            </svg>
            <span class="ml-3 text-sm font-semibold text-gray-900">Upload {{ strtolower($label) }}</span>
        </label>
    </div>
    {{-- Preview Area --}}
    <div class="mt-2 hidden" id="{{ $id }}-preview">
        <div class="flex items-center justify-between rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm">
            <div class="flex items-center space-x-2 overflow-hidden">
                <svg class="h-5 w-5 flex-shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                     <path fill-rule="evenodd" d="M15.621 4.379a3 3 0 00-4.242 0l-7 7a3 3 0 004.241 4.243h.001l.497-.5a.75.75 0 011.064 1.057l-.498.501-.002.002a4.5 4.5 0 01-6.364-6.364l7-7a4.5 4.5 0 016.368 6.36l-3.455 3.553A2.625 2.625 0 119.53 9.53l3.45-3.55a.75.75 0 011.064 1.06l-3.45 3.55a1.125 1.125 0 001.587 1.595l3.454-3.553a3 3 0 000-4.242z" clip-rule="evenodd" />
                </svg>
                <span class="truncate text-gray-700" id="{{ $id }}-filename">No file selected</span>
            </div>
            <button type="button" 
                onclick="window.claimDocument.removeFile('{{ $id }}')"
                class="ml-4 flex-shrink-0 rounded-md p-1 text-gray-400 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-red-500">
                 <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                     <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                 </svg>
            </button>
        </div>
    </div>
</div> 