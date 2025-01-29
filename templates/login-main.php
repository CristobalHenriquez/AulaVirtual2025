<div class="page-title" id="contacto" data-aos="fade">
    <div class="heading">
        <div class="container">
            <div class="row d-flex justify-content-center text-center">
                <div class="col-lg-8">
                    <h1>Iniciar Sesión</h1>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="login-section my-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <form id="loginForm">
                            <div class="mb-4">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <input type="email"
                                    class="form-control"
                                    id="email"
                                    name="email"
                                    required
                                    placeholder="Ingresa tu Email">
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password"
                                    class="form-control"
                                    id="password"
                                    name="password"
                                    required
                                    placeholder="Ingresa tu contraseña">
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Iniciar sesión</button>
                                <button type="button" class="btn btn-secondary" onclick="window.location.href='Inicio'">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('procesar_login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Bienvenido!',
                text: 'Iniciando sesión...',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.href = data.redirect;
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