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
$user_id = $_SESSION['id']; // Usando el nombre correcto de la variable de sesión

// Obtener información del intento SIN verificar el estado
$stmt = $db->prepare("SELECT i.*, e.titulo as examen_titulo, e.descripcion as examen_descripcion, 
                    e.tiempo_limite, e.nota_aprobacion, m.titulo as modulo_titulo, c.titulo as curso_titulo,
                    m.id as modulo_id, i.estado
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

// Verificar si el intento está completado
if ($intento['estado'] == 'completado') {
    header('Location: Resultado_Examen_' . $intento_id);
    exit;
}

// Si el intento no está en progreso, actualizarlo
if ($intento['estado'] != 'en_progreso') {
    $update_stmt = $db->prepare("UPDATE intentos_examen SET estado = 'en_progreso' WHERE id = ?");
    $update_stmt->bind_param("i", $intento_id);
    $update_stmt->execute();
}

// Calcular tiempo restante
$tiempo_inicio = strtotime($intento['fecha_inicio']);
$tiempo_limite_segundos = $intento['tiempo_limite'] * 60;
$tiempo_actual = time();
$tiempo_transcurrido = $tiempo_actual - $tiempo_inicio;
$tiempo_restante = $tiempo_limite_segundos - $tiempo_transcurrido;

// SOLUCIÓN: Si es un intento nuevo (creado hace menos de 10 segundos) y el tiempo aparece como expirado,
// actualizar la fecha de inicio al momento actual
$es_intento_nuevo = ($tiempo_transcurrido < 10); // Creado hace menos de 10 segundos
$tiempo_expirado = ($tiempo_restante <= 0);

if ($es_intento_nuevo && $tiempo_expirado) {
    // Actualizar la fecha de inicio al momento actual
    $nueva_fecha_inicio = date('Y-m-d H:i:s');
    $stmt = $db->prepare("UPDATE intentos_examen SET fecha_inicio = ? WHERE id = ?");
    $stmt->bind_param("si", $nueva_fecha_inicio, $intento_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Recalcular el tiempo restante
        $tiempo_inicio = time();
        $tiempo_transcurrido = 0;
        $tiempo_restante = $tiempo_limite_segundos;
    }
}
// Verificar si hay un problema con el tiempo límite (valor 0 o negativo)
else if ($intento['tiempo_limite'] <= 0) {
    // Establecer un tiempo límite predeterminado (30 minutos)
    $tiempo_limite_predeterminado = 30;
    $stmt = $db->prepare("UPDATE examenes SET tiempo_limite = ? WHERE id = ?");
    $stmt->bind_param("ii", $tiempo_limite_predeterminado, $intento['examen_id']);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Actualizar también la fecha de inicio
        $nueva_fecha_inicio = date('Y-m-d H:i:s');
        $stmt = $db->prepare("UPDATE intentos_examen SET fecha_inicio = ? WHERE id = ?");
        $stmt->bind_param("si", $nueva_fecha_inicio, $intento_id);
        $stmt->execute();

        // Recalcular el tiempo restante
        $tiempo_limite_segundos = $tiempo_limite_predeterminado * 60;
        $tiempo_inicio = time();
        $tiempo_transcurrido = 0;
        $tiempo_restante = $tiempo_limite_segundos;
    }
}

// Si después de las correcciones el tiempo sigue expirado, finalizar el examen
if ($tiempo_restante <= 0) {
    header('Location: controladores/finalizar-examen.php?intento_id=' . $intento_id . '&timeout=1');
    exit;
}

