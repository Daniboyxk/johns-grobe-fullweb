<?php
session_start();
require_once 'conexion.php';

// SEGURIDAD: Solo el administrador puede estar aquí
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') { 
    header("Location: index.php"); 
    exit; 
}

// VARIABLES OBLIGATORIAS PARA QUE LA BARRA FUNCIONE
$es_admin = true;
$nombre_user = $_SESSION['nombre'] ?? 'Admin';
$puntos_actuales = 0; // Se declara para evitar errores en el navbar

// --- PROCESAMIENTO DE FORMULARIOS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['guardar_puntos'])) {
        $stmt = $pdo->prepare("UPDATE usuarios SET puntos_acumulados = ? WHERE id_usuario = ?");
        $stmt->execute([(int)$_POST['puntos'], (int)$_POST['id_usuario']]);
        header("Location: admin_usuarios.php?msg=puntos"); exit;
    }
    if (isset($_POST['eliminar_usuario'])) {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
        $stmt->execute([(int)$_POST['id_usuario']]);
        header("Location: admin_usuarios.php?msg=eliminado"); exit;
    }
    if (isset($_POST['agregar_usuario'])) {
        $nombre = trim($_POST['nombre']);
        $email = trim($_POST['email']);
        $password_plana = trim($_POST['password']);
        $puntos = (int)$_POST['puntos'];
        $stmtCheck = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
        $stmtCheck->execute([$email]);
        if ($stmtCheck->rowCount() > 0) {
            header("Location: admin_usuarios.php?msg=error_email"); exit;
        } else {
            $password_hash = password_hash($password_plana, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol, puntos_acumulados) VALUES (?, ?, ?, 'cliente', ?)");
            $stmt->execute([$nombre, $email, $password_hash, $puntos]);
            header("Location: admin_usuarios.php?msg=creado"); exit;
        }
    }
    if (isset($_POST['editar_usuario'])) {
        $id_usuario = (int)$_POST['id_usuario_editar'];
        $nombre = trim($_POST['nombre']);
        $email = trim($_POST['email']);
        $puntos = (int)$_POST['puntos'];
        $nueva_password = trim($_POST['password']);
        if (!empty($nueva_password)) {
            $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, email = ?, puntos_acumulados = ?, password = ? WHERE id_usuario = ?");
            $stmt->execute([$nombre, $email, $puntos, $password_hash, $id_usuario]);
        } else {
            $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, email = ?, puntos_acumulados = ? WHERE id_usuario = ?");
            $stmt->execute([$nombre, $email, $puntos, $id_usuario]);
        }
        header("Location: admin_usuarios.php?msg=editado"); exit;
    }
}

