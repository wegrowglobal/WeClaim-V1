@if(!auth()->user()->first_name || !auth()->user()->second_name || !auth()->user()->email || !auth()->user()->phone || !auth()->user()->address || !auth()->user()->city)
    <div class="col-span-2 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-semibold">Notice!</strong>
        <span class="block sm:inline text-xs"> Please complete your profile information.</span>
    </div>
@endif