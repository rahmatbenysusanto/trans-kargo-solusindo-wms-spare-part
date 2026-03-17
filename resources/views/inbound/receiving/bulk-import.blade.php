@extends('layout.index')
@section('title', 'Bulk Import Receiving')
@section('layout_class', 'layout-menu-collapsed')

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
        .header-required {
            font-weight: bold;
            color: #0d6efd;
        }
    </style>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#client_id').select2({
                placeholder: "-- Choose Client --",
                allowClear: true,
                width: '100%'
            });
        });

        let groupedReceivings = [];

        function downloadTemplate() {
            const headers = [
                ["Product Number (SKU)", "Product Description", "Qty", "Serial Number (SN)", "Product Group", "Received Date", "Stock Category", "ITSM#", "SAP PO#", "Brand", "Box / Pallet"]
            ];
            const dataExample = [
                ["WS-C2960-48TT-L", "CATALYST 2960 48 LAN B", 1, "FOC1133Z5GC", "Switches", "20/03/18", "RMA", "SVO121024758", "4500463812", "CISCO", "Pallet 8"],
                ["N2K-F10G-F10G", "N2K UPLINK OPTION FET", 1, "270720SER1", "Modules,Cards", "14/08/20", "New PO", "", "4500974960", "CISCO", "24"]
            ];
            
            const worksheet = XLSX.utils.aoa_to_sheet([...headers, ...dataExample]);
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, "Template");
            XLSX.writeFile(workbook, "Template_Bulk_Import_Receiving.xlsx");
        }

        function parseExcelDate(dateStr) {
            if (!dateStr) return '';
            if (typeof dateStr === 'string' && dateStr.includes('/')) {
                const parts = dateStr.split('/');
                if (parts.length === 3) {
                    let m = parts[1].padStart(2, '0');
                    if(parseInt(m) > 12) {
                        m = parts[0].padStart(2, '0');
                        parts[0] = parts[1];
                    }
                    let d = parts[0].padStart(2, '0');
                    let y = parts[2];
                    if (y.length === 2) y = '20' + y;
                    return `${y}-${m}-${d}`;
                }
            }
            if (dateStr instanceof Date) {
                return dateStr.toISOString().split('T')[0];
            }
            return dateStr;
        }

        function processExcel(e) {
            if (e) e.preventDefault();
            const fileInput = document.getElementById('excelFile');
            const file = fileInput.files[0];
            if (!file) {
                Swal.fire('Error', 'Please select an Excel file first.', 'error');
                return;
            }

            Swal.fire({
                title: 'Parsing File...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const reader = new FileReader();
            reader.onload = function(e) {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, {
                    type: 'array',
                    cellDates: true,
                    dateNF: 'yyyy-mm-dd'
                });
                
                let bestSheetName = workbook.SheetNames[0];
                let bestHeaderRowIndex = -1;
                let bestColMap = {};
                let overallMaxScore = 0;

                const findIdx = (row, keywords) => {
                    for (let j = 0; j < row.length; j++) {
                        let cell = String(row[j] || '').toLowerCase().trim();
                        if (keywords.some(k => cell === k || cell.includes(k))) return j;
                    }
                    return undefined;
                };

                // Scan all sheets to find which one is the actual data sheet
                workbook.SheetNames.forEach(sheetName => {
                    const ws = workbook.Sheets[sheetName];
                    const data = XLSX.utils.sheet_to_json(ws, { header: 1, raw: false, defval: '' });
                    
                    for (let i = 0; i < Math.min(30, data.length); i++) {
                        const row = data[i];
                        if (!row || row.length < 5) continue;

                        let tempColMap = {
                            sn: findIdx(row, ['serial number', 'sn']),
                            pn: findIdx(row, ['product number', 'sku', 'part number', 'pn']),
                            desc: findIdx(row, ['description']),
                            category: findIdx(row, ['stock category', 'category']),
                            po: findIdx(row, ['sap', 'po #', 'po#']),
                            itsm: findIdx(row, ['itsm']),
                            qty: findIdx(row, ['qty']),
                            group: findIdx(row, ['group']),
                            brand: findIdx(row, ['brand']),
                            date: findIdx(row, ['date'])
                        };

                        let currentScore = 0;
                        if (tempColMap.sn !== undefined) currentScore += 10;
                        if (tempColMap.pn !== undefined) currentScore += 10;
                        if (tempColMap.category !== undefined) currentScore += 10;
                        if (tempColMap.po !== undefined || tempColMap.itsm !== undefined) currentScore += 8;
                        if (tempColMap.desc !== undefined) currentScore += 5;

                        if (currentScore > overallMaxScore) {
                            overallMaxScore = currentScore;
                            bestSheetName = sheetName;
                            bestHeaderRowIndex = i;
                            bestColMap = tempColMap;
                        }
                    }
                });

                const worksheet = workbook.Sheets[bestSheetName];
                const rawData = XLSX.utils.sheet_to_json(worksheet, { header: 1, raw: false, defval: '' });
                let headerRowIndex = bestHeaderRowIndex;
                let colMap = bestColMap;

                if (overallMaxScore < 20) {
                    console.warn("No high-confidence header found. Defaulting to first sheet.");
                    headerRowIndex = 0;
                    colMap = { pn: 0, desc: 1, qty: 2, sn: 3, group: 4, date: 5, category: 6, itsm: 7, po: 8, brand: 9 };
                }

                console.log(`Auto-selected sheet: "${bestSheetName}" at row ${headerRowIndex + 1} (Score: ${overallMaxScore})`);
                console.table(colMap);
                
                let receivingsMap = {};
                let snCounts = {};

                // Pass 1: Count non-empty SNs
                for (let i = headerRowIndex + 1; i < rawData.length; i++) {
                    const row = rawData[i];
                    if (!row) continue;
                    let sn = String(row[colMap['sn']] || '').trim();
                    if (sn && sn !== '' && sn !== '-' && sn !== 'N/A') {
                        snCounts[sn] = (snCounts[sn] || 0) + 1;
                    }
                }

                // Pass 2: Process Rows
                for (let i = headerRowIndex + 1; i < rawData.length; i++) {
                    const row = rawData[i];
                    if (!row || row.length < 2) continue;

                    const val = (k) => {
                        let j = colMap[k];
                        if (j === undefined || row[j] === undefined || row[j] === null) return '';
                        return String(row[j]).trim();
                    };

                    let pn = val('pn');
                    let sn = val('sn');
                    let cat = val('category') || 'New PO';
                    let desc = val('desc');
                    let sap = val('po');
                    let itsm = val('itsm');
                    let grp = val('group');
                    let brd = val('brand');
                    let dt = val('date');

                    if (!pn && !sn) continue; 

                    let isDup = (sn && sn !== '' && sn !== '-' && snCounts[sn] > 1);
                    
                    // Grouping: Category + (SAP PO || ITSM || NO_REF)
                    let ref = sap !== '' ? sap : (itsm !== '' ? itsm : 'NO_REF');
                    let key = `${cat}_${ref}`;
                    
                    if (!receivingsMap[key]) {
                        receivingsMap[key] = {
                            category: cat,
                            sap_po_number: sap,
                            itsm_number: itsm,
                            receivedDate: parseExcelDate(dt) || new Date().toISOString().split('T')[0],
                            products: []
                        };
                    }

                    receivingsMap[key].products.push({
                        id: Math.random().toString(36).substr(2, 9),
                        isDuplicate: isDup,
                        partName: pn,
                        partNumber: pn,
                        partDescription: desc,
                        serialNumber: sn,
                        productGroup: grp,
                        brand: brd,
                        condition: 'New',
                        qty: 1
                    });
                }

                groupedReceivings = Object.values(receivingsMap);
                console.log("Final Grouped Data:", groupedReceivings);

                Swal.close();
                
                if (groupedReceivings.length === 0) {
                    Swal.fire({
                        title: 'Error',
                        text: `File processed but 0 valid data rows mapped. Check your columns and make sure they contain records. (Header mapped at row: ${headerRowIndex + 1})`,
                        icon: 'error'
                    });
                    console.error("Mapped Columns:", colMap);
                    console.error("Sample Data row:", rawData.length > headerRowIndex + 1 ? rawData[headerRowIndex + 1] : "None");
                    return;
                }

                if (missingCount > 0) {
                    console.log(`Found ${missingCount} rows with incomplete info (missing SN or Category).`);
                }

                renderPreview();
            };
            reader.readAsArrayBuffer(file);
        }

        function renderPreview() {
            const tbody = document.getElementById('previewTableBody');
            let html = '';
            
            let totalReceiving = groupedReceivings.length;
            let totalProducts = 0;

            groupedReceivings.forEach((rec, index) => {
                totalProducts += rec.products.length;
                
                // Check if this group has any duplicates
                const hasDuplicate = rec.products.some(p => p.isDuplicate);
                const rowClass = hasDuplicate ? 'table-light-danger' : '';

                html += `
                    <tr class="${rowClass}">
                        <td class="text-center">${index + 1}</td>
                        <td>
                            <span class="fw-bold text-primary">${rec.category}</span>
                            ${hasDuplicate ? '<br><span class="badge bg-danger mt-1 small"><i class="ti tabler-alert-triangle tiny"></i> Has Duplicates</span>' : ''}
                        </td>
                        <td>${rec.sap_po_number ? `<span class="badge bg-label-info">SAP: ${rec.sap_po_number}</span>` : '-'}</td>
                        <td>${rec.itsm_number ? `<span class="badge bg-label-warning">ITSM: ${rec.itsm_number}</span>` : '-'}</td>
                        <td>${rec.receivedDate}</td>
                        <td class="text-center"><span class="badge bg-primary">${rec.products.length} Items</span></td>
                        <td class="text-center">
                            <button class="btn btn-xs ${hasDuplicate ? 'btn-danger' : 'btn-outline-info'}" onclick="viewItems(${index})">
                                <i class="ti tabler-eye"></i> View Items
                            </button>
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
            document.getElementById('previewContainer').classList.remove('d-none');
            document.getElementById('summaryTitle').innerText = `Preview: ${totalReceiving} Receivings (${totalProducts} Items)`;
            document.getElementById('btnSubmit').disabled = false;
        }

        function viewItems(index) {
            const data = groupedReceivings[index];
            const tbody = document.getElementById('itemPreviewTableBody');
            let html = '';
            
            data.products.forEach((p, i) => {
                let rowClass = p.isDuplicate ? 'table-danger' : '';
                html += `
                    <tr class="${rowClass}">
                        <td>${i + 1}</td>
                        <td>${p.partNumber}</td>
                        <td class="text-truncate" style="max-width: 200px;" title="${p.partDescription}">${p.partDescription || '-'}</td>
                        <td class="fw-bold text-nowrap">
                            ${p.serialNumber}
                            ${p.isDuplicate ? '<br><span class="badge bg-danger mt-1"><i class="ti tabler-alert-circle"></i> Duplicate</span>' : ''}
                        </td>
                        <td>${p.brand}</td>
                        <td>${p.productGroup}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-danger px-2 py-1" onclick="removeItem(${index}, '${p.id}')">
                                <i class="ti tabler-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
            document.getElementById('modalTitle').innerText = `Items for Category: ${data.category} | SAP: ${data.sap_po_number || 'N/A'}`;
            $('#itemsModal').modal('show');
        }

        function removeItem(groupIndex, productId) {
            let group = groupedReceivings[groupIndex];
            group.products = group.products.filter(p => p.id !== productId);
            
            recalculateDuplicates(); 

            if(group.products.length === 0) {
                 groupedReceivings.splice(groupIndex, 1);
                 $('#itemsModal').modal('hide');
            } else {
                 viewItems(groupIndex); // Re-render modal table
            }
            renderPreview();
        }

        function recalculateDuplicates() {
            let snCounts = {};
            groupedReceivings.forEach(g => {
                g.products.forEach(p => {
                    if(p.serialNumber) {
                        snCounts[p.serialNumber] = (snCounts[p.serialNumber] || 0) + 1;
                    }
                });
            });

            groupedReceivings.forEach(g => {
                g.products.forEach(p => {
                    p.isDuplicate = (snCounts[p.serialNumber] > 1);
                });
            });
        }

        async function submitBulk() {
            let hasDuplicates = false;
            groupedReceivings.forEach(g => {
                g.products.forEach(p => {
                    if (p.isDuplicate) hasDuplicates = true;
                });
            });

            if (hasDuplicates) {
                Swal.fire('Error', 'There are still duplicate Serial Numbers. Please check the "View Items" and delete the duplicate rows (marked in red) before confirming.', 'error');
                return;
            }

            const clientId = document.getElementById('client_id').value;
            
            if (!clientId) {
                Swal.fire('Error', 'Please select a Client before confirming.', 'error');
                return;
            }

            if (groupedReceivings.length === 0) {
                Swal.fire('Error', 'No data to submit. Please upload and parse Excel first.', 'error');
                return;
            }

            Swal.fire({
                title: 'Confirm Creation?',
                text: `You are about to create ${groupedReceivings.length} Receiving Records.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, create all!'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Processing...',
                        html: 'Do not close or refresh this page.',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading() }
                    });

                    try {
                        const response = await fetch('{{ route('receiving.bulk-import.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                client_id: clientId,
                                receivings: groupedReceivings
                            })
                        });

                        const resultData = await response.json();

                        if (resultData.status) {
                            Swal.fire({
                                title: 'Success!',
                                text: 'All Receivings have been successfully created.',
                                icon: 'success'
                            }).then(() => {
                                window.location.href = '{{ route('receiving') }}';
                            });
                        } else {
                            Swal.fire('Error', resultData.message || 'Failed to create receivings.', 'error');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        Swal.fire('Error', 'An unexpected error occurred.', 'error');
                    }
                }
            });
        }
    </script>
@endsection

@section('content')
    <div class="row">
        <div class="col-12 mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-1">Bulk Import Receiving</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb" style="font-size: 0.85rem; margin-bottom: 0;">
                            <li class="breadcrumb-item"><a href="{{ route('receiving') }}">Receiving</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Bulk Import</li>
                        </ol>
                    </nav>
                </div>
                <a class="btn btn-secondary text-white" onclick="downloadTemplate()">
                    <i class="ti tabler-download me-1"></i> Download Template
                </a>
            </div>
        </div>

        <div class="col-12">
            <!-- Instruction & Upload Section -->
            <div class="card mb-4">
                <div class="card-header bg-label-primary border-bottom py-3">
                    <h5 class="card-title mb-0 text-primary"><i class="ti tabler-info-circle me-1"></i> Import Instructions</h5>
                </div>
                <div class="card-body pt-3 pb-4">
                    <div class="row">
                        <div class="col-md-7">
                            <p class="mb-2">Ensure your Excel file follows the correct header format. The system will automatically group products into separate receiving transactions based on the combination of <strong>Stock Category</strong> and <strong>Reference Number (Priority: SAP PO#, then ITSM#)</strong>.</p>
                            <p class="mb-2">Required headers for the imported file:</p>
                            <ul class="row small text-muted list-unstyled ps-0 mb-3">
                                <li class="col-6 mb-1"><i class="ti tabler-point text-primary"></i> <span class="header-required">Product Number (SKU)</span></li>
                                <li class="col-6 mb-1"><i class="ti tabler-point text-primary"></i> <span class="header-required">Serial Number (SN)</span></li>
                                <li class="col-6 mb-1"><i class="ti tabler-point text-primary"></i> <span class="header-required">Stock Category</span></li>
                                <li class="col-6 mb-1"><i class="ti tabler-point text-primary"></i> <span class="header-required">SAP PO# or ITSM#</span></li>
                                <li class="col-6 mb-1"><i class="ti tabler-point"></i> Product Description</li>
                                <li class="col-6 mb-1"><i class="ti tabler-point"></i> Qty</li>
                                <li class="col-6 mb-1"><i class="ti tabler-point"></i> Product Group</li>
                                <li class="col-6 mb-1"><i class="ti tabler-point"></i> Received Date</li>
                                <li class="col-6 mb-1"><i class="ti tabler-point"></i> Brand</li>
                                <li class="col-6 mb-1"><i class="ti tabler-point"></i> Box / Pallet</li>
                            </ul>
                        </div>
                        <div class="col-md-5 border-start ps-4">
                            <h6 class="fw-bold mb-3">Upload Configuration</h6>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Select Client <span class="text-danger">*</span></label>
                                <select class="form-control select2" name="client_id" id="client_id">
                                    <option value="">-- Choose Client --</option>
                                    @foreach ($client as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block mt-1">This client will be assigned to all imported receiving records.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Upload Excel File</label>
                                <div class="input-group">
                                    <input type="file" class="form-control" id="excelFile" accept=".xlsx, .xls">
                                    <button type="button" class="btn btn-primary" onclick="processExcel(event)">Preview Data</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview Section -->
            <div id="previewContainer" class="card d-none">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0" id="summaryTitle">Preview Data</h5>
                    <button id="btnSubmit" class="btn btn-success px-4" onclick="submitBulk()" disabled>
                        <i class="ti tabler-check me-1"></i> Confirm & Create All
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped text-nowrap mb-0 align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center" width="50">#</th>
                                <th>Category</th>
                                <th>SAP PO#</th>
                                <th>ITSM#</th>
                                <th>Date</th>
                                <th class="text-center">Total Products</th>
                                <th class="text-center" width="120">Action</th>
                            </tr>
                        </thead>
                        <tbody id="previewTableBody">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for viewing items detail -->
    <div class="modal fade" id="itemsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Items Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive" style="max-height: 500px">
                        <table class="table table-sm table-striped">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>#</th>
                                    <th>SKU / Part Number</th>
                                    <th>Product Description</th>
                                    <th>Serial Number</th>
                                    <th>Brand</th>
                                    <th>Group</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="itemPreviewTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer pb-2 pt-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

@endsection
