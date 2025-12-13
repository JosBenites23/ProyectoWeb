document.addEventListener('DOMContentLoaded', async function () {
    const reservasAdminList = document.querySelector('#reservas-admin-list tbody');
    const apiUrlReservas = '../../../backend/index.php?recurso=reserva';

    // IMPORTANT: Placeholder for admin role check. In a real app,
    // this would be determined from a logged-in user's session/token.
    const isAdmin = true; // Set to false to test unauthorized access

    // Check if user is admin before proceeding
    if (!isAdmin) {
        alert('Acceso denegado. Solo los administradores pueden ver el historial de reservas.');
        window.location.href = 'login.html'; // Redirect to login or other page
        return;
    }

    async function fetchAllReservas() {
        try {
            const response = await fetch(apiUrlReservas, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                    // Add Authorization header if using tokens
                }
            });

            if (!response.ok) {
                const errorResult = await response.json();
                throw new Error(errorResult.message || 'Error al obtener las reservas.');
            }

            const reservas = await response.json();
            displayReservas(reservas);

        } catch (error) {
            console.error('Error fetching all reservas:', error);
            if (reservasAdminList) {
                reservasAdminList.innerHTML = `<tr><td colspan="9" class="text-danger text-center">Error al cargar el historial de reservas: ${error.message}</td></tr>`;
            }
        }
    }

    function displayReservas(reservas) {
        reservasAdminList.innerHTML = ''; // Limpiar lista
        if (reservas.length === 0) {
            reservasAdminList.innerHTML = `<tr><td colspan="9" class="text-center">No hay reservas registradas.</td></tr>`;
            return;
        }

        reservas.forEach(reserva => {
            const row = reservasAdminList.insertRow();
            row.insertCell().textContent = reserva.id;
            row.insertCell().textContent = reserva.id_usuario;
            row.insertCell().textContent = reserva.id_espacio;
            row.insertCell().textContent = new Date(reserva.fecha_inicio).toLocaleString();
            row.insertCell().textContent = new Date(reserva.fecha_fin).toLocaleString();
            row.insertCell().textContent = reserva.estado_reserva;
            row.insertCell().textContent = parseFloat(reserva.total_pagado).toFixed(2);
            row.insertCell().textContent = new Date(reserva.fecha_reserva).toLocaleString();
            
            const actionsCell = row.insertCell();
            const editButton = document.createElement('button');
            editButton.className = 'btn btn-sm btn-warning me-2';
            editButton.textContent = 'Editar';
            editButton.onclick = () => editReserva(reserva.id); // Placeholder
            actionsCell.appendChild(editButton);

            const deleteButton = document.createElement('button');
            deleteButton.className = 'btn btn-sm btn-danger';
            deleteButton.textContent = 'Eliminar';
            deleteButton.onclick = () => deleteReserva(reserva.id); // Placeholder
            actionsCell.appendChild(deleteButton);
        });
    }

    // Placeholder functions for actions
    window.editReserva = function(reservaId) {
        alert('Editar reserva ID: ' + reservaId + ' (próximamente)');
        // Implement logic for editing reservation
    };

    window.deleteReserva = function(reservaId) {
        if (confirm('¿Estás seguro de que quieres eliminar la reserva ID: ' + reservaId + '?')) {
            alert('Eliminar reserva ID: ' + reservaId + ' (próximamente)');
            // Implement logic for deleting reservation (DELETE request)
        }
    };

    fetchAllReservas(); // Load all reservations on page load
});
