<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\SunatValidator;
use App\Helpers\UbigeoPeru;

class SunatApiController extends Controller
{
    /** Buscar ubigeos para autocomplete */
    public function buscarUbigeo(Request $request)
    {
        $q = $request->get('q', '');
        if (strlen($q) < 2) return response()->json([]);
        return response()->json(UbigeoPeru::search($q));
    }

    /** Validar RUC o DNI */
    public function validarDocumento(Request $request)
    {
        $doc = preg_replace('/\D/', '', (string) $request->documento);
        $tipo = SunatValidator::detectTipo($doc);

        $valido = match($tipo) {
            'RUC' => SunatValidator::isValidRuc($doc),
            'DNI' => SunatValidator::isValidDni($doc),
            default => false,
        };

        return response()->json([
            'valido' => $valido,
            'tipo' => $tipo,
            'codigo_sunat' => SunatValidator::codigoSunat($tipo),
            'documento' => $doc,
        ]);
    }

    /**
     * Consulta DNI en RENIEC o RUC en SUNAT vía API (o base de datos local)
     */
    public function consultarDocumento(Request $request)
    {
        $doc = preg_replace('/\D/', '', (string) ($request->documento ?? $request->q));
        $tipo = $request->tipo ?? SunatValidator::detectTipo($doc);

        if (!$doc) {
            return response()->json(['success' => false, 'error' => 'Documento no proporcionado'], 400);
        }

        // 1. Buscar en BD local primero
        $clienteLocal = \App\Models\Cliente::where('documento', $doc)->where('activo', true)->first();
        if ($clienteLocal) {
            return response()->json([
                'success' => true,
                'origen' => 'local',
                'cliente_id' => $clienteLocal->id,
                'tipo_documento' => $clienteLocal->tipo_documento,
                'documento' => $clienteLocal->documento,
                'nombres' => $clienteLocal->nombres,
                'apellidos' => $clienteLocal->apellidos,
                'nombre_completo' => $clienteLocal->nombre_completo,
                'razon_social' => $clienteLocal->razon_social ?: $clienteLocal->nombre_completo,
                'direccion' => $clienteLocal->direccion,
                'telefono' => $clienteLocal->telefono,
                'email' => $clienteLocal->email,
            ]);
        }

        // Validar formato
        if ($tipo === 'DNI' && strlen($doc) !== 8) {
            return response()->json(['success' => false, 'error' => 'El DNI debe tener 8 dígitos'], 422);
        }
        if ($tipo === 'RUC' && strlen($doc) !== 11) {
            return response()->json(['success' => false, 'error' => 'El RUC debe tener 11 dígitos'], 422);
        }

        $token = \App\Models\Configuracion::get('api_reniec_token') ?? config('services.apisperu.token');
        $proveedor = \App\Models\Configuracion::get('api_reniec_proveedor', 'apisperu');

        $datos = null;

        try {
            if ($token) {
                if ($tipo === 'DNI') {
                    $url = "https://dniruc.apisperu.com/api/v1/dni/{$doc}?token={$token}";
                    $response = \Illuminate\Support\Facades\Http::timeout(5)->get($url);
                    if ($response->successful()) {
                        $resData = $response->json();
                        if (!empty($resData['dni']) || !empty($resData['nombres']) || !empty($resData['data'])) {
                            $d = $resData['data'] ?? $resData;
                            $nombres = $d['nombres'] ?? '';
                            $paterno = $d['apellidoPaterno'] ?? ($d['apellido_paterno'] ?? '');
                            $materno = $d['apellidoMaterno'] ?? ($d['apellido_materno'] ?? '');
                            $apellidos = trim($paterno . ' ' . $materno);
                            $nombreCompleto = $d['nombreCompleto'] ?? ($d['nombre_completo'] ?? trim($nombres . ' ' . $apellidos));

                            $datos = [
                                'tipo_documento' => 'DNI',
                                'documento' => $doc,
                                'nombres' => $nombres ?: $nombreCompleto,
                                'apellidos' => $apellidos,
                                'nombre_completo' => $nombreCompleto,
                                'razon_social' => $nombreCompleto,
                                'direccion' => $d['direccion'] ?? null,
                            ];
                        }
                    }
                } elseif ($tipo === 'RUC') {
                    $url = "https://dniruc.apisperu.com/api/v1/ruc/{$doc}?token={$token}";
                    $response = \Illuminate\Support\Facades\Http::timeout(5)->get($url);
                    if ($response->successful()) {
                        $resData = $response->json();
                        if (!empty($resData['ruc']) || !empty($resData['razonSocial']) || !empty($resData['data'])) {
                            $d = $resData['data'] ?? $resData;
                            $razonSocial = $d['razonSocial'] ?? ($d['razon_social'] ?? ($d['nombre_o_razon_social'] ?? ''));
                            $direccion = $d['direccion'] ?? ($d['direccion_completa'] ?? '');
                            $departamento = $d['departamento'] ?? '';
                            $provincia = $d['provincia'] ?? '';
                            $distrito = $d['distrito'] ?? '';
                            $ciudad = trim("{$distrito} {$provincia} {$departamento}");

                            $datos = [
                                'tipo_documento' => 'RUC',
                                'documento' => $doc,
                                'nombres' => $razonSocial,
                                'apellidos' => '',
                                'nombre_completo' => $razonSocial,
                                'razon_social' => $razonSocial,
                                'direccion' => $direccion,
                                'ciudad' => $ciudad,
                                'estado' => $d['estado'] ?? ($d['estado_del_contribuyente'] ?? 'ACTIVO'),
                                'condicion' => $d['condicion'] ?? ($d['condicion_de_domicilio'] ?? 'HABIDO'),
                            ];
                        }
                    }
                }
            }

            // Fallback con apis.net.pe si apisperu no retornó datos
            if (!$datos) {
                if ($tipo === 'DNI') {
                    $resNet = \Illuminate\Support\Facades\Http::timeout(4)->get("https://api.apis.net.pe/v1/dni?numero={$doc}");
                    if ($resNet->successful()) {
                        $d = $resNet->json();
                        if (!empty($d['nombre'])) {
                            $nombreCompleto = $d['nombre'];
                            $nombres = $d['nombres'] ?? $nombreCompleto;
                            $apellidos = trim(($d['apellidoPaterno'] ?? '') . ' ' . ($d['apellidoMaterno'] ?? ''));
                            $datos = [
                                'tipo_documento' => 'DNI',
                                'documento' => $doc,
                                'nombres' => $nombres,
                                'apellidos' => $apellidos,
                                'nombre_completo' => $nombreCompleto,
                                'razon_social' => $nombreCompleto,
                                'direccion' => $d['direccion'] ?? null,
                            ];
                        }
                    }
                } elseif ($tipo === 'RUC') {
                    $resNet = \Illuminate\Support\Facades\Http::timeout(4)->get("https://api.apis.net.pe/v1/ruc?numero={$doc}");
                    if ($resNet->successful()) {
                        $d = $resNet->json();
                        if (!empty($d['nombre'])) {
                            $razonSocial = $d['nombre'];
                            $datos = [
                                'tipo_documento' => 'RUC',
                                'documento' => $doc,
                                'nombres' => $razonSocial,
                                'apellidos' => '',
                                'nombre_completo' => $razonSocial,
                                'razon_social' => $razonSocial,
                                'direccion' => $d['direccion'] ?? '',
                                'estado' => $d['estado'] ?? 'ACTIVO',
                                'condicion' => $d['condicion'] ?? 'HABIDO',
                            ];
                        }
                    }
                }
            }

            if ($datos) {
                // Guardar/asociar automáticamente el cliente nuevo en BD
                $nuevoCliente = \App\Models\Cliente::create([
                    'codigo' => 'CL' . str_pad(\App\Models\Cliente::count() + 1, 5, '0', STR_PAD_LEFT),
                    'tipo_documento' => $datos['tipo_documento'],
                    'documento' => $datos['documento'],
                    'nombres' => $datos['nombres'] ?: $datos['nombre_completo'],
                    'apellidos' => $datos['apellidos'] ?? '',
                    'razon_social' => $datos['razon_social'] ?? null,
                    'direccion' => $datos['direccion'] ?? null,
                    'ciudad' => $datos['ciudad'] ?? null,
                    'activo' => true,
                ]);

                $datos['cliente_id'] = $nuevoCliente->id;
                $datos['origen'] = 'api_guardado';
                $datos['success'] = true;

                return response()->json($datos);
            }

            return response()->json([
                'success' => false,
                'error' => 'No se encontraron datos para este ' . $tipo . '. Puede escribir el nombre manualmente.',
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al consultar servicio: ' . $e->getMessage() . '. Puede ingresar los datos manualmente.',
            ]);
        }
    }
}
