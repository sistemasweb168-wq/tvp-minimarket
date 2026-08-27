<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iniciar Sesión | {{ $empresaGlobal->nombre_comercial ?? 'Licorería' }}</title>
    
    @if($empresaGlobal && $empresaGlobal->logo_url)
        <link rel="icon" href="{{ $empresaGlobal->logo_url }}">
    @endif

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <style>
        * { font-family: 'Inter', sans-serif; }
        .gradient-gold { 
            background: linear-gradient(135deg, #d97706 0%, #f59e0b 50%, #fbbf24 100%); 
        }
        .btn-gold {
            background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
            box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.4);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-gold:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(245, 158, 11, 0.6);
            filter: brightness(1.1);
        }
        .glass-card {
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7), inset 0 1px 1px rgba(255, 255, 255, 0.15);
        }
        .glass-input {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            color: #ffffff !important;
            backdrop-filter: blur(8px);
            transition: all 0.2s ease;
        }
        .glass-input:focus {
            border-color: #f59e0b !important;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.25) !important;
            background: rgba(15, 23, 42, 0.85) !important;
        }
    </style>
</head>
<body class="min-h-screen text-slate-100 antialiased relative flex flex-col justify-between overflow-x-hidden bg-slate-950" 
      x-data="{ showPass: false, userVal: 'admin', passVal: 'admin123' }">

    <!-- ============================================================== -->
    <!-- 🖼️ FONDO DE PANTALLA COMPLETO CON EFECTO CINEMATOGRÁFICO        -->
    <!-- ============================================================== -->
    <div class="fixed inset-0 z-0 bg-cover bg-center bg-no-repeat transition-transform duration-1000 scale-105"
         style="background-image: url('{{ asset('images/login-bg.jpg') }}');">
    </div>
    
    <!-- Capa de oscurecimiento y gradiente para contraste perfecto -->
    <div class="fixed inset-0 z-0 bg-gradient-to-t from-slate-950/95 via-slate-950/70 to-slate-950/80 backdrop-blur-[1px]"></div>

    <!-- ============================================================== -->
    <!-- 🏷️ ESQUINA SUPERIOR: LOGO Y NOMBRE COMERCIAL                  -->
    <!-- ============================================================== -->
    <header class="relative z-10 w-full p-5 sm:p-8 flex items-center justify-between">
        <div class="flex items-center gap-3.5 bg-slate-900/60 backdrop-blur-md px-4 py-2.5 rounded-2xl border border-white/10 shadow-lg">
            @if($empresaGlobal && $empresaGlobal->logo_url)
                <img src="{{ $empresaGlobal->logo_url }}" class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl object-contain bg-black/40 p-1 border border-amber-500/30 shadow-md" alt="Logo">
            @else
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl gradient-gold flex items-center justify-center shadow-lg shadow-amber-500/20">
                    <i class="fas fa-wine-bottle text-lg sm:text-xl text-white"></i>
                </div>
            @endif
            <div>
                <h1 class="text-base sm:text-lg font-black tracking-tight text-white drop-shadow">
                    {{ $empresaGlobal->nombre_comercial ?? 'LICORERÍA VALEZKA' }}
                </h1>
                <p class="text-[10px] sm:text-xs font-bold text-amber-400 uppercase tracking-widest flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                    Sistema POS & Facturación
                </p>
            </div>
        </div>

        <div class="hidden sm:flex items-center gap-2 text-xs text-slate-300 bg-slate-900/40 backdrop-blur-md px-3.5 py-2 rounded-xl border border-white/5">
            <i class="fas fa-shield-alt text-amber-400"></i>
            <span>Acceso Seguro Encriptado</span>
        </div>
    </header>

    <!-- ============================================================== -->
    <!-- 🔐 CENTRO: FORMULARIO GLASSMORPHISM TRANSPARENTE              -->
    <!-- ============================================================== -->
    <main class="relative z-10 flex items-center justify-center p-4 sm:p-6 my-auto">
        <div class="glass-card rounded-3xl w-full max-w-md p-6 sm:p-9 relative shadow-2xl">
            
            <!-- Avatar Circular Superior Flotante -->
            <div class="w-20 h-20 -mt-16 sm:-mt-20 mx-auto rounded-full bg-slate-900 border-4 border-amber-500 shadow-2xl flex items-center justify-center text-amber-400 text-3xl shadow-amber-500/30 overflow-hidden relative group">
                <i class="fas fa-user-shield group-hover:scale-110 transition duration-300"></i>
            </div>

            <!-- Título del Login -->
            <div class="text-center mt-4 mb-6">
                <h2 class="text-xl sm:text-2xl font-black text-white uppercase tracking-widest drop-shadow-md">
                    INICIAR SESIÓN
                </h2>
                <p class="text-xs text-slate-300 mt-1">
                    Ingresa con tu cuenta asignada
                </p>
            </div>

            <!-- Alertas de Error -->
            @if($errors->any())
                <div class="mb-4 bg-red-500/20 border border-red-500/40 text-red-200 px-3.5 py-2.5 rounded-xl text-xs flex items-center gap-2.5 backdrop-blur-md">
                    <i class="fas fa-exclamation-circle text-red-400 text-sm flex-shrink-0"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-4 bg-emerald-500/20 border border-emerald-500/40 text-emerald-200 px-3.5 py-2.5 rounded-xl text-xs flex items-center gap-2.5 backdrop-blur-md">
                    <i class="fas fa-check-circle text-emerald-400 text-sm flex-shrink-0"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                @csrf

                <!-- Campo Usuario -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Usuario o Correo</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-amber-400">
                            <i class="fas fa-user text-sm"></i>
                        </div>
                        <input type="text" name="username" x-model="userVal" required autofocus
                               class="glass-input w-full pl-10 pr-4 py-3 rounded-xl text-sm font-medium placeholder-slate-400 outline-none"
                               placeholder="Ingresa tu usuario">
                    </div>
                </div>

                <!-- Campo Contraseña -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Contraseña</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-amber-400">
                            <i class="fas fa-lock text-sm"></i>
                        </div>
                        <input :type="showPass ? 'text' : 'password'" name="password" x-model="passVal" required autocomplete="current-password"
                               class="glass-input w-full pl-10 pr-11 py-3 rounded-xl text-sm font-medium placeholder-slate-400 outline-none"
                               placeholder="••••••••">
                        <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-white transition" tabindex="-1">
                            <i :class="showPass ? 'fa-eye-slash' : 'fa-eye'" class="fas text-sm"></i>
                        </button>
                    </div>
                </div>

                <!-- Recordarme -->
                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 text-slate-300 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded text-amber-500 focus:ring-amber-400 bg-slate-900 border-slate-700">
                        <span>Recordar en este dispositivo</span>
                    </label>
                </div>

                <!-- Botón de Ingreso -->
                <button type="submit" class="btn-gold w-full text-slate-950 font-black py-3.5 rounded-xl text-sm uppercase tracking-wider flex items-center justify-center gap-2 mt-2 cursor-pointer shadow-xl active:scale-95">
                    <i class="fas fa-sign-in-alt text-base"></i>
                    <span>INGRESAR AL SISTEMA</span>
                </button>
            </form>

            <!-- Accesos Rápidos de Demostración -->
            <div class="mt-6 pt-4 border-t border-white/10">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2 text-center">
                    Accesos Rápidos:
                </p>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" @click="userVal='admin'; passVal='admin123'" 
                            class="p-2 rounded-xl bg-white/5 hover:bg-amber-500/20 border border-white/10 hover:border-amber-500/40 transition text-center group cursor-pointer">
                        <i class="fas fa-user-shield text-amber-400 text-xs mb-0.5 group-hover:scale-110 transition"></i>
                        <p class="text-[11px] font-bold text-white">Admin</p>
                    </button>
                    <button type="button" @click="userVal='gerente'; passVal='gerente123'" 
                            class="p-2 rounded-xl bg-white/5 hover:bg-amber-500/20 border border-white/10 hover:border-amber-500/40 transition text-center group cursor-pointer">
                        <i class="fas fa-user-tie text-blue-400 text-xs mb-0.5 group-hover:scale-110 transition"></i>
                        <p class="text-[11px] font-bold text-white">Gerente</p>
                    </button>
                    <button type="button" @click="userVal='cajero'; passVal='cajero123'" 
                            class="p-2 rounded-xl bg-white/5 hover:bg-amber-500/20 border border-white/10 hover:border-amber-500/40 transition text-center group cursor-pointer">
                        <i class="fas fa-cash-register text-emerald-400 text-xs mb-0.5 group-hover:scale-110 transition"></i>
                        <p class="text-[11px] font-bold text-white">Cajero</p>
                    </button>
                </div>
            </div>

        </div>
    </main>

    <!-- ============================================================== -->
    <!-- 📄 FOOTER                                                      -->
    <!-- ============================================================== -->
    <footer class="relative z-10 w-full p-4 text-center text-xs text-slate-400">
        &copy; {{ date('Y') }} {{ $empresaGlobal->nombre_comercial ?? 'Licorería' }} • Todos los derechos reservados.
    </footer>

</body>
</html>
