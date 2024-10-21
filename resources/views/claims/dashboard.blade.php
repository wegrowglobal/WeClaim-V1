@php
use App\Models\Claim;
@endphp

<x-layout>
    @auth
    <div class="wgg-box-border-shadow p-6">
        <div class="flex flex-col px-4 mb-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Your Claims Dashboard</h1>
            <span class="text-red-500 text-sm italic">Temporary data going to be dump into table for testing purpose</span>
        </div>

        @if ($claims->isEmpty())
            <p class="text-center text-gray-500 text-xl mt-8">No claims found. Start by submitting a new claim.</p>
        @else
            <div class="flex justify-between items-center mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 w-full">
                    <div class="bg-white space-y-2 p-4 rounded-lg shadow">
                        <p class="text-sm text-gray-600">Total Claims</p>
                        <p class="text-3xl font-semibold text-gray-300">{{ $claims->count() }}</p>
                    </div>
                    <div class="bg-white space-y-2 p-4 rounded-lg shadow">
                        <p class="text-sm text-gray-600">Pending Claims</p>
                        <p class="text-3xl font-semibold text-gray-300">{{ $claims->where('status', '!=', Claim::STATUS_DONE)->count() }}</p>
                    </div>
                    <div class="bg-white space-y-2 p-4 rounded-lg shadow">
                        <p class="text-sm text-gray-600">Approved Claims</p>
                        <p class="text-3xl font-semibold text-gray-300">{{ $claims->where('status', Claim::STATUS_APPROVED_FINANCE)->count() }}</p>
                    </div>
                    <div class="bg-white space-y-2 p-4 rounded-lg shadow">
                        <p class="text-sm text-gray-600">Total Amount Claimed</p>
                        <p class="text-3xl font-semibold text-gray-300">RM {{ number_format($claims->sum('petrol_amount') + $claims->sum('toll_amount'), 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="flex-col flex gap-4">
                <div class="overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-white uppercase bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3">Submitted Date</th>
                                <th scope="col" class="px-6 py-3">Company</th>
                                <th scope="col" class="px-6 py-3">Claim Type</th>
                                <th scope="col" class="px-6 py-3">Title</th>
                                <th scope="col" class="px-6 py-3">Total Amount (RM)</th>
                                <th scope="col" class="px-6 py-3">Status</th>
                                <th scope="col" class="px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($claims as $claim)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4">{{ $claim->submitted_at->format('d-m-Y') }}</td>
                                    <td class="px-6 py-4">{{ $claim->claim_company }}</td>
                                    <td class="px-6 py-4">{{ $claim->claim_type }}</td>
                                    <td class="px-6 py-4">{{ $claim->title }}</td>
                                    <td class="px-6 py-4">{{ number_format($claim->petrol_amount + $claim->toll_amount, 2) }}</td>
                                    <td class="px-6 py-4">
                                        <span class="claims-dashboard-status-badge inline-flex items-center justify-center w-full py-2 px-4 rounded font-bold text-white text-xs
                                            @if ($claim->status == Claim::STATUS_SUBMITTED)
                                                bg-orange-500
                                            @elseif ($claim->status == Claim::STATUS_APPROVED_ADMIN)
                                                bg-yellow-500
                                            @elseif ($claim->status == Claim::STATUS_APPROVED_DATUK)
                                                bg-blue-500
                                            @elseif ($claim->status == Claim::STATUS_APPROVED_HR)
                                                bg-purple-500
                                            @elseif ($claim->status == Claim::STATUS_APPROVED_FINANCE)
                                                bg-indigo-500
                                            @elseif ($claim->status == Claim::STATUS_REJECTED)
                                                bg-red-500
                                            @elseif ($claim->status == Claim::STATUS_DONE)
                                                bg-green-500
                                            @endif
                                        ">
                                            {{ ucfirst(str_replace('_', ' ', $claim->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('claims.claim', $claim->id) }}" class="text-blue-500 hover:text-blue-700 font-medium">View Details</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        @endif
    </div>
    @endauth

    @guest
        <script>window.location.href = "{{ route('login') }}";</script>
    @endguest
</x-layout>
