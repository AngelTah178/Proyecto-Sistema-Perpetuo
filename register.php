<?php
session_start();
include "conexion.php";

// Solo admins pueden acceder
if (!isset($_SESSION['logueado']) || $_SESSION['ROL'] !== 'admin') {
  echo "Acceso denegado. Solo administradores.";
  exit();
}

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nombre = trim($_POST['NOMBRE']);
  $apellido_p = trim($_POST['APELLIDO_P']);
  $apellido_m = trim($_POST['APELLIDO_M']);
  $correo = trim($_POST['CORREO']);
  $telefono = trim($_POST['TELEFONO']);
  $estado_emp = $_POST['ESTADO_EMP'];
  $contraseña = $_POST['CONTRASEÑA'];
  $rol = $_POST['ROL'];
  $estado_user = $_POST['ESTADO_USER'];

  if (empty($nombre) || empty($apellido_p) || empty($correo) || empty($contraseña)) {
    $mensaje = "Todos los campos obligatorios deben llenarse.";
  } else {
    // Insertar empleado
    $stmtEmp = $conn->prepare("INSERT INTO empleados (NOMBRE, APELLIDO_P, APELLIDO_M, ESTADO, CORREO, TELEFONO, FECHA_REGISTRO) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmtEmp->bind_param("ssssss", $nombre, $apellido_p, $apellido_m, $estado_emp, $correo, $telefono);

    if ($stmtEmp->execute()) {
      $empleado_id = $conn->insert_id;

      // Insertar usuario
      $hash = password_hash($contraseña, PASSWORD_DEFAULT);
      $nombre_usuario = $nombre; // o puedes concatenar con apellidos
      $apellido_usuario = $apellido_p . " " . $apellido_m;
      $stmtUser = $conn->prepare("INSERT INTO usuarios (EMPLEADO_ID, NOMBRE, APELLIDO, CORREO, CONTRASEÑA, ROL, ESTADO) VALUES (?, ?, ?, ?, ?, ?, ?)");
      $stmtUser->bind_param("issssss", $empleado_id, $nombre_usuario, $apellido_usuario, $correo, $hash, $rol, $estado_user);

      if ($stmtUser->execute()) {
        echo "<script>
          alert('✅ Usuario registrado con éxito');
          window.location.href = 'index.php';
        </script>";
        exit();
      }
    } else {
      $mensaje = "Error al crear empleado: " . $stmtEmp->error;
    }
  }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Crear Empleado y Usuario</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="CSS/registrarUsuario.css">
</head>

<body class="bg-light">
  <div class="container mt-5">
    <div class="card p-4 shadow">
      <h3 class="mb-4 text-center">Crear Empleado y Usuario</h3>

      <?php if ($mensaje != ""): ?>
        <div class="alert alert-info"><?= $mensaje ?></div>
      <?php endif; ?>

      <div class="text-center mb-3">
        <i class="bi bi-person-plus-fill" style="font-size:40px; color:#0d2b3e;"></i>
      </div>

      <form method="POST">

        <div class="row">

          <div class="col-md-6 mb-3">
            <label>Nombre</label>
            <input type="text" name="NOMBRE" class="form-control" required>
          </div>

          <div class="col-md-6 mb-3">
            <label>Apellido Paterno</label>
            <input type="text" name="APELLIDO_P" class="form-control" required>
          </div>

          <div class="col-md-6 mb-3">
            <label>Apellido Materno</label>
            <input type="text" name="APELLIDO_M" class="form-control">
          </div>

          <div class="col-md-6 mb-3">
            <label>Correo</label>
            <input type="email" name="CORREO" class="form-control" required>
          </div>

          <div class="col-md-6 mb-3">
            <label>Teléfono</label>
            <input type="text" name="TELEFONO" class="form-control">
          </div>

          <div class="col-md-6 mb-3">
            <label>Estado empleado</label>
            <select name="ESTADO_EMP" class="form-control">
              <option value="activo">Activo</option>
              <option value="inactivo">Inactivo</option>
            </select>
          </div>

          <div class="col-md-6 mb-3">
            <label>Contraseña</label>
            <input type="password" name="CONTRASEÑA" class="form-control" required>
          </div>

          <div class="col-md-6 mb-3">
            <label>Rol usuario</label>
            <select name="ROL" class="form-control">
              <option value="empleado">Empleado</option>
              <option value="admin">Administrador</option>
            </select>
          </div>

          <div class="col-md-6 mb-4">
            <label>Estado usuario</label>
            <select name="ESTADO_USER" class="form-control">
              <option value="activo">Activo</option>
              <option value="inactivo">Inactivo</option>
            </select>
          </div>

        </div>

        <button type="submit" class="btn btn-register w-100">
          <i class="bi bi-person-check"></i> Crear usuario
        </button>

      </form>
    </div>
  </div>
</body>

</html>