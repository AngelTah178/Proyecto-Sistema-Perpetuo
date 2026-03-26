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
if ($rol != "admin" && $rol != "empleado") {
    echo "Acceso denegado. Solo admninistradores";
    exit();
}
$query = $conn->query("SELECT * FROM usuarios");
$usuarios = $query->fetch_all(MYSQLI_ASSOC);



?>

<html>

<head>
    <meta charset="UTF-8">
    <title>Empleado</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="CSS/index.css">
</head>

<body>
    <?php include 'include/navbar.php'; ?>

    <div class="card shadow-sm p-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Gestión de Usuarios</h4>

            <button class="btn btn-custom" onclick="window.location.href='register.php'">
                <i class="bi bi-person-plus"></i> Registrar usuario
            </button>
        </div>
    </div>
    <!-- FORMULARIO -->

    <!-- BUSCADOR -->
    <input type="text" id="buscador" class="form-control mb-3" placeholder="Buscar usuario...">

    <!-- TABLA -->
    <div class="table-responsive">
        <table class="table table-hover align-middle">

            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>

            <tbody>
                <?php $contador = 1; ?>
                <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td>
                            <?= $contador++; ?>
                        </td>
                        <td>
                            <?= $u['NOMBRE']; ?>
                        </td>
                        <td>
                            <?= $u['APELLIDO']; ?>
                        </td>
                        <td>
                            <?= $u['CORREO']; ?>
                        </td>

                        <td>
                            <span class="badge <?= $u['ROL'] == 'admin' ? 'bg-primary' : 'bg-secondary'; ?>">
                                <?= $u['ROL']; ?>
                            </span>
                        </td>

                        <td>
                            <span class="badge <?= $u['ESTADO'] == 'activo' ? 'bg-success' : 'bg-danger'; ?>">
                                <?= $u['ESTADO']; ?>
                            </span>
                        </td>

                        <td class="text-center">

                            <button class="btn btn-sm btn-warning"
                                onclick="window.location.href='Admin/editarPerfil.php?id=<?= $u['ID_USUARIO']; ?>'">
                                <i class="bi bi-pencil"></i>
                            </button>

                            <button class="btn btn-sm btn-danger"
                                onclick="if(confirm('¿Eliminar usuario?')) { window.location.href='Admin/eliminarPerfil.php?PRODUCTO_ID=<?= $u['ID_USUARIO']; ?>'; }">
                                <i class="bi bi-trash"></i>

                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>