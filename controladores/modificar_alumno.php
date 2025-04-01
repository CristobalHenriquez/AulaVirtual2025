<?php
session_start();
require_once '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $dni = isset($_POST['dni']) ? $_POST['dni'] : null;
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
                dni = ?,
                email = ?,
                id_municipio = ?,
                municipio = ?,
                estado = ?,
                ramcc = ?
            WHERE id = ?
        ");
        $stmt->bind_param("sisissii", $nombre, $dni, $email, $id_municipio, $municipio, $estado, $ramcc, $id);
        $stmt->execute();

        // Obtener las inscripciones actuales del usuario
        $stmt = $db->prepare("SELECT curso_id FROM inscripciones WHERE usuario_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $inscripciones_actuales = [];
        while ($row = $result->fetch_assoc()) {
            $inscripciones_actuales[] = $row['curso_id'];
        }

        // Determinar qué cursos debe tener el usuario
        $cursos_deseados = [];
        
        // Si es RAMCC, incluir todos los cursos no premium
        if ($ramcc) {
            // Obtener todos los cursos no premium
            $stmt = $db->prepare("SELECT id FROM cursos WHERE premium = 0");
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $cursos_deseados[] = $row['id'];
            }
            
            // Añadir los cursos premium seleccionados manualmente
            foreach ($cursos as $curso_id) {
                $stmt = $db->prepare("SELECT premium FROM cursos WHERE id = ?");
                $stmt->bind_param("i", $curso_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $curso = $result->fetch_assoc();
                if ($curso && $curso['premium'] == 1 && !in_array($curso_id, $cursos_deseados)) {
                    $cursos_deseados[] = $curso_id;
                }
            }
        } else {
            // Si no es RAMCC, solo incluir los cursos seleccionados manualmente
            $cursos_deseados = $cursos;
        }

        // Determinar qué inscripciones hay que añadir (están en cursos_deseados pero no en inscripciones_actuales)
        $inscripciones_a_añadir = array_diff($cursos_deseados, $inscripciones_actuales);
        
        // Determinar qué inscripciones hay que eliminar (están en inscripciones_actuales pero no en cursos_deseados)
        $inscripciones_a_eliminar = array_diff($inscripciones_actuales, $cursos_deseados);

        // Añadir nuevas inscripciones
        if (!empty($inscripciones_a_añadir)) {
            $stmt = $db->prepare("
                INSERT INTO inscripciones (usuario_id, curso_id, fecha_inscripcion) 
                VALUES (?, ?, CURDATE())
            ");
            foreach ($inscripciones_a_añadir as $curso_id) {
                $stmt->bind_param("ii", $id, $curso_id);
                $stmt->execute();
            }
        }

        // Variable para controlar si hay inscripciones con certificados
        $hay_inscripciones_con_certificados = false;

        // Eliminar inscripciones que ya no se necesitan
        if (!empty($inscripciones_a_eliminar)) {
            // Primero verificar si hay certificados asociados a estas inscripciones
            $inscripciones_con_certificados = [];
            foreach ($inscripciones_a_eliminar as $curso_id) {
                $stmt = $db->prepare("
                    SELECT c.id 
                    FROM certificados c
                    WHERE c.usuario_id = ? AND c.curso_id = ?
                    LIMIT 1
                ");
                $stmt->bind_param("ii", $id, $curso_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $inscripciones_con_certificados[] = $curso_id;
                    $hay_inscripciones_con_certificados = true;
                }
            }
            
            // Eliminar solo las inscripciones que no tienen certificados
            $inscripciones_seguras_para_eliminar = array_diff($inscripciones_a_eliminar, $inscripciones_con_certificados);
            
            if (!empty($inscripciones_seguras_para_eliminar)) {
                $placeholders = implode(',', array_fill(0, count($inscripciones_seguras_para_eliminar), '?'));
                $types = str_repeat('i', count($inscripciones_seguras_para_eliminar) + 1);
                $query = "DELETE FROM inscripciones WHERE usuario_id = ? AND curso_id IN ($placeholders)";
                
                $stmt = $db->prepare($query);
                $params = array_merge([$id], $inscripciones_seguras_para_eliminar);
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
            }
        }

        // Confirmar transacción
        $db->commit();

        // Configurar mensaje de éxito
        if ($hay_inscripciones_con_certificados) {
            // Si hay inscripciones con certificados, mostrar un mensaje combinado
            $_SESSION['swal_success'] = "Cambios de alumno actualizados. Algunas inscripciones no se eliminaron porque tienen certificados asociados.";
        } else {
            // Si no hay problemas, mostrar mensaje de éxito normal
            $_SESSION['swal_success'] = "Cambios de alumno se actualizaron correctamente.";
        }
        
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $db->rollback();
        $_SESSION['swal_error'] = "Error al actualizar el usuario: " . $e->getMessage();
    }

    // Redireccionar de vuelta a la página de administración
    header('Location: ../admin-alumnos.php');
    exit;
}