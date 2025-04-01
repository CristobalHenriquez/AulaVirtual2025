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
              $pregunta_id = isset($_POST['pregunta_id']) ? (int)$_POST['pregunta_id'] : 0;
              $texto_opcion = isset($_POST['texto_opcion']) ? trim($_POST['texto_opcion']) : '';
              $es_correcta = isset($_POST['es_correcta']) ? 1 : 0;
              
              // Validar datos
              if ($pregunta_id <= 0) {
                  throw new Exception("ID de pregunta inválido");
              }
              
              if (empty($texto_opcion)) {
                  throw new Exception("El texto de la opción es obligatorio");
              }
              
              // Obtener el orden de la nueva opción
              $stmt = $db->prepare("SELECT COALESCE(MAX(orden), 0) + 1 AS nuevo_orden FROM opciones_respuesta WHERE pregunta_id = ?");
              $stmt->bind_param("i", $pregunta_id);
              $stmt->execute();
              $orden = $stmt->get_result()->fetch_assoc()['nuevo_orden'];
              
              // Si es la opción correcta, actualizar las demás opciones
              if ($es_correcta) {
                  $stmt = $db->prepare("UPDATE opciones_respuesta SET es_correcta = 0 WHERE pregunta_id = ?");
                  $stmt->bind_param("i", $pregunta_id);
                  $stmt->execute();
              }
              
              // Insertar nueva opción
              $stmt = $db->prepare("INSERT INTO opciones_respuesta (pregunta_id, texto_opcion, es_correcta, orden) VALUES (?, ?, ?, ?)");
              $stmt->bind_param("isii", $pregunta_id, $texto_opcion, $es_correcta, $orden);
              $stmt->execute();
              
              $opcion_id = $db->insert_id;
              
              // Confirmar transacción
              $db->commit();
              
              // Preparar respuesta JSON
              $response = [
                  'success' => true,
                  'message' => 'Opción creada correctamente',
                  'opcion_id' => $opcion_id,
                  'pregunta_id' => $pregunta_id
              ];
              
              echo json_encode($response);
              exit;
              
          case 'editar':
              // Obtener datos del formulario
              $opcion_id = isset($_POST['opcion_id']) ? (int)$_POST['opcion_id'] : 0;
              $pregunta_id = isset($_POST['pregunta_id']) ? (int)$_POST['pregunta_id'] : 0;
              $texto_opcion = isset($_POST['texto_opcion']) ? trim($_POST['texto_opcion']) : '';
              $es_correcta = isset($_POST['es_correcta']) ? 1 : 0;
              
              // Validar datos
              if ($opcion_id <= 0) {
                  throw new Exception("ID de opción inválido");
              }
              
              if ($pregunta_id <= 0) {
                  throw new Exception("ID de pregunta inválido");
              }
              
              if (empty($texto_opcion)) {
                  throw new Exception("El texto de la opción es obligatorio");
              }
              
              // Si es la opción correcta, actualizar las demás opciones
              if ($es_correcta) {
                  $stmt = $db->prepare("UPDATE opciones_respuesta SET es_correcta = 0 WHERE pregunta_id = ?");
                  $stmt->bind_param("i", $pregunta_id);
                  $stmt->execute();
              }
              
              // Actualizar opción
              $stmt = $db->prepare("UPDATE opciones_respuesta SET texto_opcion = ?, es_correcta = ? WHERE id = ?");
              $stmt->bind_param("sii", $texto_opcion, $es_correcta, $opcion_id);
              $stmt->execute();
              
              // Confirmar transacción
              $db->commit();
              
              // Preparar respuesta JSON
              $response = [
                  'success' => true,
                  'message' => 'Opción actualizada correctamente',
                  'opcion_id' => $opcion_id,
                  'pregunta_id' => $pregunta_id
              ];
              
              echo json_encode($response);
              exit;
              
          case 'eliminar':
              // Obtener ID de la opción
              $opcion_id = isset($_POST['opcion_id']) ? (int)$_POST['opcion_id'] : 0;
              
              // Validar datos
              if ($opcion_id <= 0) {
                  throw new Exception("ID de opción inválido");
              }
              
              // Obtener el pregunta_id de la opción
              $stmt = $db->prepare("SELECT pregunta_id FROM opciones_respuesta WHERE id = ?");
              $stmt->bind_param("i", $opcion_id);
              $stmt->execute();
              $result = $stmt->get_result();
              
              if ($result->num_rows === 0) {
                  throw new Exception("No se encontró la opción especificada");
              }
              
              $pregunta_id = $result->fetch_assoc()['pregunta_id'];
              
              // Verificar que no sea la última opción
              $stmt = $db->prepare("SELECT COUNT(*) as total FROM opciones_respuesta WHERE pregunta_id = ?");
              $stmt->bind_param("i", $pregunta_id);
              $stmt->execute();
              $total_opciones = $stmt->get_result()->fetch_assoc()['total'];
              
              if ($total_opciones <= 2) {
                  throw new Exception("No se puede eliminar la opción. Debe haber al menos dos opciones por pregunta.");
              }
              
              // Verificar si es la única opción correcta
              $stmt = $db->prepare("SELECT es_correcta FROM opciones_respuesta WHERE id = ?");
              $stmt->bind_param("i", $opcion_id);
              $stmt->execute();
              $es_correcta = $stmt->get_result()->fetch_assoc()['es_correcta'];
              
              if ($es_correcta) {
                  // Contar cuántas opciones correctas hay
                  $stmt = $db->prepare("SELECT COUNT(*) as total_correctas FROM opciones_respuesta WHERE pregunta_id = ? AND es_correcta = 1");
                  $stmt->bind_param("i", $pregunta_id);
                  $stmt->execute();
                  $total_correctas = $stmt->get_result()->fetch_assoc()['total_correctas'];
                  
                  if ($total_correctas <= 1) {
                      throw new Exception("No se puede eliminar la opción correcta. Primero establezca otra opción como correcta.");
                  }
              }
              
              // Eliminar opción
              $stmt = $db->prepare("DELETE FROM opciones_respuesta WHERE id = ?");
              $stmt->bind_param("i", $opcion_id);
              $stmt->execute();
              
              // Confirmar transacción
              $db->commit();
              
              // Preparar respuesta JSON
              $response = [
                  'success' => true,
                  'message' => 'Opción eliminada correctamente',
                  'opcion_id' => $opcion_id,
                  'pregunta_id' => $pregunta_id
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

