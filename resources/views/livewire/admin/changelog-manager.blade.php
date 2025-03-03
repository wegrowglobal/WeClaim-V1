<div class="container mx-auto">

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
                <textarea id="content" wire:model="content" rows="4" class="block w-full rounded-md border border-gray-200 bg-white px-4 py-3 text-sm transition-all focus:border-black focus:outline-none focus:ring-1 focus:ring-black" placeholder="Describe the changes..."></textarea>
                @error('content') <span class="mt-1 text-xs text-red-600">{{ $message }}</span> @enderror
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
                <div class="flex items-center justify-between p-6">
                    <div class="flex-1">
                        <div class="mb-1 flex items-center space-x-2">
                            <h3 class="text-base font-medium text-black">{{ $changelog->title }}</h3>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                @if($changelog->type === 'feature') bg-green-100 text-green-800
                                @elseif($changelog->type === 'improvement') bg-blue-100 text-blue-800
                                @elseif($changelog->type === 'bugfix') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($changelog->type) }}
                            </span>
                            @if($changelog->version)
                                <span class="text-xs text-gray-500">v{{ $changelog->version }}</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-500">{{ Str::limit($changelog->content, 100) }}</p>
                        <div class="mt-1 flex items-center space-x-4 text-xs text-gray-500">
                            <span>Created: {{ $changelog->created_at->format('M d, Y') }}</span>
                            @if($changelog->is_published)
                                <span>Published: {{ $changelog->published_at->format('M d, Y') }}</span>
                            @endif
                            <span>By: {{ $changelog->creator->first_name }} {{ $changelog->creator->second_name }}</span>
                        </div>
                    </div>
                    <div class="ml-4 flex items-center space-x-2">
                        <button wire:click="togglePublish({{ $changelog->id }})" class="rounded-md px-3 py-2 text-sm font-medium
                            @if($changelog->is_published) 
                                text-amber-700 hover:bg-amber-100
                            @else 
                                text-green-700 hover:bg-green-100
                            @endif">
                            {{ $changelog->is_published ? 'Unpublish' : 'Publish' }}
                        </button>
                        <button wire:click="edit({{ $changelog->id }})" class="rounded-md px-3 py-2 text-sm font-medium text-black hover:bg-gray-100">
                            Edit
                        </button>
                        <button wire:click="confirmDelete({{ $changelog->id }})" class="rounded-md px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-100">
                            Delete
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-gray-500">
                    No changelog entries found. Create your first one!
                </div>
            @endforelse
        </div>
        
        <div class="border-t border-gray-200 px-6 py-4">
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
                        <button wire:click="delete" type="button" class="inline-flex w-full justify-center rounded-md border border-transparent bg-red-600 px-4 py-3 text-base font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">Delete</button>
                        <button wire:click="cancelDelete" type="button" class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-3 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2 sm:ml-3 sm:mt-0 sm:w-auto sm:text-sm">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        @this.on('notify', ({ message, type }) => {
            // You can implement a notification system here
            // For example, using a toast library or custom implementation
            alert(message);
        });
    });
</script>
