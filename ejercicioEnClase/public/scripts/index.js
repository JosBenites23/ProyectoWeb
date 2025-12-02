const tbody = document.getElementById("tbody");

fetch("../../controladores/usuario.controlador.php")
    .then(response => response.json())
    .then(data => {
        console.log(data);
        data.forEach(element => {
            const row = document.createElement("tr");
            row = "";
            row.innerHTML = `
                <td>${element[0]}</td>
                <td>${element[1]}</td>
                <td>${element[2]}</td>
                <td>${element[3]}</td>
            `;
            console.log(row);
            tbody.appendChild(row);
        });
    })
    .catch(error => {
        console.error("Error:", error);
    });

