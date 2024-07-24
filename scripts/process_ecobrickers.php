<!DOCTYPE html>
<html>
<head>
    <title>Retrieve User Data</title>
</head>
<body>

<h2>Retrieve User Information</h2>
<form id="retrieveForm">
    <label for="email">Email Address:</label>
    <input type="email" id="email" name="email" required>
    <button type="button" onclick="retrieveUserData()">Retrieve</button>
</form>

<div id="userData" style="display:none;">
    <h3>User Details</h3>
    <p id="userName"></p>
    <p id="userRegistrationDate"></p>
    <p id="userEcobricks"></p>
</div>

<script>
function retrieveUserData() {
    const email = document.getElementById('email').value;

    // Prepare the API request to retrieve user data by email
    const app_id = '5b8c28c2a1152679c209ce0c';
    const api_key = '360aa2b0-af19-11e8-bd38-41d9fc3da0cf';

    const filters = [{
        "field": "field_737",
        "operator": "is",
        "value": email
    }];
    const url = "https://api.knack.com/v1/objects/object_14/records?filters=" + encodeURIComponent(JSON.stringify(filters));

    // Initialize cURL session
    const xhr = new XMLHttpRequest();
    xhr.open("GET", url, true);
    xhr.setRequestHeader("X-Knack-Application-Id", app_id);
    xhr.setRequestHeader("X-Knack-REST-API-Key", api_key);

    // Handle the response
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.records && response.records.length > 0) {
                const user = response.records[0];
                document.getElementById('userName').textContent = "Full Name: " + user.field_737;
                document.getElementById('userRegistrationDate').textContent = "Date of Registration: " + user.field_294;
                document.getElementById('userEcobricks').textContent = "Number of Ecobricks: " + user.field_141;
                document.getElementById('userData').style.display = "block";
            } else {
                alert('No user found with that email address.');
            }
        } else if (xhr.readyState === 4) {
            alert('Error retrieving data from Knack API');
        }
    };

    // Send the request
    xhr.send();
}
</script>

</body>
</html>
