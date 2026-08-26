<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MetodoPago;

class MetodoPagoSeeder extends Seeder
{
    public function run(): void
    {
        $metodos = [
            [
                'nombre'              => 'Efectivo',
                'slug'                => 'efectivo',
                'icono'               => 'fa-money-bill-wave',
                'color'               => '#10b981',
                'requiere_referencia' => false,
                'permite_vueltos'     => true,
                'activo'              => true,
                'orden'               => 1,
            ],
            [
                'nombre'              => 'Tarjeta',
                'slug'                => 'tarjeta',
                'icono'               => 'fa-credit-card',
                'color'               => '#3b82f6',
                'requiere_referencia' => false,
                'permite_vueltos'     => false,
                'activo'              => true,
                'orden'               => 2,
            ],
            [
                'nombre'              => 'Yape',
                'slug'                => 'yape',
                'icono'               => 'fa-mobile-alt',
                'color'               => '#a855f7',
                'requiere_referencia' => true,
                'permite_vueltos'     => false,
                'activo'              => true,
                'orden'               => 3,
            ],
            [
                'nombre'              => 'Plin',
                'slug'                => 'plin',
                'icono'               => 'fa-mobile-alt',
                'color'               => '#06b6d4',
                'requiere_referencia' => true,
                'permite_vueltos'     => false,
                'activo'              => true,
                'orden'               => 4,
            ],
            [
                'nombre'              => 'Transferencia',
                'slug'                => 'transferencia',
                'icono'               => 'fa-building-columns',
                'color'               => '#f59e0b',
                'requiere_referencia' => true,
                'permite_vueltos'     => false,
                'activo'              => true,
                'orden'               => 5,
            ],
            [
                'nombre'              => 'Otro',
                'slug'                => 'otro',
                'icono'               => 'fa-coins',
                'color'               => '#64748b',
                'requiere_referencia' => false,
                'permite_vueltos'     => false,
                'activo'              => true,
                'orden'               => 6,
            ],
        ];

        foreach ($metodos as $data) {
            MetodoPago::updateOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
