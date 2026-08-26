<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\ComprobanteElectronico;
use App\Models\SerieDocumento;
use App\Models\Venta;
use App\Models\ResumenDiario;
use App\Models\ComunicacionBaja;
use Greenter\See;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Client\Client;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\Note;
use Greenter\Model\Summary\Summary;
use Greenter\Model\Summary\SummaryDetail;
use Greenter\Model\Voided\Reversion;
use Greenter\Model\Voided\VoidedDetail;
use Greenter\Report\HtmlReport;
use Greenter\Report\PdfReport;
use Greenter\Report\Resolver\DefaultTemplateResolver;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * Servicio principal de integración con SUNAT vía Greenter.
 *
 * Maneja:
 * - Generación de XML firmado
 * - Envío a SUNAT (modo beta o producción)
 * - Procesamiento de CDR
 * - Generación de PDF
 * - Resúmenes diarios y comunicaciones de baja
 */
class SunatService
{
    protected ?Empresa $empresa = null;
    protected ?See $see = null;

    public function __construct()
    {
        $this->empresa = Empresa::first();
    }

    /** Inicializa Greenter con la configuración de la empresa */
    protected function getSee(): See
    {
        if ($this->see) return $this->see;

        $see = new See();

        // Certificado digital (.pem)
        $certPath = $this->getCertificadoPath();
        if (file_exists($certPath)) {
            $see->setCertificate(file_get_contents($certPath));
        } else {
            throw new \Exception("No se encontró el certificado digital. Sube tu certificado en Configuración → SUNAT");
        }

        // Servicio SOAP de SUNAT
        $endpoint = $this->empresa->sunat_modo === 'produccion'
            ? SunatEndpoints::FE_PRODUCCION
            : SunatEndpoints::FE_BETA;
        $see->setService($endpoint);

        // Credenciales SOL
        $usuario = $this->empresa->sunat_modo === 'beta'
            ? $this->empresa->ruc_nit . 'MODDATOS'  // Usuario fijo de SUNAT en beta
            : $this->empresa->sunat_usuario_sol;
        $clave = $this->empresa->sunat_modo === 'beta'
            ? 'MODDATOS'
            : $this->empresa->sunat_clave_sol;

        $see->setCredentials($usuario, $clave);

        $this->see = $see;
        return $see;
    }

    /** Construye la entidad Company de Greenter */
    protected function buildCompany(): Company
    {
        return (new Company())
            ->setRuc($this->empresa->ruc_nit)
            ->setRazonSocial($this->empresa->razon_social)
            ->setNombreComercial($this->empresa->nombre_comercial ?: $this->empresa->razon_social)
            ->setAddress((new Address())
                ->setUbigueo($this->empresa->ubigeo ?: '150101')
                ->setDepartamento($this->empresa->departamento ?: 'LIMA')
                ->setProvincia($this->empresa->provincia ?: 'LIMA')
                ->setDistrito($this->empresa->distrito ?: 'LIMA')
                ->setUrbanizacion('-')
                ->setCodLocal('0000')
                ->setDireccion($this->empresa->direccion ?: 'Sin direccion'));
    }

    /**
     * Genera comprobante electrónico (Boleta o Factura) a partir de una venta
     */
    public function emitirComprobante(Venta $venta, string $tipoDocumento = '03'): ComprobanteElectronico
    {
        $venta->load(['cliente', 'detalles.producto', 'user']);

        // Validar tipo de doc según receptor
        if ($tipoDocumento === '01') {
            if (!$venta->cliente || $venta->cliente->tipo_documento !== 'RUC') {
                throw new \Exception("Para emitir Factura el cliente debe tener RUC válido (11 dígitos)");
            }
        }

        // Obtener serie y correlativo
        $numeracion = SerieDocumento::siguienteNumero($tipoDocumento);

        // Crear comprobante en BD
        $comprobante = ComprobanteElectronico::create([
            'venta_id' => $venta->id,
            'tipo_documento' => $tipoDocumento,
            'serie' => $numeracion['serie'],
            'numero' => $numeracion['numero'],
            'numero_completo' => $numeracion['completo'],
            'emisor_ruc' => $this->empresa->ruc_nit,
            'emisor_razon_social' => $this->empresa->razon_social,
            'receptor_tipo_doc' => $this->getReceptorTipoDoc($venta, $tipoDocumento),
            'receptor_numero_doc' => $venta->cliente?->documento ?: '00000000',
            'receptor_razon_social' => $venta->cliente?->nombre_completo ?: 'CLIENTE GENERICO',
            'receptor_direccion' => $venta->cliente?->direccion,
            'receptor_email' => $venta->cliente?->email,
            'fecha_emision' => $venta->fecha_venta->toDateString(),
            'hora_emision' => $venta->fecha_venta->toTimeString(),
            'moneda' => $this->empresa->codigo_moneda ?: 'PEN',
            'total_gravadas' => $venta->subtotal,
            'total_igv' => $venta->impuesto,
            'total_descuentos' => $venta->descuento,
            'importe_total' => $venta->total,
            'importe_letras' => $this->numeroALetras($venta->total) . ' SOLES',
            'estado_sunat' => 'pendiente',
            'user_id' => auth()->id() ?? $venta->user_id,
        ]);

        // Vincular la venta con el comprobante
        $venta->update(['comprobante_electronico_id' => $comprobante->id]);

        return $comprobante;
    }

