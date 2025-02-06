<!-- Page Title -->
<div class="page-title" data-aos="fade">
    <div class="heading">
        <div class="container">
            <div class="row d-flex justify-content-center text-center">
                <div class="col-lg-8">
                    <h1>Cursos</h1>
                    <p class="mb-0">Explora nuestra amplia gama de cursos y encuentra el que necesitas para llevar tu carrera al siguiente nivel.</p>
                </div>
            </div>
        </div>
    </div>
</div><!-- End Page Title -->

<!-- Courses Section -->
<section id="courses" class="courses section">
    <div class="container">
        <div class="row">
            <?php
            // Consulta modificada para excluir el curso con ID 26
            $sql = "SELECT titulo, descripcion, imagen_path, programa_pdf_path, cantidad_horas, form_insc 
                FROM cursos 
                WHERE id != 26
                ORDER BY created_at DESC";
            $result = $db->query($sql);

            // Verificar si hay resultados
            if ($result->num_rows > 0) {
                // Mostrar cada curso
                while ($curso = $result->fetch_assoc()) {
            ?>
                    <div class="col-lg-4 col-md-6 d-flex align-items-stretch mb-4" data-aos="zoom-in" data-aos-delay="100">
                        <div class="course-item">
                            <img src="<?php echo $curso['imagen_path']; ?>" class="img-fluid" alt="<?php echo $curso['titulo']; ?>">
                            <div class="course-content">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <p class="category">Curso</p>
                                    <p class="price"><?php echo $curso['cantidad_horas']; ?> horas</p>
                                </div>

                                <h3><?php echo $curso['titulo']; ?></h3>
                                <p class="description"><?php echo $curso['descripcion']; ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <button class="btn btn-success ver-programa-btn">
                                        <a href="<?php echo $curso['programa_pdf_path']; ?>" target="_blank" class="text-white">Ver Programa</a>
                                    </button>
                                    <?php if (!empty($curso['form_insc'])): ?>
                                        <button class="btn btn-primary inscripcion-btn">
                                            <a href="<?php echo $curso['form_insc']; ?>" target="_blank" class="text-white">Inscripción</a>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
            <?php
                }
            } else {
                echo "<div class='col-12 text-center'><p>No hay cursos disponibles en este momento.</p></div>";
            }
            ?>
        </div>
    </div>
</section><!-- /Courses Section -->