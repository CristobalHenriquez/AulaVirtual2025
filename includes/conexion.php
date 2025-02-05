<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
} // Iniciar la sesión al principio

// Configuración de la conexión según el entorno
if ($_SERVER['HTTP_HOST'] === 'localhost') { // O comprueba alguna otra variable de entorno
    $server = 'localhost:3307';
    $username = 'root';
    $password = '';
    $database = 'aula_nueva';
} else {
    $server = '127.0.0.1';
    $username = 'u957245339_ramcc';
    $password = 'Desarrollo2023';
    $database = 'u957245339_administracion';
}

$db = mysqli_connect($server, $username, $password, $database);

// Verificar la conexión
if (!$db) {
    die("Error de conexión: " . mysqli_connect_error()); // O maneja el error de otra forma
}

mysqli_query($db, "SET NAMES 'utf8'"); 