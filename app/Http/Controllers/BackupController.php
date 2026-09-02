<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use App\Models\Backup;

class BackupController extends Controller
{
    public function index()
    {
        $backups = Backup::with('user')->orderByDesc('created_at')->paginate(15);
        return view('backup.index', compact('backups'));
    }

    public function crear(Request $request)
    {
        try {
            $directorio = public_path('backups');
            if (!is_dir($directorio)) mkdir($directorio, 0755, true);

            $nombreArchivo = 'backup_' . date('Y-m-d_His') . '.sql';
            $rutaArchivo = $directorio . DIRECTORY_SEPARATOR . $nombreArchivo;

            $sql = $this->generarSqlBackup();
            file_put_contents($rutaArchivo, $sql);

            $backup = Backup::create([
                'nombre' => $request->nombre ?? 'Backup ' . now()->format('d/m/Y H:i'),
                'archivo' => $nombreArchivo,
                'tamano' => filesize($rutaArchivo),
                'tipo' => 'manual',
                'user_id' => auth()->id(),
                'observaciones' => $request->observaciones,
            ]);

            return back()->with('success', 'Copia de seguridad creada: ' . $nombreArchivo);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al crear backup: ' . $e->getMessage());
        }
    }

    public function descargar(Backup $backup)
    {
        $ruta = public_path('backups/' . $backup->archivo);
        if (!file_exists($ruta)) {
            return back()->with('error', 'El archivo no existe');
        }
        return response()->download($ruta);
    }

    public function restaurar(Request $request)
    {
        $request->validate(['backup_id' => 'required|exists:backups,id']);

        try {
            $backup = Backup::findOrFail($request->backup_id);
            $ruta = public_path('backups/' . $backup->archivo);

            if (!file_exists($ruta)) {
                return back()->with('error', 'Archivo de backup no encontrado');
            }

            $sql = file_get_contents($ruta);
            DB::unprepared($sql);

            return back()->with('success', 'Sistema restaurado correctamente desde el backup');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al restaurar: ' . $e->getMessage());
        }
    }

    public function restaurarArchivo(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:sql,txt',
        ]);

        try {
            $sql = file_get_contents($request->file('archivo')->getRealPath());
            DB::unprepared($sql);
            return back()->with('success', 'Sistema restaurado correctamente desde el archivo subido');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al restaurar: ' . $e->getMessage());
        }
    }

    public function eliminar(Backup $backup)
    {
        $ruta = public_path('backups/' . $backup->archivo);
        if (file_exists($ruta)) @unlink($ruta);
        $backup->delete();
        return back()->with('success', 'Backup eliminado');
    }

    public function resetear(Request $request)
    {
        $request->validate([
            'confirmacion' => 'required|in:RESETEAR',
            'password' => 'required',
        ]);

        if (!\Hash::check($request->password, auth()->user()->password)) {
            return back()->with('error', 'Contraseña incorrecta');
        }

        try {
            // Crear backup automático antes de resetear
            $directorio = public_path('backups');
            if (!is_dir($directorio)) mkdir($directorio, 0755, true);
            $nombreArchivo = 'backup_pre_reset_' . date('Y-m-d_His') . '.sql';
            file_put_contents($directorio . DIRECTORY_SEPARATOR . $nombreArchivo, $this->generarSqlBackup());

            Backup::create([
                'nombre' => 'Backup automático pre-reset',
                'archivo' => $nombreArchivo,
                'tamano' => filesize($directorio . DIRECTORY_SEPARATOR . $nombreArchivo),
                'tipo' => 'automatico',
                'user_id' => auth()->id(),
                'observaciones' => 'Generado automáticamente antes de resetear el sistema',
            ]);

            // Resetear todos los datos transaccionales, de prueba, facturación, resúmenes y envases
            $tablasResetear = [
                // 1. Facturación electrónica y comprobantes SUNAT
                'comprobantes_electronicos',
                'resumenes_diarios',
                'comunicaciones_baja',

                // 2. Ventas y POS
                'venta_detalles',
                'ventas',
                'puntos_fidelidad',
                'auditoria_cancelaciones_pos',

                // 3. Compras y proveedores
                'compra_detalles',
                'compras',
                'proveedores',

                // 4. Inventario, mermas, combos y envases
                'movimientos_inventario',
                'envases_garantias',
                'combo_productos',
                'promociones',
                'productos',
                'categorias',

                // 5. Cajas y turnos
                'movimientos_caja',
                'turnos_caja',

                // 6. Clientes
                'clientes',

                // 7. Logs y sesiones temporales
                'actividad_log',
                'password_reset_tokens',
            ];

            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            foreach ($tablasResetear as $tabla) {
                if (Schema::hasTable($tabla)) {
                    DB::table($tabla)->truncate();
                }
            }

            // Resetear correlativos de series (Boletas B001, Facturas F001, Tickets T001, Notas) a 0
            if (Schema::hasTable('series_documentos')) {
                DB::table('series_documentos')->update(['correlativo_actual' => 0]);
            }

            // Asegurar que exista Caja Principal
            if (Schema::hasTable('cajas') && DB::table('cajas')->count() === 0) {
                DB::table('cajas')->insert([
                    'nombre' => 'Caja Principal',
                    'descripcion' => 'Caja principal del negocio',
                    'activo' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Crear cliente genérico predeterminado
            if (Schema::hasTable('clientes') && DB::table('clientes')->count() === 0) {
                DB::table('clientes')->insert([
                    'codigo' => 'CLI-000001',
                    'tipo_documento' => 'DNI',
                    'documento' => '00000000',
                    'nombres' => 'Clientes',
                    'apellidos' => 'Varios',
                    'activo' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            return redirect()->route('dashboard')
                ->with('success', 'Sistema reseteado a 0 para Producción. Se conservaron intactos: Usuarios, Datos de Empresa y Logo.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al resetear: ' . $e->getMessage());
        }
    }

    private function generarSqlBackup(): string
    {
        $sql = "-- TPV Minimarket - Backup\n";
        $sql .= "-- Fecha: " . now()->format('Y-m-d H:i:s') . "\n";
        $sql .= "-- Usuario: " . auth()->user()->name . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        $tablas = DB::select('SHOW TABLES');
        $dbName = env('DB_DATABASE');
        $key = "Tables_in_$dbName";

        foreach ($tablas as $t) {
            $tabla = $t->$key;
            $sql .= "DROP TABLE IF EXISTS `$tabla`;\n";
            $createRow = DB::select("SHOW CREATE TABLE `$tabla`");
            $sql .= $createRow[0]->{'Create Table'} . ";\n\n";

            $rows = DB::table($tabla)->get();
            foreach ($rows as $row) {
                $values = [];
                foreach ((array)$row as $val) {
                    if ($val === null) {
                        $values[] = 'NULL';
                    } else {
                        $values[] = "'" . str_replace(['\\', "'"], ['\\\\', "\\'"], $val) . "'";
                    }
                }
                $sql .= "INSERT INTO `$tabla` VALUES (" . implode(',', $values) . ");\n";
            }
            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        return $sql;
    }
}
