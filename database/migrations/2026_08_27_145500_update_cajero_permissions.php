<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $cajeroRole = DB::table('roles')->where('nombre', 'Cajero')->first();
        if ($cajeroRole) {
            $permisos = json_decode($cajeroRole->permisos ?? '[]', true) ?: [];
            if (!in_array('pos', $permisos)) {
                $permisos[] = 'pos';
            }
            if (!in_array('ventas', $permisos)) {
                $permisos[] = 'ventas';
            }
            if (!in_array('caja', $permisos)) {
                $permisos[] = 'caja';
            }
            DB::table('roles')->where('id', $cajeroRole->id)->update([
                'permisos' => json_encode($permisos)
            ]);
        }
    }

    public function down(): void
    {
        //
    }
};
