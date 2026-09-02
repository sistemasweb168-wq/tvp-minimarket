<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Caja;
use App\Models\TurnoCaja;
use App\Models\MovimientoCaja;
use App\Models\Venta;

class CajaController extends Controller
{
    public function index()
    {
        $turnoActivo = TurnoCaja::with(['caja', 'user', 'movimientos', 'ventas', 'cancelacionesPos'])
            ->where('user_id', auth()->id())
            ->where('estado', 'abierto')
            ->first();

        $cajas = Caja::where('activo', true)->get();
        $turnos = TurnoCaja::with(['caja', 'user', 'ventas'])
            ->orderByDesc('fecha_apertura')
            ->limit(15)->get();

        return view('caja.index', compact('turnoActivo', 'cajas', 'turnos'));
    }

    public function abrirTurno(Request $request)
    {
        $data = $request->validate([
            'caja_id' => 'required|exists:cajas,id',
            'monto_apertura' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string',
        ]);

        $existente = TurnoCaja::where('user_id', auth()->id())
            ->where('estado', 'abierto')->first();

        if ($existente) {
            return back()->with('error', 'Ya tiene un turno abierto');
        }

        TurnoCaja::create([
            'caja_id' => $data['caja_id'],
            'user_id' => auth()->id(),
            'fecha_apertura' => now(),
            'monto_apertura' => $data['monto_apertura'],
            'observaciones' => $data['observaciones'] ?? null,
            'estado' => 'abierto',
        ]);

        return redirect()->route('caja.index')->with('success', 'Turno de caja abierto correctamente');
    }

    public function cerrarTurno(Request $request, TurnoCaja $turno)
    {
        $data = $request->validate([
            'monto_cierre' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string',
        ]);

        $ventas = $turno->ventas ?? collect();
        $ventasEfectivoPuro = $ventas->where('forma_pago', 'efectivo')->sum('total');
        $mixtasEfectivo = 0;

        foreach ($ventas->where('forma_pago', 'mixto') as $v) {
            $dp = is_array($v->detalle_pago) ? $v->detalle_pago : (json_decode($v->detalle_pago, true) ?? []);
            $m1   = $dp['metodo_1'] ?? $dp['metodo_efectivo'] ?? 'efectivo';
            $cant1 = floatval($dp['monto_1'] ?? $dp['monto_efectivo'] ?? 0);
            $m2   = $dp['metodo_2'] ?? $dp['metodo_digital'] ?? 'yape';
            $cant2 = floatval($dp['monto_2'] ?? $dp['monto_digital'] ?? 0);

            if ($m1 === 'efectivo') $mixtasEfectivo += $cant1;
            if ($m2 === 'efectivo') $mixtasEfectivo += $cant2;
        }
        $totalEfectivoReal = $ventasEfectivoPuro + $mixtasEfectivo;

        $totalIngresos = ($turno->movimientos ?? collect())->where('tipo', 'ingreso')->sum('monto');
        $totalEgresos  = ($turno->movimientos ?? collect())->where('tipo', 'egreso')->sum('monto');

        // ── Garantías filtradas por este turno específico ────────────
        $garantiasCobradas  = 0;
        $garantiasDevueltas = 0;
        try {
            if (class_exists(\App\Models\EnvaseGarantia::class) && \Illuminate\Support\Facades\Schema::hasTable('envases_garantias')) {
                $garantiasCobradas = \App\Models\EnvaseGarantia::where('turno_caja_id', $turno->id)
                    ->where('estado', 'prestado')
                    ->sum('monto_garantia') ?? 0;

                $garantiasDevueltas = \App\Models\EnvaseGarantia::where('turno_caja_id', $turno->id)
                    ->where('estado', 'devuelto')
                    ->sum('monto_garantia') ?? 0;
            }
        } catch (\Throwable $e) {
            $garantiasCobradas  = 0;
            $garantiasDevueltas = 0;
        }

        $montoCalculado = ($turno->monto_apertura + $totalEfectivoReal + $totalIngresos + $garantiasCobradas)
                        - ($totalEgresos + $garantiasDevueltas);
        $diferencia = $data['monto_cierre'] - $montoCalculado;

        $turno->update([
            'fecha_cierre'    => now(),
            'monto_cierre'    => $data['monto_cierre'],
            'monto_calculado' => $montoCalculado,
            'diferencia'      => $diferencia,
            'observaciones'   => trim(($turno->observaciones ?? '') . "\n" . ($data['observaciones'] ?? '')),
            'estado'          => 'cerrado',
        ]);

        return redirect()->route('caja.cierre', $turno->id)
            ->with('success', 'Turno cerrado correctamente');
    }

    public function cierre(TurnoCaja $turno)
    {
        $turno->load(['caja', 'user', 'movimientos.user', 'ventas', 'cancelacionesPos.user']);
        $empresa = \App\Models\Empresa::first();
        return view('caja.cierre', compact('turno', 'empresa'));
    }

    public function ticket(TurnoCaja $turno)
    {
        $turno->load(['caja', 'user', 'movimientos.user', 'ventas']);
        $empresa = \App\Models\Empresa::first();
        return view('caja.ticket', compact('turno', 'empresa'));
    }

    public function movimiento(Request $request, TurnoCaja $turno)
    {
        $data = $request->validate([
            'tipo' => 'required|in:ingreso,egreso',
            'categoria' => 'nullable|string|max:50',
            'concepto' => 'required|string|max:255',
            'comprobante' => 'nullable|string|max:50',
            'monto' => 'required|numeric|min:0.01',
            'observaciones' => 'nullable|string',
        ]);

        $movData = [
            'turno_caja_id' => $turno->id,
            'user_id' => auth()->id(),
            'tipo' => $data['tipo'],
            'concepto' => $data['concepto'],
            'monto' => $data['monto'],
            'observaciones' => $data['observaciones'] ?? null,
        ];

        try {
            if (\Illuminate\Support\Facades\Schema::hasColumn('movimientos_caja', 'categoria')) {
                $movData['categoria'] = $data['categoria'] ?? ($data['tipo'] === 'egreso' ? 'gastos_operativos' : 'general');
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('movimientos_caja', 'comprobante')) {
                $movData['comprobante'] = $data['comprobante'] ?? null;
            }
        } catch (\Throwable $e) {
            // Seguir con los campos base si falla la verificación de esquema
        }

        MovimientoCaja::create($movData);

        return back()->with('success', 'Movimiento registrado correctamente');
    }

    public function storeCaja(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100|unique:cajas,nombre',
            'descripcion' => 'nullable|string',
        ]);
        $data['activo'] = true;
        Caja::create($data);
        return back()->with('success', 'Caja creada');
    }
}
