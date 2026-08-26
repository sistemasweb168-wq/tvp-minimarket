<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PuntoFidelidad extends Model
{
    use HasFactory;

    protected $table = 'puntos_fidelidad';

    protected $fillable = [
        'cliente_id', 'venta_id', 'tipo', 'puntos',
        'saldo_anterior', 'saldo_nuevo', 'descripcion',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }
}
