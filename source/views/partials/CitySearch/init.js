export const init = (Autocomplete, L, el) => {

    const parent = el.parentNode;
    const hiddenCity = parent.querySelector("input[name='city']");
    const hiddenCoordinates = parent.querySelector("input[name='coordinates']");

    let map;

    new Autocomplete(el.getAttribute('id'), {
        selectFirst: true,
        howManyCharacters: 2,
        insertToInput: true,
        cache: true,
        
        onSearch: ({
            currentValue
        }) => {
            const api = `https://nominatim.openstreetmap.org/search?format=geojson&limit=5&city=${encodeURI(currentValue)}`;
            return new Promise((resolve) => {
                fetch(api)
                    .then((response) => response.json())
                    .then((data) => {
                        resolve(data.features);
                    })
                    .catch((error) => {
                        console.error(error);
                    });
            });
        },
        // nominatim GeoJSON format parse this part turns json into the list of
        // records that appears when you type.
        onReset: ({
        }) => {
            resetHiddenFields();
        },
        onResults: ({
            currentValue,
            matches,
            template
        }) => {
            const regex = new RegExp(currentValue, "gi");
            // if the result returns 0 we
            // show the no results element
            console.log(matches);
            if(matches === 0) return template;
            // set pattern input validation based on results
            
            let patternList = [];
            
            const matchList = matches.map((element) => {
                const countryCode = element.context[element.context.length -1].short_code.toUpperCase().slice(0, 2);
                const countryEmoji = getFlagEmoji(countryCode);
                
                patternList = [...patternList, `${countryEmoji} ${element.place_name}`];
                
                return `
                    <li class="loupe">
                        <div class="result">
                            <span class="flag">${countryEmoji}</span> ${element.properties.display_name.replace(regex,(str) => `<b>${str}</b>`)}
                        </div>
                    </li> `;
                // Mapbox
                // return `<li class="loupe"><span>${element.place_name.replace(regex,(str) => `<b>${str}</b>`)}</span></li> `;
            })
            .join("");

            // console.log(patternList);
            el.setAttribute('pattern', patternList.join('|'));
            return matchList;
        },
    
        // // we add an action to enter or click
        onSubmit: ({
            object
        }) => {
            // console.log(object);
            const {display_name } = object.properties; // leaflet
            // const place_name = object.place_name; // mapbox
            const [lat, lng] = object.geometry.coordinates;
            
            // set hidden fields to store it as meta values in the users profile
            hiddenCity.value = display_name;
            // hiddenCity.value = place_name;
            hiddenCoordinates.value = [lng, lat];

            // remove all layers from the map
            if(map) {
                map.eachLayer(function (layer) {
                    if (!!layer.toGeoJSON) {
                        map.removeLayer(layer);
                    }
                });
        
        
                const marker = L.marker([lng, lat], {
                    title: place_name,
                });
        
                marker.addTo(map).bindPopup(place_name);
        
                map.setView([lng, lat], 8);
            }
        },
    
        // get index and data from li element after
        // hovering over li with the mouse or using
        // arrow keys ↓ | ↑
        onSelectedItem: ({
            index,
            element,
            object
        }) => {
            // console.log("onSelectedItem:", index, element, object);
        },
    
        // the method presents no results element
        noResults: ({
                currentValue,
                template
            }) => {
                // This prevents that "No results" inputs can be submitted.
                el.setAttribute('pattern', 'Search city/district, then select a suggestion.');
                template(`<li>No results found: "${currentValue}"</li>`)
            }
    });

    el.addEventListener('blur', () => {
        if(el.value === '') resetHiddenFields();
    });
    
    // Emoji prefixer
    const getFlagEmoji = countryCode=>String.fromCodePoint(...[...countryCode.toUpperCase()].map(x=>0x1f1a5+x.charCodeAt()));
    
    const resetHiddenFields = () => {
        console.log('reset');
        hiddenCity.value = '';
        hiddenCoordinates.value = '';
    }
    
    // Map
    const config = {
        minZoom: 6,
        maxZomm: 18,
    };
    
    // magnification with which the map will start
    const zoom = 3;
    
    // coordinates
    const lat = 52.22977;
    const lng = 21.01178;
    
    // calling map
    const mapEl = document.getElementById('map');

    if(mapEl) {
        map = L.map("map", config).setView([lat, lng], zoom);
        // Used to load and display tile layers on the map
        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        }).addTo(map);
    }
}