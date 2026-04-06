<?php
  session_start();
  include "conexion.php";

  // Validar que el usuario tenga sesión
  if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    echo json_encode(["success" => false, "message" => "Acceso no autorizado"]);
    exit;
  }

  // Validar que hayamos recibido un ID por POST
  if (isset($_POST['id'])) {
    $id = intval($_POST['id']); // intval por seguridad

    // Preparar la consulta para evitar inyecciones SQL
    $stmt = $conn->prepare("DELETE FROM productos WHERE PRODUCTO_ID = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
      // Si se eliminó correctamente, enviamos respuesta exitosa
      echo json_encode(["success" => true]);
    } else {
      // Si hubo error (ej. restricciones de llaves foráneas), devolvemos el error
      echo json_encode(["success" => false, "message" => $stmt->error]);
    }

    $stmt->close();
  } else {
    echo json_encode(["success" => false, "message" => "No se proporcionó un ID válido"]);
  }

  $conn->close();
?>