const requestForm = document.getElementById('request-form');
const inputField = document.getElementById('input-region');
const buttonSearch = document.getElementById('btn-search');
console.log(requestForm, inputField, buttonSearch);


buttonSearch.addEventListener('click', (e) => {
    e.preventDefault();

    const region = inputField.value.trim();
    console.log(region);

    if (region) {
        fetch(`https://restcountries.com/v3.1/region/${region}`)
            .then(response => response.json())
            .then(data => {
                const countriesContainer = document.getElementById('countries-container');
                countriesContainer.innerHTML = '';

                data.forEach(country => {
                    const countryCard = document.createElement('div');
                    countryCard.classList.add('col-md-4', 'mb-4');

                    countryCard.innerHTML = `
                                    <div class="card">
                                        <img src="${country.flags.svg}" class="card-img-top" alt="Flag of ${country.name.common}">
                                        <div class="card-body">
                                            <h5 class="card-title">${country.name.common}</h5>
                                            <p class="card-text">Capital: ${country.capital ? country.capital[0] : 'N/A'}</p>
                                            <p class="card-text">Poblacion: ${country.population.toLocaleString()}</p>
                                            <p class="card-text">Region: ${country.region}</p>
                                            <p class="card-text">Subregion: ${country.subregion}</p>
                                        </div>
                                    </div>
                                `;

                    countriesContainer.appendChild(countryCard);
                });
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
});



