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

        // Certificado digital — acepta .pem directamente o convierte .pfx
        $certPath = $this->getCertificadoPath();
        if (!$certPath || !file_exists($certPath)) {
            throw new \Exception("No se encontró el certificado digital. Sube tu certificado en Configuración → SUNAT");
        }

        $certContent = file_get_contents($certPath);

        // Detectar si es .pfx binario y convertir a .pem en memoria
        if ($this->esPfx($certContent)) {
            $pemContent = $this->pfxToPem(
                $certContent,
                $this->empresa->sunat_certificado_password ?? ''
            );
            if (!$pemContent) {
                throw new \Exception("No se pudo leer el certificado .pfx. Verifica que la contraseña sea correcta.");
            }
            $certContent = $pemContent;
        }

        $see->setCertificate($certContent);

        // Servicio SOAP de SUNAT — Beta o Producción
        $endpoint = $this->empresa->sunat_modo === 'produccion'
            ? SunatEndpoints::FE_PRODUCCION
            : SunatEndpoints::FE_BETA;
        $see->setService($endpoint);

        // Credenciales SOL (en beta SUNAT usa MODDATOS fijo)
        $usuario = ($this->empresa->sunat_modo !== 'produccion')
            ? $this->empresa->ruc_nit . 'MODDATOS'
            : $this->empresa->sunat_usuario_sol;
        $clave = ($this->empresa->sunat_modo !== 'produccion')
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

        // Calcular totales correctos usando la tasa de impuesto configurada
        $tasaIgv  = $this->getTasaIgv();
        $totales  = $this->calcularTotalesComprobante($venta, $tasaIgv);

        // Crear comprobante en BD
        $comprobante = ComprobanteElectronico::create([
            'venta_id'              => $venta->id,
            'tipo_documento'        => $tipoDocumento,
            'serie'                 => $numeracion['serie'],
            'numero'                => $numeracion['numero'],
            'numero_completo'       => $numeracion['completo'],
            'emisor_ruc'            => $this->empresa->ruc_nit,
            'emisor_razon_social'   => $this->empresa->razon_social,
            'receptor_tipo_doc'     => $this->getReceptorTipoDoc($venta, $tipoDocumento),
            'receptor_numero_doc'   => $venta->cliente?->documento ?: '00000000',
            'receptor_razon_social' => $venta->cliente?->nombre_completo ?: 'CLIENTE GENERICO',
            'receptor_direccion'    => $venta->cliente?->direccion,
            'receptor_email'        => $venta->cliente?->email,
            'fecha_emision'         => $venta->fecha_venta->toDateString(),
            'hora_emision'          => $venta->fecha_venta->toTimeString(),
            'moneda'                => $this->empresa->codigo_moneda ?: 'PEN',
            'total_gravadas'        => $totales['base_gravada'],
            'total_exoneradas'      => $totales['base_exonerada'],
            'total_inafectas'       => $totales['base_inafecta'],
            'total_igv'             => $totales['total_igv'],
            'total_descuentos'      => $venta->descuento,
            'importe_total'         => $venta->total,
            'importe_letras'        => $this->numeroALetras($venta->total) . ' SOLES',
            'estado_sunat'          => 'pendiente',
            'user_id'               => auth()->id() ?? $venta->user_id,
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
            $see   = $this->getSee();
            $venta = $comprobante->venta;

            // Construir la factura/boleta Greenter
            $invoice = $this->buildInvoice($comprobante, $venta);

            // Generar XML firmado
            $xml = $see->getXmlSigned($invoice);
            $comprobante->hash = $invoice->getFirma() ?? '';

            // Guardar XML
            $xmlPath = $this->guardarXml($comprobante, $xml);
            $comprobante->xml_path = $xmlPath;

            // Enviar a SUNAT
            $result = $see->send($invoice);

            $comprobante->fecha_envio_sunat = now();
            $comprobante->intentos_envio++;

            if ($result->isSuccess()) {
                $cdrZip = $result->getCdrZip();
                if ($cdrZip) {
                    $cdrPath = $this->guardarCdr($comprobante, $cdrZip);
                    $comprobante->cdr_path = $cdrPath;
                }

                $cdr = $result->getCdrResponse();
                $comprobante->estado_sunat          = 'aceptado';
                $comprobante->codigo_respuesta_sunat = $cdr->getCode();
                $comprobante->mensaje_sunat          = $cdr->getDescription();
                $comprobante->qr_data                = $this->buildQrData($comprobante);
                $comprobante->save();

                return [
                    'success' => true,
                    'codigo'  => $cdr->getCode(),
                    'mensaje' => $cdr->getDescription(),
                ];
            } else {
                $error = $result->getError();
                $comprobante->estado_sunat           = 'rechazado';
                $comprobante->codigo_respuesta_sunat  = $error->getCode();
                $comprobante->mensaje_sunat           = $error->getMessage();
                $comprobante->save();

                return [
                    'success' => false,
                    'codigo'  => $error->getCode(),
                    'mensaje' => $error->getMessage(),
                ];
            }
        } catch (\Throwable $e) {
            $comprobante->estado_sunat   = 'excepcion';
            $comprobante->mensaje_sunat  = $e->getMessage();
            $comprobante->intentos_envio++;
            $comprobante->save();

            return [
                'success' => false,
                'codigo'  => 'EXC',
                'mensaje' => $e->getMessage(),
            ];
        }
    }

    /**
     * Construye la entidad Invoice (Factura/Boleta) de Greenter
     * CORREGIDO: IGV dinámico, tipo de afectación por producto, forma de pago, redondeo correcto
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

        $tasaIgv = $this->getTasaIgv();

        $invoice = (new Invoice())
            ->setUblVersion('2.1')
            ->setTipoOperacion('0101') // Venta interna
            ->setTipoDoc($comprobante->tipo_documento)
            ->setSerie($comprobante->serie)
            ->setCorrelativo($comprobante->numero)
            ->setFechaEmision(Carbon::parse($comprobante->fecha_emision)->setTimeFromTimeString($comprobante->hora_emision))
            ->setTipoMoneda($comprobante->moneda)
            ->setCompany($this->buildCompany())
            ->setClient($client);

        // ── FORMA DE PAGO (campo obligatorio UBL 2.1) ────────────────
        try {
            $formaPago = new \Greenter\Model\Sale\FormaPago\FormaPagoContado();
            $invoice->setFormaPago($formaPago);
        } catch (\Throwable $e) {
            // En versiones muy antiguas de Greenter puede no existir — continuar sin él
        }

        // ── DETALLES — con IGV dinámico y tipo de afectación correcto ──
        $details         = [];
        $sumBaseGravada  = 0;
        $sumBaseExonera  = 0;
        $sumBaseInafecta = 0;
        $sumIgv          = 0;

        foreach ($venta->detalles as $d) {
            $tipoAfec = $d->producto?->tipo_afectacion_igv ?? '10'; // 10=Gravado, 20=Exon, 30=Inafecto
            $unidad   = $this->mapUnidadMedida($d->producto?->unidad_medida ?? 'UND');
            $precio   = (float) $d->precio_unitario;
            $cantidad = (float) $d->cantidad;

            if ($tipoAfec === '10') {
                // Gravado — descontar IGV del precio (si impuesto incluido)
                $valorUnitario = round($precio / (1 + $tasaIgv), 10);
                $valorVenta    = round($valorUnitario * $cantidad, 2);
                $igvItem       = round($valorVenta * $tasaIgv, 2);
                $sumBaseGravada += $valorVenta;
                $sumIgv         += $igvItem;

                $details[] = (new SaleDetail())
                    ->setCodProducto($d->codigo ?: (string) $d->producto_id)
                    ->setUnidad($unidad)
                    ->setDescripcion($d->descripcion)
                    ->setCantidad($cantidad)
                    ->setMtoValorUnitario($valorUnitario)
                    ->setMtoValorVenta($valorVenta)
                    ->setMtoBaseIgv($valorVenta)
                    ->setPorcentajeIgv($tasaIgv * 100)
                    ->setIgv($igvItem)
                    ->setTipAfeIgv('10')
                    ->setTotalImpuestos($igvItem)
                    ->setMtoPrecioUnitario($precio);

            } elseif ($tipoAfec === '20') {
                // Exonerado — sin IGV
                $valorVenta = round($precio * $cantidad, 2);
                $sumBaseExonera += $valorVenta;

                $details[] = (new SaleDetail())
                    ->setCodProducto($d->codigo ?: (string) $d->producto_id)
                    ->setUnidad($unidad)
                    ->setDescripcion($d->descripcion)
                    ->setCantidad($cantidad)
                    ->setMtoValorUnitario($precio)
                    ->setMtoValorVenta($valorVenta)
                    ->setMtoBaseIgv($valorVenta)
                    ->setPorcentajeIgv(0)
                    ->setIgv(0)
                    ->setTipAfeIgv('20')
                    ->setTotalImpuestos(0)
                    ->setMtoPrecioUnitario($precio);

            } else {
                // Inafecto (30) — sin IGV
                $valorVenta = round($precio * $cantidad, 2);
                $sumBaseInafecta += $valorVenta;

                $details[] = (new SaleDetail())
                    ->setCodProducto($d->codigo ?: (string) $d->producto_id)
                    ->setUnidad($unidad)
                    ->setDescripcion($d->descripcion)
                    ->setCantidad($cantidad)
                    ->setMtoValorUnitario($precio)
                    ->setMtoValorVenta($valorVenta)
                    ->setMtoBaseIgv($valorVenta)
                    ->setPorcentajeIgv(0)
                    ->setIgv(0)
                    ->setTipAfeIgv('30')
                    ->setTotalImpuestos(0)
                    ->setMtoPrecioUnitario($precio);
            }
        }

        // ── TOTALES calculados desde los ítems (coherencia matemática) ──
        $invoice
            ->setMtoOperGravadas(round($sumBaseGravada, 2))
            ->setMtoOperExoneradas(round($sumBaseExonera, 2))
            ->setMtoOperInafectas(round($sumBaseInafecta, 2))
            ->setMtoIGV(round($sumIgv, 2))
            ->setTotalImpuestos(round($sumIgv, 2))
            ->setValorVenta(round($sumBaseGravada + $sumBaseExonera + $sumBaseInafecta, 2))
            ->setSubTotal(round($sumBaseGravada + $sumIgv + $sumBaseExonera + $sumBaseInafecta, 2))
            ->setMtoImpVenta((float) $comprobante->importe_total)
            ->setDetails($details);

        // ── LEYENDA (importe en letras) ──────────────────────────────
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
            'venta_id'                    => $venta->id,
            'tipo_documento'              => $tipoNota,
            'serie'                       => $numeracion['serie'],
            'numero'                      => $numeracion['numero'],
            'numero_completo'             => $numeracion['completo'],
            'emisor_ruc'                  => $this->empresa->ruc_nit,
            'emisor_razon_social'         => $this->empresa->razon_social,
            'receptor_tipo_doc'           => $comprobanteOrigen->receptor_tipo_doc,
            'receptor_numero_doc'         => $comprobanteOrigen->receptor_numero_doc,
            'receptor_razon_social'       => $comprobanteOrigen->receptor_razon_social,
            'fecha_emision'               => now()->toDateString(),
            'hora_emision'                => now()->toTimeString(),
            'moneda'                      => $comprobanteOrigen->moneda,
            'total_gravadas'              => $comprobanteOrigen->total_gravadas,
            'total_igv'                   => $comprobanteOrigen->total_igv,
            'importe_total'               => $comprobanteOrigen->importe_total,
            'importe_letras'              => $comprobanteOrigen->importe_letras,
            'doc_referencia_tipo'         => $comprobanteOrigen->tipo_documento,
            'doc_referencia_serie_numero' => $comprobanteOrigen->numero_completo,
            'codigo_motivo_nc'            => $codigoMotivo,
            'motivo_referencia'           => $motivo,
            'estado_sunat'                => 'pendiente',
            'user_id'                     => auth()->id(),
        ]);

        return $nc;
    }

    /**
     * Genera y envía el Resumen Diario de Boletas a SUNAT
     * (necesario para boletas que no se enviaron en tiempo real)
     */
    public function generarResumenDiario(Carbon $fecha): ResumenDiario
    {
        $boletas = ComprobanteElectronico::boletas()
            ->whereDate('fecha_emision', $fecha)
            ->whereIn('estado_sunat', ['pendiente', 'rechazado', 'excepcion'])
            ->get();

        if ($boletas->isEmpty()) {
            throw new \Exception("No hay boletas pendientes para la fecha {$fecha->toDateString()}");
        }

        $correlativo   = ResumenDiario::whereDate('fecha_generacion', now())->count() + 1;
        $identificador = 'RC-' . now()->format('Ymd') . '-' . str_pad($correlativo, 3, '0', STR_PAD_LEFT);

        $resumen = ResumenDiario::create([
            'identificador'        => $identificador,
            'fecha_resumen'        => $fecha->toDateString(),
            'fecha_generacion'     => now()->toDateString(),
            'cantidad_comprobantes'=> $boletas->count(),
            'total_general'        => $boletas->sum('importe_total'),
            'estado_sunat'         => 'pendiente',
            'user_id'              => auth()->id(),
        ]);

        // ── Enviar el resumen a SUNAT via Greenter ───────────────────
        try {
            $see = $this->getSee();

            $summary = new Summary();
            $summary
                ->setFechaEmision(now())
                ->setFechaReferencia($fecha)
                ->setCompany($this->buildCompany())
                ->setCorrelativo($correlativo);

            $detalles = [];
            foreach ($boletas as $b) {
                $detalles[] = (new SummaryDetail())
                    ->setTipoDoc($b->tipo_documento)
                    ->setSerie($b->serie)
                    ->setCorrelativoInicio((int) $b->numero)
                    ->setCorrelativoFin((int) $b->numero)
                    ->setTotalVenta((float) $b->importe_total)
                    ->setEstado('1'); // 1 = Adicionado
            }
            $summary->setDetails($detalles);

            // Firmar y guardar XML
            $xml = $see->getXmlSigned($summary);
            $dir = 'sunat/xml/' . date('Y/m');
            Storage::makeDirectory($dir);
            $xmlPath = "$dir/{$this->empresa->ruc_nit}-{$identificador}.xml";
            Storage::put($xmlPath, $xml);

            $result = $see->send($summary);

            if ($result->isSuccess()) {
                $cdrZip = $result->getCdrZip();
                $cdrPath = null;
                if ($cdrZip) {
                    $cdrPath = "$dir/R-{$this->empresa->ruc_nit}-{$identificador}.zip";
                    Storage::put($cdrPath, $cdrZip);
                }

                $resumen->update([
                    'estado_sunat'      => 'enviado',
                    'codigo_respuesta'  => $result->getCdrResponse() ? $result->getCdrResponse()->getCode() : null,
                    'mensaje_respuesta' => $result->getCdrResponse() ? $result->getCdrResponse()->getDescription() : 'Enviado y con Ticket',
                    'ticket_sunat'      => $result->getTicket(),
                ]);

                // Actualizar estado de las boletas a aceptado (En Resumen Diario en Perú UBL 2.1 el ticket de summary confirma la recepción)
                foreach ($boletas as $b) {
                    $b->update([
                        'estado_sunat' => 'aceptado',
                        'mensaje_sunat' => 'Aceptado mediante Resumen Diario ' . $identificador
                    ]);
                }
            } else {
                $error = $result->getError();
                $resumen->update([
                    'estado_sunat'      => 'rechazado',
                    'codigo_respuesta'  => $error->getCode(),
                    'mensaje_respuesta' => $error->getMessage(),
                ]);
            }
        } catch (\Throwable $e) {
            $resumen->update([
                'estado_sunat'      => 'pendiente', // 'error' is not in enum, 'pendiente' allows retry
                'mensaje_respuesta' => $e->getMessage(),
            ]);
        }

        return $resumen;
    }

    // ===================== Métodos Utilitarios =====================

    /** Obtiene la tasa de IGV configurada en empresa (ej: 0.18) */
    protected function getTasaIgv(): float
    {
        $tasa = (float) ($this->empresa->impuesto ?? 18);
        return $tasa > 1 ? $tasa / 100 : $tasa; // Soporta 18 o 0.18
    }

    /** Calcula correctamente las bases gravada/exonerada/inafecta e IGV */
    protected function calcularTotalesComprobante(Venta $venta, float $tasaIgv): array
    {
        $baseGravada  = 0;
        $baseExonera  = 0;
        $baseInafecta = 0;
        $totalIgv     = 0;

        foreach ($venta->detalles as $d) {
            $tipoAfec = $d->producto?->tipo_afectacion_igv ?? '10';
            $precio   = (float) $d->precio_unitario;
            $cantidad = (float) $d->cantidad;

            if ($tipoAfec === '10') {
                $base = round($precio / (1 + $tasaIgv) * $cantidad, 2);
                $igv  = round($base * $tasaIgv, 2);
                $baseGravada += $base;
                $totalIgv    += $igv;
            } elseif ($tipoAfec === '20') {
                $baseExonera += round($precio * $cantidad, 2);
            } else {
                $baseInafecta += round($precio * $cantidad, 2);
            }
        }

        return [
            'base_gravada'   => round($baseGravada, 2),
            'base_exonerada' => round($baseExonera, 2),
            'base_inafecta'  => round($baseInafecta, 2),
            'total_igv'      => round($totalIgv, 2),
        ];
    }

    protected function getReceptorTipoDoc(Venta $venta, string $tipoDoc): string
    {
        if ($tipoDoc === '01') return '6'; // Factura → RUC
        if (!$venta->cliente) return '0';
        return match(strtoupper($venta->cliente->tipo_documento)) {
            'RUC'       => '6',
            'DNI'       => '1',
            'CE'        => '4',
            'PASAPORTE' => '7',
            default     => '0',
        };
    }

    protected function getCertificadoPath(): string
    {
        $path = $this->empresa->sunat_certificado_path ?? '';
        if (!$path) return '';
        return storage_path('app/' . $path);
    }

    /** Detecta si el contenido es un archivo PFX binario */
    protected function esPfx(string $contenido): bool
    {
        // Los archivos PFX/P12 empiezan con la secuencia de bytes 0x30 0x82 (ASN.1 SEQUENCE)
        return strlen($contenido) > 4 &&
               ord($contenido[0]) === 0x30 &&
               ord($contenido[1]) === 0x82;
    }

    /** Convierte PFX a PEM en memoria */
    protected function pfxToPem(string $pfxContent, string $password): ?string
    {
        $certs = [];
        if (!openssl_pkcs12_read($pfxContent, $certs, $password)) {
            return null;
        }
        // Greenter espera: clave privada + certificado concatenados en PEM
        return ($certs['pkey'] ?? '') . ($certs['cert'] ?? '');
    }

    /** Mapea unidad de medida del sistema al código SUNAT/UBL */
    protected function mapUnidadMedida(?string $unidad): string
    {
        $mapa = [
            'UND' => 'NIU', 'UNIDAD' => 'NIU', 'UNIT' => 'NIU',
            'KG'  => 'KGM', 'KILO'   => 'KGM', 'KGM' => 'KGM',
            'LT'  => 'LTR', 'LITRO'  => 'LTR', 'LTR' => 'LTR',
            'M'   => 'MTR', 'METRO'  => 'MTR', 'MTR' => 'MTR',
            'DOC' => 'DZN', 'DOCENA' => 'DZN',
            'PAQ' => 'PK',  'PAQUE'  => 'PK',
        ];
        return $mapa[strtoupper(trim($unidad ?? 'UND'))] ?? 'NIU';
    }

    protected function guardarXml(ComprobanteElectronico $c, string $xml): string
    {
        $dir      = 'sunat/xml/' . date('Y/m');
        Storage::makeDirectory($dir);
        $filename = "{$c->emisor_ruc}-{$c->tipo_documento}-{$c->numero_completo}.xml";
        $path     = "$dir/$filename";
        Storage::put($path, $xml);
        return $path;
    }

    protected function guardarCdr(ComprobanteElectronico $c, string $cdrZip): string
    {
        $dir      = 'sunat/cdr/' . date('Y/m');
        Storage::makeDirectory($dir);
        $filename = "R-{$c->emisor_ruc}-{$c->tipo_documento}-{$c->numero_completo}.zip";
        $path     = "$dir/$filename";
        Storage::put($path, $cdrZip);
        return $path;
    }

    protected function buildQrData(ComprobanteElectronico $c): string
    {
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
        $entero    = (int) floor($numero);
        $decimales = (int) round(($numero - $entero) * 100);
        $letras    = $this->convertirEntero($entero);
        return trim($letras) . ' CON ' . str_pad((string)$decimales, 2, '0', STR_PAD_LEFT) . '/100';
    }

    protected function convertirEntero(int $n): string
    {
        if ($n === 0) return 'CERO';
        if ($n === 1) return 'UN';

        $unidades  = ['', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
        $decenas   = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
        $veintenas = ['VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        $centenas  = ['CIEN', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

        if ($n >= 1000000) {
            $millones = intdiv($n, 1000000);
            $resto    = $n % 1000000;
            $prefix   = $millones === 1 ? 'UN MILLON' : $this->convertirEntero($millones) . ' MILLONES';
            return trim($prefix . ' ' . ($resto > 0 ? $this->convertirEntero($resto) : ''));
        }

        if ($n >= 1000) {
            $miles  = intdiv($n, 1000);
            $resto  = $n % 1000;
            $prefix = $miles === 1 ? 'MIL' : $this->convertirEntero($miles) . ' MIL';
            return trim($prefix . ' ' . ($resto > 0 ? $this->convertirEntero($resto) : ''));
        }

        if ($n >= 100) {
            if ($n === 100) return 'CIEN';
            $c     = intdiv($n, 100);
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
