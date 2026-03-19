<?php
#validamos si hay una sesión activa
session_start();
include "conexion.php";
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}

#si es admin entonces mostramos el apartado usuarios

$query = $conn->prepare("SELECT * FROM usuarios");
$query->execute();
$result = $query->get_result();
$usuarios = $result->fetch_all(MYSQLI_ASSOC);

#mostrando usuarios
?>
<html>

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body>
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
                    <td>
                        <?php echo $u['ID_USUARIO']; ?>
                    </td>
                    <td>
                        <?php echo $u['NOMBRE']; ?>
                    </td>
                    <td>
                        <?php echo $u['APELLIDO']; ?>
                    </td>
                    <td>
                        <?php echo $u['CORREO']; ?>
                    </td>
                    <td>
                        <?php echo $u['ROL']; ?>
                    </td>
                    <td>
                        <?php echo $u['ESTADO']; ?>
                    </td>

                    <td>
                        <button class="btn btn-danger" onclick="window.location.href='empleado.php'">Consultar</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>

</html>