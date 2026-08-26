<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    use HasFactory;

    protected $table = 'cajas';
    protected $fillable = ['nombre', 'descripcion', 'activo'];
    protected $casts = ['activo' => 'boolean'];

    public function turnos()
    {
        return $this->hasMany(TurnoCaja::class);
    }

    public function turnoActivo()
    {
        return $this->hasOne(TurnoCaja::class)->where('estado', 'abierto');
    }
}
