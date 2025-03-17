@push('styles')
<!-- Styling for HTML content in changelogs -->
<style>
    .changelog-content ul {
        list-style-type: disc;
        padding-left: 1.25rem;
        margin: 0.4rem 0;
    }
    
    .changelog-content ol {
        list-style-type: decimal;
        padding-left: 1.25rem;
        margin: 0.4rem 0;
    }
    
    .changelog-content li {
        margin-bottom: 0.2rem;
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
        margin-bottom: 0.4rem;
    }

    /* Mobile optimizations */
    @media (max-width: 768px) {
        .changelog-container {
        max-height: 80vh;
            margin-bottom: 2rem;
        }
    }
</style>
@endpush

<div class="w-full">
    <div class="changelog-container h-[650px] overflow-hidden bg-white rounded-xl md:rounded-3xl shadow-md md:shadow-xl">
        <div class="flex h-full flex-col bg-white p-3 md:p-6 text-black">
            <div class="flex items-center justify-between mb-4 md:mb-8">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold">What's New</h2>
                    <p class="mt-1 md:mt-2 text-xs md:text-sm text-gray-500">Latest updates and improvements</p>
                </div>
                <button 
                    type="button" 
                    onclick="toggleChangelog()"
                    class="md:hidden inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-transparent">
                @forelse ($changelogs as $changelog)
                    <div class="mb-3 md:mb-4 rounded-md border border-gray-200 bg-gray-50 p-3 md:p-4">
                        <div class="mb-1 md:mb-2 flex items-center justify-between">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
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
                        <h3 class="text-sm md:text-base font-medium text-black">{{ $changelog->title }}</h3>
                        <div class="mt-1 text-xs text-gray-600 changelog-content">{!! $changelog->content !!}</div>
                        <div class="mt-1 md:mt-2 text-xs text-gray-500">
                            {{ $changelog->published_at->format('M d, Y') }}
                        </div>
                    </div>
                @empty
                    <div class="rounded-md border border-gray-200 bg-gray-50 p-4">
                        <p class="text-center text-gray-500">No updates available yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
