@php
    use App\Models\Claim\Claim;
@endphp

@props(['claim'])

<div class="bg-white shadow sm:rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-medium leading-6 text-gray-900">Review Action</h3>
                <p class="mt-1 text-sm text-gray-500">Process this claim request</p>
            </div>
            <div>
                <div class="mt-5">
                    @if($claim->status === Claim::STATUS_SUBMITTED)
                        <div class="flex items-center gap-4">
                            <button type="button"
                                class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition-all hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                onclick="showApprovalForm({{ $claim->id }})">
                                <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Approve Claim
                            </button>
                            <button type="button"
                                onclick="showRejectionForm({{ $claim->id }})"
                                class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-all hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Reject Claim
                            </button>
                        </div>
                    @elseif($claim->status === Claim::STATUS_APPROVED_ADMIN)
                        <div class="flex items-center gap-4">
                            @if (Auth::user()->role->name === 'Admin' && $claim->status === Claim::STATUS_APPROVED_ADMIN)
                                <button
                                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-all hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                    onclick="sendToDatuk({{ $claim->id }})">
                                    <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    Send to Datuk
                                </button>
                            @elseif (Auth::user()->role->name === 'Manager')
                                <button
                                    class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition-all hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                    onclick="showApprovalForm({{ $claim->id }})">
                                    <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Approve as Manager
                                </button>
                                <button type="button"
                                    onclick="showRejectionForm({{ $claim->id }})"
                                    class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-all hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                    <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Reject Claim
                                </button>
                            @endif
                        </div>
                    @elseif($claim->status === Claim::STATUS_APPROVED_MANAGER)
                        <div class="flex items-center gap-4">
                            @if (Auth::user()->role->name === 'HR')
                                <button
                                    class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition-all hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                    onclick="showApprovalForm({{ $claim->id }})">
                                    <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Approve as HR
                                </button>
                                <button type="button"
                                    onclick="showRejectionForm({{ $claim->id }})"
                                    class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-all hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                    <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Reject Claim
                                </button>
                            @endif
                        </div>
                    @elseif($claim->status === Claim::STATUS_APPROVED_DATUK)
                        <div class="flex items-center gap-4">
                            <button
                                class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition-all hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                onclick="showApprovalForm({{ $claim->id }})">
                                <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Approve as Finance
                            </button>
                            <button type="button"
                                onclick="showRejectionForm({{ $claim->id }})"
                                class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-all hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Reject Claim
                            </button>
                        </div>
                    @elseif($claim->status === Claim::STATUS_APPROVED_HR)
                        <div class="flex items-center gap-4">
                            <button
                                class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition-all hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                onclick="showApprovalForm({{ $claim->id }})">
                                <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Approve as Finance
                            </button>
                            <button type="button"
                                onclick="showRejectionForm({{ $claim->id }})"
                                class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-all hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Reject Claim
                            </button>
                        </div>
                    @elseif($claim->status === Claim::STATUS_APPROVED_FINANCE)
                        <div class="flex items-center gap-4">
                            @if (Auth::user()->role->name === 'Finance')
                                <button
                                    class="inline-flex items-center justify-center rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white transition-all hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                                    onclick="showApprovalForm({{ $claim->id }}, true)">
                                    <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Mark as Done
                                </button>
                                <button type="button"
                                    onclick="showRejectionForm({{ $claim->id }})"
                                    class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-all hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                    <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Reject Claim
                                </button>
                            @endif
                        </div>
                    @else
                        <div class="flex items-center space-x-2 text-gray-500">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-sm">No actions available for current status</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showRejectionForm(claimId) {
        Swal.fire({
            title: 'Reject Claim',
            html: `<div class="rejection-form">@include('components.claims.rejection-form', ['claim' => $claim])</div>`,
            showConfirmButton: false,
            showCloseButton: false,
            showCancelButton: false,
            width: '32rem',
            allowOutsideClick: false,
            didOpen: () => {
                // Ensure the window.reviewActions object exists
                window.reviewActions = window.reviewActions || {};
                
                // Add event listener to the cancel button
                const cancelBtn = document.querySelector('.rejection-form button[onclick*="cancelRejection"]');
                if (cancelBtn) {
                    cancelBtn.onclick = () => {
                        console.log('Cancel rejection clicked'); // Debug log
                        Swal.close();
                    };
                }
            },
            customClass: {
                popup: 'rounded-lg shadow-xl border border-gray-200',
                title: 'text-xl font-medium text-gray-900 border-b border-gray-100 pb-3',
                htmlContainer: 'p-0'
            }
        });
    }

    function showApprovalForm(claimId, isDone = false) {
        Swal.fire({
            title: isDone ? 'Mark Claim as Done' : 'Approve Claim',
            html: `<div class="approval-form">@include('components.claims.approval-form', ['claim' => $claim])</div>`,
            showConfirmButton: false,
            showCloseButton: false,
            showCancelButton: false,
            width: '32rem',
            allowOutsideClick: false,
            didOpen: () => {
                // Ensure the window.reviewActions object exists
                window.reviewActions = window.reviewActions || {};
                
                // Add event listener to the cancel button
                const cancelBtn = document.querySelector('.approval-form button[onclick*="cancelApproval"]');
                if (cancelBtn) {
                    cancelBtn.onclick = () => {
                        console.log('Cancel approval clicked'); // Debug log
                        Swal.close();
                    };
                }
            },
            customClass: {
                popup: 'rounded-lg shadow-xl border border-gray-200',
                title: 'text-xl font-medium text-gray-900 border-b border-gray-100 pb-3',
                htmlContainer: 'p-0'
            }
        });
    }
</script>
@endpush
