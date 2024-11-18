@props(['route', 'label'])

<a href="{{ $route }}" 
   class="inline-flex items-center px-3 py-1.5 rounded-md bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-colors gap-2 text-sm">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
    </svg>
    {{ $label }}
</a> 