<?php

session_start();
include "conexion.php";
$rol = $_SESSION['ROL'] ?? '';
#VALIDACION SEGÚN EL ROL DEL USUARIO
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $rol = $_GET['ROL'] ?? '';
    if ($rol == "admin") {
        $query = $conn->prepare("SELECT * FROM usuarios WHERE ROL = 'admin'");
        $query->execute();
        $result = $query->get_result();
        $usuarios = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        echo "No tienes permiso para acceder a esta página.";
        exit();

    }
}
?>