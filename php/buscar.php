<?php
session_start();
require "db.php";

// 🔹 Verificar sesión (opcional para búsqueda, pero recomendado para admin)
// Si no hay sesión, aún permite buscar pero sin verificación de admin
$es_admin = isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';

$q = $_GET["q"] ?? "";

if (empty($q)) {
    echo "<p style='color: #888; text-align: center;'>Ingrese un término de búsqueda.</p>";
    exit;
}

$q = "%$q%";

$stmt = $conn->prepare("SELECT * FROM vehiculos WHERE placa LIKE ? OR nombre LIKE ?");
$stmt->bind_param("ss", $q, $q);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p style='color: #888; text-align: center;'>No se encontraron vehículos con ese criterio.</p>";
    $stmt->close();
    $conn->close();
    exit;
}

echo "<table border='1' style='width:100%; border-collapse:collapse;'><tr><th>Placa</th><th>Nombre</th><th>Documento</th><th>Tipo</th><th>Acción</th></tr>";
while($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>{$row['placa']}</td>
        <td>{$row['nombre']}</td>
        <td>{$row['documento']}</td>
        <td>{$row['tipo']}</td>
        <td><button class='btn-salida' onclick='registrarSalida({$row['id']})'>Registrar Salida</button></td>
    </tr>";
}
echo "</table>";

$stmt->close();
$conn->close();
?>
