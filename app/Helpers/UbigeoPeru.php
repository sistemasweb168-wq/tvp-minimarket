<?php

namespace App\Helpers;

/**
 * Catálogo simplificado de ubigeos del Perú (departamentos principales).
 * Para producción real recomendable usar una BD completa con los 1874 distritos.
 */
class UbigeoPeru
{
    /**
     * Mapa de los principales ubigeos (capital de cada departamento).
     */
    public const UBIGEOS = [
        '010101' => ['AMAZONAS', 'CHACHAPOYAS', 'CHACHAPOYAS'],
        '020101' => ['ANCASH', 'HUARAZ', 'HUARAZ'],
        '030101' => ['APURIMAC', 'ABANCAY', 'ABANCAY'],
        '040101' => ['AREQUIPA', 'AREQUIPA', 'AREQUIPA'],
        '050101' => ['AYACUCHO', 'HUAMANGA', 'AYACUCHO'],
        '060101' => ['CAJAMARCA', 'CAJAMARCA', 'CAJAMARCA'],
        '070101' => ['CALLAO', 'PROV. CONST. DEL CALLAO', 'CALLAO'],
        '080101' => ['CUSCO', 'CUSCO', 'CUSCO'],
        '090101' => ['HUANCAVELICA', 'HUANCAVELICA', 'HUANCAVELICA'],
        '100101' => ['HUANUCO', 'HUANUCO', 'HUANUCO'],
        '110101' => ['ICA', 'ICA', 'ICA'],
        '120101' => ['JUNIN', 'HUANCAYO', 'HUANCAYO'],
        '130101' => ['LA LIBERTAD', 'TRUJILLO', 'TRUJILLO'],
        '140101' => ['LAMBAYEQUE', 'CHICLAYO', 'CHICLAYO'],
        '150101' => ['LIMA', 'LIMA', 'LIMA'],
        '150102' => ['LIMA', 'LIMA', 'ANCON'],
        '150103' => ['LIMA', 'LIMA', 'ATE'],
        '150104' => ['LIMA', 'LIMA', 'BARRANCO'],
        '150105' => ['LIMA', 'LIMA', 'BREÑA'],
        '150106' => ['LIMA', 'LIMA', 'CARABAYLLO'],
        '150107' => ['LIMA', 'LIMA', 'CHACLACAYO'],
        '150108' => ['LIMA', 'LIMA', 'CHORRILLOS'],
        '150109' => ['LIMA', 'LIMA', 'CIENEGUILLA'],
        '150110' => ['LIMA', 'LIMA', 'COMAS'],
        '150111' => ['LIMA', 'LIMA', 'EL AGUSTINO'],
        '150112' => ['LIMA', 'LIMA', 'INDEPENDENCIA'],
        '150113' => ['LIMA', 'LIMA', 'JESUS MARIA'],
        '150114' => ['LIMA', 'LIMA', 'LA MOLINA'],
        '150115' => ['LIMA', 'LIMA', 'LA VICTORIA'],
        '150116' => ['LIMA', 'LIMA', 'LINCE'],
        '150117' => ['LIMA', 'LIMA', 'LOS OLIVOS'],
        '150118' => ['LIMA', 'LIMA', 'LURIGANCHO'],
        '150119' => ['LIMA', 'LIMA', 'LURIN'],
        '150120' => ['LIMA', 'LIMA', 'MAGDALENA DEL MAR'],
        '150121' => ['LIMA', 'LIMA', 'PUEBLO LIBRE'],
        '150122' => ['LIMA', 'LIMA', 'MIRAFLORES'],
        '150123' => ['LIMA', 'LIMA', 'PACHACAMAC'],
        '150124' => ['LIMA', 'LIMA', 'PUCUSANA'],
        '150125' => ['LIMA', 'LIMA', 'PUENTE PIEDRA'],
        '150126' => ['LIMA', 'LIMA', 'PUNTA HERMOSA'],
        '150127' => ['LIMA', 'LIMA', 'PUNTA NEGRA'],
        '150128' => ['LIMA', 'LIMA', 'RIMAC'],
        '150129' => ['LIMA', 'LIMA', 'SAN BARTOLO'],
        '150130' => ['LIMA', 'LIMA', 'SAN BORJA'],
        '150131' => ['LIMA', 'LIMA', 'SAN ISIDRO'],
        '150132' => ['LIMA', 'LIMA', 'SAN JUAN DE LURIGANCHO'],
        '150133' => ['LIMA', 'LIMA', 'SAN JUAN DE MIRAFLORES'],
        '150134' => ['LIMA', 'LIMA', 'SAN LUIS'],
        '150135' => ['LIMA', 'LIMA', 'SAN MARTIN DE PORRES'],
        '150136' => ['LIMA', 'LIMA', 'SAN MIGUEL'],
        '150137' => ['LIMA', 'LIMA', 'SANTA ANITA'],
        '150138' => ['LIMA', 'LIMA', 'SANTA MARIA DEL MAR'],
        '150139' => ['LIMA', 'LIMA', 'SANTA ROSA'],
        '150140' => ['LIMA', 'LIMA', 'SANTIAGO DE SURCO'],
        '150141' => ['LIMA', 'LIMA', 'SURQUILLO'],
        '150142' => ['LIMA', 'LIMA', 'VILLA EL SALVADOR'],
        '150143' => ['LIMA', 'LIMA', 'VILLA MARIA DEL TRIUNFO'],
        '160101' => ['LORETO', 'MAYNAS', 'IQUITOS'],
        '170101' => ['MADRE DE DIOS', 'TAMBOPATA', 'TAMBOPATA'],
        '180101' => ['MOQUEGUA', 'MARISCAL NIETO', 'MOQUEGUA'],
        '190101' => ['PASCO', 'PASCO', 'CHAUPIMARCA'],
        '200101' => ['PIURA', 'PIURA', 'PIURA'],
        '210101' => ['PUNO', 'PUNO', 'PUNO'],
        '220101' => ['SAN MARTIN', 'MOYOBAMBA', 'MOYOBAMBA'],
        '230101' => ['TACNA', 'TACNA', 'TACNA'],
        '240101' => ['TUMBES', 'TUMBES', 'TUMBES'],
        '250101' => ['UCAYALI', 'CORONEL PORTILLO', 'CALLERIA'],
    ];

    /** Devuelve [departamento, provincia, distrito] dado un ubigeo. */
    public static function get(string $codigo): ?array
    {
        if (!isset(self::UBIGEOS[$codigo])) return null;
        $u = self::UBIGEOS[$codigo];
        return [
            'departamento' => $u[0],
            'provincia' => $u[1],
            'distrito' => $u[2],
        ];
    }

    /** Búsqueda libre por texto (para autocompletar). */
    public static function search(string $query): array
    {
        $query = mb_strtoupper($query);
        $results = [];
        foreach (self::UBIGEOS as $codigo => [$dpto, $prov, $dist]) {
            if (str_contains($dpto, $query) || str_contains($prov, $query) || str_contains($dist, $query) || str_contains($codigo, $query)) {
                $results[] = [
                    'codigo' => $codigo,
                    'departamento' => $dpto,
                    'provincia' => $prov,
                    'distrito' => $dist,
                    'label' => "$dist, $prov, $dpto ($codigo)",
                ];
                if (count($results) >= 25) break;
            }
        }
        return $results;
    }

    /** Lista los departamentos únicos. */
    public static function departamentos(): array
    {
        $dptos = [];
        foreach (self::UBIGEOS as $u) {
            $dptos[$u[0]] = true;
        }
        return array_keys($dptos);
    }
}
