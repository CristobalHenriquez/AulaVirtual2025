<?php
// Función para ajustar la zona horaria de la fecha de inicio
function ajustarFechaInicio($fecha_db)
{
    // Crear objeto DateTime con la fecha de la base de datos
    $fecha = new DateTime($fecha_db);

    // Restar 4 horas para corregir la diferencia de zona horaria
    $fecha->modify('-4 hours');

    // Devolver la fecha formateada
    return $fecha->format('d/m/Y H:i');
}

// Calcular tiempo restante si hay un intento en progreso
$tiempo_restante = 0;
if ($intento_actual) {
    $tiempo_inicio = strtotime($intento_actual['fecha_inicio']);
    $tiempo_limite_segundos = $examen['tiempo_limite'] * 60;
    $tiempo_actual = time();
    $tiempo_transcurrido = $tiempo_actual - $tiempo_inicio;
    $tiempo_restante = max(0, $tiempo_limite_segundos - $tiempo_transcurrido);
}
?>

<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="Alumno" style="color: #000;"><u>Panel de Estudiante</u></a></li>
                    <li class="breadcrumb-item active" style="color: #000;" aria-current="page">Examen: <?php echo htmlspecialchars($modulo['titulo']); ?></li>
                </ol>
            </nav>

            <div class="card shadow-sm mb-5">
                <div class="card-header text-white" style="background-color: #5C7487;">
                    <h2 class="mb-0 fs-4" style="color: #fff;"><b><?php echo htmlspecialchars($examen['titulo']); ?></b></h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <p><?php echo nl2br(htmlspecialchars($examen['descripcion'])); ?></p>

                            <div class="alert alert-info">
                                <h5><i class="bi bi-info-circle-fill me-2"></i>Información del examen</h5>
                                <ul class="mb-0">
                                    <li><strong>Módulo:</strong> <?php echo htmlspecialchars($modulo['titulo']); ?></li>
                                    <li><strong>Curso:</strong> <?php echo htmlspecialchars($modulo['curso_titulo']); ?></li>
                                    <li><strong>Tiempo límite:</strong> <?php echo $examen['tiempo_limite']; ?> minutos</li>
                                    <li><strong>Intentos máximos diarios:</strong> <?php echo $examen['intentos_maximos_diarios']; ?></li>
                                    <li><strong>Nota mínima para aprobar:</strong> <?php echo $examen['nota_aprobacion']; ?>%</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Estado actual</h5>
                                    <p><strong>Intentos realizados hoy:</strong> <?php echo $intentos_hoy; ?> de <?php echo $examen['intentos_maximos_diarios']; ?></p>

                                    <?php if ($examen_aprobado): ?>
                                        <div class="alert alert-success">
                                            <p class="mb-2"><strong>¡Examen aprobado!</strong></p>
                                            <p class="mb-0">Has completado este examen satisfactoriamente.</p>
                                        </div>
                                        <div class="d-grid gap-2">
                                            <button class="btn btn-secondary w-100" disabled>
                                                <i class="bi bi-lock-fill me-2"></i>Examen completado
                                            </button>
                                            <a href="Certificado_<?php
                                                                    // Obtener el ID del curso
                                                                    $stmt = $db->prepare("
                                                                                                            SELECT c.id FROM cursos c
                                                                                                            JOIN modulos m ON c.id = m.curso_id
                                                                                                            WHERE m.id = ?
                                                                                                            LIMIT 1
                                                                                                        ");
                                                                    $stmt->bind_param("i", $modulo['id']);
                                                                    $stmt->execute();
                                                                    $curso_data = $stmt->get_result()->fetch_assoc();
                                                                    $curso_id = $curso_data['id'];
                                                                    
                                                                    echo $user_id . '_' . $curso_id;
                                                                    ?>" class="btn btn-success" target="_blank">
                                                <i class="bi bi-award-fill me-2"></i>Ver certificado
                                            </a>
                                        </div>
                                    <?php elseif ($intento_actual): ?>
                                        <div class="alert alert-warning">
                                            <p class="mb-2"><strong>Tienes un examen en progreso</strong></p>
                                            <p class="mb-0">Iniciado: <?php echo ajustarFechaInicio($intento_actual['fecha_inicio']); ?></p>

                                            <!-- Temporizador -->
                                            <div class="mt-2 d-flex align-items-center">
                                                <i class="bi bi-clock me-2"></i>
                                                <strong>Tiempo restante: </strong>
                                                <span id="timer-display" class="ms-2 badge bg-warning text-dark" data-tiempo-restante="<?php echo $tiempo_restante; ?>">
                                                    --:--
                                                </span>
                                            </div>
                                        </div>
                                        <a href="Realizar_Examen_<?php echo $intento_actual['id']; ?>" class="btn btn-warning w-100">
                                            <i class="bi bi-arrow-return-right me-2"></i>Continuar examen
                                        </a>
                                    <?php elseif ($intentos_hoy >= $examen['intentos_maximos_diarios']): ?>
                                        <div class="alert alert-danger">
                                            <p class="mb-0">Has alcanzado el número máximo de intentos diarios.</p>
                                        </div>
                                        <button class="btn btn-secondary w-100" disabled>
                                            <i class="bi bi-lock-fill me-2"></i>Iniciar examen
                                        </button>
                                    <?php else: ?>
                                        <a href="controladores/iniciar-examen.php?examen_id=<?php echo $examen['id']; ?>" class="btn btn-success w-100">
                                            <i class="bi bi-play-fill me-2"></i>Iniciar examen
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($intentos_completados)): ?>
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h3 class="mb-0 fs-5"><b>Historial de intentos</b></h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Calificación</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($intentos_completados as $intento): ?>
                                        <tr>
                                            <td><?php echo ajustarFechaInicio($intento['fecha_inicio']); ?></td>
                                            <td>
                                                <?php if ($intento['calificacion'] >= $examen['nota_aprobacion']): ?>
                                                    <span class="badge bg-success"><?php echo $intento['calificacion']; ?>%</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger"><?php echo $intento['calificacion']; ?>%</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($intento['aprobado']): ?>
                                                    <span class="badge bg-success">Aprobado</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">No aprobado</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="Resultado_Examen_<?php echo $intento['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="bi bi-eye-fill"></i> Ver resultados
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Script para el temporizador -->
<?php if ($intento_actual): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const timerDisplay = document.getElementById('timer-display');
            let tiempoRestante = parseInt(timerDisplay.getAttribute('data-tiempo-restante'));

            function actualizarTimer() {
                const minutos = Math.floor(tiempoRestante / 60);
                const segundos = tiempoRestante % 60;

                timerDisplay.textContent = `${minutos.toString().padStart(2, '0')}:${segundos.toString().padStart(2, '0')}`;

                if (tiempoRestante <= 300) { // 5 minutos o menos
                    timerDisplay.classList.remove('bg-warning', 'text-dark');
                    timerDisplay.classList.add('bg-danger', 'text-white');
                }

                if (tiempoRestante <= 0) {
                    clearInterval(timerInterval);
                    timerDisplay.textContent = '00:00';

                    // Opcional: Mostrar un mensaje o redirigir
                    Swal.fire({
                        title: '¡Tiempo agotado!',
                        text: 'El tiempo para completar el examen ha terminado.',
                        icon: 'warning',
                        confirmButtonText: 'Entendido'
                    }).then(() => {
                        window.location.reload();
                    });
                }

                tiempoRestante--;
            }

            actualizarTimer(); // Actualizar inmediatamente
            const timerInterval = setInterval(actualizarTimer, 1000);
        });
    </script>
<?php endif; ?>