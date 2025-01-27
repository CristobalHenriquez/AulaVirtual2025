<!-- Page Title -->
<div class="admin-header text-center text-white py-4"">
    <h1>Panel de Administrador</h1>
    <p class="text-light">Gestiona los cursos y usuarios desde aquí.</p>
</div>

<div class="container-fluid py-4 col-lg-10">
    <div class="card">
        <div class="card-body ">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="card-title">Gestión de Cursos</h2>
                <button class="btn btn-success" onclick="window.location.href='agregar-curso.php'">
                    Agregar Nuevo Curso
                </button>
            </div>

            <div class="table-responsive">
                <table id="cursosTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Título</th>
                            <th>Descripción</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Consulta para obtener todos los cursos
                        $sql = "SELECT id, titulo, descripcion, imagen_path FROM cursos ORDER BY created_at DESC";
                        $result = $db->query($sql);

                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td><img src='" . htmlspecialchars($row['imagen_path']) . "' alt='Imagen del curso' class='img-thumbnail' style='max-width: 100px;'></td>";
                                echo "<td>" . htmlspecialchars($row['titulo']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['descripcion']) . "</td>";
                                echo "<td>
                                        <button onclick=\"editarCurso(" . $row['id'] . ")\" class='btn btn-primary btn-sm'>Editar</button>
                                        <button onclick=\"eliminarCurso(" . $row['id'] . ")\" class='btn btn-danger btn-sm ms-2'>Eliminar</button>
                                    </td>";
                                echo "</tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Agregar DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css">

<!-- Agregar DataTables JS -->
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Inicializar DataTable
    $('#cursosTable').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
        }
    });
});

// Función para editar curso
function editarCurso(id) {
    window.location.href = 'editar-curso.php?id=' + id;
}

// Función para eliminar curso
function eliminarCurso(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "No podrás revertir esta acción",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Realizar la eliminación mediante AJAX
            $.ajax({
                url: 'eliminar-curso.php',
                type: 'POST',
                data: { id: id },
                success: function(response) {
                    Swal.fire(
                        '¡Eliminado!',
                        'El curso ha sido eliminado.',
                        'success'
                    ).then(() => {
                        location.reload();
                    });
                },
                error: function() {
                    Swal.fire(
                        'Error',
                        'Ocurrió un error al eliminar el curso.',
                        'error'
                    );
                }
            });
        }
    });
}
</script>

<style>
.admin-header {
    padding: 2rem 0;
    margin-bottom: 2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card {
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
    border: none;
    border-radius: 10px;
}

.table img {
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.btn-success {
    background-color: #27ae60;
    border-color: #27ae60;
}

.btn-success:hover {
    background-color: #219a52;
    border-color: #219a52;
}
</style>