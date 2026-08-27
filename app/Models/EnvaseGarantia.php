<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnvaseGarantia extends Model
{
    use HasFactory;

    protected $table = 'envases_garantias';

    protected $fillable = [
        'cliente_id',
        'cliente_nombre',
        'tipo_envase',
        'cantidad',
        'monto_garantia',
        'estado',
        'fecha_prestamo',
        'fecha_devolucion',
        'user_id',
        'turno_caja_id',
        'observaciones',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'monto_garantia' => 'decimal:2',
        'fecha_prestamo' => 'datetime',
        'fecha_devolucion' => 'datetime',
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
}
