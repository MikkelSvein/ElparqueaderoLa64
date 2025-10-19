<?php
session_start();
if (isset($_SESSION['rol'])) {
    header('Location: buscar.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Parqueadero La 64</title>
</head>
<body>
    <h2>Iniciar Sesión</h2>
    <form action="validar_login.php" method="POST">
        <label>Correo:</label>
        <input type="email" name="correo" required><br><br>

        <label>Contraseña:</label>
        <input type="password" name="clave" required><br><br>

        <button type="submit">Ingresar</button>
    </form>
</body>
</html>
