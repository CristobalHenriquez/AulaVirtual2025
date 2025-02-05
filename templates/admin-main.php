<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-lg border-0">
                <div class="card-header text-white py-4">
                    <h2 class="text-center mb-0"><b>Panel de Administración</b></h2>
                </div>
                <div class="card-body p-5">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card h-100 shadow-sm hover-card">
                                <div class="card-body text-center p-5">
                                    <i class="bi bi-book display-1 text-primary mb-3"></i>
                                    <h3 class="card-title mb-4">Gestión de Cursos</h3>
                                    <p class="card-text mb-4">Administra los cursos, agrega nuevos, edita o elimina existentes.</p>
                                    <a href="admin-cursos.php" class="btn btn-primary btn-lg w-100">
                                        Acceder
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100 shadow-sm hover-card">
                                <div class="card-body text-center p-5">
                                    <i class="bi bi-people display-1 text-success mb-3"></i>
                                    <h3 class="card-title mb-4">Gestión de Alumnos</h3>
                                    <p class="card-text mb-4">Gestiona los alumnos, agrega nuevos, edita o elimina existentes.</p>
                                    <a href="admin-alumnos.php" class="btn btn-success btn-lg w-100">
                                        Acceder
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-4">
                            <a href="logout.php" class="btn btn-danger btn-lg">Cerrar Sesión</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-card {
        transition: transform 0.3s ease;
    }

    .hover-card:hover {
        transform: translateY(-5px);
    }
</style>