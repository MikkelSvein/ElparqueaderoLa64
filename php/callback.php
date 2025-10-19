<?php
require 'vendor/autoload.php';
use Auth0\SDK\Auth0;

$config = require 'config/auth0.php';
$auth0 = new Auth0($config);

// Procesa el callback después del login
$user = $auth0->getUser();

if ($user) {
    session_start();
    $_SESSION['user'] = $user;
    header('Location: buscar.php'); // Página principal al iniciar sesión
    exit;
} else {
    echo "❌ Error al iniciar sesión.";
}
