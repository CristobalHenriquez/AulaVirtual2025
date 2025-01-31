<div class="page-title" data-aos="fade">
    <div class="heading">
        <div class="container">
            <div class="row d-flex justify-content-center text-center">
                <div class="col-lg-8">
                    <h1>Recuperar Contraseña</h1>
                    <p class="mb-0">Ingresa tu correo electrónico para recibir las instrucciones</p>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="forgot-password-section my-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <form id="forgotPasswordForm">
                            <div class="mb-4">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <input type="email"
                                    class="form-control"
                                    id="email"
                                    name="email"
                                    required
                                    placeholder="Ingresa tu correo electrónico">
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-envelope me-2"></i>Enviar instrucciones
                                </button>
                                <a href="InicioDeSesion" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Volver
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('procesar_forgot_password.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Correo enviado!',
                text: 'Por favor, revisa tu bandeja de entrada para continuar',
                confirmButtonText: 'Aceptar'
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