@extends('layout.index')
@section('title', 'Edit Serial Number')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center border-bottom py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="ti tabler-barcode me-2 text-primary"></i>Edit Serial Number</h5>
                </div>
                <div class="card-body pt-4">

                    <form action="{{ route('inventory.update.sn') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Current Serial Number / Asset ID</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="search_sn" 
                                   placeholder="Scan Current SN or Asset ID" 
                                   required 
                                   autofocus>
                            <small class="text-muted">Enter the current serial number or unique warehouse asset ID (if SN is empty).</small>
                            @error('search_sn')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">New Serial Number</label>
                            <input type="text" 
                                   class="form-control text-primary fw-bold" 
                                   name="new_sn" 
                                   placeholder="Scan New SN" 
                                   required>
                            @error('new_sn')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="reset" class="btn btn-label-secondary me-2">Cancel</button>
                            <button type="submit" class="btn btn-primary d-flex align-items-center">
                                <i class="ti tabler-device-floppy me-2"></i> Update Serial Number
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
                            <li>You can use this tool to correct spelling mistakes in Serial Numbers.</li>
                            <li>If an item was received with no Serial Number, you can search for it by "Warehouse Asset ID" (e.g. WH-XXX) and assign a new SN.</li>
                            <li>This will update the item's records in the Inventory and Receiving logs.</li>
                        </ul>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
@endsection
