<!-- Page Title -->
<div class="page-title" id="contacto" data-aos="fade">
    <div class="heading">
        <div class="container">
            <div class="row d-flex justify-content-center text-center">
                <div class="col-lg-8">
                    <h1>Contáctanos</h1>
                    <p class="mb-0">¿Necesitas ayuda o tienes preguntas sobre nuestra plataforma de E-Learning? Contáctanos. Estamos aquí para asistirte.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<section id="contact" class="contact section pt-0">
    <div class="mb-5" data-aos="fade-up" data-aos-delay="200">
        <iframe style="border:0; width: 100%; height: 300px;" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1991.2783388254154!2d-60.66664364923841!3d-32.926774339568695!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x95b7ab1be1e9b2d3%3A0x4761889545c7ab4f!2sRAMCC!5e0!3m2!1ses-419!2sar!4v1737563772440!5m2!1ses-419!2sar" frameborder="0" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>

    <div class="container" data-aos="fade-up" data-aos-delay="100">
        <div class="row gy-4">
            <div class="col-lg-4">
                <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="300">
                    <i class="bi bi-geo-alt flex-shrink-0"></i>
                    <div>
                        <h3>Dirección:</h3>
                        <p>Alto Buró, Puerto Norte Junín 191, S2000 Rosario, Santa Fe</p>
                    </div>
                </div>

                <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="400">
                    <i class="bi bi-telephone flex-shrink-0"></i>
                    <div>
                        <h3>Teléfono</h3>
                        <p>+54 9 341 6181694</p>
                    </div>
                </div>

                <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="500">
                    <i class="bi bi-envelope flex-shrink-0"></i>
                    <div>
                        <h3>Email</h3>
                        <p>capacitaciones@ramcc.net</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <!-- Remove the php-email-form class that's causing the error -->
                <form id="contactForm" class="contact-form" data-aos="fade-up" data-aos-delay="200">
                    <div class="row gy-4">
                        <div class="col-md-6">
                            <input type="text" name="name" class="form-control" placeholder="Nombre y apellido" required>
                        </div>

                        <div class="col-md-6">
                            <input type="email" class="form-control" name="email" placeholder="Email" required>
                        </div>

                        <div class="col-md-12">
                            <input type="text" class="form-control" name="subject" placeholder="Consulta" required>
                        </div>

                        <div class="col-md-12">
                            <textarea class="form-control" name="message" rows="6" placeholder="Mensaje" required></textarea>
                        </div>

                        <div class="col-md-12 text-center">
                            <!-- Add custom styling for these message divs -->
                            <div class="loading d-none">
                                <i class="bi bi-hourglass-split me-2"></i>Enviando mensaje...
                            </div>
                            <div class="error-message"></div>
                            <div class="sent-message d-none">
                                <i class="bi bi-check-circle me-2"></i>Su mensaje ha sido enviado. ¡Gracias!
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send me-2"></i>Enviar Mensaje
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<style>
.contact-form .loading {
    background: #ffd700;
    text-align: center;
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 15px;
}

.contact-form .error-message {
    display: none;
    color: #fff;
    background: #ed3c0d;
    text-align: center;
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 15px;
}

.contact-form .sent-message {
    color: #fff;
    background: #18d26e;
    text-align: center;
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 15px;
}
</style>

<script>
    document.getElementById('contactForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const form = this;
        const formData = new FormData(form);

        // Mostrar loading y ocultar otros mensajes
        form.querySelector('.loading').classList.remove('d-none');
        form.querySelector('.error-message').style.display = 'none';
        form.querySelector('.sent-message').classList.add('d-none');

        // Deshabilitar el botón mientras se procesa
        form.querySelector('button[type="submit"]').disabled = true;

        fetch('procesar_contacto.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Ocultar loading
                form.querySelector('.loading').classList.add('d-none');

                if (data.success) {
                    form.querySelector('.sent-message').classList.remove('d-none');
                    form.reset();
                } else {
                    form.querySelector('.error-message').style.display = 'block';
                    form.querySelector('.error-message').textContent = data.message;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                form.querySelector('.loading').classList.add('d-none');
                form.querySelector('.error-message').style.display = 'block';
                form.querySelector('.error-message').textContent = 'Ocurrió un error al enviar el mensaje. Por favor, inténtelo de nuevo.';
            })
            .finally(() => {
                // Reactivar el botón
                form.querySelector('button[type="submit"]').disabled = false;
            });
    });
</script>