@extends('layout.index')
@section('title', 'Create Invoice')

@section('content')
    <div class="row">
        <div class="col-12 mb-3">
            <h4 class="fw-bold mb-0">Create/Link Invoice</h4>
        </div>

        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('invoice.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Invoice Number</label>
                            <input type="text" name="invoice_number" class="form-control" placeholder="INV/..." required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Invoice Date</label>
                            <input type="date" name="invoice_date" class="form-control" value="{{ date('Y-m-d') }}"
                                required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-5">
                                <label class="form-label fw-bold">Reference Type</label>
                                <select name="ref_type" id="ref_type" class="form-select" required
                                    onchange="resetReference()">
                                    <option value="inbound" {{ $refType == 'inbound' ? 'selected' : '' }}>Inbound
                                        (Receiving)</option>
                                    <option value="outbound" {{ $refType == 'outbound' ? 'selected' : '' }}>Outbound
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label fw-bold">Reference Number(s) <small class="text-muted">(Can select
                                        multiple)</small></label>
                                <select name="ref_ids[]" id="ref_ids" class="form-select select2" required multiple>
                                    @if ($reference)
                                        <option value="{{ $reference->id }}" selected>{{ $reference->number }}</option>
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Total Amount (IDR)</label>
                            <input type="number" name="amount" class="form-control" placeholder="0" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Attachment (PDF/Image)</label>
                            <input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">Max file size: 5MB</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Description (Optional)</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Additional notes..."></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="ti tabler-device-floppy me-1"></i> Save Invoice
                            </button>
                            <a href="{{ route('invoice.index') }}" class="btn btn-label-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card bg-label-primary shadow-none border-0">
                <div class="card-body">
                    <h5 class="fw-bold"><i class="ti tabler-info-circle me-1"></i> Multiple Selection</h5>
                    <p>Modul ini mendukung **Gabungan Transaksi**. Anda bisa memilih lebih dari satu Inbound atau Outbound
                        untuk satu nomor invoice yang sama.</p>
                    <ul class="mb-0 small">
                        <li>Gunakan kotak "Reference Number" untuk mencari transaksi.</li>
                        <li>Satu invoice bisa meng-cover banyak list pengiriman.</li>
                        <li>Pastikan tipe referensi (Inbound/Outbound) seragam untuk satu invoice.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            initSelect2();
        });

        function initSelect2() {
            $('#ref_ids').select2({
                placeholder: "-- Search Reference # --",
                allowClear: true,
                ajax: {
                    url: '{{ route('invoice.search-reference') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            search: params.term,
                            type: $('#ref_type').val()
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                }
            });
        }

        function resetReference() {
            $('#ref_ids').val(null).trigger('change');
        }
    </script>
@endsection
