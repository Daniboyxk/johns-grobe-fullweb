<div id="modal-qr" class="fixed inset-0 bg-black/90 z-[100] hidden flex-col items-center justify-center p-4 backdrop-blur-md opacity-0 transition-opacity duration-300">
    <div class="bg-[#1a1a1a] p-8 rounded-3xl border border-white/10 text-center relative max-w-sm w-full shadow-[0_0_100px_rgba(210,31,60,0.3)] transform scale-95 transition-transform duration-300" id="modal-qr-content">
        <button onclick="cerrarModalQR()" class="absolute top-4 right-4 text-slate-400 hover:text-white bg-white/5 p-2 rounded-full hover:bg-[#D21F3C] transition-colors">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
        <h3 class="text-2xl font-black text-white mb-1 flex justify-center items-center gap-2">
            <i data-lucide="qr-code" class="text-[#D21F3C]"></i> Carnet Grobe
        </h3>
        <p class="text-slate-400 text-sm mb-8 font-bold">Cliente: <?php echo htmlspecialchars($nombre_user); ?></p>
        <div class="bg-white p-4 rounded-3xl inline-block shadow-2xl mb-8 border-4 border-white">
            <img src="<?php echo $url_qr_api; ?>" alt="Código QR Gigante" class="w-56 h-56 md:w-64 md:h-64 object-cover">
        </div>
        <p class="text-[#D21F3C] text-xs uppercase tracking-widest font-black animate-pulse">Muestra esto al camarero</p>
    </div>
</div>