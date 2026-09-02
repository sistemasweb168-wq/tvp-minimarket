<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Proveedor;
use App\Models\MovimientoInventario;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductosTemplateExport;
use App\Imports\ProductosImport;

class ProductoController extends Controller
{
    public function descargarPlantilla()
    {
        return Excel::download(new ProductosTemplateExport, 'plantilla_productos.xlsx');
    }

    public function importarExcel(Request $request)
    {
        $request->validate([
            'archivo_excel' => 'required|file|max:10240',
        ]);

        try {
            $archivo = $request->file('archivo_excel');
            $extension = strtolower($archivo->getClientOriginalExtension());
            $contenido = file_get_contents($archivo->getRealPath());

            // Si el archivo es texto plano delimitado por tabulaciones o comas (común en exportaciones .xls de POS)
            if (str_contains($contenido, "\t") && !str_starts_with($contenido, "PK") && !str_starts_with($contenido, "\xD0\xCF\x11\xE0")) {
                // Decodificar si viene en ISO-8859-1 / Windows-1252
                if (!mb_check_encoding($contenido, 'UTF-8')) {
                    $contenido = mb_convert_encoding($contenido, 'UTF-8', 'ISO-8859-1, Windows-1252, auto');
                }

                $lines = explode("\n", str_replace("\r", "", $contenido));
                $header = null;
                $importados = 0;
                $import = new ProductosImport();

                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;
                    $cols = explode("\t", $line);

                    if (!$header) {
                        $header = array_map(fn($h) => strtolower(trim($h)), $cols);
                        continue;
                    }

                    $row = [];
                    foreach ($header as $i => $hName) {
                        $row[$hName] = $cols[$i] ?? '';
                    }

                    $res = $import->model($row);
                    if ($res) $importados++;
                }

                return redirect()->route('productos.index')->with('success', "✅ Se importaron {$importados} producto(s) correctamente.");
            }

            // Importación estándar con PhpSpreadsheet
            $import = new ProductosImport();
            $import->import($archivo);

            $msg = "✅ Se importaron {$import->importados} producto(s) correctamente.";

            if ($import->omitidos > 0) {
                $erroresStr = implode(' | ', array_slice($import->errors, 0, 5));
                $msg .= " ⚠️ {$import->omitidos} fila(s) omitidas: {$erroresStr}";
                return redirect()->route('productos.index')->with('warning', $msg);
            }

            return redirect()->route('productos.index')->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->route('productos.index')->with('error', 'Error al importar: ' . $e->getMessage());
        }
    }

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
        $productosList = Producto::where('tipo_producto', 'estandar')->where('activo', true)->orderBy('nombre')->get();
        $categorias = Categoria::where('activo', true)->orderBy('nombre')->get();
        $proveedores = Proveedor::where('activo', true)->orderBy('razon_social')->get();
        $codigo = 'P' . str_pad(Producto::count() + 1, 6, '0', STR_PAD_LEFT);

        return view('productos.create', compact('categorias', 'proveedores', 'codigo', 'productosList'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo' => 'required|string|max:50|unique:productos,codigo',
            'codigo_barras' => 'nullable|string|max:50',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo_producto' => 'required|in:estandar,paquete,combo',
            'categoria_id' => 'nullable|exists:categorias,id',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'unidad_medida' => 'required|string|max:20',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'precio_mayoreo' => 'nullable|numeric|min:0',
            'cantidad_mayoreo' => 'nullable|integer|min:0',
            'stock' => 'nullable|numeric|min:0',
            'stock_minimo' => 'required|numeric|min:0',
            'stock_maximo' => 'nullable|numeric|min:0',
            'controla_stock' => 'nullable|boolean',
            'aplica_impuesto' => 'nullable|boolean',
            'fecha_vencimiento' => 'nullable|date',
            'lote' => 'nullable|string|max:50',
            'ubicacion' => 'nullable|string|max:100',
            'destacado' => 'nullable|boolean',
        ]);

        $modoProducto = $data['tipo_producto'];
        $data['tipo_producto'] = in_array($modoProducto, ['combo', 'paquete']) ? 'combo' : 'estandar';
        $data['stock'] = $data['tipo_producto'] === 'combo' ? 0 : ($data['stock'] ?? 0);
        $data['controla_stock'] = $data['tipo_producto'] === 'combo' ? false : $request->boolean('controla_stock', true);
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

        // Si es paquete estilo Abarrotes (1 solo producto con N unidades)
        if ($modoProducto === 'paquete' && $request->filled('paquete_producto_id')) {
            $cant = floatval($request->paquete_cantidad ?? 6);
            $producto->componentesCombo()->sync([
                $request->paquete_producto_id => ['cantidad' => $cant > 0 ? $cant : 6]
            ]);
        } elseif ($modoProducto === 'combo' && $request->has('componente_id')) {
            $syncData = [];
            foreach ($request->componente_id as $index => $compId) {
                if (!empty($compId)) {
                    $cant = $request->componente_cantidad[$index] ?? 1;
                    $syncData[$compId] = ['cantidad' => $cant];
                }
            }
            $producto->componentesCombo()->sync($syncData);
        }

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
        $productosList = Producto::where('tipo_producto', 'estandar')->where('id', '!=', $producto->id)->where('activo', true)->orderBy('nombre')->get();
        $producto->load('componentesCombo');
        $categorias = Categoria::where('activo', true)->orderBy('nombre')->get();
        $proveedores = Proveedor::where('activo', true)->orderBy('razon_social')->get();
        return view('productos.edit', compact('producto', 'categorias', 'proveedores', 'productosList'));
    }

    public function update(Request $request, Producto $producto)
    {
        $data = $request->validate([
            'codigo' => 'required|string|max:50|unique:productos,codigo,' . $producto->id,
            'codigo_barras' => 'nullable|string|max:50',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo_producto' => 'required|in:estandar,paquete,combo',
            'categoria_id' => 'nullable|exists:categorias,id',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'unidad_medida' => 'required|string|max:20',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'precio_mayoreo' => 'nullable|numeric|min:0',
            'cantidad_mayoreo' => 'nullable|integer|min:0',
            'stock' => 'nullable|numeric|min:0',
            'stock_minimo' => 'required|numeric|min:0',
            'stock_maximo' => 'nullable|numeric|min:0',
            'fecha_vencimiento' => 'nullable|date',
            'lote' => 'nullable|string|max:50',
            'ubicacion' => 'nullable|string|max:100',
        ]);

        $modoProducto = $data['tipo_producto'];
        $data['tipo_producto'] = in_array($modoProducto, ['combo', 'paquete']) ? 'combo' : 'estandar';
        $data['controla_stock'] = $data['tipo_producto'] === 'combo' ? false : $request->boolean('controla_stock', true);
        $data['aplica_impuesto'] = $request->boolean('aplica_impuesto', true);
        $data['destacado'] = $request->boolean('destacado');
        $data['activo'] = $request->boolean('activo', true);

        // Si es estándar y cambió el stock manualmente
        if ($data['tipo_producto'] === 'estandar' && isset($data['stock'])) {
            $stockAnterior = $producto->stock;
            $stockNuevo = floatval($data['stock']);
            if ($stockAnterior != $stockNuevo) {
                MovimientoInventario::create([
                    'producto_id' => $producto->id,
                    'user_id' => auth()->id(),
                    'tipo' => 'ajuste',
                    'motivo' => 'Edición directa de stock',
                    'cantidad' => abs($stockNuevo - $stockAnterior),
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo' => $stockNuevo,
                    'fecha' => now(),
                ]);
            }
        } elseif ($data['tipo_producto'] === 'combo') {
            $data['stock'] = 0;
        }

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

        // Sincronizar componentes
        if ($modoProducto === 'paquete' && $request->filled('paquete_producto_id')) {
            $cant = floatval($request->paquete_cantidad ?? 6);
            $producto->componentesCombo()->sync([
                $request->paquete_producto_id => ['cantidad' => $cant > 0 ? $cant : 6]
            ]);
        } elseif ($modoProducto === 'combo' && $request->has('componente_id')) {
            $syncData = [];
            foreach ($request->componente_id as $index => $compId) {
                if (!empty($compId)) {
                    $cant = $request->componente_cantidad[$index] ?? 1;
                    $syncData[$compId] = ['cantidad' => $cant];
                }
            }
            $producto->componentesCombo()->sync($syncData);
        } else {
            $producto->componentesCombo()->sync([]);
        }

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
        $query = Producto::with(['categoria', 'componentesCombo'])->where('activo', true);

        if ($request->filled('q')) {
            $b = $request->q;
            $query->where(function($q) use ($b) {
                $q->where('nombre', 'LIKE', "%$b%")
                  ->orWhere('codigo', 'LIKE', "%$b%")
                  ->orWhere('codigo_barras', 'LIKE', "%$b%");
            });
        }

        if ($request->filled('categoria')) {
            $query->where('categoria_id', $request->categoria);
        }

        $productos = $query->limit(50)->get();
        return response()->json($productos);
    }
}
