document.addEventListener("DOMContentLoaded", async () => {
  // 🔹 Verificar sesión y cargar reservas
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

    // Cargar reservas al iniciar
    await cargarReservas();
  } catch (err) {
    console.error("Error al verificar sesión:", err);
  }

  // 🔹 Manejador de envío del formulario de reserva
  const formReserva = document.getElementById("formReserva");
  if (formReserva) {
    formReserva.addEventListener("submit", async (e) => {
      e.preventDefault();

      const placaInput = document.getElementById("placa");
      const fechaEntradaInput = document.getElementById("fecha_entrada");
      const fechaSalidaInput = document.getElementById("fecha_salida");

      if (!placaInput || !fechaEntradaInput || !fechaSalidaInput) {
        alert("Error: No se encontraron los campos del formulario.");
        return;
      }

      const placa = placaInput.value.trim();
      const fecha_entrada = fechaEntradaInput.value;
      const fecha_salida = fechaSalidaInput.value;

      if (!placa || !fecha_entrada || !fecha_salida) {
        alert("Por favor completa todos los campos.");
        return;
      }

      // Validar que fecha de entrada sea menor que fecha de salida
      if (new Date(fecha_entrada) >= new Date(fecha_salida)) {
        alert("La fecha de entrada debe ser anterior a la fecha de salida.");
        return;
      }

      try {
        const response = await fetch("../php/registrarReserva.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: new URLSearchParams({ placa, fecha_entrada, fecha_salida }),
          credentials: "include"
        });

        const result = await response.json();
        alert(result.message || result.status);
        
        if (result.status === "ok") {
          formReserva.reset();
          await cargarReservas(); // Recargar lista de reservas
        }
      } catch (error) {
        console.error("Error al enviar reserva:", error);
        alert("Error al registrar la reserva.");
      }
    });
  }
});

// 🔹 Función para cargar las reservas del usuario
async function cargarReservas() {
  const listaReservas = document.getElementById("listaReservas");
  if (!listaReservas) return;

  try {
    listaReservas.innerHTML = '<p style="color: #888; text-align: center;">Cargando reservas...</p>';

    const response = await fetch("../php/listarReservas.php", {
      credentials: "include"
    });

    const data = await response.json();

    if (data.status === "error") {
      listaReservas.innerHTML = `<p style="color: #f44; text-align: center;">${data.message}</p>`;
      return;
    }

    if (!data.reservas || data.reservas.length === 0) {
      listaReservas.innerHTML = '<p style="color: #888; text-align: center;">No tienes reservas registradas.</p>';
      return;
    }

    // Crear tabla de reservas
    let html = `
      <table class="tabla-reservas">
        <thead>
          <tr>
            <th>Placa</th>
            <th>Fecha Entrada</th>
            <th>Fecha Salida</th>
            <th>Fecha Registro</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
    `;

    data.reservas.forEach(reserva => {
      const fechaEntrada = new Date(reserva.fecha_entrada).toLocaleString('es-ES');
      const fechaSalida = new Date(reserva.fecha_salida).toLocaleString('es-ES');
      const fechaRegistro = new Date(reserva.fecha_registro).toLocaleString('es-ES');

      html += `
        <tr>
          <td>${reserva.placa}</td>
          <td>${fechaEntrada}</td>
          <td>${fechaSalida}</td>
          <td>${fechaRegistro}</td>
          <td>
            <button class="btn-eliminar" onclick="eliminarReserva(${reserva.id})" title="Eliminar reserva">
              🗑️ Eliminar
            </button>
          </td>
        </tr>
      `;
    });

    html += `
        </tbody>
      </table>
    `;

    listaReservas.innerHTML = html;
  } catch (error) {
    console.error("Error al cargar reservas:", error);
    listaReservas.innerHTML = '<p style="color: #f44; text-align: center;">Error al cargar las reservas.</p>';
  }
}

// 🔹 Función para eliminar una reserva (disponible globalmente)
window.eliminarReserva = async function(reservaId) {
  if (!confirm("¿Estás seguro de que deseas eliminar esta reserva?")) {
    return;
  }

  try {
    const response = await fetch("../php/eliminarReserva.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({ reserva_id: reservaId }),
      credentials: "include"
    });

    const result = await response.json();
    alert(result.message || result.status);

    if (result.status === "ok") {
      await cargarReservas(); // Recargar lista de reservas
    }
  } catch (error) {
    console.error("Error al eliminar reserva:", error);
    alert("Error al eliminar la reserva.");
  }
}
