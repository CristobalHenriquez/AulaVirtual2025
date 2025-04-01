<?php
session_start();
require_once '../includes/conexion.php';

// Verificar si se recibió el ID del examen
if (isset($_GET['examen_id'])) {
    try {
        $examen_id = (int)$_GET['examen_id'];
        
        // Verificar que el examen existe
        $stmt = $db->prepare("SELECT id, titulo FROM examenes WHERE id = ?");
        $stmt->bind_param("i", $examen_id);
        $stmt->execute();
        $examen = $stmt->get_result()->fetch_assoc();
        
        if (!$examen) {
            throw new Exception("El examen no existe");
        }
        
        // Obtener preguntas del examen
        $stmt = $db->prepare("SELECT * FROM preguntas_examen WHERE examen_id = ? ORDER BY orden");
        $stmt->bind_param("i", $examen_id);
        $stmt->execute();
        $preguntas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Para cada pregunta, obtener sus opciones
        foreach ($preguntas as &$pregunta) {
            $stmt = $db->prepare("SELECT * FROM opciones_respuesta WHERE pregunta_id = ? ORDER BY orden");
            $stmt->bind_param("i", $pregunta['id']);
            $stmt->execute();
            $pregunta['opciones'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        
        // Preparar respuesta JSON
        $response = [
            'success' => true,
            'examen' => $examen,
            'preguntas' => $preguntas
        ];
        
        echo json_encode($response);
        exit;
        
    } catch (Exception $e) {
        // Preparar respuesta JSON con error
        $response = [
            'success' => false,
            'message' => $e->getMessage()
        ];
        
        echo json_encode($response);
        exit;
    }
} else {
    // Preparar respuesta JSON con error
    $response = [
        'success' => false,
        'message' => 'No se especificó el ID del examen'
    ];
    
    echo json_encode($response);
    exit;
}
?>

