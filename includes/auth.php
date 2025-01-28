<?php
function verificarSesion() {
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header("Location: login.php");
        exit;
    }
}

function verificarRolAdmin() {
    verificarSesion();
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
        header("Location: student.php");
        exit;
    }
}

function verificarAcceso() {
    verificarSesion();
    if ($_SESSION['rol'] === 'Administrador') {
        // Los administradores pueden acceder a todas las páginas
        return;
    } else {
        // Todos los demás roles (incluyendo 'Alumno') son redirigidos a student.php
        if (basename($_SERVER['PHP_SELF']) !== 'student.php') {
            header("Location: student.php");
            exit;
        }
    }
}