<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para el registro de actividad del sistema (log de auditoría).
 *
 * La tabla actividad_log registra acciones de usuarios como
 * inicios de sesión, creación/edición/eliminación de registros, etc.
 */
class ActividadLog extends Model
{
    use HasFactory;

    protected $table = 'actividad_log';

    protected $fillable = [
        'user_id',
        'accion',
        'modulo',
        'descripcion',
        'ip',
        'user_agent',
        'datos_anteriores',
        'datos_nuevos',
        'referencia_tipo',
        'referencia_id',
    ];

    protected $casts = [
        'datos_anteriores' => 'array',
        'datos_nuevos'     => 'array',
    ];

    /* ─── Relaciones ──────────────────────────────────────────── */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* ─── Scopes ──────────────────────────────────────────────── */

    public function scopeDelUsuario($q, $userId) { return $q->where('user_id', $userId); }
    public function scopeDelModulo($q, $modulo)  { return $q->where('modulo', $modulo); }
    public function scopeHoy($q)                  { return $q->whereDate('created_at', today()); }
}
