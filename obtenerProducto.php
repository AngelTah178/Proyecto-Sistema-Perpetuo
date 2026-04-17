<?php
  include "conexion.php";

  header('Content-Type: application/json');

  if (!isset($_GET['id'])) {
    echo json_encode([]);
    exit;
  }

  $id = intval($_GET['id']);

  $stmt = $conn->prepare("
    SELECT 
      PRODUCTO_ID,
      NOMBRE,
      PRECIO,
      MARCA_ID,
      CATEGORIA_ID,
      PROVEEDOR_ID,
      LOTE_ID
    FROM productos
    WHERE PRODUCTO_ID = ?
  ");

  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($fila = $result->fetch_assoc()) {
    echo json_encode($fila);
  } else {
    echo json_encode([]);
  }