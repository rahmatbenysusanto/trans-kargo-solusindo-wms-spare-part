@extends('layout.index')
@section('title', 'Invoice Management')

@section('content')
    <div class="row">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold mb-0">Invoice Management</h4>
                <p class="text-muted small mb-0">Manage and track official invoices connected to transactions.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('invoice.export-excel') }}" class="btn btn-label-success">
                    <i class="ti tabler-file-spreadsheet me-1"></i> Export Excel
                </a>
                <a href="{{ route('invoice.create') }}" class="btn btn-primary">
                    <i class="ti tabler-plus me-1"></i> Add New Invoice
                </a>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body border-bottom">
                    <form action="{{ route('invoice.index') }}" method="GET">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Search</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti tabler-search"></i></span>
                                    <input type="text" name="search" class="form-control"
                                        placeholder="Invoice # or Ref #" value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-secondary w-100">Filter</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="table-light">
                            <tr>
                                <th width="50">#</th>
                                <th>Invoice Number</th>
                                <th>Date</th>
                                <th>Linked References</th>
                                <th>Amount</th>
                                <th>Attachment</th>
                                <th class="text-center" width="150">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $invoice)
                                <tr>
                                    <td>{{ ($invoices->currentPage() - 1) * $invoices->perPage() + $loop->iteration }}</td>
                                    <td><span class="fw-bold text-dark">{{ $invoice->invoice_number }}</span></td>
                                    <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}</td>
                                    <td>
                                        @foreach ($invoice->inbounds as $in)
                                            <a href="{{ route('receiving.show', $in->id) }}"
                                                class="badge bg-label-info mb-1 d-inline-block">
                                                IN: {{ $in->number }}
                                            </a>
                                        @endforeach
                                        @foreach ($invoice->outbounds as $out)
                                            <a href="{{ route('outbound.show', $out->id) }}"
                                                class="badge bg-label-warning mb-1 d-inline-block">
                                                OUT: {{ $out->number }}
                                            </a>
                                        @endforeach
                                    </td>
                                    <td class="fw-bold text-primary text-nowrap">IDR
                                        {{ number_format($invoice->amount, 0, ',', '.') }}</td>
                                    <td>
                                        @if ($invoice->file_path)
                                            <a href="{{ asset('storage/' . $invoice->file_path) }}" target="_blank"
                                                class="btn btn-xs btn-label-primary">
                                                <i class="ti tabler-paperclip me-1"></i> File
                                            </a>
                                        @else
                                            <span class="text-muted small">No File</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="{{ route('invoice.print', $invoice->id) }}" target="_blank"
                                                class="btn btn-xs btn-label-info" title="Print PDF">
                                                <i class="ti tabler-printer fs-6"></i>
                                            </a>
                                            <button class="btn btn-xs btn-label-danger"
                                                onclick="deleteInvoice({{ $invoice->id }})" title="Delete">
                                                <i class="ti tabler-trash fs-6"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No invoices found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex justify-content-end">
                    {{ $invoices->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        function deleteInvoice(id) {
            Swal.fire({
                title: 'Delete this Invoice?',
                text: "This action cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ea5455',
                cancelButtonColor: '#82868b',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`{{ url('invoice') }}/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status) {
                                Swal.fire('Deleted!', 'Invoice has been deleted.', 'success').then(() => {
                                    location.reload();
                                });
                            }
                        });
                }
            });
        }
    </script>
@endsection
