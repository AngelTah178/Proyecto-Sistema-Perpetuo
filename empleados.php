<?php
#validamos si hay una sesión activa
session_start();
include "conexion.php";
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}


#obtenemos el rol desde la sesión
$rol = $_SESSION['ROL'] ?? '';
#validamos si es un admin
if ($rol != "admin") {
    echo "Acceso denegado. Solo admninistradores";
    exit();
}

#si es admin entonces mostramos el apartado usuarios

$query = $conn->prepare("SELECT * FROM usuarios");
$query->execute();
$result = $query->get_result();
$usuarios = $result->fetch_all(MYSQLI_ASSOC);

#mostrando usuarios
?>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Correo</th>
            <th>Rol</th>
            <th>Estado</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($usuarios as $u): ?>
            <tr>
                <td><?php echo $u['ID_USUARIO']; ?></td>
                <td><?php echo $u['NOMBRE']; ?></td>
                <td><?php echo $u['APELLIDO']; ?></td>
                <td><?php echo $u['CORREO']; ?></td>
                <td><?php echo $u['ROL']; ?></td>
                <td><?php echo $u['ESTADO']; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>