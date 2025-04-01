<?php
session_start();
require_once '../includes/auth.php';
verificarRolAdmin();
require_once '../includes/conexion.php';

// Verificar si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
      // Obtener el ID del módulo
      $modulo_id = isset($_POST['modulo_id']) ? (int)$_POST['modulo_id'] : 0;
      
      if ($modulo_id <= 0) {
          throw new Exception("ID de módulo inválido");
      }
      
      // Iniciar transacción
      $db->begin_transaction();
      
      // Procesar exámenes eliminados
      if (!empty($_POST['examenes_eliminados'])) {
          $examenes_eliminados = explode(',', $_POST['examenes_eliminados']);
          foreach ($examenes_eliminados as $examen_id) {
              $stmt = $db->prepare("DELETE FROM examenes WHERE id = ?");
              $stmt->bind_param("i", $examen_id);
              $stmt->execute();
          }
      }
      
      // Procesar exámenes
      if (isset($_POST['examen_ids']) && is_array($_POST['examen_ids'])) {
          $examen_ids = $_POST['examen_ids'];
          $examen_titulos = $_POST['examen_titulos'];
          $examen_descripciones = $_POST['examen_descripciones'];
          $examen_tiempos = $_POST['examen_tiempos'];
          $examen_intentos = $_POST['examen_intentos'];
          $examen_notas = $_POST['examen_notas'];
          $examen_activos = isset($_POST['examen_activos']) ? $_POST['examen_activos'] : [];
          
          foreach ($examen_ids as $index => $examen_id) {
              $titulo = $examen_titulos[$index];
              $descripcion = $examen_descripciones[$index];
              $tiempo_limite = $examen_tiempos[$index];
              $intentos_maximos = $examen_intentos[$index];
              $nota_aprobacion = $examen_notas[$index];
              $activo = in_array($examen_id, $examen_activos) ? 1 : 0;
              
              if ($examen_id === 'nuevo') {
                  // Insertar nuevo examen
                  $stmt = $db->prepare("INSERT INTO examenes (titulo, descripcion, modulo_id, curso_id, tiempo_limite, intentos_maximos_diarios, nota_aprobacion, activo) VALUES (?, ?, ?, (SELECT curso_id FROM modulos WHERE id = ?), ?, ?, ?, ?)");
                  $stmt->bind_param("ssiiiidi", $titulo, $descripcion, $modulo_id, $modulo_id, $tiempo_limite, $intentos_maximos, $nota_aprobacion, $activo);
                  $stmt->execute();
              } else {
                  // Actualizar examen existente
                  $stmt = $db->prepare("UPDATE examenes SET titulo = ?, descripcion = ?, tiempo_limite = ?, intentos_maximos_diarios = ?, nota_aprobacion = ?, activo = ? WHERE id = ?");
                  $stmt->bind_param("ssiidii", $titulo, $descripcion, $tiempo_limite, $intentos_maximos, $nota_aprobacion, $activo, $examen_id);
                  $stmt->execute();
              }
          }
      }
      
      // Confirmar transacción
      $db->commit();
      
      $_SESSION['swal_success'] = "Los exámenes se han guardado correctamente.";
      header("Location: ../editar-examen.php?modulo_id=" . $modulo_id);
      exit;
      
  } catch (Exception $e) {
      // Revertir transacción en caso de error
      $db->rollback();
      
      $_SESSION['swal_error'] = "Error al guardar los exámenes: " . $e->getMessage();
      header("Location: ../editar-examen.php?modulo_id=" . $modulo_id);
      exit;
  }
} else {
  // Si no se envió el formulario, redirigir
  header("Location: ../admin-cursos.php");
  exit;
}