<?php

session_start();
include "conexion.php";

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: index.php");
    exit;
}

$rol = $_SESSION['ROL'] ?? '';

if ($rol != "admin" && $rol != "empleado") {
    echo "Acceso denegado";
    exit();
}

$producto_id = (int) $_GET['PRODUCTO_ID'];

$stmt = $conn->prepare("DELETE FROM productos WHERE PRODUCTO_ID = ?");
$stmt->bind_param("i", $producto_id);

if ($stmt->execute()) {
    header("Location: inventario.php");
    exit();
} else {
    echo "Error al eliminar: " . $stmt->error;
}

$conn->close();
?>