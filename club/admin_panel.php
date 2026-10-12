<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') { 
    header("Location: index.php"); exit; 
}

$nombre_user = $_SESSION['nombre'] ?? 'Admin';
$es_admin = true;

// Consultas para los contadores de las tarjetas
$total_platos = $pdo->query("SELECT COUNT(*) FROM productos")->fetchColumn();
$total_clientes = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'cliente'")->fetchColumn();

// NUEVO: Intentamos contar el historial (si la tabla ya existe)
try {
    $total_transacciones = $pdo->query("SELECT COUNT(*) FROM historial_puntos")->fetchColumn();
} catch (Exception $e) {
    $total_transacciones = 0; // Por si la tabla aún no tiene datos o no está creada
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Grobe Admin - Panel</title>
    <?php include 'componentes/head.php'; ?>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        /* Ajuste para que el visor no rompa el diseño */
        #reader video { border-radius: 20px; object-fit: cover !important; }
        #reader { border: none !important; }
    </style>
</head>
<body class="bg-[#121212] text-white font-sans min-h-screen">

    <?php include 'componentes/navbar.php'; ?>

    <main class="p-6 md:p-10 max-w-7xl mx-auto">
        <div class="mb-10">
            <h2 class="text-4xl md:text-5xl font-black mb-2 italic">Bienvenido, <?php echo $nombre_user; ?> 👋</h2>
            <p class="text-slate-400">Centro de control del programa de fidelización.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <a href="carta.php" class="bg-[#1a1a1a] p-8 rounded-3xl border border-white/5 hover:border-[#D21F3C]/50 transition-all group">
                <div class="flex justify-between items-start mb-6">
                    <div class="bg-white/5 p-4 rounded-2xl group-hover:bg-[#D21F3C]/10">
                        <i data-lucide="utensils" class="text-slate-400 group-hover:text-[#D21F3C] w-8 h-8"></i>
                    </div>
                    <span class="text-3xl font-black"><?php echo $total_platos; ?></span>
                </div>
                <h3 class="text-2xl font-black mb-2">Carta</h3>
                <p class="text-slate-500 text-sm">Gestiona el menú y los puntos necesarios.</p>
            </a>

            <div onclick="abrirEscanner()" class="bg-[#1a1a1a] p-8 rounded-3xl border-2 border-[#D21F3C] hover:bg-[#D21F3C]/5 transition-all cursor-pointer group relative overflow-hidden">
                <div class="flex justify-between items-start mb-6">
                    <div class="bg-[#D21F3C]/20 p-4 rounded-2xl">
                        <i data-lucide="qr-code" class="text-[#D21F3C] w-8 h-8"></i>
                    </div>
                    <span class="bg-[#D21F3C] text-[10px] font-black px-2 py-1 rounded text-white uppercase">TPV</span>
                </div>
                <h3 class="text-2xl font-black mb-2">Escáner TPV</h3>
                <p class="text-slate-500 text-sm">Activa la cámara para escanear clientes.</p>
            </div>

            <a href="admin_usuarios.php" class="bg-[#1a1a1a] p-8 rounded-3xl border border-white/5 hover:border-[#D21F3C]/50 transition-all group">
                <div class="flex justify-between items-start mb-6">
                    <div class="bg-white/5 p-4 rounded-2xl group-hover:bg-[#D21F3C]/10">
                        <i data-lucide="users" class="text-slate-400 group-hover:text-[#D21F3C] w-8 h-8"></i>
                    </div>
                    <span class="text-3xl font-black"><?php echo $total_clientes; ?></span>
                </div>
                <h3 class="text-2xl font-black mb-2">Clientes</h3>
                <p class="text-slate-500 text-sm">Ver miembros y editar sus puntos.</p>
            </a>

            <a href="admin_historial.php" class="bg-[#1a1a1a] p-8 rounded-3xl border border-white/5 hover:border-[#D21F3C]/50 transition-all group">
                <div class="flex justify-between items-start mb-6">
                    <div class="bg-white/5 p-4 rounded-2xl group-hover:bg-[#D21F3C]/10">
                        <i data-lucide="history" class="text-slate-400 group-hover:text-[#D21F3C] w-8 h-8"></i>
                    </div>
                    <span class="text-3xl font-black"><?php echo $total_transacciones; ?></span>
                </div>
                <h3 class="text-2xl font-black mb-2">Historial</h3>
                <p class="text-slate-500 text-sm">Ver registro de transacciones y canjes.</p>
            </a>

        </div>
    </main>

    <div id="modal-scanner" class="fixed inset-0 bg-black/90 z-[100] hidden flex-col items-center justify-center p-4 backdrop-blur-sm">
        <div class="w-full max-w-md bg-[#1a1a1a] border border-white/10 rounded-3xl overflow-hidden relative shadow-2xl">
            
            <div class="p-5 border-b border-white/10 flex justify-between items-center bg-[#1f1f1f]">
                <h3 class="font-black italic text-[#D21F3C]">Grobe<span class="text-white">Scanner</span></h3>
                <button type="button" onclick="cerrarEscanner()" class="text-slate-400 hover:text-white p-2">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            
            <div class="relative aspect-square bg-black overflow-hidden">
                <div id="reader" class="w-full h-full"></div>
                <div class="absolute inset-10 border-2 border-[#D21F3C] border-dashed rounded-3xl opacity-30 pointer-events-none"></div>
            </div>
            
            <div class="p-6 text-center">
                <p class="text-slate-400 text-sm">Enfoca el QR del cliente para procesar la operación.</p>
            </div>
        </div>
    </div>

    <script>
        let html5QrCode = null;

        async function abrirEscanner() {
            document.getElementById('modal-scanner').classList.remove('hidden');
            document.getElementById('modal-scanner').classList.add('flex');

            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode("reader");
            }
            
            const config = { fps: 10, qrbox: { width: 250, height: 250 } };

            try {
                await html5QrCode.start(
                    { facingMode: "environment" }, 
                    config, 
                    (decodedText) => {
                        cerrarEscanner();
                        window.location.href = decodedText;
                    }
                );
            } catch (err) {
                console.error("Error de cámara:", err);
                alert("No se pudo activar la cámara. Revisa los permisos y usa HTTPS.");
            }
        }

        function cerrarEscanner() {
            if (html5QrCode && html5QrCode.isScanning) {
                html5QrCode.stop().then(() => {
                    document.getElementById('modal-scanner').classList.add('hidden');
                    document.getElementById('modal-scanner').classList.remove('flex');
                }).catch(err => {
                    document.getElementById('modal-scanner').classList.add('hidden');
                });
            } else {
                document.getElementById('modal-scanner').classList.add('hidden');
                document.getElementById('modal-scanner').classList.remove('flex');
            }
        }

        lucide.createIcons();
    </script>
</body>
</html>