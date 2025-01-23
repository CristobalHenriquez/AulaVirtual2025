<?php
//INCLUDE HEAD
include_once 'includes/head.php';
?>

<body class="index-page">

<?php
//INCLUYO HEADER
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

  <!-- FOOTER Y SCRIPTS -->
  <?php
  include_once 'includes/footer.php';
  include_once 'includes/scripts.php';
  ?>

</body>

</html>