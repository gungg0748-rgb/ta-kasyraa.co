<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function __construct(
        private ?string $from = null,
        private ?string $to = null
    ) {}

    public function collection()
    {
        $query = Sale::with(['user', 'items']);

        if ($this->from) $query->whereDate('date', '>=', $this->from);
        if ($this->to)   $query->whereDate('date', '<=', $this->to);

        return $query->latest('date')->get()->map(fn($s) => [
            $s->invoice_number,
            $s->date->format('d/m/Y'),
            $s->user->name,
            $s->items->count(),
            $s->total,
            $s->notes,
        ]);
    }

    public function headings(): array
    {
        return ['No. Invoice', 'Tanggal', 'Kasir', 'Jumlah Item', 'Total (Rp)', 'Catatan'];
    }

    public function title(): string { return 'Laporan Penjualan'; }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
