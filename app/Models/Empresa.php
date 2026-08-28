<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $table = 'empresas';

    protected $fillable = [
        'razon_social', 'nombre_comercial', 'ruc_nit', 'direccion', 'ciudad',
        'telefono', 'whatsapp_alertas', 'email', 'sitio_web', 'logo', 'login_imagen', 'moneda', 'codigo_moneda',
        'impuesto', 'impuesto_incluido', 'mensaje_ticket', 'terminos_condiciones',
        // SUNAT
        'ubigeo', 'departamento', 'provincia', 'distrito', 'codigo_pais',
        'sunat_modo', 'sunat_usuario_sol', 'sunat_clave_sol',
        'sunat_certificado_path', 'sunat_certificado_password',
        'facturacion_electronica_activa',
    ];

    protected $casts = [
        'impuesto' => 'decimal:2',
        'impuesto_incluido' => 'boolean',
        'facturacion_electronica_activa' => 'boolean',
    ];

    protected $hidden = ['sunat_clave_sol', 'sunat_certificado_password'];

    public function getLogoUrlAttribute()
    {
        if ($this->logo) {
            if (str_starts_with($this->logo, 'http://') || str_starts_with($this->logo, 'https://')) {
                return $this->logo;
            }
            if (file_exists(public_path('uploads/empresa/' . $this->logo))) {
                return asset('uploads/empresa/' . $this->logo);
            }
            if (file_exists(public_path('img/' . $this->logo))) {
                return asset('img/' . $this->logo);
            }
            return asset('uploads/empresa/' . $this->logo);
        }
        if (file_exists(public_path('img/logo.png'))) {
            return asset('img/logo.png');
        }
        return null;
    }

    public function getLogoPathAttribute()
    {
        if ($this->logo && file_exists(public_path('uploads/empresa/' . $this->logo))) {
            return public_path('uploads/empresa/' . $this->logo);
        }
        if ($this->logo && file_exists(public_path('img/' . $this->logo))) {
            return public_path('img/' . $this->logo);
        }
        if (file_exists(public_path('img/logo.png'))) {
            return public_path('img/logo.png');
        }
        return null;
    }

    public function getLogoBase64Attribute()
    {
        $path = $this->logo_path;
        if ($path && file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
        return null;
    }

    public function getLoginImagenUrlAttribute()
    {
        if ($this->login_imagen && file_exists(public_path('uploads/empresa/' . $this->login_imagen))) {
            return asset('uploads/empresa/' . $this->login_imagen);
        }
        return null;
    }
}
