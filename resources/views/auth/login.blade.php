<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iniciar Sesión | TPV Minimarket</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body {
            background: linear-gradient(135deg, #064e3b 0%, #047857 50%, #10b981 100%);
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }
        body::before {
            content: '';
            position: absolute;
            top: -50%; left: -50%;
            width: 200%; height: 200%;
            background-image:
                radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 25%),
                radial-gradient(circle at 80% 80%, rgba(255,255,255,0.08) 0%, transparent 25%),
                radial-gradient(circle at 40% 20%, rgba(255,255,255,0.1) 0%, transparent 25%);
            animation: drift 30s linear infinite;
        }
        @keyframes drift {
            0%   { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(50px, 50px) rotate(360deg); }
        }
        .login-card {
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.97);
            box-shadow: 0 30px 70px rgba(0,0,0,0.25);
            animation: slideUp 0.6s cubic-bezier(.2,.8,.2,1);
        }
        @keyframes slideUp { from { opacity:0; transform: translateY(40px);} to { opacity:1; transform: translateY(0);} }
        .input-group input:focus { box-shadow: 0 0 0 3px rgba(16,185,129,0.15); }
        .btn-login {
            background: linear-gradient(135deg, #059669, #10b981);
            box-shadow: 0 8px 20px rgba(16,185,129,0.4);
            transition: all 0.3s;
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(16,185,129,0.5); }
        .float-product {
            position: absolute;
            opacity: 0.15;
            font-size: 80px;
            color: white;
            animation: float 8s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }
    </style>
</head>
<body class="flex items-center justify-center p-4 relative">

    <!-- Iconos flotantes decorativos -->
    <i class="fas fa-shopping-cart float-product" style="top: 10%; left: 8%; animation-delay: 0s;"></i>
    <i class="fas fa-store float-product" style="top: 70%; left: 12%; animation-delay: 1s;"></i>
    <i class="fas fa-receipt float-product" style="top: 20%; right: 10%; animation-delay: 2s;"></i>
    <i class="fas fa-barcode float-product" style="top: 65%; right: 8%; animation-delay: 3s;"></i>
    <i class="fas fa-cash-register float-product" style="top: 40%; left: 5%; animation-delay: 4s; font-size: 60px;"></i>
    <i class="fas fa-box-open float-product" style="bottom: 10%; right: 25%; animation-delay: 5s; font-size: 70px;"></i>

    <div class="login-card rounded-3xl w-full max-w-md p-8 md:p-10 relative z-10">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl mb-4 shadow-lg" style="background: linear-gradient(135deg, #059669, #10b981);">
                <i class="fas fa-store text-white text-4xl"></i>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-800 mb-1">¡Bienvenido!</h1>
            <p class="text-slate-500">Ingresa a tu sistema TPV Minimarket</p>
        </div>

        @if($errors->any())
            <div class="mb-5 bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ $errors->first() }}
            </div>
        @endif

        @if(session('success'))
            <div class="mb-5 bg-green-50 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
            @csrf
            <div class="input-group">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Usuario o Correo</label>
                <div class="relative">
                    <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="username" value="{{ old('username') }}" required autofocus
                           class="w-full pl-12 pr-4 py-3.5 border border-slate-300 rounded-xl focus:outline-none focus:border-emerald-500 transition"
                           placeholder="admin o correo@ejemplo.com">
                </div>
            </div>

            <div class="input-group" x-data="{ show: false }">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Contraseña</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="password" x-bind:type="show ? 'text' : 'password'" name="password" required autocomplete="current-password"
                           class="w-full pl-12 pr-12 py-3.5 border border-slate-300 rounded-xl focus:outline-none focus:border-emerald-500 transition"
                           placeholder="••••••••">
                    <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600" tabindex="-1">
                        <i x-bind:class="show ? 'fa-eye-slash' : 'fa-eye'" class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2 text-slate-600 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded text-emerald-600 focus:ring-emerald-500">
                    Recordarme
                </label>
            </div>

            <button type="submit" class="btn-login w-full text-white font-semibold py-3.5 rounded-xl text-base">
                <i class="fas fa-sign-in-alt mr-2"></i>Ingresar al Sistema
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-200">
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                <p class="text-xs font-semibold text-emerald-800 mb-2"><i class="fas fa-info-circle mr-1"></i> Credenciales por defecto:</p>
                <div class="text-xs text-emerald-700 space-y-1">
                    <p><strong>Usuario:</strong> admin</p>
                    <p><strong>Contraseña:</strong> admin123</p>
                </div>
            </div>
        </div>

        <p class="text-center mt-6 text-xs text-slate-400">
            &copy; {{ date('Y') }} TPV Minimarket - Sistema de Gestión
        </p>
    </div>
</body>
</html>
