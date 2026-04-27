<?php

namespace App\Exports;

use App\Models\StockReturn;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReturnsExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function __construct(
        private ?int $supplierId = null,
        private ?string $from = null,
        private ?string $to = null
    ) {}

    public function collection()
    {
        $query = StockReturn::with(['supplier', 'purchase', 'user', 'items']);

        if ($this->supplierId) $query->where('supplier_id', $this->supplierId);
        if ($this->from)       $query->whereDate('date', '>=', $this->from);
        if ($this->to)         $query->whereDate('date', '<=', $this->to);

        return $query->latest('date')->get()->map(fn($r) => [
            $r->return_number,
            $r->date->format('d/m/Y'),
            $r->supplier->name,
            $r->purchase->invoice_number,
            $r->user->name,
            $r->items->sum('qty'),
            $r->notes,
        ]);
    }

    public function headings(): array
    {
        return ['No. Return', 'Tanggal', 'Supplier', 'Ref. Pembelian', 'Dicatat Oleh', 'Total Qty', 'Catatan'];
    }

    public function title(): string { return 'Laporan Return'; }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
