@props(['claim'])

<div class="bg-white rounded-lg shadow-sm ring-1 ring-black/5 p-6 animate-slide-in delay-400">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-lg font-medium text-gray-900">Administrative Action</h3>
            <p class="text-sm text-gray-500 mt-1">Process this claim request</p>
        </div>
    </div>

    <div class="space-y-4">
        <div class="flex items-center gap-4">
            <button 
                onclick="approveClaim({{ $claim->id }})"
                class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Approve Claim
            </button>
            <button 
                onclick="rejectClaim({{ $claim->id }})"
                class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-red-600 bg-white rounded-lg border border-red-200 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all">
                <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Reject Claim
            </button>
        </div>
    </div>
</div> 