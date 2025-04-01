<?php
session_start();
require_once '../includes/conexion.php';

// Verificar si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Obtener el ID del módulo
        $modulo_id = isset($_POST['modulo_id']) ? (int)$_POST['modulo_id'] : 0;
        
        if ($modulo_id <= 0) {
            throw new Exception("ID de módulo inválido");
        }
        
        // Obtener la acción a realizar
        $accion = isset($_POST['accion']) ? $_POST['accion'] : '';
        
        // Iniciar transacción
        $db->begin_transaction();
        
        switch ($accion) {
            case 'crear':
                // Obtener datos del formulario
                $examen_id = isset($_POST['examen_id']) ? (int)$_POST['examen_id'] : 0;
                $texto_pregunta = isset($_POST['texto_pregunta']) ? trim($_POST['texto_pregunta']) : '';
                $tipo_pregunta = isset($_POST['tipo_pregunta']) ? trim($_POST['tipo_pregunta']) : 'opcion_multiple';
                $puntaje = isset($_POST['puntaje']) ? (float)$_POST['puntaje'] : 1;
                
                // Validar datos
                if ($examen_id <= 0) {
                    throw new Exception("ID de examen inválido");
                }
                
                if (empty($texto_pregunta)) {
                    throw new Exception("El texto de la pregunta es obligatorio");
                }
                
                // Obtener el orden de la nueva pregunta
                $stmt = $db->prepare("SELECT COALESCE(MAX(orden), 0) + 1 AS nuevo_orden FROM preguntas_examen WHERE examen_id = ?");
                $stmt->bind_param("i", $examen_id);
                $stmt->execute();
                $orden = $stmt->get_result()->fetch_assoc()['nuevo_orden'];
                
                // Insertar nueva pregunta
                $stmt = $db->prepare("INSERT INTO preguntas_examen (examen_id, texto_pregunta, tipo, puntaje, orden) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("issdi", $examen_id, $texto_pregunta, $tipo_pregunta, $puntaje, $orden);
                $stmt->execute();
                
                $pregunta_id = $db->insert_id;
                
                // Procesar opciones de respuesta si se enviaron
                if ($tipo_pregunta === 'verdadero_falso') {
                    // Opciones para verdadero/falso
                    $opciones_texto = ['Verdadero', 'Falso'];
                    $opcion_correcta = isset($_POST['opcion_vf_correcta']) ? (int)$_POST['opcion_vf_correcta'] : 0;
                    
                    for ($i = 0; $i < 2; $i++) {
                        $es_correcta = ($i == $opcion_correcta) ? 1 : 0;
                        $orden_opcion = $i + 1;
                        
                        $stmt = $db->prepare("INSERT INTO opciones_respuesta (pregunta_id, texto_opcion, es_correcta, orden) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param("isii", $pregunta_id, $opciones_texto[$i], $es_correcta, $orden_opcion);
                        $stmt->execute();
                    }
                }
                // Para opción múltiple, las opciones se agregarán después
                
                // Confirmar transacción
                $db->commit();
                
                // Preparar respuesta JSON
                $response = [
                    'success' => true,
                    'message' => 'Pregunta creada correctamente',
                    'pregunta_id' => $pregunta_id,
                    'examen_id' => $examen_id
                ];
                
                echo json_encode($response);
                exit;
                
            case 'editar':
                // Obtener datos del formulario
                $pregunta_id = isset($_POST['pregunta_id']) ? (int)$_POST['pregunta_id'] : 0;
                $texto_pregunta = isset($_POST['texto_pregunta']) ? trim($_POST['texto_pregunta']) : '';
                $tipo_pregunta = isset($_POST['tipo_pregunta']) ? trim($_POST['tipo_pregunta']) : 'opcion_multiple';
                $puntaje = isset($_POST['puntaje']) ? (float)$_POST['puntaje'] : 1;
                
                // Validar datos
                if ($pregunta_id <= 0) {
                    throw new Exception("ID de pregunta inválido");
                }
                
                if (empty($texto_pregunta)) {
                    throw new Exception("El texto de la pregunta es obligatorio");
                }
                
                // Obtener el examen_id de la pregunta
                $stmt = $db->prepare("SELECT examen_id FROM preguntas_examen WHERE id = ?");
                $stmt->bind_param("i", $pregunta_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    throw new Exception("No se encontró la pregunta especificada");
                }
                
                $examen_id = $result->fetch_assoc()['examen_id'];
                
                // Actualizar pregunta
                $stmt = $db->prepare("UPDATE preguntas_examen SET texto_pregunta = ?, tipo = ?, puntaje = ? WHERE id = ?");
                $stmt->bind_param("ssdi", $texto_pregunta, $tipo_pregunta, $puntaje, $pregunta_id);
                $stmt->execute();
                
                // Confirmar transacción
                $db->commit();
                
                // Preparar respuesta JSON
                $response = [
                    'success' => true,
                    'message' => 'Pregunta actualizada correctamente',
                    'pregunta_id' => $pregunta_id,
                    'examen_id' => $examen_id
                ];
                
                echo json_encode($response);
                exit;
                
            case 'eliminar':
                // Obtener ID de la pregunta
                $pregunta_id = isset($_POST['pregunta_id']) ? (int)$_POST['pregunta_id'] : 0;
                
                // Validar datos
                if ($pregunta_id <= 0) {
                    throw new Exception("ID de pregunta inválido");
                }
                
                // Obtener el examen_id de la pregunta
                $stmt = $db->prepare("SELECT examen_id FROM preguntas_examen WHERE id = ?");
                $stmt->bind_param("i", $pregunta_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    throw new Exception("No se encontró la pregunta especificada");
                }
                
                $examen_id = $result->fetch_assoc()['examen_id'];
                
                // Eliminar pregunta (las opciones se eliminarán en cascada)
                $stmt = $db->prepare("DELETE FROM preguntas_examen WHERE id = ?");
                $stmt->bind_param("i", $pregunta_id);
                $stmt->execute();
                
                // Confirmar transacción
                $db->commit();
                
                // Preparar respuesta JSON
                $response = [
                    'success' => true,
                    'message' => 'Pregunta eliminada correctamente',
                    'pregunta_id' => $pregunta_id,
                    'examen_id' => $examen_id
                ];
                
                echo json_encode($response);
                exit;
                
            default:
                throw new Exception("Acción no válida");
        }
        
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $db->rollback();
        
        // Preparar respuesta JSON con error
        $response = [
            'success' => false,
            'message' => $e->getMessage()
        ];
        
        echo json_encode($response);
        exit;
    }
} else {
    // Si no se envió el formulario, redirigir
    header("Location: ../admin-cursos.php");
    exit;
}
?>