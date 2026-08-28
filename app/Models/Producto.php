<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'tipo_producto', 'codigo', 'codigo_barras', 'nombre', 'descripcion', 'categoria_id', 'proveedor_id',
        'unidad_medida', 'precio_compra', 'precio_venta', 'precio_mayoreo', 'cantidad_mayoreo',
        'stock', 'stock_minimo', 'stock_maximo', 'controla_stock', 'aplica_impuesto',
        'imagen', 'fecha_vencimiento', 'lote', 'ubicacion', 'activo', 'destacado',
    ];

    protected $casts = [
        'precio_compra' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'precio_mayoreo' => 'decimal:2',
        'stock' => 'decimal:3',
        'stock_minimo' => 'decimal:3',
        'stock_maximo' => 'decimal:3',
        'fecha_vencimiento' => 'date',
        'controla_stock' => 'boolean',
        'aplica_impuesto' => 'boolean',
        'activo' => 'boolean',
        'destacado' => 'boolean',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function ventaDetalles()
    {
        return $this->hasMany(VentaDetalle::class);
    }

    public function compraDetalles()
    {
        return $this->hasMany(CompraDetalle::class);
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    protected $appends = ['imagen_url', 'stock_calculado'];

    public function getStockCalculadoAttribute()
    {
        if ($this->tipo_producto === 'combo') {
            $componentes = $this->relationLoaded('componentesCombo') ? $this->componentesCombo : $this->componentesCombo()->get();
            if ($componentes->isEmpty()) {
                return 0;
            }
            $maxCombos = 999999;
            foreach ($componentes as $comp) {
                $cantRequerida = floatval($comp->pivot->cantidad ?? 1);
                if ($cantRequerida > 0) {
                    $stockComp = floatval($comp->getRawOriginal('stock') ?? $comp->stock ?? 0);
                    $posibles = floor($stockComp / $cantRequerida);
                    if ($posibles < $maxCombos) {
                        $maxCombos = $posibles;
                    }
                }
            }
            return $maxCombos === 999999 ? 0 : max(0, $maxCombos);
        }
        return floatval($this->getRawOriginal('stock') ?? 0);
    }

    public function getStockAttribute($value)
    {
        if (isset($this->attributes['tipo_producto']) && $this->attributes['tipo_producto'] === 'combo') {
            return $this->stock_calculado;
        }
        return $value;
    }

    public function getImagenUrlAttribute()
    {
        if ($this->imagen) {
            if (str_starts_with($this->imagen, 'http://') || str_starts_with($this->imagen, 'https://')) {
                return $this->imagen;
            }
            if (str_starts_with($this->imagen, 'uploads/')) {
                return asset($this->imagen);
            }
            return asset('uploads/productos/' . $this->imagen);
        }
        return asset('img/producto-default.png');
    }

    public function getStockBajoAttribute()
    {
        return $this->controla_stock && $this->stock <= $this->stock_minimo;
    }

    public function getMargenAttribute()
    {
        if ($this->precio_compra == 0) return 0;
        return round((($this->precio_venta - $this->precio_compra) / $this->precio_compra) * 100, 2);
    }

    public function componentesCombo()
    {
        return $this->belongsToMany(Producto::class, 'combo_productos', 'combo_id', 'producto_id')->withPivot('cantidad');
    }
}
