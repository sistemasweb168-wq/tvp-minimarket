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
            ['7751234567890', 'Whisky Johnnie Walker Black Label 750ml', '80.00', '110.50', '100.00', '6', '25', '5', 'UND', 'Licores', 'WHI-001'],
            ['7750987654321', 'Cerveza Pilsen Callao 620ml', '4.50', '6.50', '5.50', '12', '120', '24', 'UND', 'Cervezas', 'CER-002'],
        ];
    }

    public function headings(): array
    {
        return [
            'CODIGO_BARRAS',
            'NOMBRE_PRODUCTO',
            'PRECIO_COMPRA',
            'PRECIO_VENTA',
            'PRECIO_POR_MAYOR',
            'CANTIDAD_AL_POR_MAYOR',
            'STOCK_INICIAL',
            'STOCK_MINIMO',
            'UNIDAD_MEDIDA',
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
