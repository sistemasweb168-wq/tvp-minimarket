<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\PromocionController;
use App\Http\Controllers\FacturacionElectronicaController;
use App\Http\Controllers\SunatApiController;
use App\Http\Controllers\KardexController;
use App\Http\Controllers\EnvaseGarantiaController;
use App\Http\Controllers\AuditoriaPosController;

// Autenticación
Route::get('/', function() {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    if (auth()->user()->hasRole('Cajero') || (!auth()->user()->isAdmin() && !auth()->user()->hasRole('Gerente') && auth()->user()->hasPermission('pos'))) {
        return redirect()->route('ventas.pos');
    }
    return redirect()->route('dashboard');
});

Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Comprobantes Digitales Públicos (Para clientes desde WhatsApp)
Route::get('ventas/{venta}/ticket', [VentaController::class, 'ticket'])->name('ventas.ticket');
Route::get('ventas/{venta}/ticket-pdf', [VentaController::class, 'ticketPdf'])->name('ventas.ticket-pdf');
Route::get('ventas/{venta}/pdf', [VentaController::class, 'pdf'])->name('ventas.pdf');

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Productos
    Route::get('productos/plantilla', [ProductoController::class, 'descargarPlantilla'])->name('productos.plantilla');
    Route::post('productos/importar', [ProductoController::class, 'importarExcel'])->name('productos.importar');
    Route::resource('productos', ProductoController::class);
    Route::post('productos/{producto}/ajuste-stock', [ProductoController::class, 'ajusteStock'])->name('productos.ajuste-stock');
    Route::get('api/productos/buscar', [ProductoController::class, 'buscarApi'])->name('api.productos.buscar');

    // Categorías
    Route::resource('categorias', CategoriaController::class)->except(['create', 'show', 'edit']);

    // Clientes
    Route::resource('clientes', ClienteController::class);
    Route::get('api/clientes/buscar', [ClienteController::class, 'buscarApi'])->name('api.clientes.buscar');

    // Proveedores
    Route::resource('proveedores', ProveedorController::class);

    // Ventas - POS
    Route::get('pos', [VentaController::class, 'pos'])->name('ventas.pos')->middleware('permission:pos');
    Route::resource('ventas', VentaController::class)->only(['index', 'store', 'show'])->middleware('permission:ventas');
    Route::post('ventas/{venta}/anular', [VentaController::class, 'anular'])->name('ventas.anular')->middleware('permission:ventas.anular');
    Route::post('api/pos/auditoria-cancelacion', [AuditoriaPosController::class, 'registrar'])->name('api.pos.auditoria-cancelacion');

    // Compras
    Route::resource('compras', CompraController::class)->except(['edit', 'update', 'destroy']);

    // Caja
    Route::get('caja', [CajaController::class, 'index'])->name('caja.index');
    Route::post('caja/abrir', [CajaController::class, 'abrirTurno'])->name('caja.abrir');
    Route::post('caja/turno/{turno}/cerrar', [CajaController::class, 'cerrarTurno'])->name('caja.cerrar');
    Route::get('caja/turno/{turno}/cierre', [CajaController::class, 'cierre'])->name('caja.cierre');
    Route::get('caja/turno/{turno}/ticket', [CajaController::class, 'ticket'])->name('caja.ticket');
    Route::post('caja/turno/{turno}/movimiento', [CajaController::class, 'movimiento'])->name('caja.movimiento');
    Route::post('caja/store', [CajaController::class, 'storeCaja'])->name('caja.store');

    // Reportes
    Route::get('reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('reportes/ventas', [ReporteController::class, 'ventas'])->name('reportes.ventas');
    Route::get('reportes/productos', [ReporteController::class, 'productos'])->name('reportes.productos');
    Route::get('reportes/inventario', [ReporteController::class, 'inventario'])->name('reportes.inventario');
    Route::get('reportes/vencimientos', [ReporteController::class, 'vencimientos'])->name('reportes.vencimientos');
    Route::get('reportes/utilidades', [ReporteController::class, 'utilidades'])->name('reportes.utilidades');

    // Kardex & Mermas / Roturas
    Route::get('kardex', [KardexController::class, 'index'])->name('kardex.index')->middleware('permission:productos');
    Route::post('kardex/merma', [KardexController::class, 'registrarMerma'])->name('kardex.merma')->middleware('permission:productos');

    // Control de Envases Retornables & Garantías
    Route::get('envases', [EnvaseGarantiaController::class, 'index'])->name('envases.index');
    Route::post('envases', [EnvaseGarantiaController::class, 'store'])->name('envases.store');
    Route::put('envases/{envase}', [EnvaseGarantiaController::class, 'update'])->name('envases.update');
    Route::delete('envases/{envase}', [EnvaseGarantiaController::class, 'destroy'])->name('envases.destroy');
    Route::post('envases/{envase}/devolver', [EnvaseGarantiaController::class, 'devolver'])->name('envases.devolver');

    // Promociones
    Route::resource('promociones', PromocionController::class)->only(['index', 'store', 'update', 'destroy']);

    // API SUNAT (ubigeos, validación documentos, consulta RENIEC/SUNAT)
    Route::get('api/sunat/ubigeos', [SunatApiController::class, 'buscarUbigeo'])->name('api.sunat.ubigeos');
    Route::get('api/sunat/validar', [SunatApiController::class, 'validarDocumento'])->name('api.sunat.validar');
    Route::get('api/sunat/consulta-documento', [SunatApiController::class, 'consultarDocumento'])->name('api.sunat.consulta-documento');

    // Facturación Electrónica SUNAT
    Route::prefix('facturacion')->name('facturacion.')->group(function () {
        Route::get('/', [FacturacionElectronicaController::class, 'index'])->name('index');
        Route::get('/configuracion', [FacturacionElectronicaController::class, 'configuracion'])->name('configuracion');
        Route::post('/configuracion', [FacturacionElectronicaController::class, 'guardarConfiguracion'])->name('configuracion.guardar');
        Route::post('/series', [FacturacionElectronicaController::class, 'crearSerie'])->name('series.crear');
        Route::get('/resumenes', [FacturacionElectronicaController::class, 'resumenes'])->name('resumenes');
        Route::post('/resumenes/generar', [FacturacionElectronicaController::class, 'generarResumen'])->name('resumenes.generar');
        Route::post('/venta/{venta}/emitir', [FacturacionElectronicaController::class, 'emitir'])->name('emitir');
        Route::get('/{comprobante}', [FacturacionElectronicaController::class, 'show'])->name('show');
        Route::post('/{comprobante}/enviar', [FacturacionElectronicaController::class, 'enviar'])->name('enviar');
        Route::post('/{comprobante}/anular', [FacturacionElectronicaController::class, 'anular'])->name('anular');
        Route::get('/{comprobante}/xml', [FacturacionElectronicaController::class, 'descargarXml'])->name('xml');
        Route::get('/{comprobante}/cdr', [FacturacionElectronicaController::class, 'descargarCdr'])->name('cdr');
        Route::get('/{comprobante}/pdf', [FacturacionElectronicaController::class, 'pdf'])->name('pdf');
        Route::get('/{comprobante}/ticket', [FacturacionElectronicaController::class, 'ticket'])->name('ticket');
    });

    // Solo administradores
    Route::middleware('role:Administrador')->group(function () {
        Route::get('configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');
        Route::post('configuracion/empresa', [ConfiguracionController::class, 'actualizarEmpresa'])->name('configuracion.empresa');
        Route::post('configuracion/general', [ConfiguracionController::class, 'actualizarConfig'])->name('configuracion.general');

        Route::get('backup', [BackupController::class, 'index'])->name('backup.index');
        Route::post('backup/crear', [BackupController::class, 'crear'])->name('backup.crear');
        Route::get('backup/{backup}/descargar', [BackupController::class, 'descargar'])->name('backup.descargar');
        Route::post('backup/restaurar', [BackupController::class, 'restaurar'])->name('backup.restaurar');
        Route::post('backup/restaurar-archivo', [BackupController::class, 'restaurarArchivo'])->name('backup.restaurar-archivo');
        Route::delete('backup/{backup}', [BackupController::class, 'eliminar'])->name('backup.eliminar');
        Route::post('backup/resetear', [BackupController::class, 'resetear'])->name('backup.resetear');

        Route::get('usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::post('usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::put('usuarios/{usuario}', [UsuarioController::class, 'update'])->name('usuarios.update');
        Route::delete('usuarios/{usuario}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
        Route::get('roles', [UsuarioController::class, 'roles'])->name('usuarios.roles');
        Route::post('roles', [UsuarioController::class, 'storeRol'])->name('roles.store');
        Route::put('roles/{rol}', [UsuarioController::class, 'updateRol'])->name('roles.update');
    });
});
