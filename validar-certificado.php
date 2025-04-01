<?php
require_once 'includes/conexion.php';

$mensaje = '';
$certificado = null;

if (isset($_GET['codigo'])) {
    $codigo = $_GET['codigo'];

    // Buscar el certificado por código de validación
    $stmt = $db->prepare("
    SELECT c.*, u.nombre as nombre_usuario, u.dni, 
           e.titulo as examen_titulo, 
           cu.titulo as curso_titulo, cu.fechas_curso, cu.cantidad_horas,
           cu.imagen_path, cu.programa_pdf_path
    FROM certificados c
    JOIN usuarios u ON c.usuario_id = u.id
    JOIN examenes e ON c.examen_id = e.id
    JOIN cursos cu ON c.curso_id = cu.id
    WHERE c.codigo_validacion = ?
");
    $stmt->bind_param("s", $codigo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $certificado = $result->fetch_assoc();
        $mensaje = '<div class="alert alert-success">El certificado es válido.</div>';
    } else {
        $mensaje = '<div class="alert alert-danger">El certificado no es válido o no existe.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validar Certificado - RAMCC</title>
    <link rel="icon" href="./assets/img/aula-logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 50px;
        }

        .validation-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .certificate-details {
            margin-top: 30px;
            padding: 20px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            background-color: #f8f9fa;
        }

        .certificate-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .certificate-header img {
            max-width: 300px;
            margin-bottom: 20px;
            margin-right: 5px;
            margin-left: 5px;
        }

        .certificate-details img.img-fluid {
            border: 1px solid #dee2e6;
            padding: 5px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .btn-primary,
        .btn-success {
            margin: 5px;
        }

        @media (max-width: 768px) {
            .certificate-details img.img-fluid {
                max-height: 150px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="validation-container">
            <div class="certificate-header">
                <a href="https://aula.ramcc.net/" target="_blank"><img src="assets/img/logo-elearning.png" alt="Logo E-Learning"></a>
                <a href="https://ramcc.net/" target="_blank"><img src="assets/img/logo-ramcc.png" alt="Logo RAMCC"></a>
                <h1>Validación de Certificados</h1>
                <p class="lead">Verifique la autenticidad de un certificado</p>
            </div>

            <form method="get" class="mb-4">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" name="codigo" placeholder="Ingrese el código de validación" required>
                    <button class="btn btn-outline-dark" type="submit">Validar</button>
                </div>
            </form>

            <?php echo $mensaje; ?>

            <?php if ($certificado): ?>
                <div class="certificate-details">
                    <h3 class="mb-4">Información del Certificado</h3>

                    <?php if (!empty($certificado['imagen_path'])): ?>
                        <div class="text-center mb-4">
                            <img src="<?php echo htmlspecialchars($certificado['imagen_path']); ?>" alt="Imagen del curso" class="img-fluid rounded" style="max-height: 200px;">
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nombre del Estudiante:</strong> <?php echo htmlspecialchars($certificado['nombre_usuario']); ?></p>
                            <?php if (!empty($certificado['dni'])): ?>
                                <p><strong>DNI:</strong> <?php echo htmlspecialchars($certificado['dni']); ?></p>
                            <?php endif; ?>
                            <p><strong>Curso:</strong> <?php echo htmlspecialchars($certificado['curso_titulo']); ?></p>
                            <p><strong>Examen:</strong> <?php echo htmlspecialchars($certificado['examen_titulo']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Fecha de Emisión:</strong> <?php echo date('d/m/Y', strtotime($certificado['fecha_generacion'])); ?></p>
                            <p><strong>Duración del Curso:</strong> <?php echo htmlspecialchars($certificado['cantidad_horas']); ?> horas</p>
                            <p><strong>Período del Curso:</strong> <?php echo htmlspecialchars($certificado['fechas_curso']); ?></p>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <a href="<?php echo $certificado['ruta_certificado']; ?>" class="btn btn-success me-2" target="_blank">Ver Certificado</a>

                        <?php if (!empty($certificado['programa_pdf_path'])): ?>
                            <a href="<?php echo htmlspecialchars($certificado['programa_pdf_path']); ?>" class="btn btn-info" target="_blank">
                            <i class="bi bi-filetype-pdf"></i>Ver Programa del Curso 
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>