<?php 
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) { header("Location: index.php"); exit; }

$usuario_id = $_SESSION['usuario_id'];
$es_admin = ($_SESSION['rol'] === 'admin'); 

// Configuración del QR de la sesión
$url_escaneo = "https://eaten-litmus-active.ngrok-free.dev/grobe-club/admin_canjear.php?id=" . $usuario_id;
$url_qr_api = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=10&data=" . urlencode($url_escaneo);

// Chequeo de base de datos
$columna_existe = $pdo->query("SHOW COLUMNS FROM productos LIKE 'puntos_coste'")->rowCount();
if ($columna_existe == 0) {
    $pdo->exec("ALTER TABLE productos ADD puntos_coste INT DEFAULT 0");
}

// OBTENCIÓN DE DATOS (EL MODELO)
$nombre_user = $_SESSION['nombre'];
$puntos_actuales = 0;

if (!$es_admin) {
    $stmtUser = $pdo->prepare("SELECT puntos_acumulados FROM usuarios WHERE id_usuario = ?");
    $stmtUser->execute([$usuario_id]);
    $puntos_actuales = $stmtUser->fetchColumn() ?: 0;
}

$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre_categoria")->fetchAll();

// Filtro: Si es cliente, solo ve los disponibles.
$filtro_disponible = $es_admin ? "" : "WHERE p.disponible = 1";

$query = "SELECT p.*, IFNULL(c.nombre_categoria, 'Otros') as categoria 
          FROM productos p 
          LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
          $filtro_disponible 
          ORDER BY p.nombre_producto";
$productos_raw = $pdo->query($query)->fetchAll();

// Clasificación de la carta
$carta_triptico = [];
foreach($productos_raw as $p) { $carta_triptico[$p['categoria']][] = $p; }

// ORDEN CORREGIDO AQUÍ:
$orden_deseado = ['Hamburguesas', 'Tapas', 'Bebidas'];

uksort($carta_triptico, function($a, $b) use ($orden_deseado) {
    $pos_a = array_search($a, $orden_deseado); $pos_b = array_search($b, $orden_deseado);
    return ($pos_a === false ? 99 : $pos_a) <=> ($pos_b === false ? 99 : $pos_b);
});
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Grobe Club - Carta</title>
    <?php include 'componentes/head.php'; ?>
