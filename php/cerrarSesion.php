<?php
session_start();
require_once __DIR__ . '/cors.php';
session_unset();
session_destroy();
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Sesión cerrada correctamente.']);
exit;
?>