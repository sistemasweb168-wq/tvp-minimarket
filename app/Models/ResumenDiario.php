<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResumenDiario extends Model
{
    use HasFactory;

    protected $table = 'resumenes_diarios';

    protected $fillable = [
        'identificador', 'fecha_resumen', 'fecha_generacion',
        'cantidad_comprobantes', 'total_general',
        'estado_sunat', 'ticket_sunat', 'codigo_respuesta', 'mensaje_respuesta',
        'xml_path', 'cdr_path', 'user_id',
    ];

    protected $casts = [
        'fecha_resumen' => 'date',
        'fecha_generacion' => 'date',
        'total_general' => 'decimal:2',
        'cantidad_comprobantes' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
