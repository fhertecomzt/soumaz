<?php
// Detectamos donde esta corriendo el sistema
$host_actual = $_SERVER['HTTP_HOST'];

if ($host_actual === 'localhost' || $host_actual === '127.0.0.1') {
  // ==========================================
  // CORREO LOCAL (XAMPP -> Usa tu Gmail)
  // ==========================================
  define('MAIL_HOST', 'smtp.gmail.com');
  define('MAIL_USER', 'TuCorreo@gmail.com');
  define('MAIL_PASS', 'TuClave rnwe'); //<-- Deja el texto plano, sin tu clave real
  define('MAIL_PORT', 587);
  define('MAIL_SECURE', 'tls');
  define('MAIL_AUTO_TLS', true);
} else {
  // ==========================================
  // CORREO PRODUCCION (Neubox -> Usa Nativo)
  // ==========================================
  define('MAIL_HOST', 'localhost');
  define('MAIL_USER', 'notificaciones@tucorreo.com.mx');
  define('MAIL_PASS', 'TuContraseña'); //<-- Deja el texto plano, sin tu clave real
  define('MAIL_PORT', 25);
  define('MAIL_SECURE', 'false');
  define('MAIL_AUTO_TLS', false);
}
