<?php
// Obtener todos los cursos para el formulario, ordenados por fecha de creación descendente
$stmt = $db->prepare("SELECT id, titulo FROM cursos ORDER BY created_at DESC");
$stmt->execute();
$cursos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>

                        <div class="mb-3">
                            <label for="apellidos" class="form-label">Apellidos</label>
                            <input type="text" class="form-control" id="apellidos" name="apellidos" required>
                        </div>

                        <div class="mb-3">
                            <label for="dni" class="form-label">DNI</label>
                            <input type="text" class="form-control" id="dni" name="dni" 
                                   placeholder="No es obligatorio este campo">
                        </div>

                        <div class="mb-3">
                            <label for="municipio" class="form-label">Municipio/Institución</label>
                            <input type="text" class="form-control" id="municipio" name="municipio" 
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
                            <label for="rol" class="form-label">Rol</label>
                            <select class="form-select" id="rol" name="rol" required>
                                <option value="alumno" selected>Alumno</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Cursos (opcional)</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="selectAllCursos">
                                <label class="form-check-label" for="selectAllCursos">
                                    Seleccionar/Deseleccionar todos
                                </label>
                            </div>
                            <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                <?php foreach ($cursos as $curso): ?>
                                    <div class="form-check">
                                        <input class="form-check-input curso-checkbox" type="checkbox"
                                            name="cursos[]"
                                            value="<?php echo $curso['id']; ?>"
                                            id="curso_<?php echo $curso['id']; ?>">
                                        <label class="form-check-label" for="curso_<?php echo $curso['id']; ?>">
                                            <?php echo htmlspecialchars($curso['titulo']); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="form-text">
                                Para seleccionar más de un curso, marca las casillas correspondientes.
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">Agregar Usuario</button>
                            <a href="admin-alumnos.php" class="btn btn-danger">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Manejar el checkbox de seleccionar/deseleccionar todos
    document.getElementById('selectAllCursos').addEventListener('change', function() {
        const isChecked = this.checked;
        document.querySelectorAll('.curso-checkbox').forEach(checkbox => {
            checkbox.checked = isChecked;
        });
    });
});
</script>