<?php
// Variables por defecto para evitar errores
 = isset($es_admin) && $es_admin;
$nombre_user = $nombre_user ?? ($es_admin ? 'Admin' : 'Cliente');
?>
<header class="p-3 md:p-4 border-b border-white/10 bg-[#121316]/95 backdrop-blur-md sticky top-0 z-40 flex justify-between items-center shadow-2xl w-full">
    <div class="flex items-center gap-3 md:gap-4 truncate">
        <!-- Logo y Nombre John's Grobe -->
        <a href="../index.html" class="flex items-center gap-2.5 group transition-transform hover:scale-[1.02]">
            <img src="../assets/img/logo/logo-johns-grobe.png" alt="Logo John's Grobe" class="w-8 h-8 md:w-9 md:h-9 object-contain drop-shadow-[0_2px_8px_rgba(210,31,60,0.4)]">
            <div class="leading-tight">
                <span class="text-lg md:text-xl font-bold tracking-tight text-white block group-hover:text-[#F8E7BE] transition-colors" style="font-family: 'Lora', serif;">John's Grobe</span>
                <span class="text-[9px] uppercase tracking-widest text-[#D21F3C] font-black block">Grobe Club</span>
            </div>
        </a>

        <!-- Botón Volver a Web Principal -->
        <a href="../index.html" class="hidden sm:inline-flex items-center gap-1.5 text-[11px] font-bold text-slate-300 hover:text-white bg-white/5 hover:bg-white/10 px-3 py-1.5 rounded-full border border-white/10 transition-all hover:border-[#D21F3C]/40">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5 text-[#D21F3C]"></i>
            <span>Tornar a la Web</span>
        </a>

        <?php if($es_admin): ?>
            <span class="hidden md:inline-flex items-center gap-1.5 text-[11px] bg-[#D21F3C]/15 text-[#FF7B90] border border-[#D21F3C]/30 px-2.5 py-1 rounded-full font-bold">
                <i data-lucide="shield" class="w-3 h-3"></i> TPV / Admin
            </span>
        <?php endif; ?>
    </div>
    
    <div class="flex items-center gap-2.5 md:gap-4">
        <?php if(!$es_admin): ?>
            <div class="text-right hidden sm:block">
                <p class="text-[10px] md:text-xs text-slate-400">Hola, <span class="font-bold text-white"><?php echo htmlspecialchars($nombre_user); ?></span></p>
                <a href="logout.php" class="text-[#D21F3C] text-[10px] md:text-xs font-bold hover:underline">Tancar Sessió</a>
            </div>

            <!-- Botón Ver Mi QR -->
            <button onclick="abrirModalQR()" title="Mostrar el meu codi QR" class="bg-gradient-to-br from-white to-slate-200 p-1.5 rounded-xl shadow-lg hover:scale-105 transition-all border-2 border-[#D21F3C] cursor-pointer group flex-shrink-0">
                <img src="<?php echo $url_qr_api ?? ''; ?>" alt="El meu QR" class="w-8 h-8 md:w-9 md:h-9 object-cover rounded-md">
            </button>

            <!-- Contador de Puntos con Estilo Dorado Premium -->
            <div class="bg-gradient-to-br from-[#1a1c22] to-[#121316] px-3 md:px-4 py-1.5 rounded-xl border border-[#C6A664]/40 text-right flex-shrink-0 shadow-inner">
                <p class="text-[8px] md:text-[9px] uppercase text-[#F8E7BE] font-black tracking-widest leading-none mb-1">⭐ ELS MEUS PUNTS</p>
                <span class="text-lg md:text-2xl font-black text-white leading-none block font-mono"><?php echo $puntos_actuales ?? 0; ?></span>
            </div>
            
            <a href="logout.php" title="Sortir" class="sm:hidden text-slate-400 hover:text-[#D21F3C] p-2 bg-white/5 rounded-lg border border-white/10"><i data-lucide="log-out" class="w-4 h-4"></i></a>

        <?php else: ?>
            <a href="admin_panel.php" class="inline-flex items-center gap-1.5 bg-white/5 hover:bg-white/10 text-white px-3 py-1.5 rounded-xl border border-white/10 text-xs font-bold transition-all">
                <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 text-[#D21F3C]"></i>
                <span class="hidden sm:inline">Panel TPV</span>
            </a>

            <a href="admin_usuarios.php" class="hidden lg:inline-flex items-center gap-1.5 bg-white/5 hover:bg-white/10 text-white px-3 py-1.5 rounded-xl border border-white/10 text-xs font-bold transition-all">
                <i data-lucide="users" class="w-3.5 h-3.5 text-[#C6A664]"></i>
                <span>Clients</span>
            </a>

            <a href="admin_historial.php" class="hidden lg:inline-flex items-center gap-1.5 bg-white/5 hover:bg-white/10 text-white px-3 py-1.5 rounded-xl border border-white/10 text-xs font-bold transition-all">
                <i data-lucide="history" class="w-3.5 h-3.5 text-blue-400"></i>
                <span>Historial</span>
            </a>

            <a href="logout.php" class="flex items-center gap-1.5 bg-red-500/10 hover:bg-red-500/20 text-[#FF7B90] hover:text-white px-3 py-1.5 rounded-xl border border-red-500/30 transition-all font-bold text-xs">
                <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                <span class="hidden sm:inline">Sortir</span>
            </a>
        <?php endif; ?>
    </div>
</header>
