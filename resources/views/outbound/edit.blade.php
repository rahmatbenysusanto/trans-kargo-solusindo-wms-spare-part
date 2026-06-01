@extends('layout.index')
@section('title', 'Edit Outbound')
@section('layout_class', 'layout-menu-collapsed')

@section('css')
<style>
    .edit-table th {
        font-size: 0.8rem;
        white-space: nowrap;
    }
    .edit-table td {
        font-size: 0.82rem;
        vertical-align: middle;
    }
    .item-removed {
        text-decoration: line-through;
        opacity: 0.5;
        background-color: #fff5f5 !important;
    }
    .item-added {
        background-color: #f0fff4 !important;
    }
    .badge-diff {
        font-size: 0.65rem;
        padding: 0.15rem 0.4rem;
    }
    .diff-badge-added {
        background-color: #28a745;
    }
    .diff-badge-removed {
        background-color: #dc3545;
    }
    .sticky-header {
        position: sticky;
        top: 0;
        z-index: 10;
    }
</style>
@endsection

@section('content')
<div class="row">
    <!-- Action Header -->
    <div class="col-12 mb-4">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="ti tabler-edit me-2 text-primary"></i>
                    Edit Outbound: <span class="text-primary">{{ $outbound->number ?? $outbound->tks_dn_number }}</span>
                </h4>
                <p class="text-muted mb-0">Tambahkan atau hapus SN dari outbound ini.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('outbound.show', $outbound->id) }}" class="btn btn-label-secondary">
                    <i class="ti tabler-arrow-left me-1"></i> Kembali
                </a>
                <button class="btn btn-primary" onclick="saveChanges()" id="btnSave">
                    <i class="ti tabler-device-floppy me-1"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Header Info (readonly) -->
        <div class="card mb-4 shadow-sm border border-light-subtle">
            <div class="card-header bg-light py-2 px-3 border-bottom">
                <h6 class="card-title mb-0 text-dark fw-bold">
                    <i class="ti tabler-file-description me-2 text-secondary"></i>Informasi Outbound
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0 table-sm">
                    <tbody>
                        <tr>
                            <th class="bg-light-subtle text-muted w-25 py-2 px-3 small">Client</th>
                            <td class="fw-bold py-2 px-3 small">{{ $outbound->client?->name ?? '-' }}</td>
                            <th class="bg-light-subtle text-muted w-25 py-2 px-3 small">Category</th>
                            <td class="py-2 px-3 small">
                                <span class="badge bg-label-primary">{{ $outbound->category }}</span>
                                @if ($outbound->request_type)
                                    <span class="badge bg-label-info">{{ $outbound->request_type }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light-subtle text-muted py-2 px-3 small">Outbound Date</th>
                            <td class="fw-bold py-2 px-3 small">{{ $outbound->outbound_date }}</td>
                            <th class="bg-light-subtle text-muted py-2 px-3 small">Status</th>
                            <td class="py-2 px-3 small">
                                <span class="badge bg-label-success">{{ strtoupper($outbound->status) }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Items Table -->
        <div class="card shadow-sm border-0">
            <div class="card-header border-bottom bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title mb-0 small fw-bold">
                        <i class="ti tabler-box-seam me-1 text-primary"></i>
                        Daftar Item
                    </h6>
                    <small class="text-muted" id="itemCount">{{ $outbound->details->count() }} item</small>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <span id="changeSummary" class="small text-muted" style="display:none;"></span>
                    <button class="btn btn-primary btn-sm" onclick="$('#selectInventoryModal').modal('show'); fetchInventory();">
                        <i class="ti tabler-plus me-1"></i> Tambah Item
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover edit-table mb-0">
                        <thead class="table-light sticky-header">
                            <tr>
                                <th style="width:40px;">#</th>
                                <th>WH Asset#</th>
                                <th>Part Name / Number</th>
                                <th>SN</th>
                                <th>Condition</th>
                                <th style="width:100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody">
                            @foreach ($outbound->details as $detail)
                            <tr data-sn="{{ $detail->serial_number }}" data-status="original" class="item-original">
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td class="fw-bold small">{{ $detail->inventory?->unique_id ?? '-' }}</td>
                                <td>
                                    <span class="fw-bold text-dark small">{{ $detail->part_name }}</span>
                                    @if ($detail->part_number)
                                        <br><small class="text-muted">{{ $detail->part_number }}</small>
                                    @endif
                                </td>
                                <td class="fw-bold text-primary">{{ $detail->serial_number }}</td>
                                <td>
                                    @php
                                        $badgeClass = match($detail->condition) {
                                            'New', 'Good' => 'bg-label-success',
                                            'Faulty', 'Write-off Needed', 'Scrap' => 'bg-label-danger',
                                            default => 'bg-label-info'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ strtoupper($detail->condition) }}</span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-label-danger btn-sm" onclick="removeItem('{{ $detail->serial_number }}')" title="Hapus dari outbound">
                                        <i class="ti tabler-trash-x"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white py-2 px-3 d-flex justify-content-between align-items-center">
                <small class="text-muted" id="footerInfo">Tidak ada perubahan</small>
                <button class="btn btn-primary btn-sm" onclick="saveChanges()">
                    <i class="ti tabler-device-floppy me-1"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </div>

    <!-- Right column: Summary -->
    <div class="col-md-4">
        <div class="card mb-4 shadow-sm border border-light-subtle">
            <div class="card-header bg-light py-2 px-3 border-bottom">
                <h6 class="card-title mb-0 text-dark small fw-bold"><i class="ti tabler-list me-2"></i> Ringkasan</h6>
            </div>
            <div class="card-body py-3 px-3">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Item Saat Ini</span>
                    <span class="fw-bold" id="summaryOriginalCount">{{ $outbound->details->count() }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small text-danger">Dihapus</span>
                    <span class="fw-bold text-danger" id="summaryRemovedCount">0</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small text-success">Ditambahkan</span>
                    <span class="fw-bold text-success" id="summaryAddedCount">0</span>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between">
                    <span class="fw-bold small">Total Setelah</span>
                    <span class="fw-bold text-primary" id="summaryFinalCount">{{ $outbound->details->count() }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

@include('outbound.modals')

<script>
    // --- State ---
    const OUTBOUND_ID = {{ $outbound->id }};
    let removedSNs = new Set();
    let addedItems = []; // { serial_number, part_name, part_number, unique_id, condition, ... }

    // --- Render ---
    function updateUI() {
        // Update item rows
        document.querySelectorAll('#itemsTableBody tr').forEach(row => {
            const sn = row.dataset.sn;
            const status = row.dataset.status;

            row.className = '';
            if (status === 'removed') {
                row.classList.add('item-removed');
            } else if (status === 'added') {
                row.classList.add('item-added');
            }
        });

        // Update summary
        const original = parseInt(document.getElementById('summaryOriginalCount').textContent);
        const removed = removedSNs.size;
        const added = addedItems.length;
        const total = original - removed + added;

        document.getElementById('summaryRemovedCount').textContent = removed;
        document.getElementById('summaryAddedCount').textContent = added;
        document.getElementById('summaryFinalCount').textContent = total;
        document.getElementById('itemCount').textContent = total + ' item';

        // Footer info
        const hasChanges = removed > 0 || added > 0;
        document.getElementById('footerInfo').textContent = hasChanges
            ? (removed > 0 ? removed + ' dihapus, ' : '') + (added > 0 ? added + ' ditambahkan' : '')
            : 'Tidak ada perubahan';

        document.getElementById('changeSummary').style.display = hasChanges ? 'inline' : 'none';
        document.getElementById('changeSummary').textContent = (removed > 0 ? '-' + removed : '') + (added > 0 ? ' +' + added : '');
    }

    // --- Remove Item ---
    function removeItem(sn) {
        const row = document.querySelector(`#itemsTableBody tr[data-sn="${sn}"]`);
        if (!row) return;

        const currentStatus = row.dataset.status;

        if (currentStatus === 'removed') {
            // Undo remove
            row.dataset.status = 'original';
            removedSNs.delete(sn);
            Swal.fire({
                icon: 'info',
                title: 'Dibatalkan',
                text: 'Item ' + sn + ' dikembalikan ke daftar.',
                timer: 1200,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        } else if (currentStatus === 'added') {
            // Remove added item completely
            row.remove();
            addedItems = addedItems.filter(i => i.serial_number !== sn);
            Swal.fire({
                icon: 'info',
                title: 'Dihapus',
                text: 'Item ' + sn + ' dihapus dari daftar tambahan.',
                timer: 1200,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        } else {
            // Mark as removed
            row.dataset.status = 'removed';
            removedSNs.add(sn);
        }

        updateUI();
    }

    // --- Add Items from Inventory ---
    window.onReceivePickedItem = function(item) {
        const sn = item.serial_number;

        // Check if already in original list (and not removed)
        const existingRow = document.querySelector(`#itemsTableBody tr[data-sn="${sn}"]`);
        if (existingRow && existingRow.dataset.status !== 'removed') {
            Swal.fire({
                icon: 'warning',
                title: 'Sudah Ada',
                text: 'Item dengan SN ' + sn + ' sudah ada di daftar.',
                timer: 1500,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
            return;
        }

        // If was removed, just undo the removal
        if (existingRow && existingRow.dataset.status === 'removed') {
            existingRow.dataset.status = 'original';
            removedSNs.delete(sn);
            updateUI();
            Swal.fire({
                icon: 'success',
                title: 'Dikembalikan',
                text: 'Item ' + sn + ' dikembalikan ke daftar.',
                timer: 1200,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
            return;
        }

        // Skip if already added
        if (addedItems.some(i => i.serial_number === sn)) {
            Swal.fire({
                icon: 'warning',
                title: 'Sudah Ditambahkan',
                text: 'Item ' + sn + ' sudah ada di daftar tambahan.',
                timer: 1500,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
            return;
        }

        // Add new row
        const tbody = document.getElementById('itemsTableBody');
        const row = document.createElement('tr');
        row.dataset.sn = sn;
        row.dataset.status = 'added';

        const condBadge = item.condition === 'New' || item.condition === 'Refurbished' ? 'bg-label-success'
            : item.condition === 'Faulty' || item.condition === 'Write-off Needed' ? 'bg-label-danger'
            : 'bg-label-info';

        row.innerHTML = `
            <td class="text-center"><span class="badge bg-success badge-diff">BARU</span></td>
            <td class="fw-bold small">${item.unique_id || '-'}</td>
            <td>
                <span class="fw-bold text-dark small">${item.part_name}</span>
                ${item.part_number ? '<br><small class="text-muted">' + item.part_number + '</small>' : ''}
            </td>
            <td class="fw-bold text-primary">${sn}</td>
            <td><span class="badge ${condBadge}">${item.condition || 'GOOD'}</span></td>
            <td class="text-center">
                <button class="btn btn-label-danger btn-sm" onclick="removeItem('${sn}')" title="Batalkan">
                    <i class="ti tabler-x"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);

        addedItems.push({
            serial_number: sn,
            part_name: item.part_name,
            part_number: item.part_number,
            unique_id: item.unique_id,
            condition: item.condition
        });

        updateUI();

        Swal.fire({
            icon: 'success',
            title: 'Ditambahkan!',
            timer: 800,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });

        // Re-fetch inventory to hide selected item
        if (typeof fetchInventory === 'function') fetchInventory();
    };

    // --- Save ---
    function saveChanges() {
        const removedCount = removedSNs.size;
        const addedCount = addedItems.length;

        if (removedCount === 0 && addedCount === 0) {
            Swal.fire('Info', 'Tidak ada perubahan yang perlu disimpan.', 'info');
            return;
        }

        const msgParts = [];
        if (removedCount > 0) msgParts.push(removedCount + ' item dihapus');
        if (addedCount > 0) msgParts.push(addedCount + ' item ditambahkan');

        Swal.fire({
            title: 'Simpan Perubahan?',
            html: `
                <div class="text-start">
                    <p class="mb-2">Anda akan menyimpan perubahan berikut:</p>
                    <ul class="mb-0">
                        ${removedCount > 0 ? '<li class="text-danger">' + removedCount + ' item akan dikembalikan ke inventory</li>' : ''}
                        ${addedCount > 0 ? '<li class="text-success">' + addedCount + ' item akan di-checkout dari inventory</li>' : ''}
                    </ul>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                doSaveChanges();
            }
        });
    }

    function doSaveChanges() {
        document.getElementById('btnSave').disabled = true;

        Swal.fire({
            title: 'Processing...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        fetch('{{ route('outbound.update-items', $outbound->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                removed_sns: Array.from(removedSNs),
                added_sns: addedItems.map(i => i.serial_number)
            })
        })
        .then(res => res.json())
        .then(data => {
            Swal.close();
            document.getElementById('btnSave').disabled = false;

            if (data.status) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Perubahan outbound berhasil disimpan.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = '{{ route('outbound.show', $outbound->id) }}';
                });
            } else {
                Swal.fire('Error', data.message || 'Gagal menyimpan perubahan.', 'error');
            }
        })
        .catch(err => {
            Swal.close();
            document.getElementById('btnSave').disabled = false;
            Swal.fire('Error', 'Koneksi error.', 'error');
        });
    }

    // Override fetchInventory to use edit-friendly params
    const originalFetchInventory = window.fetchInventory;
    window.fetchInventory = function() {
        const clientId = document.getElementById('client_id')?.value || '';
        const search = document.getElementById('inventorySearch')?.value || '';
        const tbody = document.getElementById('inventoryListBody');

        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5"><div class="spinner-border text-primary mb-3"></div><p class="text-muted mb-0">Loading...</p></td></tr>';

        // Collect SNs to exclude (already in the outbound)
        const excludeSNs = new Set();
        document.querySelectorAll('#itemsTableBody tr').forEach(row => {
            if (row.dataset.status !== 'removed') {
                excludeSNs.add(row.dataset.sn);
            }
        });
        addedItems.forEach(i => excludeSNs.add(i.serial_number));

        const category = '{{ $outbound->category }}';
        const url = `{{ route('outbound.get.inventory') }}?client_id=${clientId}&search=${search}&category=${category}`;

        fetch(url)
            .then(r => r.json())
            .then(data => {
                // Filter out already selected
                const filtered = data.filter(item => !excludeSNs.has(item.serial_number));

                tbody.innerHTML = '';
                document.getElementById('inventoryResultCount').textContent = `Found ${filtered.length} available items`;

                if (filtered.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5"><i class="ti tabler-search-off fs-1 text-muted mb-3"></i><h6 class="text-muted mb-0">No available items</h6></td></tr>';
                    return;
                }

                filtered.forEach(item => {
                    tbody.innerHTML += `
                        <tr>
                            <td class="ps-4 fw-bold text-dark"><span class="badge bg-label-dark">${item.unique_id}</span></td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-dark">${item.part_name}</span>
                                    <small class="text-muted">${item.part_number}</small>
                                </div>
                            </td>
                            <td><span class="fw-medium">${item.serial_number}</span></td>
                            <td><span class="badge bg-label-secondary">${item.brand}</span></td>
                            <td><span class="text-primary fw-medium"><i class="ti tabler-map-pin me-1"></i> ${item.location}</span></td>
                            <td class="text-center pe-4">
                                <button class="btn btn-primary btn-sm rounded-pill px-3" onclick='pickInventoryItem(${JSON.stringify(item)})'>
                                    <i class="ti tabler-plus me-1"></i> Select
                                </button>
                            </td>
                        </tr>
                    `;
                });
            })
            .catch(error => {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center py-5 text-danger">Error: ${error.message}</td></tr>`;
            });
    };

    // Init
    updateUI();
</script>
@endsection