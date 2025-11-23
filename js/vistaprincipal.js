document.addEventListener("DOMContentLoaded", async () => {
    console.log("Cargando vista principal...");
    try {
        const s = await fetch("../php/obtenerSesion.php", { credentials: "include" });
        const d = await s.json();
        if (!d.logged || d.rol !== "admin") {
            window.location.href = "login.html";
            return;
        }
    } catch (e) {}
    cargarVehiculos();
    cargarTarifas();
    cargarReservasAdmin();
    cargarDisponibilidad();
    cargarMensualidades();

    const formRegistro = document.getElementById("formRegistro");
    let isSubmitting = false; // Flag para evitar múltiples submits
    
    if (formRegistro) {
        formRegistro.addEventListener("submit", e => {
            e.preventDefault();
            
            // Prevenir múltiples submits
            if (isSubmitting) {
                return;
            }
            
            isSubmitting = true;
            const submitButton = formRegistro.querySelector('button[type="submit"]');
            const originalText = submitButton ? submitButton.textContent : '';
            
            // Deshabilitar botón mientras se procesa
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Registrando...';
            }
            
            const data = new FormData(formRegistro);
            fetch("../php/registrar.php", { method: "POST", body: data })
                .then(res => res.text())
                .then(resp => {
                    alert(resp);
                    if (resp.includes("éxito") || resp.includes("exitosamente")) {
                        formRegistro.reset(); // Limpiar formulario solo si fue exitoso
                    }
                    cargarVehiculos();
                    cargarDisponibilidad();
                })
                .catch(err => {
                    console.error("Error:", err);
                    alert("Error al registrar el vehículo.");
                })
                .finally(() => {
                    isSubmitting = false;
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.textContent = originalText;
                    }
                });
        });
    }

    document.getElementById("btnGuardarTarifas").addEventListener("click", function(e){
    e.preventDefault();
    const datos = new FormData();
    datos.append("tarifaBicicleta", document.getElementById("tarifaBicicleta").value || 0);
    datos.append("tarifaBus",       document.getElementById("tarifaBus").value || 0);
    datos.append("tarifaCarro",     document.getElementById("tarifaCarro").value || 0);
    datos.append("tarifaMoto",      document.getElementById("tarifaMoto").value || 0);

    fetch("../php/tarifas.php", { method: "POST", body: datos })
        .then(r => r.text())
        .then(txt => { alert(txt); cargarTarifas(); })
        .catch(err => { console.error(err); alert('Error guardando tarifas'); });
});



    document.getElementById("btnBuscar").addEventListener("click", () => {
        const q = document.getElementById("buscar").value.trim();
        if (!q) {
            alert("Por favor ingrese un término de búsqueda (placa o nombre).");
            return;
        }
        
        // Mostrar mensaje de carga
        document.getElementById("listaVehiculos").innerHTML = '<p style="color: #888; text-align: center;">Buscando...</p>';
        
        fetch("../php/buscar.php?q=" + encodeURIComponent(q))
            .then(res => res.text())
            .then(html => {
                document.getElementById("listaVehiculos").innerHTML = html;
            })
            .catch(err => {
                console.error("Error en búsqueda:", err);
                document.getElementById("listaVehiculos").innerHTML = '<p style="color: #f44; text-align: center;">Error al realizar la búsqueda.</p>';
            });
    });
    
    // Permitir búsqueda con Enter
    document.getElementById("buscar").addEventListener("keypress", (e) => {
        if (e.key === "Enter") {
            e.preventDefault();
            document.getElementById("btnBuscar").click();
        }
    });
});

function cargarVehiculos(pagina = 1) {
    fetch("../php/listar.php?pagina=" + pagina)
        .then(res => res.text())
        .then(html => document.getElementById("listaVehiculos").innerHTML = html);
}

function cargarTarifas() {
    fetch("../php/tarifas.php")
        .then(res => res.json())
        .then(t => {
            document.getElementById("tarifaBicicleta").value = t.bicicleta || "";
            document.getElementById("tarifaBus").value = t.bus || "";
            document.getElementById("tarifaCarro").value = t.carro || "";
            document.getElementById("tarifaMoto").value = t.moto || "";
        });
}

