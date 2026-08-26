<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'codigo', 'tipo_documento', 'documento', 'nombres', 'apellidos', 'razon_social',
        'telefono', 'email', 'direccion', 'ciudad', 'fecha_nacimiento', 'genero',
        'puntos_fidelidad', 'credito_limite', 'credito_usado', 'observaciones', 'activo',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'puntos_fidelidad' => 'integer',
        'credito_limite' => 'decimal:2',
        'credito_usado' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    public function puntosFidelidad()
    {
        return $this->hasMany(PuntoFidelidad::class);
    }

    public function getNombreCompletoAttribute()
    {
        return trim($this->nombres . ' ' . $this->apellidos) ?: $this->razon_social;
    }

    public function getCreditoDisponibleAttribute()
    {
        return $this->credito_limite - $this->credito_usado;
    }
}
