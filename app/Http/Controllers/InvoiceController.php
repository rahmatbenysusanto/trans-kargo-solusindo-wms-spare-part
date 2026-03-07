<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Inbound;
use App\Models\Outbound;
use App\Models\Client;
use App\Exports\InvoiceExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['inbounds.client', 'outbounds.client']);

        if ($request->search) {
            $s = $request->search;
            $query->where('invoice_number', 'like', "%$s%")
                ->orWhereHas('inbounds', function ($q) use ($s) {
                    $q->where('number', 'like', "%$s%");
                })
                ->orWhereHas('outbounds', function ($q) use ($s) {
                    $q->where('number', 'like', "%$s%");
                });
        }

        $invoices = $query->latest()->paginate(15);
        $title = 'Invoice Management';

        return view('invoice.index', compact('invoices', 'title'));
    }

    public function create(Request $request)
    {
        $title = 'Create Invoice';
        $clients = Client::all();

        $refType = $request->ref_type;
        $refId = $request->ref_id;
        $reference = null;

        if ($refType && $refId) {
            if ($refType == 'inbound') {
                $reference = Inbound::findOrFail($refId);
            } else {
                $reference = Outbound::findOrFail($refId);
            }
        }

        return view('invoice.create', compact('title', 'clients', 'reference', 'refType', 'refId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_number' => 'required|unique:invoices',
            'invoice_date' => 'required|date',
            'ref_type' => 'required|in:inbound,outbound',
            'ref_ids' => 'required|array',
            'amount' => 'required|numeric',
            'file' => 'nullable|mimes:pdf,jpg,png,jpeg|max:5120'
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('invoices', 'public');
        }

        DB::beginTransaction();
        try {
            $invoice = Invoice::create([
                'invoice_number' => $request->invoice_number,
                'invoice_date' => $request->invoice_date,
                'amount' => $request->amount,
                'description' => $request->description,
                'file_path' => $filePath
            ]);

            if ($request->ref_type == 'inbound') {
                $invoice->inbounds()->attach($request->ref_ids);
            } else {
                $invoice->outbounds()->attach($request->ref_ids);
            }

            DB::commit();
            return redirect()->route('invoice.index')->with('success', 'Invoice created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create invoice: ' . $e->getMessage());
        }
    }

    public function exportExcel()
    {
        return Excel::download(new InvoiceExport, 'invoices_report_' . date('Ymd_His') . '.xlsx');
    }

    public function printPdf($id)
    {
        $invoice = Invoice::with(['inbounds.client', 'outbounds.client'])->findOrFail($id);
        $pdf = Pdf::loadView('invoice.pdf', compact('invoice'));
        return $pdf->stream('invoice_' . $invoice->invoice_number . '.pdf');
    }

    public function searchReference(Request $request)
    {
        $type = $request->type;
        $search = $request->search;

        if ($type == 'inbound') {
            $results = Inbound::where('number', 'like', "%$search%")
                ->limit(15)
                ->get()
                ->map(function ($item) {
                    return ['id' => $item->id, 'text' => $item->number . ' (Inbound)'];
                });
        } else {
            $results = Outbound::where('number', 'like', "%$search%")
                ->limit(15)
                ->get()
                ->map(function ($item) {
                    return ['id' => $item->id, 'text' => $item->number . ' (Outbound)'];
                });
        }

        return response()->json($results);
    }

    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        if ($invoice->file_path) {
            Storage::disk('public')->delete($invoice->file_path);
        }
        $invoice->delete();

        return response()->json(['status' => true]);
    }
}