// 🔹 Función para cargar todas las reservas (admin)
async function cargarReservasAdmin() {
    const listaReservas = document.getElementById("listaReservasAdmin");
    if (!listaReservas) return;

    try {
        listaReservas.innerHTML = '<p style="color: #888; text-align: center;">Cargando reservas...</p>';

        const response = await fetch("../php/listarReservasAdmin.php", {
            credentials: "include"
        });

        const data = await response.json();

        if (data.status === "error") {
            listaReservas.innerHTML = `<p style="color: #f44; text-align: center;">${data.message}</p>`;
            return;
        }

        if (!data.reservas || data.reservas.length === 0) {
            listaReservas.innerHTML = '<p style="color: #888; text-align: center;">No hay reservas registradas.</p>';
            return;
        }

        // Crear tabla de reservas
        let html = `
            <table class="tabla-reservas-admin" style="width:100%; border-collapse:collapse; margin-top:10px;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
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
                    <td>${reserva.id}</td>
                    <td>${reserva.nombre_usuario || 'N/A'}</td>
                    <td>${reserva.placa}</td>
                    <td>${fechaEntrada}</td>
                    <td>${fechaSalida}</td>
                    <td>${fechaRegistro}</td>
                    <td>
                        <button class="btn-eliminar-admin" onclick="eliminarReservaAdmin(${reserva.id})" title="Eliminar reserva">
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

// 🔹 Función para eliminar una reserva (admin)
window.eliminarReservaAdmin = async function(reservaId) {
    if (!confirm("¿Estás seguro de que deseas eliminar esta reserva?")) {
        return;
    }

    try {
        const response = await fetch("../php/eliminarReservaAdmin.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({ reserva_id: reservaId }),
            credentials: "include"
        });

        const result = await response.json();
        alert(result.message || result.status);

        if (result.status === "ok") {
            await cargarReservasAdmin(); // Recargar lista de reservas
        }
    } catch (error) {
        console.error("Error al eliminar reserva:", error);
        alert("Error al eliminar la reserva.");
    }
}

// 🔹 Función para registrar salida de un vehículo
window.registrarSalida = async function(vehiculoId) {
    if (!confirm("¿Registrar salida de este vehículo?")) {
        return;
    }

    try {
        const fechaSalida = new Date().toISOString().slice(0, 16); // Formato datetime-local
        const fechaSalidaConfirm = prompt("Ingrese la fecha y hora de salida (o deje vacío para usar la actual):", fechaSalida);
        
        const formData = new URLSearchParams({
            vehiculo_id: vehiculoId,
            fecha_salida: fechaSalidaConfirm || new Date().toISOString().slice(0, 19).replace('T', ' ')
        });

        const response = await fetch("../php/registrarSalida.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: formData,
            credentials: "include"
        });

        const result = await response.json();

        if (result.status === "ok") {
            let mensaje = `✅ Salida registrada correctamente.\n\n`;
            mensaje += `Vehículo: ${result.vehiculo.placa} - ${result.vehiculo.nombre}\n`;
            mensaje += `Tipo: ${result.vehiculo.tipo}\n`;
            mensaje += `Horas: ${result.cobro.horas}\n`;
            mensaje += `Tarifa por hora: $${result.cobro.tarifa_por_hora.toLocaleString()}\n`;
            mensaje += `TOTAL A COBRAR: $${result.cobro.total.toLocaleString()}`;
            
            alert(mensaje);
            cargarVehiculos(); // Recargar lista de vehículos
            cargarDisponibilidad();
        } else {
            alert(result.message || "Error al registrar la salida.");
        }
    } catch (error) {
        console.error("Error al registrar salida:", error);
        alert("Error al registrar la salida.");
    }
}

// 🔹 Cargar disponibilidad de cupos
async function cargarDisponibilidad() {
    const cont = document.getElementById("estadoDisponibilidad");
    if (!cont) return;
    try {
        cont.textContent = "Cargando disponibilidad...";
        const res = await fetch("../php/obtenerDisponibilidad.php", { credentials: "include" });
        const data = await res.json();
        if (!data.success) {
            cont.textContent = "Error al obtener disponibilidad";
            return;
        }
        cont.textContent = `Cupos disponibles: ${data.disponibles} (${data.porcentaje}%)`;
    } catch (e) {
        cont.textContent = "Error al obtener disponibilidad";
    }
}

// 🔹 Cargar mensualidades
async function cargarMensualidades() {
    const cont = document.getElementById("listaMensualidades");
    if (!cont) return;
    try {
        cont.innerHTML = '<p style="color: #888; text-align: center;">Cargando mensualidades...</p>';
        const res = await fetch("../php/listarMensualidades.php", { credentials: "include" });
        const data = await res.json();
        if (data.status === "error") {
            cont.innerHTML = `<p style=\"color: #f44; text-align: center;\">${data.message}</p>`;
            return;
        }
        if (!data.mensualidades || data.mensualidades.length === 0) {
            cont.innerHTML = '<p style="color: #888; text-align: center;">No hay mensualidades registradas.</p>';
            return;
        }
        let html = `
            <table class=\"tabla-mensualidades\">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Placa</th>
                        <th>Tipo</th>
                        <th>Precio</th>
                        <th>Método</th>
                        <th>Código</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
        `;
        data.mensualidades.forEach(m => {
            const precioFmt = `$${Number(m.precio || 0).toLocaleString()}`;
            html += `
                <tr>
                    <td>${m.id}</td>
                    <td>${m.nombre_usuario || 'N/A'}</td>
                    <td>${m.placa}</td>
                    <td>${m.tipo_vehiculo}</td>
                    <td>${precioFmt}</td>
                    <td>${m.metodo_pago}</td>
                    <td>${m.codigo_referencia}</td>
                    <td>${m.fecha_inicio}</td>
                    <td>${m.fecha_fin}</td>
                    <td>${m.estado}</td>
                </tr>
            `;
        });
        html += `
                </tbody>
            </table>
        `;
        cont.innerHTML = html;
    } catch (e) {
        cont.innerHTML = '<p style="color: #f44; text-align: center;">Error al cargar mensualidades.</p>';
    }
}