    /**
     * Envía el comprobante a SUNAT y procesa el CDR
     */
    public function enviarASunat(ComprobanteElectronico $comprobante): array
    {
        try {
            $see = $this->getSee();
            $venta = $comprobante->venta;

            // Construir la factura/boleta Greenter
            $invoice = $this->buildInvoice($comprobante, $venta);

            // Generar XML firmado
            $xml = $see->getXmlSigned($invoice);
            $hash = $see->getFactory()->getLastXml();
            $comprobante->hash = $invoice->getFirma() ?? '';

            // Guardar XML
            $xmlPath = $this->guardarXml($comprobante, $xml);
            $comprobante->xml_path = $xmlPath;

            // Enviar a SUNAT
            $result = $see->send($invoice);

            $comprobante->fecha_envio_sunat = now();
            $comprobante->intentos_envio++;

            if ($result->isSuccess()) {
                // Guardar CDR
                $cdrXml = $result->getCdrResponse() ? $result->getCdrZip() : null;
                if ($cdrXml) {
                    $cdrPath = $this->guardarCdr($comprobante, $cdrXml);
                    $comprobante->cdr_path = $cdrPath;
                }

                $cdr = $result->getCdrResponse();
                $comprobante->estado_sunat = 'aceptado';
                $comprobante->codigo_respuesta_sunat = $cdr->getCode();
                $comprobante->mensaje_sunat = $cdr->getDescription();

                $comprobante->qr_data = $this->buildQrData($comprobante);
                $comprobante->save();

                return [
                    'success' => true,
                    'codigo' => $cdr->getCode(),
                    'mensaje' => $cdr->getDescription(),
                ];
            } else {
                $error = $result->getError();
                $comprobante->estado_sunat = 'rechazado';
                $comprobante->codigo_respuesta_sunat = $error->getCode();
                $comprobante->mensaje_sunat = $error->getMessage();
                $comprobante->save();

                return [
                    'success' => false,
                    'codigo' => $error->getCode(),
                    'mensaje' => $error->getMessage(),
                ];
            }
        } catch (\Throwable $e) {
            $comprobante->estado_sunat = 'excepcion';
            $comprobante->mensaje_sunat = $e->getMessage();
            $comprobante->intentos_envio++;
            $comprobante->save();

            return [
                'success' => false,
                'codigo' => 'EXC',
                'mensaje' => $e->getMessage(),
            ];
        }
    }

