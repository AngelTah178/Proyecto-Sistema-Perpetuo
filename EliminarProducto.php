<?php
session_start();
include "conexion.php";

// Validar que el usuario tenga sesión
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
  echo json_encode(["success" => false, "message" => "Acceso no autorizado"]);
  exit;
}
$id = $_POST['id'] ?? null;
if (!$id) {
  echo json_encode(["success" => false, "message" => "ID de producto inválido"]);
  exit;
}

//validar movimientos
$stmt = $conn->prepare(
  "SELECT COUNT(*) AS total
    FROM movimientos
    WHERE PRODUCTO_ID = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if ($result['total'] > 0) {
  echo json_encode(
    ["success" => false, "message" => "No puedes eliminar este producto porque tiene movimientos registrados"]
  );
  exit;
}

//si no hay movimientos, elimina producto
$stmt = $conn->prepare(
  "DELETE FROM productos WHERE PRODUCTO_ID = ?"
);
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
  echo json_encode(["success" => true]);
} else {
  echo json_encode(["success" => false, "message" => "Error al eliminar"]);
}
?>