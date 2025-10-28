document.addEventListener("DOMContentLoaded", async () => {
  // 🔹 Verificar si hay sesión activa
  try {
    const resp = await fetch("../php/obtenerSesion.php", {
      credentials: "include"
    });
    const data = await resp.json();

    if (!data.logged) {
      alert("Debes iniciar sesión para hacer una reserva.");
      window.location.href = "login.html";
      return;
    }

    document.getElementById("nombreUsuario").textContent = data.nombre;
  } catch (err) {
    console.error("Error al verificar sesión:", err);
  }

  // 🔹 Manejador de envío del formulario
  const formReserva = document.getElementById("formReserva");

  formReserva.addEventListener("submit", async (e) => {
    e.preventDefault();

    const placa = document.getElementById("placa").value.trim();
    const fechaEntrada = document.getElementById("fechaEntrada").value;
    const fechaSalida = document.getElementById("fechaSalida").value;

    if (!placa || !fechaEntrada || !fechaSalida) {
      alert("Por favor completa todos los campos.");
      return;
    }

    const formData = new FormData();
    formData.append("placa", placa);
    formData.append("fechaEntrada", fechaEntrada);
    formData.append("fechaSalida", fechaSalida);

    try {
      const response = await fetch("../php/registrarReserva.php", {
        method: "POST",
        body: formData,
        credentials: "include" // 👈 importante para mantener la sesión
      });

      const data = await response.json();

      if (data.success) {
        alert("Reserva registrada correctamente.");
        formReserva.reset();
        actualizarDisponibilidad(); // 🔁 actualizamos número de cupos
      } else {
        alert(data.message || "Error al registrar la reserva.");
      }
    } catch (error) {
      console.error("Error al enviar reserva:", error);
    }
  });

  // 🔹 Actualizar disponibilidad automáticamente
  async function actualizarDisponibilidad() {
    try {
      const resp = await fetch("../php/obtenerDisponibilidad.php");
      const data = await resp.json();

      if (data.success) {
        document.getElementById("cuposDisponibles").textContent = data.disponibles;
      }
    } catch (err) {
      console.error("Error al actualizar disponibilidad:", err);
    }
  }

  // Cargar disponibilidad al abrir la página
  actualizarDisponibilidad();
});
