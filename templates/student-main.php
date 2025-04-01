<!-- Page Title -->
<div class="page-title" data-aos="fade">
    <div class="heading p-4">
        <div class="container">
            <div class="row d-flex justify-content-center text-center">
                <div class="col-lg-6">
                    <h1>Panel de estudiantes</h1>
                    <p class="mb-0">Accede a tu información y cursos inscritos.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="student-panel">
    <div class="text-center mb-5">
        <h1>¡Bienvenido al aula virtual, <?php echo htmlspecialchars($usuario['nombre']); ?>!</h1>
        <p class="mb-2"><strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
        <p><strong>Municipio/Institución:</strong>
            <?php
            if (!empty($usuario['nombre_municipio'])) {
                echo htmlspecialchars($usuario['nombre_municipio']);
            } elseif (!empty($usuario['municipio'])) {
                echo htmlspecialchars($usuario['municipio']);
            } else {
                echo "No especificado";
            }
            ?>
        </p>
    </div>
    <?php if ($is_enrolled_in_course_26 && $course_26_data): ?>
        <div class="card mb-4">
            <div class="card-header text-white">
                <h3 class="mb-0 fw-bold"><?php echo htmlspecialchars($course_26_data['titulo']); ?></h3>
            </div>
            <div class="card-body">
                <p class="lead mb-4"><?php echo nl2br(htmlspecialchars($course_26_data['descripcion'])); ?></p>

                <h4 class="mb-3">Recursos:</h4>
                <div class="recursos-grid">
                    <?php
                    $recursos = obtenerRecursosCurso26($db);
                    foreach ($recursos as $recurso):
                        $icono = obtenerIconoRecursoCurso26($recurso['id']);
                        $enlace = $recurso['url'] ?: $recurso['archivo_path'];
                    ?>
                        <a href="<?php echo htmlspecialchars($enlace); ?>"
                            target="_blank"
                            class="recurso-btn">
                            <i class="<?php echo $icono; ?> me-2"></i>
                            <span><?php echo htmlspecialchars($recurso['descripcion']); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <style>
            .bg-accent {
                background-color: var(--accent-color);
            }

            .recursos-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 1rem;
                margin-top: 1rem;
            }

            .recurso-btn {
                display: flex;
                align-items: center;
                padding: 1rem;
                background-color: var(--accent-color);
                color: white;
                text-decoration: none;
                border-radius: 0.5rem;
                transition: all 0.3s ease;
                height: 100%;
                min-height: 60px;
                font-size: 1rem;
                border: 2px solid transparent;
            }

            .recurso-btn:hover {
                background-color: white;
                color: var(--accent-color);
                border-color: var(--accent-color);
                transform: translateY(-2px);
                box-shadow: 0 4px 6px rgba(53, 83, 106, 0.2);
            }

            .recurso-btn i {
                font-size: 1.5rem;
                min-width: 2rem;
            }

            .recurso-btn span {
                flex: 1;
            }

            @media (max-width: 768px) {
                .recursos-grid {
                    grid-template-columns: 1fr;
                }
            }

            /* Estilos para la sección de examen */
            .examen-section {
                margin-top: 1.5rem;
                padding: 1rem;
                background-color: #f0f7ff;
                border-radius: 0.5rem;
                border-left: 4px solid var(--primary-color);
            }
        </style>
    <?php endif; ?>

    <h2 class="mb-4">Cursos Inscritos:</h2>

    <div class="accordion" id="aniosAccordion">
        <?php foreach ($cursos_por_anio as $anio => $cursos): ?>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse"
                        data-bs-target="#anio<?php echo $anio; ?>">
                        <?php echo $anio; ?>
                    </button>
                </h2>
                <div id="anio<?php echo $anio; ?>" class="accordion-collapse collapse" data-bs-parent="#aniosAccordion">
                    <div class="accordion-body">
                        <div class="accordion" id="cursosAccordion<?php echo $anio; ?>">
                            <?php foreach ($cursos as $curso): ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#curso<?php echo $curso['id']; ?>">
                                            <?php echo htmlspecialchars($curso['titulo']); ?>
                                        </button>
                                    </h2>
                                    <div id="curso<?php echo $curso['id']; ?>"
                                        class="accordion-collapse collapse"
                                        data-bs-parent="#cursosAccordion<?php echo $anio; ?>">
                                        <div class="accordion-body">
                                            <div class="text-center mb-3">
                                                <img src="<?php echo htmlspecialchars($curso['imagen_path']); ?>" alt="Imagen del curso" class="img-fluid rounded-2 m-2" style="height: auto; width: 500px;">
                                            </div>
                                            <p style="text-align: justify;"><?php echo nl2br(htmlspecialchars($curso['descripcion'])); ?></p>

                                            <?php if ($curso['programa_pdf_path']): ?>
                                                <div class="mb-3">
                                                    <a href="<?php echo htmlspecialchars($curso['programa_pdf_path']); ?>"
                                                        class="btn btn-success" target="_blank">
                                                        Ver Programa Completo
                                                    </a>
                                                </div>
                                            <?php endif; ?>

                                            <h4 class="mt-4 mb-3">Módulos:</h4>
                                            <div class="accordion" id="modulosAccordion<?php echo $curso['id']; ?>">
                                                <?php
                                                $modulos = obtenerModulos($db, $curso['id']);
                                                foreach ($modulos as $modulo):
                                                    // Verificar si el módulo tiene exámenes activos
                                                    $tiene_examen = tieneExamenesActivos($db, $modulo['id']);

                                                    // Verificar si el usuario ya ha aprobado el examen de este módulo
                                                    $examen_aprobado = false;
                                                    if ($tiene_examen) {
                                                        $stmt = $db->prepare("
                                                            SELECT 1 FROM intentos_examen ie
                                                            JOIN examenes e ON ie.examen_id = e.id
                                                            WHERE e.modulo_id = ? AND ie.usuario_id = ? AND ie.aprobado = 1
                                                            LIMIT 1
                                                        ");
                                                        $stmt->bind_param("ii", $modulo['id'], $user_id);
                                                        $stmt->execute();
                                                        $examen_aprobado = $stmt->get_result()->num_rows > 0;
                                                    }
                                                ?>
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header">
                                                            <button class="accordion-button collapsed" type="button"
                                                                data-bs-toggle="collapse"
                                                                data-bs-target="#modulo<?php echo $modulo['id']; ?>">
                                                                <?php echo htmlspecialchars($modulo['titulo']); ?>
                                                                <?php if ($tiene_examen): ?>
                                                                    <?php if ($examen_aprobado): ?>
                                                                        <span class="badge bg-success ms-2">Examen completado</span>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-primary ms-2">Examen disponible</span>
                                                                    <?php endif; ?>
                                                                <?php endif; ?>
                                                            </button>
                                                        </h2>
                                                        <div id="modulo<?php echo $modulo['id']; ?>"
                                                            class="accordion-collapse collapse"
                                                            data-bs-parent="#modulosAccordion<?php echo $curso['id']; ?>">
                                                            <div class="accordion-body">
                                                                <div class="module-content">
                                                                    <?php echo nl2br(htmlspecialchars($modulo['descripcion'])); ?>
                                                                </div>

                                                                <!-- Recursos del módulo -->
                                                                <h5 class="mt-3">Recursos:</h5>
                                                                <ul class="list-group mb-3">
                                                                    <?php
                                                                    $recursos = obtenerRecursosModulo($db, $modulo['id']);
                                                                    foreach ($recursos as $recurso):
                                                                        $enlace = $recurso['es_local'] ? $recurso['archivo_path'] : $recurso['url'];
                                                                        $nombreRecurso = $recurso['descripcion'] ? $recurso['descripcion'] : basename($enlace);
                                                                        $tipo = $recurso['tipo_real'];
                                                                        $icono = obtenerIconoRecurso($tipo);
                                                                    ?>
                                                                        <li class="list-group-item">
                                                                            <?php if ($tipo === 'video'): ?>
                                                                                <div class="embed-responsive embed-responsive-16by9 mb-2">
                                                                                    <?php if ($recurso['es_local']): ?>
                                                                                        <video class="embed-responsive-item rounded-1" style="width: 300px;" controls>
                                                                                            <source src="<?php echo htmlspecialchars($enlace); ?>" type="video/mp4">
                                                                                            Tu navegador no soporta el elemento de video.
                                                                                        </video>
                                                                                    <?php else: ?>
                                                                                        <iframe class="embed-responsive-item" src="<?php echo htmlspecialchars($enlace); ?>" allowfullscreen></iframe>
                                                                                    <?php endif; ?>
                                                                                </div>
                                                                            <?php elseif ($tipo === 'imagen'): ?>
                                                                                <img src="<?php echo htmlspecialchars($enlace); ?>" class="img-fluid mb-2 rounded-2" style="width: 300px;" alt="<?php echo htmlspecialchars($nombreRecurso); ?>">
                                                                            <?php else: ?>
                                                                                <a href="<?php echo htmlspecialchars($enlace); ?>" target="_blank" class="d-flex align-items-center text-decoration-none">
                                                                                    <i class="<?php echo $icono; ?> me-2"></i>
                                                                                    <?php echo htmlspecialchars($nombreRecurso); ?>
                                                                                </a>
                                                                            <?php endif; ?>
                                                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($tipo); ?></span>
                                                                        </li>
                                                                    <?php endforeach; ?>
                                                                </ul>

                                                                <!-- Botón de examen si existe -->
                                                                <?php if ($tiene_examen): ?>
                                                                    <div class="examen-section">
                                                                        <h5 class="mb-3"><i class="bi bi-pencil-square me-2"></i>Examen</h5>
                                                                        <?php if ($examen_aprobado): ?>
                                                                            <p>¡Felicidades! Has completado satisfactoriamente el examen de este módulo.</p>
                                                                            <div class="d-flex gap-2">
                                                                                <button class="btn btn-secondary" disabled>
                                                                                    <i class="bi bi-lock-fill me-2"></i>Examen completado
                                                                                </button>
                                                                                <a href="Certificado_<?php
                                                                                                        // Obtener el ID del curso
                                                                                                        $stmt = $db->prepare("
                                                                                                            SELECT c.id FROM cursos c
                                                                                                            JOIN modulos m ON c.id = m.curso_id
                                                                                                            WHERE m.id = ?
                                                                                                            LIMIT 1
                                                                                                        ");
                                                                                                        $stmt->bind_param("i", $modulo['id']);
                                                                                                        $stmt->execute();
                                                                                                        $curso_data = $stmt->get_result()->fetch_assoc();
                                                                                                        $curso_id = $curso_data['id'];

                                                                                                        echo $user_id . '_' . $curso_id;
                                                                                                        ?>" class="btn btn-success" target="_blank">
                                                                                    <i class="bi bi-award-fill me-2"></i>Ver certificado
                                                                                </a>
                                                                            </div>
                                                                        <?php else: ?>
                                                                            <p>Evalúa tus conocimientos sobre este módulo realizando el examen disponible.</p>
                                                                            <a href="Examen_Modulo_<?php echo $modulo['id']; ?>" class="btn btn-primary">
                                                                                <i class="bi bi-pencil-square me-2"></i>Realizar Examen
                                                                            </a>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="text-center mt-4">
        <a href="logout.php" class="btn btn-danger">Cerrar Sesión</a>
    </div>
</div>