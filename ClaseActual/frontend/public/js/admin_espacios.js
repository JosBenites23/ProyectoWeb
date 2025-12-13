document.addEventListener('DOMContentLoaded', async function () {
    const espacioForm = document.getElementById('espacio-form');
    const espaciosAdminList = document.querySelector('#espacios-admin-list tbody');
    const formTitle = document.getElementById('form-title');
    const submitButton = document.getElementById('submit-button');
    const cancelButton = document.getElementById('cancel-edit-button');
    const espacioIdInput = document.getElementById('espacio-id');

    const apiUrl = '../../../backend/index.php?recurso=espacio';

    // IMPORTANT: Placeholder for admin role check. In a real app,
    // this would be determined from a logged-in user's session/token.
    // For now, assume we are an admin for testing purposes.
    const isAdmin = true; // Set to false to test unauthorized access

    // Check if user is admin before proceeding
    if (!isAdmin) {
        alert('Acceso denegado. Solo los administradores pueden gestionar espacios.');
        window.location.href = 'login.html'; // Redirect to login or other page
        return;
    }

    async function fetchEspacios() {
        try {
            const response = await fetch(apiUrl, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                    // Add Authorization header if using tokens
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
            if (espaciosAdminList) {
                espaciosAdminList.innerHTML = `<tr><td colspan="7" class="text-danger text-center">Error al cargar espacios: ${error.message}</td></tr>`;
            }
        }
    }

    function displayEspacios(espacios) {
        espaciosAdminList.innerHTML = ''; // Limpiar lista
        if (espacios.length === 0) {
            espaciosAdminList.innerHTML = `<tr><td colspan="7" class="text-center">No hay espacios registrados.</td></tr>`;
            return;
        }

        espacios.forEach(espacio => {
            const row = espaciosAdminList.insertRow();
            row.insertCell().textContent = espacio.id;
            row.insertCell().textContent = espacio.nombre;
            row.insertCell().textContent = espacio.tipo_espacio;
            row.insertCell().textContent = espacio.capacidad;
            row.insertCell().textContent = parseFloat(espacio.precio_diario).toFixed(2);
            row.insertCell().textContent = espacio.activo ? 'Sí' : 'No';
            
            const actionsCell = row.insertCell();
            const editButton = document.createElement('button');
            editButton.className = 'btn btn-sm btn-warning me-2';
            editButton.textContent = 'Editar';
            editButton.onclick = () => editEspacio(espacio);
            actionsCell.appendChild(editButton);

            const deleteButton = document.createElement('button');
            deleteButton.className = 'btn btn-sm btn-danger';
            deleteButton.textContent = 'Eliminar';
            deleteButton.onclick = () => deleteEspacio(espacio.id);
            actionsCell.appendChild(deleteButton);
        });
    }

    async function submitEspacioForm(event) {
        event.preventDefault();

        const formData = new FormData(espacioForm);
        const espacioData = {};
        for (let [key, value] of formData.entries()) {
            if (key === 'activo') {
                espacioData[key] = value === 'on' ? true : false; // Handle checkbox value
            } else if (key === 'capacidad' || key === 'precio_diario') {
                espacioData[key] = parseFloat(value);
            } else {
                espacioData[key] = value;
            }
        }

        const id = espacioIdInput.value;
        const method = id ? 'PUT' : 'POST';
        const url = id ? `${apiUrl}&id=${id}` : apiUrl;

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json'
                    // Add Authorization header if using tokens
                },
                body: JSON.stringify(espacioData)
            });

            const result = await response.json();

            if (response.ok) {
                alert(result.message || 'Operación exitosa.');
                espacioForm.reset();
                espacioIdInput.value = ''; // Clear ID after operation
                formTitle.textContent = 'Agregar Nuevo Espacio';
                submitButton.textContent = 'Agregar Espacio';
                cancelButton.style.display = 'none';
                fetchEspacios(); // Refresh list
            } else {
                alert(result.message || 'Error en la operación.');
            }
        } catch (error) {
            console.error('Error submitting espacio form:', error);
            alert('Ocurrió un error al procesar la solicitud.');
        }
    }

    function editEspacio(espacio) {
        formTitle.textContent = 'Editar Espacio';
        submitButton.textContent = 'Actualizar Espacio';
        cancelButton.style.display = 'inline-block';

        espacioIdInput.value = espacio.id;
        document.getElementById('nombre').value = espacio.nombre;
        document.getElementById('descripcion').value = espacio.descripcion;
        document.getElementById('capacidad').value = espacio.capacidad;
        document.getElementById('precio_diario').value = espacio.precio_diario;
        document.getElementById('tipo_espacio').value = espacio.tipo_espacio;
        document.getElementById('politicas_reserva').value = espacio.politicas_reserva;
        document.getElementById('activo').checked = espacio.activo; // Set checkbox
    }

    async function deleteEspacio(id) {
        if (!confirm('¿Estás seguro de que quieres eliminar este espacio?')) {
            return;
        }

        try {
            const response = await fetch(`${apiUrl}&id=${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                    // Add Authorization header if using tokens
                }
            });

            const result = await response.json();

            if (response.ok) {
                alert(result.message || 'Espacio eliminado exitosamente.');
                fetchEspacios(); // Refresh list
            } else {
                alert(result.message || 'Error al eliminar el espacio.');
            }
        } catch (error) {
            console.error('Error deleting espacio:', error);
            alert('Ocurrió un error al intentar eliminar el espacio.');
        }
    }

    cancelButton.addEventListener('click', () => {
        espacioForm.reset();
        espacioIdInput.value = '';
        formTitle.textContent = 'Agregar Nuevo Espacio';
        submitButton.textContent = 'Agregar Espacio';
        cancelButton.style.display = 'none';
    });

    espacioForm.addEventListener('submit', submitEspacioForm);

    fetchEspacios(); // Cargar los espacios al iniciar la página
});
