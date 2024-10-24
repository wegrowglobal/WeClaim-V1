<table class="min-w-full divide-y divide-gray-200">
    <tbody class="bg-white divide-y divide-gray-200">
        @foreach ($rows as $row)
            <tr>
                <th class="table-horizontal-header">{{ $row['label'] }}</th>
                <td class="table-horizontal-item">{{ $row['value'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>