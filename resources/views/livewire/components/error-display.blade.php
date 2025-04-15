<div class="flex min-h-[60vh] flex-col items-center justify-center">
    <div class="rounded-lg bg-red-50 p-6 text-center">
        <svg class="mx-auto h-12 w-12 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <h2 class="mt-4 text-lg font-medium text-red-800">{{ $message ?? 'An error occurred' }}</h2>
        <p class="mt-2 text-sm text-red-600">You don't have permission to access this resource.</p>
        <div class="mt-6">
            <a href="{{ route('home') }}" class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                Return to Dashboard
            </a>
        </div>
    </div>
</div> 