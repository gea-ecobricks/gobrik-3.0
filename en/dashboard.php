<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set up page variables
$directory = basename(dirname($_SERVER['SCRIPT_NAME']));
$lang = $directory;
$version = '0.351';
$page = 'dashboard';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo '<script>alert("Please login before viewing this page."); window.location.href = "login.php";</script>';
    exit();
}

$user_id = $_SESSION['user_id'];

// Include database connection
include '../buwana_env.php'; // this file provides the database server, user, dbname information to access the server

// Look up fields from users_tb using the user_id
$first_name = '';
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql_lookup_user = "SELECT first_name FROM users_tb WHERE user_id = ?";
$stmt_lookup_user = $conn->prepare($sql_lookup_user);

if ($stmt_lookup_user) {
    $stmt_lookup_user->bind_param("i", $user_id);
    $stmt_lookup_user->execute();
    $stmt_lookup_user->bind_result($first_name);
    $stmt_lookup_user->fetch();
    $stmt_lookup_user->close();
} else {
    die("Error preparing statement for users_tb: " . $conn->error);
}

$conn->close();

// Include GoBrik database credentials
include '../gobrik_env.php';

// Create connection to GoBrik database
$conn2 = new mysqli($servername, $username, $password, $dbname);

if ($conn2->connect_error) {
    die("Connection failed: " . $conn2->connect_error);
}

// SQL query to fetch the count of ecobricks and the sum of weight_authenticated_kg
$sql = "SELECT COUNT(*) as ecobrick_count, SUM(weight_authenticated_kg) as total_weight FROM tb_ecobricks";
$result = $conn2->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $ecobrick_count = $row['ecobrick_count'];
    $total_weight = $row['total_weight'];
} else {
    $ecobrick_count = 0;
    $total_weight = 0;
}

$conn2->close();

echo '<!DOCTYPE html>
<html lang="' . $lang . '">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>
</head>
<body>';

?>



<title>Dashboard | GoBrik 3.0</title>

<!--
GoBrik.com site version 3.0
Developed and made open source by the Global Ecobrick Alliance
See our git hub repository for the full code and to help out:
https://github.com/gea-ecobricks/gobrik-3.0/tree/main/en-->

<?php require_once ("../includes/dashboard-inc.php"); ?>

<div class="splash-content-block"></div>
<div id="splash-bar"></div>

<!-- DASHBOARD CONTENT -->
<div id="form-submission-box" style="height:fit-content;">
    <div class="form-container">
        <div class="dolphin-pic" style="margin-top:-45px;background-size:contain;">
            <img src="../webps/earth-community.webp" width="80%">
        </div>

        <div style="text-align:center;width:100%;margin:auto;">
            <h2>Welcome <?php echo htmlspecialchars($first_name); ?>!</h2>
            <p>You're logged into the brand new GoBrik 3.0!</p>
            <h3>As of today, <?php echo $ecobrick_count; ?> ecobricks have been logged on GoBrik, representing over <?php echo $total_weight; ?> kg of sequestered plastic!</h3>
        </div>

        <div style="display:flex;flex-flow:row;width:100%;justify-content:center;">
            <button class="go-button" id="log-ecobrick-button">➕ Log an Ecobrick</button>
            <!-- Logout Button -->
            <button class="go-button" id="logout-button" onclick="logoutUser()">📤 Logout</button>
        </div>
    </div>
</div><!--closes dashboard content-->

</div>

</div><!--closes main and starry background-->

<!-- FOOTER STARTS HERE -->
<?php require_once ("../footer-2024.php");?>
<?php require_once ("../footer-2024.php"); ?>

<script type="text/javascript">
function showModalInfo(type) {
    const modal = document.getElementById('form-modal-message');
    const photobox = document.getElementById('modal-photo-box');
    const messageContainer = modal.querySelector('.modal-message');
    let content = '';
    photobox.style.display = 'none';
    switch (type) {
        case 'reset':
            content = `
                <img src="../pngs/exchange-bird.png" alt="Reset Password" height="250px" width="250px" class="preview-image">
                <div class="preview-title">Reset Password</div>
                <div class="preview-text">Oops! This function is not yet operational. Create another account for the moment as all accounts will be deleted once we migrate from beta to live.</div>
            `;
            break;
        default:
            content = '<p>Invalid term selected.</p>';
    }

    messageContainer.innerHTML = content;

    // Show the modal and update other page elements
    modal.style.display = 'flex';
    document.getElementById('page-content').classList.add('blurred');
    document.getElementById('footer-full').classList.add('blurred');
    document.body.classList.add('modal-open');
}

document.getElementById('log-ecobrick-button').addEventListener('click', function() {
    // Redirect to the log.php page
    window.location.href = 'log.php';
});
</script>

</body>
</html>
