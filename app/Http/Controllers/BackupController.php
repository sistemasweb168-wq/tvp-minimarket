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

            // Resetear datos transaccionales
            $tablasResetear = [
                'venta_detalles', 'ventas',
                'compra_detalles', 'compras',
                'movimientos_inventario', 'movimientos_caja',
                'turnos_caja', 'puntos_fidelidad',
                'productos', 'clientes', 'proveedores',
                'categorias', 'promociones',
            ];

            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            foreach ($tablasResetear as $tabla) {
                if (Schema::hasTable($tabla)) {
                    DB::table($tabla)->truncate();
                }
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            return redirect()->route('dashboard')
                ->with('success', 'Sistema reseteado. Se ha conservado: usuarios, configuración y empresa.');
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
