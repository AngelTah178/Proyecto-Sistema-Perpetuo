<?php
  include "conexion.php";

  $categoria_id = $_POST['categoria_id'] ?? '';

  if ($categoria_id == '') {
    echo '<option value="">Selecciona</option>';
    exit;
  }

  $stmt = $conn->prepare("SELECT LOTE_ID FROM lotes WHERE CATEGORIA_ID = ?");
  $stmt->bind_param("i", $categoria_id);
  $stmt->execute();

  $result = $stmt->get_result();

  echo '<option value="">Selecciona</option>';

  while ($row = $result->fetch_assoc()) {
    echo '<option value="'.$row['LOTE_ID'].'">Lote '.$row['LOTE_ID'].'</option>';
  }

?>