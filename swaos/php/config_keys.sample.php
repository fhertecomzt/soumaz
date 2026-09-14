<?php
// Detectamos donde esta corriendo el sistema
$host = $_SERVER['HTTP_HOST'];

if ($host === 'localhost' || $host === '127.0.0.1') {
  // ==========================================
  // CLAVES PARA MODO LOCAL (XAMPP / Pruebas)
  // ==========================================
  define('RECAPTCHA_SITE_KEY', 'TUCLAVE');
  define('RECAPTCHA_SECRET_KEY', 'TUCLAVE');
  // CLAVES PARA REPORTES IA
  define('api_key', 'TUCLAVESTUDIOANDROID');
} else {
  // ==========================================
  // CLAVES PARA PRODUCCIÓN (Tu servidor Web)
  // ==========================================
  define('RECAPTCHA_SITE_KEY', 'TUCLAVE');
  define('RECAPTCHA_SECRET_KEY', 'TUCLAVE');
  // CLAVES PARA REPORTES IA
  define('api_key', 'TUCLAVESTUDIOANDROID');
}
