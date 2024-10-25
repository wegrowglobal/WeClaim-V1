@php
    use App\Models\Claim;
@endphp

@props(['claims', 'claimService', 'actions', 'rows'])

<div class="bg-white overflow-hidden">
    <table id="claimsTable" class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="table-header">ID</th>
                <th scope="col" class="table-header">Submitted</th>
                <th scope="col" class="table-header">Submitted By</th>
                <th scope="col" class="table-header">Title</th>
                <th scope="col" class="table-header">Date From</th>
                <th scope="col" class="table-header">Date To</th>
                <th scope="col" class="table-header">Status</th>
                <th scope="col" class="table-header">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach ($claims as $claim)
                <tr class="claim-row" data-need-review="{{ $claimService->canReviewClaim(Auth::user(), $claim) ? 'true' : 'false' }}">
                    <td class="table-item">{{ $claim->id }}</td>
                    <td class="table-item">{{ $claim->submitted_at->format('d-m-Y') }}</td>
                    <td class="table-item">
                        <div class="flex items-center">
                            @if($claim->user->profile_picture && Storage::disk('public')->exists(auth()->user()->profile_picture))
                                <img src="{{ Storage::url('public/' . $claim->user->profile_picture) }}" alt="Profile Picture" class="h-8 w-8 rounded-full mr-2 object-cover">
                            @else
                                <div class="h-8 w-8 rounded-full mr-2 flex items-center justify-center text-white font-medium text-lg" style="background-color: {{ '#' . substr(md5($claim->user->first_name), 0, 6) }}">
                                    {{ strtoupper(substr($claim->user->first_name, 0, 1)) }}
                                </div>
                            @endif
                            {{ $claim->user->first_name . ' ' . $claim->user->second_name }}
                        </div>
                    </td>
                    <td class="table-item">{{ $claim->title }}</td>
                    <td class="table-item">{{ $claim->date_from->format('d-m-Y') }}</td>
                    <td class="table-item">{{ $claim->date_to->format('d-m-Y') }}</td>
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
                        @if ($actions === 'approval')
                            @if ($claim->status == Claim::STATUS_APPROVED_FINANCE)
                                <form action="{{ route('claims.approve', $claim->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-900">Mark as Done</button>
                                </form>
                            @else
                                <a href="{{ route('claims.review', $claim->id) }}" class="text-blue-600 hover:text-blue-900">Start Review</a>
                            @endif
                        @elseif ($actions === 'dashboard')
                            <a href="{{ route('claims.view', $claim->id) }}" class="text-blue-600 hover:text-blue-900">View</a>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>