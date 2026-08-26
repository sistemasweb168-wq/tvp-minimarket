<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proveedor;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $query = Proveedor::query();
        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->where(function($q) use ($b) {
                $q->where('razon_social', 'LIKE', "%$b%")
                  ->orWhere('nombre_comercial', 'LIKE', "%$b%")
                  ->orWhere('ruc_nit', 'LIKE', "%$b%")
                  ->orWhere('contacto', 'LIKE', "%$b%");
            });
        }
        $proveedores = $query->orderBy('razon_social')->paginate(15);
        return view('proveedores.index', compact('proveedores'));
    }

    public function create()
    {
        $codigo = 'PR' . str_pad(Proveedor::count() + 1, 5, '0', STR_PAD_LEFT);
        return view('proveedores.create', compact('codigo'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo' => 'required|string|max:30|unique:proveedores,codigo',
            'razon_social' => 'required|string|max:255',
            'nombre_comercial' => 'nullable|string|max:255',
            'ruc_nit' => 'nullable|string|max:30',
            'contacto' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string',
        ]);
        $data['activo'] = true;
        Proveedor::create($data);
        return redirect()->route('proveedores.index')->with('success', 'Proveedor creado correctamente');
    }

    public function show(Proveedor $proveedor)
    {
        $proveedor->load(['compras' => fn($q) => $q->latest('fecha_compra')->limit(20)]);
        return view('proveedores.show', compact('proveedor'));
    }

    public function edit(Proveedor $proveedor)
    {
        return view('proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $data = $request->validate([
            'codigo' => 'required|string|max:30|unique:proveedores,codigo,' . $proveedor->id,
            'razon_social' => 'required|string|max:255',
            'nombre_comercial' => 'nullable|string|max:255',
            'ruc_nit' => 'nullable|string|max:30',
            'contacto' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string',
        ]);
        $data['activo'] = $request->boolean('activo', true);
        $proveedor->update($data);
        return redirect()->route('proveedores.index')->with('success', 'Proveedor actualizado');
    }

    public function destroy(Proveedor $proveedor)
    {
        $proveedor->update(['activo' => false]);
        return redirect()->route('proveedores.index')->with('success', 'Proveedor desactivado');
    }
}
