@props(['claim'])

<div class="bg-white rounded-lg shadow-sm ring-1 ring-black/5 animate-slide-in delay-200">
    <div class="px-6 py-5">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Toll Details</h3>
                <p class="text-sm text-gray-500 mt-1">Toll information and related documents</p>
            </div>
        </div>

        <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="flex flex-col p-4 bg-gray-50 rounded-lg">
                <dt class="text-sm font-medium text-gray-500">Toll Amount</dt>
                <dd class="mt-1 text-sm text-gray-900">RM {{ number_format($claim->toll_amount, 2) }}</dd>
            </div>

            <div class="flex flex-col p-4 bg-gray-50 rounded-lg">
                <dt class="text-sm font-medium text-gray-500">Documents</dt>
                <dd class="mt-1 space-y-2">
                    @if($claim->documents->first()?->toll_file_name)
                        <x-claims.document-link 
                            :route="route('claims.view.document', ['claim' => $claim->id, 'type' => 'toll', 'filename' => $claim->documents->first()->toll_file_name])"
                            label="Toll Receipt"
                        />
                    @endif
                    @if($claim->documents->first()?->email_file_name)
                        <x-claims.document-link 
                            :route="route('claims.view.document', ['claim' => $claim->id, 'type' => 'email', 'filename' => $claim->documents->first()->email_file_name])"
                            label="Email Approval"
                        />
                    @endif
                </dd>
            </div>
        </dl>
    </div>
</div>