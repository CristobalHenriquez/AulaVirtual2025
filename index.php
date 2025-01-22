<?php
//INCLUDE HEAD
include_once 'includes/head.php';
?>

<body class="index-page">

<?php
include_once 'includes/header.php';
?>

  <main class="main">

    <!-- INCLUYO SECCIONES -->
    <?php
    //INCLUYO HERO
    include_once 'templates/hero.php';
    //INCLUYO SOBRE NOSOTROS
    include_once 'templates/about.php';
    //INCLUYO CONTADOR
    include_once 'templates/contador.php';
    //INCLUYO CURSOS
    include_once 'templates/cursos-home.php';
    //INCLUYO CONTACTO
    include_once 'includes/contacto.php';
    ?>

  </main>

  <!-- FOOTER -->
  <?php
  include_once 'includes/footer.php';
  ?>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>

  <!-- Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html>