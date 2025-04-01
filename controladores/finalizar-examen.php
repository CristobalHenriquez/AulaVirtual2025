<?php
session_start();
require_once '../includes/auth.php';

// Incluir conexión
require_once '../includes/conexion.php';

// Verificar que se ha proporcionado un ID de intento
if (isset($_POST['intento_id'])) {
    $intento_id = intval($_POST['intento_id']);
} elseif (isset($_GET['intento_id'])) {
    $intento_id = intval($_GET['intento_id']);
} else {
    header('Location: ../student.php');
    exit;
}

$user_id = $_SESSION['id']; // Usando el nombre correcto de la variable de sesión

// Verificar que el intento pertenece al usuario y está en progreso
$stmt = $db->prepare("SELECT i.*, e.modulo_id, e.nota_aprobacion, e.curso_id as examen_curso_id
                    FROM intentos_examen i
                    JOIN examenes e ON i.examen_id = e.id
                    WHERE i.id = ? AND i.usuario_id = ? AND i.estado = 'en_progreso'");
$stmt->bind_param("ii", $intento_id, $user_id);
$stmt->execute();
$intento = $stmt->get_result()->fetch_assoc();

if (!$intento) {
    header('Location: ../student.php');
    exit;
}

// Obtener todas las preguntas del examen
$stmt = $db->prepare("SELECT id, puntaje FROM preguntas_examen WHERE examen_id = ?");
$stmt->bind_param("i", $intento['examen_id']);
$stmt->execute();
$preguntas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calcular la calificación
$total_preguntas = count($preguntas);
$total_puntos_posibles = 0;
$puntos_obtenidos = 0;

foreach ($preguntas as $pregunta) {
    $total_puntos_posibles += $pregunta['puntaje'];

    // Verificar si el usuario respondió correctamente a esta pregunta
    $stmt = $db->prepare("SELECT es_correcta FROM respuestas_usuario 
                        WHERE intento_id = ? AND pregunta_id = ?");
    $stmt->bind_param("ii", $intento_id, $pregunta['id']);
    $stmt->execute();
    $respuesta = $stmt->get_result()->fetch_assoc();

    if ($respuesta && $respuesta['es_correcta']) {
        $puntos_obtenidos += $pregunta['puntaje'];
    }
}

// Calcular porcentaje de calificación
$calificacion = ($total_puntos_posibles > 0) ? ($puntos_obtenidos / $total_puntos_posibles) * 100 : 0;
$aprobado = ($calificacion >= $intento['nota_aprobacion']) ? 1 : 0;

// Obtener el ID del curso
$curso_id = null;
if (isset($intento['examen_curso_id']) && !empty($intento['examen_curso_id'])) {
    // Si el examen tiene directamente el curso_id
    $curso_id = $intento['examen_curso_id'];
} else {
    // Obtener el ID del curso a través del módulo
    $stmt = $db->prepare("
        SELECT c.id 
        FROM cursos c
        JOIN modulos m ON c.id = m.curso_id
        WHERE m.id = ?
    ");
    $stmt->bind_param("i", $intento['modulo_id']);
    $stmt->execute();
    $curso_result = $stmt->get_result();

    if ($curso_result->num_rows > 0) {
        $curso_id = $curso_result->fetch_assoc()['id'];
    }
}

// Buscar la inscripción correspondiente
$inscripcion_id = null;
if ($curso_id) {
    $stmt = $db->prepare("
        SELECT id FROM inscripciones 
        WHERE usuario_id = ? AND curso_id = ? 
        LIMIT 1
    ");
    $stmt->bind_param("ii", $user_id, $curso_id);
    $stmt->execute();
    $inscripcion_result = $stmt->get_result();

    if ($inscripcion_result->num_rows > 0) {
        $inscripcion_id = $inscripcion_result->fetch_assoc()['id'];
    }
}

// Actualizar el intento con la calificación y la inscripción
if ($inscripcion_id) {
    $stmt = $db->prepare("UPDATE intentos_examen 
                        SET fecha_fin = NOW(), estado = 'completado', calificacion = ?, aprobado = ?, inscripcion_id = ? 
                        WHERE id = ?");
    $stmt->bind_param("diii", $calificacion, $aprobado, $inscripcion_id, $intento_id);
} else {
    $stmt = $db->prepare("UPDATE intentos_examen 
                        SET fecha_fin = NOW(), estado = 'completado', calificacion = ?, aprobado = ? 
                        WHERE id = ?");
    $stmt->bind_param("dii", $calificacion, $aprobado, $intento_id);
}
$stmt->execute();

// Si el examen fue aprobado, registrar el certificado
if ($aprobado && $curso_id) {
    // Obtener el nombre del usuario para la ruta del certificado
    $stmt = $db->prepare("SELECT nombre FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $usuario_result = $stmt->get_result();

    if ($usuario_result->num_rows > 0) {
        $nombre_usuario = $usuario_result->fetch_assoc()['nombre'];
        $nombre_usuario_url = rawurlencode(str_replace(' ', '_', $nombre_usuario));

        // Generar ruta del certificado
        $ruta_certificado = "Certificado_{$user_id}_{$curso_id}";

        // Generar código de validación único
        $codigo_validacion = md5($user_id . $curso_id . time());

        // Verificar si ya existe un certificado para este usuario y curso
        $stmt = $db->prepare("SELECT id FROM certificados WHERE usuario_id = ? AND curso_id = ?");
        $stmt->bind_param("ii", $user_id, $curso_id);
        $stmt->execute();
        $certificado_existente = $stmt->get_result();

        if ($certificado_existente->num_rows > 0) {
            // Actualizar el certificado existente
            $stmt = $db->prepare("
                UPDATE certificados 
                SET examen_id = ?, intento_id = ?, ruta_certificado = ?, codigo_validacion = ?, fecha_generacion = NOW() 
                WHERE usuario_id = ? AND curso_id = ?
            ");
            $stmt->bind_param("iiissii", $intento['examen_id'], $intento_id, $ruta_certificado, $codigo_validacion, $user_id, $curso_id);
            $stmt->execute();
        } else {
            // Insertar nuevo certificado
            $stmt = $db->prepare("
                INSERT INTO certificados (usuario_id, examen_id, intento_id, curso_id, ruta_certificado, codigo_validacion) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("iiiiss", $user_id, $intento['examen_id'], $intento_id, $curso_id, $ruta_certificado, $codigo_validacion);
            $stmt->execute();
        }
    }
}

// Redirigir a la página de resultados
header('Location: ../Resultado_Examen_' . $intento_id);
exit;
