document.addEventListener('DOMContentLoaded', async function () {
    const espaciosList = document.getElementById('espacios-list');
    const apiUrlEspacios = '../../../backend/index.php?recurso=espacio'; // Endpoint para obtener espacios
    const apiUrlReservas = '../../../backend/index.php?recurso=reserva'; // Endpoint para crear reservas
    const apiUrlAuth = '../../../backend/index.php?recurso=auth'; // Endpoint para autenticación

    // Booking modal elements
    const bookingModal = new bootstrap.Modal(document.getElementById('booking-modal'));
    const modalEspacioNombre = document.getElementById('modal-espacio-nombre');
    const bookingEspacioIdInput = document.getElementById('booking-espacio-id');
    const dateRangePicker = document.getElementById('date-range-picker');
    const bookingForm = document.getElementById('booking-form');

    let flatpickrInstance; // To store the flatpickr instance

    // Navbar elements for dynamic display
    const userAnonLinks = document.querySelectorAll('.user-anon-link');
    const userLoggedInLink = document.querySelector('.user-logged-in-link');
    const adminLinks = document.querySelectorAll('.admin-link');
    const logoutButtonNavbar = document.getElementById('logout-button-navbar');

    // Get logged-in user info from localStorage
    const user = JSON.parse(localStorage.getItem('user'));
    const isLoggedIn = user && user.id;
    const userRole = user ? user.rol : null; // Assuming 'rol' is the string name like 'administrador'

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

    // Initialize Flatpickr
    flatpickrInstance = flatpickr(dateRangePicker, {
        mode: "range",
        minDate: "today",
        dateFormat: "Y-m-d",
        locale: "es", // Assuming Spanish locale
        inline: false, // Show as overlay when input is clicked
    });

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

    async function fetchEspacios() {
        try {
            const response = await fetch(apiUrlEspacios, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                const errorResult = await response.json();
                throw new Error(errorResult.message || 'Error al obtener los espacios.');
            }

            const espacios = await response.json();
            displayEspacios(espacios);

        } catch (error) {
            console.error('Error fetching espacios:', error);
            if (espaciosList) {
                espaciosList.innerHTML = `<p class="text-danger text-center">Error al cargar los espacios: ${error.message}</p>`;
            }
        }
    }

    function displayEspacios(espacios) {
        if (espaciosList) {
            espaciosList.innerHTML = ''; // Limpiar lista
            if (espacios.length === 0) {
                espaciosList.innerHTML = `<p class="text-center col-12">No hay espacios disponibles en este momento.</p>`;
                return;
            }

            espacios.forEach(espacio => {
                const espacioCard = `
                    <div class="col">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">${espacio.nombre}</h5>
                                <p class="card-text">${espacio.descripcion || 'Sin descripción.'}</p>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">Capacidad: ${espacio.capacidad} personas</li>
                                    <li class="list-group-item">Precio Diario: $${parseFloat(espacio.precio_diario).toFixed(2)}</li>
                                    <li class="list-group-item">Tipo: ${espacio.tipo_espacio}</li>
                                    <li class="list-group-item">Activo: ${espacio.activo ? 'Sí' : 'No'}</li>
                                </ul>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewAvailability(${espacio.id})">Ver Disponibilidad</button>
                                        <button type="button" class="btn btn-sm btn-outline-success" onclick="bookEspacio(${espacio.id}, '${espacio.nombre}')">Reservar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                espaciosList.innerHTML += espacioCard;
            });
        }
    }

    // Funciones para los botones
    window.viewAvailability = function(espacioId) {
        alert('Ver disponibilidad para espacio ID: ' + espacioId + ' (próximamente calendario detallado)');
        // Aquí se integraría la lógica del calendario detallado de disponibilidad
    };

    window.bookEspacio = function(espacioId, espacioNombre) {
        modalEspacioNombre.textContent = espacioNombre;
        bookingEspacioIdInput.value = espacioId;
        flatpickrInstance.clear(); // Clear any previously selected dates
        bookingModal.show(); // Show the modal
    };

    bookingForm.addEventListener('submit', async function(event) {
        event.preventDefault();

        const selectedDates = flatpickrInstance.selectedDates;
        if (selectedDates.length !== 2) {
            alert('Por favor, selecciona un rango de fechas para la reserva.');
            return;
        }

        const id_espacio = bookingEspacioIdInput.value;
        const fecha_inicio = flatpickrInstance.formatDate(selectedDates[0], "Y-m-d H:i:S"); // Use full timestamp
        const fecha_fin = flatpickrInstance.formatDate(selectedDates[1], "Y-m-d H:i:S"); // Use full timestamp

        const user = JSON.parse(localStorage.getItem('user'));
        if (!user || !user.id) {
            alert('Error: No se pudo obtener la información del usuario. Por favor, inicie sesión de nuevo.');
            window.location.href = 'login.html';
            return;
        }
        const id_usuario = user.id;

        const reservaData = {
            id_usuario: id_usuario,
            id_espacio: parseInt(id_espacio),
            fecha_inicio: fecha_inicio,
            fecha_fin: fecha_fin,
        };

        try {
            const response = await fetch(apiUrlReservas, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(reservaData)
            });

            const result = await response.json();

            if (response.ok) {
                alert(result.message || 'Reserva realizada exitosamente.');
                bookingModal.hide(); // Hide the modal on success
                // Optionally refresh espacios to show updated availability (if implemented)
            } else {
                alert(result.message || 'Error al realizar la reserva.');
            }
        } catch (error) {
            console.error('Error al enviar la reserva:', error);
            alert('Ocurrió un error al intentar realizar la reserva.');
        }
    });

    fetchEspacios(); // Cargar los espacios al iniciar la página
});