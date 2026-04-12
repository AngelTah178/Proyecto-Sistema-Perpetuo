<?php
session_start();
include "conexion.php";

// Validar sesión
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
  $_SESSION['mensajeProducto'] = "Acceso no autorizado";
  $_SESSION['tipoProducto'] = "danger";
  header("Location: index.php");
  exit;
}

$id = $_POST['id'] ?? null;

if (!$id) {
  $_SESSION['mensajeProducto'] = "ID de producto inválido";
  $_SESSION['tipoProducto'] = "danger";
  header("Location: index.php");
  exit;
}

// validar movimientos
$stmt = $conn->prepare(
  "SELECT COUNT(*) AS total
   FROM movimientos
   WHERE PRODUCTO_ID = ?
   AND TIPO_ID <> 3"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if ($result['total'] > 0) {
  $_SESSION['mensajeProducto'] = "No puedes eliminar este producto porque tiene movimientos distintos a ALTA";
  $_SESSION['tipoProducto'] = "warning";
  header("Location: index.php");
  exit;
}

// eliminar producto
$stmt = $conn->prepare(
  "DELETE FROM productos WHERE PRODUCTO_ID = ?"
);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
  $_SESSION['mensajeProducto'] = "Producto eliminado correctamente";
  $_SESSION['tipoProducto'] = "success";
} else {
  $_SESSION['mensajeProducto'] = "Error al eliminar producto";
  $_SESSION['tipoProducto'] = "danger";
}

header("Location: index.php");
exit;
?>