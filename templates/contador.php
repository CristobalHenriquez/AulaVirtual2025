<?php
// Función para obtener el conteo
function getCount($db, $table) {
    $query = "SELECT COUNT(*) as count FROM $table";
    $result = $db->query($query);
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    return 0;
}

// Obtener los conteos
$alumnosCount = getCount($db, 'usuarios');
$cursosCount = getCount($db, 'cursos');
$inscripcionesCount = getCount($db, 'inscripciones');
?>

<section id="counts" class="section counts light-background">
    <div class="container" data-aos="fade-up" data-aos-delay="100">
        <div class="row gy-4">
            <div class="col-lg-4 col-md-6">
                <div class="stats-item text-center w-100 h-100">
                    <span data-purecounter-start="0" data-purecounter-end="<?php echo $alumnosCount; ?>" data-purecounter-duration="1" class="purecounter"></span>
                    <p>Alumnos</p>
                </div>
            </div><!-- End Stats Item -->

            <div class="col-lg-4 col-md-6">
                <div class="stats-item text-center w-100 h-100">
                    <span data-purecounter-start="0" data-purecounter-end="<?php echo $cursosCount; ?>" data-purecounter-duration="1" class="purecounter"></span>
                    <p>Cursos</p>
                </div>
            </div><!-- End Stats Item -->

            <div class="col-lg-4 col-md-12">
                <div class="stats-item text-center w-100 h-100">
                    <span data-purecounter-start="0" data-purecounter-end="<?php echo $inscripcionesCount; ?>" data-purecounter-duration="1" class="purecounter"></span>
                    <p>Inscripciones</p>
                </div>
            </div><!-- End Stats Item -->
        </div>
    </div>
</section>