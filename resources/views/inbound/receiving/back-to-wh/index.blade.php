@extends('layout.index')
@section('title', 'Back to WH - Quick Return')
@section('layout_class', 'layout-menu-collapsed')

@section('css')
<style>
    .batch-table th {
        font-size: 0.8rem;
        white-space: nowrap;
    }
    .batch-table td {
        font-size: 0.8rem;
        vertical-align: middle;
    }
    .status-badge-result {
        font-size: 0.7rem;
        padding: 0.2rem 0.4rem;
    }
    .summary-card {
        transition: all 0.3s ease;
    }
    .sn-input-item {
        transition: all 0.2s ease;
    }
    .sn-input-item:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.15);
    }
    .result-row-success {
        background-color: #f0fff4;
    }
    .result-row-error {
        background-color: #fff5f5;
    }
    .option-badge {
        cursor: pointer;
        transition: all 0.2s;
        opacity: 0.6;
    }
    .option-badge.selected {
        opacity: 1;
        transform: scale(1.05);
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12 d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">
            <i class="ti tabler-arrow-back-up me-2"></i> Back to WH - Batch Return
        </h4>
    </div>

    <div class="col-lg-10 mx-auto">
        <!-- Step 1: Input SNs -->
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <span class="badge bg-primary rounded-circle p-2 me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">1</span>
                    <h5 class="mb-0">Masukkan Serial Number</h5>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">
                        Scan atau paste Serial Number (pisahkan dengan enter / koma)
                    </label>
                    <textarea class="form-control sn-input-item" id="snTextarea" rows="5"
                        placeholder="SN-001&#10;SN-002&#10;SN-003&#10;Atau pisahkan dengan koma: SN-001, SN-002, SN-003"
                        autofocus></textarea>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" onclick="validateBatch()" id="btnValidate">
                        <i class="ti tabler-search me-1"></i> Cari & Validasi
                    </button>
                    <button class="btn btn-outline-secondary" onclick="document.getElementById('snTextarea').value='';clearResults();">
                        <i class="ti tabler-trash me-1"></i> Clear
                    </button>
                    <span id="snCountBadge" class="badge bg-secondary align-self-center px-3 py-2" style="display:none;">0 SN</span>
                </div>
                <div id="validateSpinner" class="text-center mt-2" style="display:none;">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <small class="text-muted ms-2">Memvalidasi data...</small>
                </div>
            </div>
        </div>

        <!-- Step 2: Validation Results Table -->
        <div id="resultTablePanel" class="card shadow-sm mb-3" style="display:none;">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <span class="badge bg-primary rounded-circle p-2 me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">2</span>
                    <h5 class="mb-0">Hasil Validasi</h5>
                    <span id="resultCount" class="ms-2 small text-muted"></span>
                </div>

                <!-- Summary Stats -->
                <div class="row g-2 mb-3" id="summaryStats" style="display:none;">
                    <div class="col-md-4">
                        <div class="card bg-success bg-opacity-10 border-success summary-card">
                            <div class="card-body py-2 px-3 d-flex justify-content-between">
                                <span class="small text-success fw-bold">Valid (Ready)</span>
                                <span class="badge bg-success" id="countValid">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning bg-opacity-10 border-warning summary-card">
                            <div class="card-body py-2 px-3 d-flex justify-content-between">
                                <span class="small text-warning fw-bold">Masih In Stock</span>
                                <span class="badge bg-warning" id="countInStock">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-danger bg-opacity-10 border-danger summary-card">
                            <div class="card-body py-2 px-3 d-flex justify-content-between">
                                <span class="small text-danger fw-bold">Tidak Ditemukan</span>
                                <span class="badge bg-danger" id="countNotFound">0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-bordered batch-table mb-0">
                        <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                            <tr>
                                <th style="width: 40px;">#</th>
                                <th>Serial Number</th>
                                <th>Part Name</th>
                                <th>Brand</th>
                                <th>WH Asset#</th>
                                <th>Lokasi Lama</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="validationTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Step 3: Action & Process -->
        <div id="actionPanel" class="card shadow-sm mb-3" style="display:none;">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <span class="badge bg-primary rounded-circle p-2 me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">3</span>
                    <h5 class="mb-0">Proses Return</h5>
                </div>

                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Tipe Return (Semua Item)</label>
                        <div class="d-flex gap-2">
                            <span class="badge bg-warning option-badge selected px-4 py-2" data-value="in" onclick="selectBatchType(this)" style="font-size:0.9rem; cursor:pointer;">
                                <i class="ti tabler-clipboard-list me-1"></i> In / Staging
                            </span>
                            <span class="badge bg-success option-badge px-4 py-2" data-value="available" onclick="selectBatchType(this)" style="font-size:0.9rem; cursor:pointer;">
                                <i class="ti tabler-circle-check me-1"></i> Available
                            </span>
                        </div>
                        <input type="hidden" id="batchType" value="in">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Update Kondisi (Opsional)</label>
                        <select class="form-select form-select-sm" id="batchCondition">
                            <option value="">-- Tetap seperti lama --</option>
                            <option value="New">New</option>
                            <option value="Refurbished">Refurbished</option>
                            <option value="Faulty">Faulty</option>
                            <option value="Write-off Needed">Write-off Needed</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Yang Diproses</label>
                        <div class="fw-bold text-primary" id="processableCount">0 item valid</div>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" id="btnProcess" onclick="processBatch()">
                            <i class="ti tabler-arrow-back-up me-1"></i> Proses
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 4: Final Result -->
        <div id="finalResultPanel" class="card shadow-sm" style="display:none;">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <span class="badge bg-success rounded-circle p-2 me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                        <i class="ti tabler-check text-white"></i>
                    </span>
                    <h5 class="mb-0">Hasil Proses</h5>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <div class="card bg-success bg-opacity-10 border-success summary-card">
                            <div class="card-body py-2 px-3 d-flex justify-content-between">
                                <span class="small text-success fw-bold">Sukses</span>
                                <span class="badge bg-success" id="finalSuccess">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-danger bg-opacity-10 border-danger summary-card">
                            <div class="card-body py-2 px-3 d-flex justify-content-between">
                                <span class="small text-danger fw-bold">Gagal</span>
                                <span class="badge bg-danger" id="finalFailed">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info bg-opacity-10 border-info summary-card">
                            <div class="card-body py-2 px-3 d-flex justify-content-between">
                                <span class="small text-info fw-bold">Referensi</span>
                                <span class="badge bg-info" id="finalRef">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                    <table class="table table-sm table-bordered batch-table mb-0">
                        <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                            <tr>
                                <th>SN</th>
                                <th>Part Name</th>
                                <th>Tipe</th>
                                <th>Kondisi</th>
                                <th>Lokasi</th>
                                <th>Status</th>
                                <th>Inbound#</th>
                            </tr>
                        </thead>
                        <tbody id="finalTableBody">
                        </tbody>
                    </table>
                </div>

                <div class="d-flex gap-2 justify-content-center mt-3">
                    <button class="btn btn-outline-primary" onclick="resetAll()">
                        <i class="ti tabler-arrow-left me-1"></i> Kembali
                    </button>
                    <a href="{{ route('receiving') }}" class="btn btn-primary">
                        <i class="ti tabler-list me-1"></i> Lihat Receiving
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    let validatedItems = []; // { sn, found, data?, message? }
    let processedResults = [];

    // Auto-focus
    document.getElementById('snTextarea').focus();

    function getSNList() {
        const text = document.getElementById('snTextarea').value.trim();
        if (!text) return [];

        // Split by newline or comma
        const sns = text.split(/[\n,]+/).map(s => s.trim()).filter(s => s.length > 0);
        // Deduplicate
        return [...new Set(sns)];
    }

    function updateSNCount() {
        const sns = getSNList();
        const badge = document.getElementById('snCountBadge');
        if (sns.length > 0) {
            badge.style.display = 'inline';
            badge.textContent = sns.length + ' SN';
        } else {
            badge.style.display = 'none';
        }
    }

    document.getElementById('snTextarea').addEventListener('input', updateSNCount);

    async function validateBatch() {
        const sns = getSNList();
        if (sns.length === 0) {
            Swal.fire('Error', 'Silakan masukkan minimal 1 Serial Number.', 'error');
            return;
        }

        if (sns.length > 100) {
            Swal.fire('Error', 'Maksimal 100 SN per batch.', 'error');
            return;
        }

        document.getElementById('btnValidate').disabled = true;
        document.getElementById('validateSpinner').style.display = 'block';
        document.getElementById('resultTablePanel').style.display = 'none';
        document.getElementById('actionPanel').style.display = 'none';
        document.getElementById('finalResultPanel').style.display = 'none';

        validatedItems = [];
        let validCount = 0, inStockCount = 0, notFoundCount = 0;

        // Process sequentially to show progress
        const tbody = document.getElementById('validationTableBody');
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">Memvalidasi...</td></tr>';
        document.getElementById('resultTablePanel').style.display = 'block';

        for (let i = 0; i < sns.length; i++) {
            const sn = sns[i];
            try {
                const res = await fetch('{{ route('receiving.check-outbounded') }}?search=' + encodeURIComponent(sn));
                const data = await res.json();

                if (data.status) {
                    validatedItems.push({ sn, found: true, data: data.data, message: null });
                    validCount++;
                } else if (data.message && data.message.includes('masih IN STOCK')) {
                    validatedItems.push({ sn, found: false, data: null, message: data.message });
                    inStockCount++;
                } else {
                    validatedItems.push({ sn, found: false, data: null, message: data.message || 'Tidak ditemukan' });
                    notFoundCount++;
                }
            } catch (err) {
                validatedItems.push({ sn, found: false, data: null, message: 'Error koneksi' });
                notFoundCount++;
            }

            // Update progress row
            renderValidationTable();
            updateSummary(validCount, inStockCount, notFoundCount);
        }

        document.getElementById('btnValidate').disabled = false;
        document.getElementById('validateSpinner').style.display = 'none';

        // Show process panel if there are valid items
        if (validCount > 0) {
            document.getElementById('processableCount').textContent = validCount + ' item siap diproses';
            document.getElementById('actionPanel').style.display = 'block';
        }
    }

    function renderValidationTable() {
        const tbody = document.getElementById('validationTableBody');
        let html = '';
        let idx = 0;

        validatedItems.forEach((item, i) => {
            idx++;
            if (item.found) {
                const d = item.data;
                html += `
                    <tr class="result-row-success">
                        <td>${idx}</td>
                        <td class="fw-bold">${d.serial_number}</td>
                        <td>${d.part_name || '-'}</td>
                        <td>${d.brand || '-'}</td>
                        <td>${d.unique_id || '-'}</td>
                        <td>${d.old_location || '-'}</td>
                        <td><span class="badge bg-success status-badge-result">Ready</span></td>
                    </tr>`;
            } else {
                const isInStock = item.message && item.message.includes('IN STOCK');
                html += `
                    <tr class="result-row-error">
                        <td>${idx}</td>
                        <td class="fw-bold text-danger">${item.sn}</td>
                        <td colspan="4" class="text-muted">${item.message || 'Tidak ditemukan'}</td>
                        <td><span class="badge bg-${isInStock ? 'warning' : 'danger'} status-badge-result">${isInStock ? 'In Stock' : 'Error'}</span></td>
                    </tr>`;
            }
        });

        tbody.innerHTML = html;
    }

    function updateSummary(valid, inStock, notFound) {
        document.getElementById('summaryStats').style.display = 'flex';
        document.getElementById('countValid').textContent = valid;
        document.getElementById('countInStock').textContent = inStock;
        document.getElementById('countNotFound').textContent = notFound;
        document.getElementById('resultCount').textContent = `(${validatedItems.length} total)`;
    }

    function selectBatchType(el) {
        document.querySelectorAll('.option-badge').forEach(b => b.classList.remove('selected'));
        el.classList.add('selected');
        document.getElementById('batchType').value = el.dataset.value;
    }

    async function processBatch() {
        const validItems = validatedItems.filter(item => item.found);
        if (validItems.length === 0) {
            Swal.fire('Error', 'Tidak ada item valid untuk diproses.', 'error');
            return;
        }

        const batchType = document.getElementById('batchType').value;
        const condition = document.getElementById('batchCondition').value;
        const typeLabel = batchType === 'available' ? 'Bypass PA' : 'Butuh Put Away';

        const confirmHtml = validItems.slice(0, 20).map(item =>
            `<tr><td class="small">${item.sn}</td><td class="small">${item.data.part_name || '-'}</td><td><span class="badge bg-${batchType === 'available' ? 'success' : 'warning'} status-badge-result">${typeLabel}</span></td></tr>`
        ).join('');

        Swal.fire({
            title: 'Konfirmasi Batch Return',
            html: `
                <div class="text-start">
                    <p class="mb-2">Anda akan me-<em>return</em> <strong>${validItems.length} item</strong> sebagai <strong>${typeLabel}</strong></p>
                    <div class="table-responsive" style="max-height: 250px;">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light"><tr><th>SN</th><th>Item</th><th>Tipe</th></tr></thead>
                            <tbody>${confirmHtml}
                                ${validItems.length > 20 ? `<tr><td colspan="3" class="text-muted text-center small">...dan ${validItems.length - 20} lainnya</td></tr>` : ''}
                            </tbody>
                        </table>
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Proses Semua!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                doProcessBatch(batchType, condition);
            }
        });
    }

    async function doProcessBatch(batchType, condition) {
        Swal.fire({
            title: 'Processing...',
            text: 'Memproses return item...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        try {
            const res = await fetch('{{ route('receiving.quick-return.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    batch: true,
                    serial_numbers: validatedItems.filter(i => i.found).map(i => i.sn),
                    return_type: batchType,
                    condition: condition || ''
                })
            });

            const data = await res.json();
            Swal.close();

            if (data.status) {
                showBatchResult(data.results, batchType);
            } else {
                Swal.fire('Error', data.message || 'Gagal memproses batch return.', 'error');
            }
        } catch (err) {
            Swal.close();
            Swal.fire('Error', 'Terjadi kesalahan koneksi.', 'error');
        }
    }

    function showBatchResult(results, batchType) {
        document.getElementById('resultTablePanel').style.display = 'none';
        document.getElementById('actionPanel').style.display = 'none';
        document.getElementById('finalResultPanel').style.display = 'block';

        const typeLabel = batchType === 'available' ? 'Bypass PA' : 'Butuh Put Away';

        let successCount = 0, failedCount = 0;
        let tbodyHtml = '';
        let firstInbound = '';

        results.forEach((r, i) => {
            if (r.success) {
                successCount++;
                if (!firstInbound) firstInbound = r.inbound_number;
                tbodyHtml += `
                    <tr class="result-row-success">
                        <td class="fw-bold">${r.serial_number}</td>
                        <td>${r.part_name || '-'}</td>
                        <td><span class="badge bg-${batchType === 'available' ? 'success' : 'warning'} status-badge-result">${typeLabel}</span></td>
                        <td>${r.condition || '-'}</td>
                        <td>${r.old_location || '-'}</td>
                        <td><span class="badge bg-success status-badge-result">✓ Sukses</span></td>
                        <td class="small">${r.inbound_number}</td>
                    </tr>`;
            } else {
                failedCount++;
                tbodyHtml += `
                    <tr class="result-row-error">
                        <td class="fw-bold text-danger">${r.serial_number}</td>
                        <td colspan="2">-</td>
                        <td colspan="2">-</td>
                        <td><span class="badge bg-danger status-badge-result">✗ Gagal</span></td>
                        <td class="small text-danger">${r.error || '-'}</td>
                    </tr>`;
            }
        });

        document.getElementById('finalSuccess').textContent = successCount;
        document.getElementById('finalFailed').textContent = failedCount;
        document.getElementById('finalRef').textContent = results.length > 0 ? firstInbound + '...' : '-';
        document.getElementById('finalTableBody').innerHTML = tbodyHtml;
    }

    function resetAll() {
        document.getElementById('snTextarea').value = '';
        document.getElementById('resultTablePanel').style.display = 'none';
        document.getElementById('actionPanel').style.display = 'none';
        document.getElementById('finalResultPanel').style.display = 'none';
        document.getElementById('summaryStats').style.display = 'none';
        document.getElementById('snCountBadge').style.display = 'none';
        document.getElementById('batchCondition').value = '';
        document.getElementById('batchType').value = 'in';

        document.querySelectorAll('.option-badge').forEach(b => b.classList.remove('selected'));
        document.querySelector('.option-badge[data-value="in"]').classList.add('selected');

        validatedItems = [];
        processedResults = [];
        document.getElementById('snTextarea').focus();
    }

    function clearResults() {
        resetAll();
    }
</script>
@endsection