<!-- Courses Section -->
<section id="courses" class="courses section">
    <!-- Section Title -->
    <div class="container section-title" data-aos="fade-up">
        <h2>Cursos</h2>
        <p>Cursos recientes</p>
    </div><!-- End Section Title -->

    <div class="container">
        <div class="row">
            <?php
            // Consulta para obtener los 3 cursos más recientes
            $sql = "SELECT titulo, descripcion, imagen_path, programa_pdf_path, cantidad_horas 
                    FROM cursos 
                    ORDER BY created_at DESC 
                    LIMIT 3";
            $result = $db->query($sql);

            // Verificar si hay resultados
            if ($result->num_rows > 0) {
                // Mostrar cada curso
                while($curso = $result->fetch_assoc()) {
            ?>
                <div class="col-lg-4 col-md-6 d-flex align-items-stretch" data-aos="zoom-in" data-aos-delay="100">
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
                                <button class="btn btn-success">
                                    <a href="<?php echo $curso['programa_pdf_path']; ?>" target="_blank">Ver Programa</a>
                                </button>
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