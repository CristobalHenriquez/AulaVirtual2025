<?php
session_start();
require_once '../includes/conexion.php';

// Verificar que se ha proporcionado un ID de examen
if (!isset($_GET['examen_id']) || empty($_GET['examen_id'])) {
    header('Location: ../student.php');
    exit;
}

$examen_id = intval($_GET['examen_id']);
$user_id = $_SESSION['id']; // Usando el nombre correcto de la variable de sesión

// Verificar que el examen existe y está activo
$stmt = $db->prepare("SELECT e.*, m.id as modulo_id FROM examenes e 
                    JOIN modulos m ON e.modulo_id = m.id 
                    WHERE e.id = ? AND e.activo = 1");
$stmt->bind_param("i", $examen_id);
$stmt->execute();
$examen = $stmt->get_result()->fetch_assoc();

if (!$examen) {
    header('Location: ../student.php');
    exit;
}

// Verificar que el tiempo límite sea válido
if ($examen['tiempo_limite'] <= 0) {
    // Establecer un tiempo límite predeterminado (30 minutos)
    $tiempo_limite_predeterminado = 30;
    $stmt = $db->prepare("UPDATE examenes SET tiempo_limite = ? WHERE id = ?");
    $stmt->bind_param("ii", $tiempo_limite_predeterminado, $examen_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $examen['tiempo_limite'] = $tiempo_limite_predeterminado;
    }
}

// Verificar si el usuario ya ha alcanzado el número máximo de intentos diarios
$fecha_actual = date('Y-m-d');
$stmt = $db->prepare("SELECT COUNT(*) as intentos_hoy FROM intentos_examen 
                    WHERE usuario_id = ? AND examen_id = ? AND DATE(fecha_inicio) = ? AND estado = 'completado'");
$stmt->bind_param("iis", $user_id, $examen_id, $fecha_actual);
$stmt->execute();
$intentos_hoy = $stmt->get_result()->fetch_assoc()['intentos_hoy'];

if ($intentos_hoy >= $examen['intentos_maximos_diarios']) {
    $_SESSION['error_message'] = "Has alcanzado el número máximo de intentos diarios para este examen.";
    header('Location: ../Examen_Modulo_' . $examen['modulo_id']);
    exit;
}

// Verificar si hay un intento en progreso
$stmt = $db->prepare("SELECT id FROM intentos_examen 
                    WHERE usuario_id = ? AND examen_id = ? AND estado = 'en_progreso'");
$stmt->bind_param("ii", $user_id, $examen_id);
$stmt->execute();
$intento_existente = $stmt->get_result()->fetch_assoc();

if ($intento_existente) {
    // Redirigir al intento existente
    header('Location: ../Realizar_Examen_' . $intento_existente['id']);
    exit;
}

// Crear un nuevo intento
$fecha_actual = date('Y-m-d H:i:s');
$stmt = $db->prepare("INSERT INTO intentos_examen (usuario_id, examen_id, fecha_inicio, estado) 
                    VALUES (?, ?, ?, 'en_progreso')");
$stmt->bind_param("iis", $user_id, $examen_id, $fecha_actual);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    $intento_id = $db->insert_id;
    // Verificar que el intento se creó correctamente con el estado adecuado
    $verify_stmt = $db->prepare("SELECT estado FROM intentos_examen WHERE id = ?");
    $verify_stmt->bind_param("i", $intento_id);
    $verify_stmt->execute();
    $result = $verify_stmt->get_result();
    $intento_creado = $result->fetch_assoc();
    // Asegurarse de que la redirección sea correcta
    $redirect_url = "../Realizar_Examen_$intento_id";

    // Usar JavaScript para la redirección para evitar problemas con el buffer de salida
    echo "<script>window.location.href = '$redirect_url';</script>";
    // También intentar con el método tradicional como respaldo
    header("Location: $redirect_url");
    exit;
} else {
    $_SESSION['error_message'] = "Error al iniciar el examen. Por favor, inténtalo de nuevo.";
    header('Location: ../Examen_Modulo_' . $examen['modulo_id']);
    exit;
}
