<?php
#iniciamos sesion
session_start();
#conexion
include "conexion.php";
#valideishon
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $CORREO = $_POST["CORREO"];
    $CONTRASEÑA = $_POST["CONTRASEÑA"];

    $query = $conn->prepare("SELECT * FROM usuarios WHERE CORREO = ?");
    $query->bind_param("s", $CORREO);
    $query->execute();
    $result = $query->get_result();

    #SI ES 0 ENTONCES EL USUARIO NO EXISTE.
    if ($result->num_rows == 0) {
        $error = "El usuario no existe";
    } else {
        $usuario = $result->fetch_assoc();

        if ($CONTRASEÑA != $usuario["CONTRASEÑA"]) {
            $error = "CONTRASEÑA INCORRECTA";
        } else {
            $_SESSION["ID_USUARIO"] = $usuario["ID_USUARIO"];
            $_SESSION["NOMBRE"] = $usuario["NOMBRE"];
            $_SESSION["CORREO"] = $usuario["CORREO"];
            $_SESSION["logueado"] = true;

            header("Location: index.php");
            exit();
        }
    }
}

#NOTA PARA DOCUMENTACIÓN:
#EN LOS SISTEMAS REALES UNICAMENTE SE INGRESA CON USUARIO  Y CONTRASEÑA
?>

<!--Aquí empieza el html-->
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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

<body style="background-image: url('assets/inicioreg.jpg'); background-repeat: no-repeat;
background-size: cover;">
    <div class="container d-flex justify-content-center align-items-center mt-5" style="min-height: 90vh; ">
        <div class="card p-5 shadow"
            style="max-width: 450px; width: 100%; border-radius: 15px; border: 3px solid #ffffff;">
            <h3 class="fw-bold text-center mb-4" style="color:#0d2b3e;">
                Iniciar sesión
            </h3>
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
                    <label class="form-label fw-semibold" style="color:#0d2b3e;">Correo:</label>

                    <input type="text" name="CORREO" class="form-control" required style="border:2px solid #0d2b3e;">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" style="color:#0d2b3e;">Contraseña:</label>

                    <input type="text" name="CONTRASEÑA" class="form-control" required
                        style="border:2px solid #0d2b3e;">
                </div>

                <div class="d-flex justify-content-center">
                    <button class="btn" style="background-color:#0d2b3e; color:white; font-weight:600; width:100%;">
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