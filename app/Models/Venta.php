<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';

    protected $fillable = [
        'numero_ticket', 'tipo_comprobante', 'serie', 'fecha_venta',
        'cliente_id', 'user_id', 'turno_caja_id',
        'subtotal', 'descuento', 'impuesto', 'total', 'monto_recibido', 'cambio',
        'forma_pago', 'detalle_pago', 'estado', 'observaciones',
        'comprobante_electronico_id',
    ];

    protected $casts = [
        'fecha_venta' => 'datetime',
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'impuesto' => 'decimal:2',
        'total' => 'decimal:2',
        'monto_recibido' => 'decimal:2',
        'cambio' => 'decimal:2',
        'detalle_pago' => 'array',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function turnoCaja()
    {
        return $this->belongsTo(TurnoCaja::class);
    }

    public function detalles()
    {
        return $this->hasMany(VentaDetalle::class);
    }

    public function comprobanteElectronico()
    {
        return $this->belongsTo(ComprobanteElectronico::class, 'comprobante_electronico_id');
    }
}
