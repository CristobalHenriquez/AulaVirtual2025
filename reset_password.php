<?php
require_once 'includes/conexion.php';

// Verificar token
$token = $_GET['token'] ?? '';
$tokenValido = false;

if ($token) {
    $sql = "SELECT id FROM usuarios WHERE password_reset_token = ? AND password_reset_expiration > NOW()";
    if ($stmt = $db->prepare($sql)) {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $tokenValido = $result->num_rows === 1;
        $stmt->close();
    }
}

include_once 'includes/head.php';
?>

<body class="index-page">
    <?php include_once 'includes/header.php'; ?>

    <main class="main">
        <div class="page-title" data-aos="fade">
            <div class="heading">
                <div class="container">
                    <div class="row d-flex justify-content-center text-center">
                        <div class="col-lg-8">
                            <h1>Restablecer Contraseña</h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <section class="reset-password-section my-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-6 col-lg-5">
                        <?php if ($tokenValido): ?>
                            <div class="card shadow-sm">
                                <div class="card-body p-4">
                                    <form id="resetPasswordForm">
                                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                                        <div class="mb-4">
                                            <label for="password" class="form-label">Nueva Contraseña</label>
                                            <input type="password"
                                                class="form-control"
                                                id="password"
                                                name="password"
                                                required
                                                placeholder="Ingresa tu nueva contraseña">
                                        </div>
                                        <div class="mb-4">
                                            <label for="password_confirm" class="form-label">Confirmar Contraseña</label>
                                            <input type="password"
                                                class="form-control"
                                                id="password_confirm"
                                                name="password_confirm"
                                                required
                                                placeholder="Confirma tu nueva contraseña">
                                        </div>
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary">Cambiar contraseña</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger text-center">
                                El enlace para restablecer la contraseña no es válido o ha expirado.
                                <br><br>
                                <a href="forgot_password.php" class="btn btn-primary">Solicitar nuevo enlace</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php 
    include_once 'includes/footer.php';
    include_once 'includes/scripts.php';
    ?>

    <?php if ($tokenValido): ?>
    <script>
    document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const password = document.getElementById('password').value;
        const passwordConfirm = document.getElementById('password_confirm').value;
        
        if (password !== passwordConfirm) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Las contraseñas no coinciden',
                confirmButtonText: 'Intentar nuevamente'
            });
            return;
        }
        
        const formData = new FormData(this);
        
        fetch('procesar_reset_password.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Contraseña actualizada!',
                    text: 'Tu contraseña ha sido cambiada exitosamente',
                    confirmButtonText: 'Iniciar sesión'
                }).then(() => {
                    window.location.href = 'InicioDeSesion';
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message,
                    confirmButtonText: 'Intentar nuevamente'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error al procesar la solicitud',
                confirmButtonText: 'Intentar nuevamente'
            });
        });
    });
    </script>
    <?php endif; ?>
</body>
</html>