function openSearch() {
    document.body.style.overflow = 'hidden';
    document.body.style.maxHeight = '100vh';
    document.body.style.overflowY = "clip";

    var modal = document.getElementById('right-search-overlay');
    modal.style.width = "100%";

    modal.setAttribute('tabindex', '0');
    modal.focus();

    document.addEventListener('focus', function(event) {
        if (!modal.contains(event.target)) {
            event.stopPropagation();
            modal.focus();
        }
    }, true);

    // Hide the search results initially
    document.getElementById('search-results').style.display = 'none';
}

/* Close when someone clicks on the "x" symbol inside the overlay */
function closeSearch() {
    document.getElementById("right-search-overlay").style.width = "0%";

    // Allow scrolling on the body again
    document.body.style.overflow = '';
    document.body.style.maxHeight = '';
}

function clearResults() {
    document.getElementById('search_input').value = '';
    document.getElementById('search-results').innerHTML = '';
    var overlayContent = document.querySelector('.search-overlay-content');
    overlayContent.style.height = '';
    overlayContent.style.marginTop = '';
}

function handleKeyPress(event) {
    if (event.keyCode === 13) { // 13 is the key code for the enter key
        event.preventDefault(); // Prevent the default action to stop form submission
        ecobrickSearch(); // Call your search function without arguments
    }
}

//ECOBRICK SEARCH FUNCTION
function ecobrickSearch() {
    var query = document.getElementById("search_input").value.toLowerCase();

    // Hide the search results initially
    document.getElementById('search-results').style.display = 'none';
    var overlayContent = document.querySelector('.search-overlay-content');
    overlayContent.style.height = 'fit-content';
    overlayContent.style.marginTop = '8%';

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4) {
            if (this.status == 200) {
                try {
                    var data = JSON.parse(this.responseText);
                    presentEcobrickResults(data, query);
                } catch (e) {
                    console.error("Error parsing JSON:", e);
                    console.error("Response:", this.responseText);
                }
            } else {
                console.error("Error with search request:", this.status, this.statusText);
            }
        }
    };
    xmlhttp.open("GET", "../scripts/ecobrick_search.php?query=" + encodeURIComponent(query), true);
    xmlhttp.send();
}

function presentEcobrickResults(ecobricks) {
    var resultsTable = document.getElementById("ecobrick-search-return");
    resultsTable.innerHTML = `
        <tr>
            <th data-lang-id="1103-brik">Brik</th>
            <th data-lang-id="1104-weight">Weight</th>
            <th data-lang-id="1105-location">Location</th>
            <th data-lang-id="1106-maker">Maker</th>
            <th data-lang-id="1107-serial">Serial</th>
        </tr>
    `;

    ecobricks.forEach(function(ecobrick) {
        var serial_no = ecobrick.serial_no;
        var wrapped_serial_no = serial_no.slice(0, 3) + '<br>' + serial_no.slice(3);

        resultsTable.innerHTML += `
            <tr>
                <td><img src="https://ecobricks.org/${ecobrick.ecobrick_thumb_photo_url}" alt="Ecobrick ${serial_no} by ${ecobrick.ecobricker_maker} in ${ecobrick.location_full}" title="Ecobrick ${serial_no} by ${ecobrick.ecobricker_maker} in ${ecobrick.location_full}" loading="lazy" onclick="ecobrickPreview('${serial_no}','${ecobrick.weight_g}g','${ecobrick.ecobricker_maker}','${ecobrick.location_full}')" class="table-thumbnail"></td>


                <td><img src="https://ecobricks.org/${ecobrick.ecobrick_thumb_photo_url}" alt="Ecobrick Thumbnail" class="table-thumbnail"></td>
                <td>${ecobrick.weight_g}g</td>
                <td>${ecobrick.location_full}</td>
                <td>${ecobrick.ecobricker_maker}</td>
<td><button class="serial-button"><a href="brik.php?serial_no=${serial_no}">${wrapped_serial_no}</a></button></td>
            </tr>
        `;
    });

    // Show the search results
    document.getElementById('search-results').style.display = 'block';
}
