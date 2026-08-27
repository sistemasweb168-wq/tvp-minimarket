with open('app/Http/Controllers/ProductoController.php', 'r', encoding='utf-8') as f:
    s = f.read()

# Update create()
s = s.replace("public function create()\n    {\n        $categorias = Categoria::where(", "public function create()\n    {\n        $productosList = Producto::where('tipo_producto', 'estandar')->get();\n        $categorias = Categoria::where(")
s = s.replace("compact('categorias', 'proveedores')", "compact('categorias', 'proveedores', 'productosList')")

# Update edit()
s = s.replace("public function edit(Producto $producto)\n    {\n        $categorias = Categoria::where(", "public function edit(Producto $producto)\n    {\n        $productosList = Producto::where('tipo_producto', 'estandar')->where('id', '!=', $producto->id)->get();\n        $producto->load('componentesCombo');\n        $categorias = Categoria::where(")

# Update store()
store_sync = """
        if ($request->tipo_producto === 'combo' && $request->has('componente_id')) {
            $syncData = [];
            foreach ($request->componente_id as $index => $compId) {
                $cant = $request->componente_cantidad[$index] ?? 1;
                $syncData[$compId] = ['cantidad' => $cant];
            }
            $producto->componentesCombo()->sync($syncData);
        }
"""
s = s.replace("$producto = Producto::create($data);", "$producto = Producto::create($data);" + store_sync)

# Update update()
update_sync = """
        if ($request->tipo_producto === 'combo' && $request->has('componente_id')) {
            $syncData = [];
            foreach ($request->componente_id as $index => $compId) {
                $cant = $request->componente_cantidad[$index] ?? 1;
                $syncData[$compId] = ['cantidad' => $cant];
            }
            $producto->componentesCombo()->sync($syncData);
        } else {
            $producto->componentesCombo()->sync([]);
        }
"""
s = s.replace("$producto->update($data);", "$producto->update($data);" + update_sync)

with open('app/Http/Controllers/ProductoController.php', 'w', encoding='utf-8') as f:
    f.write(s)
