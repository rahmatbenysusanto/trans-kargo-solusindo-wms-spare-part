@extends('layout.index')
@section('title', 'Detail Relokasi')

@section('content')
<div class="row">
    <!-- Header Action -->
    <div class="col-12 mb-4">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="ti tabler-map-route me-2 text-primary"></i>
                    Detail Relokasi: <span class="text-primary">{{ $outbound->tks_dn_number ?? $outbound->number }}</span>
                </h4>
                <p class="text-muted mb-0">Informasi lengkap perpindahan barang antar site.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('outbound.relokasi.index') }}" class="btn btn-label-secondary">
                    <i class="ti tabler-arrow-left me-1"></i> Kembali
                </a>
                @if ($outbound->status !== 'cancel')
                    <button type="button" class="btn btn-danger"
                        onclick="cancelRelokasi({{ $outbound->id }}, '{{ $outbound->tks_dn_number ?? $outbound->number }}')">
                        <i class="ti tabler-x me-1"></i> Batalkan Relokasi
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Rute Relokasi Card -->
        <div class="card mb-4 shadow-sm border border-light-subtle" style="border-radius: 12px; overflow: hidden;">
            <div class="card-body p-4 text-center"
                style="background: linear-gradient(135deg, rgba(115,103,240,0.06) 0%, rgba(255,255,255,1) 100%);">
                <div class="d-flex align-items-center justify-content-center gap-4 flex-wrap">
                    <div class="text-center">
                        <div class="mb-2">
                            <span class="badge bg-label-secondary px-4 py-3 fs-6 fw-bold" style="border-radius: 12px;">
                                <i class="ti tabler-building-warehouse d-block mb-1 fs-4"></i>
                                {{ $outbound->from_address ?? 'WH Utama' }}
                            </span>
                        </div>
                        <small class="text-muted fw-medium text-uppercase">ASAL</small>
                    </div>
                    <div class="text-center">
                        <i class="ti tabler-arrow-right text-primary" style="font-size: 2.5rem;"></i>
                        <div class="small text-muted mt-1">{{ $outbound->qty }} item</div>
                    </div>
                    <div class="text-center">
                        <div class="mb-2">
                            <span class="badge bg-label-primary px-4 py-3 fs-6 fw-bold" style="border-radius: 12px;">
                                <i class="ti tabler-map-pin d-block mb-1 fs-4"></i>
                                {{ $outbound->pickup_address ?? '-' }}
                            </span>
                        </div>
                        <small class="text-muted fw-medium text-uppercase">TUJUAN</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informasi Umum -->
        <div class="card mb-4 shadow-sm border border-light-subtle">
            <div class="card-header bg-light py-2 px-3 border-bottom">
                <h6 class="card-title mb-0 text-dark fw-bold">
                    <i class="ti tabler-file-description me-2 text-secondary"></i>Informasi Umum
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 table-sm">
                        <tbody>
                            <tr>
                                <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">TKS DN#</th>
                                <td class="fw-bold py-2 px-3 text-primary small">{{ $outbound->tks_dn_number ?? '-' }}</td>
                                <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">Ref# (WH)</th>
                                <td class="fw-bold py-2 px-3 text-dark small">{{ $outbound->number ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">Tanggal Relokasi</th>
                                <td class="fw-bold py-2 px-3 text-dark small">
                                    {{ $outbound->outbound_date ? \Carbon\Carbon::parse($outbound->outbound_date)->format('d/m/Y') : '-' }}
                                </td>
                                <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">Diproses Oleh</th>
                                <td class="fw-bold py-2 px-3 text-dark small">{{ $outbound->outbound_by ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">Client</th>
                                <td class="fw-bold py-2 px-3 text-dark small">{{ $outbound->client?->name ?? '-' }}</td>
                                <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">Total Item</th>
                                <td class="fw-bold py-2 px-3 text-dark small">
                                    <span class="badge bg-label-primary">{{ $outbound->qty }} item</span>
                                </td>
                            </tr>
                            @if($outbound->remarks)
                            <tr>
                                <th class="bg-light-subtle text-muted py-2 px-3 small fw-medium">Keterangan</th>
                                <td class="py-2 px-3 small text-dark" colspan="3" style="white-space: pre-wrap;">{{ $outbound->remarks }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Daftar Item -->
        <div class="card shadow-sm border-0">
            <div class="card-header border-bottom bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 small fw-bold">
                    <i class="ti tabler-box-seam me-2 text-primary"></i>Item yang Direlokasi
                </h6>
                <span class="badge bg-label-primary px-2 py-1 small">{{ $outbound->qty }} Item</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 text-nowrap table-sm" style="font-size: 0.82rem;">
                        <thead class="table-light">
                            <tr>
                                <th width="30" class="py-2 px-3">#</th>
                                <th class="py-2 px-3">Part Name</th>
                                <th class="py-2 px-3">Part Number</th>
                                <th class="py-2 px-3">Serial Number</th>
                                <th class="py-2 px-3">Kondisi</th>
                                <th class="py-2 px-3">Riwayat Relokasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($outbound->details as $detail)
                                <tr>
                                    <td class="py-2 px-3">{{ $loop->iteration }}</td>
                                    <td class="py-2 px-3 fw-medium text-dark">{{ $detail->part_name }}</td>
                                    <td class="py-2 px-3">{{ $detail->part_number }}</td>
                                    <td class="py-2 px-3 fw-bold text-primary">{{ $detail->serial_number }}</td>
                                    <td class="py-2 px-3">
                                        @php
                                            $condBadge = 'bg-label-info';
                                            if (in_array($detail->condition, ['New', 'Good', 'Refurbished'])) $condBadge = 'bg-label-success';
                                            elseif (in_array($detail->condition, ['Faulty', 'Write-off Needed'])) $condBadge = 'bg-label-danger';
                                        @endphp
                                        <span class="badge {{ $condBadge }} small">{{ strtoupper($detail->condition) }}</span>
                                    </td>
                                    <td class="py-2 px-3">
                                        @if(isset($histories[$detail->serial_number]))
                                            <div class="d-flex flex-wrap gap-1 align-items-center">
                                                @foreach($histories[$detail->serial_number] as $i => $hist)
                                                    @if($i === 0)
                                                        <span class="badge bg-label-secondary small" style="font-size: 0.65rem;">{{ $hist->from_location ?? '?' }}</span>
                                                    @endif
                                                    <i class="ti tabler-arrow-right text-primary" style="font-size: 0.7rem;"></i>
                                                    <span class="badge {{ $hist->reference_number === ($outbound->tks_dn_number ?? $outbound->number) ? 'bg-primary' : 'bg-label-primary' }} small" style="font-size: 0.65rem;">
                                                        {{ $hist->to_location ?? '?' }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Status -->
    <div class="col-md-4">
        <div class="card mb-4 shadow-sm border border-light-subtle text-center">
            <div class="card-header bg-light py-2 px-3 border-bottom">
                <h6 class="card-title mb-0 text-dark small fw-bold">Status Relokasi</h6>
            </div>
            <div class="card-body py-3 px-3">
                <span class="badge {{ $outbound->status == 'cancel' ? 'bg-label-danger' : 'bg-label-success' }} py-2 w-100 shadow-sm fw-bold mb-3 fs-6">
                    {{ strtoupper($outbound->status) }}
                </span>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">DIBUAT PADA</span>
                    <span class="text-dark small fw-bold">{{ $outbound->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">KATEGORI</span>
                    <span class="badge bg-label-info">Relokasi</span>
                </div>
            </div>
        </div>

        <!-- Info Multi-Hop -->
        <div class="card shadow-sm border border-light-subtle">
            <div class="card-header bg-light py-2 px-3 border-bottom">
                <h6 class="card-title mb-0 text-dark small fw-bold">
                    <i class="ti tabler-info-circle me-2 text-primary"></i>Info Multi-Hop
                </h6>
            </div>
            <div class="card-body p-3">
                <p class="text-muted small mb-2">
                    Item yang memiliki status <span class="badge bg-label-warning text-dark">On Relocation</span> dapat direlokasi lagi ke site berikutnya tanpa harus kembali ke WH Utama.
                </p>
                <p class="text-muted small mb-0">
                    Riwayat seluruh perjalanan item dapat dilihat di kolom <strong>Riwayat Relokasi</strong> pada tabel item.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    function cancelRelokasi(id, ref) {
        Swal.fire({
            title: 'Batalkan Relokasi?',
            text: `Yakin membatalkan Relokasi ${ref}? Status barang akan dikembalikan ke kondisi sebelumnya.`,
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
