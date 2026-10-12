<?php
session_start();
require_once 'conexion.php';

// 1. SEGURIDAD: Solo admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') { 
    header("Location: index.php"); exit; 
}

$id_cliente = $_GET['id'] ?? null;
if (!$id_cliente) { header("Location: admin_panel.php"); exit; }

// 2. OBTENER DATOS DEL CLIENTE
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
$stmt->execute([$id_cliente]);
$cliente = $stmt->fetch();

if (!$cliente) { die("Cliente no encontrado."); }

$es_admin = true;
$nombre_user = $_SESSION['nombre'];

// ==========================================
// PROCESAR EDICIÓN DIRECTA DE PUNTOS (EL LÁPIZ)
// ==========================================
if (isset($_POST['ajustar_puntos'])) {
    $nuevo_total = (int)$_POST['cantidad_ajuste'];
    $diferencia = $nuevo_total - $cliente['puntos_acumulados']; 
    
    if ($diferencia != 0) { 
        if ($nuevo_total < 0) $nuevo_total = 0; 
        
        // 1. Actualizamos el saldo
        $upd = $pdo->prepare("UPDATE usuarios SET puntos_acumulados = ? WHERE id_usuario = ?");
        $upd->execute([$nuevo_total, $id_cliente]);
        
        // 2. Guardamos en el historial (id_premio es NULL)
        $hist = $pdo->prepare("INSERT INTO historial_puntos (id_usuario, puntos, id_premio) VALUES (?, ?, NULL)");
        $hist->execute([$id_cliente, $diferencia]);
        
        header("Location: admin_canjear.php?id=$id_cliente&msg=ajuste_success");
        exit;
    }
}

// ==========================================
// PROCESAR CANJE (EL CARRITO)
// ==========================================
if (isset($_POST['canjear_id'])) {
    $id_prod = (int)$_POST['canjear_id'];
    $puntos_costo = (int)$_POST['puntos_costo_valor'];

    if ($cliente['puntos_acumulados'] >= $puntos_costo) {
        $nuevo_total = $cliente['puntos_acumulados'] - $puntos_costo;
        
        // 1. Actualizamos el saldo
        $upd = $pdo->prepare("UPDATE usuarios SET puntos_acumulados = ? WHERE id_usuario = ?");
        $upd->execute([$nuevo_total, $id_cliente]);
        
        // 2. Guardamos el canje en el historial (registrando qué producto fue)
        $hist = $pdo->prepare("INSERT INTO historial_puntos (id_usuario, puntos, id_premio) VALUES (?, ?, ?)");
        $hist->execute([$id_cliente, -$puntos_costo, $id_prod]);
        
        header("Location: admin_canjear.php?id=$id_cliente&msg=success");
        exit;
    }
}

// 4. OBTENER PRODUCTOS
$productos = $pdo->query("SELECT * FROM productos WHERE puntos_coste > 0 ORDER BY nombre_producto")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Canjear Premios - Grobe</title>
    <?php include 'componentes/head.php'; ?>
