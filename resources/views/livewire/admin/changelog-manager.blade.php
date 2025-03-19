<div class="container mx-auto">

    <!-- Notification -->
    <div 
        x-data="{ 
            show: false, 
            message: '', 
            type: 'success',
            init() {
                window.addEventListener('notify', (event) => {
                    this.message = event.detail.message;
                    this.type = event.detail.type || 'success';
                    this.show = true;
                    setTimeout(() => { this.show = false }, 3000);
                });
            }
        }" 
        x-show="show" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-2"
        class="fixed right-4 top-4 z-50 max-w-sm rounded-md p-4 shadow-lg"
        :class="{
            'bg-green-50 text-green-800 border border-green-200': type === 'success',
            'bg-red-50 text-red-800 border border-red-200': type === 'error',
            'bg-blue-50 text-blue-800 border border-blue-200': type === 'info',
            'bg-amber-50 text-amber-800 border border-amber-200': type === 'warning'
        }"
    >
        <div class="flex items-center">
            <template x-if="type === 'success'">
                <svg class="mr-2 h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                </svg>
            </template>
            <template x-if="type === 'error'">
                <svg class="mr-2 h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                </svg>
            </template>
            <template x-if="type === 'info'">
                <svg class="mr-2 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                </svg>
            </template>
            <template x-if="type === 'warning'">
                <svg class="mr-2 h-5 w-5 text-amber-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                </svg>
            </template>
            <span x-text="message"></span>
        </div>
    </div>

    <!-- Form -->
    <div class="mb-8 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="mb-6 text-xl font-bold text-black">{{ $isEditing ? 'Edit' : 'Create' }} Changelog Entry</h2>
        
        <form wire:submit.prevent="save" class="space-y-6">
            <div class="relative">
                <label for="title" class="mb-1 block text-sm font-medium text-gray-700">Title</label>
                <input type="text" id="title" wire:model="title" class="block w-full rounded-md border border-gray-200 bg-white px-4 py-3 text-sm transition-all focus:border-black focus:outline-none focus:ring-1 focus:ring-black" placeholder="What's new?">
                @error('title') <span class="mt-1 text-xs text-red-600">{{ $message }}</span> @enderror
            </div>
            
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <div class="relative">
                    <label for="version" class="mb-1 block text-sm font-medium text-gray-700">Version (optional)</label>
                    <input type="text" id="version" wire:model="version" class="block w-full rounded-md border border-gray-200 bg-white px-4 py-3 text-sm transition-all focus:border-black focus:outline-none focus:ring-1 focus:ring-black" placeholder="e.g. 1.2.3">
                    @error('version') <span class="mt-1 text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                
                <div class="relative">
                    <label for="type" class="mb-1 block text-sm font-medium text-gray-700">Type</label>
                    <select id="type" wire:model="type" class="block w-full rounded-md border border-gray-200 bg-white px-4 py-3 text-sm transition-all focus:border-black focus:outline-none focus:ring-1 focus:ring-black">
                        <option value="feature">Feature</option>
                        <option value="improvement">Improvement</option>
                        <option value="bugfix">Bug Fix</option>
                        <option value="other">Other</option>
                    </select>
                    @error('type') <span class="mt-1 text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                
                <div class="flex items-end">
                    <div class="flex items-center">
                        <input id="is_published" type="checkbox" wire:model="is_published" class="h-4 w-4 rounded border-gray-300 text-black focus:ring-black">
                        <label for="is_published" class="ml-2 block text-sm text-gray-700">Publish immediately</label>
                    </div>
                </div>
            </div>
            
            <div class="relative">
                <label for="content" class="mb-1 block text-sm font-medium text-gray-700">Content</label>
                <div wire:ignore>
                    <div class="mb-2 flex flex-wrap gap-2 rounded-t-md border border-gray-200 bg-gray-50 p-2">
                        <button type="button" onclick="formatDoc('bold')" class="rounded px-2 py-1 hover:bg-gray-200">
                            <i class="fas fa-bold"></i>
                        </button>
                        <button type="button" onclick="formatDoc('italic')" class="rounded px-2 py-1 hover:bg-gray-200">
                            <i class="fas fa-italic"></i>
                        </button>
                        <button type="button" onclick="formatDoc('insertunorderedlist')" class="rounded px-2 py-1 hover:bg-gray-200">
                            <i class="fas fa-list-ul"></i>
                        </button>
                        <button type="button" onclick="formatDoc('insertorderedlist')" class="rounded px-2 py-1 hover:bg-gray-200">
                            <i class="fas fa-list-ol"></i>
                        </button>
                        <button type="button" onclick="formatDoc('createlink', prompt('Enter the link URL'))" class="rounded px-2 py-1 hover:bg-gray-200">
                            <i class="fas fa-link"></i>
                        </button>
                    </div>
                    <div id="editor" contenteditable="true" class="min-h-[200px] rounded-b-md border border-gray-200 bg-white p-4 focus:border-black focus:outline-none focus:ring-1 focus:ring-black" style="min-height: 200px;"></div>
                    <textarea id="content" wire:model.defer="content" style="display: none;"></textarea>
                </div>
                <p class="mt-1 text-xs text-gray-500">HTML formatting is supported (e.g., &lt;strong&gt;, &lt;em&gt;, &lt;ul&gt;, &lt;li&gt;)</p>
                @error('content') <span class="mt-1 text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            <!-- Live Prev iew -->
            <div class="mt-6 rounded-lg border border-gray-200 bg-white p-4">
                <h3 class="mb-2 text-sm font-medium text-gray-700">Live Preview</h3>
                <div class="rounded-lg border border-gray-100 bg-gray-50 p-4">
                    <h4 class="text-lg font-semibold text-black">{{ $title ?: 'Your Changelog Title' }}</h4>
                    <p class="text-xs text-gray-500">{{ now()->format('M d, Y') }}</p>
                    <div id="preview-content" class="mt-2 text-sm text-gray-700 changelog-content">
                        {!! $content ?: 'Your content will appear here...' !!}
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 pt-4">
                @if($isEditing)
                    <button type="button" wire:click="resetForm" class="rounded-md border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2">
                        Cancel
                    </button>
                @endif
                <button type="submit" class="rounded-md bg-black px-4 py-3 text-sm font-medium text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2">
                    {{ $isEditing ? 'Update' : 'Create' }} Entry
                </button>
            </div>
        </form>
    </div>

    <!-- Changelog List -->
    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
            <h2 class="text-lg font-bold text-black">Changelog Entries</h2>
        </div>
        
        <div class="divide-y divide-gray-200">
            @forelse($changelogs as $changelog)
                <div class="group relative p-6 transition-all hover:bg-gray-50">
                    <div class="flex flex-col space-y-4 sm:flex-row sm:items-start sm:space-y-0">
                        <!-- Left side: Content -->
                        <div class="flex-1 pr-4">
                            <!-- Title and badges -->
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-semibold text-black">{{ $changelog->title }}</h3>
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                    @if($changelog->type === 'feature') bg-green-100 text-green-800
                                    @elseif($changelog->type === 'improvement') bg-blue-100 text-blue-800
                                    @elseif($changelog->type === 'bugfix') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($changelog->type) }}
                                </span>
                                @if($changelog->version)
                                    <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">v{{ $changelog->version }}</span>
                                @endif
                                
                                <!-- Published status badge -->
                                @if($changelog->is_published)
                                    <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700">
                                        <svg class="mr-1 h-3 w-3 text-green-500" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3" />
                                        </svg>
                                        Published
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-gray-50 px-2 py-0.5 text-xs font-medium text-gray-600">
                                        <svg class="mr-1 h-3 w-3 text-gray-400" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3" />
                                        </svg>
                                        Draft
                                    </span>
                                @endif
                            </div>
                            
                            <!-- Content preview -->
                            <div class="mb-3 rounded-md bg-gray-50 p-3 text-sm text-gray-600">
                                {!! Str::limit(strip_tags($changelog->content), 150) !!}
                            </div>
                            
                            <!-- Meta information -->
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-gray-500">
                                <span class="inline-flex items-center">
                                    <svg class="mr-1 h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.75 2a.75.75 0 01.75.75V4h7V2.75a.75.75 0 011.5 0V4h.25A2.75 2.75 0 0118 6.75v8.5A2.75 2.75 0 0115.25 18H4.75A2.75 2.75 0 012 15.25v-8.5A2.75 2.75 0 014.75 4H5V2.75A.75.75 0 015.75 2zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75z" clip-rule="evenodd" />
                                    </svg>
                                    Created: {{ $changelog->created_at->format('M d, Y') }}
                                </span>
                                @if($changelog->is_published)
                                    <span class="inline-flex items-center">
                                        <svg class="mr-1 h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd" />
                                        </svg>
                                        Published: {{ $changelog->published_at->format('M d, Y') }}
                                    </span>
                                @endif
                                <span class="inline-flex items-center">
                                    <svg class="mr-1 h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10 8a3 3 0 100-6 3 3 0 000 6zM3.465 14.493a1.23 1.23 0 00.41 1.412A9.957 9.957 0 0010 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 00-13.074.003z" />
                                    </svg>
                                    By: {{ $changelog->creator->first_name }} {{ $changelog->creator->second_name }}
                                </span>
                            </div>
                        </div>
                        
                        <!-- Right side: Actions -->
                        <div class="flex flex-row items-center gap-2 sm:flex-col sm:items-end">
                            <button wire:click="togglePublish({{ $changelog->id }})" 
                                class="inline-flex items-center rounded-md border px-3 py-2 text-sm font-medium shadow-sm transition-colors
                                @if($changelog->is_published) 
                                    border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100
                                @else 
                                    border-green-200 bg-green-50 text-green-700 hover:bg-green-100
                                @endif">
                                @if($changelog->is_published)
                                    <svg class="mr-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M4 10a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1v-4a1 1 0 00-1-1H4z" />
                                        <path fill-rule="evenodd" d="M12 4a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V5a1 1 0 00-1-1h-4zm0 10a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1v-4a1 1 0 00-1-1h-4z" clip-rule="evenodd" />
                                    </svg>
                                    Unpublish
                                @else
                                    <svg class="mr-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.5.06l-4 5.5a.75.75 0 00-.23.46z" clip-rule="evenodd" />
                                    </svg>
                                    Publish
                                @endif
                            </button>
                            
                            <div class="flex items-center gap-2 sm:mt-3">
                                <button wire:click="edit({{ $changelog->id }})" 
                                    class="inline-flex items-center rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50">
                                    <svg class="mr-1.5 h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M5.433 13.917l1.262-3.155A4 4 0 017.58 9.42l6.92-6.918a2.121 2.121 0 013 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 01-.65-.65z" />
                                        <path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0010 3H4.75A2.75 2.75 0 002 5.75v9.5A2.75 2.75 0 004.75 18h9.5A2.75 2.75 0 0017 15.25V10a.75.75 0 00-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5z" />
                                    </svg>
                                    Edit
                                </button>
                                <button wire:click="confirmDelete({{ $changelog->id }})" 
                                    class="inline-flex items-center rounded-md border border-red-200 bg-white px-3 py-2 text-sm font-medium text-red-600 shadow-sm transition-colors hover:bg-red-50">
                                    <svg class="mr-1.5 h-4 w-4 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd" />
                                    </svg>
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-12">
                    <svg class="h-12 w-12 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No changelog entries</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating your first changelog entry.</p>
                </div>
            @endforelse
        </div>
        
        <div class="border-t border-gray-200 bg-gray-50 px-6 py-4">
            {{ $changelogs->links() }}
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    @if($confirmingDeletion)
        <div class="fixed inset-0 z-10 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
                <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                <h3 class="text-lg font-medium leading-6 text-black" id="modal-title">Delete Changelog Entry</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">Are you sure you want to delete this changelog entry? This action cannot be undone.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button wire:click="delete" type="button" class="inline-flex w-full justify-center rounded-md border border-transparent bg-red-600 px-4 py-3 text-base font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">
                            <span wire:loading.remove wire:target="delete">Delete</span>
                            <span wire:loading wire:target="delete" class="inline-flex items-center">
                                <svg class="mr-2 h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Deleting...
                            </span>
                        </button>
                        <button wire:click="cancelDelete" type="button" class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-3 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2 sm:ml-3 sm:mt-0 sm:w-auto sm:text-sm">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Add direct script for editor -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editor = document.getElementById('editor');
            const textarea = document.getElementById('content');
            const preview = document.getElementById('preview-content');
            
            // Initialize editor with content from textarea
            if (textarea.value) {
                editor.innerHTML = textarea.value;
                preview.innerHTML = textarea.value;
            }
            
            // Update textarea and preview when editor content changes
            editor.addEventListener('input', function() {
                textarea.value = editor.innerHTML;
                preview.innerHTML = editor.innerHTML;
                
                // Trigger Livewire update
                @this.set('content', editor.innerHTML);
            });
            
            // Listen for Livewire events
            window.addEventListener('livewire:initialized', function() {
                // When form is reset
                @this.on('formReset', function() {
                    editor.innerHTML = '';
                    preview.innerHTML = 'Your content will appear here...';
                });
                
                // When content is updated from the component
                @this.on('contentUpdated', function(content) {
                    if (content) {
                        editor.innerHTML = content;
                        preview.innerHTML = content;
                        console.log('Content updated:', content);
                    }
                });
            });
        });
        
        // Simple formatting function
        function formatDoc(command, value = null) {
            document.execCommand(command, false, value);
            document.getElementById('editor').focus();
            
            // Update textarea and preview
            const editor = document.getElementById('editor');
            const textarea = document.getElementById('content');
            const preview = document.getElementById('preview-content');
            
            textarea.value = editor.innerHTML;
            preview.innerHTML = editor.innerHTML;
            
            // Trigger Livewire update
            @this.set('content', editor.innerHTML);
        }
    </script>

    <style>
        /* Styling for HTML content in changelogs */
        .changelog-content ul {
            list-style-type: disc;
            padding-left: 1.5rem;
            margin: 0.5rem 0;
        }
        
        .changelog-content ol {
            list-style-type: decimal;
            padding-left: 1.5rem;
            margin: 0.5rem 0;
        }
        
        .changelog-content li {
            margin-bottom: 0.25rem;
        }
        
        .changelog-content strong {
            font-weight: 600;
        }
        
        .changelog-content em {
            font-style: italic;
        }
        
        .changelog-content a {
            color: #4b5563;
            text-decoration: underline;
        }
        
        .changelog-content a:hover {
            color: #000000;
        }
        
        .changelog-content p {
            margin-bottom: 0.5rem;
        }
        
        .changelog-content blockquote {
            border-left: 3px solid #e5e7eb;
            padding-left: 1rem;
            margin: 0.5rem 0;
            color: #6b7280;
        }
        
        .changelog-content pre {
            background-color: #f3f4f6;
            padding: 0.5rem;
            border-radius: 0.25rem;
            overflow-x: auto;
        }
        
        .changelog-content code {
            font-family: monospace;
            background-color: #f3f4f6;
            padding: 0.125rem 0.25rem;
            border-radius: 0.25rem;
        }
        
        .changelog-content table {
            border-collapse: collapse;
            width: 100%;
            margin: 0.5rem 0;
        }
        
        .changelog-content th, .changelog-content td {
            border: 1px solid #e5e7eb;
            padding: 0.25rem 0.5rem;
        }
        
        .changelog-content th {
            background-color: #f9fafb;
        }
        
        #editor {
            min-height: 200px;
        }
    </style>
</div>
