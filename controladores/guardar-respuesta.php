<?php
session_start();

// Incluir conexión
require_once '../includes/conexion.php';

// Verificar que la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Verificar que se han proporcionado todos los datos necesarios
if (!isset($_POST['intento_id']) || !isset($_POST['pregunta_id']) || !isset($_POST['opcion_id'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    exit;
}

$intento_id = intval($_POST['intento_id']);
$pregunta_id = intval($_POST['pregunta_id']);
$opcion_id = intval($_POST['opcion_id']);
$user_id = $_SESSION['id'];

// Verificar que el intento pertenece al usuario y está en progreso
$stmt = $db->prepare("SELECT 1 FROM intentos_examen 
                      WHERE id = ? AND usuario_id = ? AND estado = 'en_progreso'");
$stmt->bind_param("ii", $intento_id, $user_id);
$stmt->execute();

if ($stmt->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Intento no válido o finalizado']);
    exit;
}

// Verificar que la opción pertenece a la pregunta
$stmt = $db->prepare("SELECT es_correcta FROM opciones_respuesta 
                      WHERE id = ? AND pregunta_id = ?");
$stmt->bind_param("ii", $opcion_id, $pregunta_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Opción no válida para esta pregunta']);
    exit;
}

$opcion = $result->fetch_assoc();
$es_correcta = $opcion['es_correcta'];

// Verificar si ya existe una respuesta para esta pregunta en este intento
$stmt = $db->prepare("SELECT id FROM respuestas_usuario 
                      WHERE intento_id = ? AND pregunta_id = ?");
$stmt->bind_param("ii", $intento_id, $pregunta_id);
$stmt->execute();
$respuesta_existente = $stmt->get_result()->fetch_assoc();

if ($respuesta_existente) {
    // Actualizar la respuesta existente
    $stmt = $db->prepare("UPDATE respuestas_usuario 
                          SET opcion_id = ?, es_correcta = ? 
                          WHERE id = ?");
    $stmt->bind_param("iii", $opcion_id, $es_correcta, $respuesta_existente['id']);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0 || $stmt->affected_rows === 0) {
        echo json_encode(['success' => true, 'message' => 'Respuesta actualizada correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la respuesta']);
    }
} else {
    // Insertar una nueva respuesta
    $stmt = $db->prepare("INSERT INTO respuestas_usuario (intento_id, pregunta_id, opcion_id, es_correcta) 
                          VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiii", $intento_id, $pregunta_id, $opcion_id, $es_correcta);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Respuesta guardada correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar la respuesta']);
    }
}

