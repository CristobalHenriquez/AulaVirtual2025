<div class="student-panel">
    <div class="text-center mb-5">
        <h1>¡Bienvenido al aula virtual, <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']); ?>!</h1>
        <p class="mb-2"><strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
        <p><strong>Municipio/Institución:</strong> <?php echo htmlspecialchars($usuario['municipio']); ?></p>
    </div>

    <h2 class="mb-4">Cursos Inscritos:</h2>

    <div class="accordion" id="aniosAccordion">
        <?php foreach ($cursos_por_anio as $anio => $cursos): ?>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
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
                                            <?php echo htmlspecialchars($curso['titulo']); ?> /
                                            <?php echo date('F Y', strtotime($curso['created_at'])); ?>
                                        </button>
                                    </h2>
                                    <div id="curso<?php echo $curso['id']; ?>"
                                        class="accordion-collapse collapse"
                                        data-bs-parent="#cursosAccordion<?php echo $anio; ?>">
                                        <div class="accordion-body">
                                            <p><?php echo nl2br(htmlspecialchars($curso['descripcion'])); ?></p>

                                            <?php if ($curso['programa_pdf_path']): ?>
                                                <div class="mb-3">
                                                    <a href="<?php echo htmlspecialchars($curso['programa_pdf_path']); ?>"
                                                        class="btn btn-primary" target="_blank">
                                                        Ver Programa Completo
                                                    </a>
                                                </div>
                                            <?php endif; ?>

                                            <h4 class="mt-4 mb-3">Módulos:</h4>
                                            <div class="accordion" id="modulosAccordion<?php echo $curso['id']; ?>">
                                                <?php
                                                $modulos = obtenerModulos($db, $curso['id']);
                                                foreach ($modulos as $modulo):
                                                ?>
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header">
                                                            <button class="accordion-button collapsed" type="button"
                                                                data-bs-toggle="collapse"
                                                                data-bs-target="#modulo<?php echo $modulo['id']; ?>">
                                                                <?php echo htmlspecialchars($modulo['titulo']); ?>
                                                            </button>
                                                        </h2>
                                                        <div id="modulo<?php echo $modulo['id']; ?>"
                                                            class="accordion-collapse collapse"
                                                            data-bs-parent="#modulosAccordion<?php echo $curso['id']; ?>">
                                                            <div class="accordion-body">
                                                                <div class="module-content">
                                                                    <?php echo nl2br(htmlspecialchars($modulo['descripcion'])); ?>
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
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="text-center mt-4">
        <a href="logout.php" class="btn btn-danger">Cerrar Sesión</a>
    </div>
</div>