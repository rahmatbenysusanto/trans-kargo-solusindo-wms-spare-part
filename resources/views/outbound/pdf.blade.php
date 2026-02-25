@extends('layout.index')
@section('title', 'Print Outbound Report')

@section('content')
    <div class="row" id="print-area">
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                <a href="{{ route('outbound.index') }}" class="btn btn-secondary">
                    <i class="ti tabler-arrow-left me-1"></i> Back to List
                </a>
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="ti tabler-printer me-1"></i> Print Report
                </button>
            </div>

            <div class="card pdf-card shadow-none">
                <div class="card-body p-5">
                    <!-- Report Header -->
                    <div class="row border-bottom pb-4 mb-4 align-items-center">
                        <div class="col-sm-6">
                            <h2 class="text-primary fw-bold mb-1">OUTBOUND REPORT</h2>
                            <p class="text-muted mb-0">Document Number: <strong>{{ $outbound->number ?? 'N/A' }}</strong>
                            </p>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <h4 class="mb-1 fw-bold text-dark">TRANS KARGO SOLUSINDO</h4>
                            <p class="text-muted mb-0 small">
                                Warehouse Management System<br>
                                Report Generated: {{ date('F d, Y h:i A') }}
                            </p>
                        </div>
                    </div>

                    <!-- General Info -->
                    <div class="row align-items-start mb-4">
                        <div class="col-sm-6">
                            <h6 class="text-uppercase text-muted fw-bold mb-3 ls-1">Client Information</h6>
                            <h5 class="fw-bold text-dark mb-1">{{ $outbound->client->name ?? 'N/A' }}</h5>
                            <p class="mb-1"><span class="text-muted">Category:</span> {{ $outbound->category }}</p>
                            <p class="mb-1"><span class="text-muted">Outbound By:</span>
                                {{ $outbound->outbound_by ?? '-' }}</p>
                            <p class="mb-0"><span class="text-muted">Outbound Date:</span> {{ $outbound->outbound_date }}
                            </p>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <h6 class="text-uppercase text-muted fw-bold mb-3 ls-1">Reference Details</h6>
                            <p class="mb-1"><span class="text-muted">NTT DN Number:</span>
                                {{ $outbound->ntt_dn_number ?? '-' }}</p>
                            <p class="mb-1"><span class="text-muted">TKS DN Number:</span>
                                {{ $outbound->tks_dn_number ?? '-' }}</p>
                            <p class="mb-1"><span class="text-muted">TKS Invoice:</span>
                                {{ $outbound->tks_invoice_number ?? '-' }}</p>
                            <p class="mb-1"><span class="text-muted">RMA / ITSM:</span>
                                {{ $outbound->rma_number ?? '-' }} / {{ $outbound->itsm_number ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive mb-4">
                        <h6 class="text-uppercase text-muted fw-bold mb-3 ls-1">Product Items</h6>
                        <table class="table table-bordered border-dark printable-table">
                            <thead class="table-light border-dark">
                                <tr>
                                    <th class="text-center" style="width: 5%">#</th>
                                    <th style="width: 25%">Part Name</th>
                                    <th style="width: 20%">Part Number</th>
                                    <th style="width: 20%">Serial Number</th>
                                    <th class="text-center" style="width: 10%">Condition</th>
                                    <th style="width: 20%">Description</th>
                                </tr>
                            </thead>
                            <tbody class="border-dark">
                                @forelse ($outbound->details as $detail)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $detail->part_name }}</td>
                                        <td><strong>{{ $detail->part_number }}</strong></td>
                                        <td>{{ $detail->serial_number }}</td>
                                        <td class="text-center">{{ $detail->condition }}</td>
                                        <td>{{ $detail->description ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No details found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Summary & Signature -->
                    <div class="row pt-4">
                        <div class="col-sm-6">
                            <div class="p-3 bg-light rounded shadow-sm border">
                                <h6 class="text-uppercase fw-bold mb-2 ls-1">Summary</h6>
                                <p class="mb-1"><strong>Total Quantity:</strong> {{ $outbound->qty }} Items</p>
                                <p class="mb-0"><strong>Status:</strong> <span
                                        class="badge bg-secondary">{{ $outbound->status }}</span></p>
                            </div>
                        </div>
                        <div class="col-sm-6 text-center mt-4 mt-sm-0 pt-3">
                            <p class="mb-5 text-muted">Authorized Signature</p>
                            <p class="fw-bold text-dark border-top border-dark d-inline-block pt-1" style="width: 200px;">
                                {{ $outbound->outbound_by ?? 'Warehouse Manager' }}
                            </p>
                        </div>
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
