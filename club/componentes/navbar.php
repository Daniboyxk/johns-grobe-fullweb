<header class="p-3 md:p-5 border-b border-white/10 bg-[#1a1a1a]/95 backdrop-blur-md sticky top-0 z-40 flex justify-between items-center shadow-lg w-full">
    <div class="pr-2 truncate">
        <h1 class="text-2xl md:text-3xl font-black italic tracking-tight">Grobe<span class="text-[#D21F3C]">Club</span></h1>
        
        <?php if(isset($es_admin) && $es_admin): ?>
            <p class="text-[10px] md:text-xs text-slate-400 mt-1">
                Hola, <span class="font-bold text-white"><?php echo htmlspecialchars($nombre_user ?? 'Admin'); ?></span> | 
                <a href="admin_panel.php" class="text-[#D21F3C] font-bold hover:underline">Panel Control</a>
            </p>
        <?php endif; ?>
    </div>
    
    <div class="flex items-center gap-3 md:gap-5">
        <?php if(!isset($es_admin) || !$es_admin): ?>
            <div class="text-right hidden sm:block">
                <p class="text-[10px] md:text-xs text-slate-400">Hola, <span class="font-bold text-white"><?php echo htmlspecialchars($nombre_user ?? 'Cliente'); ?></span></p>
                <a href="logout.php" class="text-[#D21F3C] text-[10px] md:text-xs font-bold hover:underline">Salir</a>
            </div>

            <button onclick="abrirModalQR()" class="bg-white p-1 rounded-lg shadow-md hover:scale-105 transition-all border-2 border-transparent hover:border-[#D21F3C] cursor-pointer group relative flex-shrink-0">
                <img src="<?php echo $url_qr_api ?? ''; ?>" alt="QR" class="w-10 h-10 md:w-12 md:h-12 object-cover">
            </button>

            <div class="bg-white/5 px-3 md:px-5 py-2 rounded-xl md:rounded-2xl border border-[#D21F3C]/30 text-right flex-shrink-0">
                <p class="text-[8px] md:text-[9px] uppercase text-slate-400 font-bold tracking-widest mb-0.5">Mis Puntos</p>
                <span class="text-xl md:text-3xl font-black text-white leading-none block"><?php echo $puntos_actuales ?? 0; ?></span>
            </div>
            
            <a href="logout.php" class="sm:hidden text-slate-500 hover:text-[#D21F3C] ml-1"><i data-lucide="log-out" class="w-5 h-5"></i></a>

        <?php else: ?>
            <div class="hidden md:block bg-[#D21F3C]/10 px-3 py-2 rounded-xl border border-[#D21F3C]/30 text-right flex-shrink-0">
                <p class="text-[10px] uppercase text-[#D21F3C] font-black tracking-widest">MODO EDICIÓN</p>
            </div>

            <a href="logout.php" class="flex items-center gap-2 bg-white/5 hover:bg-red-500/10 text-white hover:text-red-500 px-4 py-2 rounded-xl border border-white/10 hover:border-red-500/50 transition-all font-bold text-xs md:text-sm">
                <i data-lucide="log-out" class="w-4 h-4"></i>
                <span>Cerrar Sesión</span>
            </a>
        <?php endif; ?>
    </div>
</header>