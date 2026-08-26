<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iniciar Sesión | {{ $empresaGlobal->nombre_comercial ?? 'Minimarket VALEZKA' }}</title>
    
    @if($empresaGlobal && $empresaGlobal->logo_url)
        <link rel="icon" href="{{ $empresaGlobal->logo_url }}">
    @endif

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <style>
        * { font-family: 'Inter', sans-serif; }
        .gradient-primary { background: linear-gradient(135deg, #059669 0%, #10b981 100%); }
        .btn-login {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.4);
            transition: all 0.25s ease;
        }
        .btn-login:hover {
            transform: translateY(-1.5px);
            box-shadow: 0 15px 30px -5px rgba(16, 185, 129, 0.5);
            filter: brightness(1.05);
        }
        .login-cover {
            background-size: cover;
            background-position: center;
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 antialiased" x-data="{ showPass: false, userVal: 'admin', passVal: 'admin123' }">

    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-2">

        <!-- ============================================================== -->
        <!-- 🏪 MITAD IZQUIERDA: IMAGEN DEL MINIMARKET / PORTADA NEGOCIO   -->
        <!-- ============================================================== -->
        <div class="relative hidden lg:flex flex-col justify-between p-12 text-white login-cover overflow-hidden"
             style="background-image: url('{{ ($empresaGlobal && $empresaGlobal->login_imagen_url) ? $empresaGlobal->login_imagen_url : 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?q=80&w=1400&auto=format&fit=crop' }}');">
            
            <!-- Overlay degradado elegante para contraste y legibilidad -->
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/95 via-emerald-950/75 to-slate-900/60 z-0"></div>

            <!-- Cabecera de portada -->
            <div class="relative z-10 flex items-center gap-3.5">
                @if($empresaGlobal && $empresaGlobal->logo_url)
                    <img src="{{ $empresaGlobal->logo_url }}" class="w-14 h-14 rounded-2xl object-contain bg-white/95 p-2 shadow-xl backdrop-blur-sm" alt="Logo">
                @else
                    <div class="w-14 h-14 rounded-2xl gradient-primary flex items-center justify-center shadow-xl shadow-emerald-500/30">
                        <i class="fas fa-store text-2xl text-white"></i>
                    </div>
                @endif
                <div>
                    <h2 class="text-2xl font-black tracking-tight text-white drop-shadow-md">
                        {{ $empresaGlobal->nombre_comercial ?? 'Minimarket VALEZKA' }}
                    </h2>
                    <p class="text-xs font-semibold text-emerald-300 uppercase tracking-widest">
                        Punto de Venta & Facturación
                    </p>
                </div>
            </div>

            <!-- Mensaje Central y Beneficios -->
            <div class="relative z-10 my-auto py-10 max-w-lg">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-bold mb-4 backdrop-blur-md">
                    <i class="fas fa-sparkles text-amber-300"></i> Sistema POS Rápido & Seguro
                </div>
                <h1 class="text-4xl xl:text-5xl font-black text-white leading-tight mb-4 drop-shadow-lg">
                    Todo lo que tu tienda necesita en un solo lugar.
                </h1>
                <p class="text-slate-200 text-sm xl:text-base leading-relaxed mb-8 text-shadow">
                    Control de stock en tiempo real, cobros ágiles con sonido, tickets electrónicos y consulta instantánea DNI/RUC.
                </p>

                <!-- Badges de características -->
                <div class="grid grid-cols-2 gap-3.5">
                    <div class="flex items-center gap-3 bg-white/10 backdrop-blur-md p-3.5 rounded-2xl border border-white/15">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/30 flex items-center justify-center text-emerald-300">
                            <i class="fas fa-bolt text-lg"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-white">Cobro en Segundos</p>
                            <p class="text-[11px] text-slate-300">Con calculadora de cambio</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 bg-white/10 backdrop-blur-md p-3.5 rounded-2xl border border-white/15">
                        <div class="w-10 h-10 rounded-xl bg-blue-500/30 flex items-center justify-center text-blue-300">
                            <i class="fas fa-receipt text-lg"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-white">SUNAT Conectada</p>
                            <p class="text-[11px] text-slate-300">Boletas, facturas y tickets</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer de la mitad izquierda -->
            <div class="relative z-10 flex items-center justify-between text-xs text-slate-400 pt-6 border-t border-white/10">
                <span>&copy; {{ date('Y') }} {{ $empresaGlobal->nombre_comercial ?? 'Minimarket VALEZKA' }}</span>
                <span class="flex items-center gap-1.5 text-emerald-400 font-medium">
                    <i class="fas fa-circle text-[8px] animate-pulse"></i> Sistema en línea 24/7
                </span>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- 🔐 MITAD DERECHA: FORMULARIO DE INICIO DE SESIÓN              -->
        <!-- ============================================================== -->
        <div class="flex flex-col justify-between p-6 sm:p-10 lg:p-14 xl:p-20 bg-white">
            
            <!-- Cabecera Móvil (Solo visible en pantallas pequeñas) -->
            <div class="lg:hidden flex items-center gap-3 mb-6 p-4 bg-emerald-50 rounded-2xl border border-emerald-100">
                @if($empresaGlobal && $empresaGlobal->logo_url)
                    <img src="{{ $empresaGlobal->logo_url }}" class="w-12 h-12 rounded-xl object-contain bg-white p-1 shadow-sm" alt="Logo">
                @else
                    <div class="w-12 h-12 rounded-xl gradient-primary flex items-center justify-center text-white shadow-sm">
                        <i class="fas fa-store text-xl"></i>
                    </div>
                @endif
                <div>
                    <h3 class="font-extrabold text-base text-slate-800">{{ $empresaGlobal->nombre_comercial ?? 'Minimarket VALEZKA' }}</h3>
                    <p class="text-xs text-emerald-700 font-medium">Punto de Venta</p>
                </div>
            </div>

            <!-- Formulario Central -->
            <div class="w-full max-w-md mx-auto my-auto py-4">
                
                <div class="mb-8">
                    <div class="hidden lg:inline-flex items-center justify-center w-14 h-14 rounded-2xl gradient-primary text-white mb-4 shadow-lg shadow-emerald-500/20">
                        <i class="fas fa-cash-register text-2xl"></i>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mb-2">
                        Iniciar Sesión
                    </h2>
                    <p class="text-sm text-slate-500">
                        Ingresa tus credenciales para acceder al sistema POS.
                    </p>
                </div>

                @if($errors->any())
                    <div class="mb-5 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl text-xs sm:text-sm flex items-center gap-2.5 shadow-xs">
                        <i class="fas fa-exclamation-circle text-red-500 text-base flex-shrink-0"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                @if(session('success'))
                    <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-2xl text-xs sm:text-sm flex items-center gap-2.5 shadow-xs">
                        <i class="fas fa-check-circle text-emerald-500 text-base flex-shrink-0"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}" class="space-y-4 sm:space-y-5">
                    @csrf

                    <!-- Campo Usuario / Email -->
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">Usuario o Correo</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 sm:pl-4 flex items-center pointer-events-none text-slate-400">
                                <i class="fas fa-user text-sm sm:text-base"></i>
                            </div>
                            <input type="text" name="username" x-model="userVal" required autofocus
                                   class="w-full pl-10 sm:pl-12 pr-4 py-3 sm:py-3.5 border border-slate-300 rounded-2xl focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 text-sm font-medium text-slate-800 transition"
                                   placeholder="admin o correo@ejemplo.com">
                        </div>
                    </div>

                    <!-- Campo Contraseña -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs sm:text-sm font-bold text-slate-700">Contraseña</label>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 sm:pl-4 flex items-center pointer-events-none text-slate-400">
                                <i class="fas fa-lock text-sm sm:text-base"></i>
                            </div>
                            <input :type="showPass ? 'text' : 'password'" name="password" x-model="passVal" required autocomplete="current-password"
                                   class="w-full pl-10 sm:pl-12 pr-11 py-3 sm:py-3.5 border border-slate-300 rounded-2xl focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 text-sm font-medium text-slate-800 transition"
                                   placeholder="••••••••">
                            <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 sm:pr-4 flex items-center text-slate-400 hover:text-slate-600 transition" tabindex="-1">
                                <i :class="showPass ? 'fa-eye-slash' : 'fa-eye'" class="fas text-sm sm:text-base"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Recordarme -->
                    <div class="flex items-center justify-between text-xs sm:text-sm pt-1">
                        <label class="flex items-center gap-2 text-slate-600 cursor-pointer select-none">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                            <span>Mantener sesión iniciada</span>
                        </label>
                    </div>

                    <!-- Botón Enviar -->
                    <button type="submit" class="btn-login w-full text-white font-extrabold py-3.5 sm:py-4 rounded-2xl text-sm sm:text-base flex items-center justify-center gap-2 active:scale-98">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Ingresar al Sistema</span>
                    </button>
                </form>

                <!-- Accesos Rápidos de Prueba (Botones 1-clic) -->
                <div class="mt-6 pt-5 border-t border-slate-200">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2.5 text-center">
                        Accesos Rápidos de Demostración:
                    </p>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" @click="userVal='admin'; passVal='admin123'" 
                                class="p-2.5 rounded-xl border border-slate-200 hover:border-emerald-400 hover:bg-emerald-50/50 transition text-center group">
                            <i class="fas fa-user-shield text-emerald-600 text-sm mb-1 group-hover:scale-110 transition"></i>
                            <p class="text-xs font-bold text-slate-800">Admin</p>
                            <p class="text-[10px] text-slate-400">admin123</p>
                        </button>
                        <button type="button" @click="userVal='gerente'; passVal='gerente123'" 
                                class="p-2.5 rounded-xl border border-slate-200 hover:border-blue-400 hover:bg-blue-50/50 transition text-center group">
                            <i class="fas fa-user-tie text-blue-600 text-sm mb-1 group-hover:scale-110 transition"></i>
                            <p class="text-xs font-bold text-slate-800">Gerente</p>
                            <p class="text-[10px] text-slate-400">gerente123</p>
                        </button>
                        <button type="button" @click="userVal='cajero'; passVal='cajero123'" 
                                class="p-2.5 rounded-xl border border-slate-200 hover:border-amber-400 hover:bg-amber-50/50 transition text-center group">
                            <i class="fas fa-cash-register text-amber-600 text-sm mb-1 group-hover:scale-110 transition"></i>
                            <p class="text-xs font-bold text-slate-800">Cajero</p>
                            <p class="text-[10px] text-slate-400">cajero123</p>
                        </button>
                    </div>
                </div>

            </div>

            <!-- Footer del Formulario -->
            <div class="pt-6 text-center text-xs text-slate-400">
                &copy; {{ date('Y') }} {{ $empresaGlobal->nombre_comercial ?? 'Minimarket VALEZKA' }} • Todos los derechos reservados.
            </div>

        </div>

    </div>

</body>
</html>
