@extends('layout.index')
@section('title', 'Print Outbound Report')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <a href="{{ route('outbound.index') }}" class="btn btn-label-secondary fw-bold px-3">
            <i class="ti tabler-arrow-left me-1"></i> Back to Documents
        </a>
        <button onclick="window.print()" class="btn btn-primary fw-bold px-4 shadow-sm">
            <i class="ti tabler-printer me-1"></i> Finalize & Print
        </button>
    </div>

    <div class="row" id="print-area">
        <div class="col-12 mb-4">
            <!-- Header (Logo / Branding) -->
            <div class="row border-bottom border-3 border-primary pb-3 mb-4 align-items-center">
                <div class="col-sm-6">
                    <h2 class="text-primary fw-bold mb-0" style="letter-spacing: -1px;">OUTBOUND REPORT</h2>
                    <span class="badge bg-label-primary px-3 py-1 rounded-pill fw-bold">{{ $outbound->number }}</span>
                </div>
                <div class="col-sm-6 text-sm-end">
                    <h4 class="mb-1 fw-bold text-dark">TRANS KARGO SOLUSINDO</h4>
                    <p class="text-muted mb-0 small text-uppercase fw-medium ls-1">WMS Spare Part & Fulfillment Center</p>
                </div>
            </div>

            <!-- Transaction Grid -->
            <div class="row mb-4">
                <div class="col-md-4 border-end">
                    <h6 class="text-muted fw-bold text-uppercase small ls-1 mb-3">Transaction Details</h6>
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="ps-0 text-muted">Stock Category:</td>
                            <td class="fw-bold text-dark">{{ $outbound->category }}</td>
                        </tr>
                        <tr>
                            <td class="ps-0 text-muted">Request Type:</td>
                            <td class="fw-bold text-primary">{{ $outbound->request_type }}</td>
                        </tr>
                        <tr>
                            <td class="ps-0 text-muted">Outbound date:</td>
                            <td class="fw-bold text-dark">{{ $outbound->outbound_date }}</td>
                        </tr>
                        <tr>
                            <td class="ps-0 text-muted">Outbound by:</td>
                            <td class="fw-bold text-dark">{{ $outbound->outbound_by }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-4 border-end">
                    <h6 class="text-muted fw-bold text-uppercase small ls-1 mb-3">Client Information</h6>
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="ps-0 text-muted">Client:</td>
                            <td class="fw-bold text-dark text-truncate d-inline-block" style="max-width: 150px;">
                                {{ $outbound->client->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="ps-0 text-muted">Requestor:</td>
                            <td class="fw-bold text-dark">{{ $outbound->ntt_requestor ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="ps-0 text-muted">Request Date:</td>
                            <td class="fw-bold text-dark">{{ $outbound->request_date ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-4">
                    <h6 class="text-muted fw-bold text-uppercase small ls-1 mb-3">Reference Numbers</h6>
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="ps-0 text-muted">SAP PO #:</td>
                            <td class="fw-bold text-dark">{{ $outbound->sap_po_number ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="ps-0 text-muted">TKS DN / Ref#:</td>
                            <td class="fw-bold text-primary">{{ $outbound->tks_dn_number ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="ps-0 text-muted">TKS Inv #:</td>
                            <td class="fw-bold text-dark">{{ $outbound->tks_invoice_number ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="ps-0 text-muted">RMA / ITSM #:</td>
                            <td class="fw-bold text-dark small">{{ $outbound->rma_number ?? '-' }} /
                                {{ $outbound->itsm_number ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Product Table -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered border-dark printable-table">
                    <thead class="bg-light-subtle">
                        <tr class="text-center align-middle">
                            <th style="width: 4%">#</th>
                            <th style="width: 25%">Product / Part Name</th>
                            <th style="width: 15%">Part Number (SKU)</th>
                            <th style="width: 15%">Serial Number (SN)</th>
                            <th style="width: 15%">WH Asset #</th>
                            <th style="width: 10%">Condition</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($outbound->details as $detail)
                            <tr class="align-middle">
                                <td class="text-center fw-bold">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $detail->part_name }}</div>
                                </td>
                                <td class="text-center">{{ $detail->part_number }}</td>
                                <td class="text-center fw-bold text-primary font-monospace">{{ $detail->serial_number }}
                                </td>
                                <td class="text-center small">{{ $detail->inventory->unique_id ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge border border-dark text-dark fw-bold"
                                        style="font-size: 0.65rem;">{{ strtoupper($detail->condition) }}</span>
                                </td>
                                <td class="small">{{ $detail->description ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">No records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Total & Signature Grid -->
            <div class="row mt-5">
                <div class="col-md-6 border rounded p-3">
                    <h6 class="text-muted fw-bold text-uppercase small ls-1 mb-2">Shipment Summary</h6>
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <p class="mb-1 text-muted">Total Quantity Dispatched:</p>
                            <h2 class="mb-0 fw-bold">{{ $outbound->qty }} <small class="text-muted fs-6">Items</small></h2>
                        </div>
                        <div class="text-end">
                            <p class="mb-1 text-muted">Current Status:</p>
                            <span class="badge bg-primary px-3 py-1">{{ strtoupper($outbound->status) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 pt-4 text-center">
                    <div class="row">
                        <div class="col-6">
                            <p class="mb-5 small text-muted">Issued By (Warehouse)</p>
                            <div class="mx-auto border-top border-dark d-inline-block pt-1 fw-bold" style="width: 150px;">
                                {{ $outbound->outbound_by }}
                            </div>
                        </div>
                        <div class="col-6">
                            <p class="mb-5 small text-muted">Received By / Carrier</p>
                            <div class="mx-auto border-top border-dark d-inline-block pt-1 fw-bold" style="width: 150px;">
                                (Signature / Stamp)
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-5 pt-5 opacity-25">
                <small class="text-muted">Generated by TKS WMS - System Auto-Generated Report</small>
            </div>
        </div>
    </div>

    <!-- Page Numbering container (fixed bottom right for each page via CSS) -->
    <div id="pageFooter" class="d-none">
        Page <span class="pageNumber"></span> of <span class="totalPages"></span>
    </div>
    </div>
    </div>

    <style>
        .ls-1 {
            letter-spacing: 1px;
        }

        @media print {
            @page {
                size: A4;
                margin: 15mm;
            }

            body {
                background: white !important;
            }

            body * {
                visibility: hidden;
            }

            #print-area,
            #print-area * {
                visibility: visible;
            }

            ::before,
            ::after {
                visibility: visible !important;
            }

            #print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            .no-print,
            .navbar,
            .menu,
            .layout-navbar,
            .layout-menu,
            footer {
                display: none !important;
            }

            .pdf-card {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
            }

            .printable-table th,
            .printable-table td {
                padding: 10px !important;
                font-size: 12px;
                border-color: #000 !important;
            }

            .bg-light {
                background-color: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .badge {
                border: 1px solid #000 !important;
                color: #000 !important;
                background: transparent !important;
            }

            .text-primary {
                color: #000 !important;
            }

            /* Page Numbering Simulation via print */
            #pageFooter {
                display: block !important;
                position: fixed;
                bottom: -10mm;
                right: 0;
                font-size: 12px;
                color: #555;
            }

            @page {
                @bottom-right {
                    content: "Page " counter(page) " of " counter(pages);
                }
            }
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Optional: Automatically trigger print when page loads
            // setTimeout(() => { window.print(); }, 500);
        });
    </script>
@endsection
