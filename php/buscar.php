<?php
require "db.php";

$q = $_GET["q"] ?? "";
$q = "%$q%";

$stmt = $conn->prepare("SELECT * FROM vehiculos WHERE placa LIKE ? OR nombre LIKE ?");
$stmt->bind_param("ss", $q, $q);
$stmt->execute();
$result = $stmt->get_result();

echo "<table border='1'><tr><th>Placa</th><th>Nombre</th><th>Documento</th><th>Tipo</th></tr>";
while($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['placa']}</td><td>{$row['nombre']}</td><td>{$row['documento']}</td><td>{$row['tipo']}</td></tr>";
}
echo "</table>";

$stmt->close();
$conn->close();
?>
