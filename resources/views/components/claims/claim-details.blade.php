@props(['claim'])

<div class="bg-white rounded-lg shadow-sm ring-1 ring-black/5 animate-slide-in delay-100">
    <div class="px-6 py-5">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Basic Details</h3>
                <p class="text-sm text-gray-500 mt-1">Claim information and details</p>
            </div>
        </div>

        <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div class="flex flex-col p-4 bg-gray-50 rounded-lg">
                <dt class="text-sm font-medium text-gray-500">Submitted Date</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $claim->submitted_at->format('d M Y') }}</dd>
            </div>
            
            <div class="flex flex-col p-4 bg-gray-50 rounded-lg">
                <dt class="text-sm font-medium text-gray-500">Staff Name</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $claim->user->first_name }} {{ $claim->user->second_name }}</dd>
            </div>

            <div class="flex flex-col p-4 bg-gray-50 rounded-lg">
                <dt class="text-sm font-medium text-gray-500">Period</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    {{ $claim->date_from->format('d M Y') }} - {{ $claim->date_to->format('d M Y') }}
                </dd>
            </div>

            <div class="col-span-full p-4 bg-gray-50 rounded-lg">
                <dt class="text-sm font-medium text-gray-500">Claim Title</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $claim->title }}</dd>
            </div>

            <div class="col-span-full p-4 bg-gray-50 rounded-lg">
                <dt class="text-sm font-medium text-gray-500">Description</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $claim->description ?: 'No description provided' }}</dd>
            </div>
        </dl>
    </div>
</div> 