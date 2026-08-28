<?php

namespace App\Http\Controllers;

use App\Models\EnvaseGarantia;
use App\Models\Cliente;
use App\Models\TurnoCaja;
use App\Models\MovimientoCaja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnvaseGarantiaController extends Controller
{
    public function index(Request $request)
    {
        $estado = $request->get('estado', 'prestado');
        $query = EnvaseGarantia::with(['cliente', 'user'])->orderByDesc('fecha_prestamo');

        if ($estado && in_array($estado, ['prestado', 'devuelto'])) {
            $query->where('estado', $estado);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('cliente_nombre', 'LIKE', "%{$buscar}%")
                  ->orWhere('tipo_envase', 'LIKE', "%{$buscar}%")
                  ->orWhereHas('cliente', function($qc) use ($buscar) {
                      $qc->where('nombres', 'LIKE', "%{$buscar}%")
                         ->orWhere('documento', 'LIKE', "%{$buscar}%");
                  });
            });
        }

        $envases = $query->paginate(20)->withQueryString();
        $clientes = Cliente::where('activo', true)->orderBy('nombres')->get();

        // Resumen
        $totalPrestados = EnvaseGarantia::where('estado', 'prestado')->sum('cantidad');
        $totalGarantiaRetenida = EnvaseGarantia::where('estado', 'prestado')->sum('monto_garantia');
        $totalDevueltos = EnvaseGarantia::where('estado', 'devuelto')->sum('cantidad');

        return view('envases.index', compact('envases', 'clientes', 'totalPrestados', 'totalGarantiaRetenida', 'totalDevueltos', 'estado'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id' => 'nullable|exists:clientes,id',
            'cliente_nombre' => 'nullable|string|max:255',
            'tipo_envase' => 'required|string|max:100', // Caja Cerveza 12u, Botella 620ml, etc.
            'cantidad' => 'required|integer|min:1',
            'monto_garantia' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:255',
        ]);

        $turnoActivo = TurnoCaja::where('user_id', auth()->id())->where('estado', 'abierto')->first();

        // Si no seleccionó cliente de la lista, exigir nombre manual
        $nombreCliente = $data['cliente_nombre'];
        if ($data['cliente_id']) {
            $cliente = Cliente::find($data['cliente_id']);
            $nombreCliente = $cliente ? ($cliente->nombres . ' ' . $cliente->apellidos) : $nombreCliente;
        }

        if (empty($nombreCliente)) {
            $nombreCliente = 'Cliente Mostrador';
        }

        DB::beginTransaction();
        try {
            $envase = EnvaseGarantia::create([
                'cliente_id' => $data['cliente_id'] ?? null,
                'cliente_nombre' => $nombreCliente,
                'tipo_envase' => $data['tipo_envase'],
                'cantidad' => $data['cantidad'],
                'monto_garantia' => $data['monto_garantia'],
                'estado' => 'prestado',
                'fecha_prestamo' => now(),
                'user_id' => auth()->id(),
                'turno_caja_id' => $turnoActivo ? $turnoActivo->id : null,
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            // Si hay garantía en dinero y turno abierto, ingresar el dinero a caja
            if ($data['monto_garantia'] > 0 && $turnoActivo) {
                MovimientoCaja::create([
                    'turno_caja_id' => $turnoActivo->id,
                    'user_id' => auth()->id(),
                    'tipo' => 'ingreso',
                    'categoria' => 'garantia_envase',
                    'concepto' => 'Garantía por ' . $data['cantidad'] . ' ' . $data['tipo_envase'] . ' (' . $nombreCliente . ')',
                    'monto' => $data['monto_garantia'],
                    'observaciones' => 'Garantía de envase #' . $envase->id,
                ]);
                $turnoActivo->increment('total_efectivo', $data['monto_garantia']);
            }

            DB::commit();
            return back()->with('success', 'Préstamo de envases registrado con éxito.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al registrar: ' . $e->getMessage());
        }
    }

    public function devolver(EnvaseGarantia $envase)
    {
        if ($envase->estado === 'devuelto') {
            return back()->with('error', 'Este préstamo ya fue marcado como devuelto.');
        }

        $turnoActivo = TurnoCaja::where('user_id', auth()->id())->where('estado', 'abierto')->first();

        DB::beginTransaction();
        try {
            $envase->update([
                'estado' => 'devuelto',
                'fecha_devolucion' => now(),
            ]);

            // Si se cobró garantía, devolver el dinero de caja
            if ($envase->monto_garantia > 0 && $turnoActivo) {
                MovimientoCaja::create([
                    'turno_caja_id' => $turnoActivo->id,
                    'user_id' => auth()->id(),
                    'tipo' => 'egreso',
                    'categoria' => 'devolucion_garantia',
                    'concepto' => 'Devolución de Garantía por ' . $envase->cantidad . ' ' . $envase->tipo_envase . ' (' . $envase->cliente_nombre . ')',
                    'monto' => $envase->monto_garantia,
                    'observaciones' => 'Recepción de envases retornados #' . $envase->id,
                ]);
                $turnoActivo->decrement('total_efectivo', $envase->monto_garantia);
            }

            DB::commit();
            return back()->with('success', 'Envases recibidos y garantía devuelta al cliente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al devolver: ' . $e->getMessage());
        }
    }

    public function update(Request $request, EnvaseGarantia $envase)
    {
        $data = $request->validate([
            'cliente_id' => 'nullable|exists:clientes,id',
            'cliente_nombre' => 'nullable|string|max:255',
            'tipo_envase' => 'required|string|max:100',
            'cantidad' => 'required|integer|min:1',
            'monto_garantia' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:255',
        ]);

        $nombreCliente = $data['cliente_nombre'];
        if ($data['cliente_id']) {
            $cliente = Cliente::find($data['cliente_id']);
            $nombreCliente = $cliente ? ($cliente->nombres . ' ' . $cliente->apellidos) : $nombreCliente;
        }

        if (empty($nombreCliente)) {
            $nombreCliente = 'Cliente Mostrador';
        }

        $envase->update([
            'cliente_id' => $data['cliente_id'] ?? null,
            'cliente_nombre' => $nombreCliente,
            'tipo_envase' => $data['tipo_envase'],
            'cantidad' => $data['cantidad'],
            'monto_garantia' => $data['monto_garantia'],
            'observaciones' => $data['observaciones'] ?? null,
        ]);

        return back()->with('success', 'Registro de envase actualizado correctamente.');
    }

    public function destroy(EnvaseGarantia $envase)
    {
        $envase->delete();
        return back()->with('success', 'Registro eliminado correctamente.');
    }
}
