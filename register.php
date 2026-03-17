<html>
<form method="POST">

    <div class="mb-3">
        <label class="form-label fw-semibold" style="color:#0d2b3e;">Nombre</label>
        <input type="text" name="NOMBRE" class="form-control" style="border:2px solid #0d2b3e; height:48px;" required>
    </div>
    <div class="mb-3">
        <label class="form-label fw-semibold" style="color:#0d2b3e;">Apellido</label>
        <input type="text" name="APELLIDO" class="form-control" style="border:2px solid #0d2b3e; height:48px;" required>
    </div>

    <div class="mb-3">
        <label class="form-label fw-semibold" style="color:#0d2b3e;">Correo electrónico</label>
        <input type="email" name="CORREO" class="form-control" style="border:2px solid #0d2b3e; height:48px;" required>
    </div>

    <div class="mb-3">
        <label class="form-label fw-semibold" style="color:#0d2b3e;">Contraseña</label>
        <input type="tel" name="CONTRASEÑA" class="form-control" maxlength="10"
            oninput="this.value = this.value.replace(/[^0-9]/g, '');" style="border:2px solid #0d2b3e; height:48px;"
            required>
    </div>
    <br>
    <div class="mb-3">
        <label class="form-label fw-semibold" style="color:#0d2b3e;">Rol</label>
        <input type="password" name="ROL" class="form-control" style="border:2px solid #0d2b3e; height:48px;" required>
    </div>

    <div class="mb-4">
        <label class="form-label fw-semibold" style="color:#0d2b3e;">Estado</label>
        <input type="password" name="ESTADO" class="form-control" style="border:2px solid #0d2b3e; height:48px;"
            required>
    </div>

    <button class="btn py-2"
        style="background-color:#0d2b3e; color:white; font-weight:700; width:100%; font-size:1.1rem;">
        Registrar usuario
    </button>

</form>
</div>
</div>

</body>

</html>