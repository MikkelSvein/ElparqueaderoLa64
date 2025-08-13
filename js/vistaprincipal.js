document.addEventListener("DOMContentLoaded", () => {
    console.log("Cargando vista principal...");
    cargarVehiculos();
    cargarTarifas();

    document.getElementById("formRegistro").addEventListener("submit", e => {
        e.preventDefault();
        const data = new FormData(e.target);
        fetch("../php/registrar.php", { method: "POST", body: data })
            .then(res => res.text())
            .then(resp => {
                alert(resp);
                cargarVehiculos();
            });
    });

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
        const q = document.getElementById("buscar").value;
        fetch("../php/buscar.php?q=" + encodeURIComponent(q))
            .then(res => res.text())
            .then(html => document.getElementById("listaVehiculos").innerHTML = html);
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
