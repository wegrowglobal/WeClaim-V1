@php
    use App\Models\Claim;
@endphp

@props(['claim'])

<div class="bg-white rounded-lg shadow-sm ring-1 ring-black/5 p-6 animate-slide-in delay-400">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-lg font-medium text-gray-900">Review Action</h3>
            <p class="text-sm text-gray-500 mt-1">Process this claim request</p>
        </div>
    </div>

    <div class="space-y-4">
        @if ($claim->status !== Claim::STATUS_APPROVED_ADMIN)
            <!-- Remarks -->
            <div class="space-y-2">
                <label for="remarks" class="block text-sm font-medium text-gray-700">Remarks</label>
                <textarea id="remarks" 
                        name="remarks" 
                        rows="3" 
                        class="form-input p-4 block w-full rounded-lg border border-gray-200 bg-gray-50/50 focus:bg-white focus:border-gray-400 transition-all sm:text-sm"
                        placeholder="Enter your remarks"></textarea>
            </div>
        @endif


        @if($claim->status === Claim::STATUS_SUBMITTED)
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
        @elseif($claim->status === Claim::STATUS_APPROVED_ADMIN)

            <div class="flex items-center gap-4">
                @if(Auth::user()->role->name === 'Admin' && $claim->status === Claim::STATUS_APPROVED_ADMIN)
                <button 
                    onclick="sendToDatuk({{ $claim->id }})"
                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all">
                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Send to Datuk
                    </button>
                @else
                    <button 
                        onclick="rejectClaim({{ $claim->id }})"
                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-red-600 bg-white rounded-lg border border-red-200 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all">
                        <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Reject Claim
                    </button>
                @endif
            </div>
        @elseif($claim->status === Claim::STATUS_APPROVED_DATUK)
            <div class="flex items-center gap-4">
                <button 
                    onclick="approveClaim({{ $claim->id }})"
                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Approve as HR
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
        @elseif($claim->status === Claim::STATUS_APPROVED_HR)
            <div class="flex items-center gap-4">
                <button 
                    onclick="approveClaim({{ $claim->id }})"
                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Approve as Finance
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
        @elseif($claim->status === Claim::STATUS_APPROVED_FINANCE)
            <div class="flex items-center gap-4">
                <button 
                    onclick="javascript:approveClaim({{ $claim->id }}, true)"
                    data-action="mark-as-done"
                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all">
                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Mark as Done
                </button>
            </div>
        @else
            <div class="text-sm text-gray-500">
                No actions available for current status
            </div>
        @endif
    </div>
</div> 