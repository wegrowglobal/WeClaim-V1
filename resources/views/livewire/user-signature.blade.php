<div class="w-full">
    <div class="space-y-4">
        <!-- Current Signature Display -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Current Signature</label>
            @if($signatureImage)
                <div class="relative inline-block">
                    <div class="bg-white rounded-lg border border-gray-200 p-4 w-48 h-32 flex items-center justify-center">
                        <img src="{{ asset('storage/' . $signatureImage) }}" 
                             alt="Current Signature" 
                             class="max-h-full max-w-full object-contain">
                        <button wire:click="deleteSignature" 
                                class="absolute -top-2 -right-2 bg-white text-red-500 rounded-full p-1.5 shadow-sm border border-gray-200 
                                       opacity-0 hover:opacity-100 transition-opacity duration-200 hover:bg-red-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            @else
                <div class="text-sm text-gray-500 italic">
                    No signature uploaded yet
                </div>
            @endif
        </div>

        <!-- Upload Section -->
        <div class="mt-6">
            <div class="relative">
                <label class="block w-full rounded-lg border-2 border-dashed border-gray-200 hover:border-indigo-500 transition-colors duration-200 bg-white">
                    <input type="file" 
                           wire:model="signature" 
                           accept="image/*" 
                           class="sr-only"
                           wire:loading.attr="disabled">
                    <div class="p-6 text-center">
                        <div wire:loading.remove wire:target="signature">
                            <div class="mx-auto h-12 w-12 text-gray-400 mb-4">
                                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                                          d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                            </div>
                            <div class="text-sm text-gray-600">
                                Click to upload or drag and drop
                            </div>
                            <div class="mt-1 text-xs text-gray-500">
                                PNG, JPG up to 1MB
                            </div>
                        </div>
                        <div wire:loading wire:target="signature" class="text-center">
                            <div class="mx-auto h-12 w-12 text-indigo-500 mb-4">
                                <svg class="animate-spin h-12 w-12" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <div class="text-sm text-indigo-600">
                                Uploading signature...
                            </div>
                        </div>
                    </div>
                </label>
            </div>
            @error('signature') 
                <div class="mt-2 text-sm text-red-600">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>
</div>
