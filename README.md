# SWAOS (Sistema Web de Administración Punto de Venta)

## 🎯 Objetivo del Proyecto
SWAOS es una plataforma web integral diseñada para centralizar, digitalizar y automatizar la operación diaria de tiendas. 
Su propósito es unificar el Punto de Venta (POS), el control avanzado de inventarios y la trazabilidad de productos en un ecosistema robusto, seguro y de alto rendimiento.

## 🛠️ ¿Qué problema resuelve?
* **Opacidad y desconfianza:** Elimina las notas de papel informales mediante un **Comprobante digital**.
* **Ineficiencia en mostrador y control de stock:** Agiliza el cobro y previene fugas de inventario a través de un sistema de generación de **etiquetas QR inteligentes para anaqueles**.
* Permite el escaneo veloz en el POS y la aplicación automática de reglas de negocio complejas (como precios especiales por volumen o mayoreo de tipo 3x).
* **Vulnerabilidad y brechas de seguridad:** Protege la información financiera y técnica mediante un estricto control de acceso basado en roles (RBAC)
*  y la eliminación de búsquedas públicas vulnerables a ataques de fuerza bruta o IDOR.

---

## 🚀 Características Principales

* **Landing Page Moderna y Responsiva:** Diseño de pantalla completa con menú adaptativo para dispositivos móviles.
* **Portal del Cliente Autenticado (`portal_cliente.php`):** Autonomía total para el usuario final con sesiones blindadas por PHP.
* **Generador Masivo de Etiquetas QR:** 
  * Soporte para tamaños estándar ($4\text{cm} \times 4\text{cm}$) y compactos ($2\text{cm} \times 2\text{cm}$).
  * Adaptación automática de columnas según el ancho del papel de impresión (Mini-printers térmicas de 58mm y 80mm, o impresoras tradicionales en formato Carta).
  * Inclusión automática de ganchos comerciales para promociones por volumen.
* **Punto de Venta (POS) Optimizado:** Lectura ultrarrápida por código de barras o QR para agilizar el flujo en mostrador.

---

## 💻 Stack Tecnológico

* **Backend:** PHP 8+ con manejo de sesiones seguras y MySQL.
* **Frontend:** JavaScript (Vanilla ES6+), HTML5 y CSS3 personalizado.
* **Librerías y Herramientas:** 
  * [QRious](https://github.com/neocotic/qrious) para la renderización nativa de códigos QR en el navegador.
  * FontAwesome para iconografía corporativa.
* **Entorno de Desarrollo:** Compatible con XAMPP, servidores locales y despliegues en producción sobre Linux/Windows Server.

---

## 📦 Instalación y Configuración Local

1. Clona el repositorio en tu directorio web local (ej. `htdocs/swaos`):
   ```bash
   git clone [https://github.com/fhertecomzt/swaos.git](https://github.com/fhertecomzt/swaos.git)
