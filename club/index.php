<?php require_once 'conexion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grobe Club - Login</title>
    <link href="./dist/output.css" rel="stylesheet">
</head>
<body class="bg-[#121212] min-h-screen flex items-center justify-center p-4 font-sans">

    <div class="bg-white p-10 rounded-3xl shadow-[0_0_50px_rgba(210,31,60,0.1)] max-w-md w-full text-center border-t-8 border-[#D21F3C]">
        
        <h1 class="text-5xl font-black text-slate-900 mb-2 tracking-tighter">
            Grobe<span class="text-[#D21F3C]">Club</span>
        </h1>
        <p class="text-slate-500 mb-8 font-bold text-sm tracking-widest uppercase">Acceso Exclusivo</p>

        <form action="login_proceso.php" method="POST" class="space-y-6 text-left">
            <div>
                <label class="block text-xs font-black text-slate-400 mb-2 uppercase tracking-wider">Correo Electrónico</label>
                <input type="email" name="email" required placeholder="ejemplo@grobe.com"
                    class="w-full px-4 py-4 bg-slate-50 border-2 border-slate-100 rounded-xl outline-none focus:border-[#D21F3C] focus:bg-white transition-all text-slate-800 font-medium">
            </div>

            <div>
                <label class="block text-xs font-black text-slate-400 mb-2 uppercase tracking-wider">Contraseña</label>
                <input type="password" name="password" required placeholder="••••••••"
                    class="w-full px-4 py-4 bg-slate-50 border-2 border-slate-100 rounded-xl outline-none focus:border-[#D21F3C] focus:bg-white transition-all text-slate-800 font-medium">
            </div>

            <button type="submit" class="btn-primario py-4 text-lg transform hover:-translate-y-1">
                INICIAR SESIÓN
            </button>
        </form>

    </div>

</body>
</html>