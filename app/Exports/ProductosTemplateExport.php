<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductosTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    public function array(): array
    {
        return [
            ['7751234567890', 'Whisky Johnnie Walker Black Label 750ml', '110.50', '25', 'Licores', 'WHI-001'],
            ['7750987654321', 'Cerveza Pilsen Callao 620ml', '6.50', '120', 'Cervezas', 'CER-002'],
        ];
    }

    public function headings(): array
    {
        return [
            'CODIGO_BARRAS',
            'NOMBRE_PRODUCTO',
            'PRECIO_VENTA',
            'STOCK_INICIAL',
            'CATEGORIA',
            'CODIGO_INTERNO'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => '1E293B']]],
        ];
    }
}
