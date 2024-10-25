@props(['claim'])

<div class="max-w-full rounded-lg border border-wgg-border mb-10">
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="p-10">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Claim Details</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Information</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <x-claims.detail-row label="Submitted At" :value="$claim->submitted_at->format('d-m-Y')" />
                        <x-claims.detail-row label="Date From" :value="$claim->date_from->format('d-m-Y')" />
                        <x-claims.detail-row label="Date To" :value="$claim->date_to->format('d-m-Y')" />
                        <x-claims.detail-row label="Toll Amount" :value="$claim->toll_amount" />
                        <x-claims.document-row label="Toll Document" :claim="$claim" type="toll" />
                        <x-claims.document-row label="Email Document" :claim="$claim" type="email" />
                        @foreach($claim->locations as $index => $location)
                            <x-claims.detail-row label="Location {{ $index + 1 }}" :value="$location->location" />
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>