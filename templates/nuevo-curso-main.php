<!-- TITULO -->
<div class="page-title" data-aos="fade">
    <div class="heading p-5">
        <div class="container">
            <div class="row d-flex justify-content-center text-center">
                <div class="col-lg-8">
                    <h1>Formulario para agregar un CURSO<i class="bi bi-journal m-2"></i></h1>
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
                    <h3 class="card-title mb-0 text-center"><b>Agregar Curso</b></h3>
                </div>
                <div class="card-body p-4">
                    <form action="controladores/agregar_curso.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label for="titulo" class="form-label">Título del Curso</label>
                            <input type="text"
                                class="form-control"
                                id="titulo"
                                name="titulo"
                                required>
                        </div>

                        <div class="mb-4">
                            <label for="descripcion" class="form-label">Descripción del Curso</label>
                            <textarea
                                class="form-control"
                                id="descripcion"
                                name="descripcion"
                                rows="6"
                                required></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="imagen" class="form-label">Imagen del Curso</label>
                            <input type="file"
                                class="form-control"
                                id="imagen"
                                name="imagen"
                                accept="image/*">
                            <div class="form-text">Seleccione una imagen representativa para el curso</div>
                        </div>

                        <div class="mb-4">
                            <label for="programa" class="form-label">Programa del Curso (PDF)</label>
                            <input type="file"
                                class="form-control"
                                id="programa"
                                name="programa"
                                accept=".pdf">
                            <div class="form-text">Seleccione el programa del curso en formato PDF</div>
                        </div>

                        <div class="mb-4">
                            <label for="form_insc" class="form-label">URL del Formulario de Inscripción</label>
                            <input type="text"
                                class="form-control"
                                id="form_insc"
                                name="form_insc"
                                placeholder="https://ejemplo.com/formulario">
                        </div>

                        <div class="mb-4">
                            <label for="cantidad_horas" class="form-label">Cantidad de Horas</label>
                            <input type="number"
                                class="form-control"
                                id="cantidad_horas"
                                name="cantidad_horas"
                                min="1"
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
                                value="<?php echo date('Y'); ?>"
                                required>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-plus-circle me-2"></i>Agregar Curso
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