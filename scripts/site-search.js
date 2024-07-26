

 /* -------------------------------------------------------------------------- */

 /*	SITE SEARCH

 /* -------------------------------------------------------------------------- */


 /* RIGHT SEARCH CURTAIN OVERLAY

 Triggers the right search panel*/

 function openSearch() {
   document.body.style.overflow = 'hidden';
   document.body.style.maxHeight = '100vh';
   document.getElementById("right-search-overlay").style.width = "100%";
//   document.getElementById("right-search-overlay").style.display = "block";

   document.body.style.overflowY = "clip";

   var modal = document.getElementById('right-search-overlay');

   function modalShow() {
       modal.setAttribute('tabindex', '0');
       modal.focus();
   }

   function focusRestrict(event) {
       document.addEventListener('focus', function(event) {
           if (modalOpen && !modal.contains(event.target)) {
               event.stopPropagation();
               modal.focus();
           }
       }, true);
   }


   modalShow(); // Ensure the modal is shown correctly
}



 /* Close when someone clicks on the "x" symbol inside the overlay */
 function closeSearch() {
   document.getElementById("right-search-overlay").style.width = "0%";

        // Allow scrolling on the body again
        document.body.style.overflow = '';
        document.body.style.maxHeight = '';
 }





//
//function clearResults() {
//  var searchInput = document.getElementById('search_input');
//  var resultsContainer = document.getElementById('search_results');
//  var overlayContent = document.querySelector('.search-overlay-content');
//  searchInput.value = '';
//  resultsContainer.innerHTML = '';
//  overlayContent.style.height = '';
//  overlayContent.style.marginTop = '';
//
//}



//ECOBRICK SEARCH FUNCTION
function ecobrickSearch() {
    var query = document.getElementById("search_input").value.toLowerCase();

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


function presentEcobrickResults(ecobricks, query) {
    var resultsTable = document.getElementById("ecobrick-search-return");
    resultsTable.innerHTML = `
        <tr>
            <th>Brik</th>
            <th>Weight (g)</th>
            <th>Location</th>
            <th>Maker</th>
            <th>Serial No</th>
        </tr>
    `;

    ecobricks.forEach(function(ecobrick) {
        var serial_no = ecobrick.serial_no;
        var wrapped_serial_no = serial_no.slice(0, 3) + '<br>' + serial_no.slice(3);

        resultsTable.innerHTML += `
            <tr>
                <td><img src="https://ecobricks.org/${ecobrick.ecobrick_thumb_photo_url}" alt="Ecobrick Thumbnail" style="max-width: 100px;"></td>
                <td>${ecobrick.weight_g}g</td>
                <td>${ecobrick.location_full}</td>
                <td>${ecobrick.ecobricker_maker}</td>
                <td><a href="brik.php?serial_no=${serial_no}">${wrapped_serial_no}</a></td>
            </tr>
        `;
    });
}