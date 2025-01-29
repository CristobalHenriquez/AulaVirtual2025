<?php
// Obtener todos los cursos ordenados por fecha de creación (más nuevos primero)
$stmt = $db->prepare("SELECT * FROM cursos ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
$cursos = $result->fetch_all(MYSQLI_ASSOC);
?>
<!-- TITULO -->
<div class="page-title" data-aos="fade">
    <div class="heading">
        <div class="container">
            <div class="row d-flex justify-content-center text-center">
                <div class="col-lg-8">
                    <h1>Panel administrador de Cursos</h1>
                    <p class="mb-0">Gestiona los cursos desde aquí, agregando módulos, modificando sus datos o eliminándolos</p>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- BOTONES -->
<div class="container-fluid col-lg-10 pt-3">
    <div class="d-flex justify-content-between">
        <a href="admin.php" class="btn btn-secondary shadow">
            <i class="bi bi-arrow-90deg-left me-2"></i>Volver a panel general
        </a>
        <a href="nuevo-curso.php" class="btn btn-success shadow">
            <i class="bi bi-book-half me-2"></i>Agregar Curso
        </a>
    </div>
</div>
<!-- CONTAINER -->
<div class="container-fluid col-lg-10 py-3">
    <div class="card shadow-lg">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaCursos" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Título</th>
                            <th>Descripción</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cursos as $curso): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo htmlspecialchars($curso['imagen_path']); ?>"
                                        alt="<?php echo htmlspecialchars($curso['titulo']); ?>"
                                        class="img-fluid" style="max-width: 200px; max-height: 200px;">
                                </td>
                                <td><?php echo htmlspecialchars($curso['titulo']); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($curso['descripcion'])); ?></td>
                                <td>
                                    <a href="editar-curso.php?id=<?php echo $curso['id']; ?>" class="btn btn-warning btn-sm mb-2">
                                        <i class="bi bi-pencil-fill"></i> Editar
                                    </a>
                                    <button class="btn btn-danger btn-sm eliminar-curso"
                                        data-id="<?php echo $curso['id']; ?>">
                                        <i class="bi bi-trash-fill"></i> Eliminar
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

<style>
    #tablaCursos thead {
        background-color: #343a40;
        color: white;
    }

    #tablaCursos thead th {
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
        // Inicializar DataTable
        new DataTable('#tablaCursos', {
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            columnDefs: [{
                targets: -1,
                orderable: false,
                searchable: false
            }],
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, "Todos"]
            ],
            dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rtip'
        });

        // Manejar el clic en el botón de eliminar
        document.querySelectorAll('.eliminar-curso').forEach(button => {
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
                            text: "Por favor, confirma nuevamente que deseas eliminar este curso",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Sí, eliminar definitivamente',
                            cancelButtonText: 'Cancelar'
                        }).then((secondResult) => {
                            if (secondResult.isConfirmed) {
                                // Crear un formulario dinámicamente
                                const form = document.createElement('form');
                                form.method = 'POST';
                                form.action = 'controladores/eliminar_curso.php';
                                
                                // Crear un input oculto para el ID del curso
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = 'curso_id';
                                input.value = id;
                                
                                // Añadir el input al formulario
                                form.appendChild(input);
                                
                                // Añadir el formulario al body y enviarlo
                                document.body.appendChild(form);
                                form.submit();
                            }
                        });
                    }
                });
            });
        });
    });
</script>