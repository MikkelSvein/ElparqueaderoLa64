// 🔹 Función para cambiar entre tabs (solo cambia el estilo visual, ambos formularios siempre visibles)
function cambiarTab(tab) {
  // Remover active de todos los tabs y formularios
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.classList.remove('active');
  });
  
  document.querySelectorAll('.form-tab').forEach(form => {
    form.classList.remove('active');
  });
  
  // Activar el tab y formulario correspondiente
  if (tab === 'login') {
    document.getElementById('formLogin').classList.add('active');
    document.getElementById('tabLogin').classList.add('active');
  } else if (tab === 'register') {
    document.getElementById('formRegister').classList.add('active');
    document.getElementById('tabRegister').classList.add('active');
  }
}

// 🔹 Verificar si viene de register.html para mostrar tab de registro
document.addEventListener("DOMContentLoaded", () => {
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('tab') === 'register') {
    cambiarTab('register');
  }
  
  // Inicializar funcionalidad de login
  const formLogin = document.getElementById("formLogin");
  if (formLogin) {
    formLogin.addEventListener("submit", async (e) => {
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
          credentials: "include",
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
  }

  // Inicializar funcionalidad de registro
  const formRegister = document.getElementById("formRegister");
  if (formRegister) {
    formRegister.addEventListener("submit", async (e) => {
      e.preventDefault();

      const nombre = document.getElementById("nombre-register").value.trim();
      const correo = document.getElementById("correo-register").value.trim();
      const contrasena = document.getElementById("contrasena-register").value.trim();

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
          cambiarTab('login');
          // Limpiar formulario de registro
          formRegister.reset();
        } else {
          alert("⚠️ " + data.message);
        }

      } catch (error) {
        console.error("Error:", error);
        alert("Error en la conexión con el servidor.");
      }
    });
  }

  // Inicializar botón de Google (si existe)
  const btnGoogle = document.getElementById("btnGoogle");
  if (btnGoogle) {
    btnGoogle.addEventListener("click", () => {
      // Funcionalidad de Google (puede implementarse después)
      alert("Inicio con Google aún no está disponible.");
    });
  }
});

