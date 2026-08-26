<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Promocion;
use App\Models\Producto;
use App\Models\Categoria;

class PromocionController extends Controller
{
    public function index()
    {
        $promociones = Promocion::with(['producto', 'categoria'])->orderByDesc('fecha_inicio')->paginate(15);
        $productos = Producto::where('activo', true)->orderBy('nombre')->get();
        $categorias = Categoria::where('activo', true)->orderBy('nombre')->get();
        return view('promociones.index', compact('promociones', 'productos', 'categorias'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo' => 'required|in:descuento_porcentaje,descuento_fijo,2x1,3x2,precio_especial',
            'valor' => 'required|numeric|min:0',
            'producto_id' => 'nullable|exists:productos,id',
            'categoria_id' => 'nullable|exists:categorias,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'cantidad_minima' => 'nullable|integer|min:1',
        ]);
        $data['activo'] = true;
        $data['cantidad_minima'] = $data['cantidad_minima'] ?? 1;
        Promocion::create($data);
        return back()->with('success', 'Promoción creada correctamente');
    }

    public function update(Request $request, Promocion $promocion)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo' => 'required|in:descuento_porcentaje,descuento_fijo,2x1,3x2,precio_especial',
            'valor' => 'required|numeric|min:0',
            'producto_id' => 'nullable|exists:productos,id',
            'categoria_id' => 'nullable|exists:categorias,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'cantidad_minima' => 'nullable|integer|min:1',
        ]);
        $data['activo'] = $request->boolean('activo', true);
        $promocion->update($data);
        return back()->with('success', 'Promoción actualizada');
    }

    public function destroy(Promocion $promocion)
    {
        $promocion->delete();
        return back()->with('success', 'Promoción eliminada');
    }
}
