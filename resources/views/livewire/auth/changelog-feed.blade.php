<div class="hidden md:block md:w-full md:max-w-md">
    <div class="h-[650px] overflow-hidden bg-white md:rounded-3xl md:shadow-xl">
        <div class="flex h-full flex-col bg-black p-8 text-white">
            <div class="mb-8">
                <h2 class="text-2xl font-bold">What's New</h2>
                <p class="mt-2 text-sm text-gray-400">Latest updates and improvements</p>
            </div>

            <div class="flex-1 overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-gray-500 scrollbar-track-transparent">
                @forelse ($changelogs as $changelog)
                    <div class="mb-4 rounded-md border border-gray-800 bg-gray-900/50 p-4">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                @if($changelog->type === 'feature') bg-white text-black
                                @elseif($changelog->type === 'improvement') bg-gray-200 text-black
                                @elseif($changelog->type === 'bugfix') bg-gray-300 text-black
                                @else bg-gray-100 text-black
                                @endif">
                                {{ ucfirst($changelog->type) }}
                            </span>
                            @if($changelog->version)
                                <span class="text-xs text-gray-400">v{{ $changelog->version }}</span>
                            @endif
                        </div>
                        <h3 class="font-medium">{{ $changelog->title }}</h3>
                        <p class="mt-1 text-sm text-gray-400">{{ $changelog->content }}</p>
                        <div class="mt-2 text-xs text-gray-500">
                            {{ $changelog->published_at->format('M d, Y') }}
                        </div>
                    </div>
                @empty
                    <div class="rounded-md border border-gray-800 bg-gray-900/50 p-4">
                        <p class="text-center text-gray-400">No updates available yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
