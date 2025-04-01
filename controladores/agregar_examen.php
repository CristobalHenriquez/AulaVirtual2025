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
        
        // Obtener datos del formulario
        $titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
        $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
        $tiempo_limite = isset($_POST['tiempo_limite']) ? (int)$_POST['tiempo_limite'] : 60;
        $intentos_maximos = isset($_POST['intentos_maximos']) ? (int)$_POST['intentos_maximos'] : 3;
        $nota_aprobacion = isset($_POST['nota_aprobacion']) ? (float)$_POST['nota_aprobacion'] : 60;
        $activo = isset($_POST['activo']) ? 1 : 0;
        
        // Validar datos
        if (empty($titulo)) {
            throw new Exception("El título del examen es obligatorio");
        }
        
        // Obtener el curso_id del módulo
        $stmt = $db->prepare("SELECT curso_id FROM modulos WHERE id = ?");
        $stmt->bind_param("i", $modulo_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("No se encontró el módulo especificado");
        }
        
        $curso_id = $result->fetch_assoc()['curso_id'];
        
        // Iniciar transacción
        $db->begin_transaction();
        
        // Insertar nuevo examen
        $stmt = $db->prepare("INSERT INTO examenes (titulo, descripcion, modulo_id, curso_id, tiempo_limite, intentos_maximos_diarios, nota_aprobacion, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiiiidi", $titulo, $descripcion, $modulo_id, $curso_id, $tiempo_limite, $intentos_maximos, $nota_aprobacion, $activo);
        $stmt->execute();
        
        $examen_id = $db->insert_id;
        
        // Confirmar transacción
        $db->commit();
        
        // Preparar respuesta JSON
        $response = [
            'success' => true,
            'message' => 'Examen creado correctamente',
            'examen_id' => $examen_id
        ];
        
        echo json_encode($response);
        exit;
        
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

