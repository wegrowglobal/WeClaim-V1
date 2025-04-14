@php
    use App\Models\Claim\Claim;
@endphp

@props(['claim'])

<div class="flex justify-end gap-4 mt-8">
    @if ($claim->status == Claim::STATUS_SUBMITTED && Auth::id() == $claim->user_id)
        <form action="{{ route('claims.cancel', $claim->id) }}" method="POST" class="inline">
            @csrf
            @method('PUT')
            <button type="submit" 
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Cancel Claim
            </button>
        </form>
    @elseif ($claim->status == Claim::STATUS_REJECTED && Auth::id() == $claim->user_id)
        <form action="{{ route('claims.resubmit', $claim->id) }}" method="POST" class="inline">
            @csrf
            @method('PUT')
            <button type="submit"
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Resubmit Claim
            </button>
        </form>
    @elseif ($claim->status == Claim::STATUS_APPROVED_FINANCE && Auth::user()->role->name === 'Finance')
        <form action="{{ route('claims.complete', $claim->id) }}" method="POST" class="inline">
            @csrf
            @method('PUT')
            <button type="submit" 
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Mark as Paid
            </button>
        </form>
    @endif
</div> 