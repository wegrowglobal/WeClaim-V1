@props(['notifications'])

<div class="overflow-hidden rounded-lg bg-white shadow-sm">
    @forelse($notifications->groupBy('data.claim_id') as $claimId => $claimNotifications)
        <div class="border-b border-gray-100 last:border-b-0">
            <!-- Claim Header -->
            <div class="flex items-center justify-between bg-gradient-to-r from-blue-50 to-indigo-50 px-4 py-2">
                <h4 class="flex items-center text-base font-semibold text-gray-800">
                    <svg class="mr-1 h-4 w-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Claim #{{ $claimId }}
                </h4>
                <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-800">
                    {{ $claimNotifications->count() }}
                </span>
            </div>

            <!-- Timeline -->
            <div class="px-4 py-4">
                <div class="relative">
                    <div class="absolute bottom-0 left-3 top-0 w-0.5 bg-gray-200"></div>
                    @foreach ($claimNotifications->sortByDesc('created_at') as $notification)
                        <div class="{{ $notification->read_at ? 'opacity-75' : '' }} relative mb-4 last:mb-0">
                            <!-- Timeline Dot -->
                            <div class="absolute left-0 top-1/2 -translate-y-1/2 transform">
                                <span
                                    class="{{ isset($notification->data['action'])
                                        ? match ($notification->data['action']) {
                                            'rejected_admin', 'rejected_datuk', 'rejected_hr', 'rejected_finance' => 'bg-red-500 text-white',
                                            'approved_admin', 'approved_datuk', 'approved_hr', 'approved_finance' => 'bg-green-500 text-white',
                                            'resubmitted' => 'bg-yellow-500 text-white',
                                            'pending_review_admin', 'pending_admin_review' => 'bg-blue-500 text-white',
                                            'pending_review_datuk', 'pending_datuk_review' => 'bg-purple-500 text-white',
                                            'pending_review_hr', 'pending_hr_review' => 'bg-indigo-500 text-white',
                                            'pending_review_finance', 'pending_finance_review' => 'bg-teal-500 text-white',
                                            'completed' => 'bg-green-500 text-white',
                                            default => 'bg-gray-500 text-white',
                                        }
                                        : 'bg-gray-500 text-white' }} flex h-6 w-6 items-center justify-center rounded-full border-2 border-white shadow-sm">
                                    @if (str_contains($notification->data['action'] ?? '', 'completed'))
                                        <svg class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    @elseif(str_contains($notification->data['action'] ?? '', 'rejected'))
                                        <svg class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    @endif
                                </span>
                            </div>

                            <!-- Notification Content -->
                            <div class="ml-9">
                                <div class="rounded-md border border-gray-100 bg-white p-2 shadow-sm">
                                    <p
                                        class="{{ $notification->read_at ? 'opacity-75' : 'font-medium' }} text-xs text-gray-700">
                                        {{ $notification->data['message'] }}
                                    </p>
                                    <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                                        <time class="flex items-center" datetime="{{ $notification->created_at }}">
                                            <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $notification->created_at->diffForHumans() }}
                                        </time>

                                        <!-- Action Buttons -->
                                        <div class="flex items-center space-x-1">
                                            @if ($notification->data['is_for_claim_owner'] ?? true)
                                                @if (str_contains($notification->data['action'], 'rejected'))
                                                    @php
                                                        $claim = \App\Models\Claim::find(
                                                            $notification->data['claim_id'],
                                                        );
                                                        $hasBeenResubmitted =
                                                            $claim &&
                                                            $claim->status !== \App\Models\Claim::STATUS_REJECTED;
                                                    @endphp
                                                    @unless ($hasBeenResubmitted)
                                                        <a class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs text-red-700 transition-colors duration-150 ease-in-out hover:bg-red-200"
                                                            href="{{ route('claims.resubmit', $notification->data['claim_id']) }}">
                                                            <svg class="mr-0.5 h-3 w-3" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                                                </path>
                                                            </svg>
                                                            Resubmit
                                                        </a>
                                                    @endunless
                                                @else
                                                    <a class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-700 transition-colors duration-150 ease-in-out hover:bg-blue-200"
                                                        href="{{ route('claims.view', $notification->data['claim_id']) }}">
                                                        <svg class="mr-0.5 h-3 w-3" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z">
                                                            </path>
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                            </path>
                                                        </svg>
                                                        View
                                                    </a>
                                                @endif
                                            @else
                                                @php
                                                    $claim = \App\Models\Claim::find($notification->data['claim_id']);
                                                    $hasBeenReviewed =
                                                        $claim &&
                                                        $claim
                                                            ->reviews()
                                                            ->where('reviewer_id', auth()->id())
                                                            ->where('created_at', '>', $notification->created_at)
                                                            ->exists();
                                                @endphp
                                                @unless ($hasBeenReviewed)
                                                    <a class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-700 transition-colors duration-150 ease-in-out hover:bg-green-200"
                                                        href="{{ route('claims.review', $notification->data['claim_id']) }}">
                                                        <svg class="mr-0.5 h-3 w-3" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                                                            </path>
                                                        </svg>
                                                        Review
                                                    </a>
                                                @endunless
                                            @endif

                                            @unless ($notification->read_at)
                                                <form class="inline"
                                                    action="{{ route('notifications.mark-as-read', $notification->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    <button
                                                        class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-700 transition-colors duration-150 ease-in-out hover:bg-gray-200"
                                                        type="submit">
                                                        <svg class="mr-0.5 h-3 w-3" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
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
            <svg class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <h3 class="mt-2 text-lg font-semibold text-gray-900">No notifications</h3>
            <p class="mt-1 text-sm text-gray-500">You're all caught up!</p>
        </div>
    @endforelse
</div>
