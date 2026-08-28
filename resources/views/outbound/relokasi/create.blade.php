@extends('layout.index')
@section('title', $title)
@section('layout_class', 'layout-menu-collapsed')

@section('js')
<script>
    localStorage.clear();
    const STORE_URL = '{{ route('outbound.relokasi.store') }}';

    function renderProducts() {
        const products = JSON.parse(localStorage.getItem('relokasi_temp_products')) ?? [];
        const tbody = document.getElementById('productTableBody');
        const totalCount = document.getElementById('totalItemsCount');

        if (totalCount) totalCount.innerText = products.length;
        tbody.innerHTML = '';

        if (products.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <div class="mb-2"><i class="ti tabler-package-off text-light shadow-sm bg-label-secondary rounded p-3 fs-1"></i></div>
                        <h5 class="text-muted mb-0">Belum ada item dipilih.</h5>
                        <small class="text-muted text-uppercase fw-medium">Pilih item dari inventory untuk memulai.</small>
                    </td>
                </tr>`;
            return;
        }

        const outboundedStatuses = ['Shipped / Outbound', 'Out for Replacement/ Support', 'Out for Loan', 'Out for Return'];

        products.forEach((product, index) => {
            let condClass = 'bg-label-secondary';
            if (['New', 'Refurbished', 'Good'].includes(product.condition)) condClass = 'bg-label-success';
            else if (['Faulty', 'Write-off Needed'].includes(product.condition)) condClass = 'bg-label-danger';

            const isOutbounded = product.is_outbounded || outboundedStatuses.includes(product.status);
            const isOnRelocation = product.status === 'On Relocation';

            let statusClass = 'bg-label-success';
            if (isOutbounded) statusClass = 'bg-label-danger';
            else if (isOnRelocation) statusClass = 'bg-label-warning';

            const obFlag = isOutbounded
                ? `<span class="badge bg-danger ms-1" style="font-size:0.6rem;" title="Item ini sudah di-Outbound sebelumnya via transaksi lain">
                       <i class="ti tabler-alert-triangle me-1"></i>Sudah OB
                   </span>`
                : '';

            const rowClass = isOutbounded ? 'table-warning' : '';

            tbody.innerHTML += `
            <tr class="animate__animated animate__fadeIn ${rowClass}">
                <td class="text-center">${index + 1}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm me-3">
                            <span class="avatar-initial rounded-circle bg-label-primary font-small"><i class="ti tabler-barcode fs-6"></i></span>
                        </div>
                        <div>
                            <span class="fw-bold text-dark">${product.unique_id}</span>
                            ${obFlag}
                        </div>
                    </div>
                </td>
                <td>
                    <div class="d-flex flex-column">
                        <span class="fw-bold text-dark">${product.partName}</span>
                        <small class="text-muted badge bg-label-secondary border-0 text-start px-0" style="width: fit-content;">${product.partNumber}</small>
                    </div>
                </td>
                <td><span class="badge ${condClass}">${product.condition}</span></td>
                <td><span class="fw-medium">${product.serialNumber}</span></td>
                <td><span class="badge ${statusClass}">${product.status || '-'}</span></td>
                <td><span class="badge bg-label-info border-info-subtle"><i class="ti tabler-map-pin me-1 fs-tiny"></i> ${product.location}</span></td>
                <td class="text-center">
                    <button class="btn btn-label-danger btn-icon btn-sm rounded-circle shadow-none waves-effect" onclick="deleteProduct(${index})">
                        <i class="ti tabler-trash-x fs-5"></i>
                    </button>
                </td>
            </tr>`;
        });
    }

    function deleteProduct(index) {
        Swal.fire({
            title: 'Hapus item?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ea5455',
            cancelButtonColor: '#82868b',
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            customClass: { confirmButton: 'btn btn-danger me-1', cancelButton: 'btn btn-label-secondary' },
            buttonsStyling: false
        }).then(result => {
            if (result.isConfirmed) {
                const products = JSON.parse(localStorage.getItem('relokasi_temp_products')) ?? [];
                products.splice(index, 1);
                localStorage.setItem('relokasi_temp_products', JSON.stringify(products));
                renderProducts();
            }
        });
    }

    function submitRelokasi() {
        const products = JSON.parse(localStorage.getItem('relokasi_temp_products')) ?? [];
        if (products.length === 0) {
            Swal.fire({ title: 'List Kosong', text: 'Pilih minimal satu item sebelum menyimpan.', icon: 'error', customClass: { confirmButton: 'btn btn-primary' }, buttonsStyling: false });
            return;
        }

        const fromAddress  = document.getElementById('from_address').value.trim();
        const toAddress    = document.getElementById('pickup_address').value.trim();
        const date         = document.getElementById('date').value;
        const processedBy  = document.getElementById('outbound_by').value.trim();

        if (!fromAddress || !toAddress || !date || !processedBy) {
            Swal.fire({ title: 'Form Belum Lengkap', text: 'Harap isi semua field yang bertanda *', icon: 'warning', customClass: { confirmButton: 'btn btn-primary' }, buttonsStyling: false });
            return;
        }

        if (fromAddress.toLowerCase() === toAddress.toLowerCase()) {
            Swal.fire({ title: 'Lokasi Sama', text: 'Lokasi asal dan tujuan tidak boleh sama.', icon: 'warning', customClass: { confirmButton: 'btn btn-primary' }, buttonsStyling: false });
            return;
        }

        const data = {
            from_address:   fromAddress,
            pickup_address: toAddress,
            tks_dn_number:  document.getElementById('tks_dn_number').value,
            client_id:      document.getElementById('client_id')?.value || null,
            outbound_date:  date,
            outbound_by:    processedBy,
            remarks:        document.getElementById('remarks').value,
            products
        };

        Swal.fire({
            title: 'Konfirmasi Relokasi?',
            html: `Item akan direlokasi dari <strong>${fromAddress}</strong> ke <strong>${toAddress}</strong>.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="ti tabler-device-floppy me-1"></i> Simpan Relokasi',
            cancelButtonText: 'Batal',
            customClass: { confirmButton: 'btn btn-success me-1', cancelButton: 'btn btn-label-secondary' },
            buttonsStyling: false
        }).then(result => {
            if (result.isConfirmed) {
                fetch(STORE_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify(data)
                })
                .then(r => r.json())
                .then(res => {
                    if (res.status) {
                        localStorage.removeItem('relokasi_temp_products');
                        Swal.fire({ title: 'Berhasil!', text: 'Relokasi berhasil disimpan.', icon: 'success', customClass: { confirmButton: 'btn btn-primary' }, buttonsStyling: false })
                            .then(() => window.location.href = '{{ route('outbound.relokasi.index') }}');
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                });
            }
        });
    }

    window.onReceivePickedItem = function (item) {
        const products = JSON.parse(localStorage.getItem('relokasi_temp_products')) ?? [];
        if (products.some(p => p.product_id === item.id)) return;

        products.push({
            product_id:      item.id,
            unique_id:       item.unique_id,
            partName:        item.part_name,
            partNumber:      item.part_number,
            partDescription: item.part_description,
            serialNumber:    item.serial_number,
            brand:           item.brand,
            productGroup:    item.product_group,
            condition:       item.condition,
            status:          item.status,
            location:        item.location,
        });

        localStorage.setItem('relokasi_temp_products', JSON.stringify(products));
        renderProducts();
    };

    window.addEventListener('DOMContentLoaded', () => renderProducts());
