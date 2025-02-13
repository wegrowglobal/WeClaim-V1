@php
    $user = auth()->user();
    $isProfileComplete = 
        $user->first_name && 
        $user->second_name && 
        $user->phone && 
        $user->address && 
        $user->city && 
        $user->state && 
        $user->zip_code && 
        $user->country && 
        $user->bankingInformation && 
        $user->bankingInformation->bank_name && 
        $user->bankingInformation->account_holder && 
        $user->bankingInformation->account_number;

    $shouldShow = $user->role_id === 1 && !$isProfileComplete;
@endphp

@if($shouldShow)
    <div class="fixed bottom-4 right-4 z-50 max-w-sm w-full bg-white rounded-lg border border-yellow-200 shadow-lg animate-slide-in">
        <div class="p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="ml-3 w-0 flex-1">
                    <h3 class="text-sm font-medium text-gray-900">Complete Your Profile</h3>
                    <p class="mt-1 text-sm text-gray-500">Please complete your profile information to start creating claims.</p>
                    <div class="mt-3">
                        <a href="{{ route('profile') }}" 
                           class="inline-flex items-center rounded-lg bg-yellow-500 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                            Complete Profile
                        </a>
                    </div>
                </div>
                <div class="ml-4 flex flex-shrink-0">
                    <button onclick="this.closest('div.fixed').remove()" 
                            class="inline-flex rounded-md text-gray-400 hover:text-gray-500 focus:outline-none">
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif 