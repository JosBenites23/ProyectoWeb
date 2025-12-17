document.addEventListener('DOMContentLoaded', async function () {
    const loginForm = document.getElementById('login-form');
    // URL del controlador de autenticación
    const apiUrl = '../../../backend/index.php?recurso=auth';

    if (loginForm) {
        loginForm.addEventListener('submit', async function (event) {
            event.preventDefault(); // Prevenir el envío por defecto del formulario

            const formData = new FormData(loginForm);
            const userData = {
                action: 'login' // Indicar al backend que es una acción de login
            };
            for (let [key, value] of formData.entries()) {
                userData[key] = value;
            }

            try {
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(userData)
                });

                const result = await response.json();

                if (response.ok) {
                    alert(result.message || 'Inicio de sesión exitoso.');
                    // Almacenar datos del usuario en localStorage
                    localStorage.setItem('user', JSON.stringify(result.user));
                    // Redirigir al usuario a una página principal o dashboard
                    window.location.href = 'espacios.html'; // Redirigir a la página de usuarios como ejemplo
                } else {
                    alert(result.message || 'Error al iniciar sesión. Verifique sus credenciales.');
                }
            } catch (error) {
                console.error('Error de red o del servidor:', error);
                alert('Ocurrió un error al intentar iniciar sesión.');
            }
        });
    }
});