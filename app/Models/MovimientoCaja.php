<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoCaja extends Model
{
    use HasFactory;

    protected $table = 'movimientos_caja';

    protected $fillable = [
        'turno_caja_id', 'user_id', 'tipo', 'concepto', 'monto', 'observaciones',
    ];

    protected $casts = ['monto' => 'decimal:2'];

    public function turnoCaja()
    {
        return $this->belongsTo(TurnoCaja::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
