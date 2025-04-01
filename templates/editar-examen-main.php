<?php
// Obtener datos del módulo
$modulo_id = isset($_GET['modulo_id']) ? (int)$_GET['modulo_id'] : 0;

$stmt = $db->prepare("SELECT m.*, c.titulo AS curso_titulo FROM modulos m JOIN cursos c ON m.curso_id = c.id WHERE m.id = ?");
$stmt->bind_param("i", $modulo_id);
$stmt->execute();
$modulo = $stmt->get_result()->fetch_assoc();

if (!$modulo) {
    header('Location: admin-cursos.php');
    exit;
}

// Obtener exámenes del módulo
$stmt = $db->prepare("SELECT e.*, COUNT(p.id) as num_preguntas 
                     FROM examenes e 
                     LEFT JOIN preguntas_examen p ON e.id = p.examen_id 
                     WHERE e.modulo_id = ? 
                     GROUP BY e.id");
$stmt->bind_param("i", $modulo_id);
$stmt->execute();
$examenes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<style>
    .examen-item {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .pregunta-item {
        background-color: #ffffff;
        border: 1px solid #e9ecef;
        border-radius: 0.25rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .opcion-item {
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 0.25rem;
        padding: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .collapse-toggle {
        cursor: pointer;
    }

    .collapse-toggle:hover {
        text-decoration: underline;
    }

    .modal-xl {
        max-width: 1140px;
    }

    .modal-lg {
        max-width: 900px;
    }
</style>

<!-- TITULO -->
<div class="page-title" data-aos="fade">
    <div class="heading p-5">
        <div class="container">
            <div class="row d-flex justify-content-center text-center">
                <div class="col-lg-8">
                    <h1>Editar Exámenes del Módulo<i class="bi bi-pencil-square m-2"></i></h1>
                    <p class="mb-0">Módulo: <?php echo htmlspecialchars($modulo['titulo']); ?></p>
                    <p class="mb-0">Curso: <?php echo htmlspecialchars($modulo['curso_titulo']); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- BOTONES -->
<div class="container-fluid col-lg-10 pt-3">
    <div class="d-flex justify-content-between">
        <a href="editar-recursos.php?modulo_id=<?php echo $modulo_id; ?>" class="btn btn-secondary shadow">
            <i class="bi bi-arrow-90deg-left me-2"></i>Volver a editar recursos
        </a>
    </div>
</div>

<div class="container py-3">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-lg">
                <div class="card-header">
                    <h3 class="card-title mb-0 text-center"><b>Exámenes del Módulo</b></h3>
                </div>
                <div class="card-body p-4">
                    <form action="controladores/modificar_examen.php" method="POST">
                        <input type="hidden" name="modulo_id" value="<?php echo $modulo_id; ?>">
                        <input type="hidden" name="examenes_eliminados" id="examenes_eliminados" value="">

                        <div id="examenes-container">
                            <?php if (count($examenes) > 0): ?>
                                <?php foreach ($examenes as $examen): ?>
                                    <div class="examen-item mb-4">
                                        <input type="hidden" name="examen_ids[]" value="<?php echo $examen['id']; ?>">

                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h5 class="collapse-toggle" data-bs-toggle="collapse" data-bs-target="#examen<?php echo $examen['id']; ?>">
                                                <i class="bi bi-chevron-down me-2"></i>
                                                Examen: <?php echo htmlspecialchars($examen['titulo']); ?>
                                            </h5>
                                            <div>
                                                <button type="button" class="btn btn-outline-info btn-sm me-2" onclick="mostrarPreguntas(<?php echo $examen['id']; ?>)">
                                                    <i class="bi bi-list-check me-1"></i> Preguntas (<?php echo $examen['num_preguntas']; ?>)
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="eliminarExamen(this)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="collapse show" id="examen<?php echo $examen['id']; ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Título del Examen</label>
                                                <input type="text" class="form-control" name="examen_titulos[]" value="<?php echo htmlspecialchars($examen['titulo']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Descripción</label>
                                                <textarea class="form-control" name="examen_descripciones[]" rows="2"><?php echo htmlspecialchars($examen['descripcion']); ?></textarea>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Tiempo Límite (minutos)</label>
                                                    <input type="number" class="form-control" name="examen_tiempos[]" value="<?php echo $examen['tiempo_limite']; ?>" min="0">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Intentos Máximos Diarios</label>
                                                    <input type="number" class="form-control" name="examen_intentos[]" value="<?php echo $examen['intentos_maximos_diarios']; ?>" min="1" required>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Nota de Aprobación (%)</label>
                                                    <input type="number" class="form-control" name="examen_notas[]" value="<?php echo $examen['nota_aprobacion']; ?>" min="0" max="100" required>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="examen_activos[]" value="<?php echo $examen['id']; ?>" <?php echo $examen['activo'] ? 'checked' : ''; ?>>
                                                    <label class="form-check-label">Examen Activo</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    No hay exámenes creados para este módulo. Utilice el botón "Agregar Nuevo Examen" para crear uno.
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex justify-content-end">
                            <?php if (count($examenes) == 0): ?>
                                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalAgregarExamen">
                                    <i class="bi bi-plus-circle me-2"></i>Agregar Nuevo Examen
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn btn-secondary mb-3" disabled title="Solo puede haber un examen por módulo">
                                    <i class="bi bi-plus-circle me-2"></i>Agregar Nuevo Examen
                                </button>
                            <?php endif; ?>
                        </div>


                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-save me-2"></i>Guardar Cambios
                            </button>
                            <a href="editar-curso.php?id=<?php echo $modulo['curso_id']; ?>" class="btn btn-danger">
                                <i class="bi bi-x-circle me-2"></i>Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Agregar/Editar Examen -->
<div class="modal fade" id="modalAgregarExamen" tabindex="-1" aria-labelledby="modalAgregarExamenLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAgregarExamenLabel">Agregar Nuevo Examen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAgregarExamen" action="controladores/agregar_examen.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="modulo_id" value="<?php echo $modulo_id; ?>">

                    <div class="mb-3">
                        <label for="titulo_examen" class="form-label">Título del Examen</label>
                        <input type="text" class="form-control" id="titulo_examen" name="titulo" required>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion_examen" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion_examen" name="descripcion" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="tiempo_limite" class="form-label">Tiempo Límite (minutos)</label>
                            <input type="number" class="form-control" id="tiempo_limite" name="tiempo_limite" value="60" min="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="intentos_maximos" class="form-label">Intentos Máximos Diarios</label>
                            <input type="number" class="form-control" id="intentos_maximos" name="intentos_maximos" value="3" min="1" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="nota_aprobacion" class="form-label">Nota de Aprobación (%)</label>
                            <input type="number" class="form-control" id="nota_aprobacion" name="nota_aprobacion" value="60" min="0" max="100" required>
                        </div>
                    </div>
                    <div class="mb-3 form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="activo" name="activo" checked>
                        <label class="form-check-label" for="activo">Examen Activo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Mostrar Preguntas -->
<div class="modal fade" id="modalPreguntas" tabindex="-1" aria-labelledby="modalPreguntasLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPreguntasLabel">Preguntas del Examen</h5>
                <button type="button" class="btn-close" onclick="recargarPagina()" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between mb-3">
                    <h6 id="examen-titulo-preguntas"></h6>
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary btn-sm" onclick="agregarPreguntaMultiple()">
                            <i class="bi bi-check-square me-1"></i>Agregar Opción Múltiple
                        </button>
                        <button type="button" class="btn btn-success btn-sm" onclick="agregarPreguntaVF()">
                            <i class="bi bi-check-circle me-1"></i><i class="bi bi-slash-circle me-1"></i>Agregar Verdadero/Falso
                        </button>
                    </div>
                </div>
                <div id="preguntas-container">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p>Cargando preguntas...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="recargarPagina()">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Agregar/Editar Pregunta -->
<div class="modal fade" id="modalPregunta" tabindex="-1" aria-labelledby="modalPreguntaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPreguntaLabel">Agregar Nueva Pregunta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formPregunta" action="controladores/gestionar_pregunta.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="accion" id="accion_pregunta" value="crear">
                    <input type="hidden" name="pregunta_id" id="pregunta_id" value="">
                    <input type="hidden" name="examen_id" id="examen_id_pregunta" value="">
                    <input type="hidden" name="modulo_id" value="<?php echo $modulo_id; ?>">

                    <div class="mb-3">
                        <label for="texto_pregunta" class="form-label">Texto de la Pregunta</label>
                        <textarea class="form-control" id="texto_pregunta" name="texto_pregunta" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <input type="hidden" id="tipo_pregunta" name="tipo_pregunta" value="opcion_multiple">
                        <label for="puntaje" class="form-label">Puntaje</label>
                        <input type="number" class="form-control" id="puntaje" name="puntaje" value="10" min="1" max="100" step="1" required>
                    </div>

                    <div id="opciones-container">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6>Opciones de Respuesta</h6>
                            <div id="btn-agregar-opcion-container">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="mostrarModalOpcion()">
                                    <i class="bi bi-plus-circle me-1"></i>Agregar Opción
                                </button>
                            </div>
                        </div>
                        <div id="opciones-lista">
                            <div class="alert alert-info">
                                Primero guarde la pregunta para poder agregar opciones.
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-warning">Recuerde "Guardar Pregunta" para guardar las opciones nuevas o modificadas</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="volverAModalPreguntas()">Volver</button>
                    <button type="submit" class="btn btn-success">Guardar Pregunta</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Agregar/Editar Opción -->
<div class="modal fade" id="modalOpcion" tabindex="-1" aria-labelledby="modalOpcionLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalOpcionLabel">Agregar Nueva Opción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formOpcion" action="controladores/gestionar_opcion.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="accion" id="accion_opcion" value="crear">
                    <input type="hidden" name="opcion_id" id="opcion_id" value="">
                    <input type="hidden" name="pregunta_id" id="pregunta_id_opcion" value="">
                    <input type="hidden" name="modulo_id" value="<?php echo $modulo_id; ?>">

                    <div class="mb-3">
                        <label for="texto_opcion" class="form-label">Texto de la Opción</label>
                        <input type="text" class="form-control" id="texto_opcion" name="texto_opcion" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="es_correcta" name="es_correcta">
                        <label class="form-check-label" for="es_correcta">Es la respuesta correcta</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="volverAModalPregunta()">Volver</button>
                    <button type="submit" class="btn btn-primary">Guardar Opción</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Confirmación para Eliminar -->
<div class="modal fade" id="modalConfirmacion" tabindex="-1" aria-labelledby="modalConfirmacionLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConfirmacionLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="mensajeConfirmacion">¿Estás seguro de que deseas eliminar este elemento?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Variables globales
    let examenActualId = null;
    let preguntaActualId = null;
    let modalPreguntasInstance = null;
    let modalPreguntaInstance = null;
    let modalOpcionInstance = null;

    // Función para agregar un nuevo examen
    // Función para agregar un nuevo examen
    function agregarExamen() {
        const container = document.getElementById('examenes-container');
        const examenId = 'nuevo_' + Date.now();
        const examenItem = document.createElement('div');
        examenItem.className = 'examen-item mb-4';

        examenItem.innerHTML = `
          <input type="hidden" name="examen_ids[]" value="nuevo">
          <div class="d-flex justify-content-between align-items-center mb-2">
              <h5 class="collapse-toggle" data-bs-toggle="collapse" data-bs-target="#examen${examenId}">
                  <i class="bi bi-chevron-down me-2"></i>
                  Nuevo Examen
              </h5>
              <button type="button" class="btn btn-outline-danger btn-sm" onclick="eliminarExamen(this)">
                  <i class="bi bi-trash"></i>
              </button>
          </div>
          <div class="collapse show" id="examen${examenId}">
              <div class="mb-3">
                  <label class="form-label">Título del Examen</label>
                  <input type="text" class="form-control" name="examen_titulos[]" required>
              </div>
              <div class="mb-3">
                  <label class="form-label">Descripción</label>
                  <textarea class="form-control" name="examen_descripciones[]" rows="2"></textarea>
              </div>
              <div class="row">
                  <div class="col-md-4 mb-3">
                      <label class="form-label">Tiempo Límite (minutos)</label>
                      <input type="number" class="form-control" name="examen_tiempos[]" value="60" min="0">
                  </div>
                  <div class="col-md-4 mb-3">
                      <label class="form-label">Intentos Máximos Diarios</label>
                      <input type="number" class="form-control" name="examen_intentos[]" value="3" min="1" required>
                  </div>
                  <div class="col-md-4 mb-3">
                      <label class="form-label">Nota de Aprobación (%)</label>
                      <input type="number" class="form-control" name="examen_notas[]" value="60" min="0" max="100" required>
                  </div>
              </div>
              <div class="mb-3">
                  <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" name="examen_activos[]" value="${examenId}" checked>
                      <label class="form-check-label">Examen Activo</label>
                  </div>
              </div>
              <div class="alert alert-info">
                  Guarde el examen primero para poder agregar preguntas.
              </div>
          </div>
      `;

        container.appendChild(examenItem);

        // Eliminar el mensaje de alerta si existe
        const alertaInfo = container.querySelector('.alert-info:not([class*="mb-"])');
        if (alertaInfo) {
            alertaInfo.remove();
        }
    }

    // Función para eliminar un examen
    function eliminarExamen(btn) {
        const examenItem = btn.closest('.examen-item');
        const examenId = examenItem.querySelector('input[name="examen_ids[]"]').value;

        // Si es un examen nuevo (no guardado en la BD), simplemente eliminarlo del DOM
        if (examenId === 'nuevo') {
            Swal.fire({
                title: '¿Eliminar examen?',
                text: "Este examen no guardado será eliminado",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    examenItem.remove();

                    // Mostrar mensaje si no hay exámenes
                    const container = document.getElementById('examenes-container');
                    if (container.children.length === 0) {
                        container.innerHTML = `
                        <div class="alert alert-info">
                            No hay exámenes creados para este módulo. Utilice el botón "Agregar Nuevo Examen" para crear uno.
                        </div>
                    `;
                    }

                    Swal.fire(
                        '¡Eliminado!',
                        'El examen ha sido eliminado correctamente.',
                        'success'
                    );
                }
            });
            return;
        }

        // Si es un examen existente, mostrar confirmación y eliminarlo de la BD
        Swal.fire({
            title: '¿Eliminar examen?',
            text: "Se eliminarán también todas sus preguntas y opciones",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: '¿Estás completamente seguro?',
                    text: "Esta acción no se puede deshacer",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonText: 'Cancelar',
                    confirmButtonText: 'Sí, eliminar definitivamente'
                }).then((secondResult) => {
                    if (secondResult.isConfirmed) {
                        // Mostrar indicador de carga
                        Swal.fire({
                            title: 'Eliminando...',
                            text: 'Por favor espere',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Enviar solicitud para eliminar examen directamente
                        fetch('controladores/eliminar_examen.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: `examen_id=${examenId}&modulo_id=<?php echo $modulo_id; ?>`
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Eliminar elemento del DOM
                                    examenItem.remove();

                                    // Mostrar mensaje si no hay exámenes
                                    const container = document.getElementById('examenes-container');
                                    if (container.children.length === 0) {
                                        container.innerHTML = `
                                    <div class="alert alert-info">
                                        No hay exámenes creados para este módulo. Utilice el botón "Agregar Nuevo Examen" para crear uno.
                                    </div>
                                `;
                                    }

                                    Swal.fire({
                                        icon: 'success',
                                        title: '¡Eliminado!',
                                        text: 'El examen ha sido eliminado correctamente de la base de datos.',
                                        showConfirmButton: false,
                                        timer: 1500
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: data.message || 'No se pudo eliminar el examen'
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Ocurrió un error al eliminar el examen'
                                });
                            });
                    }
                });
            }
        });
    }

    // Función para mostrar el modal de preguntas
    function mostrarPreguntas(examenId) {
        examenActualId = examenId;

        // Obtener título del examen
        const examenItem = document.querySelector(`.examen-item input[value="${examenId}"]`).closest('.examen-item');
        const tituloExamen = examenItem.querySelector('h5').textContent.trim().replace('Examen: ', '');

        // Mostrar spinner de carga
        document.getElementById('preguntas-container').innerHTML = `
            <div class="text-center">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p>Cargando preguntas...</p>
            </div>
            `;

        // Mostrar modal de preguntas
        modalPreguntasInstance = new bootstrap.Modal(document.getElementById('modalPreguntas'));
        modalPreguntasInstance.show();

        // Cargar preguntas mediante AJAX
        fetch(`controladores/obtener_preguntas.php?examen_id=${examenId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Calcular estadísticas
                    const preguntas = data.preguntas;
                    const totalPreguntas = preguntas.length;
                    let puntajeTotal = 0;

                    preguntas.forEach(pregunta => {
                        puntajeTotal += parseInt(pregunta.puntaje);
                    });

                    const puntajeFaltante = 100 - puntajeTotal;
                    const estadoPuntaje = puntajeFaltante === 0 ?
                        '<span class="badge bg-success">Completo (100%)</span>' :
                        (puntajeFaltante > 0 ?
                            `<span class="badge bg-warning">Falta un ${puntajeFaltante}%</span>` :
                            `<span class="badge bg-danger">Excede por ${Math.abs(puntajeFaltante)}%</span>`);

                    // Actualizar título con estadísticas
                    document.getElementById('examen-titulo-preguntas').innerHTML = `
                    <div>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <span class="badge bg-primary">Preguntas: ${totalPreguntas}</span>
                            <span class="badge bg-info">Puntaje total: ${puntajeTotal}%</span>
                            ${estadoPuntaje}
                        </div>
                    </div>
                `;

                    mostrarListaPreguntas(preguntas);
                } else {
                    document.getElementById('preguntas-container').innerHTML = `
                  <div class="alert alert-danger">
                      ${data.message || 'Error al cargar las preguntas'}
                  </div>
                `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('preguntas-container').innerHTML = `
              <div class="alert alert-danger">
                  Ocurrió un error al cargar las preguntas
              </div>
            `;
            });
    }



    // Función para mostrar la lista de preguntas
    function mostrarListaPreguntas(preguntas) {
        const container = document.getElementById('preguntas-container');

        if (preguntas.length === 0) {
            container.innerHTML = `
            <div class="alert alert-info">
                No hay preguntas creadas para este examen. Utilice el botón "Agregar Pregunta" para crear una.
            </div>
        `;
            return;
        }

        // Separar preguntas por tipo
        const preguntasOpcionMultiple = preguntas.filter(pregunta => pregunta.tipo === 'opcion_multiple');
        const preguntasVerdaderoFalso = preguntas.filter(pregunta => pregunta.tipo === 'verdadero_falso');

        let html = `
        <div class="row">
            <!-- Columna izquierda: Preguntas de opción múltiple -->
            <div class="col-md-6">
                <h5 class="mb-3 mi-2">Preguntas de Opción Múltiple  <i class="bi bi-check-square"></i></h5>
                ${preguntasOpcionMultiple.length > 0 ? 
                    preguntasOpcionMultiple.map(pregunta => `
                        <div class="pregunta-item mb-3" id="pregunta-${pregunta.id}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="collapse-toggle" data-bs-toggle="collapse" data-bs-target="#pregunta-content-${pregunta.id}">
                                    <i class="bi bi-chevron-down me-2"></i>
                                    ${pregunta.texto_pregunta.length > 50 ? pregunta.texto_pregunta.substring(0, 50) + '...' : pregunta.texto_pregunta}
                                </h6>
                                <div>
                                    <button type="button" class="btn btn-outline-primary btn-sm me-1" onclick="editarPregunta(${pregunta.id})">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="eliminarPregunta(${pregunta.id})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="collapse" id="pregunta-content-${pregunta.id}">
                                <div class="col-12 row">
                                    <div class="mb-3 col-8">
                                        <strong>Texto:</strong> ${pregunta.texto_pregunta}
                                    </div>
                                    <div class="col-4 mb-3">
                                        <strong>Puntaje:</strong> ${pregunta.puntaje}
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <div class="mb-2">
                                        <h6 class="mb-0">Opciones de Respuesta</h6>
                                    </div>
                                    <div id="opciones-pregunta-${pregunta.id}">
                                        ${pregunta.opciones && pregunta.opciones.length > 0 ? 
                                            pregunta.opciones.map(opcion => `
                                                <div class="opcion-item mb-2" id="opcion-${opcion.id}">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <span class="badge ${opcion.es_correcta ? 'bg-success' : 'bg-danger'} me-2">
                                                                ${opcion.es_correcta ? 'Correcta' : 'Incorrecta'}
                                                            </span>
                                                            ${opcion.texto_opcion}
                                                        </div>
                                                    </div>
                                                </div>
                                            `).join('') 
                                            : 
                                            '<div class="alert alert-info">No hay opciones para esta pregunta.</div>'
                                        }
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('') 
                    : 
                    '<div class="alert alert-info">No hay preguntas de opción múltiple.</div>'
                }
            </div>

            <!-- Columna derecha: Preguntas de verdadero/falso -->
            <div class="col-md-6">
                <h5 class="mb-3">Preguntas de Verdadero/Falso  <i class="bi bi-check-circle"></i>  <i class="bi bi-slash-circle"></i></h5>
                ${preguntasVerdaderoFalso.length > 0 ? 
                    preguntasVerdaderoFalso.map(pregunta => `
                        <div class="pregunta-item mb-3" id="pregunta-${pregunta.id}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="collapse-toggle" data-bs-toggle="collapse" data-bs-target="#pregunta-content-${pregunta.id}">
                                    <i class="bi bi-chevron-down me-2"></i>
                                    ${pregunta.texto_pregunta.length > 50 ? pregunta.texto_pregunta.substring(0, 50) + '...' : pregunta.texto_pregunta}
                                </h6>
                                <div>
                                    <button type="button" class="btn btn-outline-primary btn-sm me-1" onclick="editarPregunta(${pregunta.id})">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="eliminarPregunta(${pregunta.id})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="collapse" id="pregunta-content-${pregunta.id}">
                                <div class="mb-3">
                                    <strong>Texto:</strong> ${pregunta.texto_pregunta}
                                </div>
                                <div class="mb-3">
                                    <strong>Puntaje:</strong> ${pregunta.puntaje}
                                </div>

                                <div class="mt-3">
                                    <h6 class="mb-2">Opciones de Respuesta</h6>
                                    <div id="opciones-pregunta-${pregunta.id}">
                                        ${pregunta.opciones && pregunta.opciones.length > 0 ? 
                                            pregunta.opciones.map(opcion => `
                                                <div class="opcion-item mb-2" id="opcion-${opcion.id}">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <span class="badge ${opcion.es_correcta ? 'bg-success' : 'bg-danger'} me-2">
                                                                ${opcion.es_correcta ? 'Correcta' : 'Incorrecta'}
                                                            </span>
                                                            ${opcion.texto_opcion}
                                                        </div>
                                                    </div>
                                                </div>
                                            `).join('') 
                                            : 
                                            '<div class="alert alert-info">No hay opciones para esta pregunta.</div>'
                                        }
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('') 
                    : 
                    '<div class="alert alert-info">No hay preguntas de verdadero/falso.</div>'
                }
            </div>
        </div>
    `;

        container.innerHTML = html;
    }

    // Función para agregar una nueva pregunta de opción múltiple
    function agregarPreguntaMultiple() {
        // Limpiar formulario
        document.getElementById('formPregunta').reset();
        document.getElementById('accion_pregunta').value = 'crear';
        document.getElementById('pregunta_id').value = '';
        document.getElementById('examen_id_pregunta').value = examenActualId;
        document.getElementById('tipo_pregunta').value = 'opcion_multiple';
        document.getElementById('modalPreguntaLabel').textContent = 'Agregar Pregunta de Opción Múltiple';

        // Actualizar el contenedor de opciones para opción múltiple
        toggleTipoPregunta();

        // Ocultar opciones hasta que se guarde la pregunta
        document.getElementById('btn-agregar-opcion-container').style.display = 'none';

        // Ocultar modal de preguntas y mostrar modal de pregunta
        modalPreguntasInstance.hide();
        modalPreguntaInstance = new bootstrap.Modal(document.getElementById('modalPregunta'));
        modalPreguntaInstance.show();
    }

    // Función para agregar una nueva pregunta de verdadero/falso
    function agregarPreguntaVF() {
        // Limpiar formulario
        document.getElementById('formPregunta').reset();
        document.getElementById('accion_pregunta').value = 'crear';
        document.getElementById('pregunta_id').value = '';
        document.getElementById('examen_id_pregunta').value = examenActualId;
        document.getElementById('tipo_pregunta').value = 'verdadero_falso';
        document.getElementById('modalPreguntaLabel').textContent = 'Agregar Pregunta de Verdadero/Falso';

        // Actualizar el contenedor de opciones para verdadero/falso
        toggleTipoPregunta();

        // Ocultar modal de preguntas y mostrar modal de pregunta
        modalPreguntasInstance.hide();
        modalPreguntaInstance = new bootstrap.Modal(document.getElementById('modalPregunta'));
        modalPreguntaInstance.show();
    }

    // Función para editar una pregunta existente
    function editarPregunta(preguntaId) {
        preguntaActualId = preguntaId;

        // Obtener datos de la pregunta mediante AJAX
        fetch(`controladores/obtener_datos.php?tipo=pregunta&id=${preguntaId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const pregunta = data.data;
                    const opciones = data.opciones;

                    // Llenar formulario con datos de la pregunta
                    document.getElementById('accion_pregunta').value = 'editar';
                    document.getElementById('pregunta_id').value = pregunta.id;
                    document.getElementById('examen_id_pregunta').value = pregunta.examen_id;
                    document.getElementById('texto_pregunta').value = pregunta.texto_pregunta;
                    document.getElementById('tipo_pregunta').value = pregunta.tipo;
                    document.getElementById('puntaje').value = pregunta.puntaje;

                    // Actualizar el contenedor de opciones según el tipo de pregunta
                    if (pregunta.tipo === 'verdadero_falso') {
                        // Para preguntas de verdadero/falso, mostrar selector de opción correcta
                        const opcionCorrecta = opciones.find(opcion => opcion.es_correcta == 1);
                        const valorCorrecto = opcionCorrecta ? (opcionCorrecta.texto_opcion === 'Falso' ? '1' : '0') : '0';

                        document.getElementById('opciones-container').innerHTML = `
                        <div class="mb-3">
                            <label class="form-label">Opción Correcta</label>
                            <select class="form-select" name="opcion_vf_correcta">
                                <option value="0" ${valorCorrecto === '0' ? 'selected' : ''}>Verdadero</option>
                                <option value="1" ${valorCorrecto === '1' ? 'selected' : ''}>Falso</option>
                            </select>
                        </div>
                        <div class="alert alert-info">
                            Las opciones "Verdadero" y "Falso" se crearán automáticamente al guardar la pregunta.
                        </div>
                    `;
                    } else {
                        // Para preguntas de opción múltiple, mostrar las opciones existentes
                        document.getElementById('opciones-container').innerHTML = `
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6>Opciones de Respuesta</h6>
                            <div id="btn-agregar-opcion-container" style="display: block;">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="mostrarModalOpcion()">
                                    <i class="bi bi-plus-circle me-1"></i>Agregar Opción
                                </button>
                            </div>
                        </div>
                        <div id="opciones-lista"></div>
                    `;

                        // Mostrar opciones existentes
                        mostrarOpcionesPregunta(opciones);
                    }

                    // Mostrar botón de agregar opción para preguntas de opción múltiple
                    if (pregunta.tipo === 'opcion_multiple') {
                        document.getElementById('btn-agregar-opcion-container').style.display = 'block';
                    }

                    // Actualizar título del modal según el tipo de pregunta
                    if (pregunta.tipo === 'opcion_multiple') {
                        document.getElementById('modalPreguntaLabel').textContent = 'Editar Pregunta de Opción Múltiple';
                    } else {
                        document.getElementById('modalPreguntaLabel').textContent = 'Editar Pregunta de Verdadero/Falso';
                    }

                    // Ocultar modal de preguntas y mostrar modal de pregunta
                    modalPreguntasInstance.hide();
                    modalPreguntaInstance = new bootstrap.Modal(document.getElementById('modalPregunta'));
                    modalPreguntaInstance.show();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo cargar la información de la pregunta'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al cargar los datos'
                });
            });
    }

    // Añade esta función después de la función editarPregunta
    function agregarOpcion(preguntaId) {
        preguntaActualId = preguntaId;

        // Limpiar formulario
        document.getElementById('formOpcion').reset();
        document.getElementById('accion_opcion').value = 'crear';
        document.getElementById('opcion_id').value = '';
        document.getElementById('pregunta_id_opcion').value = preguntaId;
        document.getElementById('modalOpcionLabel').textContent = 'Agregar Nueva Opción';

        // Mostrar modal de opción
        modalOpcionInstance = new bootstrap.Modal(document.getElementById('modalOpcion'));
        modalOpcionInstance.show();
    }

    // Agregar la función mostrarModalOpcion() después de la función editarOpcion()

    function mostrarModalOpcion() {
        // Obtener el ID de la pregunta actual
        const preguntaId = document.getElementById('pregunta_id').value;

        if (!preguntaId) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Primero debe guardar la pregunta para poder agregar opciones'
            });
            return;
        }

        // Limpiar formulario
        document.getElementById('formOpcion').reset();
        document.getElementById('accion_opcion').value = 'crear';
        document.getElementById('opcion_id').value = '';
        document.getElementById('pregunta_id_opcion').value = preguntaId;
        document.getElementById('modalOpcionLabel').textContent = 'Agregar Nueva Opción';

        // Ocultar modal de pregunta y mostrar modal de opción
        modalPreguntaInstance.hide();
        modalOpcionInstance = new bootstrap.Modal(document.getElementById('modalOpcion'));
        modalOpcionInstance.show();
    }

    // Función para eliminar una pregunta
    function eliminarPregunta(preguntaId) {
        Swal.fire({
            title: '¿Eliminar pregunta?',
            text: "Se eliminarán también todas sus opciones de respuesta",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: '¿Estás completamente seguro?',
                    text: "Esta acción no se puede deshacer",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonText: 'Cancelar',
                    confirmButtonText: 'Sí, eliminar definitivamente'
                }).then((secondResult) => {
                    if (secondResult.isConfirmed) {
                        // Enviar solicitud para eliminar pregunta
                        fetch('controladores/gestionar_pregunta.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: `accion=eliminar&pregunta_id=${preguntaId}&modulo_id=<?php echo $modulo_id; ?>`
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Eliminar elemento del DOM
                                    document.getElementById(`pregunta-${preguntaId}`).remove();

                                    // Actualizar estadísticas
                                    actualizarEstadisticasPreguntas();

                                    // Mostrar mensaje de éxito
                                    Swal.fire({
                                        icon: 'success',
                                        title: '¡Eliminado!',
                                        text: 'La pregunta ha sido eliminada correctamente',
                                        showConfirmButton: false,
                                        timer: 1500
                                    });

                                    // Verificar si no hay más preguntas
                                    const container = document.getElementById('preguntas-container');
                                    if (container.children.length === 0) {
                                        container.innerHTML = `
                                    <div class="alert alert-info">
                                        No hay preguntas creadas para este examen. Utilice el botón "Agregar Pregunta" para crear una.
                                    </div>
                                `;
                                    }
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: data.message || 'No se pudo eliminar la pregunta'
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Ocurrió un error al eliminar la pregunta'
                                });
                            });
                    }
                });
            }
        });
    }

    // Función para eliminar una opción
    function eliminarOpcion(opcionId) {
        Swal.fire({
            title: '¿Eliminar opción?',
            text: "Esta opción será eliminada de la pregunta",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: '¿Estás completamente seguro?',
                    text: "Esta acción no se puede deshacer",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonText: 'Cancelar',
                    confirmButtonText: 'Sí, eliminar definitivamente'
                }).then((secondResult) => {
                    if (secondResult.isConfirmed) {
                        // Enviar solicitud para eliminar opción
                        fetch('controladores/gestionar_opcion.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: `accion=eliminar&opcion_id=${opcionId}&modulo_id=<?php echo $modulo_id; ?>`
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Eliminar elemento del DOM
                                    const opcionElement = document.getElementById(`opcion-${opcionId}`);
                                    if (opcionElement) {
                                        opcionElement.remove();
                                    }

                                    const opcionFormElement = document.getElementById(`opcion-form-${opcionId}`);
                                    if (opcionFormElement) {
                                        opcionFormElement.remove();
                                    }

                                    // Mostrar mensaje de éxito
                                    Swal.fire({
                                        icon: 'success',
                                        title: '¡Eliminado!',
                                        text: 'La opción ha sido eliminada correctamente',
                                        showConfirmButton: false,
                                        timer: 1500
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: data.message || 'No se pudo eliminar la opción'
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Ocurrió un error al eliminar la opción'
                                });
                            });
                    }
                });
            }
        });
    }

    // Función para volver al modal de preguntas desde el modal de pregunta
    function volverAModalPreguntas() {
        modalPreguntaInstance.hide();
        modalPreguntasInstance.show();
    }

    // Función para volver al modal de pregunta desde el modal de opción
    function volverAModalPregunta() {
        modalOpcionInstance.hide();
        modalPreguntaInstance.show();
    }

    // Inicializar todos los collapse
    document.addEventListener('DOMContentLoaded', function() {
        var collapseElements = document.querySelectorAll('.collapse');
        collapseElements.forEach(function(el) {
            new bootstrap.Collapse(el, {
                toggle: false
            });
        });

        // Configurar formularios para envío AJAX
        configurarFormularioAjax('formPregunta', function(data) {
            if (data.success) {
                // Verificar si es una pregunta nueva de opción múltiple
                const esCreacion = document.getElementById('accion_pregunta').value === 'crear';
                const esOpcionMultiple = document.getElementById('tipo_pregunta').value === 'opcion_multiple';

                if (esCreacion && esOpcionMultiple) {
                    // Si es una nueva pregunta de opción múltiple, editar directamente
                    Swal.fire({
                        icon: 'success',
                        title: '¡Pregunta creada!',
                        text: 'Ahora puedes agregar opciones a tu pregunta',
                        showConfirmButton: false,
                        timer: 1500,
                        willClose: () => {
                            // Editar la pregunta recién creada
                            editarPregunta(data.pregunta_id);
                        }
                    });
                } else {
                    // Para otros casos (edición o verdadero/falso)

                    // Cerrar y destruir el modal actual
                    if (modalPreguntaInstance) {
                        modalPreguntaInstance.hide();
                        document.getElementById('modalPregunta').addEventListener('hidden.bs.modal', function() {
                            // Mostrar mensaje de éxito
                            Swal.fire({
                                icon: 'success',
                                title: '¡Éxito!',
                                text: data.message,
                                showConfirmButton: false,
                                timer: 1500,
                                willClose: () => {
                                    // Guardar el ID del examen actual
                                    const examenId = examenActualId;

                                    // Recargar la lista de preguntas
                                    // Usamos setTimeout para asegurar que todo se ejecute en orden
                                    setTimeout(() => {
                                        // Recrear la instancia del modal de preguntas
                                        modalPreguntasInstance = new bootstrap.Modal(document.getElementById('modalPreguntas'));
                                        mostrarPreguntas(examenId);
                                    }, 100);
                                }
                            });
                        }, {
                            once: true
                        }); // Importante: el evento se ejecuta solo una vez
                    } else {
                        // Si por alguna razón no hay instancia, simplemente mostrar el mensaje y recargar
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500,
                            willClose: () => {
                                mostrarPreguntas(examenActualId);
                            }
                        });
                    }
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Error al guardar la pregunta'
                });
            }
        });

        configurarFormularioAjax('formOpcion', function(data) {
            if (data.success) {
                modalOpcionInstance.hide();

                // Si estamos en el modal de pregunta, actualizar las opciones
                if (modalPreguntaInstance) {
                    fetch(`controladores/obtener_datos.php?tipo=pregunta&id=${preguntaActualId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                mostrarOpcionesPregunta(data.opciones);
                                modalPreguntaInstance.show();
                            }
                        });
                } else {
                    // Si estamos en el modal de preguntas, actualizar la lista de preguntas
                    mostrarPreguntas(examenActualId);
                }

                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: data.message,
                    showConfirmButton: false,
                    timer: 1500
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Error al guardar la opción'
                });
            }
        });

        configurarFormularioAjax('formAgregarExamen', function(data) {
            if (data.success) {
                // Cerrar modal
                const modalAgregarExamen = bootstrap.Modal.getInstance(document.getElementById('modalAgregarExamen'));
                modalAgregarExamen.hide();

                // Mostrar mensaje de éxito antes de recargar la página
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: 'El examen ha sido creado correctamente',
                    showConfirmButton: false,
                    timer: 1500,
                    willClose: () => {
                        // Recargar la página para mostrar el nuevo examen
                        window.location.reload();
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Error al guardar el examen'
                });
            }
        });
    });

    function configurarFormularioAjax(formId, callback) {
        const form = document.getElementById(formId);

        if (!form) {
            console.error(`Formulario con ID ${formId} no encontrado`);
            return;
        }

        // Verificar si el formulario ya tiene un controlador de eventos configurado
        if (form.hasAttribute('data-ajax-configured')) {
            console.log(`Formulario ${formId} ya configurado, evitando duplicación`);
            return;
        }

        // Marcar el formulario como configurado
        form.setAttribute('data-ajax-configured', 'true');

        // Variable para controlar si el formulario ya se está enviando
        let isSubmitting = false;

        console.log(`Configurando formulario AJAX para ${formId}`);

        form.addEventListener('submit', function(e) {
            // Detener el envío normal del formulario
            e.preventDefault();

            console.log(`Formulario ${formId} enviado, isSubmitting=${isSubmitting}`);

            // Si ya se está enviando, no hacer nada
            if (isSubmitting) {
                console.log(`Formulario ${formId} ya está siendo enviado, ignorando`);
                return;
            }

            // Marcar como enviando
            isSubmitting = true;
            console.log(`Formulario ${formId} marcado como enviando`);

            // Crear FormData del formulario
            const formData = new FormData(form);

            // Mostrar indicador de carga
            Swal.fire({
                title: 'Procesando...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Enviar mediante fetch API
            console.log(`Enviando formulario ${formId} a ${form.action}`);
            fetch(form.action, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    console.log(`Respuesta recibida para ${formId}`, response);
                    return response.json();
                })
                .then(data => {
                    // Restablecer el estado de envío
                    isSubmitting = false;
                    console.log(`Formulario ${formId} completado, respuesta:`, data);

                    // Cerrar indicador de carga
                    Swal.close();

                    // Procesar la respuesta
                    callback(data);
                })
                .catch(error => {
                    // Restablecer el estado de envío en caso de error
                    isSubmitting = false;

                    console.error(`Error en formulario ${formId}:`, error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al procesar la solicitud'
                    });
                });
        });
    }

    // Función para recargar la página borrando el caché
    function recargarPagina() {
        // Cerrar el modal
        modalPreguntasInstance.hide();

        // Mostrar mensaje de carga
        Swal.fire({
            title: 'Actualizando...',
            text: 'Recargando la página para mostrar los cambios',
            showConfirmButton: false,
            allowOutsideClick: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        // Esperar un momento y luego recargar la página con parámetro para evitar caché
        setTimeout(() => {
            const timestamp = new Date().getTime();
            window.location.href = window.location.pathname + '?modulo_id=<?php echo $modulo_id; ?>&t=' + timestamp;
        }, 800);
    }

    function toggleTipoPregunta() {
        const tipoPregunta = document.getElementById('tipo_pregunta').value;
        const opcionesContainer = document.getElementById('opciones-container');

        if (tipoPregunta === 'verdadero_falso') {
            // Para preguntas de verdadero/falso, mostrar selector de opción correcta
            opcionesContainer.innerHTML = `
            <div class="mb-3">
                <label class="form-label">Opción Correcta</label>
                <select class="form-select" name="opcion_vf_correcta">
                    <option value="0">Verdadero</option>
                    <option value="1">Falso</option>
                </select>
            </div>
            <div class="alert alert-info">
                Las opciones "Verdadero" y "Falso" se crearán automáticamente al guardar la pregunta.
            </div>
        `;
        } else {
            // Para preguntas de opción múltiple, mostrar mensaje de que se pueden agregar opciones después de guardar
            opcionesContainer.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6>Opciones de Respuesta</h6>
                <div id="btn-agregar-opcion-container" style="display: none;">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="mostrarModalOpcion()">
                        <i class="bi bi-plus-circle me-1"></i>Agregar Opción
                    </button>
                </div>
            </div>
            <div id="opciones-lista">
                <div class="alert alert-info">
                    Primero guarde la pregunta para poder agregar opciones.
                </div>
            </div>
        `;
        }
    }

    // Buscar la función mostrarOpcionesPregunta y reemplazarla con esta versión mejorada

    function mostrarOpcionesPregunta(opciones) {
        const container = document.getElementById('opciones-lista');

        if (!container) {
            console.error('El contenedor de opciones no existe');
            return;
        }

        if (opciones.length === 0) {
            container.innerHTML = `
            <div class="alert alert-info">
                No hay opciones para esta pregunta. Utilice el botón "Agregar Opción" para crear una.
            </div>
        `;
            return;
        }

        let html = '';

        opciones.forEach(opcion => {
            html += `
            <div class="opcion-item mb-2" id="opcion-form-${opcion.id}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge ${opcion.es_correcta == 1 ? 'bg-success' : 'bg-danger'} me-2">
                            ${opcion.es_correcta == 1 ? 'Correcta' : 'Incorrecta'}
                        </span>
                        ${opcion.texto_opcion}
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline-primary btn-sm me-1" onclick="editarOpcion(${opcion.id})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="eliminarOpcion(${opcion.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        });

        container.innerHTML = html;
    }

    // También vamos a mejorar la función editarOpcion para asegurarnos de que funcione correctamente

    function editarOpcion(opcionId) {
        // Obtener datos de la opción mediante AJAX
        fetch(`controladores/obtener_datos.php?tipo=opcion&id=${opcionId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const opcion = data.data;

                    // Llenar formulario con datos de la opción
                    document.getElementById('accion_opcion').value = 'editar';
                    document.getElementById('opcion_id').value = opcion.id;
                    document.getElementById('pregunta_id_opcion').value = opcion.pregunta_id;
                    document.getElementById('texto_opcion').value = opcion.texto_opcion;
                    document.getElementById('es_correcta').checked = opcion.es_correcta == 1;

                    // Actualizar título del modal
                    document.getElementById('modalOpcionLabel').textContent = 'Editar Opción';

                    // Guardar pregunta actual ID
                    preguntaActualId = opcion.pregunta_id;

                    // Ocultar modal de pregunta (si está abierto) y mostrar modal de opción
                    if (modalPreguntaInstance) {
                        modalPreguntaInstance.hide();
                    } else if (modalPreguntasInstance) {
                        modalPreguntasInstance.hide();
                    }

                    modalOpcionInstance = new bootstrap.Modal(document.getElementById('modalOpcion'));
                    modalOpcionInstance.show();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'No se pudo cargar la información de la opción'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al cargar los datos de la opción'
                });
            });
    }

    function actualizarOpcionesVerdaderoFalso() {
        // Verificar si hay una pregunta seleccionada y es de tipo verdadero/falso
        const preguntaId = document.getElementById('pregunta_id').value;
        const tipoPregunta = document.getElementById('tipo_pregunta').value;

        if (!preguntaId || tipoPregunta !== 'verdadero_falso') {
            return;
        }

        // Obtener el valor seleccionado (0 = Verdadero, 1 = Falso)
        const opcionCorrecta = document.querySelector('select[name="opcion_vf_correcta"]').value;

        // Obtener las opciones existentes
        fetch(`controladores/obtener_datos.php?tipo=pregunta&id=${preguntaId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.opciones && data.opciones.length === 2) {
                    const opciones = data.opciones;

                    // Identificar las opciones de Verdadero y Falso
                    const opcionVerdadero = opciones.find(o => o.texto_opcion === 'Verdadero');
                    const opcionFalso = opciones.find(o => o.texto_opcion === 'Falso');

                    if (opcionVerdadero && opcionFalso) {
                        // Determinar qué opción debe ser correcta
                        const opcionAActualizar = opcionCorrecta === '0' ? opcionVerdadero : opcionFalso;

                        // Actualizar la opción correcta
                        const formData = new FormData();
                        formData.append('accion', 'editar');
                        formData.append('opcion_id', opcionAActualizar.id);
                        formData.append('pregunta_id', preguntaId);
                        formData.append('texto_opcion', opcionAActualizar.texto_opcion);
                        formData.append('es_correcta', '1');
                        formData.append('modulo_id', document.querySelector('input[name="modulo_id"]').value);

                        fetch('controladores/gestionar_opcion.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(result => {
                                if (!result.success) {
                                    console.error('Error al actualizar la opción correcta:', result.message);
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                            });
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    configurarFormularioAjax('formPregunta', function(data) {
        if (data.success) {
            // Verificar si es una pregunta de verdadero/falso y actualizar las opciones
            const tipoPregunta = document.getElementById('tipo_pregunta').value;
            if (tipoPregunta === 'verdadero_falso') {
                actualizarOpcionesVerdaderoFalso();
            }

            // Verificar si es una pregunta nueva de opción múltiple
            const esCreacion = document.getElementById('accion_pregunta').value === 'crear';
            const esOpcionMultiple = tipoPregunta === 'opcion_multiple';

            if (esCreacion && esOpcionMultiple) {
                // Si es una nueva pregunta de opción múltiple, editar directamente
                Swal.fire({
                    icon: 'success',
                    title: '¡Pregunta creada!',
                    text: 'Ahora puedes agregar opciones a tu pregunta',
                    showConfirmButton: false,
                    timer: 1500,
                    willClose: () => {
                        // Editar la pregunta recién creada
                        editarPregunta(data.pregunta_id);
                    }
                });
            } else {
                // Para otros casos (edición o verdadero/falso)

                // Cerrar y destruir el modal actual
                if (modalPreguntaInstance) {
                    modalPreguntaInstance.hide();
                    document.getElementById('modalPregunta').addEventListener('hidden.bs.modal', function() {
                        // Mostrar mensaje de éxito
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500,
                            willClose: () => {
                                // Guardar el ID del examen actual
                                const examenId = examenActualId;

                                // Recargar la lista de preguntas
                                // Usamos setTimeout para asegurar que todo se ejecute en orden
                                setTimeout(() => {
                                    // Recrear la instancia del modal de preguntas
                                    modalPreguntasInstance = new bootstrap.Modal(document.getElementById('modalPreguntas'));
                                    mostrarPreguntas(examenId);
                                }, 100);
                            }
                        });
                    }, {
                        once: true
                    }); // Importante: el evento se ejecuta solo una vez
                } else {
                    // Si por alguna razón no hay instancia, simplemente mostrar el mensaje y recargar
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: data.message,
                        showConfirmButton: false,
                        timer: 1500,
                        willClose: () => {
                            mostrarPreguntas(examenActualId);
                        }
                    });
                }
            }
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Error al guardar la pregunta'
            });
        }
    });

    function actualizarEstadisticasPreguntas() {
        // Obtener todas las preguntas visibles
        const preguntasItems = document.querySelectorAll('.pregunta-item');
        const totalPreguntas = preguntasItems.length;

        // Calcular puntaje total
        let puntajeTotal = 0;
        preguntasItems.forEach(item => {
            const puntajeText = item.querySelector('div.mb-3:nth-child(2), div.col-4.mb-3').textContent;
            const puntaje = parseInt(puntajeText.replace('Puntaje:', '').trim());
            if (!isNaN(puntaje)) {
                puntajeTotal += puntaje;
            }
        });

        // Actualizar estadísticas en el título
        const puntajeFaltante = 100 - puntajeTotal;
        const estadoPuntaje = puntajeFaltante === 0 ?
            '<span class="badge bg-success">Completo (100%)</span>' :
            (puntajeFaltante > 0 ?
                `<span class="badge bg-warning">Falta un ${puntajeFaltante}%</span>` :
                `<span class="badge bg-danger">Excede por ${Math.abs(puntajeFaltante)}%</span>`);

        document.getElementById('examen-titulo-preguntas').innerHTML = `
        <div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="badge bg-primary">Preguntas: ${totalPreguntas}</span>
                <span class="badge bg-info">Puntaje total: ${puntajeTotal}%</span>
                ${estadoPuntaje}
            </div>
        </div>
    `;
    }
</script>