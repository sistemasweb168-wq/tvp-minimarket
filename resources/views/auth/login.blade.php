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
        html, body { height: 100%; overflow: hidden; }
        .gradient-gold { 
            background: linear-gradient(135deg, #d97706 0%, #f59e0b 50%, #fbbf24 100%); 
        }
        .btn-gold {
            background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
            box-shadow: 0 8px 20px -4px rgba(245, 158, 11, 0.5);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-gold:hover {
            transform: translateY(-1.5px);
            box-shadow: 0 12px 25px -4px rgba(245, 158, 11, 0.7);
            filter: brightness(1.1);
        }
        /* ✨ Super Transparencia Glassmorphism Cristalina */
        .glass-card {
            background: rgba(8, 12, 24, 0.32);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7), inset 0 1px 1px rgba(255, 255, 255, 0.2);
        }
        .glass-input {
            background: rgba(0, 0, 0, 0.35) !important;
            border: 1px solid rgba(255, 255, 255, 0.18) !important;
            color: #ffffff !important;
            backdrop-filter: blur(4px);
            transition: all 0.2s ease;
        }
        .glass-input:focus {
            border-color: #f59e0b !important;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.3) !important;
            background: rgba(0, 0, 0, 0.55) !important;
        }
    </style>
</head>
<body class="h-screen w-screen text-slate-100 antialiased relative flex flex-col justify-between overflow-hidden bg-slate-950 select-none" 
      x-data="{ showPass: false, userVal: 'admin', passVal: 'admin123' }">

    <!-- ============================================================== -->
    <!-- 🖼️ FONDO DE PANTALLA COMPLETO CON MAXIMA NITIDEZ              -->
    <!-- ============================================================== -->
    <div class="fixed inset-0 z-0 bg-cover bg-center bg-no-repeat"
         style="background-image: url('{{ asset('images/login-bg.jpg') }}');">
    </div>
    
    <!-- Capa sutil de oscurecimiento para resaltar colores del bar y del oso -->
    <div class="fixed inset-0 z-0 bg-gradient-to-t from-black/75 via-black/35 to-black/60 backdrop-blur-[0.5px]"></div>

    <!-- ============================================================== -->
    <!-- 🏷️ ESQUINA SUPERIOR: LOGO Y NOMBRE COMERCIAL                  -->
    <!-- ============================================================== -->
    <header class="relative z-20 w-full p-4 sm:p-6 flex items-center justify-between">
        <div class="flex items-center gap-3 bg-black/40 backdrop-blur-md px-3.5 py-2 rounded-2xl border border-white/15 shadow-xl">
            @if($empresaGlobal && $empresaGlobal->logo_url)
                <img src="{{ $empresaGlobal->logo_url }}" class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl object-contain bg-black/50 p-1 border border-amber-500/40 shadow-md" alt="Logo">
            @else
                <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl gradient-gold flex items-center justify-center shadow-md">
                    <i class="fas fa-wine-bottle text-base sm:text-lg text-slate-950"></i>
                </div>
            @endif
            <div>
                <h1 class="text-sm sm:text-base font-black tracking-tight text-white drop-shadow">
                    {{ $empresaGlobal->nombre_comercial ?? 'LICORERÍA VALEZKA' }}
                </h1>
                <p class="text-[9px] sm:text-[11px] font-bold text-amber-400 uppercase tracking-widest flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                    Sistema POS & Facturación
                </p>
            </div>
        </div>

        <div class="hidden sm:flex items-center gap-2 text-xs text-slate-300 bg-black/40 backdrop-blur-md px-3 py-1.5 rounded-xl border border-white/10 shadow-lg">
            <i class="fas fa-shield-alt text-amber-400"></i>
            <span>Acceso Seguro Encriptado</span>
        </div>
    </header>

    <!-- ============================================================== -->
    <!-- 🔐 CENTRO: FORMULARIO GLASSMORPHISM ULTRA TRANSPARENTE        -->
    <!-- ============================================================== -->
    <main class="relative z-20 flex-1 flex items-center justify-center p-3 sm:p-4 my-auto">
        <div class="glass-card rounded-3xl w-full max-w-[390px] p-5 sm:p-7 relative shadow-2xl">
            
            <!-- Avatar Circular Superior Flotante -->
            <div class="w-16 h-16 sm:w-18 sm:h-18 -mt-13 sm:-mt-15 mx-auto rounded-full bg-black/60 backdrop-blur-md border-2 border-amber-400 shadow-2xl flex items-center justify-center text-amber-400 text-2xl shadow-amber-500/30 overflow-hidden relative group">
                <i class="fas fa-user-shield group-hover:scale-110 transition duration-300"></i>
            </div>

            <!-- Título del Login -->
            <div class="text-center mt-2 mb-4">
                <h2 class="text-lg sm:text-xl font-black text-white uppercase tracking-widest drop-shadow-md">
                    INICIAR SESIÓN
                </h2>
                <p class="text-[11px] text-slate-300 mt-0.5">
                    Ingresa con tu cuenta asignada
                </p>
            </div>

            <!-- Alertas de Error / Éxito -->
            @if($errors->any())
                <div class="mb-3 bg-red-500/30 border border-red-400/50 text-white px-3 py-2 rounded-xl text-xs flex items-center gap-2 backdrop-blur-md">
                    <i class="fas fa-exclamation-circle text-red-300 text-sm flex-shrink-0"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-3 bg-emerald-500/30 border border-emerald-400/50 text-white px-3 py-2 rounded-xl text-xs flex items-center gap-2 backdrop-blur-md">
                    <i class="fas fa-check-circle text-emerald-300 text-sm flex-shrink-0"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-3">
                @csrf

                <!-- Campo Usuario -->
                <div>
                    <label class="block text-[10px] sm:text-xs font-bold uppercase tracking-wider text-slate-200 mb-1">Usuario o Correo</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-amber-400">
                            <i class="fas fa-user text-xs"></i>
                        </div>
                        <input type="text" name="username" x-model="userVal" required autofocus
                               class="glass-input w-full pl-9 pr-3 py-2.5 rounded-xl text-xs sm:text-sm font-medium placeholder-slate-400 outline-none"
                               placeholder="Ingresa tu usuario">
                    </div>
                </div>

                <!-- Campo Contraseña -->
                <div>
                    <label class="block text-[10px] sm:text-xs font-bold uppercase tracking-wider text-slate-200 mb-1">Contraseña</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-amber-400">
                            <i class="fas fa-lock text-xs"></i>
                        </div>
                        <input :type="showPass ? 'text' : 'password'" name="password" x-model="passVal" required autocomplete="current-password"
                               class="glass-input w-full pl-9 pr-9 py-2.5 rounded-xl text-xs sm:text-sm font-medium placeholder-slate-400 outline-none"
                               placeholder="••••••••">
                        <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-white transition" tabindex="-1">
                            <i :class="showPass ? 'fa-eye-slash' : 'fa-eye'" class="fas text-xs"></i>
                        </button>
                    </div>
                </div>

                <!-- Recordarme -->
                <div class="flex items-center justify-between text-[11px] pt-0.5">
                    <label class="flex items-center gap-1.5 text-slate-300 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="w-3.5 h-3.5 rounded text-amber-500 focus:ring-amber-400 bg-black/50 border-white/20">
                        <span>Recordar en este equipo</span>
                    </label>
                </div>

                <!-- Botón de Ingreso -->
                <button type="submit" class="btn-gold w-full text-slate-950 font-black py-2.5 sm:py-3 rounded-xl text-xs sm:text-sm uppercase tracking-wider flex items-center justify-center gap-2 mt-1.5 cursor-pointer shadow-lg active:scale-95">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>INGRESAR AL SISTEMA</span>
                </button>
            </form>

            <!-- Accesos Rápidos de Demostración -->
            <div class="mt-4 pt-3 border-t border-white/15">
                <p class="text-[9px] font-bold uppercase tracking-wider text-slate-300 mb-1.5 text-center">
                    Accesos Rápidos:
                </p>
                <div class="grid grid-cols-3 gap-1.5">
                    <button type="button" @click="userVal='admin'; passVal='admin123'" 
                            class="p-1.5 rounded-xl bg-black/40 hover:bg-amber-500/25 border border-white/15 hover:border-amber-400/50 transition text-center group cursor-pointer">
                        <i class="fas fa-user-shield text-amber-400 text-xs mb-0.5 group-hover:scale-110 transition"></i>
                        <p class="text-[10px] font-bold text-white">Admin</p>
                    </button>
                    <button type="button" @click="userVal='gerente'; passVal='gerente123'" 
                            class="p-1.5 rounded-xl bg-black/40 hover:bg-amber-500/25 border border-white/15 hover:border-amber-400/50 transition text-center group cursor-pointer">
                        <i class="fas fa-user-tie text-blue-400 text-xs mb-0.5 group-hover:scale-110 transition"></i>
                        <p class="text-[10px] font-bold text-white">Gerente</p>
                    </button>
                    <button type="button" @click="userVal='cajero'; passVal='cajero123'" 
                            class="p-1.5 rounded-xl bg-black/40 hover:bg-amber-500/25 border border-white/15 hover:border-amber-400/50 transition text-center group cursor-pointer">
                        <i class="fas fa-cash-register text-emerald-400 text-xs mb-0.5 group-hover:scale-110 transition"></i>
                        <p class="text-[10px] font-bold text-white">Cajero</p>
                    </button>
                </div>
            </div>

        </div>
    </main>

    <!-- ============================================================== -->
    <!-- 📄 FOOTER                                                      -->
    <!-- ============================================================== -->
    <footer class="relative z-20 w-full p-2 text-center text-[10px] text-slate-400">
        &copy; {{ date('Y') }} {{ $empresaGlobal->nombre_comercial ?? 'Licorería' }} • Todos los derechos reservados.
    </footer>

</body>
</html>
