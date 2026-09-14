<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$roles_permitidos = ["superusuario", "gerencia"];

//Includes
include "../verificar_sesion.php";
require "../conexion.php";
include "../funciones/funciones.php";
include "../funciones/activoinactivo.php";

$estatusFiltroInicial = isset($_GET['estatus']) ? $_GET['estatus'] : '';

$categorias = obtenerRegistros($dbh, "categorias", "id_categoria, nombre_cat", "ASC", "id_categoria", 1000, 1, true);
$marcas = obtenerRegistros($dbh, "marcas", "id_marca, nom_marca", "ASC", "id_marca", 1000, 1, true);
$proveedores = obtenerRegistros($dbh, "proveedores", "id_prov, nombre_prov, contacto_prov", "ASC", "id_prov", 1000, 1, true);
$impuestos = obtenerRegistros($dbh, "impuestos", "idimpuesto, nomimpuesto, tasa", "ASC", "idimpuesto", 1000, 1, true);
$umedidas = obtenerRegistros($dbh, "unidades_med", "id_unidad, nom_unidad", "ASC", "id_unidad", 1000, 1, true);
$productos = obtenerProductosStock($dbh, "productos", "p.id_prod, p.codebar_prod, p.nombre_prod, p.costo_prod, p.precio, p.cant_promo, p.precio_promo, p.stock_minimo, invsuc.stock, p.imagen, p.estatus", "ASC", "p.id_prod", $estatusFiltroInicial);

?>

<div class="containerr">
  <!-- Abrir Modal crear productos -->
  <button class="boton" onclick="abrirModalProducto('crear-modalProducto')">Nuevo</button>

  <!-- Filtro cantidaD -->
  <label class="buscarlabel" for="cantidad-registros" style="margin-left: auto;">Mostrar:</label>
  <select class="buscar--box" id="cantidad-registros" style="width: auto; margin-right: 15px; padding-right: 10px;">
    <option value="8">8</option>
    <option value="25">25</option>
    <option value="50">50</option>
    <option value="-1">Todos</option>
  </select>

  <!-- Input buscar productos -->
  <label class="buscarlabel" for="buscarboxproducto">Buscar:</label>
  <input class="buscar--box" id="buscarboxproducto" type="search" placeholder="¿Qué estas buscando?" autocomplete="off">
</div>

