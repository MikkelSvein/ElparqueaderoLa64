document.getElementById('formLogin').addEventListener('submit', async function (e) {
  e.preventDefault();

  const correo = document.getElementById('correo').value.trim();
  const contrasena = document.getElementById('contrasena').value.trim();
  const rol = document.getElementById('rol').value;

  if (!correo || !contrasena || !rol) {
    alert("Por favor, complete todos los campos.");
    return;
  }

  const formData = new FormData();
  formData.append('correo', correo);
  formData.append('contrasena', contrasena);
  formData.append('rol', rol);

  try {
    const response = await fetch('../php/login.php', {
      method: 'POST',
      body: formData
    });

    const result = await response.json();

    if (result.success) {
      // ✅ Redirección según el rol
      if (rol === 'usuario') {
        window.location.href = '../html/vistaUsuario.html';
      } else if (rol === 'admin') {
        window.location.href = '../html/vistaAdmin.html';
      }
    } else {
      alert(result.message || 'Credenciales incorrectas');
    }
  } catch (error) {
    console.error(error);
    alert('Error de conexión con el servidor.');
  }
});


