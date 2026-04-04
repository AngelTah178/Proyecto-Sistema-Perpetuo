<?php
session_start();
include "conexion.php";


// ================== VALIDAR SESIÓN ==================
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
  header("Location: login.php");
  exit;
}

$rol = $_SESSION['ROL'];

// ================== INSERTAR PRODUCTO ==================
if (isset($_POST['form_producto'])) {

  $CODIGO_BARRAS = $_POST["CODIGO_BARRAS"] ?? null;
  $SKU = $_POST["SKU"] ?? null;
  $NOMBRE = $_POST["NOMBRE"] ?? null;
  $DESCRIPCION = $_POST["DESCRIPCION"] ?? null;
  $PRECIO = $_POST["PRECIO"] ?? 0;
  $FECHA_REGISTRO = $_POST["FECHA_REGISTRO"] ?? null;
  $LOTE_ID = $_POST["LOTE_ID"] ?? null;
  $MARCA_ID = $_POST["MARCA_ID"] ?? null;
  $CATEGORIA_ID = $_POST["CATEGORIA_ID"] ?? null;
  $PROVEEDOR_ID = $_POST["PROVEEDOR_ID"] ?? null;

  // Validación básica
  if ($CODIGO_BARRAS && $NOMBRE && $PRECIO) {

    // Validación de lo duplicado
    $check = $conn->prepare("SELECT PRODUCTO_ID FROM productos WHERE CODIGO_BARRAS = ?");
    $check->bind_param("s", $CODIGO_BARRAS);
    $check->execute();
    $resultado = $check->get_result();

    if ($resultado->num_rows > 0) {
      echo "<script>alert('Este código de barras ya está registrado');</script>";
    } else {

      $stmt = $conn->prepare("
        INSERT INTO productos 
        (CODIGO_BARRAS, SKU, NOMBRE, DESCRIPCION, PRECIO, FECHA_REGISTRO, LOTE_ID, MARCA_ID, CATEGORIA_ID, PROVEEDOR_ID) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
      ");

      $stmt->bind_param(
        "ssssdsiiii",
        $CODIGO_BARRAS,
        $SKU,
        $NOMBRE,
        $DESCRIPCION,
        $PRECIO,
        $FECHA_REGISTRO,
        $LOTE_ID,
        $MARCA_ID,
        $CATEGORIA_ID,
        $PROVEEDOR_ID
      );

      if ($stmt->execute()) {
        header("Location: index.php");
        exit();
      } else {
        echo "Error al insertar producto: " . $stmt->error;
      }
    }
  } else {
    echo "Faltan datos obligatorios del producto";
  }
}
//====================FIN INSERTAR PRODUCTO====================

// ================== ACTUALIZAR PRODUCTO ==================
if (isset($_POST['form_editar_producto'])) {

  $id = $_POST['PRODUCTO_ID'];
  $nombre = $_POST['NOMBRE'];
  $precio = $_POST['PRECIO'];
  $marca = $_POST['MARCA_ID'];
  $categoria = $_POST['CATEGORIA_ID'];
  $proveedor = $_POST['PROVEEDOR_ID'];
  $lote = $_POST['LOTE_ID'];

  $stmt = $conn->prepare("
      UPDATE productos SET 
        NOMBRE = ?, 
        PRECIO = ?, 
        MARCA_ID = ?, 
        CATEGORIA_ID = ?, 
        PROVEEDOR_ID = ?, 
        LOTE_ID = ?
      WHERE PRODUCTO_ID = ?
    ");

  $stmt->bind_param("sdiiiii", $nombre, $precio, $marca, $categoria, $proveedor, $lote, $id);

  if ($stmt->execute()) {
    header("Location: index.php");
    exit();
  } else {
    echo "Error al actualizar producto: " . $stmt->error;
  }
}

// ================== INSERTAR USUARIO ==================
if (isset($_POST['form_usuario'])) {

  $nombre = trim($_POST['NOMBRE'] ?? '');
  $apellido_p = trim($_POST['APELLIDO_P'] ?? '');
  $apellido_m = trim($_POST['APELLIDO_M'] ?? '');
  $correo = trim($_POST['CORREO'] ?? '');
  $telefono = trim($_POST['TELEFONO'] ?? '');
  $estado = $_POST['ESTADO'] ?? 'activo';
  $contraseña = $_POST['CONTRASEÑA'] ?? '';
  $rol_user = $_POST['ROL'] ?? 'empleado';

  if (!empty($nombre) && !empty($apellido_p) && !empty($correo) && !empty($contraseña)) {

    // ENCRIPTAR CONTRASEÑA
    $hash = password_hash($contraseña, PASSWORD_DEFAULT);

    // INSERT CORRECTO
    $stmt = $conn->prepare("
        INSERT INTO usuarios 
        (NOMBRE, APELLIDO_P, APELLIDO_M, CORREO, CONTRASEÑA, ROL, ESTADO) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
      ");

    if (!$stmt) {
      die("Error en prepare: " . $conn->error);
    }

    $stmt->bind_param(
      "sssssss",
      $nombre,
      $apellido_p,
      $apellido_m,
      $correo,
      $hash,
      $rol_user,
      $estado
    );

    if ($stmt->execute()) {
      echo "<script>
          alert('Usuario registrado correctamente');
          window.location='index.php';
        </script>";
      exit();
    } else {
      echo "Error al insertar usuario: " . $stmt->error;
    }

  } else {
    echo "Faltan campos obligatorios del usuario";
  }
}

// ================== PAGINACIÓN ==================
$registros_por_pagina = 10;

// USUARIOS
$pagina = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
if ($pagina < 1)
  $pagina = 1;

$offset = ($pagina - 1) * $registros_por_pagina;

$total_usuarios = $conn->query("SELECT COUNT(*) as total FROM usuarios")->fetch_assoc()['total'];
$total_paginas = ceil($total_usuarios / $registros_por_pagina);

$total_admins = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE ROL = 'admin'")->fetch_assoc()['total'];
$total_activos = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE ESTADO = 'activo'")->fetch_assoc()['total'];

// PRODUCTOS
$pagina_productos = isset($_GET['pagina_productos']) ? (int) $_GET['pagina_productos'] : 1;
if ($pagina_productos < 1)
  $pagina_productos = 1;

$offset_productos = ($pagina_productos - 1) * $registros_por_pagina;

$total_productos = $conn->query("SELECT COUNT(*) as total FROM productos")->fetch_assoc()['total'];
$total_paginas_productos = ceil($total_productos / $registros_por_pagina);

$total_marcas = $conn->query("SELECT COUNT(*) as total FROM marcas")->fetch_assoc()['total'];
$total_proveedores = $conn->query("SELECT COUNT(*) as total FROM proveedores")->fetch_assoc()['total'];
// ================== FIN PAGINACIÓN ==================

// ================== MOVIMIENTOS ==================
$movimientos = $conn->query("
      SELECT 
        m.ID_MOVIMIENTO,
        m.FECHA_REGISTRO,
        m.CANTIDAD,
        tm.MOVIMIENTO,
        u.NOMBRE AS USUARIO,
        p.NOMBRE AS PRODUCTO,
        pr.NOMBRE AS PROVEEDOR,
        m.ALMACEN_ID
      FROM movimientos m
      LEFT JOIN tipo_movimientos tm ON m.TIPO_ID = tm.TIPO_ID
      LEFT JOIN usuarios u ON m.ID_USUARIO = u.ID_USUARIO
      LEFT JOIN productos p ON m.PRODUCTO_ID = p.PRODUCTO_ID
      LEFT JOIN proveedores pr ON m.PROVEEDOR_ID = pr.PROVEEDOR_ID
      ORDER BY m.ID_MOVIMIENTO DESC
    ");
// ================== FIN MOVIMIENTOS ==================

// ================== CONSULTAS ==================
$usuarios = $conn->query("SELECT * FROM usuarios LIMIT $offset, $registros_por_pagina")->fetch_all(MYSQLI_ASSOC);

$productos = $conn->query("
      SELECT 
        p.*, 
        m.NOMBRE AS MARCA, 
        c.NOMBRE AS CATEGORIA, 
        pr.NOMBRE AS PROVEEDOR
      FROM productos p
      LEFT JOIN marcas m ON p.MARCA_ID = m.MARCA_ID
      LEFT JOIN categorias c ON p.CATEGORIA_ID = c.CATEGORIA_ID
      LEFT JOIN proveedores pr ON p.PROVEEDOR_ID = pr.PROVEEDOR_ID
      LIMIT $offset_productos, $registros_por_pagina
    ")->fetch_all(MYSQLI_ASSOC);

$lotes = $conn->query("SELECT LOTE_ID FROM lotes");
$marcas = $conn->query("SELECT MARCA_ID, NOMBRE FROM marcas");
$categorias = $conn->query("SELECT CATEGORIA_ID, NOMBRE FROM categorias");
$proveedores = $conn->query("SELECT PROVEEDOR_ID, NOMBRE FROM proveedores");
// ================== FIN DE CONSULTAS ==================

#TOKEN PARA CONOCER MOVIMIENTOS
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
      <?php if ($rol == 'admin'): ?>
        <div class="col-md-4">
          <div class="card dashboard-card shadow-sm">
            <div class="card-body">
              <h5>Total Usuarios</h5>
              <h2><?= $total_usuarios; ?></h2>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card dashboard-card shadow-sm">
            <div class="card-body">
              <h5>Admins</h5>
              <h2>
                <?= $total_admins; ?>
              </h2>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card dashboard-card shadow-sm">
            <div class="card-body">
              <h5>Activos</h5>
              <h2 class="text-success">
                <?= $total_activos; ?>
              </h2>
            </div>
          </div>
        </div>

      <?php else: ?>

        <div class="col-md-4">
          <div class="card dashboard-card shadow-sm">
            <div class="card-body">
              <h5>Total Productos</h5>
              <h2><?= $total_productos; ?></h2>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card dashboard-card shadow-sm">
            <div class="card-body">
              <h5>Marcas</h5>
              <h2><?= $total_marcas; ?></h2>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card dashboard-card shadow-sm">
            <div class="card-body">
              <h5>Proveedores</h5>
              <h2><?= $total_proveedores; ?></h2>
            </div>
          </div>
        </div>

      <?php endif; ?>


    </div>

    <!-- TABLAS DE USUARIOS / PRODUCTOS -->
    <div class="card shadow-sm p-4">
      <?php if ($rol == 'admin'): ?>
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4>Gestión de Usuarios</h4>

          <button class="btn btn-custom" data-bs-toggle="modal" data-bs-target="#modalUsuario">
            Registrar usuario
          </button>

          <!--Modal de registro de usuarios-->
          <div class="modal fade" id="modalUsuario" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
              <div class="modal-content modal-producto">

                <form method="POST">
                  <input type="hidden" name="form_usuario" value="1">
                  <!-- HEADER -->
                  <div class="modal-header">
                    <h5 class="modal-title">
                      Agregar usuario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>

                  <!-- BODY -->
                  <div class="modal-body">

                    <div class="row">

                      <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="NOMBRE" class="form-control input-pro" required>
                      </div>

                      <div class="col-md-6 mb-3">
                        <label class="form-label">Apellido paterno</label>
                        <input type="text" name="APELLIDO_P" class="form-control input-pro" required>
                      </div>

                      <div class="col-md-6 mb-3">
                        <label class="form-label">Apellido materno</label>
                        <input type="text" name="APELLIDO_M" class="form-control input-pro" required>
                      </div>

                      <div class="col-md-6 mb-3">
                        <label class="form-label">Correo</label>
                        <input type="email" name="CORREO" class="form-control input-pro" required>
                      </div>

                      <div class="col-12 mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="text" name="CONTRASEÑA" class="form-control input-pro">
                      </div>

                      <div class="col-md-6 mb-3">
                        <label>Rol usuario</label>
                        <select name="ROL" class="form-control">
                          <option value="empleado">Empleado</option>
                          <option value="admin">Administrador</option>
                        </select>
                      </div>

                      <div class="col-md-6 mb-3">
                        <label>Estado empleado</label>
                        <select name="ESTADO" class="form-control">
                          <option value="activo">Activo</option>
                          <option value="inactivo">Inactivo</option>
                        </select>
                      </div>



                    </div>

                  </div>

                  <div class="modal-footer">
                    <button type="submit" class="btn ms-2 btn-success"
                      style="border-radius:10px; padding:8px 20px; font-weight:600;">
                      Guardar
                    </button>
                    <button type="button" class="btn ms-2 btn-danger"
                      style="border-radius:10px; padding:8px 20px; font-weight:600;" data-bs-dismiss="modal">
                      Cancelar
                    </button>
                  </div>

                </form>

              </div>
            </div>
          </div>
        </div>

        <!-- BUSCADOR -->
        <input type="text" id="buscador" class="form-control mb-3" placeholder="Buscar usuario...">

        <!-- TABLA USUARIOS-->
        <div class="table-responsive">
          <table class="table table-hover align-middle">

            <thead class="table-dark">
              <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Apellido Paterno</th>
                <th>Apellido Materno</th>
                <th>Correo</th>
                <th>Rol</th>
                <th>Estado</th>
                <th class="text-center">Acciones</th>
              </tr>
            </thead>

            <tbody>
              <?php $contador = $offset + 1; ?>
              <?php foreach ($usuarios as $u): ?>
                <tr>
                  <td>
                    <?= $contador++; ?>
                  </td>
                  <td>
                    <?= $u['NOMBRE']; ?>
                  </td>
                  <td>
                    <?= $u['APELLIDO_P']; ?>
                  </td>
                  <td>
                    <?= $u['APELLIDO_M']; ?>
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
          <nav class="d-flex justify-content-center align-items-center gap-2 mt-3">
            <a class="btn btn-light <?= ($pagina <= 1) ? 'disabled' : '' ?>" href="?pagina=<?= $pagina - 1 ?>">
              &lt;
            </a>

            <span class="fw-bold"><?= $pagina ?></span>

            <a class="btn btn-light <?= ($pagina >= $total_paginas) ? 'disabled' : '' ?>"
              href="?pagina=<?= $pagina + 1 ?>">
              &gt;
            </a>
          </nav>
        </div>
      <?php endif; ?>

      <!-- GESTIÓN DE PRODUCTOS -->
      <?php if ($rol != 'admin'): ?>
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4>Gestión de Productos</h4>

          <button class="btn btn-custom" data-bs-toggle="modal" data-bs-target="#modalProducto">
            Registrar producto
          </button>

          <!--MODAL REGISTRO DE USUARIO -->
          <div class="modal fade" id="modalProducto" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
              <div class="modal-content modal-producto">

                <form method="POST">
                  <input type="hidden" name="form_producto" value="1">
                  <!-- HEADER -->
                  <div class="modal-header">
                    <h5 class="modal-title">
                      Agregar producto
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>

                  <!-- BODY -->
                  <div class="modal-body">

                    <div class="row">

                      <div class="col-md-6 mb-3">
                        <label class="form-label">Código de barras</label>
                        <input type="text" name="CODIGO_BARRAS" class="form-control input-pro" required>
                      </div>

                      <div class="col-md-6 mb-3">
                        <label class="form-label">SKU</label>
                        <input type="text" name="SKU" class="form-control input-pro">
                      </div>

                      <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="NOMBRE" class="form-control input-pro" required>
                      </div>

                      <div class="col-md-6 mb-3">
                        <label class="form-label">Precio</label>
                        <input type="number" name="PRECIO" class="form-control input-pro" required>
                      </div>

                      <div class="col-12 mb-3">
                        <label class="form-label">Descripción</label>
                        <input type="text" name="DESCRIPCION" class="form-control input-pro">
                      </div>

                      <div class="col-md-6 mb-3">
                        <label class="form-label">Fecha de registro</label>
                        <input type="date" name="FECHA_REGISTRO" class="form-control input-pro" required>
                      </div>

                      <div class="col-md-6 mb-3">
                        <label class="form-label">Lote</label>
                        <select name="LOTE_ID" class="form-control input-pro" required>
                          <option value="">Selecciona</option>
                          <?php while ($l = $lotes->fetch_assoc()): ?>
                            <option value="<?= $l['LOTE_ID'] ?>">Lote <?= $l['LOTE_ID'] ?></option>
                          <?php endwhile; ?>
                        </select>
                      </div>

                      <div class="col-md-6 mb-3">
                        <label class="form-label">Marca</label>
                        <select name="MARCA_ID" class="form-control input-pro" required>
                          <option value="">Selecciona</option>
                          <?php while ($m = $marcas->fetch_assoc()): ?>
                            <option value="<?= $m['MARCA_ID'] ?>"><?= $m['NOMBRE'] ?></option>
                          <?php endwhile; ?>
                        </select>
                      </div>

                      <div class="col-md-6 mb-3">
                        <label class="form-label">Categoría</label>
                        <select name="CATEGORIA_ID" class="form-control input-pro" required>
                          <option value="">Selecciona</option>
                          <?php while ($c = $categorias->fetch_assoc()): ?>
                            <option value="<?= $c['CATEGORIA_ID'] ?>"><?= $c['NOMBRE'] ?></option>
                          <?php endwhile; ?>
                        </select>
                      </div>

                      <div class="col-md-12 mb-3">
                        <label class="form-label">Proveedor</label>
                        <select name="PROVEEDOR_ID" class="form-control input-pro" required>
                          <option value="">Selecciona</option>
                          <?php while ($p = $proveedores->fetch_assoc()): ?>
                            <option value="<?= $p['PROVEEDOR_ID'] ?>"><?= $p['NOMBRE'] ?></option>
                          <?php endwhile; ?>
                        </select>
                      </div>

                    </div>

                  </div>

                  <div class="modal-footer">
                    <button type="submit" class="btn ms-2 btn-success"
                      style="border-radius:10px; padding:8px 20px; font-weight:600;">
                      Guardar
                    </button>
                    <button type="button" class="btn ms-2 btn-danger"
                      style="border-radius:10px; padding:8px 20px; font-weight:600;" data-bs-dismiss="modal">
                      Cancelar
                    </button>
                  </div>

                </form>

              </div>
            </div>
          </div>
        </div>

        <!-- BUSCADOR -->
        <input type="text" id="buscador" class="form-control mb-3" placeholder="Buscar producto...">
        <div id="resultado"></div>

        <!--TABLA PRODUCTOS -->
        <div class="table-responsive" id="tablaProductos">
          <table class="table table-hover align-middle">

            <thead class="table-dark">
              <tr>
                <th>#</th>
                <th>Código de barras</th>
                <th>SKU</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Precio</th>
                <th>Fecha de registro</th>
                <th>Lote</th>
                <th>Marca</th>
                <th>Categoría</th>
                <th>Proveedor</th>
                <th class="text-center">Acciones</th>
              </tr>
            </thead>

            <tbody id="tbodyProductos">
              <?php $contador = $offset_productos + 1; ?>
              <?php foreach ($productos as $p): ?>
                <tr>
                  <td>
                    <?= $contador++; ?>
                  </td>
                  <td>
                    <?= $p['CODIGO_BARRAS']; ?>
                  </td>
                  <td>
                    <?= $p['SKU']; ?>
                  </td>
                  <td>
                    <?= $p['NOMBRE']; ?>
                  </td>
                  <td>
                    <?= $p['DESCRIPCION']; ?>
                  </td>
                  <td>
                    <?= $p['PRECIO']; ?>
                  </td>
                  <td>
                    <?= $p['FECHA_REGISTRO']; ?>
                  </td>
                  <td>
                    <?= $p['LOTE_ID']; ?>
                  </td>
                  <td>
                    <?= $p['MARCA']; ?>
                  </td>
                  <td>
                    <?= $p['CATEGORIA']; ?>
                  </td>
                  <td>
                    <?= $p['PROVEEDOR']; ?>
                  </td>

                  <td class="text-center">
                    <button class="btn btn-sm btn-warning" onclick='abrirModalEditar(<?= json_encode($p) ?>)'>
                      <i class="bi bi-pencil"></i>
                    </button>
                    <!-- MODAL EDITAR PRODUCTO -->
                    <div class="modal fade" id="modalEditarProducto" tabindex="-1">
                      <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">

                          <form method="POST">
                            <input type="hidden" name="form_editar_producto" value="1">
                            <input type="hidden" name="PRODUCTO_ID" id="edit_id">

                            <div class="modal-header">
                              <h5 class="modal-title">Editar producto</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                              <div class="row">

                                <div class="col-md-6 mb-3">
                                  <label>Nombre</label>
                                  <input type="text" name="NOMBRE" id="edit_nombre" class="form-control">
                                </div>

                                <div class="col-md-6 mb-3">
                                  <label>Precio</label>
                                  <input type="number" name="PRECIO" id="edit_precio" class="form-control">
                                </div>

                                <div class="col-md-6 mb-3">
                                  <label>Marca</label>
                                  <select name="MARCA_ID" id="edit_marca" class="form-control">
                                    <?php foreach ($marcas as $m): ?>
                                      <option value="<?= $m['MARCA_ID'] ?>"><?= $m['NOMBRE'] ?></option>
                                    <?php endforeach; ?>
                                  </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                  <label>Categoría</label>
                                  <select name="CATEGORIA_ID" id="edit_categoria" class="form-control">
                                    <?php foreach ($categorias as $c): ?>
                                      <option value="<?= $c['CATEGORIA_ID'] ?>"><?= $c['NOMBRE'] ?></option>
                                    <?php endforeach; ?>
                                  </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                  <label>Proveedor</label>
                                  <select name="PROVEEDOR_ID" id="edit_proveedor" class="form-control">
                                    <?php foreach ($proveedores as $p): ?>
                                      <option value="<?= $p['PROVEEDOR_ID'] ?>"><?= $p['NOMBRE'] ?></option>
                                    <?php endforeach; ?>
                                  </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                  <label>Lote</label>
                                  <select name="LOTE_ID" id="edit_lote" class="form-control">
                                    <?php foreach ($lotes as $l): ?>
                                      <option value="<?= $l['LOTE_ID'] ?>"><?= $l['LOTE_ID'] ?></option>
                                    <?php endforeach; ?>
                                  </select>
                                </div>

                              </div>
                            </div>

                            <div class="modal-footer">
                              <button type="submit" class="btn btn-success">Guardar cambios</button>
                              <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                            </div>

                          </form>

                        </div>
                      </div>
                    </div>

                    <button class="btn btn-sm btn-danger" onclick="eliminarProducto(<?= $u['PRODUCTO_ID']; ?>)">
                      <i class="bi bi-trash"></i>
                    </button>

                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <nav class="d-flex justify-content-center align-items-center gap-2 mt-3">
            <a class="btn btn-light <?= ($pagina_productos <= 1) ? 'disabled' : '' ?>"
              href="?pagina_productos=<?= $pagina_productos - 1 ?>">
              &lt;
            </a>

            <span class="fw-bold"><?= $pagina_productos ?></span>

            <a class="btn btn-light <?= ($pagina_productos >= $total_paginas_productos) ? 'disabled' : '' ?>"
              href="?pagina_productos=<?= $pagina_productos + 1 ?>">
              &gt;
            </a>
          </nav>
        </div>
      <?php endif; ?>
    </div>

    <!-- GESTIÓN DE MOVIMIENTOS -->

    <?php if ($rol == 'empleado'): ?>
      <div class="card shadow-sm p-4 mt-4">
        <h4>Gestión de Movimientos</h4>

        <div class="table-responsive">
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalReporte">
            Generar reporte
          </button>
          <!---INICIO MODAL REPORTES--->
        <div class="modal fade" id="modalReporte">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">

              <div class="modal-header">
                <h5>Generar Reporte</h5>
              </div>

              <div class="modal-body">

                <!-- FILTROS -->
                  <div class="row">
                    <div class="col">
                      <label>Fecha inicio</label>
                      <input type="date" id="fecha_inicio" class="form-control">
                    </div>

                    <div class="col">
                      <label>Fecha fin</label>
                      <input type="date" id="fecha_fin" class="form-control">
                    </div>

                    <div class="col">
                      <label>Tipo</label>
                      <select id="tipo" class="form-control">
                        <option value="">Todos</option>
                        <option value="1">Entradas</option>
                        <option value="2">Salidas</option>
                      </select>
                    </div>
                  </div>

                  <br>

                  <button class="btn btn-success" onclick="buscarReporte()">
                    Buscar
                  </button>

                  <!-- TABLA -->
                  <div id="tabla_reporte"></div>

                </div>
                
                <button class="btn btn-success">Imprimir</button>

                <button type="button" class="btn ms-2 btn-danger"
                  style="border-radius:10px; padding:8px 20px; font-weight:600;" data-bs-dismiss="modal">
                  Cancelar
                </button>

              </div>
            </div>
          </div>
          <!----FIN MODAL REPORTES--->
        <table class="table table-hover align-middle">

          <thead class="table-dark">
            <tr>
              <th>#</th>
              <th>Fecha</th>
              <th>Tipo</th>
              <th>Producto</th>
              <th>Cantidad</th>
              <th>Usuario</th>
              <th>Proveedor</th>
              <th>Almacén</th>
            </tr>
          </thead>

          <tbody>
            <?php foreach ($movimientos as $m): ?>
            <tr>
              <td>
                <?= $m['ID_MOVIMIENTO'] ?>
              </td>
              <td>
                <?= $m['FECHA_REGISTRO'] ?>
              </td>

              <td>
                <span class="badge <?= $m['MOVIMIENTO'] == 'ENTRADA' ? 'bg-success' : 'bg-danger' ?>">
                  <?= $m['MOVIMIENTO'] ?>
                </span>
              </td>

              <td>
                <?= $m['PRODUCTO'] ?>
              </td>
              <td>
                <?= $m['CANTIDAD'] ?>
              </td>
              <td>
                <?= $m['USUARIO'] ?>
              </td>
              <td>
                <?= $m['PROVEEDOR'] ?>
              </td>
              <td>
                <?= $m['ALMACEN_ID'] ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>

        </table>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!--AUN NO SE QUE CHOW CON ESTOS BOTONES -->
  <button class="btn btn-sm btn-warning" onclick="window.location.href='Stock.php'">
    stock de producto
  </button>
  <button class="btn btn-custom" onclick="window.location.href='GenerarCompra.php'">
    Generar orden de compra
  </button>


  <!-- SCRIPTS -->
  <script>
    const input = document.getElementById("buscador");
    input.addEventListener("keyup", function () {

      let valor = input.value.trim();

      if (valor === "") {
        location.reload(); // vuelve a la tabla normal
        return;
      }

      fetch("buscarGeneral.php?q=" + valor)
        .then(res => res.json())
        .then(data => {

          let html = "";

          if (data.length === 0) {
            html = `
                <tr>
                  <td colspan="12" class="text-center text-danger">
                    No se encontraron resultados
                  </td>
                </tr>
              `;
          } else {

            data.forEach((p, index) => {
              html += `
                  <tr>
                    <td>${index + 1}</td>
                    <td>${p.CODIGO_BARRAS}</td>
                    <td>${p.SKU}</td>
                    <td>${p.NOMBRE}</td>
                    <td>${p.DESCRIPCION}</td>
                    <td>${p.PRECIO}</td>
                    <td>${p.FECHA_REGISTRO}</td>
                    <td>${p.LOTE_ID}</td>
                    <td>${p.MARCA}</td>
                    <td>${p.CATEGORIA}</td>
                    <td>${p.PROVEEDOR}</td>

                    <td class="text-center">
                      <button class="btn btn-sm btn-warning"
                        onclick="window.location.href='editarProducto.php?id=${p.PRODUCTO_ID}'">
                        <i class="bi bi-pencil"></i>
                      </button>

                      <button class="btn btn-sm btn-danger"
                        onclick="if(confirm('¿Eliminar producto?')) window.location.href='EliminarProducto.php?id=${p.PRODUCTO_ID}'">
                        <i class="bi bi-trash"></i>
                      </button>
                    </td>
                  </tr>
                `;
            });

          }

          document.getElementById("tbodyProductos").innerHTML = html;

        });

    });

    function abrirModalEditar(producto) {
      document.getElementById("edit_id").value = producto.PRODUCTO_ID;
      document.getElementById("edit_nombre").value = producto.NOMBRE;
      document.getElementById("edit_precio").value = producto.PRECIO;

      document.getElementById("edit_marca").value = producto.MARCA_ID;
      document.getElementById("edit_categoria").value = producto.CATEGORIA_ID;
      document.getElementById("edit_proveedor").value = producto.PROVEEDOR_ID;
      document.getElementById("edit_lote").value = producto.LOTE_ID;

      let modal = new bootstrap.Modal(document.getElementById('modalEditarProducto'));
      modal.show();
    }

    function eliminarUsuario(id) {
      if (confirm("¿Seguro que quieres eliminar este usuario?")) {
        window.location.href = "eliminar_usuario.php?id=" + id;
      }
    }

    ///FUNCION PARA BUSCAR REPORTE BY JACK NICHOLSON
    function buscarReporte() {
      let inicio = document.getElementById("fecha_inicio").value;
      let fin = document.getElementById("fecha_fin").value;
      let tipo = document.getElementById("tipo").value;

      fetch(`reporte.php?inicio=${inicio}&fin=${fin}&tipo=${tipo}`)
        .then(res => res.text())
        .then(data => {
          document.getElementById("tabla_reporte").innerHTML = data;
        });
    }

  </script>

</body>

</html>