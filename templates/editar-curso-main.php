<?php
// Obtener datos del curso
$curso_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $db->prepare("SELECT * FROM cursos WHERE id = ?");
$stmt->bind_param("i", $curso_id);
$stmt->execute();
$curso = $stmt->get_result()->fetch_assoc();

if (!$curso) {
    header('Location: admin-cursos.php');
    exit;
}

// Obtener módulos del curso
$stmt = $db->prepare("SELECT * FROM modulos WHERE curso_id = ? ORDER BY id ASC");
$stmt->bind_param("i", $curso_id);
$stmt->execute();
$modulos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener recursos de cada módulo
$recursos_por_modulo = [];
foreach ($modulos as $modulo) {
    $stmt = $db->prepare("SELECT * FROM recursos_modulo WHERE modulo_id = ?");
    $stmt->bind_param("i", $modulo['id']);
    $stmt->execute();
    $recursos_por_modulo[$modulo['id']] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
<style>
    .curso-form {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .modulos-container {
        background-color: #ffffff;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        padding: 1rem;
    }

    .modulo-item {
        background-color: #e9ecef;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .recurso-item {
        background-color: #ffffff;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        padding: 1rem;
        margin-top: 1rem;
    }

    .collapse-toggle {
        cursor: pointer;
    }

    .collapse-toggle:hover {
        text-decoration: underline;
    }

    .btn-outline-secondary {
        color: #6c757d;
        border-color: #6c757d;
    }

    .btn-outline-secondary:hover {
        color: #fff;
        background-color: #6c757d;
        border-color: #6c757d;
    }
</style>
<!-- TITULO -->
<div class="page-title" data-aos="fade">
    <div class="heading p-5">
        <div class="container">
            <div class="row d-flex justify-content-center text-center">
                <div class="col-lg-8">
                    <h1>Editar Curso<i class="bi bi-pencil-square m-2"></i></h1>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- BOTONES -->
<div class="container-fluid col-lg-10 pt-3">
    <div class="d-flex justify-content-start">
        <a href="admin-cursos.php" class="btn btn-secondary shadow">
            <i class="bi bi-arrow-90deg-left me-2"></i>Volver a panel cursos
        </a>
    </div>
</div>

<div class="container py-3">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg">
                <div class="card-header">
                    <h3 class="card-title mb-0 text-center"><b>Editar Curso</b></h3>
                </div>
                <div class="card-body p-4">
                    <form action="controladores/modificar_curso.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="curso_id" value="<?php echo $curso_id; ?>">

                        <!-- Datos básicos del curso -->
                        <div class="mb-4">
                            <label for="titulo" class="form-label">Título del Curso</label>
                            <input type="text"
                                class="form-control"
                                id="titulo"
                                name="titulo"
                                value="<?php echo htmlspecialchars($curso['titulo']); ?>"
                                required>
                        </div>

                        <div class="mb-4">
                            <label for="descripcion" class="form-label">Descripción del Curso</label>
                            <textarea
                                class="form-control"
                                id="descripcion"
                                name="descripcion"
                                rows="6"
                                required><?php echo htmlspecialchars($curso['descripcion']); ?></textarea>
                        </div>

                        <!-- Imagen actual y campo para nueva imagen -->
                        <div class="mb-4">
                            <label class="form-label">Imagen Actual</label>
                            <div class="mb-2">
                                <img src="<?php echo htmlspecialchars($curso['imagen_path']); ?>"
                                    alt="Imagen del curso"
                                    class="img-fluid rounded"
                                    style="max-height: 200px;">
                            </div>
                            <label for="imagen" class="form-label">Nueva Imagen (opcional)</label>
                            <input type="file"
                                class="form-control"
                                id="imagen"
                                name="imagen"
                                accept="image/*">
                            <div class="form-text">Seleccione una nueva imagen solo si desea cambiarla</div>
                        </div>

                        <!-- Programa actual y campo para nuevo programa -->
                        <div class="mb-4">
                            <label class="form-label">Programa Actual</label>
                            <?php if ($curso['programa_pdf_path']): ?>
                                <div class="mb-2">
                                    <a href="<?php echo htmlspecialchars($curso['programa_pdf_path']); ?>"
                                        target="_blank"
                                        class="btn btn-sm btn-primary">
                                        <i class="bi bi-file-pdf me-2"></i>Ver programa actual
                                    </a>
                                </div>
                            <?php endif; ?>
                            <label for="programa" class="form-label">Nuevo Programa PDF (opcional)</label>
                            <input type="file"
                                class="form-control"
                                id="programa"
                                name="programa"
                                accept=".pdf">
                            <div class="form-text">Seleccione un nuevo programa solo si desea cambiarlo</div>
                        </div>

                        <div class="mb-4">
                            <label for="form_insc" class="form-label">URL del Formulario de Inscripción</label>
                            <input type="text"
                                class="form-control"
                                id="form_insc"
                                name="form_insc"
                                value="<?php echo htmlspecialchars($curso['form_insc']); ?>"
                                required>
                        </div>

                        <div class="mb-4">
                            <label for="cantidad_horas" class="form-label">Cantidad de Horas</label>
                            <input type="number"
                                class="form-control"
                                id="cantidad_horas"
                                name="cantidad_horas"
                                min="1"
                                value="<?php echo htmlspecialchars($curso['cantidad_horas']); ?>"
                                required>
                        </div>

                        <div class="mb-4">
                            <label for="anio" class="form-label">Año</label>
                            <input type="number"
                                class="form-control"
                                id="anio"
                                name="anio"
                                min="2000"
                                max="2100"
                                value="<?php echo htmlspecialchars($curso['anio']); ?>"
                                required>
                        </div>

                        <!-- Sección de Módulos -->
                        <div class="modulos-container">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3><b><u>Módulos del Curso</u></b></h3>
                                <button type="button" class="btn btn-success btn-sm" onclick="agregarModulo()">
                                    <i class="bi bi-plus-circle me-2"></i>Agregar Módulo
                                </button>
                            </div>
                            <div id="modulos-container">
                                <?php foreach ($modulos as $modulo): ?>
                                    <div class="modulo-item mb-3">
                                        <input type="hidden" name="modulo_ids[]" value="<?php echo $modulo['id']; ?>">

                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h5 class="collapse-toggle" data-bs-toggle="collapse" data-bs-target="#modulo<?php echo $modulo['id']; ?>">
                                                <i class="bi bi-chevron-down me-2"></i>
                                                Módulo: <?php echo htmlspecialchars($modulo['titulo']); ?>
                                            </h5>
                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="eliminarModulo(this)">
                                                <i class="bi bi-trash"></i> Eliminar Módulo
                                            </button>
                                        </div>

                                        <div class="collapse show" id="modulo<?php echo $modulo['id']; ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Título del Módulo</label>
                                                <input type="text"
                                                    class="form-control"
                                                    name="modulo_titulos[]"
                                                    value="<?php echo htmlspecialchars($modulo['titulo']); ?>"
                                                    required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Descripción del Módulo</label>
                                                <textarea class="form-control"
                                                    name="modulo_descripciones[]"
                                                    rows="3"
                                                    required><?php echo htmlspecialchars($modulo['descripcion']); ?></textarea>
                                            </div>

                                            <!-- Recursos del módulo -->
                                            <div class="recursos-container mt-4">
                                                <h6 class="mb-3"><u>Recursos del Módulo</u></h6>
                                                <?php if (isset($recursos_por_modulo[$modulo['id']])): ?>
                                                    <?php foreach ($recursos_por_modulo[$modulo['id']] as $recurso): ?>
                                                        <div class="recurso-item mb-3">
                                                            <input type="hidden" name="recurso_ids[<?php echo $modulo['id']; ?>][]" value="<?php echo $recurso['id']; ?>">

                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <h6 class="collapse-toggle" data-bs-toggle="collapse" data-bs-target="#recurso<?php echo $recurso['id']; ?>">
                                                                    <i class="bi bi-chevron-down me-2"></i>
                                                                    Recurso: <?php echo htmlspecialchars($recurso['descripcion']); ?>
                                                                </h6>
                                                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="eliminarRecurso(this)">
                                                                    <i class="bi bi-trash"></i> Eliminar Recurso
                                                                </button>
                                                            </div>

                                                            <div class="collapse" id="recurso<?php echo $recurso['id']; ?>">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Descripción del Recurso</label>
                                                                    <input type="text" class="form-control" name="recurso_descripciones[<?php echo $modulo['id']; ?>][]" value="<?php echo htmlspecialchars($recurso['descripcion']); ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Tipo de Recurso</label>
                                                                    <select class="form-select" name="recurso_tipos[<?php echo $modulo['id']; ?>][]" onchange="toggleRecursoFields(this)" required>
                                                                        <option value="url" <?php echo $recurso['tipo'] == 'url' ? 'selected' : ''; ?>>URL</option>
                                                                        <option value="archivo" <?php echo $recurso['tipo'] == 'archivo' ? 'selected' : ''; ?>>Archivo</option>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3 recurso-url" style="display: <?php echo $recurso['tipo'] == 'url' ? 'block' : 'none'; ?>;">
                                                                    <label class="form-label">URL del Recurso</label>
                                                                    <input type="url" class="form-control" name="recurso_urls[<?php echo $modulo['id']; ?>][]" value="<?php echo htmlspecialchars($recurso['url']); ?>">
                                                                </div>
                                                                <div class="mb-3 recurso-archivo" style="display: <?php echo $recurso['tipo'] == 'archivo' ? 'block' : 'none'; ?>;">
                                                                    <label class="form-label">Archivo del Recurso</label>
                                                                    <?php if ($recurso['archivo_path']): ?>
                                                                        <p>Archivo actual: <?php echo basename($recurso['archivo_path']); ?></p>
                                                                    <?php endif; ?>
                                                                    <input type="file" class="form-control" name="recurso_archivos[<?php echo $modulo['id']; ?>][]">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>

                                            <button type="button" class="btn btn-outline-primary btn-sm mt-3" onclick="agregarRecurso(this)">
                                                <i class="bi bi-plus-circle me-2"></i>Agregar Recurso
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-save me-2"></i>Guardar Cambios
                            </button>
                            <a href="admin-cursos.php" class="btn btn-danger">
                                <i class="bi bi-x-circle me-2"></i>Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function agregarModulo() {
        const container = document.getElementById('modulos-container');
        const moduloId = 'nuevo_' + Date.now();
        const moduloItem = document.createElement('div');
        moduloItem.className = 'modulo-item mb-3';

        moduloItem.innerHTML = `
        <input type="hidden" name="modulo_ids[]" value="nuevo">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="collapse-toggle" data-bs-toggle="collapse" data-bs-target="#modulo${moduloId}">
                <i class="bi bi-chevron-down me-2"></i>
                Nuevo Módulo
            </h5>
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="eliminarModulo(this)">
                <i class="bi bi-trash"></i> Eliminar Módulo
            </button>
        </div>
        
        <div class="collapse show" id="modulo${moduloId}">
            <div class="mb-3">
                <label class="form-label">Título del Módulo</label>
                <input type="text" class="form-control" name="modulo_titulos[]" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Descripción del Módulo</label>
                <textarea class="form-control" name="modulo_descripciones[]" rows="3" required></textarea>
            </div>

            <div class="recursos-container mt-4">
                <h6 class="mb-3">Recursos del Módulo</h6>
            </div>
            
            <button type="button" class="btn btn-outline-primary btn-sm mt-3" onclick="agregarRecurso(this)">
                <i class="bi bi-plus-circle me-2"></i>Agregar Recurso
            </button>
        </div>
    `;

        container.appendChild(moduloItem);
    }

    function agregarRecurso(btn) {
        const moduloItem = btn.closest('.modulo-item');
        const recursosContainer = moduloItem.querySelector('.recursos-container');
        const moduloId = moduloItem.querySelector('input[name="modulo_ids[]"]').value;
        const recursoId = 'nuevo_' + Date.now();

        const recursoItem = document.createElement('div');
        recursoItem.className = 'recurso-item mb-3';

        recursoItem.innerHTML = `
        <input type="hidden" name="recurso_ids[${moduloId}][]" value="nuevo">
        
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="collapse-toggle" data-bs-toggle="collapse" data-bs-target="#recurso${recursoId}">
                <i class="bi bi-chevron-down me-2"></i>
                Nuevo Recurso
            </h6>
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="eliminarRecurso(this)">
                <i class="bi bi-trash"></i> Eliminar Recurso
            </button>
        </div>

        <div class="collapse show" id="recurso${recursoId}">
            <div class="mb-3">
                <label class="form-label">Descripción del Recurso</label>
                <input type="text" class="form-control" name="recurso_descripciones[${moduloId}][]" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Tipo de Recurso</label>
                <select class="form-select" name="recurso_tipos[${moduloId}][]" onchange="toggleRecursoFields(this)" required>
                    <option value="url">URL</option>
                    <option value="archivo">Archivo</option>
                </select>
            </div>
            <div class="mb-3 recurso-url">
                <label class="form-label">URL del Recurso</label>
                <input type="url" class="form-control" name="recurso_urls[${moduloId}][]">
            </div>
            <div class="mb-3 recurso-archivo" style="display: none;">
                <label class="form-label">Archivo del Recurso</label>
                <input type="file" class="form-control" name="recurso_archivos[${moduloId}][]">
            </div>
        </div>
    `;

        recursosContainer.appendChild(recursoItem);
    }

    function toggleRecursoFields(select) {
        const recursoItem = select.closest('.recurso-item');
        const urlField = recursoItem.querySelector('.recurso-url');
        const archivoField = recursoItem.querySelector('.recurso-archivo');

        if (select.value === 'url') {
            urlField.style.display = 'block';
            archivoField.style.display = 'none';
        } else {
            urlField.style.display = 'none';
            archivoField.style.display = 'block';
        }
    }


    function eliminarModulo(btn) {
        if (confirm('¿Estás seguro de que quieres eliminar este módulo y todos sus recursos?')) {
            btn.closest('.modulo-item').remove();
        }
    }

    function eliminarRecurso(btn) {
        if (confirm('¿Estás seguro de que quieres eliminar este recurso?')) {
            btn.closest('.recurso-item').remove();
        }
    }


    // Inicializar todos los collapse y campos de recursos
    document.addEventListener('DOMContentLoaded', function() {
        var collapseElements = document.querySelectorAll('.collapse');
        collapseElements.forEach(function(el) {
            new bootstrap.Collapse(el, {
                toggle: false
            });
        });

        // Inicializar los campos de recursos existentes
        document.querySelectorAll('select[name^="recurso_tipos"]').forEach(function(select) {
            toggleRecursoFields(select);
        });
    });
</script>