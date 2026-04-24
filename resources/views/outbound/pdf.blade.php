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

    <div class="card overflow-hidden border-0" id="print-area">
        <div class="card-body p-4">
            <!-- Header -->
            <div class="row align-items-start mb-3">
                <div class="col-6">
                    <div class="mb-1 text-dark small">DN NO : {{ $outbound->tks_dn_number }}</div>
                    <div class="text-dark small">DATE &nbsp;&nbsp;: {{ date('d-m-Y', strtotime($outbound->outbound_date)) }}
                    </div>
                </div>
                <div class="col-6 text-end">
                    <img src="{{ asset('assets/image/ntt-data.png') }}" alt="NTT DATA Logo" style="height: 35px;">
                </div>
            </div>

            <!-- Title -->
            <div class="text-center my-3 py-1">
                <h5 class="fw-bold text-dark mb-0 text-decoration-underline" style="letter-spacing: 1px;">DELIVERY NOTE</h5>
                <p class="text-muted small italic mb-0">Services Department</p>
            </div>

            <!-- Sender & Recipient -->
            <div class="row mt-4">
                <div class="col-7">
                    <p class="fw-bold text-dark mb-1 small">FROM :</p>
                    <div class="ps-2 border-start border-2 border-light">
                        <div class="text-dark fw-bold mb-1 small">{{ $outbound->ntt_requestor ?? 'NTT Data' }}</div>
                        <p class="text-muted mb-0" style="white-space: pre-line; font-size: 11px;">WH Transkargo Solusindo
                            Pergudangan Tunas Daan Mogot Blok B2 No.11
                            Batu Ceper Tangerang 12522.</p>
                    </div>
                </div>
                <div class="col-5">
                    <p class="fw-bold text-dark mb-1 small">DELIVER / SHIP TO :</p>
                    <div class="ps-2 border-start border-2 border-light">
                        <h6 class="fw-bold mb-0 text-dark small">{{ $outbound->client->name }}</h6>
                        <div class="text-dark fw-bold mb-1 small">{{ $outbound->client_contact }}</div>
                        <p class="text-muted mb-0" style="white-space: pre-line; font-size: 11px;">
                            {{ $outbound->pickup_address ?? ($outbound->client->address ?? '-') }}</p>
                    </div>
                </div>
            </div>

            <!-- Product Table -->
            <div class="table-responsive mt-4">
                <table class="table table-bordered border-dark custom-pdf-table">
                    <thead>
                        <tr class="text-center align-middle bg-light text-uppercase" style="font-size: 11px;">
                            <th style="width: 25%" class="fw-bold py-1">PRODUCT NO.</th>
                            <th style="width: 20%" class="fw-bold py-1">SERIAL NO.</th>
                            <th style="width: 35%" class="fw-bold py-1">DESCRIPTION</th>
                            <th style="width: 10%" class="fw-bold py-1">STATUS</th>
                            <th style="width: 10%" class="fw-bold py-1">QTY</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 11px;">
                        @foreach ($outbound->details as $detail)
                            <tr class="align-middle">
                                <td class="text-center fw-bold">{{ $detail->part_number }}</td>
                                <td class="text-center">{{ $detail->serial_number }}</td>
                                <td class="px-2">{{ $detail->part_name }} - {{ $detail->description ?? '' }}</td>
                                <td class="text-center">{{ $detail->condition }}</td>
                                <td class="text-center">1</td>
                            </tr>
                        @endforeach
                        <!-- Add empty rows to fill the space if needed -->
                        @for ($i = count($outbound->details); $i < 6; $i++)
                            <tr>
                                <td class="py-2">&nbsp;</td>
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
                    Re:
                    {{ $outbound->remarks ?? $outbound->category . ' unit for ' . $outbound->request_type . ' || ' . ($outbound->itsm_number ?? ($outbound->rma_number ?? '-')) }}
                </p>
            </div>

            <!-- Note Text -->
            <div class="mt-2 text-wrap" style="max-width: 90%;">
                <p class="text-muted italic mb-0" style="font-size: 10px;">Barang kembali harus sesuai dengan keadaan semula
                    (komplit dengan box, accessories, buku manual, busa, plastik pembungkus dll) Kerusakan barang di
                    tanggung oleh Peminjam.</p>
            </div>

            <!-- Signatures -->
            <div class="row mt-4 pt-2">
                <div class="col-6">
                    <div class="border border-dark p-2 rounded" style="min-height: 140px;">
                        <p class="fw-bold mb-0 small">Dispatched by,</p>
                        <div class="mt-auto pt-4">
                            <div class="border-top border-dark pt-1 mt-4">
                                <p class="mb-0 small">Name : <strong>{{ $outbound->outbound_by }}</strong></p>
                                <p class="mb-0 small">Date : {{ date('d-m-Y', strtotime($outbound->outbound_date)) }}</p>
                                <p class="mb-0 small">Time : {{ date('H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 text-end">
                    <div class="border border-dark p-2 rounded text-start" style="min-height: 140px;">
                        <p class="fw-bold mb-0 small">Received by,</p>
                        <div class="mt-auto pt-4">
                            <div class="border-top border-dark pt-1 mt-4">
                                <p class="mb-0 small">Name : ____________________</p>
                                <p class="mb-0 small">Date : {{ date('d-m-Y', strtotime($outbound->outbound_date)) }}</p>
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
            .menu,
            footer {
                display: none !important;
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
