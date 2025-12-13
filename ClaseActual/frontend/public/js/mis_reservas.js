document.addEventListener('DOMContentLoaded', async function () {
    const misReservasList = document.getElementById('mis-reservas-list');
    const apiUrlReservas = '../../../backend/index.php?recurso=reserva';
    const apiUrlAuth = '../../../backend/index.php?recurso=auth'; // Endpoint for auth

    // Navbar elements for dynamic display
    const userAnonLinks = document.querySelectorAll('.user-anon-link');
    const userLoggedInLink = document.querySelector('.user-logged-in-link');
    const adminLinks = document.querySelectorAll('.admin-link');
    const logoutButtonNavbar = document.getElementById('logout-button-navbar');

    // Get logged-in user info from localStorage
    const user = JSON.parse(localStorage.getItem('user'));
    const isLoggedIn = user && user.id;
    const userRole = user ? user.rol : null; // Assuming 'rol' is the string name like 'administrador'
    const loggedInUserId = user ? user.id : null; // Get actual user ID

    // Function to update navbar visibility
    function updateNavbarVisibility() {
        if (isLoggedIn) {
            userAnonLinks.forEach(link => link.style.display = 'none');
            if (userLoggedInLink) userLoggedInLink.style.display = 'block';
            if (userRole === 'administrador') { // Assuming 'administrador' is the role name for admin
                adminLinks.forEach(link => link.style.display = 'block');
            }
        } else {
            userAnonLinks.forEach(link => link.style.display = 'block');
            if (userLoggedInLink) userLoggedInLink.style.display = 'none';
            adminLinks.forEach(link => link.style.display = 'none');
        }
    }
    updateNavbarVisibility(); // Call on load

    // Adapt logout functionality to new navbar button
    if (logoutButtonNavbar) {
        logoutButtonNavbar.addEventListener('click', async function() {
            try {
                const response = await fetch(`${apiUrlAuth}&action=logout`, {
                    method: 'GET',
                    headers: { 'Content-Type': 'application/json' }
                });

                if (response.ok) {
                    alert('Sesión cerrada correctamente.');
                    localStorage.removeItem('user'); // Clear user data
                    window.location.href = 'login.html'; // Redirect
                } else {
                    const errorResult = await response.json();
                    alert(errorResult.message || 'Error al cerrar sesión.');
                }
            } catch (error) {
                console.error('Error al cerrar sesión:', error);
                alert('Ocurrió un error al intentar cerrar sesión.');
            }
        });
    }

    async function fetchMisReservas() {
        if (!loggedInUserId) {
            misReservasList.innerHTML = `<p class="text-danger text-center">No hay usuario logueado. Por favor, inicie sesión.</p>`;
            return;
        }

        try {
            const response = await fetch(`${apiUrlReservas}&id_usuario=${loggedInUserId}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                const errorResult = await response.json();
                throw new Error(errorResult.message || 'Error al obtener tus reservas.');
            }

            const reservas = await response.json();
            displayMisReservas(reservas);

        } catch (error) {
            console.error('Error fetching mis reservas:', error);
            if (misReservasList) {
                misReservasList.innerHTML = `<p class="text-danger text-center">Error al cargar tus reservas: ${error.message}</p>`;
            }
        }
    }

    function displayMisReservas(reservas) {
        if (misReservasList) {
            misReservasList.innerHTML = ''; // Limpiar lista
            if (reservas.length === 0) {
                misReservasList.innerHTML = `<p class="text-center col-12">No tienes reservas activas.</p>`;
                return;
            }

            reservas.forEach(reserva => {
                // Formatting dates
                const fechaInicio = new Date(reserva.fecha_inicio).toLocaleString();
                const fechaFin = new Date(reserva.fecha_fin).toLocaleString();

                const reservaCard = `
                    <div class="col">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Reserva #${reserva.id}</h5>
                                <p class="card-text">Espacio ID: ${reserva.id_espacio}</p>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">Inicio: ${fechaInicio}</li>
                                    <li class="list-group-item">Fin: ${fechaFin}</li>
                                    <li class="list-group-item">Estado: ${reserva.estado_reserva}</li>
                                    <li class="list-group-item">Total Pagado: $${parseFloat(reserva.total_pagado).toFixed(2)}</li>
                                    ${reserva.codigo_qr ? `<li class="list-group-item">QR Code: <a href="${reserva.codigo_qr}" target="_blank">Ver QR</a></li>` : ''}
                                </ul>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-info" onclick="modificarReserva(${reserva.id})">Modificar</button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="cancelarReserva(${reserva.id})">Cancelar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                misReservasList.innerHTML += reservaCard;
            });
        }
    }

    // Funciones placeholder para los botones
    window.modificarReserva = function(reservaId) {
        alert('Modificar reserva ID: ' + reservaId + ' (próximamente)');
        // Implementar lógica para modificar reserva
    };

    window.cancelarReserva = function(reservaId) {
        if (confirm('¿Estás seguro de que deseas cancelar la reserva ID: ' + reservaId + '?')) {
            // Implementar lógica para cancelar reserva (DELETE request)
            alert('Cancelar reserva ID: ' + reservaId + ' (próximamente)');
        }
    };

    fetchMisReservas(); // Cargar las reservas al iniciar la página
});
