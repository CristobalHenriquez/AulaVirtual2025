<section id="hero" class="hero section dark-background">
    <img src="./assets/img/aula2.jpg" alt="RAMMCC Y ALPA" data-aos="fade-in">

    <div class="container">
        <h2 data-aos="fade-up" data-aos-delay="100">Bienvenido a<br>nuestra Aula Virtual</h2>
        <p data-aos="fade-up" data-aos-delay="200">Explora una amplia variedad de cursos y amplía tus conocimientos con nosotros.</p>
        <div class="d-flex mt-4" data-aos="fade-up" data-aos-delay="300">
            <a href="Cursos" class="btn-get-started">Saber más</a>
        </div>
        <div class="logos-container col-12" data-aos="fade-up" data-aos-delay="400">
            <div class="logo-wrapper col-6">
                <img src="assets/img/ramcc.png" alt="RAMCC" class="hero-logo">
            </div>
            <div class="logo-wrapper col-6">
                <img src="assets/img/alpa.png" alt="ALPA" class="hero-logo">
            </div>
        </div>
    </div>
</section>

<style>
/* Updated styles for the logos */
.logos-container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 2rem;
    margin-top: 2rem;
    position: relative;
    z-index: 10;
}

.logo-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(255, 255, 255, 0.1);
    padding: 0.5rem 1rem;
    border-radius: 8px;
}

.hero-logo {
    height: 40px;
    width: auto;
    object-fit: contain;
    max-width: 300px; /* Prevent logos from being too wide */
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .logos-container {
        gap: 1rem;
    }

    .logo-wrapper {
        padding: 0.25rem 0.5rem;
    }

    .hero-logo {
        height: 30px;
        max-width: 120px;
    }
}
</style>
