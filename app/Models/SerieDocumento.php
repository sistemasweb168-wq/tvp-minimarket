<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SerieDocumento extends Model
{
    use HasFactory;

    protected $table = 'series_documentos';

    protected $fillable = [
        'tipo_documento', 'serie', 'correlativo_actual',
        'correlativo_max', 'activo', 'descripcion',
    ];

    protected $casts = [
        'correlativo_actual' => 'integer',
        'correlativo_max' => 'integer',
        'activo' => 'boolean',
    ];

    /** Obtiene el próximo correlativo y lo incrementa atómicamente */
    public static function siguienteNumero($tipoDocumento, $serie = null): array
    {
        if (!$serie) {
            $serie = self::serieDefault($tipoDocumento);
        }

        $registro = self::firstOrCreate(
            ['tipo_documento' => $tipoDocumento, 'serie' => $serie],
            ['correlativo_actual' => 0, 'activo' => true]
        );

        $registro->increment('correlativo_actual');
        $numero = str_pad($registro->correlativo_actual, 8, '0', STR_PAD_LEFT);

        return [
            'serie' => $serie,
            'numero' => $numero,
            'completo' => $serie . '-' . $numero,
        ];
    }

    public static function serieDefault($tipoDocumento): string
    {
        return match($tipoDocumento) {
            '01' => 'F001', // Factura
            '03' => 'B001', // Boleta
            '07' => 'FC01', // Nota de Crédito (de factura)
            '08' => 'FD01', // Nota de Débito
            default => 'B001',
        };
    }
}
