<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function __construct(
        private ?int $categoryId = null,
        private ?string $search = null
    ) {}

    public function collection()
    {
        $query = Product::with(['category', 'unit', 'variants']);

        if ($this->categoryId) {
            $query->where('category_id', $this->categoryId);
        }
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        $rows = collect();
        foreach ($query->orderBy('name')->get() as $product) {
            foreach ($product->variants as $variant) {
                $stock  = $variant->stock;
                $status = $stock == 0 ? 'Habis' : ($stock <= $product->reorder_level ? 'Kritis' : 'Aman');
                $rows->push([
                    $product->name,
                    $product->category->name,
                    $product->unit->name,
                    $variant->model ?: '-',
                    $variant->color ?: '-',
                    $variant->size  ?: '-',
                    $variant->barcode,
                    $stock,
                    $product->reorder_level,
                    $status,
                    'Rp ' . number_format($product->price, 0, ',', '.'),
                ]);
            }
        }
        return $rows;
    }

    public function headings(): array
    {
        return ['Produk', 'Kategori', 'Satuan', 'Model', 'Warna', 'Ukuran', 'Barcode', 'Stok', 'Min. Stok', 'Status', 'Harga Jual'];
    }

    public function title(): string { return 'Laporan Stok'; }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
