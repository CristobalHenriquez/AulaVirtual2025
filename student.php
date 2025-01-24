<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

// Incluir conexión
include_once 'includes/head.php';

// Obtener datos del usuario
$user_id = $_SESSION['id'];
$stmt = $db->prepare("SELECT nombre, apellidos, email, municipio FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Obtener cursos inscritos agrupados por año
$sql = "SELECT c.*, DATE_FORMAT(c.created_at, '%Y') as anio
        FROM cursos c
        INNER JOIN inscripciones i ON c.id = i.curso_id
        WHERE i.usuario_id = ?
        ORDER BY c.anio DESC, c.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Agrupar cursos por año
$cursos_por_anio = [];
while ($curso = $result->fetch_assoc()) {
    $cursos_por_anio[$curso['anio']][] = $curso;
}

// Función para obtener módulos de un curso
function obtenerModulos($db, $curso_id)
{
    $stmt = $db->prepare("SELECT * FROM modulos WHERE curso_id = ? ORDER BY created_at");
    $stmt->bind_param("i", $curso_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>


<body class="" style="background-image: url('assets/img/student-bg.jpg'); background-size: cover;">

    <?php
    //INCLUYO HEADER
    include_once 'includes/header.php';
    ?>
    <main>
        <?php
        include_once 'templates/student-main.php';
        ?>
    </main>
    <!-- FOOTER Y SCRIPTS -->
    <?php
    include_once 'includes/footer.php';
    include_once 'includes/scripts.php';
    ?>
    

</body>

</html>