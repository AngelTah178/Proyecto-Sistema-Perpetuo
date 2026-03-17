<?php 
  $base = "/Proyecto-Sistema-Perpetuo"; 
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }

  require_once __DIR__ . "/../conexion.php"; 

  $cart_count = 0;

?>  <!--no sirven las ruitas sin esto KLJASDASLKD m cago en todo-->

<html lang="es">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <title>SISTEMA DE INVENTARIO</title>

    <style>
      a:hover, button:hover {
        opacity: 0.85;
        transform: scale(1.03);
        transition: 0.2s;
      }
    </style>
  </head>

  <body style="font-family: 'Poppins', sans-serif; color: #ffffff;">

    <nav class="navbar navbar-expand-lg fixed-top" style="background-color: #fff; border-radius: 40px; margin: 20px auto; width: 90%; padding: 0.5rem 2rem; box-shadow: 0 2px 6px rgba(0,0,0,0.1);" data-bs-theme="light">

      <div class="container-fluid">
      <!--
        <a class="navbar-brand d-flex align-items-center" href="<?= $base ?>/index.php">
          <img src="<?= $base ?>/assets/logo.png" alt="logo" width="200" class="me-2">
        </a>
      -->

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
          <ul class="navbar-nav align-items-center">
            <li class="nav-item"><a class="nav-link" href="<?= $base ?>/aboutus.php">Acerca de nosotros</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= $base ?>/catalogo.php">Catálogo</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= $base ?>/servicios.php">Servicios</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= $base ?>/contacto.php">Contacto</a></li>

            <?php if (isset($_SESSION['logueado']) && $_SESSION['logueado'] === true): ?>

            <li class="nav-item">
              <a class="btn ms-2" style="background-color:#0d2b3e; color:#fff; border-radius:10px; padding:8px 20px; font-weight:600;" href="<?= $base ?>/user/perfil.php">
                Mi Perfil
              </a>
            </li>

            <!-- Botón Cerrar Sesión -->
            <li class="nav-item">
              <a class="btn ms-2 btn-danger" style="border-radius:10px; padding:8px 20px; font-weight:600;" href="<?= $base ?>/logout.php">
                Cerrar Sesión
              </a>
            </li>

            <?php else: ?>

            <li class="nav-item">
              <a class="btn ms-2" style="background-color:#0d2b3e; color:#fff; border-radius:10px; padding:8px 20px; font-weight:600;" href="<?= $base ?>/iniciarsesion.php">
                Iniciar sesión
              </a>
            </li>

            <?php endif; ?>

          </ul>
        </div>
      </div>
    
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>