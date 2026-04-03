<?php
include "conexion.php";

if (!isset($_GET['proveedor_id'], $_GET['almacen_id'])) {
    exit;
}

$proveedor_id = $_GET['proveedor_id'];
$almacen_id = $_GET['almacen_id'];

$stmt = $conn->prepare("
    SELECT p.PRODUCTO_ID, p.NOMBRE
    FROM productos p
    INNER JOIN stock s 
        ON p.PRODUCTO_ID = s.PRODUCTO_ID
    WHERE p.PROVEEDOR_ID = ?
    AND s.ALMACEN_ID = ?
    AND s.UNIDADES > 0
");

$stmt->bind_param("ii", $proveedor_id, $almacen_id);
$stmt->execute();
$result = $stmt->get_result();

echo '<option value="">Selecciona producto</option>';

while ($row = $result->fetch_assoc()) {
    echo "<option value='{$row['PRODUCTO_ID']}'>{$row['NOMBRE']}</option>";
}