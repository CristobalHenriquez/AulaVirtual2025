<?php
session_start();
require_once '../includes/conexion.php';

// Verificar si se recibió el tipo y el ID
if (isset($_GET['tipo']) && isset($_GET['id'])) {
    try {
        $tipo = $_GET['tipo'];
        $id = (int)$_GET['id'];
        
        switch ($tipo) {
            case 'examen':
                // Obtener datos del examen
                $stmt = $db->prepare("SELECT * FROM examenes WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $examen = $stmt->get_result()->fetch_assoc();
                
                if (!$examen) {
                    throw new Exception("El examen no existe");
                }
                
                // Preparar respuesta JSON
                $response = [
                    'success' => true,
                    'data' => $examen
                ];
                
                echo json_encode($response);
                exit;
                
            case 'pregunta':
                // Obtener datos de la pregunta
                $stmt = $db->prepare("SELECT * FROM preguntas_examen WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $pregunta = $stmt->get_result()->fetch_assoc();
                
                if (!$pregunta) {
                    throw new Exception("La pregunta no existe");
                }
                
                // Obtener opciones de la pregunta
                $stmt = $db->prepare("SELECT * FROM opciones_respuesta WHERE pregunta_id = ? ORDER BY orden");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $opciones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                
                // Preparar respuesta JSON
                $response = [
                    'success' => true,
                    'data' => $pregunta,
                    'opciones' => $opciones
                ];
                
                echo json_encode($response);
                exit;
                
            case 'opcion':
                // Obtener datos de la opción
                $stmt = $db->prepare("SELECT * FROM opciones_respuesta WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $opcion = $stmt->get_result()->fetch_assoc();
                
                if (!$opcion) {
                    throw new Exception("La opción no existe");
                }
                
                // Preparar respuesta JSON
                $response = [
                    'success' => true,
                    'data' => $opcion
                ];
                
                echo json_encode($response);
                exit;
                
            default:
                throw new Exception("Tipo no válido");
        }
        
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
        'message' => 'No se especificaron los parámetros necesarios'
    ];
    
    echo json_encode($response);
    exit;
}
?>

