<table class="min-w-full divide-y divide-wgg-border-200">
    <tbody class="bg-white divide-y divide-wgg-border-200">
        @foreach ($rows as $row)
            <tr class="">
                <th class="table-header whitespace-nowrap w-fit">{{ $row['label'] }}</th>
                <td class="table-item">
                    @if (isset($row['component']))
                        <x-dynamic-component :component="$row['component']" :attributes="$row['attributes'] ?? []">
                            {{ $row['label'] }}
                        </x-dynamic-component>
                    @else
                        {{ $row['value'] }}
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
