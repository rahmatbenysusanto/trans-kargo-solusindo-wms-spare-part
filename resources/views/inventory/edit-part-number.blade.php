@extends('layout.index')
@section('title', 'Edit Part Number')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center border-bottom py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="ti tabler-package me-2 text-primary"></i>Edit Part Number</h5>
                </div>
                <div class="card-body pt-4">

                    <form action="{{ route('inventory.update.part-number') }}" method="POST">
                        @csrf

                        {{-- Current / Old Data Reference --}}
                        @if ($sn)
                        <div class="alert alert-secondary d-flex align-items-center mb-4" role="alert">
                            <i class="ti tabler-info-circle fs-3 me-3 text-primary"></i>
                            <div>
                                <span class="fw-bold">Produk yang akan diedit:</span>
                                <div class="mt-2">
                                    <table class="table table-sm table-borderless mb-0 small">
                                        <tr>
                                            <td class="text-muted pe-3" width="120">Serial Number</td>
                                            <td class="fw-bold text-mono">{{ $sn }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted pe-3">Part Number Lama</td>
                                            <td class="fw-bold text-mono text-warning">{{ $currentPartNumber ?: '(kosong)' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="search_sn" value="{{ $sn }}">
                        @else
                        <div class="mb-3">
                            <label class="form-label fw-bold">Serial Number / Asset ID</label>
                            <input type="text"
                                   class="form-control"
                                   name="search_sn"
                                   placeholder="Scan SN or Asset ID (WH-XXX)"
                                   required
                                   autofocus>
                            <small class="text-muted">Enter the serial number or unique warehouse asset ID to find the item.</small>
                            @error('search_sn')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        @endif

                        <div class="mb-4">
                            <label class="form-label fw-bold">New Part Number</label>
                            <input type="text"
                                   class="form-control text-primary fw-bold"
                                   name="new_part_number"
                                   placeholder="Enter new Part Number"
                                   value="{{ old('new_part_number', $currentPartNumber) }}"
                                   required>
                            <small class="text-muted">Masukkan Part Number yang baru untuk menggantikan yang lama.</small>
                            @error('new_part_number')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('inventory.index') }}" class="btn btn-label-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary d-flex align-items-center">
                                <i class="ti tabler-device-floppy me-2"></i> Update Part Number
                            </button>
                        </div>
                    </form>

                </div>
            </div>

            <div class="alert alert-info mt-4 pb-0">
                <div class="d-flex">
                    <i class="ti tabler-info-circle fs-3 me-3"></i>
                    <div>
                        <h6 class="alert-heading fw-bold mb-1">How this works</h6>
                        <ul class="small ps-3 mb-3">
                            <li>Use this tool to correct or update Part Numbers for items already in inventory.</li>
                            <li>Search by <strong>Serial Number</strong> or <strong>Warehouse Asset ID</strong> (e.g. WH-XXX).</li>
                            <li>This will update the Part Number in both <strong>Inventory</strong> and <strong>Receiving</strong> records.</li>
                            <li>All changes are recorded in the inventory history log.</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
