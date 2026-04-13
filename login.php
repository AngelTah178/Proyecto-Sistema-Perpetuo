<?php
#iniciamos sesion
session_start();
#conexion
include "conexion.php";
#valideishon
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $NOMBRE = $_POST["NOMBRE"];
  $CONTRASEÑA = $_POST["CONTRASEÑA"];

  $query = $conn->prepare("SELECT * FROM usuarios WHERE NOMBRE = ?");
  $query->bind_param("s", $NOMBRE);
  $query->execute();
  $result = $query->get_result();

  #SI ES 0 ENTONCES EL USUARIO NO EXISTE.
  if ($result->num_rows == 0) {
    $_SESSION['mensajeUsuario'] = "El usuario no existe";
    header("Location: login.php");
    exit();

  } else {
    $usuario = $result->fetch_assoc();

    if (!password_verify($CONTRASEÑA, $usuario["CONTRASEÑA"])) {
      $_SESSION['mensajeUsuario'] = "Contraseña incorrecta";
      header("Location: login.php");
      exit();
    } else {
      // Guardar datos en sesión
      $_SESSION["ID_USUARIO"] = $usuario["ID_USUARIO"];
      $_SESSION["NOMBRE"] = $usuario["NOMBRE"];
      $_SESSION["CORREO"] = $usuario["CORREO"];
      $_SESSION["ROL"] = $usuario["ROL"];
      $_SESSION["logueado"] = true;

      // Redirigir al index
      header("Location: index.php");
      exit();
    }
  }
}

#NOTA PARA DOCUMENTACIÓN:
#EN LOS SISTEMAS REALES UNICAMENTE SE INGRESA CON USUARIO  Y CONTRASEÑA
?>

<!--Aquí empieza el html-->
<html lang="es">

<head>
  <meta charset="UTF-8">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="CSS/login.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar sesión</title>

  <style>
    a:hover,
    button:hover {
      opacity: 0.85;
      transform: scale(1.03);
      transition: 0.2s;
    }
  </style>
</head>

<body style="background-image: url('assets/inicioreg.jpg'); background-repeat: no-repeat; background-size: cover;">
  <div class="container d-flex justify-content-center align-items-center mt-5" style="min-height: 90vh; ">
    <div class="card p-5 shadow login-card">
      <div class="text-center mb-3">
        <img src="Assets/Logo.png" width="250">
      </div>
      <h3 class="text-center mb-4 login-title">
        Iniciar sesión
      </h3>
      <?php if (isset($_SESSION['mensajeUsuario'])): ?>
        <div class="alert alert-warning">
          <?= $_SESSION['mensajeUsuario']; ?>
        </div>
      <?php endif; ?>
      <?php if (isset($_GET['error'])): ?>

        <div class="alert alert-danger text-center fw-bold" style="border-radius:10px;">
          <?php echo $_GET['error']; ?>
        </div>

      <?php endif; ?>
      <?php if (isset($_GET['ok'])): ?>
        <div class="alert alert-success text-center fw-bold" style="border-radius:10px;">
          <?php echo $_GET['ok']; ?>
        </div>

      <?php endif; ?>
      <form method="POST">
        <div class="mb-3">
          <label class="form-label fw-semibold" style="color:#0d2b3e;">Nombre:</label>
          <input type="text" name="NOMBRE" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold" style="color:#0d2b3e;">Contraseña:</label>
          <input type="password" name="CONTRASEÑA" class="form-control" required>
        </div>

        <div class="d-flex justify-content-center">
          <button class="btn btn-login w-100">
            Entrar
          </button>
        </div>

      </form>
    </div>
  </div>

  <script>
    setTimeout(() => {
      let alert = document.querySelector(".alert");
      if (alert) alert.style.opacity = "0";
    }, 3000);
  </script>

</body>

</html>