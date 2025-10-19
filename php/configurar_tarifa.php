<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$roles = $user['roles'] ?? [];

if (!in_array('Administrador', $roles)) {
    echo "🚫 No tienes permisos para configurar tarifas.";
    exit;
}

// Aquí actualizas la BD (ejemplo)
include 'db.php';
$tipo = $_POST['tipo'];
$tarifa = $_POST['tarifa'];
// Ejecutar UPDATE o INSERT según tu estructura
echo "✅ Tarifa actualizada correctamente.";
?>
