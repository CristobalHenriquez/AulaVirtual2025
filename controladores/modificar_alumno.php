<?php
session_start();
require_once '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $id_municipio = !empty($_POST['id_municipio']) ? $_POST['id_municipio'] : null;
    $municipio = isset($_POST['municipio']) ? $_POST['municipio'] : null;
    $estado = isset($_POST['estado']) ? $_POST['estado'] : null;
    $ramcc = isset($_POST['ramcc']) ? 1 : 0;
    $cursos = isset($_POST['cursos']) ? $_POST['cursos'] : [];

    try {
        // Iniciar transacción
        $db->begin_transaction();

        // Actualizar datos del usuario
        $stmt = $db->prepare("
            UPDATE usuarios 
            SET nombre = ?, 
                email = ?,
                id_municipio = ?,
                municipio = ?,
                estado = ?,
                ramcc = ?
            WHERE id = ?
        ");
        $stmt->bind_param("ssissii", $nombre, $email, $id_municipio, $municipio, $estado, $ramcc, $id);
        $stmt->execute();

        // Eliminar inscripciones existentes
        $stmt = $db->prepare("DELETE FROM inscripciones WHERE usuario_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Si es RAMCC, inscribir en todos los cursos disponibles
        if ($ramcc) {
            // Obtener todos los cursos
            $stmt = $db->prepare("SELECT id FROM cursos");
            $stmt->execute();
            $result = $stmt->get_result();
            $todos_cursos = [];
            while ($row = $result->fetch_assoc()) {
                $todos_cursos[] = $row['id'];
            }
            
            // Usar todos los cursos en lugar de los seleccionados
            $cursos = $todos_cursos;
        }

        // Insertar nuevas inscripciones
        if (!empty($cursos)) {
            $stmt = $db->prepare("
                INSERT INTO inscripciones (usuario_id, curso_id) 
                VALUES (?, ?)
            ");
            foreach ($cursos as $curso_id) {
                $stmt->bind_param("ii", $id, $curso_id);
                $stmt->execute();
            }
        }

        // Confirmar transacción
        $db->commit();

        $_SESSION['swal_success'] = "Cambios de alumno se actualizaron correctamente.";
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $db->rollback();
        $_SESSION['swal_error'] = "Error al actualizar el usuario: " . $e->getMessage();
    }

    // Redireccionar de vuelta a la página de administración
    header('Location: ../admin-alumnos.php');
    exit;
}