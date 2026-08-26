<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promocion extends Model
{
    use HasFactory;

    protected $table = 'promociones';

    protected $fillable = [
        'nombre', 'descripcion', 'tipo', 'valor', 'producto_id', 'categoria_id',
        'fecha_inicio', 'fecha_fin', 'cantidad_minima', 'activo',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'activo' => 'boolean',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function getVigenteAttribute()
    {
        $hoy = now()->toDateString();
        return $this->activo && $this->fecha_inicio->toDateString() <= $hoy && $this->fecha_fin->toDateString() >= $hoy;
    }
}
