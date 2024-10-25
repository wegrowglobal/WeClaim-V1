@php
    use App\Models\Claim;
@endphp

<span class="status-badge
    @if ($status == Claim::STATUS_SUBMITTED)
        bg-orange-100 text-orange-800
    @elseif ($status == Claim::STATUS_APPROVED_ADMIN)
        bg-yellow-100 text-yellow-800
    @elseif ($status == Claim::STATUS_APPROVED_DATUK)
        bg-blue-100 text-blue-800
    @elseif ($status == Claim::STATUS_APPROVED_HR)
        bg-purple-100 text-purple-800
    @elseif ($status == Claim::STATUS_APPROVED_FINANCE)
        bg-indigo-100 text-indigo-800
    @elseif ($status == Claim::STATUS_REJECTED)
        bg-red-100 text-red-800
    @elseif ($status == Claim::STATUS_DONE)
        bg-green-100 text-green-800
    @endif
">
    {{ str_replace('_', ' ', $status) }}
</span>