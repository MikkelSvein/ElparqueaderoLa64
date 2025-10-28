document.addEventListener("DOMContentLoaded", async () => {
  const numero = document.querySelector(".numero");
  const porcentaje = document.querySelector(".porcentaje");
  const btnReservar = document.getElementById("btnReservar");

  // 🟢 Mostrar disponibilidad al cargar
  await actualizarDisponibilidad();
    // 🔄 Actualizar disponibilidad cada 30 segundo
    setInterval(actualizarDisponibilidad, 30000);

  // 🔄 Función para actualizar la disponibilidad
  async function actualizarDisponibilidad() {
    try {
      const response = await fetch("../php/obtenerDisponibilidad.php");
      const data = await response.json();
      if (data.success) {
        numero.textContent = data.disponibles;
        porcentaje.textContent = data.porcentaje + "%";
      }
    } catch (error) {
      console.error("Error actualizando disponibilidad:", error);
    }
  }
});
