<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = ['nombre', 'descripcion', 'permisos', 'activo'];

    protected $casts = [
        'permisos' => 'array',
        'activo' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
