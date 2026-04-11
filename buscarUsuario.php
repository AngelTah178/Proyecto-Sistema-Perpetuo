<?php
  include "conexion.php";

  header('Content-Type: application/json');

  $q = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : "";

  if ($q == "") {
    echo json_encode([]);
    exit;
  }

  $sql = "SELECT * FROM usuarios 
  WHERE 
  NOMBRE LIKE '%$q%' OR
  APELLIDO_P LIKE '%$q%' OR
  APELLIDO_M LIKE '%$q%' OR
  CORREO LIKE '%$q%'";

  $result = $conn->query($sql);

  $data = [];

  if ($result) {
    while ($row = $result->fetch_assoc()) {
      $data[] = $row;
    }
  }

  echo json_encode($data);
?>