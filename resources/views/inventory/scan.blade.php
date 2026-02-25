<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Detail - {{ $inventory->unique_id }}</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
    </style>
</head>

<body class="antialiased text-slate-800 pb-12">
    <!-- Header -->
    <div class="bg-blue-600 text-white rounded-b-3xl shadow-lg px-6 pt-8 pb-12">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-bold tracking-tight">Product Details</h1>
            <i class="ti tabler-scan bg-white/20 p-2 rounded-full text-xl"></i>
        </div>
        <p class="text-blue-100 text-sm opacity-90">Warehouse Management System</p>
    </div>

    <!-- Main Content -->
    <div class="px-4 -mt-8">
        <!-- Main Card -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-5">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1">Asset ID</h2>
                    <p class="text-xl font-extrabold text-gray-900">{{ $inventory->unique_id }}</p>
                </div>

                @php
                    $statusColor = 'bg-gray-100 text-gray-700';
                    $status = strtolower($inventory->status);
                    if (in_array($status, ['available', 'in stock'])) {
                        $statusColor = 'bg-green-100 text-green-700 font-bold';
                    } elseif (
                        in_array($status, [
                            'out for replacement/ support',
                            'out for loan',
                            'out for return',
                            'shipped / outbound',
                        ])
                    ) {
                        $statusColor = 'bg-yellow-100 text-yellow-800 font-bold';
                    } elseif (in_array($status, ['write-off', 'faulty', 'broken', 'defective'])) {
                        $statusColor = 'bg-red-100 text-red-700 font-bold';
                    }
                @endphp
                <span class="px-3 py-1 rounded-full text-xs uppercase tracking-wide {{ $statusColor }}">
                    {{ $inventory->status }}
                </span>
            </div>

            <div class="p-5">
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-gray-500 font-medium mb-1">Part Name</p>
                        <p class="font-bold text-gray-800">{{ $inventory->part_name }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 font-medium mb-1">Part Number (P/N)</p>
                            <p class="font-medium text-gray-800">{{ $inventory->part_number }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium mb-1">Serial Number (S/N)</p>
                            <p class="font-bold text-blue-600 font-mono">{{ $inventory->serial_number }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium mb-1">Condition</p>
                            <p class="font-medium text-gray-800">{{ $inventory->condition ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium mb-1">Quantity</p>
                            <p class="font-medium text-gray-800">{{ $inventory->qty }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 p-4 border-t border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 font-medium mb-1">Brand</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $inventory->product->brand->name ?? '-' }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 font-medium mb-1">Product Group</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $inventory->product->productGroup->name ?? '-' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Location Card -->
        <h3 class="text-sm font-bold text-gray-800 px-2 mb-2 uppercase tracking-wide">Location Info</h3>
        <div class="bg-white rounded-2xl shadow-md p-5 mb-5 flex items-center">
            <div class="bg-blue-100 text-blue-600 rounded-full p-3 mr-4">
                <i class="ti tabler-map-pin text-2xl"></i>
            </div>
            <div>
                @if ($inventory->storageLevel)
                    <p class="font-bold text-gray-800">{{ $inventory->storageLevel->bin->rak->zone->name }} -
                        {{ $inventory->storageLevel->bin->rak->name }}</p>
                    <p class="text-xs text-gray-500 mt-1">Bin: <span
                            class="font-medium text-gray-700">{{ $inventory->storageLevel->bin->name }}</span> | Level:
                        <span class="font-medium text-gray-700">{{ $inventory->storageLevel->name }}</span></p>
                @else
                    <p class="font-bold text-gray-800 italic">Not Set</p>
                    <p class="text-xs text-gray-500 mt-1">Item has no warehouse location.</p>
                @endif
            </div>
        </div>

        <!-- Client Card -->
        <h3 class="text-sm font-bold text-gray-800 px-2 mb-2 uppercase tracking-wide">Client</h3>
        <div class="bg-white rounded-2xl shadow-md p-5 mb-5 flex items-center">
            <div class="bg-indigo-100 text-indigo-600 rounded-full p-3 mr-4">
                <i class="ti tabler-building text-2xl"></i>
            </div>
            <div>
                <p class="font-bold text-gray-800">{{ $inventory->client->name ?? 'Internal / No Client' }}</p>
            </div>
        </div>

        <!-- Recent History -->
        @if ($history->count() > 0)
            <h3 class="text-sm font-bold text-gray-800 px-2 mb-2 uppercase tracking-wide">Recent History</h3>
            <div class="bg-white rounded-2xl shadow-md overflow-hidden">
                <div class="px-5 py-2">
                    @foreach ($history as $record)
                        <div class="py-4 border-b border-gray-100 last:border-0 relative">
                            <!-- Timeline vertical line -->
                            <div class="absolute left-[11px] top-6 bottom-[-16px] w-[2px] bg-gray-200 last:hidden">
                            </div>

                            <div class="flex items-start">
                                <div class="z-10 bg-white mr-3">
                                    @if (strtolower($record->type) == 'inbound')
                                        <div
                                            class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 shadow-sm border border-white">
                                            <i class="ti tabler-arrow-down-right text-xs"></i>
                                        </div>
                                    @elseif(strtolower($record->type) == 'outbound')
                                        <div
                                            class="w-6 h-6 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600 shadow-sm border border-white">
                                            <i class="ti tabler-arrow-up-right text-xs"></i>
                                        </div>
                                    @elseif(strtolower($record->type) == 'movement')
                                        <div
                                            class="w-6 h-6 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 shadow-sm border border-white">
                                            <i class="ti tabler-arrows-right-left text-xs"></i>
                                        </div>
                                    @else
                                        <div
                                            class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 shadow-sm border border-white">
                                            <i class="ti tabler-circle text-xs"></i>
                                        </div>
                                    @endif
                                </div>

                                <div class="flex-1">
                                    <div class="flex justify-between items-start mb-1">
                                        <p class="text-sm font-bold text-gray-800">{{ $record->type }}</p>
                                        <p class="text-xs text-gray-400">
                                            {{ \Carbon\Carbon::parse($record->created_at)->diffForHumans() }}</p>
                                    </div>
                                    <p class="text-xs text-gray-600 leading-relaxed mb-1">{{ $record->description }}
                                    </p>
                                    <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider mt-1">
                                        {{ \Carbon\Carbon::parse($record->created_at)->format('d M Y, H:i') }} • By
                                        {{ $record->user ?? 'System' }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</body>

</html>
