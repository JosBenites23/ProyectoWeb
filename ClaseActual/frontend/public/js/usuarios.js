document.addEventListener('DOMContentLoaded', async function () {
    const userTableBody = document.getElementById('user-table-body');
    const usuarioForm = document.getElementById('usuario-form');
    const formTitle = document.getElementById('form-title');
    const submitButton = document.getElementById('submit-button');
    const cancelButton = document.getElementById('cancel-edit-button');
    const usuarioIdInput = document.getElementById('usuario-id');

    const apiUrl = '../../../backend/index.php?recurso=usuario';

    // Función para cargar los usuarios y mostrarlos en la tabla
    async function fetchUsers() {
        try {
            const response = await fetch(apiUrl);
            if (!response.ok) {
                const errorResult = await response.json();
                throw new Error(errorResult.message || 'Error al obtener los usuarios.');
            }

            const users = await response.json();
            displayUsers(users);
        } catch (error) {
            console.error('Error fetching users:', error);
            userTableBody.innerHTML = `<tr><td colspan="6" class="text-danger text-center">${error.message}</td></tr>`;
        }
    }

    // Función para mostrar los usuarios en la tabla
    function displayUsers(users) {
        userTableBody.innerHTML = '';
        if (users.length === 0) {
            userTableBody.innerHTML = '<tr><td colspan="6" class="text-center">No hay usuarios registrados.</td></tr>';
            return;
        }

        users.forEach(user => {
            const row = userTableBody.insertRow();
            row.insertCell().textContent = user.nombre;
            row.insertCell().textContent = user.correo;
            row.insertCell().textContent = user.cedula;
            row.insertCell().textContent = user.telefono;
            row.insertCell().textContent = user.rol; // 'rol' is the string name from the JOIN

            const actionsCell = row.insertCell();
            const editButton = document.createElement('button');
            editButton.className = 'btn btn-primary btn-sm me-2';
            editButton.textContent = 'Editar';
            editButton.onclick = () => editUser(user);
            actionsCell.appendChild(editButton);

            const deleteButton = document.createElement('button');
            deleteButton.className = 'btn btn-danger btn-sm';
            deleteButton.textContent = 'Eliminar';
            deleteButton.onclick = () => deleteUser(user.id);
            actionsCell.appendChild(deleteButton);
        });
    }

    // Función para manejar el envío del formulario (solo para editar)
    async function submitUserForm(event) {
        event.preventDefault();

        const formData = new FormData(usuarioForm);
        const userData = {};
        for (let [key, value] of formData.entries()) {
            userData[key] = value;
        }

        // Convert role name back to role ID for backend
        // This is a simplification; a better approach would be to have role names and IDs available from an endpoint.
        const id_rol_value = document.getElementById('id_rol').value;
        userData.id_rol = parseInt(id_rol_value);
        
        // Remove password if it's empty, so it's not updated
        if (!userData.password) {
            delete userData.password;
        }
        
        const id = usuarioIdInput.value;
        if (!id) {
            alert('No se ha seleccionado un usuario para editar.');
            return;
        }
        
        const url = `${apiUrl}&id=${id}`;

        try {
            const response = await fetch(url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(userData)
            });

            const result = await response.json();

            if (response.ok) {
                alert(result.message || 'Usuario actualizado exitosamente.');
                resetForm();
                fetchUsers(); // Refresh list
            } else {
                alert(result.message || 'Error al actualizar el usuario.');
            }
        } catch (error) {
            console.error('Error submitting user form:', error);
            alert('Ocurrió un error al procesar la solicitud.');
        }
    }

    // Función para poblar el formulario para editar un usuario
    function editUser(user) {
        formTitle.textContent = 'Editar Usuario';
        submitButton.textContent = 'Actualizar Usuario';
        cancelButton.style.display = 'inline-block';
        usuarioForm.scrollIntoView({ behavior: 'smooth' });

        usuarioIdInput.value = user.id;
        document.getElementById('nombre').value = user.nombre;
        document.getElementById('correo').value = user.correo;
        document.getElementById('cedula').value = user.cedula;
        document.getElementById('telefono').value = user.telefono;
        document.getElementById('password').value = ''; // Clear password field
        
        // Set the correct role in the dropdown
        const rolValue = user.rol.toLowerCase() === 'administrador' ? '2' : '1';
        document.getElementById('id_rol').value = rolValue;
    }

    // Función para eliminar un usuario
    async function deleteUser(id) {
        if (!confirm('¿Estás seguro de que quieres eliminar este usuario?')) {
            return;
        }

        try {
            const response = await fetch(`${apiUrl}&id=${id}`, {
                method: 'DELETE'
            });

            const result = await response.json();

            if (response.ok) {
                alert(result.message || 'Usuario eliminado exitosamente.');
                fetchUsers(); // Refresh list
            } else {
                alert(result.message || 'Error al eliminar el usuario.');
            }
        } catch (error) {
            console.error('Error deleting user:', error);
            alert('Ocurrió un error al intentar eliminar el usuario.');
        }
    }
    
    // Función para resetear el formulario
    function resetForm(){
        usuarioForm.reset();
        usuarioIdInput.value = '';
        formTitle.textContent = 'Editar Usuario';
        submitButton.textContent = 'Actualizar Usuario';
        cancelButton.style.display = 'none';
    }

    // Event Listeners
    cancelButton.addEventListener('click', resetForm);
    usuarioForm.addEventListener('submit', submitUserForm);

    // Cargar los usuarios al iniciar la página
    fetchUsers();
});