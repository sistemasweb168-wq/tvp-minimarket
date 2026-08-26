<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $query = Cliente::query();

        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->where(function($q) use ($b) {
                $q->where('nombres', 'LIKE', "%$b%")
                  ->orWhere('apellidos', 'LIKE', "%$b%")
                  ->orWhere('documento', 'LIKE', "%$b%")
                  ->orWhere('email', 'LIKE', "%$b%")
                  ->orWhere('telefono', 'LIKE', "%$b%");
            });
        }

        $clientes = $query->orderBy('nombres')->paginate(15);
        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        $codigo = 'CL' . str_pad(Cliente::count() + 1, 5, '0', STR_PAD_LEFT);
        return view('clientes.create', compact('codigo'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo' => 'required|string|max:30|unique:clientes,codigo',
            'tipo_documento' => 'required|string|max:20',
            'documento' => 'nullable|string|max:30',
            'nombres' => 'required|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'razon_social' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:100',
            'fecha_nacimiento' => 'nullable|date',
            'genero' => 'nullable|string|max:20',
            'credito_limite' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string',
        ]);
        $data['activo'] = true;
        Cliente::create($data);
        return redirect()->route('clientes.index')->with('success', 'Cliente creado correctamente');
    }

    public function show(Cliente $cliente)
    {
        $cliente->load(['ventas' => function($q) { $q->latest('fecha_venta')->limit(20); }]);
        return view('clientes.show', compact('cliente'));
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $data = $request->validate([
            'codigo' => 'required|string|max:30|unique:clientes,codigo,' . $cliente->id,
            'tipo_documento' => 'required|string|max:20',
            'documento' => 'nullable|string|max:30',
            'nombres' => 'required|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'razon_social' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:100',
            'fecha_nacimiento' => 'nullable|date',
            'genero' => 'nullable|string|max:20',
            'credito_limite' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string',
        ]);
        $data['activo'] = $request->boolean('activo', true);
        $cliente->update($data);
        return redirect()->route('clientes.index')->with('success', 'Cliente actualizado correctamente');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->update(['activo' => false]);
        return redirect()->route('clientes.index')->with('success', 'Cliente desactivado');
    }

    public function buscarApi(Request $request)
    {
        $b = $request->q;
        $clientes = Cliente::where('activo', true)
            ->where(function($q) use ($b) {
                $q->where('nombres', 'LIKE', "%$b%")
                  ->orWhere('apellidos', 'LIKE', "%$b%")
                  ->orWhere('documento', 'LIKE', "%$b%");
            })
            ->limit(10)
            ->get();
        return response()->json($clientes);
    }
}
