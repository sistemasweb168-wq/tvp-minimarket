<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditoriaCancelacionPos extends Model
{
    use HasFactory;

    protected $table = "auditoria_cancelaciones_pos";

    protected $fillable = [
        "user_id",
        "turno_caja_id",
        "producto_id",
        "producto_nombre",
        "tipo_evento",
        "cantidad",
        "precio_unitario",
        "total_afectado",
        "motivo",
    ];

    protected $casts = [
        "cantidad" => "decimal:3",
        "precio_unitario" => "decimal:2",
        "total_afectado" => "decimal:2",
        "created_at" => "datetime",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function turno()
    {
        return $this->belongsTo(TurnoCaja::class, "turno_caja_id");
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}

