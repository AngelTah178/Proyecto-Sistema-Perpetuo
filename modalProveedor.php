<div class="modal fade" id="modalProveedor" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content modal-producto">
      <form method="POST">
        <input type="hidden" name="form_proveedor" value="1">

        <div class="modal-header">
          <h5 class="modal-title">Registrar proveedor</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="row">

            <div class="col-md-6 mb-3">
              <label class="form-label">Nombre</label>
              <input type="text" name="NOMBRE" class="form-control input-pro" required>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Teléfono</label>
              <input type="text" name="TELEFONO" class="form-control input-pro">
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Correo</label>
              <input type="email" name="CORREO" class="form-control input-pro">
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Estado</label>
              <select name="ESTADO" class="form-control input-pro">
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
              </select>
            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Guardar</button>
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
        </div>

      </form>
    </div>
  </div>
</div>