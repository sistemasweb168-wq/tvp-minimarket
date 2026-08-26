<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\Configuracion;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $empresa = Empresa::first() ?? new Empresa();
        $configs = Configuracion::pluck('valor', 'clave')->toArray();
        return view('configuracion.index', compact('empresa', 'configs'));
    }

    public function actualizarEmpresa(Request $request)
    {
        $data = $request->validate([
            'razon_social' => 'required|string|max:255',
            'nombre_comercial' => 'nullable|string|max:255',
            'ruc_nit' => 'nullable|string|max:30',
            'direccion' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'sitio_web' => 'nullable|string|max:255',
            'moneda' => 'required|string|max:10',
            'codigo_moneda' => 'required|string|max:5',
            'impuesto' => 'required|numeric|min:0|max:100',
            'impuesto_incluido' => 'nullable|boolean',
            'mensaje_ticket' => 'nullable|string|max:255',
            'terminos_condiciones' => 'nullable|string',
        ]);

        $data['impuesto_incluido'] = $request->boolean('impuesto_incluido');

        $empresa = Empresa::first();

        if ($request->hasFile('logo')) {
            if ($empresa && $empresa->logo && file_exists(public_path('uploads/empresa/' . $empresa->logo))) {
                @unlink(public_path('uploads/empresa/' . $empresa->logo));
            }
            $logo = $request->file('logo');
            $nombreLogo = 'logo_' . time() . '.' . $logo->getClientOriginalExtension();
            $logo->move(public_path('uploads/empresa'), $nombreLogo);
            $data['logo'] = $nombreLogo;
        }

        if ($empresa) {
            $empresa->update($data);
        } else {
            Empresa::create($data);
        }

        return back()->with('success', 'Datos de empresa actualizados correctamente');
    }

    public function actualizarConfig(Request $request)
    {
        foreach ($request->config ?? [] as $clave => $valor) {
            Configuracion::set($clave, $valor);
        }
        return back()->with('success', 'Configuración actualizada');
    }
}
