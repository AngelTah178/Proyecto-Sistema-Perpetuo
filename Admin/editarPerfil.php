<?php
session_start();
require_once "../conexion.php";

#credenciales de inicio de sesión
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
  header("Location: ../login.php");
  exit();
}

$id = $_SESSION['ID_USUARIO'];

#procesar edición
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  $nombre = $_POST["nombre"];
  $correo = $_POST["correo"];
  $clave = $_POST["clave"];
  $clave_confirmar = $_POST["clave_confirmar"];

  # Validar contraseñas
  if (!empty($clave) && $clave !== $clave_confirmar) {
    echo "Las contraseñas no coinciden";
    exit();
  }

  # nueva contraseña
  if (!empty($clave)) {

    $claveHash = password_hash($clave, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
      UPDATE usuarios 
      SET NOMBRE = ?, CORREO = ?, CONTRASEÑA = ?
      WHERE ID_USUARIO = ?
    ");
    $stmt->bind_param("sssi", $nombre, $correo, $claveHash, $id);

  } else {

    # stmt sin actualizar contraseña
    $stmt = $conn->prepare("
      UPDATE usuarios 
      SET NOMBRE = ?, CORREO = ?
      WHERE ID_USUARIO = ?
    ");
    $stmt->bind_param("ssi", $nombre, $correo, $id);
  }

  $stmt->execute();

  # Redirigir después de guardar
  header("Location: ../index.php");
  exit();
}

#datos usuario
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

<!DOCTYPE html>
<html lang="es">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editar Usuario</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
  background-image: url('../assets/fondo-form.jpg');
  background-size: cover;
  background-repeat: no-repeat;
  background-position: center;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
}

.form-container {
  background-color: white;
  padding: 30px;
  border-radius: 12px;
  max-width: 500px;
  width: 100%;
}

h2 {
  text-align: center;
  color: #0557a3;
}

.btn-submit {
  background-color: #0557a3;
  color: white;
}

.btn-submit:hover {
  background-color: #063965;
}
</style>
</head>

<body>

<div class="form-container">

<h2>Editar Perfil</h2>

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

<input type="hidden" name="ROL" value="<?= $usuario['ROL'] ?>">

<div class="d-flex justify-content-between">

<button type="submit" class="btn btn-submit"
onclick="return confirm('¿Guardar cambios?');">
Guardar cambios
</button>

<a href="../index.php" class="btn btn-danger">
Cancelar
</a>

</div>

</form>

</div>

<script>
document.getElementById('formEditar').addEventListener('submit', function(e){
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