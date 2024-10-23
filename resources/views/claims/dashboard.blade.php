@php
use App\Models\Claim;
@endphp
<x-layout>
    <div class="max-w-full-custom border border-wgg-border">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-10 space-y-4">
                <h2 class="heading-1">Your Claims Dashboard</h2>

                @if ($claims->isEmpty())
                    <p class="text-left text-wgg-black-400 text-xl">No claims found. Start by submitting a new claim.</p>
                @else
                    <!-- Claims Statistics -->
                    <div class="space-y-4">
                        <h3 class="heading-2">Claims Overview</h3>
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                            <div class="bg-white p-6 rounded-lg border border-gray-200 transition-all duration-300 hover:shadow-md">
                                <p class="text-sm font-medium text-gray-500 mb-2">Total Claims</p>
                                <p class="text-3xl font-bold text-blue-600">{{ $claims->count() }}</p>
                            </div>
                            <div class="bg-white p-6 rounded-lg border border-gray-200 transition-all duration-300 hover:shadow-md">
                                <p class="text-sm font-medium text-gray-500 mb-2">Pending Claims</p>
                                <p class="text-3xl font-bold text-yellow-600">{{ $claims->where('status', '!=', Claim::STATUS_DONE)->count() }}</p>
                            </div>
                            <div class="bg-white p-6 rounded-lg border border-gray-200 transition-all duration-300 hover:shadow-md">
                                <p class="text-sm font-medium text-gray-500 mb-2">Pending Amount</p>
                                <p class="text-3xl font-bold text-orange-600">RM {{ number_format($claims->where('status', '!=', Claim::STATUS_DONE)->sum(function($claim) { return $claim->petrol_amount + $claim->toll_amount; }), 2) }}</p>
                            </div>
                            <div class="bg-white p-6 rounded-lg border border-gray-200 transition-all duration-300 hover:shadow-md">
                                <p class="text-sm font-medium text-gray-500 mb-2">Approved Amount</p>
                                <p class="text-3xl font-bold text-green-600">RM {{ number_format($claims->where('status', Claim::STATUS_DONE)->sum(function($claim) { return $claim->petrol_amount + $claim->toll_amount; }), 2) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Claims Table -->
                    <div class="space-y-4">
                        <h3 class="heading-2">Claims List</h3>
                        <div class="overflow-x-auto">
                            <div class="bg-white shadow overflow-hidden">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="table-header">Submitted Date</th>
                                            <th scope="col" class="table-header">Company</th>
                                            <th scope="col" class="table-header">Claim Type</th>
                                            <th scope="col" class="table-header">Title</th>
                                            <th scope="col" class="table-header">Total Amount (RM)</th>
                                            <th scope="col" class="table-header">Status</th>
                                            <th scope="col" class="table-header">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($claims as $claim)
                                            <tr>
                                                <td class="table-item">{{ $claim->submitted_at->format('d-m-Y') }}</td>
                                                <td class="table-item">{{ $claim->claim_company }}</td>
                                                <td class="table-item">{{ $claim->claim_type }}</td>
                                                <td class="table-item">{{ $claim->title }}</td>
                                                <td class="table-item">{{ number_format($claim->petrol_amount + $claim->toll_amount, 2) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="status-badge
                                                        @if ($claim->status == Claim::STATUS_SUBMITTED)
                                                            bg-orange-100 text-orange-800
                                                        @elseif ($claim->status == Claim::STATUS_APPROVED_ADMIN)
                                                            bg-yellow-100 text-yellow-800
                                                        @elseif ($claim->status == Claim::STATUS_APPROVED_DATUK)
                                                            bg-blue-100 text-blue-800
                                                        @elseif ($claim->status == Claim::STATUS_APPROVED_HR)
                                                            bg-purple-100 text-purple-800
                                                        @elseif ($claim->status == Claim::STATUS_APPROVED_FINANCE)
                                                            bg-indigo-100 text-indigo-800
                                                        @elseif ($claim->status == Claim::STATUS_REJECTED)
                                                            bg-red-100 text-red-800
                                                        @elseif ($claim->status == Claim::STATUS_DONE)
                                                            bg-green-100 text-green-800
                                                        @endif
                                                    ">
                                                        {{ str_replace('_', ' ', $claim->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('claims.claim', $claim->id) }}" class="text-blue-600 hover:text-blue-900">View Details</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layout>
