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

// Obtener recursos del módulo
$stmt = $db->prepare("SELECT * FROM recursos_modulo WHERE modulo_id = ?");
$stmt->bind_param("i", $modulo_id);
$stmt->execute();
$recursos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<style>
    .recurso-item {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    .collapse-toggle {
        cursor: pointer;
    }
    .collapse-toggle:hover {
        text-decoration: underline;
    }
</style>

<!-- TITULO -->
<div class="page-title" data-aos="fade">
    <div class="heading p-5">
        <div class="container">
            <div class="row d-flex justify-content-center text-center">
                <div class="col-lg-8">
                    <h1>Editar Recursos del Módulo<i class="bi bi-pencil-square m-2"></i></h1>
                    <p class="mb-0">Módulo: <?php echo htmlspecialchars($modulo['titulo']); ?></p>
                    <p class="mb-0">Curso: <?php echo htmlspecialchars($modulo['curso_titulo']); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- BOTONES -->
<div class="container-fluid col-lg-10 pt-3">
    <div class="d-flex justify-content-start">
        <a href="editar-curso.php?id=<?php echo $modulo['curso_id']; ?>" class="btn btn-secondary shadow">
            <i class="bi bi-arrow-90deg-left me-2"></i>Volver a editar curso
        </a>
    </div>
</div>

<div class="container py-3">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg">
                <div class="card-header">
                    <h3 class="card-title mb-0 text-center"><b>Recursos del Módulo</b></h3>
                </div>
                <div class="card-body p-4">
                    <form action="controladores/modificar_recurso.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="modulo_id" value="<?php echo $modulo_id; ?>">
                        <input type="hidden" name="recursos_eliminados" id="recursos_eliminados" value="">

                        <div id="recursos-container">
                            <?php foreach ($recursos as $recurso): ?>
                                <div class="recurso-item mb-3">
                                    <input type="hidden" name="recurso_ids[]" value="<?php echo $recurso['id']; ?>">

                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h5 class="collapse-toggle" data-bs-toggle="collapse" data-bs-target="#recurso<?php echo $recurso['id']; ?>">
                                            <i class="bi bi-chevron-down me-2"></i>
                                            Recurso: <?php echo htmlspecialchars($recurso['descripcion']); ?>
                                        </h5>
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="eliminarRecurso(this)">
                                            <i class="bi bi-trash"></i> Eliminar Recurso
                                        </button>
                                    </div>

                                    <div class="collapse show" id="recurso<?php echo $recurso['id']; ?>">
                                        <div class="mb-3">
                                            <label class="form-label">Descripción del Recurso</label>
                                            <input type="text" class="form-control" name="recurso_descripciones[]" value="<?php echo htmlspecialchars($recurso['descripcion']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Tipo de Recurso</label>
                                            <select class="form-select" name="recurso_tipos[]" onchange="toggleRecursoFields(this)" required>
                                                <option value="url" <?php echo $recurso['tipo'] == 'url' ? 'selected' : ''; ?>>URL</option>
                                                <option value="archivo" <?php echo $recurso['tipo'] == 'archivo' ? 'selected' : ''; ?>>Archivo</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 recurso-url" style="display: <?php echo $recurso['tipo'] == 'url' ? 'block' : 'none'; ?>;">
                                            <label class="form-label">URL del Recurso</label>
                                            <input type="url" class="form-control" name="recurso_urls[]" value="<?php echo htmlspecialchars($recurso['url']); ?>">
                                        </div>
                                        <div class="mb-3 recurso-archivo" style="display: <?php echo $recurso['tipo'] == 'archivo' ? 'block' : 'none'; ?>;">
                                            <label class="form-label">Archivo del Recurso</label>
                                            <?php if ($recurso['archivo_path']): ?>
                                                <p>Archivo actual: <?php echo basename($recurso['archivo_path']); ?></p>
                                            <?php endif; ?>
                                            <input type="file" class="form-control" name="recurso_archivos[]">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <button type="button" class="btn btn-primary mb-3" onclick="agregarRecurso()">
                            <i class="bi bi-plus-circle me-2"></i>Agregar Nuevo Recurso
                        </button>

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

<script>
    function agregarRecurso() {
        const container = document.getElementById('recursos-container');
        const recursoId = 'nuevo_' + Date.now();
        const recursoItem = document.createElement('div');
        recursoItem.className = 'recurso-item mb-3';

        recursoItem.innerHTML = `
            <input type="hidden" name="recurso_ids[]" value="nuevo">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="collapse-toggle" data-bs-toggle="collapse" data-bs-target="#recurso${recursoId}">
                    <i class="bi bi-chevron-down me-2"></i>
                    Nuevo Recurso
                </h5>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="eliminarRecurso(this)">
                    <i class="bi bi-trash"></i> Eliminar Recurso
                </button>
            </div>
            <div class="collapse show" id="recurso${recursoId}">
                <div class="mb-3">
                    <label class="form-label">Descripción del Recurso</label>
                    <input type="text" class="form-control" name="recurso_descripciones[]" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tipo de Recurso</label>
                    <select class="form-select" name="recurso_tipos[]" onchange="toggleRecursoFields(this)" required>
                        <option value="url">URL</option>
                        <option value="archivo">Archivo</option>
                    </select>
                </div>
                <div class="mb-3 recurso-url">
                    <label class="form-label">URL del Recurso</label>
                    <input type="url" class="form-control" name="recurso_urls[]">
                </div>
                <div class="mb-3 recurso-archivo" style="display: none;">
                    <label class="form-label">Archivo del Recurso</label>
                    <input type="file" class="form-control" name="recurso_archivos[]">
                </div>
            </div>
        `;

        container.appendChild(recursoItem);
    }

    function eliminarRecurso(btn) {
        if (confirm('¿Estás seguro de que quieres eliminar este recurso?')) {
            const recursoItem = btn.closest('.recurso-item');
            const recursoId = recursoItem.querySelector('input[name="recurso_ids[]"]').value;
            
            if (recursoId !== 'nuevo') {
                const recursosEliminados = document.getElementById('recursos_eliminados');
                recursosEliminados.value += (recursosEliminados.value ? ',' : '') + recursoId;
            }
            
            recursoItem.remove();
        }
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

    // Inicializar todos los collapse y campos de recursos
    document.addEventListener('DOMContentLoaded', function() {
        var collapseElements = document.querySelectorAll('.collapse');
        collapseElements.forEach(function(el) {
            new bootstrap.Collapse(el, {
                toggle: false
            });
        });

        // Inicializar los campos de recursos existentes
        document.querySelectorAll('select[name="recurso_tipos[]"]').forEach(function(select) {
            toggleRecursoFields(select);
        });
    });
</script>