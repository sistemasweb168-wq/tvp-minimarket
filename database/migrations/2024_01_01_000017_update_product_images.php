<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $imagenes = [
            1 => 'prod_1_arroz-costeno-5kg.jpg',
            2 => 'prod_2_aceite-primor-1l.jpg',
            3 => 'prod_3_azucar-rubia-1kg.jpg',
            4 => 'prod_4_sal-yodada-1kg.jpg',
            5 => 'prod_5_fideos-don-vittorio-500g.jpg',
            6 => 'prod_6_atun-real-lata-170g.jpg',
            7 => 'prod_7_inca-kola-15l.jpg',
            8 => 'prod_8_coca-cola-15l.jpg',
            9 => 'prod_9_agua-cielo-625ml.jpg',
            10 => 'prod_10_cerveza-cristal-630ml.jpg',
            11 => 'real_11_jugo-frugos-manzana-1l.jpg',
            12 => 'real_12_leche-gloria-entera-1l.jpg',
            13 => 'real_13_yogurt-gloria-fresa-1kg.jpg',
            14 => 'real_14_queso-fresco-250g.jpg',
            15 => 'real_15_mantequilla-gloria-200g.jpg',
            16 => 'real_16_pan-frances.jpg',
            17 => 'real_17_pan-integral.jpg',
            18 => 'real_18_tostadas-bimbo.jpg',
            19 => 'real_19_manzana-roja.jpg',
            20 => 'real_20_platano-de-seda.jpg',
            21 => 'real_21_tomate.jpg',
            22 => 'real_22_cebolla.jpg',
            23 => 'real_23_limon.jpg',
            24 => 'real_24_lays-original-105g.jpg',
            25 => 'real_25_doritos-nacho-110g.jpg',
            26 => 'real_26_chocman-costa.jpg',
            27 => 'real_27_galletas-oreo.jpg',
            28 => 'real_28_detergente-ariel-850g.jpg',
            29 => 'real_29_lejia-clorox-1l.jpg',
            30 => 'real_30_jabon-bolivar-250g.jpg',
            31 => 'real_31_shampoo-hs-200ml.jpg',
            32 => 'real_32_pasta-dental-colgate.jpg',
            33 => 'real_33_papel-higienico-suave-x4.jpg',
            34 => 'real_34_jamon-ingles-200g.jpg',
            35 => 'real_35_pollo-congelado-1kg.jpg',
            36 => 'prod_36_caramelos-surtidos-x100.jpg',
            37 => 'real_37_vino-tinto-tabernero-750ml.jpg',
            38 => 'prod_38_cereal-angel-choco-500g.jpg',
            39 => 'prod_39_atun-florida-en-aceite.jpg',
            40 => 'real_40_cuaderno-cuadriculado-a4.jpg',
            41 => 'real_41_foco-led-9w.jpg',
            42 => 'real_42_pastillas-paracetamol-x10.jpg',
            43 => 'real_43_escoba-plastica.jpg',
        ];

        foreach ($imagenes as $id => $img) {
            DB::table('productos')->where('id', $id)->update(['imagen' => $img]);
        }
    }

    public function down(): void
    {
    }
};
