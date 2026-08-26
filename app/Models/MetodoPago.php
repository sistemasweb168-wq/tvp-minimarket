<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class MetodoPago extends Model
{
    use HasFactory;

    protected $table = 'metodos_pago';

    protected $fillable = [
        'nombre',
        'slug',
        'icono',
        'color',
        'requiere_referencia',
        'permite_vueltos',
        'activo',
        'orden',
    ];

    protected $casts = [
        'requiere_referencia' => 'boolean',
        'permite_vueltos'     => 'boolean',
        'activo'              => 'boolean',
        'orden'               => 'integer',
    ];

    public function scopeActivo($query)
    {
        return $query->where('activo', true)->orderBy('orden', 'asc')->orderBy('id', 'asc');
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->nombre);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('nombre') && empty($model->slug)) {
                $model->slug = Str::slug($model->nombre);
            }
        });
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'metodo_pago_id');
    }
}
