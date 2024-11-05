@php
    use App\Models\Claim;
@endphp

@props(['claims', 'claimService', 'actions', 'rows'])

<div class="bg-white overflow-x-auto">
    <div class="inline-block min-w-full">
        <div class="overflow-hidden">
            <table id="claimsTable" class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="table-header whitespace-nowrap w-fit cursor-pointer" data-sort="id">
                            ID <i class="fas fa-sort ml-2"></i>
                        </th>
                        <th scope="col" class="table-header hidden sm:table-cell whitespace-nowrap w-fit cursor-pointer" data-sort="submitted">
                            Submitted <i class="fas fa-sort ml-2"></i>
                        </th>
                        <th scope="col" class="table-header whitespace-nowrap w-fit cursor-pointer" data-sort="user">
                            Submitted By <i class="fas fa-sort ml-2"></i>
                        </th>
                        <th scope="col" class="table-header whitespace-nowrap w-fit cursor-pointer" data-sort="title">
                            Title <i class="fas fa-sort ml-2"></i>
                        </th>
                        <th scope="col" class="table-header hidden md:table-cell whitespace-nowrap w-fit cursor-pointer" data-sort="dateFrom">
                            Date From <i class="fas fa-sort ml-2"></i>
                        </th>
                        <th scope="col" class="table-header hidden md:table-cell whitespace-nowrap w-fit cursor-pointer" data-sort="dateTo">
                            Date To <i class="fas fa-sort ml-2"></i>
                        </th>
                        <th scope="col" class="table-header whitespace-nowrap w-fit cursor-pointer" data-sort="status">
                            Status <i class="fas fa-sort"></i>
                        </th>
                        <th scope="col" class="table-header whitespace-nowrap w-fit">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($claims as $claim)
                        <tr class="claim-row" data-need-review="{{ $claimService->canReviewClaim(Auth::user(), $claim) ? 'true' : 'false' }}">
                            <td class="table-item">{{ $claim->id }}</td>
                            <td class="table-item hidden sm:table-cell">{{ $claim->submitted_at->format('d-m-Y') }}</td>
                            <td class="table-item">
                                <div class="flex items-center">
                                    @if($claim->user->profile_picture && Storage::disk('public')->exists($claim->user->profile_picture))
                                        <img src="{{ Storage::url($claim->user->profile_picture) }}" alt="Profile Picture" class="h-8 w-8 rounded-full mr-2 object-cover">
                                    @else
                                        <div class="h-8 w-8 rounded-full mr-2 flex items-center justify-center text-white font-medium text-lg" style="background-color: {{ '#' . substr(md5($claim->user->first_name), 0, 6) }}">
                                            {{ strtoupper(substr($claim->user->first_name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <span class="hidden sm:inline">{{ $claim->user->first_name . ' ' . $claim->user->second_name }}</span>
                                    <span class="sm:hidden">{{ $claim->user->first_name }}</span>
                                </div>
                            </td>
                            <td class="table-item">
                                <div class="max-w-xs truncate">{{ $claim->title }}</div>
                            </td>
                            <td class="table-item hidden md:table-cell">{{ $claim->date_from->format('d-m-Y') }}</td>
                            <td class="table-item hidden md:table-cell">{{ $claim->date_to->format('d-m-Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="status-badge inline-block w-fit text-center
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
                                    @if ($claimService->canReviewClaim(Auth::user(), $claim))
                                        @if ($claim->status == Claim::STATUS_APPROVED_FINANCE)
                                            <form action="{{ route('claims.approve', $claim->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-900">Mark as Done</button>
                                            </form>
                                        @else
                                            <a href="{{ route('claims.review', $claim->id) }}" class="text-blue-600 hover:text-blue-900">Start Review</a>
                                        @endif
                                    @else
                                        <span class="text-gray-400">Pending Other Review</span>
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
    </div>
</div>
