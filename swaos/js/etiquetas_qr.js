// =========================================================
// MÓDULO SWAOS: GENERADOR MASIVO DE ETIQUETAS QR
// =========================================================

let productoActualQR = null; // Memoria del producto seleccionado

window.lanzarEtiquetaQR = function (boton) {
  // 1. Guardamos los datos en memoria
  productoActualQR = {
    nombre: boton.getAttribute("data-nombre"),
    precio: parseFloat(boton.getAttribute("data-precio")) || 0,
    codigo: boton.getAttribute("data-codigo"),
    cantPromo: parseInt(boton.getAttribute("data-cantpromo")) || 0,
    precioPromo: parseFloat(boton.getAttribute("data-preciopromo")) || 0,
    tienda: boton.getAttribute("data-tienda") || "Mi tienda", // Captura el valor de PHP
  };

  // 2. Llenamos la vista previa individual (Por defecto 4x4)
  document.getElementById("etq-tienda-preview").innerText = productoActualQR.tienda; // Asegúrate de ponerle este ID al span de la tienda en tu modal si no lo tiene
  document.getElementById("etq-nombre").innerText = productoActualQR.nombre;
  document.getElementById("etq-precio-normal").innerText =
    "$" + productoActualQR.precio.toFixed(2);
  document.getElementById("etq-sku").innerText =
    "COD: " + productoActualQR.codigo;

  let cajaPromo = document.getElementById("etq-caja-promo");
  if (cajaPromo) {
    if (productoActualQR.cantPromo > 0 && productoActualQR.precioPromo > 0) {
      document.getElementById("etq-texto-promo").innerText =
        `${productoActualQR.cantPromo} x $${productoActualQR.precioPromo.toFixed(2)}`;
      cajaPromo.style.display = "inline-block";
    } else {
      cajaPromo.style.display = "none";
    }
  }

  // 3. Dibujamos el QR en la vista previa
  if (typeof QRious !== "undefined") {
    new QRious({
      element: document.getElementById("canvas-qr-etiqueta"),
      value: String(productoActualQR.codigo),
      size: 130,
      level: "M",
    });
  }

  // 4. Abrimos modal
  document.getElementById("modal-etiqueta-qr").style.display = "block";
};

