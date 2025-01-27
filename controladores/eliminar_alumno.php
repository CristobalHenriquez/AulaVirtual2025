<?php
session_start();
require_once '../includes/conexion.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Iniciar transacción
        $db->begin_transaction();

        // Eliminar inscripciones del alumno
        $stmt = $db->prepare("DELETE FROM inscripciones WHERE usuario_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Eliminar al alumno
        $stmt = $db->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Confirmar transacción
        $db->commit();

        $_SESSION['mensaje'] = "Alumno eliminado correctamente.";
        $_SESSION['tipo_mensaje'] = "success";
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $db->rollback();
        $_SESSION['mensaje'] = "Error al eliminar el alumno: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
    }
} else {
    $_SESSION['mensaje'] = "ID de alumno no proporcionado.";
    $_SESSION['tipo_mensaje'] = "danger";
}

// Redireccionar de vuelta a la página de administración
header('Location: ../admin-alumnos.php');
exit;

