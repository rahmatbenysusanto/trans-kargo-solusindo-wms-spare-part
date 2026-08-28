@extends('layout.index')
@section('title', 'Relokasi Outbound')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single {
            border: 1px solid #dbdade !important;
            border-radius: 0.375rem !important;
            height: 38px !important;
            display: flex !important;
            align-items: center !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #6f6b7d !important;
            padding-left: 0.9rem !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }
        .route-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
    </style>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.select2').select2({ placeholder: "-- Pilih Client --", allowClear: true, width: '100%' });
        });

        function cancelRelokasi(id, ref) {
            Swal.fire({
                title: 'Batalkan Relokasi?',
                text: `Yakin ingin membatalkan Relokasi ${ref}? Status barang akan dikembalikan ke kondisi sebelumnya.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, batalkan!',
                cancelButtonText: 'Tidak',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetch('{{ route('outbound.relokasi.cancel') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ id })
                    }).then(r => r.json()).catch(e => Swal.showValidationMessage(`Error: ${e}`));
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then(result => {
                if (result.isConfirmed) {
                    if (result.value.status) {
                        Swal.fire('Dibatalkan!', 'Relokasi berhasil dibatalkan.', 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', result.value.message || 'Gagal membatalkan relokasi.', 'error');
                    }
                }
            });
        }
    </script>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="fw-bold mb-1"><i class="ti tabler-map-route me-2 text-primary"></i>Relokasi Outbound</h4>
                    <p class="text-muted mb-0 small">Daftar perpindahan barang antar site (multi-hop relocation)</p>
                </div>
                <a href="{{ route('outbound.relokasi.create') }}" class="btn btn-primary d-flex align-items-center shadow-sm px-4">
                    <i class="ti tabler-plus me-2 fs-5"></i> Buat Relokasi
                </a>
            </div>

            <div class="card mb-4">
                <div class="card-header border-bottom">
                    <form action="{{ url()->current() }}" method="GET">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label fw-bold small">Global Search</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti tabler-search"></i></span>
                                    <input type="text" class="form-control" name="search"
                                        value="{{ request()->get('search') }}"
                                        placeholder="Cari Ref#, SN, From/To Site...">
                                    <button class="btn btn-primary" type="submit">Filter</button>
                                    @if(request('search'))
                                        <a href="{{ route('outbound.relokasi.index') }}" class="btn btn-outline-secondary">Reset</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-sm text-nowrap">
                        <thead class="table-light">
                            <tr>
                                <th width="30">#</th>
                                <th>Tanggal</th>
                                <th>Ref#</th>
                                <th>Rute Relokasi</th>
                                <th class="text-center">Qty</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data as $item)
                                <tr>
                                    <td>{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold text-dark">{{ $item->outbound_date ? \Carbon\Carbon::parse($item->outbound_date)->format('d/m/Y') : '-' }}</span>
                                            <small class="text-muted" style="font-size: 0.65rem;">By: {{ $item->outbound_by }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold text-primary small">{{ $item->tks_dn_number ?? '-' }}</span>
                                            @if ($item->number)
                                                <small class="text-muted" style="font-size: 0.65rem;">{{ $item->number }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="route-badge">
                                            <span class="badge bg-label-secondary">{{ $item->from_address ?? 'WH Utama' }}</span>
                                            <i class="ti tabler-arrow-right text-primary fs-6"></i>
                                            <span class="badge bg-label-primary">{{ $item->pickup_address ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-label-secondary fw-bold px-2">{{ $item->qty }}</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $item->status == 'cancel' ? 'bg-label-danger' : 'bg-label-success' }}">
                                            {{ strtoupper($item->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-center">
                                            <a href="{{ route('outbound.relokasi.show', $item->id) }}"
                                                class="btn btn-icon btn-sm btn-label-primary" title="Lihat Detail">
                                                <i class="ti tabler-eye fs-5"></i>
                                            </a>
                                            @if ($item->status !== 'cancel')
                                                <button type="button" class="btn btn-icon btn-sm btn-label-danger"
                                                    onclick="cancelRelokasi({{ $item->id }}, '{{ $item->tks_dn_number ?? $item->number }}')"
                                                    title="Batalkan Relokasi">
                                                    <i class="ti tabler-x fs-5"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="ti tabler-map-route text-muted mb-2" style="font-size: 3rem;"></i>
                                            <p class="text-muted mb-0">Belum ada data relokasi.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 px-3">
                    {{ $data->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
