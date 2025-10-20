<?php
// php/tarifas.php
sesion_start();
ini_set('display_errors', 1); // Solo para desarrollo
error_reporting(E_ALL);

require_once __DIR__ . '/db.php'; // Este archivo debe definir $conn como mysqli

if (!isset($conn) || !$conn instanceof mysqli) {
    http_response_code(500);
    echo json_encode(['error' => 'Conexión a BD no encontrada']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Valores recibidos, si no están se ponen a 0
    $bicicleta = floatval($_POST['tarifaBicicleta'] ?? 0);
    $bus       = floatval($_POST['tarifaBus'] ?? 0);
    $carro     = floatval($_POST['tarifaCarro'] ?? 0);
    $moto      = floatval($_POST['tarifaMoto'] ?? 0);

    // Verificar si ya existe el registro con id=1
    $stmt = $conn->prepare("SELECT 1 FROM tarifas WHERE id = 1");
    $stmt->execute();
    $stmt->store_result();
    $existe = $stmt->num_rows > 0;
    $stmt->close();

    if ($existe) {
        $sql = "UPDATE tarifas SET bicicleta = ?, bus = ?, carro = ?, moto = ? WHERE id = 1";
    } else {
        $sql = "INSERT INTO tarifas (id, bicicleta, bus, carro, moto) VALUES (1, ?, ?, ?, ?)";
    }

    $upd = $conn->prepare($sql);
    $upd->bind_param("dddd", $bicicleta, $bus, $carro, $moto);

    if ($upd->execute()) {
        echo "Tarifas guardadas correctamente.";
    } else {
        http_response_code(500);
        echo "Error al guardar tarifas: " . $upd->error;
    }
    $upd->close();
    $conn->close();
    exit;
}

// Si es GET, devolver tarifas en JSON
header('Content-Type: application/json; charset=utf-8');

$result = $conn->query("SELECT bicicleta, bus, carro, moto FROM tarifas WHERE id = 1 LIMIT 1");

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    // Valores por defecto
    $row = [
        'bicicleta' => 500,
        'bus'       => 5000,
        'carro'     => 2000,
        'moto'      => 1000
    ];
}

echo json_encode([
    'bicicleta' => floatval($row['bicicleta']),
    'bus'       => floatval($row['bus']),
    'carro'     => floatval($row['carro']),
    'moto'      => floatval($row['moto'])
]);

$conn->close();
