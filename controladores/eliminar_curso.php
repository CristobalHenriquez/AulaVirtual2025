<?php
session_start();
require_once '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['curso_id'])) {
    $curso_id = (int)$_POST['curso_id'];

    try {
        $db->begin_transaction();

        // Obtener información del curso
        $stmt = $db->prepare("SELECT imagen_path, programa_pdf_path FROM cursos WHERE id = ?");
        $stmt->bind_param("i", $curso_id);
        $stmt->execute();
        $curso = $stmt->get_result()->fetch_assoc();

        // Eliminar recursos y sus archivos
        $stmt = $db->prepare("SELECT rm.archivo_path FROM recursos_modulo rm 
                              INNER JOIN modulos m ON rm.modulo_id = m.id 
                              WHERE m.curso_id = ?");
        $stmt->bind_param("i", $curso_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            if ($row['archivo_path'] && file_exists('../' . $row['archivo_path'])) {
                unlink('../' . $row['archivo_path']);
            }
        }

        // Eliminar recursos de la base de datos
        $stmt = $db->prepare("DELETE rm FROM recursos_modulo rm 
                              INNER JOIN modulos m ON rm.modulo_id = m.id 
                              WHERE m.curso_id = ?");
        $stmt->bind_param("i", $curso_id);
        $stmt->execute();

        // Eliminar módulos
        $stmt = $db->prepare("DELETE FROM modulos WHERE curso_id = ?");
        $stmt->bind_param("i", $curso_id);
        $stmt->execute();

        // Eliminar archivos del curso
        if ($curso['imagen_path'] && file_exists('../' . $curso['imagen_path'])) {
            unlink('../' . $curso['imagen_path']);
        }
        if ($curso['programa_pdf_path'] && file_exists('../' . $curso['programa_pdf_path'])) {
            unlink('../' . $curso['programa_pdf_path']);
        }

        // Eliminar el curso
        $stmt = $db->prepare("DELETE FROM cursos WHERE id = ?");
        $stmt->bind_param("i", $curso_id);
        $stmt->execute();

        // Limpiar directorios vacíos
        $directorios = ['../uploads/cursos', '../uploads/programas', '../uploads/recursos'];
        foreach ($directorios as $dir) {
            if (is_dir($dir)) {
                $files = array_diff(scandir($dir), array('.', '..'));
                if (empty($files)) {
                    rmdir($dir);
                }
            }
        }

        $db->commit();

        $_SESSION['mensaje'] = "Curso y todos sus recursos eliminados correctamente";
        $_SESSION['tipo_mensaje'] = "success";
    } catch (Exception $e) {
        $db->rollback();
        $_SESSION['mensaje'] = "Error al eliminar el curso: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
    }
} else {
    $_SESSION['mensaje'] = "Solicitud inválida";
    $_SESSION['tipo_mensaje'] = "danger";
}

// Redirigir de vuelta a la página de administración de cursos
header('Location: ../admin-cursos.php');
exit;

