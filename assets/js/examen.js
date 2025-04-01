// Este archivo contiene funciones auxiliares para la funcionalidad del examen
document.addEventListener("DOMContentLoaded", () => {
  // Función para confirmar antes de abandonar la página durante un examen
  if (document.querySelector(".timer-container")) {
    window.addEventListener("beforeunload", (e) => {
      // Cancelar el evento según lo permita el navegador
      e.preventDefault()
      // Chrome requiere returnValue
      e.returnValue = "¿Estás seguro de que deseas salir? Tu progreso podría perderse."
      // Mensaje para otros navegadores
      return "¿Estás seguro de que deseas salir? Tu progreso podría perderse."
    })
  }
})
