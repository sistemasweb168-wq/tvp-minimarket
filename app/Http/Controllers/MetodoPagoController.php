<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MetodoPago;
use Illuminate\Support\Str;

class MetodoPagoController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'              => 'required|string|max:100',
            'icono'               => 'nullable|string|max:50',
            'color'               => 'nullable|string|max:30',
            'requiere_referencia' => 'nullable|boolean',
            'permite_vueltos'     => 'nullable|boolean',
            'orden'               => 'nullable|integer',
        ]);

        $data['slug'] = Str::slug($data['nombre']);
        $data['icono'] = $data['icono'] ?? 'fa-money-bill';
        $data['color'] = $data['color'] ?? '#10b981';
        $data['requiere_referencia'] = $request->boolean('requiere_referencia');
        $data['permite_vueltos'] = $request->boolean('permite_vueltos');
        $data['activo'] = true;
        $data['orden'] = $data['orden'] ?? (MetodoPago::max('orden') + 1);

        MetodoPago::create($data);

        return back()->with('success', 'Método de pago agregado correctamente');
    }

    public function update(Request $request, MetodoPago $metodoPago)
    {
        $data = $request->validate([
            'nombre'              => 'required|string|max:100',
            'icono'               => 'nullable|string|max:50',
            'color'               => 'nullable|string|max:30',
            'requiere_referencia' => 'nullable|boolean',
            'permite_vueltos'     => 'nullable|boolean',
            'orden'               => 'nullable|integer',
            'activo'              => 'nullable|boolean',
        ]);

        $data['slug'] = Str::slug($data['nombre']);
        $data['requiere_referencia'] = $request->boolean('requiere_referencia');
        $data['permite_vueltos'] = $request->boolean('permite_vueltos');
        $data['activo'] = $request->has('activo') ? $request->boolean('activo') : $metodoPago->activo;

        $metodoPago->update($data);

        return back()->with('success', 'Método de pago actualizado correctamente');
    }

    public function toggle(MetodoPago $metodoPago)
    {
        $metodoPago->update(['activo' => !$metodoPago->activo]);
        return back()->with('success', 'Estado del método de pago actualizado');
    }

    public function destroy(MetodoPago $metodoPago)
    {
        if ($metodoPago->ventas()->count() > 0) {
            return back()->with('error', 'No se puede eliminar el método de pago porque existen ventas asociadas. Puedes desactivarlo.');
        }

        $metodoPago->delete();
        return back()->with('success', 'Método de pago eliminado correctamente');
    }
}
