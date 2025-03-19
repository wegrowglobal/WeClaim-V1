<div class="w-full">
    <div class="space-y-4">
        <!-- Current Signature Status -->
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        @if($signatureImage)
                            <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                                <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        @else
                            <div class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center">
                                <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                            </div>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-900">Digital Signature</h3>
                        @if($signatureImage)
                            <div class="mt-1 text-xs text-gray-500">
                                <span class="font-medium">File:</span> {{ basename($signatureImage) }}
                                @php
                                    $filePath = storage_path('app/public/' . $signatureImage);
                                    $uploadDate = file_exists($filePath) ? \Carbon\Carbon::createFromTimestamp(filemtime($filePath))->format('M d, Y h:ia') : null;
                                @endphp
                                @if($uploadDate)
                                    <br>
                                    <span class="font-medium">Uploaded:</span> {{ $uploadDate }}
                                @endif
                            </div>
                        @else
                            <p class="mt-1 text-xs text-gray-500">No signature uploaded yet</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    @if($signatureImage)
                        <!-- Delete Button -->
                        <button wire:click="deleteSignature" 
                                class="inline-flex items-center px-2.5 py-1.5 border border-red-100 text-xs font-medium rounded text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-150">
                            <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Upload Section -->
        <div class="mt-4">
            <label class="block w-full rounded-lg border-2 border-dashed {{ $signatureImage ? 'border-gray-200 bg-gray-50 cursor-not-allowed' : 'border-gray-200 hover:border-indigo-500 transition-colors duration-200 bg-white cursor-pointer' }}">
                <input type="file" 
                       wire:model="signature" 
                       accept="image/*" 
                       class="sr-only"
                       {{ $signatureImage ? 'disabled' : '' }}
                       wire:loading.attr="disabled">
                <div class="p-4 text-center">
                    <div wire:loading.remove wire:target="signature">
                        <div class="text-sm {{ $signatureImage ? 'text-gray-400' : 'text-gray-600' }}">
                            @if($signatureImage)
                                Please delete current signature before uploading a new one
                            @else
                                Click to upload or drag and drop
                            @endif
                        </div>
                        <div class="mt-1 text-xs {{ $signatureImage ? 'text-gray-400' : 'text-gray-500' }}">
                            PNG, JPG up to 1MB
                        </div>
                    </div>
                    <div wire:loading wire:target="signature" class="text-sm text-indigo-600">
                        Uploading signature...
                    </div>
                </div>
            </label>
            @error('signature') 
                <div class="mt-2 text-xs text-red-600">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>
</div>
