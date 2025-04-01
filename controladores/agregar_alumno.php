<?php
session_start();
require_once '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $dni = $_POST['dni'] ?: null;
    $id_municipio = !empty($_POST['id_municipio']) ? $_POST['id_municipio'] : null;
    $municipio = isset($_POST['municipio']) ? $_POST['municipio'] : null;
    $estado = isset($_POST['estado']) && !empty($_POST['estado']) ? $_POST['estado'] : null;
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $rol = $_POST['rol'];
    $ramcc = isset($_POST['ramcc']) ? 1 : 0;
    $cursos = isset($_POST['cursos']) ? $_POST['cursos'] : [];

    try {
        // Iniciar transacción
        $db->begin_transaction();

        // Verificar si el email ya existe
        $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception("El email ya está registrado.");
        }

        // Insertar el nuevo usuario
        $stmt = $db->prepare("
            INSERT INTO usuarios (nombre, dni, id_municipio, municipio, email, password, rol, estado, ramcc)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssisssssi", $nombre, $dni, $id_municipio, $municipio, $email, $password, $rol, $estado, $ramcc);
        $stmt->execute();
        
        $usuario_id = $db->insert_id;

        // Si es RAMCC, inscribir automáticamente en todos los cursos no premium
        if ($ramcc) {
            // Obtener todos los cursos no premium
            $stmt = $db->prepare("SELECT id FROM cursos WHERE premium = 0");
            $stmt->execute();
            $result = $stmt->get_result();
            $cursos_no_premium = [];
            while ($row = $result->fetch_assoc()) {
                $cursos_no_premium[] = $row['id'];
            }
            
            // Combinar los cursos no premium con los cursos premium seleccionados manualmente
            $cursos_premium_seleccionados = array_filter($cursos, function($curso_id) use ($db) {
                $stmt = $db->prepare("SELECT premium FROM cursos WHERE id = ?");
                $stmt->bind_param("i", $curso_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $curso = $result->fetch_assoc();
                return $curso && $curso['premium'] == 1;
            });
            
            // Unir los cursos no premium con los premium seleccionados
            $cursos = array_merge($cursos_no_premium, $cursos_premium_seleccionados);
        }

        // Insertar inscripciones a cursos si se seleccionaron
        if (!empty($cursos)) {
            $stmt = $db->prepare("
                INSERT INTO inscripciones (usuario_id, curso_id)
                VALUES (?, ?)
            ");
            foreach ($cursos as $curso_id) {
                $stmt->bind_param("ii", $usuario_id, $curso_id);
                $stmt->execute();
            }
        }

        // Confirmar transacción
        $db->commit();

        $_SESSION['swal_success'] = "Alumno agregado exitosamente.";
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $db->rollback();
        $_SESSION['swal_error'] = "Error al agregar el alumno: " . $e->getMessage();
    }

    // Redireccionar de vuelta a la página de administración de alumnos
    header('Location: ../admin-alumnos.php');
    exit;
}