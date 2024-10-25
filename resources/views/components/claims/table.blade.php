<table class="min-w-full divide-y divide-gray-200">
    <tbody class="bg-white divide-y divide-gray-200">
        @foreach ($rows as $row)
            <tr>
                <th class="table-horizontal-header">{{ $row['label'] }}</th>
                <td class="table-horizontal-item">
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
