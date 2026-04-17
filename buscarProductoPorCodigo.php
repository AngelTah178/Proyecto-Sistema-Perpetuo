<?php
  include "conexion.php";

  header('Content-Type: application/json');

  $codigo = $_GET['codigo'] ?? '';

  if ($codigo === '') {
      echo json_encode([
          "success" => false,
          "message" => "Código vacío"
      ]);
      exit;
  }

  $stmt = $conn->prepare("
      SELECT PRODUCTO_ID, NOMBRE, PROVEEDOR_ID
      FROM productos
      WHERE CODIGO_BARRAS = ?
      AND ESTADO = 1
  ");

  $stmt->bind_param("s", $codigo);
  $stmt->execute();
  $res = $stmt->get_result();

  if ($res->num_rows === 0) {
      echo json_encode([
          "success" => false,
          "message" => "Producto eliminado o inexistente"
      ]);
      exit;
  }

  echo json_encode([
      "success" => true,
      "producto" => $res->fetch_assoc()
  ]);