<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Proveedor;
use App\Models\MovimientoInventario;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::with(['categoria', 'proveedor']);

        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->where(function($q) use ($b) {
                $q->where('nombre', 'LIKE', "%$b%")
                  ->orWhere('codigo', 'LIKE', "%$b%")
                  ->orWhere('codigo_barras', 'LIKE', "%$b%");
            });
        }

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        if ($request->filled('estado')) {
            if ($request->estado === 'activo') $query->where('activo', true);
            if ($request->estado === 'inactivo') $query->where('activo', false);
            if ($request->estado === 'stock_bajo') {
                $query->where('controla_stock', true)->whereColumn('stock', '<=', 'stock_minimo');
            }
        }

        $productos = $query->orderBy('nombre')->paginate(15);
        $categorias = Categoria::where('activo', true)->orderBy('nombre')->get();

        return view('productos.index', compact('productos', 'categorias'));
    }

    public function create()
    {
        $categorias = Categoria::where('activo', true)->orderBy('nombre')->get();
        $proveedores = Proveedor::where('activo', true)->orderBy('razon_social')->get();
        $codigo = 'P' . str_pad(Producto::count() + 1, 6, '0', STR_PAD_LEFT);

        return view('productos.create', compact('categorias', 'proveedores', 'codigo'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo' => 'required|string|max:50|unique:productos,codigo',
            'codigo_barras' => 'nullable|string|max:50',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'categoria_id' => 'nullable|exists:categorias,id',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'unidad_medida' => 'required|string|max:20',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'precio_mayoreo' => 'nullable|numeric|min:0',
            'cantidad_mayoreo' => 'nullable|integer|min:0',
            'stock' => 'required|numeric|min:0',
            'stock_minimo' => 'required|numeric|min:0',
            'stock_maximo' => 'nullable|numeric|min:0',
            'controla_stock' => 'nullable|boolean',
            'aplica_impuesto' => 'nullable|boolean',
            'fecha_vencimiento' => 'nullable|date',
            'lote' => 'nullable|string|max:50',
            'ubicacion' => 'nullable|string|max:100',
            'destacado' => 'nullable|boolean',
        ]);

        $data['controla_stock'] = $request->boolean('controla_stock', true);
        $data['aplica_impuesto'] = $request->boolean('aplica_impuesto', true);
        $data['destacado'] = $request->boolean('destacado');
        $data['activo'] = true;

        if ($request->hasFile('imagen')) {
            $imagen = $request->file('imagen');
            $nombreImagen = time() . '_' . preg_replace('/[^A-Za-z0-9.\-]/', '_', $imagen->getClientOriginalName());
            $imagen->move(public_path('uploads/productos'), $nombreImagen);
            $data['imagen'] = $nombreImagen;
        }

        $producto = Producto::create($data);

        if ($producto->stock > 0) {
            MovimientoInventario::create([
                'producto_id' => $producto->id,
                'user_id' => auth()->id(),
                'tipo' => 'entrada',
                'motivo' => 'Stock inicial',
                'cantidad' => $producto->stock,
                'stock_anterior' => 0,
                'stock_nuevo' => $producto->stock,
                'fecha' => now(),
            ]);
        }

        return redirect()->route('productos.index')->with('success', 'Producto creado correctamente');
    }

    public function show(Producto $producto)
    {
        $producto->load(['categoria', 'proveedor']);
        $movimientos = MovimientoInventario::with('user')
            ->where('producto_id', $producto->id)
            ->orderByDesc('fecha')
            ->limit(20)
            ->get();

        return view('productos.show', compact('producto', 'movimientos'));
    }

    public function edit(Producto $producto)
    {
        $categorias = Categoria::where('activo', true)->orderBy('nombre')->get();
        $proveedores = Proveedor::where('activo', true)->orderBy('razon_social')->get();
        return view('productos.edit', compact('producto', 'categorias', 'proveedores'));
    }

    public function update(Request $request, Producto $producto)
    {
        $data = $request->validate([
            'codigo' => 'required|string|max:50|unique:productos,codigo,' . $producto->id,
            'codigo_barras' => 'nullable|string|max:50',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'categoria_id' => 'nullable|exists:categorias,id',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'unidad_medida' => 'required|string|max:20',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'precio_mayoreo' => 'nullable|numeric|min:0',
            'cantidad_mayoreo' => 'nullable|integer|min:0',
            'stock_minimo' => 'required|numeric|min:0',
            'stock_maximo' => 'nullable|numeric|min:0',
            'fecha_vencimiento' => 'nullable|date',
            'lote' => 'nullable|string|max:50',
            'ubicacion' => 'nullable|string|max:100',
        ]);

        $data['controla_stock'] = $request->boolean('controla_stock', true);
        $data['aplica_impuesto'] = $request->boolean('aplica_impuesto', true);
        $data['destacado'] = $request->boolean('destacado');
        $data['activo'] = $request->boolean('activo', true);

        if ($request->hasFile('imagen')) {
            if ($producto->imagen && file_exists(public_path('uploads/productos/' . $producto->imagen))) {
                @unlink(public_path('uploads/productos/' . $producto->imagen));
            }
            $imagen = $request->file('imagen');
            $nombreImagen = time() . '_' . preg_replace('/[^A-Za-z0-9.\-]/', '_', $imagen->getClientOriginalName());
            $imagen->move(public_path('uploads/productos'), $nombreImagen);
            $data['imagen'] = $nombreImagen;
        }

        $producto->update($data);
        return redirect()->route('productos.index')->with('success', 'Producto actualizado correctamente');
    }

    public function destroy(Producto $producto)
    {
        $producto->update(['activo' => false]);
        return redirect()->route('productos.index')->with('success', 'Producto desactivado correctamente');
    }

    public function ajusteStock(Request $request, Producto $producto)
    {
        $data = $request->validate([
            'tipo' => 'required|in:entrada,salida,ajuste,merma',
            'cantidad' => 'required|numeric|min:0.001',
            'motivo' => 'required|string|max:100',
            'observaciones' => 'nullable|string',
        ]);

        $stockAnterior = $producto->stock;
        $cantidad = $data['cantidad'];

        if ($data['tipo'] === 'entrada') {
            $stockNuevo = $stockAnterior + $cantidad;
        } elseif (in_array($data['tipo'], ['salida', 'merma'])) {
            $stockNuevo = max(0, $stockAnterior - $cantidad);
        } else {
            $stockNuevo = $cantidad;
        }

        $producto->update(['stock' => $stockNuevo]);

        MovimientoInventario::create([
            'producto_id' => $producto->id,
            'user_id' => auth()->id(),
            'tipo' => $data['tipo'],
            'motivo' => $data['motivo'],
            'cantidad' => $cantidad,
            'stock_anterior' => $stockAnterior,
            'stock_nuevo' => $stockNuevo,
            'observaciones' => $data['observaciones'] ?? null,
            'fecha' => now(),
        ]);

        return back()->with('success', 'Movimiento de inventario registrado');
    }

    public function buscarApi(Request $request)
    {
        $b = $request->q;
        $productos = Producto::with('categoria')
            ->where('activo', true)
            ->where(function($q) use ($b) {
                $q->where('nombre', 'LIKE', "%$b%")
                  ->orWhere('codigo', 'LIKE', "%$b%")
                  ->orWhere('codigo_barras', 'LIKE', "%$b%");
            })
            ->limit(15)
            ->get();

        return response()->json($productos);
    }
}
