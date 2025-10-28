document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("formLogin");

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const correo = document.getElementById("correo").value.trim();
    const contrasena = document.getElementById("contrasena").value.trim();
    const rol = document.getElementById("rol").value.trim();

    if (!correo || !contrasena || !rol) {
      alert("Por favor, complete todos los campos.");
      return;
    }

    try {
      const response = await fetch("../php/login.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        credentials: "include", // 👈 NECESARIO
        body: new URLSearchParams({ correo, contrasena, rol }),
      });

      const data = await response.json();

      if (data.success) {
        if (data.rol === "admin") {
          window.location.href = "vistaAdmin.html";
        } else {
          window.location.href = "vistaUsuario.html";
        }
      } else {
        alert("⚠️ " + data.message);
      }
    } catch (error) {
      console.error("Error:", error);
      alert("Error en la conexión con el servidor.");
    }
  });
});
