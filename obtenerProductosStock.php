<?php
include "conexion.php";

if (!isset($_GET['proveedor_id'])) {
    exit;
}

$proveedor_id = $_GET['proveedor_id'];

$stmt = $conn->prepare("
    SELECT PRODUCTO_ID, NOMBRE
    FROM productos
    WHERE PROVEEDOR_ID = ?
");

$stmt->bind_param("i", $proveedor_id);
$stmt->execute();
$result = $stmt->get_result();

echo '<option value="">Selecciona producto</option>';

while ($row = $result->fetch_assoc()) {
    echo "<option value='{$row['PRODUCTO_ID']}'>{$row['NOMBRE']}</option>";
}