<?php
session_start();
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password_ingresada = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    // La magia está en password_verify
    if ($usuario && password_verify($password_ingresada, $usuario['password'])) {
        // LOGIN CORRECTO
        $_SESSION['usuario_id'] = $usuario['id_usuario'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['rol'] = $usuario['rol'];

        if ($usuario['rol'] === 'admin') {
            header("Location: admin_panel.php");
        } else {
            header("Location: carta.php");
        }
        exit;
    } else {
        // LOGIN INCORRECTO
        header("Location: index.php?error=1");
        exit;
    }
}