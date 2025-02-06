<?php
//VERIFICAR SESION
session_start();
require_once 'includes/auth.php';
verificarAcceso();

// Incluir conexión
include_once 'includes/head.php';

// Obtener datos del usuario
$user_id = $_SESSION['id'];
$stmt = $db->prepare("SELECT nombre, apellidos, email, municipio FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Obtener cursos inscritos agrupados por año
$sql = "SELECT c.*, c.anio as anio
        FROM cursos c
        INNER JOIN inscripciones i ON c.id = i.curso_id
        WHERE i.usuario_id = ? AND c.id != 26
        ORDER BY c.anio DESC, c.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Agrupar cursos por año
$cursos_por_anio = [];
while ($curso = $result->fetch_assoc()) {
    $cursos_por_anio[$curso['anio']][] = $curso;
}

// Check if the student is enrolled in course 26
$stmt = $db->prepare("SELECT 1 FROM inscripciones WHERE usuario_id = ? AND curso_id = 26 LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$is_enrolled_in_course_26 = $stmt->get_result()->num_rows > 0;

// Fetch course 26 data if enrolled
$course_26_data = null;
if ($is_enrolled_in_course_26) {
    $stmt = $db->prepare("SELECT * FROM cursos WHERE id = 26");
    $stmt->execute();
    $course_26_data = $stmt->get_result()->fetch_assoc();
}

// Fetch resources for course 26
function obtenerRecursosCurso26($db) {
    $stmt = $db->prepare("
        SELECT rm.* 
        FROM recursos_modulo rm
        JOIN modulos m ON rm.modulo_id = m.id
        WHERE m.curso_id = 26
    ");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Custom function to get icon for course 26 resources
function obtenerIconoRecursoCurso26($recurso_id) {
    $iconos = [
        56 => 'bi bi-tree',
        57 => 'bi bi-trash',
        58 => 'bi bi-bicycle',
        59 => 'bi bi-book',
        60 => 'bi bi-building',
        61 => 'bi bi-images'
    ];
    return $iconos[$recurso_id] ?? 'bi bi-file-earmark';
}

// Función para obtener módulos de un curso
function obtenerModulos($db, $curso_id)
{
    $stmt = $db->prepare("SELECT * FROM modulos WHERE curso_id = ? ORDER BY created_at");
    $stmt->bind_param("i", $curso_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Función para obtener recursos de un módulo
function obtenerRecursosModulo($db, $modulo_id)
{
    $stmt = $db->prepare("SELECT * FROM recursos_modulo WHERE modulo_id = ?");
    $stmt->bind_param("i", $modulo_id);
    $stmt->execute();
    $recursos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    foreach ($recursos as &$recurso) {
        $recurso['es_local'] = !filter_var($recurso['url'], FILTER_VALIDATE_URL) && !empty($recurso['archivo_path']);
        $recurso['tipo_real'] = determinarTipoRecurso($recurso);
    }
    
    return $recursos;
}

// Función para determinar el tipo real del recurso
function determinarTipoRecurso($recurso)
{
    if ($recurso['es_local']) {
        $extension = pathinfo($recurso['archivo_path'], PATHINFO_EXTENSION);
    } else {
        $extension = pathinfo($recurso['url'], PATHINFO_EXTENSION);
    }
    
    $extension = strtolower($extension);
    
    if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'svg'])) {
        return 'imagen';
    } elseif (in_array($extension, ['mp4', 'webm', 'ogg'])) {
        return 'video';
    } elseif ($extension === 'pdf') {
        return 'pdf';
    } elseif (in_array($extension, ['ppt', 'pptx'])) {
        return 'powerpoint';
    } else {
        return 'enlace';
    }
}

// Función para obtener el icono correspondiente al tipo de recurso
function obtenerIconoRecurso($tipo)
{
    switch ($tipo) {
        case 'pdf':
            return 'bi bi-file-earmark-pdf';
        case 'video':
            return 'bi bi-play-circle';
        case 'imagen':
            return 'bi bi-image';
        case 'powerpoint':
            return 'bi bi-file-earmark-slides';
        case 'enlace':
            return 'bi bi-link-45deg';
        default:
            return 'bi bi-file-earmark';
    }
}
?>

<body class="">
    <?php
    //INCLUYO HEADER
    include_once 'includes/header.php';
    ?>
    <main class="pb-5" style="background-image: url('assets/img/aula2.jpg'); background-size: cover">
        <?php
        include_once 'templates/student-main.php';
        ?>
    </main>
    <!-- FOOTER Y SCRIPTS -->
    <?php
    include_once 'includes/footer.php';
    include_once 'includes/scripts.php';
    ?>
</body>
</html>