    /**
     * Construye la entidad Invoice (Factura/Boleta) de Greenter
     */
    protected function buildInvoice(ComprobanteElectronico $comprobante, Venta $venta): Invoice
    {
        $client = (new Client())
            ->setTipoDoc($comprobante->receptor_tipo_doc)
            ->setNumDoc($comprobante->receptor_numero_doc)
            ->setRznSocial($comprobante->receptor_razon_social);

        if ($comprobante->receptor_direccion) {
            $client->setAddress((new Address())->setDireccion($comprobante->receptor_direccion));
        }

        $invoice = (new Invoice())
            ->setUblVersion('2.1')
            ->setTipoOperacion('0101') // Venta interna
            ->setTipoDoc($comprobante->tipo_documento)
            ->setSerie($comprobante->serie)
            ->setCorrelativo($comprobante->numero)
            ->setFechaEmision(Carbon::parse($comprobante->fecha_emision)->setTimeFromTimeString($comprobante->hora_emision))
            ->setTipoMoneda($comprobante->moneda)
            ->setCompany($this->buildCompany())
            ->setClient($client)
            ->setMtoOperGravadas((float) $comprobante->total_gravadas)
            ->setMtoIGV((float) $comprobante->total_igv)
            ->setTotalImpuestos((float) $comprobante->total_igv)
            ->setValorVenta((float) $comprobante->total_gravadas)
            ->setSubTotal((float) ($comprobante->total_gravadas + $comprobante->total_igv))
            ->setMtoImpVenta((float) $comprobante->importe_total);

        // Detalles
        $details = [];
        foreach ($venta->detalles as $d) {
            $valorUnitario = round((float) $d->precio_unitario / 1.18, 4);
            $valorVenta = round($valorUnitario * (float) $d->cantidad, 2);
            $igv = round($valorVenta * 0.18, 2);

            $details[] = (new SaleDetail())
                ->setCodProducto($d->codigo)
                ->setUnidad('NIU') // Unidad
                ->setDescripcion($d->descripcion)
                ->setCantidad((float) $d->cantidad)
                ->setMtoValorUnitario($valorUnitario)
                ->setMtoValorVenta($valorVenta)
                ->setMtoBaseIgv($valorVenta)
                ->setPorcentajeIgv(18.00)
                ->setIgv($igv)
                ->setTipAfeIgv('10') // Gravado
                ->setTotalImpuestos($igv)
                ->setMtoPrecioUnitario((float) $d->precio_unitario);
        }
        $invoice->setDetails($details);

        // Leyenda (importe en letras)
        $invoice->setLegends([
            (new Legend())->setCode('1000')->setValue($comprobante->importe_letras),
        ]);

        return $invoice;
    }

    /**
     * Genera y envía una Nota de Crédito
     */
    public function emitirNotaCredito(Venta $venta, string $motivo, string $codigoMotivo = '01'): ComprobanteElectronico
    {
        if (!$venta->comprobanteElectronico) {
            throw new \Exception("La venta no tiene comprobante electrónico para anular");
        }

        $comprobanteOrigen = $venta->comprobanteElectronico;
        $tipoNota = '07';

        $numeracion = SerieDocumento::siguienteNumero($tipoNota,
            $comprobanteOrigen->tipo_documento === '01' ? 'FC01' : 'BC01');

        $nc = ComprobanteElectronico::create([
            'venta_id' => $venta->id,
            'tipo_documento' => $tipoNota,
            'serie' => $numeracion['serie'],
            'numero' => $numeracion['numero'],
            'numero_completo' => $numeracion['completo'],
            'emisor_ruc' => $this->empresa->ruc_nit,
            'emisor_razon_social' => $this->empresa->razon_social,
            'receptor_tipo_doc' => $comprobanteOrigen->receptor_tipo_doc,
            'receptor_numero_doc' => $comprobanteOrigen->receptor_numero_doc,
            'receptor_razon_social' => $comprobanteOrigen->receptor_razon_social,
            'fecha_emision' => now()->toDateString(),
            'hora_emision' => now()->toTimeString(),
            'moneda' => $comprobanteOrigen->moneda,
            'total_gravadas' => $comprobanteOrigen->total_gravadas,
            'total_igv' => $comprobanteOrigen->total_igv,
            'importe_total' => $comprobanteOrigen->importe_total,
            'importe_letras' => $comprobanteOrigen->importe_letras,
            'doc_referencia_tipo' => $comprobanteOrigen->tipo_documento,
            'doc_referencia_serie_numero' => $comprobanteOrigen->numero_completo,
            'codigo_motivo_nc' => $codigoMotivo,
            'motivo_referencia' => $motivo,
            'estado_sunat' => 'pendiente',
            'user_id' => auth()->id(),
        ]);

        return $nc;
    }

    /**
     * Genera un resumen diario de boletas y comunicaciones de baja
     */
    public function generarResumenDiario(Carbon $fecha): ResumenDiario
    {
        $boletas = ComprobanteElectronico::boletas()
            ->whereDate('fecha_emision', $fecha)
            ->where('estado_sunat', 'aceptado')
            ->get();

        if ($boletas->isEmpty()) {
            throw new \Exception("No hay boletas aceptadas para la fecha {$fecha->toDateString()}");
        }

        $correlativo = ResumenDiario::whereDate('fecha_generacion', now())->count() + 1;
        $identificador = 'RC-' . now()->format('Ymd') . '-' . str_pad($correlativo, 3, '0', STR_PAD_LEFT);

        $resumen = ResumenDiario::create([
            'identificador' => $identificador,
            'fecha_resumen' => $fecha->toDateString(),
            'fecha_generacion' => now()->toDateString(),
            'cantidad_comprobantes' => $boletas->count(),
            'total_general' => $boletas->sum('importe_total'),
            'estado_sunat' => 'pendiente',
            'user_id' => auth()->id(),
        ]);

        // TODO: enviar resumen a SUNAT vía Greenter Summary

        return $resumen;
    }

