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
        $turnoActivo = TurnoCaja::with(['caja', 'user', 'movimientos'])
            ->where('user_id', auth()->id())
            ->where('estado', 'abierto')
            ->first();

        $cajas = Caja::where('activo', true)->get();
        $turnos = TurnoCaja::with(['caja', 'user'])
            ->orderByDesc('fecha_apertura')
            ->limit(10)->get();

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

        $totalIngresos = $turno->movimientos()->where('tipo', 'ingreso')->sum('monto');
        $totalEgresos = $turno->movimientos()->where('tipo', 'egreso')->sum('monto');
        $montoCalculado = $turno->monto_apertura + $turno->total_efectivo + $totalIngresos - $totalEgresos;
        $diferencia = $data['monto_cierre'] - $montoCalculado;

        $turno->update([
            'fecha_cierre' => now(),
            'monto_cierre' => $data['monto_cierre'],
            'monto_calculado' => $montoCalculado,
            'diferencia' => $diferencia,
            'observaciones' => trim(($turno->observaciones ?? '') . "\n" . ($data['observaciones'] ?? '')),
            'estado' => 'cerrado',
        ]);

        return redirect()->route('caja.cierre', $turno->id)
            ->with('success', 'Turno cerrado correctamente');
    }

    public function cierre(TurnoCaja $turno)
    {
        $turno->load(['caja', 'user', 'movimientos.user', 'ventas']);
        return view('caja.cierre', compact('turno'));
    }

    public function movimiento(Request $request, TurnoCaja $turno)
    {
        $data = $request->validate([
            'tipo' => 'required|in:ingreso,egreso',
            'concepto' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0.01',
            'observaciones' => 'nullable|string',
        ]);

        MovimientoCaja::create([
            'turno_caja_id' => $turno->id,
            'user_id' => auth()->id(),
            'tipo' => $data['tipo'],
            'concepto' => $data['concepto'],
            'monto' => $data['monto'],
            'observaciones' => $data['observaciones'] ?? null,
        ]);

        return back()->with('success', 'Movimiento registrado');
    }

    public function storeCaja(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
        ]);
        $data['activo'] = true;
        Caja::create($data);
        return back()->with('success', 'Caja creada');
    }
}
