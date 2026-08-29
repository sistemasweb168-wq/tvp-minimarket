<?php

namespace App\Http\Controllers;

use App\Models\AuditoriaCancelacionPos;
use App\Models\TurnoCaja;
use Illuminate\Http\Request;

class AuditoriaPosController extends Controller
{
    public function registrar(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(["error" => "No autenticado"], 401);
            }

            // Buscar turno activo del usuario o el turno activo global más reciente
            $turno = TurnoCaja::where("user_id", $user->id)
                ->where("estado", "abierto")
                ->latest()
                ->first() 
                ?? TurnoCaja::where("estado", "abierto")->latest()->first();

            $items = $request->input("items", []);
            $tipoEvento = $request->input("tipo_evento", "item_eliminado");
            $motivo = $request->input("motivo", "Cancelación manual en POS");

            // Si se envió un solo ítem directamente en el payload
            if (empty($items) && $request->filled("producto_nombre")) {
                $items = [[
                    "producto_id" => $request->input("producto_id"),
                    "producto_nombre" => $request->input("producto_nombre"),
                    "cantidad" => $request->input("cantidad", 1),
                    "precio_unitario" => $request->input("precio_unitario", 0),
                    "total_afectado" => $request->input("total_afectado", 0),
                ]];
            }

            $guardados = [];
            foreach ($items as $item) {
                $cant = floatval($item["cantidad"] ?? 1);
                $precio = floatval($item["precio_unitario"] ?? 0);
                $total = floatval($item["total_afectado"] ?? ($cant * $precio));

                $guardados[] = AuditoriaCancelacionPos::create([
                    "user_id" => $user->id,
                    "turno_caja_id" => $turno?->id,
                    "producto_id" => $item["producto_id"] ?? null,
                    "producto_nombre" => $item["producto_nombre"] ?? "Producto sin nombre",
                    "tipo_evento" => $tipoEvento,
                    "cantidad" => $cant,
                    "precio_unitario" => $precio,
                    "total_afectado" => $total,
                    "motivo" => $motivo,
                ]);
            }

            return response()->json([
                "success" => true,
                "registrados" => count($guardados),
            ]);
        } catch (\Throwable $e) {
            \Log::error("Error registrando auditoria POS: " . $e->getMessage());
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }
}

