<?php
session_start();
require_once '../includes/auth.php';
verificarRolAdmin();
require_once '../includes/conexion.php';

// Verificar si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Obtener el ID del examen y del módulo
        $examen_id = isset($_POST['examen_id']) ? (int)$_POST['examen_id'] : 0;
        $modulo_id = isset($_POST['modulo_id']) ? (int)$_POST['modulo_id'] : 0;
        
        if ($examen_id <= 0) {
            throw new Exception("ID de examen inválido");
        }
        
        if ($modulo_id <= 0) {
            throw new Exception("ID de módulo inválido");
        }
        
        // Verificar que el examen pertenezca al módulo
        $stmt = $db->prepare("SELECT id FROM examenes WHERE id = ? AND modulo_id = ?");
        $stmt->bind_param("ii", $examen_id, $modulo_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("El examen no pertenece al módulo especificado");
        }
        
        // Iniciar transacción
        $db->begin_transaction();
        
        // Eliminar el examen (las preguntas y opciones se eliminarán en cascada por las restricciones de clave foránea)
        $stmt = $db->prepare("DELETE FROM examenes WHERE id = ?");
        $stmt->bind_param("i", $examen_id);
        $stmt->execute();
        
        // Confirmar transacción
        $db->commit();
        
        // Preparar respuesta JSON
        $response = [
            'success' => true,
            'message' => 'Examen eliminado correctamente'
        ];
        
        echo json_encode($response);
        exit;
        
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        if ($db->inTransaction()) {
            $db->rollback();
        }
        
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