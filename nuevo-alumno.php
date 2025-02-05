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
        include_once 'templates/nuevo-alumno-main.php';
        ?>
    </main>
    <!-- FOOTER Y SCRIPTS -->
    <?php
    include_once 'includes/footer.php';
    include_once 'includes/scripts.php';
    ?>

    <!-- Agregar script de SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Script para manejar las notificaciones -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php
        if (isset($_SESSION['swal_success'])) {
            echo "Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: '" . addslashes($_SESSION['swal_success']) . "',
                showConfirmButton: false,
                timer: 1500
            });";
            unset($_SESSION['swal_success']);
        }

        if (isset($_SESSION['swal_error'])) {
            echo "Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '" . addslashes($_SESSION['swal_error']) . "',
                showConfirmButton: true
            });";
            unset($_SESSION['swal_error']);
        }
        ?>
    });
    </script>

</body>
</html>