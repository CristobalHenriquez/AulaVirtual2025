<?php
// Obtener todos los cursos para el formulario, separados por premium y no premium
$stmt = $db->prepare("SELECT id, titulo, premium FROM cursos ORDER BY premium DESC, created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
$cursos_premium = [];
$cursos_no_premium = [];

while ($curso = $result->fetch_assoc()) {
    if ($curso['premium']) {
        $cursos_premium[] = $curso;
    } else {
        $cursos_no_premium[] = $curso;
    }
}

// Obtener todos los municipios para el select del formulario
$stmt = $db->prepare("
    SELECT m.id, m.name as nombre_municipio, p.name as nombre_provincia 
    FROM municipios m
    LEFT JOIN provincias p ON m.province_id = p.id
    WHERE m.visible = 1
    ORDER BY p.name, m.name
");
$stmt->execute();
$municipios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!-- TITULO -->
<div class="page-title" data-aos="fade">
    <div class="heading p-5">
        <div class="container">
            <div class="row d-flex justify-content-center text-center">
                <div class="col-lg-8">
                    <h1>Formulario para agregar un USUARIO<i class="bi bi-person"></i></h1>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- BOTONES -->
<div class="container-fluid col-lg-10 pt-3">
    <div class="d-flex justify-content-start">
        <a href="admin-alumnos.php" class="btn btn-secondary shadow">
            <i class="bi bi-arrow-90deg-left me-2"></i>Volver a panel alumnos
        </a>
    </div>
</div>
<div class="container py-3">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg">
                <div class="card-header">
                    <h3 class="card-title mb-0 text-center"><b>Agregar Usuario</b></h3>
                </div>
                <div class="card-body">
                    <form action="controladores/agregar_alumno.php" method="POST">
                        <div class="col-12 row">
                            <div class="mb-3 col-6">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>

                            <div class="mb-3 col-6">
                            <label for="dni" class="form-label">DNI (es recomendable para el certificado)</label>
                                <input type="number" class="form-control" id="dni" name="dni"
                                    placeholder="No es obligatorio este campo">
                            </div>
                        </div>
                        <div class="col-12 row">
                            <div class="mb-3 col-6">
                                <label for="id_municipio" class="form-label">Municipio</label>
                                <select class="form-select" id="id_municipio" name="id_municipio">
                                    <option value="">Sin municipio asociado</option>
                                    <?php foreach ($municipios as $municipio): ?>
                                        <option value="<?php echo $municipio['id']; ?>">
                                            <?php echo htmlspecialchars($municipio['nombre_municipio']); ?>
                                            <?php if (!empty($municipio['nombre_provincia'])): ?>
                                                (<?php echo htmlspecialchars($municipio['nombre_provincia']); ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3 col-6">
                                <label for="municipio" class="form-label">Institución/Empresa</label>
                                <input type="text" class="form-control" id="municipio" name="municipio"
                                    placeholder="No es obligatorio este campo">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <input type="text" class="form-control" id="estado" name="estado"
                                placeholder="No es obligatorio este campo">
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="ramcc" name="ramcc" value="1">
                                <label class="form-check-label" for="ramcc">
                                    <strong>RAMCC</strong> (Inscribe automáticamente en todos los cursos no premium)
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="rol" class="form-label">Rol</label>
                            <select class="form-select" id="rol" name="rol" required>
                                <option value="alumno" selected>Alumno</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>

                        <!-- Sección de cursos no premium -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Cursos Estándar</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="selectAllNoPremium">
                                <label class="form-check-label" for="selectAllNoPremium">
                                    Seleccionar/Deseleccionar todos los cursos estándar
                                </label>
                            </div>
                            <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                <?php foreach ($cursos_no_premium as $curso): ?>
                                    <div class="form-check">
                                        <input class="form-check-input curso-no-premium" type="checkbox"
                                            name="cursos[]"
                                            value="<?php echo $curso['id']; ?>"
                                            id="curso_<?php echo $curso['id']; ?>">
                                        <label class="form-check-label" for="curso_<?php echo $curso['id']; ?>">
                                            <?php echo htmlspecialchars($curso['titulo']); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Sección de cursos premium -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Cursos Premium</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="selectAllPremium">
                                <label class="form-check-label" for="selectAllPremium">
                                    Seleccionar/Deseleccionar todos los cursos premium
                                </label>
                            </div>
                            <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                <?php foreach ($cursos_premium as $curso): ?>
                                    <div class="form-check">
                                        <input class="form-check-input curso-premium" type="checkbox"
                                            name="cursos[]"
                                            value="<?php echo $curso['id']; ?>"
                                            id="curso_<?php echo $curso['id']; ?>">
                                        <label class="form-check-label" for="curso_<?php echo $curso['id']; ?>">
                                            <?php echo htmlspecialchars($curso['titulo']); ?>
                                            <span class="badge bg-warning text-dark">Premium</span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="form-text">
                                Para seleccionar más de un curso, marca las casillas correspondientes.
                            </div>
                        </div>
                        
                        <!-- BOTONES -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-plus-circle me-2"></i>Agregar Usuario
                            </button>
                            <a href="admin-alumnos.php" class="btn btn-danger">
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
    document.addEventListener('DOMContentLoaded', function() {
        // Función para manejar los checkboxes de cursos según RAMCC
        function handleCursosCheckboxes(ramccChecked) {
            const cursosNoPremiumCheckboxes = document.querySelectorAll('.curso-no-premium');
            const cursosPremiumCheckboxes = document.querySelectorAll('.curso-premium');
            const selectAllNoPremium = document.getElementById('selectAllNoPremium');
            const selectAllPremium = document.getElementById('selectAllPremium');

            // Si RAMCC está activado, marcar todos los cursos no premium y desactivar su selección
            cursosNoPremiumCheckboxes.forEach(checkbox => {
                checkbox.checked = ramccChecked;
                if (ramccChecked) {
                    checkbox.setAttribute('onclick', 'return false');
                    checkbox.parentNode.classList.add('text-muted');
                } else {
                    checkbox.removeAttribute('onclick');
                    checkbox.parentNode.classList.remove('text-muted');
                }
            });

            // Configurar el checkbox "Seleccionar todos" para cursos no premium
            selectAllNoPremium.checked = ramccChecked;
            if (ramccChecked) {
                selectAllNoPremium.setAttribute('onclick', 'return false');
                selectAllNoPremium.parentNode.classList.add('text-muted');
            } else {
                selectAllNoPremium.removeAttribute('onclick');
                selectAllNoPremium.parentNode.classList.remove('text-muted');
            }

            // Los cursos premium siempre son seleccionables individualmente
            // No se marcan automáticamente con RAMCC
            if (ramccChecked) {
                cursosPremiumCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                selectAllPremium.checked = false;
            }
        }

        // Manejar cambios en el checkbox RAMCC
        document.getElementById('ramcc').addEventListener('change', function() {
            handleCursosCheckboxes(this.checked);
        });

        // Manejar el checkbox de seleccionar/deseleccionar todos los cursos no premium
        document.getElementById('selectAllNoPremium').addEventListener('change', function() {
            if (!document.getElementById('ramcc').checked) {
                const isChecked = this.checked;
                document.querySelectorAll('.curso-no-premium').forEach(checkbox => {
                    checkbox.checked = isChecked;
                });
            }
        });

        // Manejar el checkbox de seleccionar/deseleccionar todos los cursos premium
        document.getElementById('selectAllPremium').addEventListener('change', function() {
            const isChecked = this.checked;
            document.querySelectorAll('.curso-premium').forEach(checkbox => {
                checkbox.checked = isChecked;
            });
        });
    });
</script>