<?php
  include "conexion.php";

  $codigo = $_GET['codigo'] ?? '';

  $sql = "SELECT PRODUCTO_ID, NOMBRE, PROVEEDOR_ID 
  FROM productos 
  WHERE CODIGO_BARRAS = ?";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $codigo);
  $stmt->execute();

  $result = $stmt->get_result();

  if ($row = $result->fetch_assoc()) {
    echo json_encode([
      "success" => true,
      "producto" => $row
    ]);
  } else {
    echo json_encode(["success" => false]);
  }
?>