</script>
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 border-0 shadow-sm overflow-hidden" style="border-radius: 12px;">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center bg-white border-bottom py-3 px-4">
                    <div class="me-3">
                        <h4 class="mb-1 text-primary fw-bold">
                            <i class="ti tabler-map-route me-2"></i>{{ $title }}
                        </h4>
                        <p class="text-muted mb-0 small text-uppercase fw-medium">Relokasi barang ke site lain (multi-hop didukung)</p>
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                        <a href="{{ route('outbound.relokasi.index') }}" class="btn btn-label-secondary waves-effect btn-sm">
                            <i class="ti tabler-arrow-left me-1"></i> Batal
                        </a>
                        <button class="btn btn-primary shadow-sm btn-sm waves-effect waves-light" onclick="submitRelokasi()">
                            <i class="ti tabler-device-floppy me-1"></i> Simpan Relokasi
                        </button>
                    </div>
                </div>

                <div class="card-body p-4" style="background: linear-gradient(180deg, rgba(255,255,255,1) 0%, rgba(248,247,255,1) 100%);">
                    <div class="row g-4">
                        <!-- Kolom 1: Rute Relokasi -->
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm bg-white p-3 h-100" style="border-radius: 12px; border: 1px solid rgba(115, 103, 240, 0.08) !important;">
                                <h6 class="fw-bold mb-3 d-flex align-items-center text-primary">
                                    <i class="ti tabler-map-route me-2"></i> Rute Relokasi
                                </h6>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-dark">Dari Site (Asal) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control border-light-subtle fw-bold" id="from_address"
                                        placeholder="Contoh: WH Utama, Surabaya, Makasar..."
                                        list="site-suggestions-from">
                                    <datalist id="site-suggestions-from">
                                        <option value="WH Utama">
                                        <option value="Surabaya">
                                        <option value="Makasar">
                                        <option value="Jakarta">
                                        <option value="Bandung">
                                        <option value="Medan">
                                    </datalist>
                                    <small class="text-muted">Lokasi asal barang dikirim</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-dark">Ke Site (Tujuan) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control border-light-subtle fw-bold text-primary" id="pickup_address"
                                        placeholder="Contoh: Surabaya, Makasar, Jakarta..."
                                        list="site-suggestions-to">
                                    <datalist id="site-suggestions-to">
                                        <option value="WH Utama">
                                        <option value="Surabaya">
                                        <option value="Makasar">
                                        <option value="Jakarta">
                                        <option value="Bandung">
                                        <option value="Medan">
                                    </datalist>
                                    <small class="text-muted">Lokasi tujuan barang dikirim</small>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label small fw-bold text-dark">Client</label>
                                    <select class="form-select border-light-subtle" id="client_id" name="client_id">
                                        <option value="">-- Opsional --</option>
                                        @foreach(\App\Models\Client::orderBy('name')->get() as $client)
                                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom 2: Info Dokumen -->
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm bg-white p-3 h-100" style="border-radius: 12px; border: 1px solid rgba(115, 103, 240, 0.08) !important;">
                                <h6 class="fw-bold mb-3 d-flex align-items-center text-primary">
                                    <i class="ti tabler-file-description me-2"></i> Informasi Dokumen
                                </h6>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-dark">TKS DN# (Opsional)</label>
                                    <input type="text" class="form-control border-light-subtle" id="tks_dn_number"
                                        placeholder="Akan digenerate otomatis jika kosong">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-primary">Tanggal Relokasi <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control border-primary-subtle fw-bold"
                                        id="date" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label small fw-bold text-primary">Diproses Oleh <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control border-primary-subtle fw-bold"
                                        id="outbound_by" placeholder="Nama petugas">
                                </div>
                            </div>
                        </div>

                        <!-- Kolom 3: Keterangan -->
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm bg-white p-3 h-100" style="border-radius: 12px; border: 1px solid rgba(115, 103, 240, 0.08) !important;">
                                <h6 class="fw-bold mb-3 d-flex align-items-center text-primary">
                                    <i class="ti tabler-message-2 me-2"></i> Keterangan & Info
                                </h6>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-dark">Keterangan / Remarks</label>
                                    <textarea class="form-control border-light-subtle" id="remarks" rows="4"
                                        placeholder="Catatan tambahan untuk relokasi ini..."></textarea>
                                </div>
                                <div class="alert alert-info border-0 py-2 px-3 mb-0" style="font-size: 0.8rem; border-radius: 8px;">
                                    <i class="ti tabler-info-circle me-1"></i>
                                    <strong>Multi-hop:</strong> Barang yang sudah direlokasi ke suatu site dapat direlokasi lagi ke site berikutnya tanpa harus kembali ke WH Utama.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daftar Item -->
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <div class="card-header bg-white py-4 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold text-dark"><i class="ti tabler-box-seam me-2 text-primary"></i>Item yang Direlokasi</h5>
                        <p class="text-muted small mb-0">Total item: <span id="totalItemsCount" class="fw-bold text-primary">0</span></p>
                    </div>
                    <button class="btn btn-primary shadow-sm fw-bold px-4 py-2" style="border-radius: 10px;"
                        onclick="$('#selectInventoryModal').modal('show'); fetchInventoryRelokasi();">
                        <i class="ti tabler-plus me-2 fs-5"></i> Tambah Item
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle custom-table mb-0">
                            <thead class="bg-label-primary text-uppercase small fw-bold">
                                <tr>
                                    <th class="text-center" style="width: 60px;">#</th>
                                    <th>WH Asset ID</th>
                                    <th>Spesifikasi</th>
                                    <th>Kondisi</th>
                                    <th>Serial Number</th>
                                    <th>Status Stok</th>
                                    <th>Lokasi Saat Ini</th>
                                    <th class="text-center" style="width: 80px;">Hapus</th>
                                </tr>
                            </thead>
                            <tbody id="productTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .custom-table thead th {
        font-size: 0.75rem;
        letter-spacing: 0.8px;
        color: #7367f0;
        background: rgba(115, 103, 240, 0.05);
        border-bottom: 2px solid rgba(115, 103, 240, 0.1);
        padding: 1.2rem;
    }
