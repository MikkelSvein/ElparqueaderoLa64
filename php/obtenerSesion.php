<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['id'])) {
    echo json_encode([
        'logged' => true,
        'id' => $_SESSION['id'],
        'nombre' => $_SESSION['nombre'],
        'correo' => $_SESSION['correo'],
        'rol' => $_SESSION['rol']
    ]);
} else {
    echo json_encode(['logged' => false]);
}
?>
