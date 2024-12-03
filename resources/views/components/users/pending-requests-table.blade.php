@props(['requests'])

<div class="overflow-hidden rounded-lg bg-white">
    @if ($requests->isEmpty())
        <div class="py-8 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-2 text-lg font-semibold text-gray-900">No registration requests found</h3>
            <p class="mt-1 text-sm text-gray-500">There are no pending registration requests at the moment.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" id="requestsTable">
                <thead class="bg-gray-50">
                    <tr class="*:text-xs *:font-medium *:text-gray-600">
                        <th class="w-16 px-3 py-2 text-left" data-sort="id" scope="col">
                            <div class="flex cursor-pointer items-center gap-1">
                                ID
                                <i class="fas fa-sort ml-1 opacity-60"></i>
                            </div>
                        </th>
                        <th class="px-3 py-2 text-left" data-sort="name" scope="col">
                            <div class="flex cursor-pointer items-center gap-1">
                                Name
                                <i class="fas fa-sort ml-1 opacity-60"></i>
                            </div>
                        </th>
                        <th class="hidden px-3 py-2 text-left sm:table-cell" data-sort="email" scope="col">
                            <div class="flex cursor-pointer items-center gap-1">
                                Email
                                <i class="fas fa-sort ml-1 opacity-60"></i>
                            </div>
                        </th>
                        <th class="hidden px-3 py-2 text-left md:table-cell" data-sort="department" scope="col">
                            <div class="flex cursor-pointer items-center gap-1">
                                Department
                                <i class="fas fa-sort ml-1 opacity-60"></i>
                            </div>
                        </th>
                        <th class="w-24 px-3 py-2 text-left" data-sort="status" scope="col">
                            <div class="flex cursor-pointer items-center gap-1">
                                Status
                                <i class="fas fa-sort ml-1 opacity-60"></i>
                            </div>
                        </th>
                        <th class="w-24 px-3 py-2 text-right" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach ($requests as $request)
                        <tr class="*:text-xs *:text-gray-500 hover:bg-gray-50/50">
                            <td class="whitespace-nowrap px-3 py-3">{{ $request->id }}</td>
                            <td class="whitespace-nowrap px-3 py-3">
                                {{ $request->first_name }} {{ $request->last_name }}
                            </td>
                            <td class="hidden whitespace-nowrap px-3 py-3 sm:table-cell">
                                {{ $request->email }}
                            </td>
                            <td class="hidden whitespace-nowrap px-3 py-3 md:table-cell">
                                {{ $request->department }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-3">
                                <span
                                    class="{{ $request->status === 'pending'
                                        ? 'bg-yellow-100 text-yellow-700'
                                        : ($request->status === 'approved'
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-red-100 text-red-700') }} inline-flex items-center rounded-full px-2 py-1 text-xs">
                                    {{ ucfirst($request->status) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-3 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    @if ($request->status === 'pending')
                                        <button class="text-xs font-medium text-green-500 hover:text-green-700"
                                            onclick="handleRegistrationRequest({{ $request->id }}, 'approve')">
                                            Approve
                                        </button>
                                        <button class="text-xs font-medium text-red-500 hover:text-red-700"
                                            onclick="handleRegistrationRequest({{ $request->id }}, 'reject')">
                                            Reject
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof initializeTableSorting === 'function') {
                initializeTableSorting();
            } else {
                console.error('Table sorting functionality not loaded');
            }
        });
    </script>
@endpush
