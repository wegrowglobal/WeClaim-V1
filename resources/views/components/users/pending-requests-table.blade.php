@props(['requests'])

<div class="overflow-hidden rounded-lg bg-white">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200" id="requestsTable">
            <thead class="bg-gray-50 text-xs">
                <tr>
                    <th class="w-16 px-3 py-2 text-left font-medium text-gray-500" scope="col">
                        <div class="flex cursor-pointer items-center gap-1">
                            ID
                            <i class="fas fa-sort ml-1 opacity-60"></i>
                        </div>
                    </th>
                    <th class="w-32 px-3 py-2 text-left font-medium text-gray-500" scope="col">
                        <div class="flex cursor-pointer items-center gap-1">
                            First Name
                            <i class="fas fa-sort ml-1 opacity-60"></i>
                        </div>
                    </th>
                    <th class="w-32 px-3 py-2 text-left font-medium text-gray-500" scope="col">
                        <div class="flex cursor-pointer items-center gap-1">
                            Last Name
                            <i class="fas fa-sort ml-1 opacity-60"></i>
                        </div>
                    </th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500" scope="col">
                        <div class="flex cursor-pointer items-center gap-1">
                            Email
                            <i class="fas fa-sort ml-1 opacity-60"></i>
                        </div>
                    </th>
                    <th class="w-32 px-3 py-2 text-left font-medium text-gray-500" scope="col">
                        <div class="flex cursor-pointer items-center gap-1">
                            Department
                            <i class="fas fa-sort ml-1 opacity-60"></i>
                        </div>
                    </th>
                    <th class="w-24 px-3 py-2 text-left font-medium text-gray-500" scope="col">Status</th>
                    <th class="w-24 px-3 py-2 text-right font-medium text-gray-500" scope="col">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse ($requests as $request)
                    <tr class="text-xs hover:bg-gray-50/50">
                        <td class="whitespace-nowrap px-3 py-3 text-gray-500">{{ $request->id }}</td>
                        <td class="whitespace-nowrap px-3 py-3 text-gray-500">{{ $request->first_name }}</td>
                        <td class="whitespace-nowrap px-3 py-3 text-gray-500">{{ $request->last_name }}</td>
                        <td class="px-3 py-3 text-gray-500">{{ $request->email }}</td>
                        <td class="whitespace-nowrap px-3 py-3 text-gray-500">{{ $request->department }}</td>
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
                                    <button class="text-xs font-medium text-green-600 hover:text-green-800"
                                        onclick="handleRegistrationRequest({{ $request->id }}, 'approve')">
                                        Approve
                                    </button>
                                    <button class="text-xs font-medium text-red-600 hover:text-red-800"
                                        onclick="handleRegistrationRequest({{ $request->id }}, 'reject')">
                                        Reject
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-3 py-8 text-center text-gray-500" colspan="7">
                            No registration requests found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
