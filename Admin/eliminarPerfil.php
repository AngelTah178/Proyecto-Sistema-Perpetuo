<?php

session_start();
include "../conexion.php";

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: ../index.php");
    exit;
}

$rol = $_SESSION['ROL'] ?? '';

if ($rol != "admin" && $rol) {
    echo "Acceso denegado";
    exit();
}

$producto_id = (int) $_GET['PRODUCTO_ID'];

$stmt = $conn->prepare("DELETE FROM usuarios WHERE ID_USUARIO= ?");
$stmt->bind_param("i", $producto_id);

if ($stmt->execute()) {
    echo "<script>
          alert('Usuario eliminado correctamente');
          window.location='../index.php';
        </script>";
    exit();
} else {
    echo "Error al eliminar: " . $stmt->error;
}

$conn->close();
?>