</style>

<!-- Modal Pilih Inventory -->
<div class="modal fade" id="selectInventoryModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
            <div class="modal-header bg-label-primary border-bottom py-3 px-4">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-sm me-3 shadow-sm">
                        <span class="avatar-initial rounded-circle bg-primary"><i class="ti tabler-search"></i></span>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-primary mb-0">Pilih Item dari Inventory</h5>
                        <small class="text-muted text-uppercase fw-medium ls-1 fs-tiny">Termasuk item yang sedang "On Relocation" (multi-hop)</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 bg-light-subtle">
                <div class="p-4 bg-white border-bottom shadow-xs">
                    <div class="row align-items-center g-3">
                        <div class="col-md-8">
                            <div class="input-group input-group-merge border rounded-pill overflow-hidden bg-light">
                                <span class="input-group-text bg-transparent border-0 pe-1 fs-4"><i class="ti tabler-search text-primary"></i></span>
                                <input type="text" class="form-control border-0 bg-transparent py-2 shadow-none"
                                    id="inventorySearch" placeholder="Cari Asset#, Serial Number, Part Name..."
                                    onkeyup="fetchInventoryRelokasi()">
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end d-flex flex-column gap-1 align-items-end">
                            <span class="badge bg-label-warning text-dark small">
                                <i class="ti tabler-refresh me-1"></i> On Relocation = multi-hop
                            </span>
                            <span class="badge bg-danger small">
                                <i class="ti tabler-alert-triangle me-1"></i> Sudah OB = sudah dioutbound via transaksi lain
                            </span>
                        </div>
                    </div>

                    <!-- Paste Serial Numbers -->
                    <div class="mt-3 pt-3 border-top">
                        <div class="d-flex align-items-center justify-content-between">
                            <a href="javascript:void(0)" onclick="togglePasteSection()"
                               class="text-primary fw-medium text-decoration-none small d-flex align-items-center gap-1" id="pasteToggle">
                                <i class="ti tabler-chevron-down fs-6" id="pasteChevron" style="transition: transform 0.2s;"></i>
                                Tempel Serial Number
                            </a>
                        </div>
                        <div id="pasteSection" class="mt-2" style="display: none;">
                            <div class="d-flex gap-2 align-items-start">
                                <textarea class="form-control form-control-sm" id="serialNumbersPaste" rows="3"
                                    placeholder="Tempel serial number di sini...&#10;Satu per baris, atau pisahkan dengan koma / titik koma"
                                    style="resize: vertical; font-size: 0.85rem;"></textarea>
                                <button class="btn btn-primary btn-sm px-3 flex-shrink-0 shadow-sm"
                                    onclick="selectBySerialNumbers()" id="selectBySnBtn" style="border-radius: 8px; min-width: 110px;">
                                    <i class="ti tabler-check me-1"></i> Pilih Semua
                                </button>
                            </div>
                            <div id="pasteResult" class="mt-1 small fw-medium" style="display: none;"></div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-label-secondary text-uppercase small ls-1 fw-bold border-top-0">
                            <tr>
                                <th class="ps-4">WH Asset ID</th>
                                <th>Spesifikasi</th>
                                <th>Serial Number</th>
                                <th>Brand</th>
                                <th>Status</th>
                                <th>Lokasi</th>
                                <th class="text-center pe-4" style="width: 120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="inventoryListBody">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-white border-top p-3 justify-content-between">
                <small class="text-muted" id="inventoryResultCount">Ditemukan 0 item</small>
                <button type="button" class="btn btn-label-secondary rounded-pill px-4 waves-effect" data-bs-dismiss="modal">Selesai Memilih</button>
            </div>
        </div>
    </div>
