<?php 
require_once 'conexion.php'; 
session_start();

// Si ja t sessi activa, redirigir
if (isset($_SESSION['rol'])) {
    if ($_SESSION['rol'] === 'admin') {
        header("Location: admin_panel.php");
        exit;
    } else {
        header("Location: carta.php");
        exit;
    }
}

$error = isset($_GET['error']);
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grobe Club — Accés Clients & TPV | John's Grobe</title>
    
    <!-- Tipografies Oficials John's Grobe -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,400&family=Nunito+Sans:ital,wght@0,300;0,400;0,600;0,700;0,800;0,900;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    
    <link href="./dist/output.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        :root {
            --font-heading: 'Lora', Georgia, serif;
            --font-body: 'Nunito Sans', sans-serif;
            --color-burgundy: #D21F3C;
            --color-gold: #C6A664;
        }
        body {
            font-family: var(--font-body);
            background-color: #0b0c0e;
            background-image: 
                radial-gradient(circle at 50% 0%, rgba(210, 31, 60, 0.18) 0%, transparent 55%),
                radial-gradient(circle at 80% 80%, rgba(198, 166, 100, 0.08) 0%, transparent 40%);
            min-height: 100vh;
        }
        .font-heading {
            font-family: var(--font-heading);
        }
        .glass-card {
            background: rgba(18, 20, 24, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.6), 0 0 40px rgba(210, 31, 60, 0.1);
        }
        .btn-grobe {
            background: linear-gradient(135deg, #D21F3C 0%, #9B152A 100%);
            border: 1px solid rgba(210, 31, 60, 0.5);
            box-shadow: 0 4px 18px rgba(210, 31, 60, 0.4);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .btn-grobe:hover {
            box-shadow: 0 8px 28px rgba(210, 31, 60, 0.6);
            transform: translateY(-2px);
            border-color: #C6A664;
        }
    </style>
</head>
<body class="flex flex-col items-center justify-between p-4 md:p-8 text-slate-100">

    <!-- Enllaç Superior Tornar a la Web -->
    <header class="w-full max-w-md flex justify-between items-center mb-6">
        <a href="../index.html" class="inline-flex items-center gap-2 text-xs font-bold text-slate-400 hover:text-white bg-white/5 hover:bg-white/10 px-4 py-2 rounded-full border border-white/10 transition-all hover:border-[#D21F3C]/40">
            <i data-lucide="arrow-left" class="w-4 h-4 text-[#D21F3C]"></i>
            <span>Tornar a la Web Principal</span>
        </a>

        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#D21F3C]/10 border border-[#D21F3C]/30 text-[11px] font-bold text-[#FF7B90]">
            <span class="w-1.5 h-1.5 rounded-full bg-[#C6A664] animate-pulse"></span>
            <span>Club Fidelitat</span>
        </div>
    </header>

    <!-- Targeta Principal de Login -->
    <main class="w-full max-w-md">
        <div class="glass-card p-8 md:p-10 rounded-3xl relative overflow-hidden">
            
            <!-- Barra superior decorativa de marca -->
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-[#D21F3C] via-[#C6A664] to-[#D21F3C]"></div>

            <!-- Logo & Capçalera -->
            <div class="text-center mb-8">
                <a href="../index.html" class="inline-block group">
                    <img src="../assets/img/logo/logo-johns-grobe.png" alt="Logo John's Grobe" class="w-20 h-20 mx-auto object-contain drop-shadow-[0_4px_16px_rgba(210,31,60,0.5)] group-hover:scale-105 transition-transform duration-300">
                </a>
                
                <h1 class="text-3xl md:text-4xl font-bold tracking-tight text-white mt-3 font-heading">
                    John's Grobe
                </h1>
                
                <p class="text-xs uppercase tracking-[0.25em] text-[#C6A664] font-black mt-1">
                    Grobe Club Digital
                </p>
                <p class="text-xs text-slate-400 mt-2">
                    Acumula punts a cada comanda i bescanvia'ls per plats exclusius.
                </p>
            </div>

            <?php if ($error): ?>
                <div class="mb-6 p-3.5 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-xs flex items-center gap-2.5">
                    <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
                    <span>Email o contrasenya incorrectes. Si us plau, torna-ho a provar.</span>
                </div>
            <?php endif; ?>

            <!-- Formulari de Sessió -->
            <form action="login_proceso.php" method="POST" class="space-y-5" id="login-form">
                <div>
                    <label class="block text-[11px] font-black text-slate-300 mb-1.5 uppercase tracking-wider">
                        Correu Electrònic
                    </label>
                    <div class="relative">
                        <input type="email" id="email" name="email" required placeholder="nom@grobe.com"
                            class="w-full px-4 py-3.5 pl-11 bg-white/5 border border-white/10 rounded-xl outline-none focus:border-[#D21F3C] focus:bg-white/[0.08] focus:ring-1 focus:ring-[#D21F3C] transition-all text-white placeholder-slate-500 text-sm font-medium">
                        <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute left-4 top-4"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-black text-slate-300 mb-1.5 uppercase tracking-wider">
                        Contrasenya
                    </label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required placeholder="••••••••"
                            class="w-full px-4 py-3.5 pl-11 bg-white/5 border border-white/10 rounded-xl outline-none focus:border-[#D21F3C] focus:bg-white/[0.08] focus:ring-1 focus:ring-[#D21F3C] transition-all text-white placeholder-slate-500 text-sm font-medium">
                        <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute left-4 top-4"></i>
                    </div>
                </div>

                <button type="submit" class="btn-grobe w-full py-4 text-sm font-extrabold text-white rounded-xl uppercase tracking-wider flex items-center justify-center gap-2 mt-6">
                    <span>Entrar al Grobe Club</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </form>

            <!-- Accés Ràpid de Proves (Demo) -->
            <div class="mt-8 pt-6 border-t border-white/10">
                <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-2.5 text-center">
                    ⚡ Credencials de Prova Ràpida:
                </p>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <button type="button" onclick="autoFill('admin@grobe.com', '1234')" class="bg-white/5 hover:bg-white/10 border border-white/10 hover:border-[#D21F3C]/40 p-2.5 rounded-xl text-left transition-all group">
                        <span class="block text-[10px] text-[#FF7B90] font-black uppercase">Administrador / TPV</span>
                        <span class="text-slate-300 text-[11px] font-mono">admin@grobe.com</span>
                    </button>
                    <button type="button" onclick="autoFill('jose@grobe.com', '1234')" class="bg-white/5 hover:bg-white/10 border border-white/10 hover:border-[#C6A664]/40 p-2.5 rounded-xl text-left transition-all group">
                        <span class="block text-[10px] text-[#F8E7BE] font-black uppercase">Client (150 Punts)</span>
                        <span class="text-slate-300 text-[11px] font-mono">jose@grobe.com</span>
                    </button>
                </div>
            </div>

        </div>
    </main>

    <!-- Peu de Pàgina -->
    <footer class="w-full max-w-md text-center mt-6 text-slate-500 text-[11px]">
        <p>John's Grobe · Marina Port Premià · Salsitxers & Graella des de 2006</p>
    </footer>

    <script>
        lucide.createIcons();
        function autoFill(email, pass) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = pass;
        }
    </script>

</body>
</html>
