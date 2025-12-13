document.addEventListener('DOMContentLoaded', async function () {
    const apiUrlStats = '../../../backend/index.php?recurso=stats';

    // Elements for displaying stats
    const totalUsersElement = document.getElementById('total-users');
    const totalActiveSpacesElement = document.getElementById('total-active-spaces');
    const totalReservationsElement = document.getElementById('total-reservations');
    const reservationsByStatusElement = document.getElementById('reservations-by-status');
    const mostReservedSpacesElement = document.getElementById('most-reserved-spaces');

    // IMPORTANT: Placeholder for admin role check. In a real app,
    // this would be determined from a logged-in user's session/token.
    // For now, assume we are an admin for testing purposes.
    const isAdmin = true; // Set to false to test unauthorized access

    // Check if user is admin before proceeding
    if (!isAdmin) {
        alert('Acceso denegado. Solo los administradores pueden ver el dashboard.');
        window.location.href = 'login.html'; // Redirect to login or other page
        return;
    }

    async function fetchStats() {
        try {
            const response = await fetch(apiUrlStats, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                    // Add Authorization header if using tokens
                }
            });

            if (!response.ok) {
                const errorResult = await response.json();
                throw new Error(errorResult.message || 'Error al obtener las estadísticas.');
            }

            const stats = await response.json();
            displayStats(stats);

        } catch (error) {
            console.error('Error fetching stats:', error);
            alert('Ocurrió un error al cargar las estadísticas.');
        }
    }

    function displayStats(stats) {
        if (totalUsersElement) totalUsersElement.textContent = stats.total_users !== undefined ? stats.total_users : 'N/A';
        if (totalActiveSpacesElement) totalActiveSpacesElement.textContent = stats.total_active_spaces !== undefined ? stats.total_active_spaces : 'N/A';
        if (totalReservationsElement) totalReservationsElement.textContent = stats.total_reservations !== undefined ? stats.total_reservations : 'N/A';

        // Reservations by Status
        if (reservationsByStatusElement) {
            reservationsByStatusElement.innerHTML = '';
            const statusMap = {
                'pending_reservations': 'Pendientes',
                'confirmed_reservations': 'Confirmadas',
                'cancelled_reservations': 'Canceladas'
            };
            for (const key in statusMap) {
                if (stats[key] !== undefined) {
                    const item = document.createElement('li');
                    item.className = 'list-group-item d-flex justify-content-between align-items-center';
                    item.innerHTML = `<span>Reservas ${statusMap[key]}:</span> <span class="badge bg-primary rounded-pill">${stats[key]}</span>`;
                    reservationsByStatusElement.appendChild(item);
                }
            }
        }

        // Most Reserved Spaces
        if (mostReservedSpacesElement) {
            mostReservedSpacesElement.innerHTML = '';
            if (stats.most_reserved_spaces && stats.most_reserved_spaces.length > 0) {
                stats.most_reserved_spaces.forEach(space => {
                    // To display space names, you'd need to fetch space details or join in backend
                    // For now, just showing ID and count
                    const item = document.createElement('li');
                    item.className = 'list-group-item d-flex justify-content-between align-items-center';
                    item.innerHTML = `<span>Espacio ID: ${space.id_espacio}</span> <span class="badge bg-secondary rounded-pill">${space.total_reservas} reservas</span>`;
                    mostReservedSpacesElement.appendChild(item);
                });
            } else {
                mostReservedSpacesElement.innerHTML = `<li class="list-group-item text-center">No hay espacios reservados aún.</li>`;
            }
        }
    }

    fetchStats(); // Cargar las estadísticas al iniciar la página
});
