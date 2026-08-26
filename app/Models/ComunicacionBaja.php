<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComunicacionBaja extends Model
{
    use HasFactory;

    protected $table = 'comunicaciones_baja';

    protected $fillable = [
        'identificador', 'comprobante_id', 'fecha_generacion', 'motivo',
        'estado_sunat', 'ticket_sunat', 'codigo_respuesta', 'mensaje_respuesta',
        'xml_path', 'cdr_path', 'user_id',
    ];

    protected $casts = ['fecha_generacion' => 'date'];

    public function comprobante()
    {
        return $this->belongsTo(ComprobanteElectronico::class, 'comprobante_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
