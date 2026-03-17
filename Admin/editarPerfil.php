<?php
  session_start();
  require_once "../conexion.php";

  if (!isset($_SESSION['ROL']) || $_SESSION['ROL'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
  }

  $id = $_SESSION['ID_USUARIO'];

  $stmt = $conexion->prepare("SELECT id, nombre, correo, ROL FROM usuarios WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();

  if($result->num_rows == 0){
    echo "Usuario no encontrado";
    exit();
  }

  $usuario = $result->fetch_assoc();
?>

<html lang="es">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      body {
        background-image: url('../assets/fondo-form.jpg');
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center; 
        font-family: Arial, sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        margin: 0;
        padding: 0; 
        box-sizing: border-box; 
      }

      nav {
        background-color: #0557a3ff;
      }

      .btn-cerrar {
        color: #0557a3ff;
        background-color: #f0f4f8;
        font-weight: bold;
      }

      .btn-cerrar:hover {
        background-color: white;
      }

      h2 {
        color: #0557a3ff;
        font-weight: bold;
        margin-top: 15px;
        margin-bottom: 15px;
      }

      .form-container {
        background-color: white;
        border-radius: 12px;
        padding: 30px;
        max-width: 600px;
        width: 100%;
        flex: 0 0 auto;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      }

      label {
        font-weight: 500;
        color: #063965ff;
      }

      input.form-control {
        border-radius: 8px;
        border: 1px solid #ccc;
        padding: 8px 12px;
      }

      input.form-control:focus {
        border-color: #0557a3ff;
        box-shadow: 0 0 5px rgba(5,87,163,0.3);
      }

      .btn-submit {
        background-color: #0557a3ff;
        color: white;
        font-weight: bold;
      }

      .btn-submit:hover {
        background-color: #063965ff;
        color: white;
      }

      .btn-cancel {
        background-color: #dc3545;
        color: white;
        font-weight: bold;
      }

      .btn-cancel:hover {
        background-color: #c82333;
        color: white;
      }
    </style>
  </head>
    
  <body>

    <div class="form-container">
      <h2 class="text-center">Editar Perfil</h2>
      <form action="procesar_editar_usuario.php" method="POST" id="formEditar">
        <input type="hidden" name="id" value="<?= $usuario['id'] ?>">

        <div class="mb-4">
          <label for="nombre" class="form-label">Nombre:</label>
          <input type="text" id="nombre" name="nombre" class="form-control" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
        </div>

        <div class="mb-4">
          <label for="correo" class="form-label">Email:</label>
          <input type="correo" id="correo" name="correo" class="form-control" value="<?= htmlspecialchars($usuario['correo']) ?>" required>
        </div>

        <div class="mb-4">
          <label for="clave" class="form-label">Contraseña nueva:</label>
          <input type="password" id="clave" name="clave" class="form-control">
        </div>

        <div class="mb-4">
          <label for="clave_confirmar" class="form-label">Confirmar contraseña:</label>
          <input type="password" id="clave_confirmar" name="clave_confirmar" class="form-control">
        </div>

        <input type="hidden" name="ROL" value="<?= $usuario['ROL'] ?>">

        <div class="d-flex justify-content-between">
          <button type="submit" class="btn btn-success" onclick="return confirm('¿Estás seguro de guardar cambios?');">Guardar cambios</button>
          <a href="panel_administrador.php" class="btn btn-cancel">Cancelar</a>
        </div>
      </form>
    </div>

    <script>
      document.getElementById('formEditar').addEventListener('submit', function(e){
        const clave = document.getElementById('clave').value;
        const claveConfirmar = document.getElementById('clave_confirmar').value;
        if(clave !== "" && clave !== claveConfirmar){
          alert("Las contraseñas no coinciden.");
          e.preventDefault();
        }
      });
    </script>

  </body>
</html>