</head>
<body class="bg-[#121212] text-white font-sans overflow-x-hidden">

    <?php include 'componentes/navbar.php'; ?>

    <main class="w-full max-w-[1400px] mx-auto mt-6 px-4 lg:px-6 pb-40">
        
        <?php if($es_admin): ?>
        <div id="panel-formulario" class="bg-white/[0.03] p-6 rounded-3xl border border-dashed border-[#D21F3C]/50 mb-10 transition-all duration-300">
            <div class="flex justify-between items-center mb-4">
                <h2 id="titulo-formulario" class="text-xl font-black text-white flex items-center gap-2">
                    <i data-lucide="plus-circle" id="icono-formulario" class="text-[#D21F3C]"></i> <span id="texto-formulario">Añadir Nuevo Plato</span>
                </h2>
                <button type="button" id="btn-cancelar-edicion" onclick="cancelarEdicion()" class="hidden text-xs text-slate-400 hover:text-white underline">Cancelar Edición</button>
            </div>

            <form action="procesar_carta.php" method="POST" id="form-producto" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <input type="hidden" name="agregar_producto" id="input-accion" value="1">
                <input type="hidden" name="id_producto_editar" id="input-id-editar" value="">
                
                <input type="text" name="nombre" id="input-nombre" placeholder="Nombre..." required class="input-grobe">
                <input type="text" name="descripcion" id="input-descripcion" placeholder="Descripción (opcional)..." class="input-grobe">
                <input type="number" step="0.01" name="precio" id="input-precio" placeholder="Precio (€)" required class="input-grobe">
                
                <select name="id_categoria" id="input-categoria" required class="input-grobe">
                    <option value="" disabled selected>Categoría...</option>
                    <?php foreach($categorias as $c): ?>
                        <option value="<?php echo $c['id_categoria']; ?>"><?php echo $c['nombre_categoria']; ?></option>
                    <?php endforeach; ?>
                </select>

                <input type="number" name="puntos_coste" id="input-puntos-coste" placeholder="Puntos coste" value="0" required class="input-grobe border-yellow-500/30 focus:border-yellow-500">
                <button type="submit" id="btn-submit" class="btn-primario">Guardar Plato</button>
            </form>
        </div>
        <?php endif; ?>

        <?php if (empty($carta_triptico)): ?>
            <p class="text-center text-slate-500 mt-10">La carta está vacía.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-10">
                <?php foreach($carta_triptico as $nombre_categoria => $platos): ?>
                    <div class="space-y-4 tarjeta-panel">
                        <h2 class="text-2xl lg:text-3xl font-black text-[#D21F3C] border-b-2 border-[#D21F3C]/30 pb-3 mb-4 lg:mb-6 uppercase tracking-tighter">
                            <?php echo $nombre_categoria; ?>
                        </h2>
                        <div class="space-y-3 lg:space-y-4">
                            <?php foreach($platos as $p): ?>
                                <?php $opacidad = ($es_admin && $p['disponible'] == 0) ? 'opacity-50 grayscale hover:grayscale-0' : ''; ?>
                                
                                <div class="bg-[#1a1a1a] p-4 lg:p-5 rounded-2xl border <?php echo ($p['puntos_coste'] > 0) ? 'border-yellow-500/40 hover:border-yellow-Ç600 hover:shadow-[0_0_15px_rgba(234,179,8,0.15)]' : 'border-white/5 hover:border-[#D21F3C]/60 hover:shadow-[0_0_15px_rgba(210,31,60,0.15)]'; ?> flex justify-between items-center transition-all duration-300 hover:-translate-y-1 relative overflow-hidden group <?php echo $opacidad; ?>">
                                    <div class="pr-3 flex-1 relative z-10">
                                        
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="font-bold text-base lg:text-lg leading-tight">
                                                <?php echo htmlspecialchars($p['nombre_producto']); ?>
                                            </h3>
                                            
                                            <?php if($p['puntos_coste'] > 0): ?>
                                                <span class="bg-yellow-500/20 text-yellow-500 text-[10px] uppercase font-black px-2 py-0.5 rounded-md border border-yellow-500/30">
                                                    🎁 <?php echo $p['puntos_coste']; ?> pts
                                                </span>
                                            <?php endif; ?>

                                            <?php if($es_admin && $p['disponible'] == 0): ?>
                                                <span class="bg-red-500/20 text-red-500 text-[10px] uppercase font-black px-2 py-0.5 rounded-md border border-red-500/30">
                                                    Agotado
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if(!empty($p['descripcion'])): ?>
                                            <p class="text-xs text-slate-400 mt-1 italic leading-snug">
                                                <?php echo htmlspecialchars($p['descripcion']); ?>
                                            </p>
                                        <?php endif; ?>

                                        <p class="text-white font-black text-lg lg:text-xl mt-1.5"><?php echo number_format($p['precio'], 2); ?>€</p>
                                    </div>
                                    
                                    <?php if($es_admin): ?>
                                        <div class="flex items-center gap-1.5 lg:gap-2 relative z-10">
                                            
                                            <form action="procesar_carta.php" method="POST" class="inline">
                                                <input type="hidden" name="toggle_disponible" value="1">
                                                <input type="hidden" name="id_producto" value="<?php echo $p['id_producto']; ?>">
                                                <input type="hidden" name="nuevo_estado" value="<?php echo $p['disponible'] ? 0 : 1; ?>">
                                                <button type="submit" class="p-2.5 rounded-xl transition-all <?php echo $p['disponible'] ? 'bg-orange-900/30 border border-orange-500/30 hover:bg-orange-600 text-orange-400 group-hover:text-white' : 'bg-green-900/30 border border-green-500/30 hover:bg-green-600 text-green-400 group-hover:text-white'; ?>" title="<?php echo $p['disponible'] ? 'Marcar como Agotado' : 'Marcar como Disponible'; ?>">
                                                    <i data-lucide="<?php echo $p['disponible'] ? 'eye-off' : 'eye'; ?>" class="w-5 h-5"></i>
                                                </button>
                                            </form>

                                            <button type="button" onclick="prepararEdicion(<?php echo $p['id_producto']; ?>, '<?php echo addslashes($p['nombre_producto']); ?>', '<?php echo addslashes((string)$p['descripcion']); ?>', <?php echo $p['precio']; ?>, <?php echo $p['id_categoria']; ?>, <?php echo $p['puntos_coste']; ?>)"
                                                class="bg-blue-900/30 border border-blue-500/30 p-2.5 rounded-xl hover:bg-blue-600 transition-all group-hover:text-white">
                                                <i data-lucide="pencil" class="w-5 h-5 text-blue-400"></i>
                                            </button>

                                            <form action="procesar_carta.php" method="POST" onsubmit="return confirm('¿Borrar plato definitivamente?');" class="inline">
                                                <input type="hidden" name="eliminar_producto" value="1"><input type="hidden" name="id_producto" value="<?php echo $p['id_producto']; ?>">
                                                <button type="submit" class="bg-red-900/30 border border-red-500/30 p-2.5 rounded-xl hover:bg-red-600 transition-all group-hover:text-white">
                                                    <i data-lucide="trash-2" class="w-5 h-5 text-red-500"></i>
                                                </button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <button onclick="agregarAlCarrito('<?php echo addslashes($p['nombre_producto']); ?>', <?php echo $p['precio']; ?>)" class="bg-[#121212] border border-white/10 p-2.5 lg:p-3 rounded-xl hover:bg-[#D21F3C] active:scale-95 transition-all relative z-10">
                                            <i data-lucide="plus" class="w-5 h-5 lg:w-6 lg:h-6 text-white"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php if(!$es_admin): ?>
        <?php include 'componentes/modal_qr.php'; ?>

        <div id="barra-carrito" class="fixed bottom-0 left-0 right-0 bg-white text-black p-5 lg:p-6 rounded-t-[2rem] shadow-[0_-10px_40px_rgba(0,0,0,0.3)] transform translate-y-full transition-transform duration-500 z-30 border-t-4 border-[#D21F3C]">
            <div class="max-w-md mx-auto">
                <div class="flex justify-between items-center mb-4">
                    <span class="font-black text-xl uppercase tracking-tighter">Tu Pedido</span>
                    <span id="total-precio" class="text-4xl font-black text-[#D21F3C]">0.00€</span>
                </div>
                
                <ul id="lista-carrito" class="mb-4 max-h-32 overflow-y-auto space-y-2 text-sm pr-2"></ul>

                <form action="procesar_carta.php" method="POST">
                    <input type="hidden" name="total_pedido" id="input-total" value="0">
                    <button type="submit" class="btn-primario text-lg py-4 w-full">SUMAR PUNTOS Y CERRAR</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <script src="assets/js/carta.js"></script>
</body>
</html>