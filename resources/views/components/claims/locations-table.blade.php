@props(['locations'])

<div class="bg-white rounded-lg shadow-sm ring-1 ring-black/5 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th scope="col" class="text-sm font-medium text-gray-500 px-4 py-3 text-left">No.</th>
                    <th scope="col" class="text-sm font-medium text-gray-500 px-4 py-3 text-left">From</th>
                    <th scope="col" class="text-sm font-medium text-gray-500 px-4 py-3 text-left">To</th>
                    <th scope="col" class="text-sm font-medium text-gray-500 px-4 py-3 text-left">Distance (km)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($locations->sortBy('order') as $location)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $location->order }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $location->from_location }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $location->to_location }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ number_format($location->distance, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-sm text-gray-400 text-center">No locations found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>