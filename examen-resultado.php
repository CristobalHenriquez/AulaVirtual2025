<?php
//VERIFICAR SESION
session_start();
require_once 'includes/auth.php';

// Incluir conexión
include_once 'includes/head.php';

// Verificar que se ha proporcionado un ID de intento
if (!isset($_GET['intento_id']) || empty($_GET['intento_id'])) {
    header('Location: student.php');
    exit;
}

$intento_id = intval($_GET['intento_id']);
$user_id = $_SESSION['id'];

// Obtener información del intento
$stmt = $db->prepare("SELECT i.*, e.titulo as examen_titulo, e.descripcion as examen_descripcion, 
                  e.tiempo_limite, e.nota_aprobacion, m.titulo as modulo_titulo, c.titulo as curso_titulo,
                  m.id as modulo_id, e.id as examen_id
                  FROM intentos_examen i
                  JOIN examenes e ON i.examen_id = e.id
                  JOIN modulos m ON e.modulo_id = m.id
                  JOIN cursos c ON m.curso_id = c.id
                  WHERE i.id = ? AND i.usuario_id = ?");
$stmt->bind_param("ii", $intento_id, $user_id);
$stmt->execute();
$intento = $stmt->get_result()->fetch_assoc();

if (!$intento) {
    header('Location: student.php');
    exit;
}

// Si el intento está en progreso, redirigir a examen-realizar.php
if ($intento['estado'] == 'en_progreso') {
    header('Location: Realizar_Examen_' . $intento_id);
    exit;
}

// Obtener todas las preguntas del examen
$stmt = $db->prepare("SELECT * FROM preguntas_examen WHERE examen_id = ? ORDER BY orden, id");
$stmt->bind_param("i", $intento['examen_id']);
$stmt->execute();
$preguntas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener las respuestas dadas por el usuario
$stmt = $db->prepare("SELECT ru.*, pe.texto_pregunta, op.texto_opcion, op.es_correcta
                  FROM respuestas_usuario ru
                  JOIN preguntas_examen pe ON ru.pregunta_id = pe.id
                  JOIN opciones_respuesta op ON ru.opcion_id = op.id
                  WHERE ru.intento_id = ?");
$stmt->bind_param("i", $intento_id);
$stmt->execute();
$respuestas_usuario = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Crear un array asociativo para facilitar el acceso a las respuestas
$respuestas_por_pregunta = [];
foreach ($respuestas_usuario as $respuesta) {
    $respuestas_por_pregunta[$respuesta['pregunta_id']] = $respuesta;
}

// Obtener datos del usuario
$stmt = $db->prepare("SELECT nombre FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

// Función para ajustar la zona horaria de la fecha de inicio
function ajustarFechaInicio($fecha_db)
{
    // Crear objeto DateTime con la fecha de la base de datos
    $fecha = new DateTime($fecha_db);

    // Restar 4 horas para corregir la diferencia de zona horaria
    $fecha->modify('-5 hours');

    // Devolver la fecha formateada
    return $fecha->format('d/m/Y H:i');
}

// Función para formatear fecha sin ajuste (para la fecha de finalización)
function formatearFecha($fecha_db)
{
    return date('d/m/Y H:i', strtotime($fecha_db));
}
?>

<body class="">
    <?php
    //INCLUYO HEADER
    include_once 'includes/header.php';
    ?>
    <main class="pb-5">
        <div class="container py-4">
            <div class="row">
                <div class="col-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="Alumno" style="color: #000;"><u>Panel de Estudiante</u></a></li>
                            <li class="breadcrumb-item"><a href="Examen_Modulo_<?php echo $intento['modulo_id']; ?>" style="color: #000;"><u>Examen</u></a></li>
                            <li class="breadcrumb-item active" aria-current="page" style="color: #000;">Resultados del examen</li>
                        </ol>
                    </nav>

                    <div class="card shadow-xl mb-4">
                        <div class="card-header text-white" style="background-color: #5C7487;">
                            <h2 class="mb-0 fs-4" style="color: #fff;"><b>Resultados: <?php echo htmlspecialchars($intento['examen_titulo']); ?></b></h2>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-8">
                                    <h5>Información del examen</h5>
                                    <p><?php echo nl2br(htmlspecialchars($intento['examen_descripcion'])); ?></p>

                                    <ul class="list-group">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><strong>Módulo:</strong> <?php echo htmlspecialchars($intento['modulo_titulo']); ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><strong>Curso:</strong> <?php echo htmlspecialchars($intento['curso_titulo']); ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><strong>Fecha de realización:</strong> <?php echo ajustarFechaInicio($intento['fecha_inicio']); ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><strong>Fecha de finalización:</strong> <?php echo formatearFecha($intento['fecha_fin']); ?></span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-4">
                                    <div class="card <?php echo $intento['aprobado'] ? 'bg-success' : 'bg-danger'; ?> text-white">
                                        <div class="card-body text-center">
                                            <h3 class="display-4 mb-0"><?php echo number_format($intento['calificacion'], 2); ?>%</h3>
                                            <p class="lead mt-2 mb-0">
                                                <?php if ($intento['aprobado']): ?>
                                                    <i class="bi bi-check-circle-fill me-2"></i>APROBADO
                                                <?php else: ?>
                                                    <i class="bi bi-x-circle-fill me-2"></i>NO APROBADO
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                        <div class="card-footer text-center">
                                            <small>Nota mínima para aprobar: <?php echo $intento['nota_aprobacion']; ?>%</small>
                                        </div>
                                    </div>

                                    <div class="mt-3 text-center">
                                        <a href="Examen_Modulo_<?php echo $intento['modulo_id']; ?>" class="btn btn-secondary">
                                            <i class="bi bi-arrow-left me-2"></i>Volver al examen
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <h4 class="mb-3">Detalle de respuestas</h4>

                            <?php foreach ($preguntas as $index => $pregunta): ?>
                                <?php
                                $respondida = isset($respuestas_por_pregunta[$pregunta['id']]);
                                $es_correcta = $respondida && $respuestas_por_pregunta[$pregunta['id']]['es_correcta'];
                                $header_class = $es_correcta ? 'bg-success' : 'bg-danger';
                                ?>
                                <div class="card mb-3 border-<?php echo $es_correcta ? 'success' : 'danger'; ?>">
                                    <div class="card-header <?php echo $header_class; ?> text-white">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0"><b>Pregunta <?php echo $index + 1; ?></b></h5>
                                            <span>
                                                <?php if ($es_correcta): ?>
                                                    <i class="bi bi-check-circle-fill me-1"></i>Correcta
                                                <?php else: ?>
                                                    <i class="bi bi-x-circle-fill me-1"></i>Incorrecta
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-3"><?php echo nl2br(htmlspecialchars($pregunta['texto_pregunta'])); ?></p>

                                        <?php
                                        // Obtener todas las opciones para esta pregunta
                                        $stmt = $db->prepare("SELECT * FROM opciones_respuesta WHERE pregunta_id = ? ORDER BY orden, id");
                                        $stmt->bind_param("i", $pregunta['id']);
                                        $stmt->execute();
                                        $opciones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                                        ?>

                                        <div class="opciones-container">
                                            <?php foreach ($opciones as $opcion): ?>
                                                <?php
                                                $es_respuesta_usuario = $respondida && $respuestas_por_pregunta[$pregunta['id']]['opcion_id'] == $opcion['id'];
                                                $mostrar_badge_correcta = $opcion['es_correcta'] && $es_correcta;
                                                ?>
                                                <div class="form-check mb-2">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-2">
                                                            <?php if ($es_respuesta_usuario): ?>
                                                                <i class="bi bi-record-fill text-primary"></i>
                                                            <?php else: ?>
                                                                <i class="bi bi-circle"></i>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="<?php echo $mostrar_badge_correcta ? 'text-success fw-bold' : ''; ?>">
                                                            <?php echo htmlspecialchars($opcion['texto_opcion']); ?>
                                                            <?php if ($mostrar_badge_correcta): ?>
                                                                <span class="badge bg-success ms-2">Respuesta correcta</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- FOOTER Y SCRIPTS -->
    <?php
    include_once 'includes/footer.php';
    include_once 'includes/scripts.php';
    ?>
</body>

</html>