<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (isLoggedIn()) {
    header('Location: dashboard.php'); // Redirect to dashboard if the user is logged in
    exit();
}

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.68';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));
$is_logged_in = false; // Ensure not logged in for this page

// Initialize variables
$buwana_id = $_GET['id'] ?? null;  // Correctly initializing buwana_id
$page = 'activate';
$first_name = '';

// PART 1: Check if the user is already logged in
if (isset($_SESSION['buwana_id'])) {
    header("Location: dashboard.php");
    exit();
}

// PART 2: Check if buwana_id is passed in the URL
if (is_null($buwana_id)) {
    echo '<script>
        alert("Hmm... something went wrong. No buwana ID was passed along. Please try logging in again. If this problem persists, you\'ll need to create a new account.");
        window.location.href = "login.php";
    </script>';
    exit();
}

// PART 3: Look up user information using buwana_id provided in URL
require_once("../buwanaconn_env.php");

// Fetch user information using buwana_id from the Buwana database
$sql_user_info = "SELECT first_name FROM users_tb WHERE buwana_id = ?";
$stmt_user_info = $buwana_conn->prepare($sql_user_info);

if ($stmt_user_info) {
    $stmt_user_info->bind_param('i', $buwana_id);
    $stmt_user_info->execute();
    $stmt_user_info->bind_result($first_name);
    $stmt_user_info->fetch();
    $stmt_user_info->close();
} else {
    die('Error preparing statement for fetching user info: ' . $buwana_conn->error);
}

// Ensure $first_name is set and not empty
if (empty($first_name)) {
    $first_name = 'User'; // Fallback if first name is not set
}

// PART 4: Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_location_full = $_POST['location_full'];
    $user_lat = $_POST['latitude'];
    $user_lon = $_POST['longitude'];

    // Extract country from the last term in the location string (after the last comma)
    $location_parts = explode(',', $user_location_full);
    $selected_country = trim(end($location_parts));

    // Fetch the country_id from the countries_tb using the extracted country name
    $sql_country = "SELECT country_id, continent_id FROM countries_tb WHERE country_name = ?";
    $stmt_country = $buwana_conn->prepare($sql_country);

    if ($stmt_country) {
        $stmt_country->bind_param('s', $selected_country);
        $stmt_country->execute();
        $stmt_country->bind_result($set_country_id, $set_continent_code);
        $stmt_country->fetch();
        $stmt_country->close();
    } else {
        die('Error preparing statement for fetching country info: ' . $buwana_conn->error);
    }

    // Check if the country was found in the database
    if (empty($set_country_id) || empty($set_continent_code)) {
        echo '<script>alert("Could not determine your country or continent based on your location. Please refine your location details.");</script>';
    } else {
        // Update the Buwana user's continent, country using buwana_id
        $sql_update_buwana = "UPDATE users_tb SET continent_code = ?, country_id = ?, location_full = ?, location_lat = ?, location_long = ? WHERE buwana_id = ?";
        $stmt_update_buwana = $buwana_conn->prepare($sql_update_buwana);
        if ($stmt_update_buwana) {
            $stmt_update_buwana->bind_param('sissdi', $set_continent_code, $set_country_id, $user_location_full, $user_lat, $user_lon, $buwana_id);
            $stmt_update_buwana->execute();
            $stmt_update_buwana->close();

            // PART 5: Open GoBrik connection and update tb_ecobrickers to set buwana_activated to 1
            require_once("../gobrikconn_env.php");

            $sql_update_gobrik = "UPDATE tb_ecobrickers SET buwana_activated = 1 WHERE buwana_id = ?";
            $stmt_update_gobrik = $gobrik_conn->prepare($sql_update_gobrik);

            if ($stmt_update_gobrik) {
                $stmt_update_gobrik->bind_param('i', $buwana_id); // Update based on the ecobricker's unique identifier
                if ($stmt_update_gobrik->execute()) {
                    // Successfully updated GoBrik
                    $stmt_update_gobrik->close();
                } else {
                    error_log('Error executing update on tb_ecobrickers: ' . $stmt_update_gobrik->error);
                    echo "Failed to update GoBrik record.";
                }
            } else {
                error_log('Error preparing GoBrik statement: ' . $gobrik_conn->error);
                echo "Failed to prepare GoBrik update statement.";
            }

            // Close the GoBrik connection
            $gobrik_conn->close();

            // Redirect to the next step
            header("Location: login.php?status=firsttime&id=" . urlencode($buwana_id));
            exit();
        } else {
            error_log('Error preparing statement for updating Buwana user: ' . $buwana_conn->error);
            header("Location: activate-3.php?id=" . urlencode($buwana_id) . "&error=db_update_failed");
            exit();
        }
    }
}

// Close the Buwana database connection after all operations are done
$buwana_conn->close();
?>
