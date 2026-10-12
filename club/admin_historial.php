<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') { 
    header("Location: index.php"); exit; 
}

$nombre_user = $_SESSION['nombre'] ?? 'Admin';
$es_admin = true;

try {
    // Consulta con LEFT JOIN para obtener el nombre del producto si existe
    $stmt = $pdo->query("
        SELECT 
            h.id_transaccion, 
            h.puntos, 
            h.fecha, 
            u.nombre as nombre_cliente, 
            p.nombre_producto 
        FROM historial_puntos h 
        JOIN usuarios u ON h.id_usuario = u.id_usuario 
        LEFT JOIN productos p ON h.id_premio = p.id_producto 
        ORDER BY h.fecha DESC 
        LIMIT 50
    ");
    $transacciones = $stmt->fetchAll();
} catch (Exception $e) {
    $transacciones = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Grobe Admin - Historial</title>
    <?php include 'componentes/head.php'; ?>
</head>
<body class="bg-[#121212] text-white font-sans min-h-screen">

    <?php include 'componentes/navbar.php'; ?>
    
    <main class="p-6 md:p-10 max-w-5xl mx-auto mt-4 pb-20">
        <div class="mb-8 flex justify-between items-end">
            <div>
                <h2 class="text-3xl md:text-4xl font-black mb-2 flex items-center gap-3">
                    <i data-lucide="history" class="text-[#D21F3C]"></i> Historial de Movimientos
                </h2>
                <p class="text-slate-400">Control de puntos, canjes y ajustes del sistema.</p>
            </div>
            <span class="hidden md:block text-[10px] font-black text-slate-500 uppercase tracking-widest bg-white/5 px-3 py-1 rounded-full border border-white/10">
                Panel de Auditoría
            </span>
        </div>

        <?php if (empty($transacciones)): ?>
            <div class="bg-[#1a1a1a] p-10 rounded-3xl border border-white/5 text-center">
                <i data-lucide="file-search" class="w-16 h-16 text-slate-600 mx-auto mb-4"></i>
                <h3 class="text-xl font-bold text-white mb-2">Sin registros</h3>
                <p class="text-slate-500">No se han encontrado movimientos en la base de datos.</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach($transacciones as $t): ?>
                    <?php 
                        $es_canje = !empty($t['nombre_producto']);
                        $es_suma = ($t['puntos'] > 0); 
                        
                        // LÓGICA DE CLASIFICACIÓN SEGÚN TU SOLICITUD
                        if ($es_canje) {
                            // Si hay producto = CANJE (Amarillo)
                            $badge_color = "bg-yellow-500/20 text-yellow-500 border-yellow-500/30";
                            $tipo_texto = "CANJE DE PREMIO";
                            $detalle = "Producto: " . $t['nombre_producto'];
                            $icono = "gift";
                        } elseif ($es_suma) {
                            // Si es positivo y NO hay producto = COMPRA (Verde)
                            $badge_color = "bg-green-500/20 text-green-500 border-green-500/30";
                            $tipo_texto = "COMPRA / CONSUMO";
                            $detalle = "Puntos ganados por ticket";
                            $icono = "shopping-cart";
                        } else {
                            // Si es negativo y NO hay producto = AJUSTE (Azul)
                            $badge_color = "bg-blue-500/20 text-blue-500 border-blue-500/30";
                            $tipo_texto = "AJUSTE MANUAL";
                            $detalle = "Modificado por Administrador";
                            $icono = "user-cog";
                        }
                    ?>
                    
                    <div class="bg-[#1a1a1a] p-5 rounded-2xl border border-white/5 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:border-white/10 transition-all hover:bg-white/[0.02] shadow-lg">
                        
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 flex-shrink-0 rounded-full flex items-center justify-center border <?php echo $badge_color; ?>">
                                <i data-lucide="<?php echo $icono; ?>" class="w-5 h-5"></i>
                            </div>
                            
                            <div class="overflow-hidden">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="font-bold text-lg leading-tight truncate"><?php echo htmlspecialchars($t['nombre_cliente']); ?></h3>
                                    <span class="text-[8px] px-2 py-0.5 rounded border font-black <?php echo $badge_color; ?>">
                                        <?php echo $tipo_texto; ?>
                                    </span>
                                </div>
                                <p class="text-slate-500 text-xs italic">
                                    <?php echo htmlspecialchars($detalle); ?>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between md:justify-end gap-8 border-t md:border-t-0 border-white/5 pt-3 md:pt-0">
                            <div class="text-left md:text-right">
                                <p class="text-slate-500 text-[10px] font-black uppercase tracking-tighter mb-1">Registro</p>
                                <p class="text-slate-300 text-xs">
                                    <?php echo date('d/m/Y', strtotime($t['fecha'])); ?> 
                                    <span class="text-slate-500 ml-1"><?php echo date('H:i', strtotime($t['fecha'])); ?></span>
                                </p>
                            </div>
                            
                            <div class="min-w-[90px] text-right">
                                <span class="text-2xl font-black <?php echo $es_suma ? 'text-green-500' : 'text-[#D21F3C]'; ?>">
                                    <?php echo ($es_suma ? '+' : '') . $t['puntos']; ?>
                                </span>
                                <span class="text-[10px] font-black text-slate-500 ml-1 uppercase">pts</span>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="mt-12 text-center">
            <a href="admin_panel.php" class="inline-flex items-center gap-2 text-slate-500 hover:text-[#D21F3C] font-black text-[10px] uppercase tracking-[0.2em] transition-all group">
                <i data-lucide="chevron-left" class="w-4 h-4 transition-transform group-hover:-translate-x-1"></i>
                Volver al Panel
            </a>
        </div>
    </main>

    <script>
        // Inicializar iconos de Lucide
        lucide.createIcons();
    </script>
</body>
</html>