</div>

<script>
    function fetchInventoryRelokasi() {
        const search = document.getElementById('inventorySearch').value;
        const tbody  = document.getElementById('inventoryListBody');

        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-5"><div class="spinner-border text-primary shadow-sm mb-3"></div><p class="text-muted fw-medium mb-0">Memuat data inventory...</p></td></tr>';

        const existing = JSON.parse(localStorage.getItem('relokasi_temp_products')) ?? [];
        const excludeIds = existing.map(i => i.product_id).filter(Boolean);

        const url = `{{ route('outbound.relokasi.get.inventory') }}?search=${encodeURIComponent(search)}&exclude_ids=${excludeIds.join(',')}`;

        fetch(url)
            .then(r => { if (!r.ok) throw new Error('Server error ' + r.status); return r.json(); })
            .then(data => {
                tbody.innerHTML = '';
                document.getElementById('inventoryResultCount').innerText = `Ditemukan ${data.length} item`;

                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-5"><i class="ti tabler-search-off fs-1 text-muted mb-3 d-block"></i><h6 class="text-muted mb-0">Tidak ada item yang tersedia.</h6></td></tr>';
                    return;
                }

                const outboundedStatuses = ['Shipped / Outbound', 'Out for Replacement/ Support', 'Out for Loan', 'Out for Return'];

                data.forEach(item => {
                    const isOutbounded = item.is_outbounded || outboundedStatuses.includes(item.status);
                    const isOnRelocation = item.status === 'On Relocation';

                    let statusBadge;
                    if (isOutbounded) {
                        statusBadge = `<div class="d-flex flex-column gap-1">
                            <span class="badge bg-danger" style="font-size:0.65rem;">
                                <i class="ti tabler-alert-triangle me-1"></i>Sudah OB
                            </span>
                            <span class="badge bg-label-secondary" style="font-size:0.6rem;">${item.status}</span>
                        </div>`;
                    } else if (isOnRelocation) {
                        statusBadge = `<span class="badge bg-label-warning text-dark">
                            <i class="ti tabler-refresh me-1"></i>On Relocation
                        </span>`;
                    } else {
                        statusBadge = `<span class="badge bg-label-success">${item.status}</span>`;
                    }

                    const rowBg = isOutbounded ? 'style="background:rgba(234,84,85,0.05);"' : '';
                    const btnClass = isOutbounded ? 'btn-warning' : 'btn-primary';
                    const btnLabel = isOutbounded ? '<i class="ti tabler-alert-triangle me-1"></i> Pilih (OB)' : '<i class="ti tabler-plus me-1"></i> Pilih';

                    tbody.innerHTML += `
                        <tr class="animate__animated animate__fadeIn" ${rowBg}>
                            <td class="ps-4 fw-bold text-dark"><span class="badge bg-label-dark rounded-pill shadow-xs">${item.unique_id}</span></td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-dark">${item.part_name}</span>
                                    <small class="text-muted">${item.part_number}</small>
                                </div>
                            </td>
                            <td><span class="fw-medium">${item.serial_number}</span></td>
                            <td><span class="badge bg-label-secondary border-0">${item.brand}</span></td>
                            <td>${statusBadge}</td>
                            <td><span class="text-primary fw-medium"><i class="ti tabler-map-pin me-1"></i> ${item.location}</span></td>
                            <td class="text-center pe-4">
                                <button class="btn ${btnClass} btn-sm rounded-pill px-3 shadow-sm waves-effect"
                                    onclick='pickItem(${JSON.stringify(item)})'>
                                    ${btnLabel}
                                </button>
                            </td>
                        </tr>`;
                });
            })
            .catch(err => {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5"><div class="badge bg-label-danger fs-6 rounded-pill px-4 py-2 mb-2"><i class="ti tabler-alert-circle me-1"></i> Error</div><p class="text-muted mb-0">${err.message}</p></td></tr>`;
            });
    }

    function pickItem(item) {
        if (typeof window.onReceivePickedItem === 'function') {
            window.onReceivePickedItem(item);
            const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 800, width: '18rem', padding: '0.5rem' });
            Toast.fire({ icon: 'success', title: 'Ditambahkan!' });
            fetchInventoryRelokasi();
        }
    }

    function togglePasteSection() {
        const section = document.getElementById('pasteSection');
        const chevron = document.getElementById('pasteChevron');
        const isHidden = section.style.display === 'none' || !section.style.display;
        section.style.display = isHidden ? 'block' : 'none';
        chevron.style.transform = isHidden ? 'rotate(0deg)' : 'rotate(-90deg)';
    }

    function selectBySerialNumbers() {
        const raw = document.getElementById('serialNumbersPaste').value.trim();
        if (!raw) return;

        const sns = raw.split(/[\r\n,;]+/).map(s => s.trim()).filter(s => s.length > 0);
        const btn = document.getElementById('selectBySnBtn');
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memilih...';

        const url = `{{ route('outbound.relokasi.get.inventory') }}?serial_numbers=${encodeURIComponent(sns.join(','))}`;

        fetch(url)
            .then(r => r.json())
            .then(data => {
                let selected = 0, skipped = 0;
                data.forEach(item => {
                    const existing = JSON.parse(localStorage.getItem('relokasi_temp_products')) ?? [];
                    if (!existing.some(i => i.product_id === item.id) && typeof window.onReceivePickedItem === 'function') {
                        window.onReceivePickedItem(item);
                        selected++;
                    } else {
                        skipped++;
                    }
                });

                const resultDiv = document.getElementById('pasteResult');
                resultDiv.style.display = 'block';
                let msg = '';
                if (selected > 0) msg += `<span class="text-success"><i class="ti tabler-check-circle me-1"></i> ${selected} item dipilih</span>`;
                if (skipped > 0) msg += `<span class="text-muted ms-2">(${skipped} sudah dipilih)</span>`;
                const notFound = sns.length - data.length;
                if (notFound > 0) msg += `<div class="text-warning mt-1"><i class="ti tabler-alert-triangle me-1"></i> ${notFound} SN tidak ditemukan</div>`;
                resultDiv.innerHTML = msg || '<span class="text-muted">Tidak ada item baru yang dipilih</span>';

                btn.disabled = false;
                btn.innerHTML = original;
                fetchInventoryRelokasi();
            })
            .catch(err => {
                document.getElementById('pasteResult').innerHTML = `<span class="text-danger">${err.message}</span>`;
                document.getElementById('pasteResult').style.display = 'block';
                btn.disabled = false;
                btn.innerHTML = original;
            });
    }
</script>
@endsection
