<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    use HasFactory;

    protected $table = 'configuraciones';

    protected $fillable = ['clave', 'valor', 'tipo', 'grupo', 'descripcion'];

    public static function get($clave, $default = null)
    {
        $config = self::where('clave', $clave)->first();
        if (!$config) return $default;

        return match ($config->tipo) {
            'integer' => (int) $config->valor,
            'boolean' => filter_var($config->valor, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($config->valor, true),
            default => $config->valor,
        };
    }

    public static function set($clave, $valor, $tipo = 'string', $grupo = 'general')
    {
        if ($tipo === 'json') $valor = json_encode($valor);
        if ($tipo === 'boolean') $valor = $valor ? '1' : '0';

        return self::updateOrCreate(
            ['clave' => $clave],
            ['valor' => $valor, 'tipo' => $tipo, 'grupo' => $grupo]
        );
    }
}
