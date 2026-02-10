@extends('layout.index')
@section('title', 'Inventory List')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <form action="{{ url()->current() }}" method="GET">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Global Search</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search"
                                        value="{{ request()->get('search') }}"
                                        placeholder="Search SN, Asset#, Part Name ...">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="ti tabler-search"></i>
                                    </button>
                                </div>
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
                                    <th>Warehouse Location / Rack ID</th>
                                    <th>Part Name</th>
                                    <th>Part Number / SKU</th>
                                    <th>Part Description</th>
                                    <th>Serial Number (SN)</th>
                                    <th>Parent SN</th>
                                    <th>Asset#</th>
                                    <th>Status Stock / Asset</th>
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
                                        <td>
                                            @if ($item->storageLevel)
                                                <span class="text-primary fw-bold">
                                                    {{ $item->storageLevel->bin->rak->zone->name }} -
                                                    {{ $item->storageLevel->bin->rak->name }} -
                                                    {{ $item->storageLevel->bin->name }} -
                                                    {{ $item->storageLevel->name }}
                                                </span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->part_name }}</td>
                                        <td>{{ $item->part_number }}</td>
                                        <td>{{ $item->part_description }}</td>
                                        <td><span class="badge bg-label-info">{{ $item->serial_number }}</span></td>
                                        <td>{{ $item->parent_serial_number ?? '-' }}</td>
                                        <td><span class="text-dark fw-medium">{{ $item->unique_id }}</span></td>
                                        <td>
                                            @if ($item->status == 'In Stock' || $item->status == 'Available')
                                                <span class="badge bg-success">{{ $item->status }}</span>
                                            @elseif(in_array($item->status, ['Defective', 'Broken', 'Faulty']))
                                                <span class="badge bg-danger">{{ $item->status }}</span>
                                            @else
                                                <span class="badge bg-warning">{{ $item->status }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->last_staging_date ? \Carbon\Carbon::parse($item->last_staging_date)->format('d/m/Y H:i') : '-' }}
                                        </td>
                                        <td>{{ $item->last_movement_date ? \Carbon\Carbon::parse($item->last_movement_date)->format('d/m/Y H:i') : '-' }}
                                        </td>
                                        <td>
                                            <a href="{{ route('inventory.show', $item->id) }}"
                                                class="btn btn-primary btn-sm d-flex align-items-center justify-content-center">
                                                <i class="ti tabler-info-circle me-1"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center py-5 text-muted">No inventory records found.
                                        </td>
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
