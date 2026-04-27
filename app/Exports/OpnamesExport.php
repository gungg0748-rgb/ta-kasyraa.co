<?php

namespace App\Exports;

use App\Models\StockOpname;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OpnamesExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function __construct(
        private ?string $from = null,
        private ?string $to = null,
        private ?string $status = null
    ) {}

    public function collection()
    {
        $query = StockOpname::with(['user', 'items.variant.product']);

        if ($this->from)   $query->whereDate('date', '>=', $this->from);
        if ($this->to)     $query->whereDate('date', '<=', $this->to);
        if ($this->status) $query->where('status', $this->status);

        $rows = collect();
        foreach ($query->latest('date')->get() as $opname) {
            foreach ($opname->items as $item) {
                $rows->push([
                    $opname->date->format('d/m/Y'),
                    $opname->user->name,
                    ucfirst($opname->status),
                    $item->variant->product->name,
                    collect([$item->variant->model, $item->variant->color, $item->variant->size])->filter()->implode(' / ') ?: '-',
                    $item->system_stock,
                    $item->physical_stock,
                    $item->difference,
                    $opname->notes,
                ]);
            }
        }
        return $rows;
    }

    public function headings(): array
    {
        return ['Tanggal', 'Dicatat Oleh', 'Status', 'Produk', 'Varian', 'Stok Sistem', 'Stok Fisik', 'Selisih', 'Catatan'];
    }

    public function title(): string { return 'Laporan Stok Opname'; }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
