<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'username', 'email', 'password', 'role_id',
        'telefono', 'avatar', 'activo', 'ultimo_login',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'ultimo_login' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    public function turnosCaja()
    {
        return $this->hasMany(TurnoCaja::class);
    }

    public function hasRole($nombre): bool
    {
        return $this->role && strtolower($this->role->nombre) === strtolower($nombre);
    }

    public function hasPermission($permiso): bool
    {
        if (!$this->role) return false;
        $permisos = $this->role->permisos ?? [];
        return in_array($permiso, $permisos) || in_array('*', $permisos);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('Administrador');
    }
}
