@props(['notifications'])

<div class="bg-white shadow-sm rounded-lg overflow-hidden">
    @forelse($notifications->groupBy('data.claim_id') as $claimId => $claimNotifications)
        <div class="border-b border-gray-100 last:border-b-0">
            <!-- Claim Header -->
            <div class="flex items-center justify-between px-4 py-2 bg-gradient-to-r from-blue-50 to-indigo-50">
                <h4 class="text-base font-semibold text-gray-800 flex items-center">
                    <svg class="w-4 h-4 mr-1 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Claim #{{ $claimId }}
                </h4>
                <span class="px-2 py-0.5 text-xs font-medium bg-indigo-100 text-indigo-800 rounded-full">
                    {{ $claimNotifications->count() }}
                </span>
            </div>

            <!-- Timeline -->
            <div class="px-4 py-4">
                <div class="relative">
                    <div class="absolute left-3 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                    @foreach($claimNotifications->sortByDesc('created_at') as $notification)
                        <div class="relative mb-4 last:mb-0 {{ $notification->read_at ? 'opacity-75' : '' }}">
                            <!-- Timeline Dot -->
                            <div class="absolute left-0 top-1/2 transform -translate-y-1/2">
                                <span class="flex items-center justify-center h-6 w-6 rounded-full border-2 border-white shadow-sm {{
                                    isset($notification->data['action']) ?
                                        match($notification->data['action']) {
                                            'rejected_admin', 'rejected_datuk', 'rejected_hr', 'rejected_finance' => 'bg-red-500 text-white',
                                            'approved_admin', 'approved_datuk', 'approved_hr', 'approved_finance' => 'bg-green-500 text-white',
                                            'resubmitted' => 'bg-yellow-500 text-white',
                                            'pending_review_admin', 'pending_admin_review' => 'bg-blue-500 text-white',
                                            'pending_review_datuk', 'pending_datuk_review' => 'bg-purple-500 text-white',
                                            'pending_review_hr', 'pending_hr_review' => 'bg-indigo-500 text-white',
                                            'pending_review_finance', 'pending_finance_review' => 'bg-teal-500 text-white',
                                            default => 'bg-gray-500 text-white'
                                        }
                                    : 'bg-gray-500 text-white'
                                }}">
                                    @if(str_contains($notification->data['action'] ?? '', 'rejected'))
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                    @elseif(str_contains($notification->data['action'] ?? '', 'approved'))
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    @else
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                                    @endif
                                </span>
                            </div>

                            <!-- Notification Content -->
                            <div class="ml-9">
                                <div class="bg-white p-2 rounded-md shadow-sm border border-gray-100">
                                    <p class="text-xs text-gray-700 {{ $notification->read_at ? 'opacity-75' : 'font-medium' }}">
                                        {{ $notification->data['message'] }}
                                    </p>
                                    <div class="flex items-center justify-between text-xs text-gray-500 mt-2">
                                        <time datetime="{{ $notification->created_at }}" class="flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>
                                            {{ $notification->created_at->diffForHumans() }}
                                        </time>

                                        <!-- Action Buttons -->
                                        <div class="flex items-center space-x-1">
                                            @if($notification->data['is_for_claim_owner'] ?? true)
                                                @if(str_contains($notification->data['action'], 'rejected'))
                                                    @php
                                                        $claim = \App\Models\Claim::find($notification->data['claim_id']);
                                                        $hasBeenResubmitted = $claim && $claim->status !== \App\Models\Claim::STATUS_REJECTED;
                                                    @endphp
                                                    @unless($hasBeenResubmitted)
                                                        <a href="{{ route('claims.resubmit', $notification->data['claim_id']) }}"
                                                           class="inline-flex items-center px-2 py-0.5 bg-red-100 text-red-700 rounded-full hover:bg-red-200 transition-colors duration-150 ease-in-out text-xs">
                                                            <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                                            Resubmit
                                                        </a>
                                                    @endunless
                                                @else
                                                    <a href="{{ route('claims.view', $notification->data['claim_id']) }}"
                                                       class="inline-flex items-center px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full hover:bg-blue-200 transition-colors duration-150 ease-in-out text-xs">
                                                        <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                        View
                                                    </a>
                                                @endif
                                            @else
                                                @php
                                                    $claim = \App\Models\Claim::find($notification->data['claim_id']);
                                                    $hasBeenReviewed = $claim && $claim->reviews()
                                                        ->where('reviewer_id', auth()->id())
                                                        ->where('created_at', '>', $notification->created_at)
                                                        ->exists();
                                                @endphp
                                                @unless($hasBeenReviewed)
                                                    <a href="{{ route('claims.review', $notification->data['claim_id']) }}"
                                                       class="inline-flex items-center px-2 py-0.5 bg-green-100 text-green-700 rounded-full hover:bg-green-200 transition-colors duration-150 ease-in-out text-xs">
                                                        <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                                        Review
                                                    </a>
                                                @endunless
                                            @endif

                                            @unless($notification->read_at)
                                                <form action="{{ route('notifications.mark-as-read', $notification->id) }}" 
                                                      method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="inline-flex items-center px-2 py-0.5 bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200 transition-colors duration-150 ease-in-out text-xs">
                                                        <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                        Read
                                                    </button>
                                                </form>
                                            @endunless
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @empty
        <div class="py-8 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <h3 class="mt-2 text-lg font-semibold text-gray-900">No notifications</h3>
            <p class="mt-1 text-sm text-gray-500">You're all caught up!</p>
        </div>
    @endforelse
</div>