<?php 
session_start();
require_once 'conexion.php';

// Seguridad: Si no hay sesión, fuera.
if (!isset($_SESSION['usuario_id'])) { 
    header("Location: index.php"); 
    exit; 
}

$usuario_id = $_SESSION['usuario_id'];
$es_admin = ($_SESSION['rol'] === 'admin'); 

// Solo aceptamos peticiones POST (cuando alguien envía un formulario)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- ACCIONES DEL ADMINISTRADOR ---
    if ($es_admin) {
        if (isset($_POST['agregar_producto'])) {
            $stmt = $pdo->prepare("INSERT INTO productos (nombre_producto, descripcion, precio, id_categoria, puntos_coste, disponible) VALUES (?, ?, ?, ?, ?, 1)");
            $stmt->execute([$_POST['nombre'], $_POST['descripcion'], $_POST['precio'], $_POST['id_categoria'], (int)$_POST['puntos_coste']]);
        }
        if (isset($_POST['eliminar_producto'])) {
            $stmt = $pdo->prepare("DELETE FROM productos WHERE id_producto = ?");
            $stmt->execute([$_POST['id_producto']]);
        }
        if (isset($_POST['editar_producto'])) {
            $stmt = $pdo->prepare("UPDATE productos SET nombre_producto = ?, descripcion = ?, precio = ?, id_categoria = ?, puntos_coste = ? WHERE id_producto = ?");
            $stmt->execute([$_POST['nombre'], $_POST['descripcion'], $_POST['precio'], $_POST['id_categoria'], (int)$_POST['puntos_coste'], $_POST['id_producto_editar']]);
        }
        if (isset($_POST['toggle_disponible'])) {
            $stmt = $pdo->prepare("UPDATE productos SET disponible = ? WHERE id_producto = ?");
            $stmt->execute([(int)$_POST['nuevo_estado'], $_POST['id_producto']]);
        }
    }

    // --- ACCIONES DEL CLIENTE (SUMAR PUNTOS) ---
    if (!$es_admin && isset($_POST['total_pedido'])) {
        $puntos = floor((float)$_POST['total_pedido']);
        if ($puntos > 0) {
            $stmt = $pdo->prepare("UPDATE usuarios SET puntos_acumulados = puntos_acumulados + ? WHERE id_usuario = ?");
            $stmt->execute([$puntos, $usuario_id]);
            
            $stmtHistorial = $pdo->prepare("INSERT INTO historial_puntos (id_usuario, puntos, id_premio) VALUES (?, ?, NULL)");
            $stmtHistorial->execute([$usuario_id, $puntos]);
        }
    }

    // Una vez terminado el trabajo, el usuario vuelve a la carta sin darse cuenta
    header("Location: carta.php"); 
    exit;
} else {
    // Si alguien intenta entrar a procesar_carta.php poniendo la URL en el navegador
    header("Location: carta.php"); 
    exit;
}