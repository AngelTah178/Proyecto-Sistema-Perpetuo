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
        SELECT 
            p.PRODUCTO_ID,
            p.NOMBRE,
            p.PROVEEDOR_ID,
            s.ALMACEN_ID
        FROM productos p
        LEFT JOIN stock s 
            ON s.PRODUCTO_ID = p.PRODUCTO_ID
        WHERE p.CODIGO_BARRAS = ?
        AND p.ESTADO = 1
        ORDER BY s.UNIDADES DESC
        LIMIT 1
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