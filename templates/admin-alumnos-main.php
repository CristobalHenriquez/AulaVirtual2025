<?php
// Obtener todos los usuarios con rol de alumno
$stmt = $db->prepare("
    SELECT u.*, 
           GROUP_CONCAT(DISTINCT c.titulo SEPARATOR '||') as cursos_inscritos,
           GROUP_CONCAT(DISTINCT i.curso_id) as curso_ids
    FROM usuarios u
    LEFT JOIN inscripciones i ON u.id = i.usuario_id
    LEFT JOIN cursos c ON i.curso_id = c.id
    GROUP BY u.id
    ORDER BY u.id ASC
");
$stmt->execute();
$result = $stmt->get_result();
$alumnos = $result->fetch_all(MYSQLI_ASSOC);

// Obtener todos los cursos para el modal, ordenados por fecha de creación descendente
$stmt = $db->prepare("SELECT id, titulo FROM cursos ORDER BY created_at DESC");
$stmt->execute();
$cursos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!-- Page Title -->
<div class="page-title" data-aos="fade">
    <div class="heading">
        <div class="container">
            <div class="row d-flex justify-content-center text-center">
                <div class="col-lg-8">
                    <h1>Panel administrador de Alumnos</h1>
                    <p class="mb-0">Gestiona los alumnos desde aqui, cambiando sus datos o agregando cursos</p>
                </div>
            </div>
        </div>
    </div>
</div><!-- End Page Title -->
<!-- Add this right after the page title div and before the container-fluid div -->
<div class="container-fluid col-lg-10 pt-3">
    <div class="d-flex justify-content-end">
        <a href="nuevo-alumno.php" class="btn btn-success">
            <i class="bi bi-person-plus-fill me-2"></i>Agregar Usuario
        </a>
    </div>
</div>
<!-- Rest of the existing code remains the same -->
<div class="container-fluid col-lg-10 py-5">
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaAlumnos" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nombre y apellido</th>
                            <th>Municipio/Empresa</th>
                            <th>Email</th>
                            <th>Cursos Inscritos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alumnos as $alumno): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellidos']); ?></td>
                                <td><?php echo htmlspecialchars($alumno['municipio']); ?></td>
                                <td><?php echo htmlspecialchars($alumno['email']); ?></td>
                                <td>
                                    <?php if ($alumno['cursos_inscritos']): ?>
                                        <ul class="mb-0">
                                            <?php foreach (explode('||', $alumno['cursos_inscritos']) as $curso): ?>
                                                <li><?php echo htmlspecialchars($curso); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        No inscrito
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-warning btn-circle btn-sm editar-alumno"
                                        data-id="<?php echo $alumno['id']; ?>"
                                        data-nombre="<?php echo htmlspecialchars($alumno['nombre']); ?>"
                                        data-apellidos="<?php echo htmlspecialchars($alumno['apellidos']); ?>"
                                        data-email="<?php echo htmlspecialchars($alumno['email']); ?>"
                                        data-cursos="<?php echo htmlspecialchars($alumno['curso_ids']); ?>">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    <button class="btn btn-danger btn-circle btn-sm eliminar-alumno"
                                        data-id="<?php echo $alumno['id']; ?>">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Usuario -->
<div class="modal fade" id="editarUsuarioModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editarUsuarioForm" action="controladores/modificar_alumno.php" method="POST">
                    <input type="hidden" name="id" id="usuario_id">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="apellidos" class="form-label">Apellidos</label>
                        <input type="text" class="form-control" id="apellidos" name="apellidos" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cursos</label>
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
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="editarUsuarioForm" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<style>
    .btn-circle {
        width: 30px;
        height: 30px;
        padding: 6px 0px;
        border-radius: 15px;
        text-align: center;
        font-size: 12px;
        line-height: 1.42857;
    }

    #tablaAlumnos thead {
        background-color: #343a40;
        color: white;
    }

    #tablaAlumnos thead th {
        font-weight: normal;
    }

    .dataTables_wrapper .dataTables_length select {
        padding-right: 25px;
    }

    .dataTables_wrapper .dataTables_filter input {
        margin-left: 0.5em;
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 0.375rem 0.75rem;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar DataTable (sin cambios)
    new DataTable('#tablaAlumnos', {
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        columnDefs: [
            {
                targets: -1,
                orderable: false,
                searchable: false
            }
        ],
        order: [[0, 'asc']],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
        dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rtip'
    });

    // Manejar el clic en el botón de editar
    document.querySelectorAll('.editar-alumno').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const nombre = this.dataset.nombre;
            const apellidos = this.dataset.apellidos;
            const email = this.dataset.email;
            const cursoIds = this.dataset.cursos ? this.dataset.cursos.split(',') : [];

            // Limpiar checkboxes anteriores
            document.querySelectorAll('input[name="cursos[]"]').forEach(checkbox => {
                checkbox.checked = false;
            });

            // Establecer valores en el formulario
            document.getElementById('usuario_id').value = id;
            document.getElementById('nombre').value = nombre;
            document.getElementById('apellidos').value = apellidos;
            document.getElementById('email').value = email;

            // Marcar los checkboxes de los cursos del alumno
            cursoIds.forEach(cursoId => {
                const checkbox = document.getElementById(`curso_${cursoId}`);
                if (checkbox) {
                    checkbox.checked = true;
                }
            });

            // Mostrar el modal
            const modal = new bootstrap.Modal(document.getElementById('editarUsuarioModal'));
            modal.show();
        });
    });

    // Manejar el checkbox de seleccionar/deseleccionar todos
    document.getElementById('selectAllCursos').addEventListener('change', function() {
        const isChecked = this.checked;
        document.querySelectorAll('.curso-checkbox').forEach(checkbox => {
            checkbox.checked = isChecked;
        });
    });

    // Manejar el clic en el botón de eliminar
    document.querySelectorAll('.eliminar-alumno').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Confirmar eliminación',
                        text: "Por favor, confirma nuevamente que deseas eliminar este alumno",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, eliminar definitivamente',
                        cancelButtonText: 'Cancelar'
                    }).then((secondResult) => {
                        if (secondResult.isConfirmed) {
                            window.location.href = `controladores/eliminar_alumno.php?id=${id}`;
                        }
                    });
                }
            });
        });
    });
});
</script>

