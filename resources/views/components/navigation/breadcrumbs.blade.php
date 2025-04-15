<nav class="w-full bg-wgg-white border-b border-wgg-border">
    <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center h-12 text-sm">
            @foreach($breadcrumbs as $index => $breadcrumb)
                @if($index > 0)
                    <div class="mx-1 text-wgg-black-400">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </div>
                @endif

                <div class="{{ $loop->last ? 'text-wgg-black-950 font-medium' : 'text-wgg-black-600 hover:text-wgg-black-950' }}">
                    @if($loop->last || $breadcrumb['url'] === '#')
                        <span class="truncate max-w-[150px] sm:max-w-xs">{{ $breadcrumb['name'] }}</span>
                    @else
                        <a href="{{ $breadcrumb['url'] }}" class="truncate max-w-[150px] sm:max-w-xs transition-colors duration-200">
                            {{ $breadcrumb['name'] }}
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</nav>