// Obtener todas las preguntas del examen
$stmt = $db->prepare("SELECT * FROM preguntas_examen WHERE examen_id = ? ORDER BY orden, id");
$stmt->bind_param("i", $intento['examen_id']);
$stmt->execute();
$preguntas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener las respuestas ya dadas por el usuario
$stmt = $db->prepare("SELECT * FROM respuestas_usuario WHERE intento_id = ?");
$stmt->bind_param("i", $intento_id);
$stmt->execute();
$respuestas_usuario = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Crear un array asociativo para facilitar el acceso a las respuestas
$respuestas_por_pregunta = [];
foreach ($respuestas_usuario as $respuesta) {
    $respuestas_por_pregunta[$respuesta['pregunta_id']] = $respuesta['opcion_id'];
}

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
        <div class="container py-4">
            <div class="row">
                <div class="col-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="Alumno" style="color: #000;"><u>Panel de Estudiante</u></a></li>
                            <li class="breadcrumb-item"><a href="Examen_Modulo_<?php echo $intento['modulo_id']; ?>" style="color: #000;"><u>Examen</u></a></li>
                            <li class="breadcrumb-item active" aria-current="page" style="color: #000;">Realizando examen</li>
                        </ol>
                    </nav>

                    <div class="card shadow-sm mb-4">
                        <div class="card-header text-white d-flex justify-content-between align-items-center" style="background-color: #35536A;">
                            <h2 class="mb-0 fs-4" style="color: #fff;"><b><?php echo htmlspecialchars($intento['examen_titulo']); ?></b></h2>
                            <div class="timer-container">
                                <div id="timer" class="badge bg-warning text-dark fs-5" data-tiempo-restante="<?php echo $tiempo_restante; ?>">
                                    <i class="bi bi-clock me-1"></i> <span id="timer-display">--:--</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-4">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <h5 class="mb-1"><i class="bi bi-info-circle-fill me-2"></i>Información del examen</h5>
                                        <p class="mb-0">Responde todas las preguntas y haz clic en "Finalizar examen" cuando hayas terminado.</p>
                                    </div>
                                    <div class="ms-auto">
                                        <form action="controladores/finalizar-examen.php" method="post" id="form-finalizar">
                                            <input type="hidden" name="intento_id" value="<?php echo $intento_id; ?>">
                                            <button type="button" id="btn-finalizar" class="btn btn-success">
                                                <i class="bi bi-check-circle-fill me-2"></i>Finalizar examen
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <form id="examen-form">
                                <input type="hidden" name="intento_id" value="<?php echo $intento_id; ?>">

                                <?php foreach ($preguntas as $index => $pregunta): ?>
                                    <div class="card mb-4 pregunta-card">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0">Pregunta <?php echo $index + 1; ?></h5>
                                        </div>
                                        <div class="card-body">
                                            <p class="pregunta-texto"><?php echo nl2br(htmlspecialchars($pregunta['texto_pregunta'])); ?></p>

                                            <?php if ($pregunta['tipo'] == 'opcion_multiple'): ?>
                                                <?php
                                                // Obtener opciones para esta pregunta
                                                $stmt = $db->prepare("SELECT * FROM opciones_respuesta WHERE pregunta_id = ? ORDER BY orden, id");
                                                $stmt->bind_param("i", $pregunta['id']);
                                                $stmt->execute();
                                                $opciones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                                                ?>

                                                <div class="opciones-container">
                                                    <?php foreach ($opciones as $opcion): ?>
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input respuesta-input" type="radio"
                                                                name="respuesta_<?php echo $pregunta['id']; ?>"
                                                                id="opcion_<?php echo $opcion['id']; ?>"
                                                                value="<?php echo $opcion['id']; ?>"
                                                                data-pregunta-id="<?php echo $pregunta['id']; ?>"
                                                                <?php echo (isset($respuestas_por_pregunta[$pregunta['id']]) && $respuestas_por_pregunta[$pregunta['id']] == $opcion['id']) ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="opcion_<?php echo $opcion['id']; ?>">
                                                                <?php echo htmlspecialchars($opcion['texto_opcion']); ?>
                                                            </label>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>

                                            <?php elseif ($pregunta['tipo'] == 'verdadero_falso'): ?>
                                                <?php
                                                // Obtener opciones para esta pregunta (verdadero/falso)
                                                $stmt = $db->prepare("SELECT * FROM opciones_respuesta WHERE pregunta_id = ? ORDER BY id");
                                                $stmt->bind_param("i", $pregunta['id']);
                                                $stmt->execute();
                                                $opciones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                                                ?>

                                                <div class="opciones-container">
                                                    <?php foreach ($opciones as $opcion): ?>
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input respuesta-input" type="radio"
                                                                name="respuesta_<?php echo $pregunta['id']; ?>"
                                                                id="opcion_<?php echo $opcion['id']; ?>"
                                                                value="<?php echo $opcion['id']; ?>"
                                                                data-pregunta-id="<?php echo $pregunta['id']; ?>"
                                                                <?php echo (isset($respuestas_por_pregunta[$pregunta['id']]) && $respuestas_por_pregunta[$pregunta['id']] == $opcion['id']) ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="opcion_<?php echo $opcion['id']; ?>">
                                                                <?php echo htmlspecialchars($opcion['texto_opcion']); ?>
                                                            </label>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>

                                            <div class="respuesta-status mt-2">
                                                <span class="badge bg-success d-none" id="status_<?php echo $pregunta['id']; ?>">Respuesta guardada</span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <div class="d-flex justify-content-between mt-4">
                                    <a href="Examen_Modulo_<?php echo $intento['modulo_id']; ?>" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left me-2"></i>Volver
                                    </a>
                                </div>
                            </form>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar el temporizador
            const timerElement = document.getElementById('timer');
            const timerDisplay = document.getElementById('timer-display');
            let tiempoRestante = parseInt(timerElement.getAttribute('data-tiempo-restante'));

            function actualizarTimer() {
                const minutos = Math.floor(tiempoRestante / 60);
                const segundos = tiempoRestante % 60;

                timerDisplay.textContent = `${minutos.toString().padStart(2, '0')}:${segundos.toString().padStart(2, '0')}`;

                if (tiempoRestante <= 300) { // 5 minutos o menos
                    timerElement.classList.remove('bg-warning', 'text-dark');
                    timerElement.classList.add('bg-danger', 'text-white');
                }

                if (tiempoRestante <= 0) {
                    clearInterval(timerInterval);
                    alert('¡El tiempo ha terminado! El examen se finalizará automáticamente.');
                    window.location.href = `controladores/finalizar-examen.php?intento_id=<?php echo $intento_id; ?>&timeout=1`;
                }

                tiempoRestante--;
            }

            actualizarTimer(); // Actualizar inmediatamente
            const timerInterval = setInterval(actualizarTimer, 1000);

            // Guardar respuestas automáticamente
            const respuestaInputs = document.querySelectorAll('.respuesta-input');

            respuestaInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const preguntaId = this.getAttribute('data-pregunta-id');
                    const opcionId = this.value;
                    const statusBadge = document.getElementById(`status_${preguntaId}`);

                    // Ocultar el badge de éxito si estaba visible
                    statusBadge.classList.add('d-none');

                    // Enviar la respuesta al servidor
                    fetch('controladores/guardar-respuesta.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `intento_id=<?php echo $intento_id; ?>&pregunta_id=${preguntaId}&opcion_id=${opcionId}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Mostrar el badge de éxito
                                statusBadge.classList.remove('d-none');

                                // Ocultar el badge después de 3 segundos
                                setTimeout(() => {
                                    statusBadge.classList.add('d-none');
                                }, 3000);
                            } else {
                                console.error('Error al guardar la respuesta:', data.message);
                                alert('Error al guardar la respuesta. Por favor, inténtalo de nuevo.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error al guardar la respuesta. Por favor, inténtalo de nuevo.');
                        });
                });
            });

            // Configurar el botón de finalizar examen con SweetAlert2
            const btnFinalizar = document.getElementById('btn-finalizar');
            const formFinalizar = document.getElementById('form-finalizar');

            btnFinalizar.addEventListener('click', function() {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: '¿Deseas finalizar el examen? Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#dc3545',
                    confirmButtonText: 'Sí, finalizar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        formFinalizar.submit();
                    }
                });
            });
        });
    </script>
</body>
</html>