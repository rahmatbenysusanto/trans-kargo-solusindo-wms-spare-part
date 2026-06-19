@extends('layout.pdf')
@section('title', 'Print Inbound Report')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <a href="{{ route('receiving') }}" class="btn btn-label-secondary fw-bold px-3">
            <i class="ti tabler-arrow-left me-1"></i> Back to Documents
        </a>
        <button onclick="window.print()" class="btn btn-primary fw-bold px-4 shadow-sm">
            <i class="ti tabler-printer me-1"></i> Finalize & Print
        </button>
    </div>

    <div class="card overflow-hidden border-0" id="print-area">
        <div class="card-body p-4">
            <!-- Header -->
            <div class="row align-items-start mb-3">
                <div class="col-6">
                    <div class="mb-1 text-dark small">RN NO : {{ $inbound->number }}</div>
                    <div class="text-dark small">DATE &nbsp;&nbsp;: {{ date('d-m-Y', strtotime($inbound->received_date)) }}
                    </div>
                </div>
                <div class="col-6 text-end">
                    <img src="{{ asset('assets/image/ntt-data.png') }}" alt="NTT DATA Logo" style="height: 35px;">
                </div>
            </div>

            <!-- Title -->
            <div class="text-center my-3 py-1">
                <h5 class="fw-bold text-dark mb-0 text-decoration-underline" style="letter-spacing: 1px;">RECEIVING NOTE</h5>
                <p class="text-muted small italic mb-0">Services Department</p>
            </div>

            <!-- Sender & Recipient -->
            <div class="row mt-4">
                <div class="col-7">
                    <p class="fw-bold text-dark mb-1 small">FROM :</p>
                    <div class="ps-2 border-start border-2 border-light">
                        <div class="text-dark fw-bold mb-1 small">{{ $inbound->vendor ?? 'N/A' }}</div>
                        @if ($inbound->client)
                            <div class="text-dark fw-bold mb-1 small">{{ $inbound->client->name }}</div>
                        @endif
                        @if ($inbound->client_contact)
                            <div class="text-dark mb-0 small">Attn: {{ $inbound->client_contact }}</div>
                        @endif
                        <p class="text-muted mb-0" style="white-space: pre-line; font-size: 11px;">
                            {{ $inbound->pickup_address ?? $inbound->client->address ?? '-' }}
                        </p>
                    </div>
                </div>
                <div class="col-5">
                    <p class="fw-bold text-dark mb-1 small">RECEIVED BY :</p>
                    <div class="ps-2 border-start border-2 border-light">
                        <h6 class="fw-bold mb-0 text-dark small">NTT Data</h6>
                        <div class="text-dark fw-bold mb-1 small">{{ $inbound->received_by }}</div>
                        <p class="text-muted mb-0" style="white-space: pre-line; font-size: 11px;">WH Transkargo Solusindo
                            Pergudangan Tunas Daan Mogot Blok B2 No.11
                            Batu Ceper Tangerang 12522.</p>
                    </div>
                </div>
            </div>

            <!-- Reference Info -->
            <div class="row mt-3">
                <div class="col-12">
                    <table style="font-size: 11px; width: 100%;">
                        <tr>
                            <td style="width: 120px;" class="fw-bold text-dark">Stock Category</td>
                            <td>: {{ $inbound->category }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-dark">Request Type</td>
                            <td>: {{ $inbound->request_type ?? '-' }}</td>
                        </tr>
                        @if ($inbound->sap_po_number)
                        <tr>
                            <td class="fw-bold text-dark">SAP PO#</td>
                            <td>: {{ $inbound->sap_po_number }}</td>
                        </tr>
                        @endif
                        @if ($inbound->receiving_note)
                        <tr>
                            <td class="fw-bold text-dark">NTT RN#</td>
                            <td>: {{ $inbound->receiving_note }}</td>
                        </tr>
                        @endif
                        @if ($inbound->rma_number)
                        <tr>
                            <td class="fw-bold text-dark">RMA#</td>
                            <td>: {{ $inbound->rma_number }}</td>
                        </tr>
                        @endif
                        @if ($inbound->itsm_number)
                        <tr>
                            <td class="fw-bold text-dark">ITSM#</td>
                            <td>: {{ $inbound->itsm_number }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Product Table -->
            <div class="table-responsive mt-4">
                <table class="table table-bordered border-dark custom-pdf-table">
                    <thead>
                        <tr class="text-center align-middle bg-light text-uppercase" style="font-size: 11px;">
                            <th style="width: 5%;" class="fw-bold py-1">NO</th>
                            <th style="width: 20%;" class="fw-bold py-1">PART NUMBER</th>
                            <th style="width: 25%;" class="fw-bold py-1">SERIAL NUMBER</th>
                            <th style="width: 30%;" class="fw-bold py-1">DESCRIPTION</th>
                            <th style="width: 10%;" class="fw-bold py-1">CONDITION</th>
                            <th style="width: 10%;" class="fw-bold py-1">QTY</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 11px;">
                        @foreach ($inbound->details as $detail)
                            <tr class="align-middle">
                                <td class="text-center fw-bold">{{ $loop->iteration }}</td>
                                <td class="text-center fw-bold">{{ $detail->part_number ?? '-' }}</td>
                                <td class="text-center">{{ $detail->serial_number ?? '-' }}</td>
                                <td class="px-2">{{ $detail->part_name }}{{ $detail->description ? ' - ' . $detail->description : '' }}</td>
                                <td class="text-center">{{ $detail->condition ?? '-' }}</td>
                                <td class="text-center">{{ $detail->qty ?? 1 }}</td>
                            </tr>
                        @endforeach
                        @for ($i = count($inbound->details); $i < 6; $i++)
                            <tr>
                                <td class="py-2">&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            <!-- Reference Line -->
            <div class="mt-3 p-2 bg-light border rounded">
                <p class="mb-0 fw-bold text-dark" style="font-size: 11px;">
                    Re: {{ $inbound->remarks ?? $inbound->category . ' - ' . ($inbound->request_type ?? '') . ' || ' . $inbound->number }}
                </p>
            </div>

            <!-- Signatures -->
            <div class="row mt-4 pt-2">
                <div class="col-6">
                    <div class="border border-dark p-2 rounded" style="min-height: 140px;">
                        <p class="fw-bold mb-0 small">Received by,</p>
                        <div class="mt-auto pt-4">
                            <div class="border-top border-dark pt-1 mt-4">
                                <p class="mb-0 small">Name : <strong>{{ $inbound->received_by }}</strong></p>
                                <p class="mb-0 small">Date : {{ date('d-m-Y', strtotime($inbound->received_date)) }}</p>
                                <p class="mb-0 small">Time : {{ date('H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 text-end">
                    <div class="border border-dark p-2 rounded text-start" style="min-height: 140px;">
                        <p class="fw-bold mb-0 small">Checked by,</p>
                        <div class="mt-auto pt-4">
                            <div class="border-top border-dark pt-1 mt-4">
                                <p class="mb-0 small">Name : ____________________</p>
                                <p class="mb-0 small">Date : {{ date('d-m-Y', strtotime($inbound->received_date)) }}</p>
                                <p class="mb-0 small">Time : ________</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Company Footer Address -->
            <div class="mt-4 pt-3 text-start border-top">
                <p class="fw-bold mb-0 text-dark small">PT NTT DATA Indonesia</p>
                <p class="text-muted mb-0" style="font-size: 10px;">DBS Tower 22nd Floor, Jl. Prof. Dr. Satrio Kav 3-5,
                    Jakarta Selatan 12940 Indonesia</p>
                <p class="text-muted mb-0" style="font-size: 10px;">Tel: +62 21 2922 8300 | Fax: +62 21 2922 8301</p>
            </div>
        </div>
    </div>

    <style>
        #print-area {
            font-family: 'Inter', -apple-system, sans-serif !important;
            line-height: normal !important;
        }

        @media print {
            body {
                background: white !important;
            }

            .no-print,
            .navbar,
            .layout-navbar,
            #layout-navbar,
            .layout-menu,
            #layout-menu,
            aside,
            nav,
            footer {
                display: none !important;
            }

            .layout-page,
            .content-wrapper,
            .container-p-y {
                padding: 0 !important;
                margin: 0 !important;
            }

            #print-area {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }

            .custom-pdf-table th {
                background-color: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
            }

            .table-bordered> :not(caption)>*>* {
                border-width: 1px !important;
                border-color: #000 !important;
            }
        }

        .custom-pdf-table th,
        .custom-pdf-table td {
            padding: 5px !important;
            border-color: #000 !important;
        }

        .italic {
            font-style: italic;
        }
    </style>
@endsection