    // ============= Métodos utilitarios =============

    protected function getReceptorTipoDoc(Venta $venta, string $tipoDoc): string
    {
        if ($tipoDoc === '01') return '6'; // Factura → RUC
        if (!$venta->cliente) return '0'; // sin identificación
        return match(strtoupper($venta->cliente->tipo_documento)) {
            'RUC' => '6',
            'DNI' => '1',
            'CE' => '4',
            'PASAPORTE' => '7',
            default => '0',
        };
    }

    protected function getCertificadoPath(): string
    {
        $path = $this->empresa->sunat_certificado_path;
        if (!$path) return '';
        return storage_path('app/' . $path);
    }

    protected function guardarXml(ComprobanteElectronico $c, string $xml): string
    {
        $dir = 'sunat/xml/' . date('Y/m');
        Storage::makeDirectory($dir);
        $filename = "{$c->emisor_ruc}-{$c->tipo_documento}-{$c->numero_completo}.xml";
        $path = "$dir/$filename";
        Storage::put($path, $xml);
        return $path;
    }

    protected function guardarCdr(ComprobanteElectronico $c, string $cdrZip): string
    {
        $dir = 'sunat/cdr/' . date('Y/m');
        Storage::makeDirectory($dir);
        $filename = "R-{$c->emisor_ruc}-{$c->tipo_documento}-{$c->numero_completo}.zip";
        $path = "$dir/$filename";
        Storage::put($path, $cdrZip);
        return $path;
    }

    protected function buildQrData(ComprobanteElectronico $c): string
    {
        // Formato SUNAT: RUC | TipoDoc | Serie | Numero | IGV | Total | FechaEmision | TipoDocReceptor | NumDocReceptor
        return implode('|', [
            $c->emisor_ruc,
            $c->tipo_documento,
            $c->serie,
            $c->numero,
            number_format($c->total_igv, 2, '.', ''),
            number_format($c->importe_total, 2, '.', ''),
            Carbon::parse($c->fecha_emision)->format('d/m/Y'),
            $c->receptor_tipo_doc,
            $c->receptor_numero_doc,
        ]);
    }

    /** Convierte un número decimal a letras en español */
    public function numeroALetras(float $numero): string
    {
        $entero = (int) floor($numero);
        $decimales = (int) round(($numero - $entero) * 100);

        $letras = $this->convertirEntero($entero);
        return trim($letras) . ' CON ' . str_pad((string)$decimales, 2, '0', STR_PAD_LEFT) . '/100';
    }

    protected function convertirEntero(int $n): string
    {
        if ($n === 0) return 'CERO';
        if ($n === 1) return 'UN';

        $unidades = ['', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
        $decenas = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
        $veintenas = ['VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        $centenas = ['CIEN', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

        if ($n >= 1000000) {
            $millones = intdiv($n, 1000000);
            $resto = $n % 1000000;
            $prefix = $millones === 1 ? 'UN MILLON' : $this->convertirEntero($millones) . ' MILLONES';
            return trim($prefix . ' ' . ($resto > 0 ? $this->convertirEntero($resto) : ''));
        }

        if ($n >= 1000) {
            $miles = intdiv($n, 1000);
            $resto = $n % 1000;
            $prefix = $miles === 1 ? 'MIL' : $this->convertirEntero($miles) . ' MIL';
            return trim($prefix . ' ' . ($resto > 0 ? $this->convertirEntero($resto) : ''));
        }

        if ($n >= 100) {
            if ($n === 100) return 'CIEN';
            $c = intdiv($n, 100);
            $resto = $n % 100;
            $prefix = $c === 1 ? 'CIENTO' : $centenas[$c - 1];
            return trim($prefix . ' ' . ($resto > 0 ? $this->convertirEntero($resto) : ''));
        }

        if ($n >= 20) {
            $d = intdiv($n, 10);
            $u = $n % 10;
            if ($u === 0) return $veintenas[$d - 2];
            if ($d === 2) return 'VEINTI' . strtolower($unidades[$u]);
            return $veintenas[$d - 2] . ' Y ' . $unidades[$u];
        }

        if ($n >= 10) return $decenas[$n - 10];
        return $unidades[$n];
    }
}
