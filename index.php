<?php
  session_start();
  include "conexion.php";

  if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
  }

  // Obtener usuarios
  $query = $conn->query("SELECT * FROM usuarios");
  $usuarios = $query->fetch_all(MYSQLI_ASSOC);

  $query = $conn->query("
    SELECT 
      p.*,
      m.NOMBRE AS MARCA,
      c.NOMBRE AS CATEGORIA,
      pr.NOMBRE AS PROVEEDOR,
      l.LOTE_ID
    FROM productos p
    LEFT JOIN marcas m ON p.MARCA_ID = m.MARCA_ID
    LEFT JOIN categorias c ON p.CATEGORIA_ID = c.CATEGORIA_ID
    LEFT JOIN proveedores pr ON p.PROVEEDOR_ID = pr.PROVEEDOR_ID
    LEFT JOIN lotes l ON p.LOTE_ID = l.LOTE_ID
  ");

  $productos = $query->fetch_all(MYSQLI_ASSOC);

  # LOTES
  $lotes = $conn->query("SELECT LOTE_ID FROM lotes");

  # MARCAS
  $marcas = $conn->query("SELECT MARCA_ID, NOMBRE FROM marcas");

  # CATEGORIAS
  $categorias = $conn->query("SELECT CATEGORIA_ID, NOMBRE FROM categorias");

  # PROVEEDORES
  $proveedores = $conn->query("SELECT PROVEEDOR_ID, NOMBRE FROM proveedores");

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $CODIGO_BARRAS = $_POST["CODIGO_BARRAS"];
    $SKU = $_POST["SKU"];
    $NOMBRE = $_POST["NOMBRE"];
    $DESCRIPCION = $_POST["DESCRIPCION"];
    $PRECIO = $_POST["PRECIO"];
    $FECHA_REGISTRO = $_POST["FECHA_REGISTRO"];
    $LOTE_ID = $_POST["LOTE_ID"];
    $MARCA_ID = $_POST["MARCA_ID"];
    $CATEGORIA_ID = $_POST["CATEGORIA_ID"];
    $PROVEEDOR_ID = $_POST["PROVEEDOR_ID"];

    $stmt = $conn->prepare("INSERT INTO productos (CODIGO_BARRAS, SKU, NOMBRE, DESCRIPCION, PRECIO, FECHA_REGISTRO, LOTE_ID, MARCA_ID, CATEGORIA_ID, PROVEEDOR_ID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssdsiiii", $CODIGO_BARRAS, $SKU, $NOMBRE, $DESCRIPCION, $PRECIO, $FECHA_REGISTRO, $LOTE_ID, $MARCA_ID, $CATEGORIA_ID, $PROVEEDOR_ID);
    if ($stmt->execute()) {
      $mensaje = "Producto agregado correctamente";
      exit();
    } else {
      $mensaje = "Error al agregar producto: " . $query;
    }
  }
?>

<html lang="es">
  <head>
    <meta charset="UTF-8">
    <title>Panel Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="CSS/index.css">
  </head>

  <body>

    <?php include 'include/navbar.php'; ?>
    <div class="container mt-4">

      <!-- BIENVENIDA -->
      <div class="mb-4">
        <h2 class="fw-bold">Bienvenido <?php echo $_SESSION['NOMBRE']; ?></h2>
        <p class="text-muted">Panel de administración</p>
      </div>

      <!-- CARDS -->
      <div class="row g-4 mb-4">
        <div class="col-md-4">
          <div class="card dashboard-card shadow-sm">
            <div class="card-body">
              <h5>Total Usuarios</h5>
              <h2><?= count($usuarios); ?></h2>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card dashboard-card shadow-sm">
            <div class="card-body">
              <h5>Admins</h5>
              <h2>
                <?= count(array_filter($usuarios, fn($u) => $u['ROL'] == 'admin')); ?>
              </h2>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card dashboard-card shadow-sm">
            <div class="card-body">
              <h5>Activos</h5>
              <h2 class="text-success">
                <?= count(array_filter($usuarios, fn($u) => $u['ESTADO'] == 'activo')); ?>
              </h2>
            </div>
          </div>
        </div>

      </div>

      <!-- GESTIÓN DE USUARIOS -->
      <div class="card shadow-sm p-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4>Gestión de Usuarios 👥</h4>

          <button class="btn btn-custom" onclick="window.location.href='register.php'">
            <i class="bi bi-person-plus"></i> Registrar usuario
          </button>
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
                <td><?= $contador++; ?></td>
                <td><?= $u['NOMBRE']; ?></td>
                <td><?= $u['APELLIDO']; ?></td>
                <td><?= $u['CORREO']; ?></td>

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

                  <button class="btn btn-sm btn-warning" onclick="window.location.href='editar_usuario.php?id=<?= $u['ID_USUARIO']; ?>'">
                    <i class="bi bi-pencil"></i>
                  </button>

                  <button class="btn btn-sm btn-danger" onclick="eliminarUsuario(<?= $u['ID_USUARIO']; ?>)">
                    <i class="bi bi-trash"></i>
                  </button>

                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle">

            <thead class="table-dark">
              <tr>
                <th>#</th>
                <th>Código de barras</th>
                <th>SKU</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Precio</th>
                <th>Fecha_registro</th>
                <th>Lote</th>
                <th>Marca</th>
                <th>Categoría</th>
                <th>Proveedor</th>
                <th class="text-center">Acciones</th>
              </tr>
            </thead>

            <tbody>
              <?php $contador = 1; ?>
              <?php foreach ($usuarios as $u): ?>
              <tr>
                <td><?= $contador++; ?></td>
                <td><?= $u['CODIGO_BARRAS']; ?></td>
                <td><?= $u['SKU']; ?></td>
                <td><?= $u['NOMBRE']; ?></td>
                <td><?= $u['DESCRIPCION']; ?></td>
                <td><?= $u['PRECIO']; ?></td>
                <td><?= $u['FECHA_REGISTRO']; ?></td>
                <td><?= $u['LOTE']; ?></td>
                <td><?= $u['MARCA']; ?></td>
                <td><?= $u['CATEGORIA']; ?></td>
                <td><?= $u['PROVEEDOR']; ?></td>

                <td class="text-center">
                  <button class="btn btn-sm btn-warning" onclick="window.location.href='editar_usuario.php?id=<?= $u['ID_USUARIO']; ?>'">
                    <i class="bi bi-pencil"></i>
                  </button>

                  <button class="btn btn-sm btn-danger" onclick="eliminarUsuario(<?= $u['ID_USUARIO']; ?>)">
                    <i class="bi bi-trash"></i>
                  </button>

                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- SCRIPTS -->
    <script>
      document.getElementById("buscador").addEventListener("keyup", function() {
        let filtro = this.value.toLowerCase();
        let filas = document.querySelectorAll("tbody tr");

        filas.forEach(fila => {
          fila.style.display = fila.innerText.toLowerCase().includes(filtro) ? "" : "none";
        });
      });

      function eliminarUsuario(id) {
        if (confirm("¿Seguro que quieres eliminar este usuario?")) {
          window.location.href = "eliminar_usuario.php?id=" + id;
        }
      }
    </script>

  </body>
</html>