let vehiculoSeleccionado = null;
let precioSeleccionado = 0;

// 🔹 Cargar precios de mensualidad
async function cargarPreciosMensualidad() {
  try {
    const response = await fetch("../php/tarifas.php");
    const data = await response.json();

    // Calcular precio mensual (tarifa por hora * 24 horas * 30 días * factor mensual 0.7)
    // Factor 0.7 = 30% de descuento por mensualidad
    const factorMensual = 0.7;
    const horasPorMes = 24 * 30;

    const precioCarro = Math.round(data.carro * horasPorMes * factorMensual);
    const precioMoto = Math.round(data.moto * horasPorMes * factorMensual);
    const precioBicicleta = Math.round(data.bicicleta * horasPorMes * factorMensual);
    const precioBus = Math.round(data.bus * horasPorMes * factorMensual);

    document.getElementById("precio-carro").textContent = `$${precioCarro.toLocaleString()}/mes`;
    document.getElementById("precio-moto").textContent = `$${precioMoto.toLocaleString()}/mes`;
    document.getElementById("precio-bicicleta").textContent = `$${precioBicicleta.toLocaleString()}/mes`;
    document.getElementById("precio-bus").textContent = `$${precioBus.toLocaleString()}/mes`;
  } catch (error) {
    console.error("Error cargando precios:", error);
    // Valores por defecto si falla
    document.getElementById("precio-carro").textContent = "$0/mes";
    document.getElementById("precio-moto").textContent = "$0/mes";
    document.getElementById("precio-bicicleta").textContent = "$0/mes";
    document.getElementById("precio-bus").textContent = "$0/mes";
  }
}

// 🔹 Seleccionar vehículo
function seleccionarVehiculo(tipo) {
  vehiculoSeleccionado = tipo;
  
  // Remover selección anterior
  document.querySelectorAll('.vehiculo-card').forEach(card => {
    card.classList.remove('selected');
  });
  
  // Agregar selección actual
  const card = document.querySelector(`[data-tipo="${tipo}"]`);
  if (card) {
    card.classList.add('selected');
  }

  // Obtener precio
  const precioText = card.querySelector('.precio').textContent;
  precioSeleccionado = parseInt(precioText.replace(/[^0-9]/g, '')) || 0;

  // Abrir modal de pago
  abrirModal();
}

// 🔹 Abrir modal
function abrirModal() {
  if (!vehiculoSeleccionado) {
    alert("Por favor seleccione un tipo de vehículo.");
    return;
  }

  const modal = document.getElementById("modalPago");
  const resumenTipo = document.getElementById("resumen-tipo");
  const resumenPrecio = document.getElementById("resumen-precio");

  resumenTipo.textContent = vehiculoSeleccionado;
  resumenPrecio.textContent = `$${precioSeleccionado.toLocaleString()}/mes`;

  // Generar código de pago único
  generarCodigoPago();

  modal.style.display = "block";
  
  // Resetear formulario
  document.getElementById("formPago").reset();
  const efectivoRadio = document.querySelector('input[name="metodo"][value="efectivo"]');
  if (efectivoRadio) {
    efectivoRadio.checked = true;
  }
  mostrarFormularioMetodo('efectivo');
}

// 🔹 Cerrar modal
function cerrarModal() {
  document.getElementById("modalPago").style.display = "none";
  vehiculoSeleccionado = null;
  precioSeleccionado = 0;
}

// 🔹 Generar código de pago único
function generarCodigoPago() {
  const codigo = 'MEN-' + Date.now().toString().slice(-8) + '-' + Math.random().toString(36).substr(2, 4).toUpperCase();
  document.getElementById("codigo-pago").textContent = codigo;
}

// 🔹 Manejar cambio de método de pago
document.addEventListener("DOMContentLoaded", () => {
  const metodosPago = document.querySelectorAll('input[name="metodo"]');
  
  metodosPago.forEach(metodo => {
    metodo.addEventListener('change', (e) => {
      mostrarFormularioMetodo(e.target.value);
    });
  });

  // Manejar envío del formulario
  const formPago = document.getElementById("formPago");
  if (formPago) {
    formPago.addEventListener("submit", async (e) => {
      e.preventDefault();
      
      const metodo = document.querySelector('input[name="metodo"]:checked').value;
      const placa = document.getElementById("placa-vehiculo").value.trim();
      const telefono = document.getElementById("telefono").value.trim();

      if (!placa || !telefono) {
        alert("Por favor complete todos los campos.");
        return;
      }

      // Validar método de pago
      if (metodo === 'nequi' || metodo === 'tarjeta') {
        alert("⚠️ Este método de pago no está disponible aún. Por favor seleccione 'Efectivo'.");
        return;
      }

      // Procesar pago
      await procesarPago(metodo, placa, telefono);
    });
  }
});

// 🔹 Mostrar formulario según método de pago
function mostrarFormularioMetodo(metodo) {
  const formularioEfectivo = document.getElementById("formulario-efectivo");
  
  if (metodo === 'efectivo') {
    formularioEfectivo.style.display = 'block';
  } else {
    formularioEfectivo.style.display = 'none';
  }
}

// 🔹 Procesar pago
async function procesarPago(metodo, placa, telefono) {
  const btnPagar = document.querySelector('.btn-pagar');
  btnPagar.disabled = true;
  btnPagar.textContent = 'Procesando...';

  try {
    const formData = new URLSearchParams({
      tipo_vehiculo: vehiculoSeleccionado,
      metodo_pago: metodo,
      placa: placa,
      telefono: telefono,
      precio: precioSeleccionado
    });

    const response = await fetch("../php/procesarMensualidad.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: formData,
      credentials: "include"
    });

    const result = await response.json();

    if (result.status === "ok") {
      alert("✅ " + result.message);
      cerrarModal();
      // Opcional: redirigir o mostrar mensaje de éxito
    } else {
      alert("⚠️ " + result.message);
    }
  } catch (error) {
    console.error("Error procesando pago:", error);
    alert("Error al procesar el pago. Por favor intente nuevamente.");
  } finally {
    btnPagar.disabled = false;
    btnPagar.textContent = 'Procesar Pago';
  }
}

// 🔹 Cerrar modal al hacer clic fuera
window.onclick = function(event) {
  const modal = document.getElementById("modalPago");
  if (event.target == modal) {
    cerrarModal();
  }
}

