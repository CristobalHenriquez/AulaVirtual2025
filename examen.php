<?php
//VERIFICAR SESION
session_start();
require_once 'includes/auth.php';

// Incluir conexión
include_once 'includes/head.php';

// Verificar que se ha proporcionado un ID de módulo
if (!isset($_GET['modulo_id']) || empty($_GET['modulo_id'])) {
    header('Location: student.php');
    exit;
}

$modulo_id = intval($_GET['modulo_id']);
$user_id = $_SESSION['id'];

// Obtener información del módulo
$stmt = $db->prepare("SELECT m.*, c.titulo as curso_titulo FROM modulos m 
                      JOIN cursos c ON m.curso_id = c.id 
                      WHERE m.id = ?");
$stmt->bind_param("i", $modulo_id);
$stmt->execute();
$modulo = $stmt->get_result()->fetch_assoc();

if (!$modulo) {
    header('Location: student.php');
    exit;
}

// Verificar si el usuario está inscrito en el curso
$stmt = $db->prepare("SELECT 1 FROM inscripciones WHERE usuario_id = ? AND curso_id = ? LIMIT 1");
$stmt->bind_param("ii", $user_id, $modulo['curso_id']);
$stmt->execute();
$inscrito = $stmt->get_result()->num_rows > 0;

if (!$inscrito) {
    header('Location: student.php');
    exit;
}

// Obtener información del examen activo para este módulo
$stmt = $db->prepare("SELECT * FROM examenes WHERE modulo_id = ? AND activo = 1 LIMIT 1");
$stmt->bind_param("i", $modulo_id);
$stmt->execute();
$examen = $stmt->get_result()->fetch_assoc();

if (!$examen) {
    header('Location: student.php');
    exit;
}

// Verificar si el usuario ya ha aprobado el examen
$stmt = $db->prepare("SELECT 1 FROM intentos_examen 
                      WHERE usuario_id = ? AND examen_id = ? AND aprobado = 1 
                      LIMIT 1");
$stmt->bind_param("ii", $user_id, $examen['id']);
$stmt->execute();
$examen_aprobado = $stmt->get_result()->num_rows > 0;

// Verificar si el usuario ya ha alcanzado el número máximo de intentos diarios
$fecha_actual = date('Y-m-d');
$stmt = $db->prepare("SELECT COUNT(*) as intentos_hoy FROM intentos_examen 
                      WHERE usuario_id = ? AND examen_id = ? AND DATE(fecha_inicio) = ? AND estado = 'completado'");
$stmt->bind_param("iis", $user_id, $examen['id'], $fecha_actual);
$stmt->execute();
$intentos_hoy = $stmt->get_result()->fetch_assoc()['intentos_hoy'];

// Verificar si hay un intento en progreso
$stmt = $db->prepare("SELECT * FROM intentos_examen 
                      WHERE usuario_id = ? AND examen_id = ? AND estado = 'en_progreso'
                      ORDER BY fecha_inicio DESC LIMIT 1");
$stmt->bind_param("ii", $user_id, $examen['id']);
$stmt->execute();
$intento_actual = $stmt->get_result()->fetch_assoc();

// Obtener historial de intentos completados
$stmt = $db->prepare("SELECT * FROM intentos_examen 
                      WHERE usuario_id = ? AND examen_id = ? AND estado = 'completado'
                      ORDER BY fecha_inicio DESC");
$stmt->bind_param("ii", $user_id, $examen['id']);
$stmt->execute();
$intentos_completados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener datos del usuario
$stmt = $db->prepare("SELECT nombre FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
?>

<body class="">
    <?php
    //INCLUYO HEADER
    include_once 'includes/header.php';
    ?>
    <main class="pb-5">
        <?php
        include_once 'templates/examen-main.php';
        ?>
    </main>
    <!-- FOOTER Y SCRIPTS -->
    <?php
    include_once 'includes/footer.php';
    include_once 'includes/scripts.php';
    ?>
    
    <!-- Scripts específicos para el examen -->
    <script src="assets/js/examen.js"></script>
</body>
</html>