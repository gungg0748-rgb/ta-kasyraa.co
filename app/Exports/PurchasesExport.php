<?php

namespace App\Exports;

use App\Models\Purchase;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PurchasesExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function __construct(
        private ?int $supplierId = null,
        private ?string $from = null,
        private ?string $to = null
    ) {}

    public function collection()
    {
        $query = Purchase::with(['supplier', 'user', 'items']);

        if ($this->supplierId) $query->where('supplier_id', $this->supplierId);
        if ($this->from)       $query->whereDate('date', '>=', $this->from);
        if ($this->to)         $query->whereDate('date', '<=', $this->to);

        return $query->latest('date')->get()->map(fn($p) => [
            $p->invoice_number,
            $p->date->format('d/m/Y'),
            $p->supplier->name,
            $p->user->name,
            $p->items->count(),
            $p->total,
            $p->notes,
        ]);
    }

    public function headings(): array
    {
        return ['No. Invoice', 'Tanggal', 'Supplier', 'Dicatat Oleh', 'Jumlah Item', 'Total (Rp)', 'Catatan'];
    }

    public function title(): string { return 'Laporan Pembelian'; }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