$clientes = $pdo->query("SELECT * FROM usuarios WHERE rol = 'cliente' ORDER BY puntos_acumulados DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Grobe Admin - Usuarios</title>
    <?php include __DIR__ . '/componentes/head.php'; ?>
</head>
<body class="bg-[#121212] text-white font-sans min-h-screen">

    <?php include __DIR__ . '/componentes/navbar.php'; ?>
    
    <main class="p-6 md:p-10 max-w-5xl mx-auto mt-4 pb-20">
        <div class="mb-8">
            <h2 class="text-3xl md:text-4xl font-black mb-2 flex items-center gap-3">
                <i data-lucide="users" class="text-[#D21F3C]"></i> Gestión de Clientes
            </h2>
            <p class="text-slate-400">Añade nuevos miembros, edita sus datos o ajusta sus puntos manualmente.</p>
        </div>

        <div id="panel-formulario" class="bg-white/[0.03] p-6 rounded-3xl border border-dashed border-[#D21F3C]/50 mb-10 shadow-2xl">
            <div class="flex justify-between items-center mb-4">
                <h2 id="titulo-formulario" class="text-xl font-black text-white flex items-center gap-2">
                    <i data-lucide="user-plus" id="icono-formulario" class="text-[#D21F3C]"></i> <span id="texto-formulario">Añadir Nuevo Cliente</span>
                </h2>
                <button type="button" id="btn-cancelar-edicion" onclick="cancelarEdicion()" class="hidden text-xs text-slate-400 hover:text-white underline">Cancelar Edición</button>
            </div>
            <form action="" method="POST" id="form-usuario" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <input type="hidden" name="agregar_usuario" id="input-accion" value="1">
                <input type="hidden" name="id_usuario_editar" id="input-id-editar" value="">
                
                <div class="md:col-span-1">
                    <label class="block text-[10px] uppercase text-slate-400 font-bold mb-1">Nombre</label>
                    <input type="text" name="nombre" id="input-nombre" placeholder="Nombre completo" required class="w-full bg-white/5 border border-white/10 p-3 rounded-xl outline-none focus:border-[#D21F3C] transition-all">
                </div>
                <div class="md:col-span-1">
                    <label class="block text-[10px] uppercase text-slate-400 font-bold mb-1">Email</label>
                    <input type="email" name="email" id="input-email" placeholder="ejemplo@correo.com" required class="w-full bg-white/5 border border-white/10 p-3 rounded-xl outline-none focus:border-[#D21F3C] transition-all">
                </div>
                <div class="md:col-span-1">
                    <label class="block text-[10px] uppercase text-slate-400 font-bold mb-1" id="label-password">Contraseña</label>
                    <input type="text" name="password" id="input-password" placeholder="Mínimo 6 caracteres" class="w-full bg-white/5 border border-white/10 p-3 rounded-xl outline-none focus:border-[#D21F3C] transition-all" required>
                </div>
                <div class="md:col-span-1">
                    <label class="block text-[10px] uppercase text-slate-400 font-bold mb-1">Puntos Iniciales</label>
                    <input type="number" name="puntos" id="input-puntos" placeholder="0" value="0" required class="w-full bg-white/5 border border-yellow-500/30 p-3 rounded-xl outline-none focus:border-yellow-500 transition-all">
                </div>
                <div class="md:col-span-1 flex items-end">
                    <button type="submit" id="btn-submit" class="w-full bg-[#D21F3C] hover:bg-[#b01a32] text-white font-black py-3 px-6 rounded-xl transition-all active:scale-95">Guardar Cliente</button>
                </div>
            </form>
        </div>

        <div class="space-y-4">
            <?php foreach($clientes as $c): ?>
                <div class="bg-[#1a1a1a] p-5 rounded-2xl border border-white/5 flex flex-col lg:flex-row lg:items-center justify-between gap-4 hover:border-white/10 transition-colors shadow-md group">
                    <div class="flex items-center gap-4">
                        <div class="bg-white/5 w-12 h-12 flex-shrink-0 rounded-full flex items-center justify-center text-[#D21F3C] font-black text-xl border border-white/10">
                            <?php echo strtoupper(substr($c['nombre'], 0, 1)); ?>
                        </div>
                        <div class="overflow-hidden">
                            <h3 class="font-bold text-lg leading-tight truncate"><?php echo htmlspecialchars($c['nombre']); ?></h3>
                            <p class="text-slate-500 text-sm truncate"><?php echo htmlspecialchars($c['email']); ?></p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <form action="" method="POST" class="flex items-center gap-2 bg-[#121212] p-2 rounded-xl border border-white/10 flex-grow lg:flex-grow-0">
                            <input type="hidden" name="guardar_puntos" value="1">
                            <input type="hidden" name="id_usuario" value="<?php echo $c['id_usuario']; ?>">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-2">PTS</span>
                            <input type="number" name="puntos" value="<?php echo $c['puntos_acumulados']; ?>" class="w-20 bg-transparent text-white font-black text-xl outline-none text-right focus:text-[#D21F3C] transition-colors">
                            <button type="submit" class="bg-white/5 hover:bg-[#D21F3C] p-2 rounded-lg transition-colors" title="Guardar Puntos">
                                <i data-lucide="save" class="w-4 h-4 text-white"></i>
                            </button>
                        </form>
                        <div class="w-px h-8 bg-white/10 hidden lg:block mx-1"></div>
                        <button type="button" onclick="prepararEdicion(<?php echo $c['id_usuario']; ?>, '<?php echo addslashes($c['nombre']); ?>', '<?php echo addslashes($c['email']); ?>', <?php echo $c['puntos_acumulados']; ?>)" class="bg-blue-900/30 border border-blue-500/30 p-3 rounded-xl hover:bg-blue-600 transition-all flex-shrink-0 group-hover:text-white text-blue-400" title="Editar Cliente">
                            <i data-lucide="pencil" class="w-5 h-5"></i>
                        </button>
                        <form action="" method="POST" onsubmit="return confirm('¿Seguro que quieres borrar a este usuario?');" class="inline flex-shrink-0">
                            <input type="hidden" name="eliminar_usuario" value="1">
                            <input type="hidden" name="id_usuario" value="<?php echo $c['id_usuario']; ?>">
                            <button type="submit" class="bg-red-900/30 border border-red-500/30 p-3 rounded-xl hover:bg-red-600 transition-all text-red-500 group-hover:text-white" title="Eliminar Cliente">
                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script>
        function prepararEdicion(id, nombre, email, puntos) {
            document.getElementById('panel-formulario').classList.replace('border-[#D21F3C]/50', 'border-blue-500');
            document.getElementById('texto-formulario').innerText = "Editando Cliente...";
            document.getElementById('icono-formulario').setAttribute('data-lucide', 'user-cog');
            document.getElementById('icono-formulario').classList.replace('text-[#D21F3C]', 'text-blue-500');
            document.getElementById('btn-submit').className = "w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-3 px-6 rounded-xl transition-all active:scale-95";
            document.getElementById('btn-submit').innerText = "Actualizar Cliente";
            document.getElementById('btn-cancelar-edicion').classList.remove('hidden');
            document.getElementById('input-accion').name = "editar_usuario";
            document.getElementById('input-id-editar').value = id;
            document.getElementById('input-nombre').value = nombre;
            document.getElementById('input-email').value = email;
            document.getElementById('input-puntos').value = puntos;
            document.getElementById('input-password').required = false;
            document.getElementById('input-password').value = "";
            document.getElementById('label-password').innerText = "Nueva Contraseña (Opcional)";
            document.getElementById('input-password').placeholder = "Dejar en blanco para no cambiar";
            window.scrollTo({ top: 0, behavior: 'smooth' });
            lucide.createIcons();
        }

        function cancelarEdicion() {
            document.getElementById('panel-formulario').classList.replace('border-blue-500', 'border-[#D21F3C]/50');
            document.getElementById('texto-formulario').innerText = "Añadir Nuevo Cliente";
            document.getElementById('icono-formulario').setAttribute('data-lucide', 'user-plus');
            document.getElementById('icono-formulario').classList.replace('text-blue-500', 'text-[#D21F3C]');
            document.getElementById('btn-submit').className = "w-full bg-[#D21F3C] hover:bg-[#b01a32] text-white font-black py-3 px-6 rounded-xl transition-all active:scale-95";
            document.getElementById('btn-submit').innerText = "Guardar Cliente";
            document.getElementById('btn-cancelar-edicion').classList.add('hidden');
            document.getElementById('input-accion').name = "agregar_usuario";
            document.getElementById('input-id-editar').value = "";
            document.getElementById('input-password').required = true;
            document.getElementById('label-password').innerText = "Contraseña";
            document.getElementById('input-password').placeholder = "Mínimo 6 caracteres";
            document.getElementById('form-usuario').reset();
            lucide.createIcons();
        }
        lucide.createIcons();
    </script>
</body>
</html>