// =========================================================
// MOTOR DE IMPRESIÓN MASIVA AISLADA (SWAOS PRO)
// =========================================================
window.generarEImprimirMasivo = function() {
  if (!productoActualQR) return;

  const tamanio = document.getElementById('cfg-tamanio').value; // '4x4' o '2x2'
  const cantidad = parseInt(document.getElementById('cfg-cantidad').value) || 1;
  const impresora = document.getElementById('cfg-impresora').value; // '58mm', '80mm', 'carta'

  // 1. ANCHOS Y COLUMNACIONES SEGÚN EL PAPEL
  let anchoContenedor = '100%';
  let maxAncho = '210mm';
  let anchoEtiqueta = tamanio === '4x4' ? '40mm' : '20mm';
  let flexBasis = 'auto';

  if (impresora === '58mm') {
    anchoContenedor = '48mm';
    maxAncho = '48mm';
    if (tamanio === '2x2') flexBasis = '46%'; // 2 columnas en 58mm
  } else if (impresora === '80mm') {
    anchoContenedor = '72mm';
    maxAncho = '72mm';
    if (tamanio === '2x2') flexBasis = '31%'; // 3 columnas en 80mm
  }

  // 2. GENERAMOS LAS ETIQUETAS Y LAS CONVERTIMOS A IMÁGENES BASE64
  // (Convertir el QR a imagen garantiza que no se pierda al pasar a la ventana de impresión)
  let etiquetasHTML = '';

  for (let i = 0; i < cantidad; i++) {
    // Creamos un canvas temporal en memoria para generar el QR
    let canvasTemp = document.createElement('canvas');
    if (typeof QRious !== 'undefined') {
      new QRious({
        element: canvasTemp,
        value: String(productoActualQR.codigo),
        size: tamanio === '2x2' ? 70 : 130,
        level: 'M'
      });
    }
    let imagenQR = canvasTemp.toDataURL('image/png');

    etiquetasHTML += `
      <div class="etiqueta etq-${tamanio}" style="${flexBasis !== "auto" ? `flex: 0 0 ${flexBasis}; max-width: ${flexBasis};` : ""}">
        <div class="tienda">${productoActualQR.tienda}</div>
        <div class="producto">${productoActualQR.nombre}</div>
        <div class="precios">
          <span class="precio-gran">$${productoActualQR.precio.toFixed(2)}</span>
          ${
            productoActualQR.cantPromo > 0 && tamanio === "4x4"
              ? `<div class="promo-box">${productoActualQR.cantPromo}x$${productoActualQR.precioPromo.toFixed(2)}</div>`
              : ""
          }
        </div>
        <img src="${imagenQR}" class="qr-img" />
        <div class="codigo">COD: ${productoActualQR.codigo}</div>
      </div>
    `;
  }

  // 3. CREAMOS LA VENTANA DE IMPRESIÓN LIMPIA (Cero márgenes y sin ad.php)
  let ventanaImpresion = window.open('', '_blank', 'width=800,height=600');
  
  ventanaImpresion.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>Impresión de Etiquetas - SWAOS</title>
      <style>
        @page {
          size: auto;
          margin: 3mm !important; /* Margen físico mínimo para que no se corte en la impresora */
        }
        html, body {
          margin: 0 !important;
          padding: 0 !important;
          background: #fff;
          font-family: 'Segoe UI', Arial, sans-serif;
          color: #000;
        }
        /* Contenedor principal que acomoda las etiquetas */
        .lienzo {
          display: flex;
          flex-wrap: wrap;
          align-content: flex-start;
          justify-content: flex-start;
          gap: 2mm;
          width: ${anchoContenedor};
          max-width: ${maxAncho};
          margin: 0;
          padding: 2mm;
          box-sizing: border-box;
        }
        /* Estilos generales de etiqueta */
        .etiqueta {
          border: 1px dotted #999;
          background: #fff;
          text-align: center;
          box-sizing: border-box;
          overflow: hidden;
          display: flex;
          flex-direction: column;
          justify-content: space-between;
          padding: 1.5mm;
          page-break-inside: avoid; /* EVITA QUE UNA ETIQUETA SE CORTE A LA MITAD ENTRE DOS HOJAS */
          break-inside: avoid;
        }
        /* Medida 4x4 */
        .etq-4x4 { width: 40mm; height: 40mm; }
        .etq-4x4 .tienda { font-size: 7pt; font-weight: bold; color: #555; }
        .etq-4x4 .producto { font-size: 8pt; font-weight: bold; line-height: 1.1; height: 22px; overflow: hidden; margin: 2px 0; }
        .etq-4x4 .precio-gran { font-size: 14pt; font-weight: 900; display: block; }
        .etq-4x4 .promo-box { background: #000; color: #fff; font-size: 6.5pt; font-weight: bold; padding: 1px 5px; border-radius: 8px; display: inline-block; }
        .etq-4x4 .qr-img { width: 65px; height: 65px; margin: 2px auto; display: block; }
        .etq-4x4 .codigo { font-size: 6pt; color: #333; font-weight: bold; }

        /* Medida 2x2 */
        .etq-2x2 { width: 20mm; height: 20mm; padding: 1mm; }
        .etq-2x2 .tienda { display: none; }
        .etq-2x2 .producto { font-size: 5pt; font-weight: bold; line-height: 1; height: 12px; overflow: hidden; margin: 0; }
        .etq-2x2 .precio-gran { font-size: 8pt; font-weight: 900; line-height: 1; }
        .etq-2x2 .promo-box { display: none !important; }
        .etq-2x2 .qr-img { width: 35px; height: 35px; margin: 1px auto; display: block; }
        .etq-2x2 .codigo { font-size: 4.5pt; margin: 0; font-weight: bold; }
      </style>
    </head>
    <body>
      <div class="lienzo">
        ${etiquetasHTML}
      </div>
    </body>
    </html>
  `);

  ventanaImpresion.document.close();
  ventanaImpresion.focus();

  // 4. Mandamos a imprimir tras 250ms para asegurar que las imágenes QR cargaron
  setTimeout(() => {
    ventanaImpresion.print();
    // Opcional: Cerrar la mini-ventana después de imprimir
    // ventanaImpresion.close(); 
  }, 250);
};

// =========================================================
// CAMBIO EN VIVO DE TAMAÑO EN LA VISTA PREVIA (PANTALLA)
// =========================================================
document.addEventListener('DOMContentLoaded', () => {
  const selectorTamanio = document.getElementById('cfg-tamanio');
  
  if (selectorTamanio) {
    selectorTamanio.addEventListener('change', function() {
      const nuevoTamanio = this.value; // '4x4' o '2x2'
      const areaVistaPrevia = document.getElementById('area-impresion-etiqueta');
      const cajaPromo = document.getElementById('etq-caja-promo');
      const lienzoQR = document.getElementById('canvas-qr-etiqueta');

      if (!areaVistaPrevia || !productoActualQR) return;

      // 1. Cambiamos la clase del contenedor visual (etq-4x4 o etq-2x2)
      areaVistaPrevia.className = `etiqueta-anaquel etq-${nuevoTamanio}`;

      // 2. Si cambiamos a 2x2, ocultamos la promo porque en 2cm físicos no cabe la pastilla negra
      if (nuevoTamanio === '2x2') {
        if (cajaPromo) cajaPromo.style.display = 'none';
        // Dibujamos el QR chiquito para que no se desborde
        if (typeof QRious !== 'undefined') {
          new QRious({ element: lienzoQR, value: String(productoActualQR.codigo), size: 65, level: 'M' });
        }
      } else {
        // Regresamos a 4x4: Volvemos a mostrar la promo si el producto la tenía
        if (cajaPromo && productoActualQR.cantPromo > 0 && productoActualQR.precioPromo > 0) {
          cajaPromo.style.display = 'inline-block';
        }
        // Dibujamos el QR grande
        if (typeof QRious !== 'undefined') {
          new QRious({ element: lienzoQR, value: String(productoActualQR.codigo), size: 130, level: 'M' });
        }
      }
    });
  }
});