<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
} // Iniciar la sesión al principio

// Detectar si estamos en entorno local o en producción
$isLocal = ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1');

if ($isLocal) {
    // Configuración para entorno local (XAMPP o desarrollo)
    $server = 'localhost:3307'; // Puerto de MySQL en XAMPP/MAMP (ajústalo si es necesario)
    $username = 'root';
    $password = '';
    $database = 'aula_nueva';
} else {
    // Configuración para el servidor en producción
    $server = 'localhost'; // En la mayoría de los hostings compartidos se usa 'localhost'
    $username = 'u692790713_aula'; // Usuario de la base de datos (mismo que Moodle)
    $password = 'U5e9uwo1h='; // Contraseña de la BD
    $database = 'u692790713_aula'; // Nombre de la BD (mismo que Moodle)
}

// Conectar usando **MySQLi**
$db = mysqli_connect($server, $username, $password, $database);

// Verificar la conexión
if (!$db) {
    die("❌ Error de conexión a la base de datos: " . mysqli_connect_error());
}

// Configurar UTF-8 para evitar problemas con caracteres especiales
mysqli_query($db, "SET NAMES 'utf8mb4'");
mysqli_set_charset($db, "utf8mb4");

// Opcional: Configurar PDO si quieres compatibilidad con consultas preparadas
try {
    $pdo = new PDO("mysql:host=$server;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Error de conexión a la base de datos (PDO): " . $e->getMessage());
}