</head>
<body class="bg-[#121212] text-white font-sans min-h-screen">
    <?php include 'componentes/navbar.php'; ?>

    <main class="p-6 max-w-4xl mx-auto relative">
        <div class="bg-[#1a1a1a] p-8 rounded-[2rem] border border-white/5 mb-8 flex flex-col md:flex-row items-center justify-between gap-6 shadow-2xl relative overflow-hidden">
            <div class="flex items-center gap-5 relative z-10">
                <div class="w-16 h-16 bg-[#D21F3C]/10 rounded-2xl flex items-center justify-center border border-[#D21F3C]/20 text-[#D21F3C]">
                    <i data-lucide="user" class="w-8 h-8"></i>
                </div>
                <div>
                    <h2 class="text-3xl font-black italic uppercase tracking-tighter"><?php echo htmlspecialchars($cliente['nombre']); ?></h2>
                    <p class="text-slate-500 text-sm font-medium"><?php echo htmlspecialchars($cliente['email']); ?></p>
                </div>
            </div>
            
            <div class="bg-black/40 px-8 py-4 rounded-2xl border border-[#D21F3C]/30 text-center min-w-[240px] relative z-10 min-h-[120px] flex flex-col justify-center">
                <p class="text-[10px] uppercase font-black text-slate-500 tracking-[0.2em] mb-2">Saldo Actual</p>
                
                <div id="vista-saldo" class="flex items-center justify-center gap-3 transition-all duration-300">
                    <span class="text-5xl font-black text-white italic"><?php echo $cliente['puntos_acumulados']; ?></span>
                    <button type="button" onclick="toggleEdicionPuntos(true)" class="text-[#D21F3C] hover:text-red-400 hover:scale-110 transition-all cursor-pointer p-2">
                        <i data-lucide="pencil" class="w-5 h-5"></i>
                    </button>
                </div>

                <form id="form-edicion-saldo" action="" method="POST" class="hidden flex-col items-center justify-center gap-2 transition-all duration-300">
                    <input type="hidden" name="ajustar_puntos" value="1">
                    <div class="flex items-center justify-center gap-2">
                        <input type="number" name="cantidad_ajuste" required 
                               value="<?php echo $cliente['puntos_acumulados']; ?>"
                               class="w-32 bg-[#121212] text-white text-4xl font-black italic p-2 rounded-xl border border-[#D21F3C] focus:outline-none focus:ring-1 focus:ring-[#D21F3C] text-center">
                        <button type="submit" class="bg-[#D21F3C] hover:bg-red-700 text-white p-3 rounded-xl transition-all shadow-lg active:scale-95" title="Guardar">
                            <i data-lucide="check" class="w-6 h-6"></i>
                        </button>
                        <button type="button" onclick="toggleEdicionPuntos(false)" class="bg-slate-800 hover:bg-slate-700 text-white p-3 rounded-xl transition-all" title="Cancelar">
                            <i data-lucide="x" class="w-6 h-6"></i>
                        </button>
                    </div>
                </form>

                <p class="text-[10px] text-[#D21F3C] font-bold mt-2 tracking-widest uppercase">Puntos Grobe</p>
            </div>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
            <div class="bg-green-500/10 border border-green-500 text-green-500 p-4 rounded-2xl mb-6 text-center font-bold text-sm italic">
                ✓ Premio canjeado y guardado en el historial.
            </div>
        <?php endif; ?>
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'ajuste_success'): ?>
            <div class="bg-blue-500/10 border border-blue-500 text-blue-500 p-4 rounded-2xl mb-6 text-center font-bold text-sm italic">
                ✓ Saldo modificado y guardado en el historial.
            </div>
        <?php endif; ?>

        <h3 class="text-sm font-black mb-6 flex items-center gap-2 italic text-slate-400 tracking-[0.3em] uppercase">
            <i data-lucide="gift" class="w-4 h-4 text-[#D21F3C]"></i> Premios Disponibles
        </h3>

        <div class="space-y-3">
            <?php foreach($productos as $p): ?>
                <?php 
                    $costo = $p['puntos_coste'];
                    $puede_pagar = $cliente['puntos_acumulados'] >= $costo; 
                ?>
                
                <div class="bg-[#1a1a1a] p-5 rounded-2xl border border-white/5 flex justify-between items-center transition-all <?php echo $puede_pagar ? 'hover:bg-[#222] hover:border-white/10' : 'opacity-40'; ?>">
                    <div class="flex flex-col gap-1">
                        <h4 class="font-black text-xl italic uppercase tracking-tight text-white/90">
                            <?php echo htmlspecialchars($p['nombre_producto']); ?>
                        </h4>
                        <div class="flex items-center gap-3">
                            <span class="text-[#D21F3C] text-xs font-black italic tracking-widest uppercase">
                                Costo: <?php echo $costo; ?> pts
                            </span>
                            <span class="text-slate-600 text-[10px] font-bold uppercase tracking-widest">
                                <?php echo htmlspecialchars($p['id_categoria'] == 1 ? 'Hamburguesas' : ($p['id_categoria'] == 3 ? 'Bebidas' : 'Tapas')); ?>
                            </span>
                        </div>
                    </div>

                    <?php if($puede_pagar): ?>
                        <form action="" method="POST" onsubmit="return confirm('¿Canjear <?php echo $p['nombre_producto']; ?>?');">
                            <input type="hidden" name="canjear_id" value="<?php echo $p['id_producto']; ?>">
                            <input type="hidden" name="puntos_costo_valor" value="<?php echo $costo; ?>">
                            <button type="submit" class="bg-[#D21F3C] hover:bg-red-700 text-white p-4 rounded-2xl transition-all shadow-lg active:scale-95 flex items-center justify-center">
                                <i data-lucide="shopping-cart" class="w-6 h-6"></i>
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="text-slate-700 p-4 bg-white/5 rounded-2xl border border-white/5 cursor-not-allowed">
                            <i data-lucide="lock" class="w-6 h-6"></i>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-12 text-center">
            <a href="admin_panel.php" class="text-slate-500 hover:text-white font-black text-[10px] uppercase tracking-[0.4em] transition-all border-b border-transparent hover:border-[#D21F3C] pb-2">
                ← Volver al Panel
            </a>
        </div>
    </main>

    <script>
        lucide.createIcons();

        function toggleEdicionPuntos(mostrarFomulario) {
            const vistaSaldo = document.getElementById('vista-saldo');
            const formEdicion = document.getElementById('form-edicion-saldo');
            const inputCantidad = formEdicion.querySelector('input[name="cantidad_ajuste"]');
            const saldoActual = "<?php echo $cliente['puntos_acumulados']; ?>";

            if (mostrarFomulario) {
                vistaSaldo.classList.add('hidden');
                vistaSaldo.classList.remove('flex');
                formEdicion.classList.remove('hidden');
                formEdicion.classList.add('flex');
                inputCantidad.value = saldoActual;
                inputCantidad.focus();
            } else {
                formEdicion.classList.add('hidden');
                formEdicion.classList.remove('flex');
                vistaSaldo.classList.remove('hidden');
                vistaSaldo.classList.add('flex');
            }
        }
    </script>
</body>
</html>