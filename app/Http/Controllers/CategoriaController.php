<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categoria;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::withCount('productos')->orderBy('nombre')->paginate(15);
        return view('categorias.index', compact('categorias'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100|unique:categorias,nombre',
            'descripcion' => 'nullable|string|max:255',
            'color' => 'required|string|max:20',
            'icono' => 'required|string|max:50',
        ]);
        $data['activo'] = true;
        Categoria::create($data);
        return redirect()->route('categorias.index')->with('success', 'Categoría creada correctamente');
    }

    public function update(Request $request, Categoria $categoria)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100|unique:categorias,nombre,' . $categoria->id,
            'descripcion' => 'nullable|string|max:255',
            'color' => 'required|string|max:20',
            'icono' => 'required|string|max:50',
        ]);
        $data['activo'] = $request->boolean('activo', true);
        $categoria->update($data);
        return redirect()->route('categorias.index')->with('success', 'Categoría actualizada correctamente');
    }

    public function destroy(Categoria $categoria)
    {
        if ($categoria->productos()->count() > 0) {
            return back()->with('error', 'No se puede eliminar: la categoría tiene productos asociados');
        }
        $categoria->delete();
        return back()->with('success', 'Categoría eliminada correctamente');
    }
}
