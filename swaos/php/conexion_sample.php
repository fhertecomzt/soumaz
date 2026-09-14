<?php
//Conexion remota
$dsn = 'mysql:host=localhost;dbname=soumaz;charset=utf8mb4'; // Usar utf8mb4 para mayor compatibilidad con caracteres especiales.
$username = 'root'; // Verifica si el usuario es realmente 'roots'.
$password = '';

try {
  $dbh = new PDO($dsn, $username, $password);
  // 1. REAFIRMAR PHP: Por si el servidor ignora el .htaccess en peticiones AJAX
  date_default_timezone_set('America/Mazatlan');

  // 2. EL BLINDAJE DE MYSQL: Obligamos a la base de datos a usar UTC-7 exacto
  // Usamos '-07:00' en lugar del nombre para evitar el error del Horario de Verano
  $dbh->exec("SET time_zone = '-07:00';");
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Habilita el modo de errores para capturar excepciones.
} catch (PDOException $e) {
  error_log('Error de conexión: ' . $e->getMessage()); // Registra el error en el log del servidor.
  die('No se pudo conectar a la base de datos.'); // Mensaje más seguro para evitar exponer detalles sensibles.
}
