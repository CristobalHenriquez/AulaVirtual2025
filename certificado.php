<?php
require_once 'includes/conexion.php';

// Reemplazar la sección al inicio del archivo donde se verifican los parámetros
if (!isset($_GET['user_id']) || empty($_GET['user_id']) || !isset($_GET['curso_id']) || empty($_GET['curso_id'])) {
    header('Location: Alumno');
    exit;
}

$user_id = intval($_GET['user_id']);
$curso_id = intval($_GET['curso_id']);

// Buscar el usuario por ID
$stmt = $db->prepare("SELECT nombre FROM usuarios WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Si no se encuentra el usuario, mostrar un mensaje de error
    echo "Certificado no encontrado";
    exit;
}

$nombre_usuario = $result->fetch_assoc()['nombre'];

$nombre_usuario = str_replace('_', ' ', $nombre_usuario);
$curso_id = intval($_GET['curso_id']);

// Buscar el usuario por nombre
/*$stmt = $db->prepare("SELECT id FROM usuarios WHERE nombre = ? LIMIT 1");
$stmt->bind_param("s", $nombre_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Si no se encuentra el usuario, mostrar un mensaje de error
    echo "Certificado no encontrado";
    exit;
}

$user_id = $result->fetch_assoc()['id'];*/

// Obtener el intento aprobado más reciente para este usuario y curso
$stmt = $db->prepare("
    SELECT ie.id FROM intentos_examen ie
    JOIN examenes e ON ie.examen_id = e.id
    JOIN modulos m ON e.modulo_id = m.id
    WHERE m.curso_id = ? AND ie.usuario_id = ? AND ie.aprobado = 1
    ORDER BY ie.fecha_fin DESC LIMIT 1
");
$stmt->bind_param("ii", $curso_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: Alumno');
    exit;
}

$intento_id = $result->fetch_assoc()['id'];

// Obtener información del intento y verificar que esté aprobado
// Modificar la consulta para incluir el DNI del usuario
$stmt = $db->prepare("SELECT i.*, e.titulo as examen_titulo, e.nota_aprobacion, 
                      m.titulo as modulo_titulo, c.titulo as curso_titulo, c.cantidad_horas,
                      c.anio, c.fechas_curso, u.nombre as nombre_alumno, u.dni
                      FROM intentos_examen i
                      JOIN examenes e ON i.examen_id = e.id
                      JOIN modulos m ON e.modulo_id = m.id
                      JOIN cursos c ON m.curso_id = c.id
                      JOIN usuarios u ON i.usuario_id = u.id
                      WHERE i.id = ? AND i.usuario_id = ? AND i.aprobado = 1");
$stmt->bind_param("ii", $intento_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Si no hay resultados o el intento no está aprobado, redirigir
    header('Location: student.php');
    exit;
}

$certificado = $result->fetch_assoc();

// Si no hay año específico, usar el año actual
if (empty($certificado['anio'])) {
    $certificado['anio'] = date('Y');
}

// Formatear el nombre del alumno (primera letra de cada palabra en mayúscula)
$nombre_alumno = ucwords(strtolower($certificado['nombre_alumno']));

// Obtener el código de validación del certificado
$stmt = $db->prepare("
    SELECT codigo_validacion 
    FROM certificados 
    WHERE usuario_id = ? AND curso_id = ?
");
$stmt->bind_param("ii", $user_id, $curso_id);
$stmt->execute();
$codigo_result = $stmt->get_result();
$codigo_validacion = '';

if ($codigo_result->num_rows > 0) {
    $codigo_validacion = $codigo_result->fetch_assoc()['codigo_validacion'];
}

// Obtener el título del curso completo
$titulo_curso = $certificado['curso_titulo'];

// Dividir el año en dos partes para mostrar como en el diseño original
$anio = $certificado['anio'];
$anio_primera_parte = substr($anio, 0, 2);
$anio_segunda_parte = substr($anio, 2, 2);

// Verificar si el DNI existe y no es 0
$mostrar_dni = !empty($certificado['dni']) && $certificado['dni'] != '0';

// Determinar el tamaño de fuente según la longitud del título
$longitud_titulo = strlen($titulo_curso);
$tamano_fuente = '24px'; // Tamaño predeterminado

if ($longitud_titulo > 100) {
    $tamano_fuente = '18px';
} elseif ($longitud_titulo > 70) {
    $tamano_fuente = '20px';
} elseif ($longitud_titulo < 40) {
    $tamano_fuente = '26px';
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificado de Finalización - RAMCC</title>
    <!-- Favicons -->
    <link rel="icon" href="./assets/img/aula-logo.ico" type="image/x-icon">
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dom-to-image/2.6.0/dom-to-image.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

        body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 40px 0;
        }

        .certificate-wrapper {
            position: relative;
            margin: 20px;
        }

        .certificate-container {
            width: 1000px;
            height: 700px;
            position: relative;
            background-color: white;
            border: 1px solid #ddd;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .certificate-background {
            position: absolute;
            top: 0;
            right: 0;
            width: 50%;
            height: 100%;
            z-index: 0;
            background-image: url('assets/img/certificate-bg.png');
            background-size: contain;
            background-position: top right;
            background-repeat: no-repeat;
            opacity: 0.3;
        }

        .certificate-content {
            position: relative;
            z-index: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 50px 20px;
            border: 2px solid #333;
            box-sizing: border-box;
        }

        .year-circle {
            position: absolute;
            top: 40px;
            left: 30px;
            width: 100px;
            height: 100px;
            border: 2px solid #333;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-color: white;
            line-height: 1;
        }

        .year-top {
            font-size: 32px;
            font-weight: normal;
        }

        .year-bottom {
            font-size: 32px;
            font-weight: bold;
        }

        .certificate-header {
            text-align: center;
            margin-top: 20px;
        }

        .certificate-title {
            font-size: 36px;
            font-weight: normal;
            margin-bottom: 15px;
            color: #333;
        }

        .course-title-container {
            width: 70%;
            margin: 0 auto;
            padding: 0 20px;
        }

        .course-title {
            font-size: <?php echo $tamano_fuente; ?>;
            line-height: 1.3;
            margin: 10px 0;
            color: #333;
            font-weight: bold;
            text-align: center;
            word-wrap: break-word;
            max-width: 100%;
        }

        .student-name-container {
            text-align: center;
            margin: 10px 0;
            border: 2px solid #333;
            border-radius: 50px;
            padding: 15px;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
            background-color: white;
        }

        .student-name {
            font-size: 36px;
            font-weight: 500;
            color: #333;
        }

        .student-dni {
            font-size: 18px;
            color: #555;
            margin-top: 5px;
        }

        .certificate-text {
            text-align: center;
            font-size: 18px;
            line-height: 1.5;
            margin-bottom: 5px;
            color: #333;
        }

        .signature-container {
            text-align: center;
            margin-top: 5px;
            margin-bottom: 5px;
        }

        .signature-image {
            width: 120px;
            height: auto;
            margin: 0 auto;
            display: block;
        }

        .signature-line {
            width: 300px;
            height: 1px;
            background-color: #333;
            margin: 5px auto;
        }

        .signature-name {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 2px;
        }

        .signature-title {
            font-size: 16px;
            color: #555;
        }

        .certificate-footer {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 10px;
            padding: 0 20px 10px;
            box-sizing: border-box;
        }

        .logo {
            height: auto;
            width: auto;
            max-width: 600px;
            object-fit: contain;
            margin: 0 20px;
        }

        .download-buttons {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            z-index: 100;
        }

        .download-button {
            padding: 10px 20px;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
        }

        .pdf-button {
            background-color: #e74c3c;
        }

        .pdf-button:hover {
            background-color: #c0392b;
        }

        .png-button {
            background-color: #3498db;
        }

        .png-button:hover {
            background-color: #2980b9;
        }

        .download-button i {
            margin-right: 8px;
        }

        @media print {
            body {
                background-color: white;
            }

            .certificate-container {
                box-shadow: none;
                border: none;
            }

            .download-buttons {
                display: none;
            }
        }

        .certificate-validation {
            font-size: 12px;
            color: #777;
            margin-top: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <div class="download-buttons">
        <button class="download-button png-button" onclick="downloadCertificate('png')">
            <i class="fas fa-image"></i> Descargar como PNG
        </button>
        <button class="download-button pdf-button" onclick="downloadCertificate('pdf')">
            <i class="fas fa-file-pdf"></i> Descargar como PDF
        </button>
    </div>

    <div class="certificate-wrapper">
        <div class="certificate-container" id="certificate">
            <div class="certificate-background"></div>
            <div class="certificate-content">
                <div class="year-circle">
                    <div class="year-top"><?php echo $anio_primera_parte; ?></div>
                    <div class="year-bottom"><?php echo $anio_segunda_parte; ?></div>
                </div>

                <div class="certificate-header">
                    <div class="certificate-title">CERTIFICADO DE FINALIZACIÓN</div>
                    <div class="course-title-container">
                        <div class="course-title"><?php echo htmlspecialchars($titulo_curso); ?></div>
                    </div>
                </div>

                <div class="student-name-container">
                    <div class="student-name"><?php echo htmlspecialchars($nombre_alumno); ?></div>
                    <?php if ($mostrar_dni): ?>
                        <div class="student-dni">DNI: <?php echo htmlspecialchars($certificado['dni']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="certificate-text">
                    Por haber participado y completado con éxito el curso de formación llevado a cabo<br>
                    <?php echo htmlspecialchars($certificado['fechas_curso']); ?>.<br>
                    Duración: <?php echo htmlspecialchars($certificado['cantidad_horas']); ?> horas
                </div>

                <div class="signature-container">
                    <img src="assets/img/firma-ricardo.png" alt="Firma" class="signature-image">
                    <div class="signature-line"></div>
                    <div class="signature-name">Ingeniero Ricardo Bertolino</div>
                    <div class="signature-title">Director ejecutivo RAMCC</div>
                </div>

                <div class="certificate-footer">
                    <img src="assets/img/logos3.png" alt="Logo RAMCC, ALPA y E-Learning" class="logo" id="logo-ramcc">
                </div>
                <?php if (!empty($codigo_validacion)): ?>
                    <div class="certificate-validation">
                        <small>Verifique este certificado en: https://aula.ramcc.net/ValidarCertificado <br> con este código de validación: <?php echo $codigo_validacion; ?></small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Función para descargar el certificado
        // Función para descargar el certificado
        function downloadCertificate(format) {
            // Ocultar los botones durante la captura
            const downloadBtns = document.querySelector('.download-buttons');
            downloadBtns.style.display = 'none';

            // Mostrar mensaje de carga
            const loadingMessage = document.createElement('div');
            loadingMessage.style.position = 'fixed';
            loadingMessage.style.top = '20px';
            loadingMessage.style.left = '50%';
            loadingMessage.style.transform = 'translateX(-50%)';
            loadingMessage.style.padding = '10px 20px';
            loadingMessage.style.backgroundColor = '#333';
            loadingMessage.style.color = 'white';
            loadingMessage.style.borderRadius = '5px';
            loadingMessage.style.zIndex = '1000';
            loadingMessage.textContent = format === 'pdf' ? 'Generando PDF...' : 'Generando imagen...';
            document.body.appendChild(loadingMessage);

            // Obtener el elemento del certificado
            const certificateElement = document.getElementById('certificate');

            // Crear un nuevo canvas con el tamaño exacto del certificado
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');

            // Establecer el tamaño del canvas
            canvas.width = certificateElement.offsetWidth;
            canvas.height = certificateElement.offsetHeight;

            // Usar html2canvas para capturar el certificado
            html2canvas(certificateElement, {
                width: 1002,
                height: 702,
                scale: 2,
                useCORS: true,
                allowTaint: true,
                backgroundColor: 'white',
                imageTimeout: 0,
                logging: true
            }).then(function(canvas) {
                // Eliminar mensaje de carga
                document.body.removeChild(loadingMessage);

                // Mostrar los botones nuevamente
                downloadBtns.style.display = 'flex';

                if (format === 'png') {
                    // Descargar como PNG
                    const link = document.createElement('a');
                    link.download = 'Certificado_<?php echo str_replace(" ", "_", $nombre_alumno); ?>.png';
                    link.href = canvas.toDataURL('image/png', 1.0);
                    link.click();
                } else if (format === 'pdf') {
                    // Descargar como PDF
                    const imgData = canvas.toDataURL('image/jpeg', 1.0);

                    // Crear un nuevo documento PDF
                    const {
                        jsPDF
                    } = window.jspdf;
                    const pdf = new jsPDF({
                        orientation: 'landscape',
                        unit: 'mm',
                        format: 'a4'
                    });

                    // Calcular las dimensiones para ajustar la imagen al PDF
                    const imgWidth = 297; // Ancho de A4 en landscape
                    const imgHeight = 210; // Alto de A4 en landscape

                    // Añadir la imagen al PDF
                    pdf.addImage(imgData, 'JPEG', 0, 0, imgWidth, imgHeight);

                    // Descargar el PDF
                    pdf.save('Certificado_<?php echo str_replace(" ", "_", $nombre_alumno); ?>.pdf');
                }
            }).catch(function(err) {
                console.error('Error al generar el certificado:', err);

                // Eliminar mensaje de carga
                document.body.removeChild(loadingMessage);

                // Mostrar los botones nuevamente
                downloadBtns.style.display = 'flex';

                alert('No se pudo generar el certificado. Por favor, intente nuevamente o contacte al administrador.');
            });
        }

        // Asegurarse de que las fuentes estén cargadas
        document.fonts.ready.then(function() {
            console.log("Todas las fuentes están cargadas");
        });

        // Asegurarse de que las fuentes estén cargadas
        document.fonts.ready.then(function() {
            console.log("Todas las fuentes están cargadas");
        });
    </script>

</body>

</html>