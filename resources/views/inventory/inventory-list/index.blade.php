@extends('layout.index')
@section('title', 'Inventory List')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <form action="{{ url()->current() }}" method="GET">
                        <div class="row">
                            <div class="col-2">
                                <label class="form-label">SKU</label>
                                <input type="text" class="form-control" name="sku" value="{{ request()->get('sku') }}"
                                    placeholder="SKU ...">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Asset</th>
                                    <th>Part Name</th>
                                    <th>Part Number</th>
                                    <th>Part Desc</th>
                                    <th>Parent SN</th>
                                    <th>Serial Number</th>
                                    <th>Status</th>
                                    <th>Storage</th>
                                    <th>Last Staging Date</th>
                                    <th>Last Movement Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($inventory as $item)
                                    <tr>
                                        <td>{{ $loop->iteration + ($inventory->currentPage() - 1) * $inventory->perPage() }}
                                        </td>
                                        <td>{{ $item->unique_id }}</td>
                                        <td>{{ $item->part_name }}</td>
                                        <td>{{ $item->part_number }}</td>
                                        <td>{{ $item->part_description }}</td>
                                        <td>{{ $item->parent_serial_number }}</td>
                                        <td>{{ $item->serial_number }}</td>
                                        <td>
                                            @if ($item->status == 'In Stock')
                                                <span class="badge bg-success">{{ $item->status }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $item->status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($item->storageLevel)
                                                {{ $item->storageLevel->bin->rak->zone->name }} -
                                                {{ $item->storageLevel->bin->rak->name }} -
                                                {{ $item->storageLevel->bin->name }} -
                                                {{ $item->storageLevel->name }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $item->last_staging_date ? \Carbon\Carbon::parse($item->last_staging_date)->format('d/m/Y H:i') : '' }}
                                        </td>
                                        <td>{{ $item->last_movement_date ? \Carbon\Carbon::parse($item->last_movement_date)->format('d/m/Y H:i') : '' }}
                                        </td>
                                        <td>
                                            <a href="{{ route('inventory.show', $item->id) }}"
                                                class="btn btn-sm btn-info text-white">
                                                <i class="bi bi-info-circle mr-1"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">No data found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $inventory->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
