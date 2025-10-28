document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("formRegister");

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const nombre = document.getElementById("nombre").value.trim();
    const correo = document.getElementById("correo").value.trim();
    const contrasena = document.getElementById("contrasena").value.trim();

    if (!nombre || !correo || !contrasena) {
      alert("Por favor, complete todos los campos.");
      return;
    }

    try {
      const response = await fetch("../php/registrarUsuario.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
          nombre,
          correo,
          contrasena
        }),
      });

      const data = await response.json();

      if (data.status === "ok") {
        alert("✅ Registro exitoso. Ahora puede iniciar sesión.");
        window.location.href = "login.html";
      } else {
        alert("⚠️ " + data.message);
      }

    } catch (error) {
      console.error("Error:", error);
      alert("Error en la conexión con el servidor.");
    }
  });
});
