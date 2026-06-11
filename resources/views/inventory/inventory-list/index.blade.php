@extends('layout.index')
@section('title', 'Inventory List')

@section('css')
    <style>
        /* ---- base table styles ---- */
        .table thead th {
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #5d596c;
            white-space: nowrap;
        }
        .table-compact td {
            font-size: 0.8rem;
            padding: 0.5rem 0.6rem !important;
        }
        .badge-status {
            font-size: 0.65rem;
            padding: 0.4em 0.8em;
            border-radius: 4px;
        }
        .text-mono {
            font-family: 'Monaco', 'Consolas', monospace;
            font-size: 0.75rem;
        }

        /* ---- Excel-style filter header ---- */
        .excel-header {
            position: relative;
            cursor: pointer;
            user-select: none;
            padding-right: 18px !important;
        }
        .excel-header:hover {
            background: #e9ecef !important;
        }
        .excel-header .header-label {
            display: inline-block;
        }
        .excel-header .header-arrow {
            position: absolute;
            right: 4px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 8px;
            color: #999;
            transition: color 0.15s;
        }
        .excel-header:hover .header-arrow {
            color: #333;
        }
        .excel-header.filter-active .header-arrow {
            color: #4a90d9;
        }
        .excel-header.filter-active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 2px;
            right: 2px;
            height: 2px;
            background: #4a90d9;
            border-radius: 1px;
        }
        .excel-header.sort-active .header-arrow {
            color: #4a90d9;
        }

        /* ---- Excel-style dropdown ---- */
        .excel-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            z-index: 1050;
            min-width: 230px;
            max-width: 320px;
            background: #fff;
            border: 1px solid #c6c6c6;
            border-radius: 2px;
            box-shadow: 0 6px 16px rgba(0,0,0,0.18);
            padding: 0;
            display: none;
            font-size: 12px;
            color: #333;
        }
        .excel-dropdown.show {
            display: block;
        }
        .excel-dropdown .dropdown-section {
            padding: 6px 8px;
            border-bottom: 1px solid #e8e8e8;
        }
        .excel-dropdown .dropdown-section:last-child {
            border-bottom: none;
        }

        /* sort buttons */
        .excel-dropdown .sort-btn {
            display: block;
            width: 100%;
            text-align: left;
            background: none;
            border: none;
            padding: 5px 8px;
            font-size: 12px;
            cursor: pointer;
            border-radius: 2px;
            color: #333;
        }
        .excel-dropdown .sort-btn:hover {
            background: #e8f0fe;
        }
        .excel-dropdown .sort-btn.active {
            background: #e8f0fe;
            color: #1967d2;
            font-weight: 600;
        }
        .excel-dropdown .sort-btn .sort-icon {
            display: inline-block;
            width: 16px;
            text-align: center;
            margin-right: 4px;
        }

        /* clear filter button */
        .excel-dropdown .clear-filter-btn {
            display: block;
            width: 100%;
            text-align: left;
            background: none;
            border: none;
            padding: 5px 8px;
            font-size: 12px;
            cursor: pointer;
            border-radius: 2px;
            color: #d93025;
        }
        .excel-dropdown .clear-filter-btn:hover {
            background: #fce8e6;
        }
        .excel-dropdown .clear-filter-btn .clear-icon {
            margin-right: 6px;
        }

        /* search inside dropdown */
        .excel-dropdown .filter-search {
            width: 100%;
            padding: 4px 6px;
            font-size: 12px;
            border: 1px solid #ccc;
            border-radius: 2px;
            box-sizing: border-box;
            outline: none;
        }
        .excel-dropdown .filter-search:focus {
            border-color: #4a90d9;
        }

        /* checkbox list */
        .excel-dropdown .checkbox-list {
            max-height: 220px;
            overflow-y: auto;
            padding: 2px 0;
        }
        .excel-dropdown .checkbox-list label {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 3px 8px;
            font-size: 12px;
            font-weight: 400;
            cursor: pointer;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-transform: none;
            letter-spacing: normal;
            color: #333;
            margin: 0;
        }
        .excel-dropdown .checkbox-list label:hover {
            background: #f5f5f5;
        }
        .excel-dropdown .checkbox-list input[type="checkbox"] {
            margin: 0;
            flex-shrink: 0;
            accent-color: #4a90d9;
        }
        .excel-dropdown .checkbox-list .select-all {
            border-bottom: 1px solid #e8e8e8;
            padding-bottom: 4px;
            margin-bottom: 2px;
            font-weight: 600;
        }
        .excel-dropdown .checkbox-list .no-results {
            padding: 12px 8px;
            text-align: center;
            color: #999;
        }
        .excel-dropdown .checkbox-list .value-count {
            margin-left: auto;
            color: #999;
            font-size: 11px;
            flex-shrink: 0;
        }

        /* action buttons */
        .excel-dropdown .dropdown-actions {
            display: flex;
            gap: 6px;
            padding: 6px 8px;
            border-top: 1px solid #e8e8e8;
        }
        .excel-dropdown .dropdown-actions button {
            flex: 1;
            padding: 5px 8px;
            font-size: 12px;
            cursor: pointer;
            border: 1px solid #c6c6c6;
            background: #fff;
            border-radius: 2px;
            text-align: center;
        }
        .excel-dropdown .dropdown-actions button:hover {
            background: #f0f0f0;
        }
        .excel-dropdown .dropdown-actions .btn-ok {
            background: #4a90d9;
            color: #fff;
            border-color: #4a90d9;
        }
        .excel-dropdown .dropdown-actions .btn-ok:hover {
            background: #357abd;
        }

        /* loading spinner inside dropdown */
        .excel-dropdown .dropdown-loading {
            padding: 20px;
            text-align: center;
            color: #999;
        }
        .excel-dropdown .dropdown-loading::after {
            content: '';
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #ccc;
            border-top-color: #4a90d9;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            margin-left: 6px;
            vertical-align: middle;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* allow dropdown to overflow the responsive wrapper */
        .table-responsive {
            overflow: visible !important;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center border-bottom py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="ti tabler-box me-2 text-primary"></i>Inventory Data</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('inventory.export.pdf', request()->all()) }}" target="_blank"
                            class="btn btn-sm btn-label-secondary">
                            <i class="ti tabler-file-type-pdf me-1"></i> PDF Export
                        </a>
                        <a href="{{ route('inventory.export.excel', request()->all()) }}"
                            class="btn btn-sm btn-label-success">
                            <i class="ti tabler-file-spreadsheet me-1"></i> Excel Export
                        </a>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <form id="inventoryForm" action="{{ url()->current() }}" method="GET">
                        <div class="row g-2 mb-3 align-items-end">
                            @if (Auth::user()->isAdminWMS() || Auth::user()->clients->count() > 1)
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">Client</label>
                                    <select name="client_id" class="form-select form-select-sm"
                                        onchange="this.form.submit()">
                                        <option value="">All Clients</option>
                                        @foreach ($clients as $client)
                                            <option value="{{ $client->id }}"
                                                {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                                {{ $client->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">
                                    <i class="ti tabler-search me-1"></i> Quick Search SN / Asset ID
                                </label>
                                <input type="text" name="quick_search" class="form-control form-control-sm"
                                    placeholder="Paste SN atau Asset ID, pisahkan dengan koma atau enter"
                                    value="{{ request('quick_search') }}"
                                    title="Paste multiple serial numbers or asset IDs&#10;Separate by comma, semicolon, or newline">
                            </div>
                            <div class="col-md d-flex gap-1 align-items-end pb-1">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="ti tabler-search"></i>
                                </button>
                                @if (request('quick_search'))
                                    <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->except(['quick_search', 'page']))) }}"
                                        class="btn btn-sm btn-label-secondary">
                                        <i class="ti tabler-x"></i>
                                    </a>
                                @endif
                                <div class="ms-auto">
                                    <a href="{{ url()->current() }}" class="btn btn-sm btn-label-secondary">
                                        <i class="ti tabler-refresh me-1"></i> Reset Filters
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Hidden inputs for sort and filters --}}
                        <input type="hidden" name="sort_field" id="sortFieldInput" value="{{ $sortField ?? '' }}">
                        <input type="hidden" name="sort_direction" id="sortDirectionInput" value="{{ $sortDirection ?? 'asc' }}">
                        <div id="filterInputsContainer"></div>

                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-compact table-sm text-nowrap align-middle">
                                <thead class="table-light border-top">
                                    <tr>
                                        <th width="30">#</th>
                                        <th class="excel-header" data-column="unique_id">
                                            <span class="header-label">Warehouse Asset ID</span>
                                            <span class="header-arrow">▾</span>
                                            <div class="excel-dropdown"></div>
                                        </th>
                                        <th class="excel-header" data-column="serial_number">
                                            <span class="header-label">Serial Number</span>
                                            <span class="header-arrow">▾</span>
                                            <div class="excel-dropdown"></div>
                                        </th>
                                        <th class="excel-header" data-column="parent_serial_number">
                                            <span class="header-label">Parent Serial Number</span>
                                            <span class="header-arrow">▾</span>
                                            <div class="excel-dropdown"></div>
                                        </th>
                                        <th class="excel-header" data-column="part_number">
                                            <span class="header-label">Part Number</span>
                                            <span class="header-arrow">▾</span>
                                            <div class="excel-dropdown"></div>
                                        </th>
                                        <th class="excel-header" data-column="part_description">
                                            <span class="header-label">Part Description</span>
                                            <span class="header-arrow">▾</span>
                                            <div class="excel-dropdown"></div>
                                        </th>
                                        <th class="excel-header" data-column="brand">
                                            <span class="header-label">Brand</span>
                                            <span class="header-arrow">▾</span>
                                            <div class="excel-dropdown"></div>
                                        </th>
                                        <th class="excel-header" data-column="group">
                                            <span class="header-label">Group</span>
                                            <span class="header-arrow">▾</span>
                                            <div class="excel-dropdown"></div>
                                        </th>
                                        <th class="excel-header" data-column="location">
                                            <span class="header-label">Location</span>
                                            <span class="header-arrow">▾</span>
                                            <div class="excel-dropdown"></div>
                                        </th>
                                        <th class="excel-header" data-column="condition">
                                            <span class="header-label">Stock Condition</span>
                                            <span class="header-arrow">▾</span>
                                            <div class="excel-dropdown"></div>
                                        </th>
                                        <th class="excel-header" data-column="staging_condition">
                                            <span class="header-label">Staging Condition</span>
                                            <span class="header-arrow">▾</span>
                                            <div class="excel-dropdown"></div>
                                        </th>
                                        <th class="excel-header" data-column="status">
                                            <span class="header-label">Status</span>
                                            <span class="header-arrow">▾</span>
                                            <div class="excel-dropdown"></div>
                                        </th>
                                        <th class="excel-header" data-column="last_staging_date">
                                            <span class="header-label">Check Date</span>
                                            <span class="header-arrow">▾</span>
                                            <div class="excel-dropdown"></div>
                                        </th>
                                        <th class="excel-header" data-column="last_movement_date">
                                            <span class="header-label">Activity</span>
                                            <span class="header-arrow">▾</span>
                                            <div class="excel-dropdown"></div>
                                        </th>
                                        <th class="text-center" width="70">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($inventory as $item)
                                        <tr>
                                            <td>{{ $loop->iteration + ($inventory->currentPage() - 1) * $inventory->perPage() }}
                                            </td>
                                            <td><span class="text-mono fw-bold text-primary">{{ $item->unique_id }}</span></td>
                                            <td><span class="text-mono fw-bold text-dark">{{ $item->serial_number }}</span>
                                            </td>
                                            <td><span class="text-mono">{{ $item->parent_serial_number ?? '-' }}</span></td>
                                            <td style="max-width: 200px; white-space: normal;"><span
                                                    class="text-mono">{{ $item->part_number ?? '-' }}</span></td>
                                            <td>{{ $item->part_description }}</td>
                                            <td><span class="badge bg-label-dark"
                                                    style="font-size: 0.65rem;">{{ $item->brand->name ?? '-' }}</span></td>
                                            <td><span class="badge bg-label-secondary"
                                                    style="font-size: 0.65rem;">{{ $item->productGroup->name ?? '-' }}</span>
                                            </td>
                                            <td>
                                                @if ($item->storageLevel)
                                                    <div class="d-flex align-items-center gap-1">
                                                        <span class="badge bg-label-success p-1" title="Put Away Complete">
                                                            <i class="ti tabler-check fs-7"></i> PA
                                                        </span>
                                                        <span class="text-muted" style="font-size: 0.72rem;">
                                                            {{ $item->storageLevel->bin->rak->zone->name }}-{{ $item->storageLevel->bin->rak->name }}-{{ $item->storageLevel->bin->name }}-{{ $item->storageLevel->name }}
                                                        </span>
                                                    </div>
                                                @else
                                                    <span class="badge bg-label-warning p-1" title="Still in Staging">
                                                        <i class="ti tabler-clock fs-7"></i> Pending PA
                                                    </span>
                                                @endif
                                            </td>
                                            <td><span
                                                    class="badge {{ $item->condition == 'New' ? 'bg-label-info' : 'bg-label-secondary' }} badge-status">{{ $item->condition ?? '-' }}</span>
                                            </td>
                                            <td>
                                                @if($item->staging_condition)
                                                <span class="badge bg-label-dark badge-status">{{ $item->staging_condition }}</span>
                                                @else
                                                <span class="text-muted small">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $bgClass = 'bg-label-secondary';
                                                    switch (strtolower($item->status)) {
                                                        case 'available':
                                                            $bgClass = 'bg-label-success';
                                                            break;
                                                        case 'staging':
                                                            $bgClass = 'bg-label-info';
                                                            break;
                                                        case 'out for replacement/ support':
                                                            $bgClass = 'bg-label-warning';
                                                            break;
                                                        case 'out for loan':
                                                            $bgClass = 'bg-label-primary';
                                                            break;
                                                        case 'out for return':
                                                            $bgClass = 'bg-label-secondary';
                                                            break;
                                                        case 'write-off':
                                                            $bgClass = 'bg-label-danger';
                                                            break;
                                                    }
                                                @endphp
                                                <span
                                                    class="badge {{ $bgClass }} badge-status">{{ strtoupper($item->status) }}</span>
                                            </td>
                                            <td><small
                                                    class="text-muted">{{ $item->last_staging_date ? \Carbon\Carbon::parse($item->last_staging_date)->format('d/m/Y') : '-' }}</small>
                                            </td>
                                            <td><small
                                                    class="text-muted">{{ $item->last_movement_date ? \Carbon\Carbon::parse($item->last_movement_date)->format('d/m/Y') : '-' }}</small>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1 justify-content-center">
                                                    <a href="{{ route('inventory.show', $item->id) }}"
                                                        class="btn btn-icon btn-sm btn-label-primary">
                                                        <i class="ti tabler-info-circle fs-6"></i>
                                                    </a>
                                                    <button
                                                        onclick="printBarcode('{{ $item->unique_id }}', '{{ $item->part_number }}', '{{ $item->serial_number }}')"
                                                        class="btn btn-icon btn-sm btn-label-info">
                                                        <i class="ti tabler-printer fs-6"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="15" class="text-center py-5">No records found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $inventory->links() }}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        // ===== Excel-Style Filter System =====
        (function() {
            'use strict';

            const FILTERS_ENDPOINT = '{{ route('inventory.filter-values') }}';
            const COLUMNS = ['unique_id', 'serial_number', 'parent_serial_number', 'part_number', 'part_description',
                            'brand', 'group', 'location',
                            'condition', 'staging_condition', 'status',
                            'last_staging_date', 'last_movement_date'];

            // ---- state ----
            let activeColumn = null;
            let valueCache = {};

            // ---- init ----
            document.addEventListener('DOMContentLoaded', function() {
                restoreFilterState();
                restoreSortState();
                attachHeaderClickHandlers();
                attachGlobalClickHandler();
            });

            // ---- restore filter state from URL on page load ----
            function restoreFilterState() {
                const params = new URLSearchParams(window.location.search);
                const container = document.getElementById('filterInputsContainer');

                params.forEach((value, key) => {
                    if (key.startsWith('filter[')) {
                        const inp = document.createElement('input');
                        inp.type = 'hidden';
                        inp.name = key;
                        inp.value = value;
                        container.appendChild(inp);
                    }
                });

                // Mark headers that have active filters
                COLUMNS.forEach(col => {
                    const filterKey = 'filter[' + col + ']';
                    if (params.has(filterKey) || params.has(filterKey + '[]')) {
                        const th = document.querySelector('.excel-header[data-column="' + col + '"]');
                        if (th) th.classList.add('filter-active');
                    }
                });
            }

            // ---- restore sort state from URL ----
            function restoreSortState() {
                const params = new URLSearchParams(window.location.search);
                const field = params.get('sort_field');
                const dir = params.get('sort_direction') || 'asc';
                if (field) {
                    const th = document.querySelector('.excel-header[data-column="' + field + '"]');
                    if (th) {
                        th.classList.add('sort-active');
                        th.querySelector('.header-arrow').textContent = dir === 'asc' ? '↑' : '↓';
                    }
                }
            }

            // ---- attach click handlers to headers ----
            function attachHeaderClickHandlers() {
                document.querySelectorAll('.excel-header').forEach(function(th) {
                    th.addEventListener('click', function(e) {
                        // Don't open if clicking inside an already-open dropdown
                        if (e.target.closest('.excel-dropdown')) return;

                        const col = th.dataset.column;
                        if (!col) return;
                        e.stopPropagation();
                        toggleDropdown(col);
                    });
                });
            }

            // ---- click outside to close ----
            function attachGlobalClickHandler() {
                document.addEventListener('click', function() {
                    closeAllDropdowns();
                });
            }

            // ---- toggle dropdown ----
            function toggleDropdown(column) {
                const dropdown = getDropdownEl(column);
                if (!dropdown) return;

                if (activeColumn === column) {
                    closeAllDropdowns();
                    return;
                }

                closeAllDropdowns();
                activeColumn = column;

                // Position the dropdown
                const th = getHeaderEl(column);
                const rect = th.getBoundingClientRect();
                const tableWrap = th.closest('.table-responsive');
                if (tableWrap) {
                    // Ensure dropdown stays within the table wrapper horizontally
                    dropdown.style.left = '0';
                    dropdown.style.right = 'auto';
                }

                dropdown.classList.add('show');

                // Load values if not cached
                if (valueCache[column]) {
                    renderValues(column, valueCache[column]);
                } else {
                    dropdown.innerHTML = '<div class="dropdown-loading">Loading</div>';
                    fetchValues(column, '')
                        .then(function(values) {
                            valueCache[column] = values;
                            renderValues(column, values);
                        })
                        .catch(function() {
                            dropdown.innerHTML = '<div class="dropdown-loading" style="color:#e74c3c;">Failed to load</div>';
                        });
                }
            }

            // ---- fetch values from server ----
            function fetchValues(column, search) {
                const clientId = document.querySelector('select[name="client_id"]')?.value || '';
                const url = FILTERS_ENDPOINT + '?column=' + encodeURIComponent(column) +
                    '&search=' + encodeURIComponent(search) +
                    '&client_id=' + encodeURIComponent(clientId);

                return fetch(url)
                    .then(function(r) { return r.json(); })
                    .then(function(data) { return data.values || []; });
            }

            // ---- render checkbox list in dropdown ----
            function renderValues(column, values) {
                const dropdown = getDropdownEl(column);
                if (!dropdown) return;

                // Get currently active filter values for this column
                const activeValues = getActiveFilterValues(column);
                const hasActiveFilter = activeValues.length > 0;

                let html = '';

                // Clear Filter section (only when filter is active on this column)
                if (hasActiveFilter) {
                    html += '<div class="dropdown-section" style="border-bottom:2px solid #d93025;">';
                    html += '<button class="clear-filter-btn" data-column="' + column + '">' +
                            '<span class="clear-icon">✕</span> Clear Filter from <strong>' +
                            getColumnLabel(column) + '</strong></button>';
                    html += '</div>';
                }

                // Sort section
                html += '<div class="dropdown-section">';
                html += '<button class="sort-btn" data-sort="asc"><span class="sort-icon">↑</span> Sort A to Z</button>';
                html += '<button class="sort-btn" data-sort="desc"><span class="sort-icon">↓</span> Sort Z to A</button>';
                html += '</div>';

                // Search section
                html += '<div class="dropdown-section">';
                html += '<input type="text" class="filter-search" placeholder="Search..." autocomplete="off">';
                html += '</div>';

                // Checkbox list
                html += '<div class="checkbox-list">';
                if (values.length === 0) {
                    html += '<div class="no-results">No values found</div>';
                } else {
                    const allChecked = !hasActiveFilter || activeValues.length === values.length;
                    html += '<label class="select-all"><input type="checkbox" ' +
                        (allChecked ? 'checked' : '') + '> <span>(Select All)</span></label>';
                    values.forEach(function(v) {
                        const val = v.value || '';
                        const label = v.label || val;
                        const checked = !hasActiveFilter || activeValues.includes(val);
                        const count = v.count ? ' <span class="value-count">(' + v.count + ')</span>' : '';
                        html += '<label><input type="checkbox" value="' + escAttr(val) + '" ' +
                            (checked ? 'checked' : '') + '> ' +
                            '<span>' + escHtml(label) + '</span>' + count + '</label>';
                    });
                }
                html += '</div>';

                // Actions
                html += '<div class="dropdown-actions">';
                html += '<button class="btn-ok">OK</button>';
                html += '<button class="btn-cancel">Cancel</button>';
                html += '</div>';

                dropdown.innerHTML = html;

                // ---- bind events ----

                // Clear filter button
                const clearBtn = dropdown.querySelector('.clear-filter-btn');
                if (clearBtn) {
                    clearBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        clearFilter(column);
                    });
                }

                // Sort buttons — mark active
                const params = new URLSearchParams(window.location.search);
                const currentSortField = params.get('sort_field');
                const currentSortDir = params.get('sort_direction') || 'asc';
                if (currentSortField === column) {
                    dropdown.querySelectorAll('.sort-btn').forEach(function(btn) {
                        if (btn.dataset.sort === currentSortDir) {
                            btn.classList.add('active');
                        }
                    });
                }

                dropdown.querySelectorAll('.sort-btn').forEach(function(btn) {
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const dir = btn.dataset.sort;
                        applySort(column, dir);
                    });
                });

                // Search
                const searchInput = dropdown.querySelector('.filter-search');
                if (searchInput) {
                    searchInput.addEventListener('input', function(e) {
                        e.stopPropagation();
                        const q = searchInput.value;
                        clearTimeout(searchInput._timer);
                        searchInput._timer = setTimeout(function() {
                            fetchValues(column, q).then(function(filtered) {
                                renderValues(column, filtered);
                                const newSearch = dropdown.querySelector('.filter-search');
                                if (newSearch) {
                                    newSearch.value = q;
                                    newSearch.focus();
                                }
                            });
                        }, 250);
                    });
                    searchInput.addEventListener('click', function(e) { e.stopPropagation(); });
                    setTimeout(function() { searchInput.focus(); }, 50);
                }

                // Select All — click on entire label toggles
                const selectAllLabel = dropdown.querySelector('.select-all');
                const selectAllCb = selectAllLabel ? selectAllLabel.querySelector('input') : null;
                if (selectAllCb) {
                    selectAllLabel.addEventListener('click', function(e) {
                        // Only toggle if the click wasn't directly on the checkbox (checkbox handles itself)
                        if (e.target.tagName !== 'INPUT') {
                            selectAllCb.checked = !selectAllCb.checked;
                        }
                        const isChecked = selectAllCb.checked;
                        dropdown.querySelectorAll('.checkbox-list label:not(.select-all) input[type="checkbox"]')
                            .forEach(function(cb) { cb.checked = isChecked; });
                    });
                    selectAllCb.addEventListener('change', function(e) {
                        e.stopPropagation();
                        const isChecked = selectAllCb.checked;
                        dropdown.querySelectorAll('.checkbox-list label:not(.select-all) input[type="checkbox"]')
                            .forEach(function(cb) { cb.checked = isChecked; });
                    });
                }

                // Individual checkboxes → update Select All state
                dropdown.querySelectorAll('.checkbox-list label:not(.select-all) input[type="checkbox"]')
                    .forEach(function(cb) {
                        cb.addEventListener('change', function(e) {
                            e.stopPropagation();
                            updateSelectAllState(dropdown);
                        });
                    });

                // OK button
                const okBtn = dropdown.querySelector('.btn-ok');
                if (okBtn) {
                    okBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        applyFilter(column);
                    });
                }

                // Cancel button
                const cancelBtn = dropdown.querySelector('.btn-cancel');
                if (cancelBtn) {
                    cancelBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        closeAllDropdowns();
                    });
                }

                // Prevent clicks on dropdown from closing it
                dropdown.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }

            // ---- get currently active filter values for a column ----
            function getActiveFilterValues(column) {
                const container = document.getElementById('filterInputsContainer');
                const inputs = container.querySelectorAll('input[name="filter[' + column + '][]"]');
                const values = [];
                inputs.forEach(function(inp) {
                    if (inp.value) values.push(inp.value);
                });
                return values;
            }

            // ---- update Select All checkbox state ----
            function updateSelectAllState(dropdown) {
                const allCbs = dropdown.querySelectorAll('.checkbox-list label:not(.select-all) input[type="checkbox"]');
                const selectAllCb = dropdown.querySelector('.select-all input');
                if (!selectAllCb || allCbs.length === 0) return;

                const allChecked = Array.from(allCbs).every(function(cb) { return cb.checked; });
                selectAllCb.checked = allChecked;
            }

            // ---- apply filter (OK button) ----
            function applyFilter(column) {
                const dropdown = getDropdownEl(column);
                if (!dropdown) return;

                const checked = [];
                dropdown.querySelectorAll('.checkbox-list label:not(.select-all) input[type="checkbox"]:checked')
                    .forEach(function(cb) {
                        if (cb.value) checked.push(cb.value);
                    });

                // Remove old hidden inputs for this column
                const container = document.getElementById('filterInputsContainer');
                container.querySelectorAll('input[name="filter[' + column + '][]"]').forEach(function(inp) {
                    inp.remove();
                });

                // Add new hidden inputs if any values selected
                if (checked.length > 0) {
                    checked.forEach(function(val) {
                        const inp = document.createElement('input');
                        inp.type = 'hidden';
                        inp.name = 'filter[' + column + '][]';
                        inp.value = val;
                        container.appendChild(inp);
                    });
                }

                // Update header indicator
                const th = getHeaderEl(column);
                if (th) {
                    if (checked.length > 0) {
                        th.classList.add('filter-active');
                    } else {
                        th.classList.remove('filter-active');
                    }
                }

                closeAllDropdowns();
                document.getElementById('inventoryForm').submit();
            }

            // ---- apply sort ----
            function applySort(column, direction) {
                document.getElementById('sortFieldInput').value = column;
                document.getElementById('sortDirectionInput').value = direction;

                closeAllDropdowns();
                document.getElementById('inventoryForm').submit();
            }

            // ---- close all dropdowns ----
            function closeAllDropdowns() {
                document.querySelectorAll('.excel-dropdown.show').forEach(function(el) {
                    el.classList.remove('show');
                    const th = el.closest('.excel-header');
                    if (th && th.dataset.column) {
                        delete valueCache[th.dataset.column];
                    }
                });
                activeColumn = null;
            }

            // ---- clear filter for a specific column ----
            function clearFilter(column) {
                const container = document.getElementById('filterInputsContainer');
                container.querySelectorAll('input[name="filter[' + column + '][]"]').forEach(function(inp) {
                    inp.remove();
                });

                const th = getHeaderEl(column);
                if (th) th.classList.remove('filter-active');

                closeAllDropdowns();
                document.getElementById('inventoryForm').submit();
            }

            // ---- get human-readable column label ----
            function getColumnLabel(column) {
                var labels = {
                    'unique_id': 'Warehouse Asset ID',
                    'serial_number': 'Serial Number',
                    'parent_serial_number': 'Parent Serial Number',
                    'part_description': 'Part Description',
                    'brand': 'Brand',
                    'group': 'Group',
                    'location': 'Location',
                    'condition': 'Stock Condition',
                    'staging_condition': 'Staging Condition',
                    'status': 'Status',
                    'last_staging_date': 'Check Date',
                    'last_movement_date': 'Activity'
                };
                return labels[column] || column;
            }

            // ---- helpers ----
            function getHeaderEl(column) {
                return document.querySelector('.excel-header[data-column="' + column + '"]');
            }

            function getDropdownEl(column) {
                const th = getHeaderEl(column);
                return th ? th.querySelector('.excel-dropdown') : null;
            }

            function escHtml(str) {
                if (str === null || str === undefined) return '';
                return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            }

            function escAttr(str) {
                if (str === null || str === undefined) return '';
                return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            }
        })();

        // ===== Print Barcode (unchanged) =====
        function printBarcode(uniqueId, partNumber, serialNumber) {
            const printWindow = window.open('', '_blank', 'width=600,height=400');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Print QR Code - ${uniqueId}</title>
                        <style>
                            @page {
                                margin: 0;
                                size: 50mm 40mm;
                            }
                            body {
                                margin: 0;
                                padding: 5px;
                                display: flex;
                                flex-direction: column;
                                align-items: center;
                                justify-content: center;
                                font-family: sans-serif;
                                height: 40mm;
                                width: 50mm;
                                background-color: white;
                                overflow: hidden;
                            }
                            .unique-id {
                                font-size: 13px;
                                font-weight: bold;
                                margin-bottom: 2px;
                                letter-spacing: 0.5px;
                            }
                            #qrcode {
                                margin-bottom: 2px;
                            }
                            .details {
                                font-size: 9px;
                                text-align: center;
                                line-height: 1.2;
                            }
                        </style>
                    </head>
                    <body>
                        <div class="unique-id">${uniqueId}</div>
                        <div id="qrcode"></div>
                        <div class="details">
                            <div>P/N: ${partNumber}</div>
                            <div>S/N: ${serialNumber}</div>
                        </div>
                        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"><\/script>
                        <script>
                            window.onload = function() {
                                new QRCode(document.getElementById("qrcode"), {
                                    text: "{{ url('/scan') }}/" + "${uniqueId}",
                                    width: 80,
                                    height: 80
                                });
                                setTimeout(() => { window.print(); window.close(); }, 500);
                            };
                        <\/script>
                    </body>
                </html>
            `);
            printWindow.document.close();
        }
    </script>
@endsection
