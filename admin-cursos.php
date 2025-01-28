<?php
//VERIFICO ROL
session_start();
require_once 'includes/auth.php';
verificarRolAdmin();
//INCLUYO HEAD
include_once 'includes/head.php';
?>

<body class="bg-light">
    <?php
    //INCLUYO HEADER
    include_once 'includes/header.php';
    ?>
    <main>
        <?php
        include_once 'templates/admin-cursos-main.php';
        ?>
    </main>
    <!-- FOOTER Y SCRIPTS -->
    <?php
    include_once 'includes/footer.php';
    include_once 'includes/scripts.php';
    ?>
</body>

</html>