<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Retrieve User Data</title>
    <script>
        function fetchData() {
            var email = document.getElementById('email').value;
            if (!email) {
                alert('Please enter an email address.');
                return;
            }

            // Make the API call to fetch user data
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "fetch_user_data.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    document.getElementById('user-data').innerHTML = xhr.responseText;
                }
            };
            xhr.send("email=" + encodeURIComponent(email));
        }
    </script>
</head>
<body>
    <h1>Retrieve User Data</h1>
    <form onsubmit="event.preventDefault(); fetchData();">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <button type="submit">Retrieve</button>
    </form>
    <div id="user-data"></div>
</body>
</html>
