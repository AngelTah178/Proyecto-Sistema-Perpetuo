<?php
session_start();
require_once "../conexion.php";

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'] ?? null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = $_POST["nombre"];
    $correo = $_POST["correo"];
    $clave = $_POST["clave"];
    $clave_confirmar = $_POST["clave_confirmar"];

    if (!empty($clave) && $clave !== $clave_confirmar) {
        $_SESSION['mensaje'] = "Las contraseñas no coinciden";
        header("Location: editarPerfil.php");
        exit();
    }


    if (!empty($clave)) {

        $claveHash = password_hash($clave, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("
      UPDATE usuarios 
      SET NOMBRE = ?, CORREO = ?, CONTRASEÑA = ?
      WHERE ID_USUARIO = ?
      ");
        $stmt->bind_param("sssi", $nombre, $correo, $claveHash, $id);

    } else {

        $stmt = $conn->prepare("
      UPDATE usuarios 
      SET NOMBRE = ?, CORREO = ?
      WHERE ID_USUARIO = ?
      ");
        $stmt->bind_param("ssi", $nombre, $correo, $id);
    }

    $stmt->execute();

    echo "<script>
          alert('Perfil actualizado correctamente');
          window.location='../index.php';
        </script>";
    exit();
}

$stmt = $conn->prepare("SELECT ID_USUARIO, NOMBRE, CORREO, ROL FROM usuarios WHERE ID_USUARIO = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Usuario no encontrado";
    exit();
}

$usuario = $result->fetch_assoc();
?>

<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../CSS/editarPerfil.css">
</head>

<body>

    <div class="container mt-5">
        <div class="form-container">

            <div class="text-center mb-3">
                <i class="bi bi-person-circle icono-perfil"></i>
            </div>

            <h2>Editar Perfil</h2>


            <?php if (isset($_SESSION['mensaje'])): ?>
                <div class="alert alert-warning text-center fw-bold">
                    <?= $_SESSION['mensaje']; ?>
                </div>
                <?php unset($_SESSION['mensaje']); ?>
            <?php endif; ?>

            <form method="POST" id="formEditar">

                <div class="mb-3">
                    <label>Nombre</label>
                    <input type="text" name="nombre" class="form-control"
                        value="<?= htmlspecialchars($usuario['NOMBRE']) ?>" required>
                </div>

                <div class="mb-3">
                    <label>Correo</label>
                    <input type="email" name="correo" class="form-control"
                        value="<?= htmlspecialchars($usuario['CORREO']) ?>" required>
                </div>

                <div class="mb-3">
                    <label>Contraseña nueva</label>
                    <input type="password" name="clave" id="clave" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Confirmar contraseña</label>
                    <input type="password" name="clave_confirmar" id="clave_confirmar" class="form-control">
                </div>

                <div class="d-flex justify-content-between">

                    <button type="submit" class="btn btn-submit">
                        Guardar cambios
                    </button>

                    <a href="../index.php" class="btn btn-danger">
                        Cancelar
                    </a>

                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('formEditar').addEventListener('submit', function (e) {
            const clave = document.getElementById('clave').value;
            const confirmar = document.getElementById('clave_confirmar').value;

            if (clave !== "" && clave !== confirmar) {
                alert("Las contraseñas no coinciden");
                e.preventDefault();
            }
        });
    </script>

</body>

</html>