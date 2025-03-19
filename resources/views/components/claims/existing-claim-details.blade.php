@props(['claim'])

<div class="bg-white rounded-lg shadow-sm ring-1 ring-black/5">
    <div class="p-6">
        <div class="space-y-6">
            @foreach($claim->getDetails() as $detail)
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500 sm:w-1/4">{{ $detail['label'] }}</dt>
                    <dd class="text-sm text-gray-900 sm:w-3/4">
                        @if(isset($detail['type']) && $detail['type'] === 'document')
                            <x-claims.document-link :claim="$claim" :type="$detail['documentType']" />
                        @else
                            {{ $detail['value'] }}
                        @endif
                    </dd>
                </div>
            @endforeach
        </div>
    </div>
</div>