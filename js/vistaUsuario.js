document.addEventListener("DOMContentLoaded", async () => {
  const numero = document.querySelector(".numero");
  const porcentaje = document.querySelector(".porcentaje");
  const btnReservar = document.getElementById("btnReservar");
  const mapEl = document.getElementById("map");

  await actualizarDisponibilidad();
  setInterval(actualizarDisponibilidad, 30000);

  async function actualizarDisponibilidad() {
    try {
      const response = await fetch("../php/obtenerDisponibilidad.php");
      const data = await response.json();
      if (data.success) {
        if (numero) numero.textContent = data.disponibles;
        if (porcentaje) porcentaje.textContent = data.porcentaje + "%";
      }
    } catch (error) {
    }
  }

  if (mapEl && typeof L !== "undefined") {
    const latAttr = mapEl.getAttribute("data-lat");
    const lngAttr = mapEl.getAttribute("data-lng");
    const lat = latAttr ? parseFloat(latAttr) : 6.265;
    const lng = lngAttr ? parseFloat(lngAttr) : -75.566;
    const map = L.map("map", { zoomControl: true }).setView([lat, lng], 17);
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      attribution: "© OpenStreetMap"
    }).addTo(map);
    map.zoomControl.setPosition("topright");
    L.control.scale({ imperial: false }).addTo(map);
    const dirUrl = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;
    const popupHtml = `<strong>Parqueadero La 64</strong><br><a href="${dirUrl}" target="_blank" rel="noopener">Cómo llegar</a>`;
    L.marker([lat, lng]).addTo(map).bindPopup(popupHtml).openPopup();
  }
});
