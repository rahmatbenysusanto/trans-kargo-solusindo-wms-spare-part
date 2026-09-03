@forelse ($suggestions as $inv)
    <tr>
        <td style="width:40px;">
            <input type="checkbox" class="suggestion-check" value="{{ $inv->id }}">
        </td>
        <td>
            <span class="badge bg-label-secondary font-monospace" style="font-size:0.7rem;">{{ $inv->serial_number }}</span>
            <div class="small text-muted text-mono mt-1">{{ $inv->unique_id }}</div>
        </td>
        <td>
            <div class="fw-semibold small">{{ $inv->part_name }}</div>
            <div class="small text-muted text-mono">{{ $inv->part_number ?: '-' }}</div>
        </td>
        <td>
            @php
                $condClass = match ($inv->condition) {
                    'New', 'Refurbished', 'Good' => 'bg-label-success',
                    'Faulty', 'Write-off Needed' => 'bg-label-danger',
                    default => 'bg-label-info'
                };
            @endphp
            <span class="badge {{ $condClass }}" style="font-size:0.7rem;">{{ $inv->condition ?? '-' }}</span>
        </td>
        <td style="max-width: 320px;">
            <span class="small text-muted text-wrap">{{ $inv->reason }}</span>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="text-center py-4 text-muted">
            <i class="ti tabler-bulb fs-1 d-block mb-2 opacity-50"></i>
            No related units found from lineage data. Use manual search to add members.
        </td>
    </tr>
@endforelse