<div class="container_dashboard_tablas" id="productos">
  <h3>Lista de productos</h3>
  <div id="scroll-container">
    <table class="tbl" id="tabla-productos">
      <thead>
        <tr>
          <th>Imagen</th>
          <th>Código de barras</th>
          <th>Nombre</th>
          <th>Costo</th>
          <th>Precio</th>
          <th>Stock Mínimo</th>
          <th>Stock</th>
          <th>Estatus</th>
          <th style="text-align: left;">Acciones</th>
        </tr>
      </thead>

      <tbody id="productos-lista">
        <?php foreach ($productos as $u): ?>
          <?php
          // Nos aseguramos de que sean números reales (floats o enteros)
          $stock_actual = floatval($u['stock'] ?? 0);
          $stock_minimo = floatval($u['stock_minimo'] ?? 0);

          // REGLA ESTRICTA: 
          // - El producto debe tener un stock mínimo configurado mayor a 0
          // - Y sus existencias actuales deben ser MENORES a ese mínimo
          $clase_alerta = ($stock_minimo > 0 && $stock_actual < $stock_minimo) ? 'bajo-stock' : '';
          ?>
          <tr class="producto <?php echo $clase_alerta; ?>" data-estatus="<?php echo ($u['estatus'] == 0) ? 'Activo' : 'Inactivo'; ?>">

            <td data-lable="Imagen"><?php if (!empty($u['imagen'])): ?>
                <img src="<?= htmlspecialchars($u['imagen']) ?>" alt="Imagen de producto" width="40px" height="40px" onerror="this.src='../imgs/default.png'">
              <?php else: ?>
                Sin imagen
              <?php endif; ?>
            </td>
            <td data-lable="Código de barras:"><?php echo htmlspecialchars($u['codebar_prod']); ?>
            <td data-lable="Nombre:"><?php echo htmlspecialchars($u['nombre_prod']); ?></td>
            <td data-lable="Costo:"><?php echo htmlspecialchars($u['costo_prod']); ?></td>
            <td data-lable="Precio:"><?php echo htmlspecialchars($u['precio']); ?></td>
            <td data-lable="Stock Mínimo:"><?php echo htmlspecialchars($u['stock_minimo']); ?></td>
            <td data-lable="Stock:"><?php echo ($u['stock'] !== null) ? htmlspecialchars($u['stock']) : '0'; ?></td>
            <td data-lable="Estatus:">
              <button class="btn <?php echo ($u['estatus'] == 0) ? 'btn-success' : 'btn-danger'; ?>">
                <?php echo ($u['estatus'] == 0) ? 'Activo' : 'Inactivo'; ?>
              </button>
            </td>

            <td data-lable="Acciones" style=" gap: 10px; justify-content: center;">
              <button title="Editar" class="editarProducto fa-solid fa-pen-to-square" data-id="<?php echo $u['id_prod']; ?>"></button>
              <button title="Eliminar" class="eliminarProducto fa-solid fa-trash" data-id="<?php echo $u['id_prod']; ?>"></button>
              <button title="Imprimir Etiqueta QR" class="fa-solid fa-qrcode" style="background:none; border:none; color:#0d6efd; cursor:pointer; font-size:16px;"
                data-nombre="<?php echo htmlspecialchars($u['nombre_prod']); ?>"
                data-precio="<?php echo htmlspecialchars($u['precio']); ?>"
                data-codigo="<?php echo htmlspecialchars($u['codebar_prod'] ?: $u['id_prod']); ?>"
                data-cantpromo="<?php echo htmlspecialchars($u['cant_promo'] ?? 0); ?>"
                data-preciopromo="<?php echo htmlspecialchars($u['precio_promo'] ?? 0); ?>"
                data-tienda="<?php echo htmlspecialchars($_SESSION['nombre_t'] ?? 'Mi tienda'); ?>"
                onclick="lanzarEtiquetaQR(this)"></button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Modal para crear Producto ******************************* -->
  <div id="crear-modalProducto" class="modal" style="display: none;">
    <div class="modal-contentProductos" style="width: 90%; max-width: 1000px; padding: 25px;"> <span title="Cerrar" class="close" onclick="cerrarModalProducto('crear-modalProducto')">&times;</span>
      <h2 class="tittle">Crear Producto</h2>

      <form id="form-crearProducto" onsubmit="validarFormularioProducto(event, 'crear');" enctype="multipart/form-data" novalidate>

        <div class="form-grid-3">

          <div class="seccion-form">
            <h4>1. Información General</h4>

            <div class="form-group">
              <label for="crear-codebar">Código de barras:</label>
              <input type="text" id="crear-codebar" name="codebar" autocomplete="off"
                pattern="[a-zA-Z0-9]+" title="Solo se permiten letras y números."
                oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '')" maxlength="25" required>
            </div>

            <div class="form-group">
              <label for="crear-producto">Nombre:</label>
              <input type="text" id="crear-producto" name="producto" autocomplete="off"
                pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚ\/. ]+" title="Solo se permiten letras, números, espacios, tildes y la barra /"
                oninput="this.value = this.value.replace(/[^a-zA-Z0-9áéíóúÁÉÍÓÚ\/ ]/g, '')" required>
            </div>

            <div class="form-group">
              <label for="crear-descprod">Descripción:</label>
              <input type="text" id="crear-descprod" name="descprod" autocomplete="off"
                pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚ\/. ]+" title="Solo se permiten letras, números, espacios, tildes y la barra /"
                oninput="this.value = this.value.replace(/[^a-zA-Z0-9áéíóúÁÉÍÓÚ\/ ]/g, '')" required>
            </div>

            <div class="form-group">
              <label>Imagen:</label>
              <input type="file" id="imagen" name="imagen" accept="image/*">
            </div>
          </div>

          <div class="seccion-form">
            <h4>2. Clasificación</h4>

            <div class="form-group" id="campo-categoria">
              <label for="crear-categoria">Categoría:</label>
              <select id="crear-categoria" name="categoria" required>
                <option value="">[Selecciona una categoría]</option>
                <?php foreach ($categorias as $categoria): ?>
                  <option value="<?php echo htmlspecialchars($categoria['id_categoria']); ?>">
                    <?php echo htmlspecialchars($categoria['nombre_cat']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group" id="campo-marca">
              <label for="crear-marca">Marca:</label>
              <select id="crear-marca" name="marca" required>
                <option value="">[Selecciona una marca]</option>
                <?php foreach ($marcas as $marca): ?>
                  <option value="<?php echo htmlspecialchars($marca['id_marca']); ?>">
                    <?php echo htmlspecialchars($marca['nom_marca']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label for="crear-proveedor">Proveedor:</label>
              <select id="crear-proveedor" name="proveedor" required>
                <option value="">[Selecciona un proveedor]</option>
                <?php foreach ($proveedores as $proveedor): ?>
                  <option value="<?php echo htmlspecialchars($proveedor['id_prov']); ?>" <?php echo $proveedor['id_prov'] == 0 ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($proveedor['contacto_prov']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label for="crear-umedida">Unidad de Medida:</label>
              <select id="crear-umedida" name="umedida" required>
                <option value="">[Selecciona una medida]</option>
                <?php foreach ($umedidas as $umedid): ?>
                  <option value="<?php echo htmlspecialchars($umedid['id_unidad']); ?>" <?php echo $umedid['id_unidad'] == 0 ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($umedid['nom_unidad']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="seccion-form">
            <h4>3. Precios e Inventario</h4>

            <div class="form-group">
              <label for="crear-impuesto">Impuesto:</label>
              <select id="crear-impuesto" name="idimpuesto" required>
                <option value="">[Selecciona un impuesto]</option>
                <?php foreach ($impuestos as $impuesto): ?>
                  <?php $valorImpuestoDecimal = $impuesto['tasa'] / 100; ?>
                  <option value="<?php echo htmlspecialchars($impuesto['idimpuesto']); ?>" data-tasa="<?php echo htmlspecialchars($valorImpuestoDecimal); ?>">
                    <?php echo htmlspecialchars($impuesto['nomimpuesto']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-containernum">
              <label style="margin-top: 10px; color: #0d6efd; font-weight: bold;">Configurar Precio Especial (Opcional):</label>
              <div class="form-containernum">
                <div class="form-group ladoble">
                  <label for="crear-cant-promo">A partir de (Cantidad):</label>
                  <input type="number" id="crear-cant-promo" name="cant_promo" min="0" value="0" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>
                <div class="form-group ladoble">
                  <label for="crear-precio-promo">Precio del Paquete ($):</label>
                  <input type="number" id="crear-precio-promo" name="precio_promo" step="0.01" min="0" value="0.00">
                </div>
              </div>
              <div class="form-group ladoble">
                <label for="crear-costo_compra">Costo ($):</label>
                <input type="number" id="crear-costo_compra" name="costo_compra" autocomplete="off"
                  pattern="^\d+(\.\d{1,2})?$" step="0.01" min="0" required>
              </div>
              <div class="form-group ladoble">
                <label for="crear-ganancia">Ganancia (%):</label>
                <input type="text" id="crear-ganancia" name="ganancia" autocomplete="off"
                  pattern="^\d+(\.\d{1,2})?$" min="0" required>
              </div>
            </div>

            <div class="form-containernum">
              <div class="form-group ladoble">
                <label for="crear-precio1">Precio Venta:</label>
                <input type="number" id="crear-precio1" name="precio1" autocomplete="off"
                  step="0.01" min="0" readonly required style="background: #eef2f5; font-weight: bold;">
              </div>
              <div class="form-group ladoble">
                <label for="crear-stock_minimo">Stock mínimo:</label>
                <input type="text" id="crear-stock_minimo" name="stock_minimo" autocomplete="off"
                  pattern="^[0-9]" oninput="this.value = this.value.replace(/[^0-9]/g, '')" min="0" value="0" required>
              </div>
            </div>

            <div class="form-group" style="margin-top: 10px;">
              <label for="estatus">Estatus:</label>
              <select id="estatus" name="estatus">
                <?php
                //DocBlock para que confie en que ya viene el arreglo $options
                /** @var array $options */
                /** @var int $selected */
                foreach ($options as $key => $text) {
                ?>
                  <option value="<?= $key ?>" <?= $key === $selected ? 'selected' : '' ?>><?= $text ?></option>
                <?php } ?>
              </select>
            </div>
          </div>

        </div>
        <div style="margin-top: 20px; text-align: right; border-top: 1px solid #ddd; padding-top: 15px;">
          <button type="submit" class="boton-guardar"><i class="fa-solid fa-floppy-disk"></i> Guardar Producto</button>
          <span class="cancelarModal" onclick="cerrarModalProducto('crear-modalProducto')">Cancelar</span>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal editar producto -->
  <div id="editar-modalProducto" class="modal" style="display: none;">
    <div class="modal-contentProductos" style="width: 90%; max-width: 1000px; padding: 25px;">
      <span title="Cerrar" class="close" onclick="cerrarModalProducto('editar-modalProducto')">&times;</span>
      <h2 class="tittle">Editar Producto</h2>

      <form id="form-editarProducto" enctype="multipart/form-data" novalidate>
        <input type="hidden" id="editar-idproducto" name="editar-idproducto" value="" />

        <div class="form-grid-3">

          <div class="seccion-form">
            <h4>1. Información General</h4>

            <div class="form-group">
              <label for="editar-codebar">Código de barras:</label>
              <input type="text" id="editar-codebar" name="codebar" autocomplete="off"
                pattern="[a-zA-Z0-9]+" title="Solo se permiten letras y números."
                oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '')" maxlength="25" required>
            </div>

            <div class="form-group">
              <label for="editar-producto">Nombre:</label>
              <input type="text" id="editar-producto" name="producto" autocomplete="off"
                pattern="[a-zA-ZÀ-ÿ0-9\s]+" title="Solo se permiten letras, números y espacios."
                oninput="this.value = this.value.replace(/[^a-zA-ZÀ-ÿ0-9\s]/g, '')" required>
            </div>

            <div class="form-group">
              <label for="editar-descprod">Descripción:</label>
              <input type="text" id="editar-descprod" name="descprod" autocomplete="off"
                pattern="[a-zA-Z0-9\s]+" title="Solo se permiten letras, espacios y números."
                oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '')" required>
            </div>

            <div class="form-group">
              <label>Imagen:</label>
              <input type="file" id="editar-imagen" name="imagen" accept="image/*">
            </div>
          </div>

          <div class="seccion-form">
            <h4>2. Clasificación</h4>

            <div class="form-group" id="campo-categoria">
              <label for="editar-categoria">Categoría:</label>
              <select id="editar-categoria" name="categoria">
                <option value="">[Selecciona una categoría]</option>
                <?php foreach ($categorias as $categoria): ?>
                  <option value="<?php echo htmlspecialchars($categoria['id_categoria']); ?>">
                    <?php echo htmlspecialchars($categoria['nombre_cat']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group" id="campo-marca">
              <label for="editar-marca">Marca:</label>
              <select id="editar-marca" name="marca">
                <option value="">[Selecciona una marca]</option>
                <?php foreach ($marcas as $marca): ?>
                  <option value="<?php echo htmlspecialchars($marca['id_marca']); ?>">
                    <?php echo htmlspecialchars($marca['nom_marca']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label for="editar-proveedor">Proveedor:</label>
              <select id="editar-proveedor" name="proveedor">
                <option value="">[Selecciona un proveedor]</option>
                <?php foreach ($proveedores as $proveedor): ?>
                  <option value="<?php echo htmlspecialchars($proveedor['id_prov']); ?>">
                    <?php echo htmlspecialchars($proveedor['contacto_prov']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label for="editar-umedida">Unidad de Medida:</label>
              <select id="editar-umedida" name="umedida">
                <option value="">[Selecciona una umedida]</option>
                <?php foreach ($umedidas as $umedida): ?>
                  <option value="<?php echo htmlspecialchars($umedida['id_unidad']); ?>">
                    <?php echo htmlspecialchars($umedida['nom_unidad']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="seccion-form">
            <h4>3. Precios e Inventario</h4>

            <div class="form-group">
              <label for="editar-impuesto">Impuesto:</label>
              <select id="editar-impuesto" name="idimpuesto" required>
                <option value="">[Selecciona un impuesto]</option>
                <?php foreach ($impuestos as $impuesto): ?>
                  <?php $valorImpuestoDecimal = $impuesto['tasa'] / 100; ?>
                  <option value="<?php echo htmlspecialchars($impuesto['idimpuesto']); ?>" data-tasa="<?php echo htmlspecialchars($valorImpuestoDecimal); ?>">
                    <?php echo htmlspecialchars($impuesto['nomimpuesto']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-containernum">
              <label style="margin-top: 10px; color: #0d6efd; font-weight: bold;">Configurar Precio Especial (Opcional):</label>
              <div class="form-containernum">
                <div class="form-group ladoble">
                  <label for="editar-cant-promo">A partir de (Cantidad):</label>
                  <input type="number" id="editar-cant-promo" name="cant_promo" min="0" value="0" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>
                <div class="form-group ladoble">
                  <label for="editar-precio-promo">Precio del Paquete ($):</label>
                  <input type="number" id="editar-precio-promo" name="precio_promo" step="0.01" min="0" value="0.00">
                </div>
              </div>
              <div class="form-group ladoble">
                <label for="editar-costo_compra">Costo ($):</label>
                <input type="number" id="editar-costo_compra" name="costo_compra" autocomplete="off"
                  pattern="^\d+(\.\d{1,2})?$" oninput="this.value = this.value.replace(/[^0-9.]/g, '')" step="0.01" min="0" required>
              </div>
              <div class="form-group ladoble">
                <label for="editar-ganancia">Ganancia (%):</label>
                <input type="text" id="editar-ganancia" name="ganancia" autocomplete="off"
                  pattern="^\d+(\.\d{1,2})?$" oninput="this.value = this.value.replace(/[^0-9.]/g, '')" min="0" required>
              </div>
            </div>

            <div class="form-containernum">
              <div class="form-group ladoble">
                <label for="editar-precio1">Precio Venta:</label>
                <input type="number" id="editar-precio1" name="precio1" autocomplete="off"
                  pattern="^\d+(\.\d{1,3})?$" oninput="this.value = this.value.replace(/[^0-9.]/g, '')" step="0.01" min="0" readonly required style="background: #eef2f5; font-weight: bold;">
              </div>
              <div class="form-group ladoble">
                <label for="editar-stock_minimo">Stock mínimo:</label>
                <input type="text" id="editar-stock_minimo" name="stock_minimo" autocomplete="off"
                  pattern="^[0-9]" oninput="this.value = this.value.replace(/[^0-9]/g, '')" min="0" required>
              </div>
            </div>

            <div class="form-group" style="margin-top: 10px;">
              <label for="editar-estatus">Estatus:</label>
              <select id="editar-estatus" name="estatus">
                <?php
                /** @var array $options */
                /** @var int $selected */
                foreach ($options as $key => $text) {
                ?>
                  <option value="<?= $key ?>" <?= $key === $selected ? 'selected' : '' ?>><?= $text ?></option>
                <?php } ?>
              </select>
            </div>
          </div>

        </div>
        <div style="margin-top: 20px; text-align: right; border-top: 1px solid #ddd; padding-top: 15px;">
          <button type="submit" class="boton-guardar"><i class="fa-solid fa-floppy-disk"></i> Actualizar</button>
          <span class="cancelarModal" onclick="cerrarModalProducto('editar-modalProducto')">Cancelar</span>
        </div>
      </form>
    </div>
  </div>


  <!-- MODAL VISTA PREVIA Y CONFIGURADOR DE ETIQUETAS QR -->
  <div id="modal-etiqueta-qr" class="modal" style="display: none; z-index: 999;">
    <div class="modal-contentProductos" style="width: 90%; max-width: 420px; text-align: center; padding: 20px; margin: 3% auto; background: #fff; border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
      <span title="Cerrar" class="close" onclick="document.getElementById('modal-etiqueta-qr').style.display='none'">&times;</span>
      <h3 style="margin-bottom: 15px; color: #333;"><i class="fa-solid fa-tags"></i> Configurar Etiquetas</h3>

      <!-- PANEL DE OPCIONES DE IMPRESIÓN -->
      <div style="background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 15px; text-align: left; font-size: 13px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
          <div>
            <label style="font-weight: bold; color: #475569;">Tamaño:</label>
            <select id="cfg-tamanio" style="width: 100%; padding: 6px; border-radius: 4px; border: 1px solid #ccc;">
              <option value="4x4">Estándar (4cm x 4cm)</option>
              <option value="2x2">Chica (2cm x 2cm)</option>
            </select>
          </div>
          <div>
            <label style="font-weight: bold; color: #475569;">Cantidad:</label>
            <input type="number" id="cfg-cantidad" value="1" min="1" max="500" style="width: 100%; padding: 6px; border-radius: 4px; border: 1px solid #ccc; text-align: center;">
          </div>
        </div>
        <div>
          <label style="font-weight: bold; color: #475569;">Papel / Impresora:</label>
          <select id="cfg-impresora" style="width: 100%; padding: 6px; border-radius: 4px; border: 1px solid #ccc;">
            <option value="58mm">Mini-printer Térmica (58 mm)</option>
            <option value="80mm">Mini-printer Térmica (80 mm)</option>
            <option value="carta">Impresora Tradicional (Hoja Carta/A4)</option>
          </select>
        </div>
      </div>

      <!-- VISTA PREVIA INDIVIDUAL (Pantalla) -->
      <p style="font-size: 12px; color: #666; margin-bottom: 5px;">Vista Previa (1 unidad):</p>
      <div id="area-impresion-etiqueta" class="etiqueta-anaquel etq-4x4">
        <div id="etq-tienda-preview" class="etq-tienda"><?php echo $_SESSION['nombre_t']; ?></div>
        <div id="etq-nombre" class="etq-producto">Producto</div>
        <div class="etq-precios">
          <span id="etq-precio-normal" class="etq-precio-gran">$0.00</span>
          <div id="etq-caja-promo" class="etq-promo-box" style="display: none;">
            <i class="fa-solid fa-tag"></i> <span id="etq-texto-promo">Promo</span>
          </div>
        </div>
        <canvas id="canvas-qr-etiqueta" style="margin: 5px auto; display: block;"></canvas>
        <div id="etq-sku" class="etq-codigo">COD: 000</div>
      </div>

      <div style="margin-top: 20px; display: flex; gap: 10px; justify-content: center;">
        <button onclick="generarEImprimirMasivo()" class="boton-guardar" style="padding: 10px 20px; font-size: 14px;"><i class="fa-solid fa-print"></i> Imprimir Etiquetas</button>
        <span class="cancelarModal" onclick="document.getElementById('modal-etiqueta-qr').style.display='none'">Cerrar</span>
      </div>
    </div>
  </div>

  <!-- CONTENEDOR OCULTO PARA IMPRESIÓN MASIVA (Solo se ve en la impresora) -->
  <div id="lienzo-impresion-masiva"></div>