<?php
session_start();
include "conexion.php";
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: index.php");
    exit;
}

#obtenemos el rol desde la sesión
$rol = $_SESSION['ROL'] ?? '';
#validamos si es un admin
if ($rol != "admin") {
    echo "Acceso denegado. Solo admninistradores";
    exit();
}

$query = $conn->prepare("SELECT * FROM usuarios");
$query->execute();
$result = $query->get_result();
$usuarios = $result->fetch_all(MYSQLI_ASSOC);
?>
<html>

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body>
    <?php include 'include/navbar.php'; ?>


</body>

</html>