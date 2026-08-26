<?php

namespace App\Helpers;

/**
 * Validadores SUNAT/RENIEC para documentos peruanos.
 * Incluye verificación de dígito de control y normalización.
 */
class SunatValidator
{
    /**
     * Valida un RUC peruano (11 dígitos con dígito verificador SUNAT).
     */
    public static function isValidRuc(?string $ruc): bool
    {
        if (!$ruc) return false;
        $ruc = preg_replace('/\D/', '', $ruc);
        if (strlen($ruc) !== 11) return false;

        // RUC debe empezar con 10, 15, 17 o 20
        $prefix = substr($ruc, 0, 2);
        if (!in_array($prefix, ['10', '15', '17', '20'])) return false;

        // Algoritmo de validación SUNAT (modulo 11)
        $factores = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
        $suma = 0;
        for ($i = 0; $i < 10; $i++) {
            $suma += (int) $ruc[$i] * $factores[$i];
        }
        $resto = $suma % 11;
        $verificador = 11 - $resto;
        if ($verificador === 10) $verificador = 0;
        if ($verificador === 11) $verificador = 1;

        return $verificador === (int) $ruc[10];
    }

    /**
     * Valida un DNI peruano (8 dígitos).
     * No tiene dígito verificador estándar, pero validamos formato.
     */
    public static function isValidDni(?string $dni): bool
    {
        if (!$dni) return false;
        $dni = preg_replace('/\D/', '', $dni);
        return strlen($dni) === 8;
    }

    /**
     * Detecta automáticamente el tipo de documento según longitud.
     */
    public static function detectTipo(string $documento): ?string
    {
        $clean = preg_replace('/\D/', '', $documento);
        return match(strlen($clean)) {
            8 => 'DNI',
            11 => 'RUC',
            9, 10, 12 => 'CE',
            default => null,
        };
    }

    /**
     * Devuelve el código SUNAT del tipo de documento.
     * 0 = sin documento, 1 = DNI, 4 = CE, 6 = RUC, 7 = Pasaporte
     */
    public static function codigoSunat(?string $tipo): string
    {
        return match(strtoupper($tipo ?? '')) {
            'DNI' => '1',
            'CE' => '4',
            'RUC' => '6',
            'PASAPORTE' => '7',
            default => '0',
        };
    }

    /**
     * Tipo de operación SUNAT por defecto para ventas internas.
     */
    public static function tipoOperacionVenta(): string
    {
        return '0101'; // Venta interna
    }

    /**
     * Valida si una serie SUNAT tiene formato correcto.
     * Series: B001-B999, F001-F999, BC01-BC99, FC01-FC99, BD01-BD99, FD01-FD99
     */
    public static function isValidSerie(string $tipoDoc, string $serie): bool
    {
        $serie = strtoupper($serie);
        if (strlen($serie) !== 4) return false;

        return match($tipoDoc) {
            '01' => preg_match('/^F\d{3}$/', $serie) === 1, // Factura
            '03' => preg_match('/^B\d{3}$/', $serie) === 1, // Boleta
            '07' => preg_match('/^[BF]C\d{2}$/', $serie) === 1, // Nota crédito
            '08' => preg_match('/^[BF]D\d{2}$/', $serie) === 1, // Nota débito
            default => false,
        };
    }
}
