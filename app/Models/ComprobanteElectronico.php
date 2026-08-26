<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComprobanteElectronico extends Model
{
    use HasFactory;

    protected $table = 'comprobantes_electronicos';

    protected $fillable = [
        'venta_id', 'tipo_documento', 'serie', 'numero', 'numero_completo',
        'emisor_ruc', 'emisor_razon_social',
        'receptor_tipo_doc', 'receptor_numero_doc', 'receptor_razon_social',
        'receptor_direccion', 'receptor_email',
        'fecha_emision', 'hora_emision', 'fecha_vencimiento', 'moneda',
        'total_gravadas', 'total_exoneradas', 'total_inafectas', 'total_gratuitas',
        'total_igv', 'total_isc', 'total_descuentos', 'importe_total', 'importe_letras',
        'doc_referencia_tipo', 'doc_referencia_serie_numero', 'motivo_referencia', 'codigo_motivo_nc',
        'estado_sunat', 'codigo_respuesta_sunat', 'mensaje_sunat',
        'hash', 'xml_path', 'cdr_path', 'pdf_path', 'qr_data',
        'fecha_envio_sunat', 'intentos_envio',
        'user_id', 'observaciones',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'fecha_envio_sunat' => 'datetime',
        'total_gravadas' => 'decimal:2',
        'total_exoneradas' => 'decimal:2',
        'total_inafectas' => 'decimal:2',
        'total_gratuitas' => 'decimal:2',
        'total_igv' => 'decimal:2',
        'total_isc' => 'decimal:2',
        'total_descuentos' => 'decimal:2',
        'importe_total' => 'decimal:2',
        'intentos_envio' => 'integer',
    ];

    /** Mapeo de tipos de documento SUNAT */
    public const TIPOS = [
        '01' => 'Factura',
        '03' => 'Boleta de Venta',
        '07' => 'Nota de Crédito',
        '08' => 'Nota de Débito',
    ];

    public const MOTIVOS_NC = [
        '01' => 'Anulación de la operación',
        '02' => 'Anulación por error en el RUC',
        '03' => 'Corrección por error en la descripción',
        '04' => 'Descuento global',
        '05' => 'Descuento por ítem',
        '06' => 'Devolución total',
        '07' => 'Devolución por ítem',
        '08' => 'Bonificación',
        '09' => 'Disminución en el valor',
        '10' => 'Otros conceptos',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTipoDocumentoNombreAttribute()
    {
        return self::TIPOS[$this->tipo_documento] ?? 'Desconocido';
    }

    public function getEstadoColorAttribute()
    {
        return match($this->estado_sunat) {
            'aceptado' => 'green',
            'enviado' => 'blue',
            'pendiente' => 'yellow',
            'observado' => 'orange',
            'rechazado', 'baja', 'anulado' => 'red',
            default => 'slate',
        };
    }

    public function getReceptorTipoDocLabelAttribute()
    {
        return match($this->receptor_tipo_doc) {
            '1' => 'DNI',
            '6' => 'RUC',
            '4' => 'CE',
            '7' => 'PASAPORTE',
            default => '-',
        };
    }

    public function scopeBoletas($q) { return $q->where('tipo_documento', '03'); }
    public function scopeFacturas($q) { return $q->where('tipo_documento', '01'); }
    public function scopeNotasCredito($q) { return $q->where('tipo_documento', '07'); }
}
