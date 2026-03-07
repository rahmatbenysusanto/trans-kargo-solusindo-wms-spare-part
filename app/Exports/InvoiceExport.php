<?php

namespace App\Exports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InvoiceExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Invoice::with(['inbounds', 'outbounds'])->latest()->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Invoice Number',
            'Invoice Date',
            'Connected References',
            'Amount (IDR)',
            'Description',
            'Created At'
        ];
    }

    public function map($invoice): array
    {
        $references = [];
        foreach ($invoice->inbounds as $in) $references[] = "IN: " . $in->number;
        foreach ($invoice->outbounds as $out) $references[] = "OUT: " . $out->number;

        return [
            $invoice->id,
            $invoice->invoice_number,
            $invoice->invoice_date,
            implode(', ', $references),
            isset($invoice->amount) ? (float) $invoice->amount : 0,
            $invoice->description,
            $invoice->created_at->format('d/m/Y H:i')
        ];
    }
}
