<x-app-layout>
    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 md:px-8">
            <h1 class="text-2xl font-semibold text-gray-900">Bulk Email Claims to Datuk</h1>
            <p class="mt-1 text-sm text-gray-500">Send multiple HR-approved claims to Datuk for review.</p>
        </div>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 md:px-8">
            <div class="py-4">
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-black/5">
                    @if($claims->isEmpty())
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No Claims Ready</h3>
                            <p class="mt-1 text-sm text-gray-500">There are no HR-approved claims ready to be sent to Datuk.</p>
                            @if(config('app.debug'))
                                <div class="mt-4 text-xs text-gray-500">
                                    <p>Debug Info:</p>
                                    <p>User Role ID: {{ Auth::user()->role_id }}</p>
                                    <p>Expected Status: {{ App\Models\Claim::STATUS_APPROVED_HR }}</p>
                                    <p>Total Claims in DB: {{ App\Models\Claim::count() }}</p>
                                    <p>Claims with HR Approval: {{ App\Models\Claim::where('status', App\Models\Claim::STATUS_APPROVED_HR)->count() }}</p>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h2 class="text-lg font-medium text-gray-900">Select Claims to Send</h2>
                                    <p class="mt-1 text-sm text-gray-500">Choose the HR-approved claims you want to send to Datuk for review.</p>
                                </div>
                                <button
                                    onclick="sendSelectedClaims()"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    Send Selected Claims
                                </button>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <input type="checkbox" id="select-all" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                ID
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Employee
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Title
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Amount
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Submitted Date
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($claims as $claim)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <input type="checkbox" name="claims[]" value="{{ $claim->id }}" class="claim-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $claim->id }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-10 w-10">
                                                            <x-profile.profile-picture :user="$claim->user" size="md" />
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900">
                                                                {{ $claim->user->first_name }} {{ $claim->user->second_name }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $claim->title }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    RM {{ number_format($claim->petrol_amount + $claim->toll_amount, 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $claim->submitted_at->format('d M Y') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('select-all').addEventListener('change', function() {
            document.querySelectorAll('.claim-checkbox').forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        function sendSelectedClaims() {
            const selectedClaims = Array.from(document.querySelectorAll('.claim-checkbox:checked')).map(cb => cb.value);
            
            if (selectedClaims.length === 0) {
                alert('Please select at least one claim to send.');
                return;
            }

            fetch('{{ route("bulk-email.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ claims: selectedClaims })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while sending the claims.');
            });
        }
    </script>
    @endpush
</x-app-layout> 