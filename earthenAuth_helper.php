<?php


// Function to display alert message in different languages
function getNoEcobrickAlert($lang) {
    switch ($lang) {
        case 'fr':
            return "Whoop ! Aucun ecobrick n'a pu être trouvé pour la mise à jour. Quelque chose s'est mal passé lors du processus d'enregistrement ou vous avez supprimé cet ecobrick. Essayez d'enregistrer à nouveau.";
        case 'es':
            return "¡Whoop! No se pudo encontrar un ecobrick para actualizar. Algo salió mal en el proceso de registro o eliminaste este ecobrick. Intenta registrarte nuevamente.";
        case 'id':
            return "Whoop! Tidak ada ecobrick yang dapat ditemukan untuk diperbarui. Ada yang salah dalam proses pencatatan atau Anda telah menghapus ecobrick ini. Cobalah mencatat lagi.";
        default: // English as default
            return "Whoop! No ecobrick could be found to update. Something went wrong in the logging process or you've deleted this ecobrick. Try logging again.";
    }
}



function startSecureSession() {
    // Start the session if it's not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Regenerate the session ID periodically to prevent session fixation
    if (!isset($_SESSION['CREATED'])) {
        $_SESSION['CREATED'] = time();
    } elseif (time() - $_SESSION['CREATED'] > 1800) { // Regenerate session ID every 30 minutes
        session_regenerate_id(true);
        $_SESSION['CREATED'] = time();
    }
}

function isLoggedIn() {
    return isset($_SESSION['buwana_id']);
}

function getUserFirstName($buwana_conn, $buwana_id) {
    $first_name = '';
    $sql_user_info = "SELECT first_name FROM users_tb WHERE buwana_id = ?";
    $stmt_user_info = $buwana_conn->prepare($sql_user_info);

    if ($stmt_user_info) {
        $stmt_user_info->bind_param('i', $buwana_id);
        if ($stmt_user_info->execute()) {
            $stmt_user_info->bind_result($first_name);
            $stmt_user_info->fetch();
        }
        $stmt_user_info->close();
    }
    return $first_name;
}

function getWatershedName($buwana_conn, $buwana_id, $lang) {
    $watershed_name = '';

    // Determine the appropriate field name for the watershed based on the language
    switch (strtolower($lang)) {
        case 'fr':
            $field_name = 'watershed_name_fr';
            break;
        case 'es':
            $field_name = 'watershed_name_es';
            break;
        case 'id':
            $field_name = 'watershed_name_id';
            break;
        case 'en':
        default:
            $field_name = 'watershed_name'; // Default to the general name if $lang is not set or is 'en'
            break;
    }

    // Query to get the user's watershed name based on the determined field
    $sql_watershed = "SELECT $field_name FROM watersheds_tb WHERE watershed_id = (SELECT watershed_id FROM users_tb WHERE buwana_id = ?)";
    $stmt_watershed = $buwana_conn->prepare($sql_watershed);

    if ($stmt_watershed) {
        $stmt_watershed->bind_param('i', $buwana_id);
        if ($stmt_watershed->execute()) {
            $stmt_watershed->bind_result($watershed_name);
            $stmt_watershed->fetch();
            $stmt_watershed->close();
        }
    }

    // If $watershed_name is still empty or null, set a default value
    if (empty($watershed_name)) {
        $watershed_name = 'Unknown Watershed'; // Default value if no valid watershed name is found
    }

    return $watershed_name;
}

function getUserFullLocation($buwana_conn, $buwana_id) {
    $location_full = '';

    // Query to get the user's full location from the users_tb table
    $sql_location = "SELECT location_full FROM users_tb WHERE buwana_id = ?";
    $stmt_location = $buwana_conn->prepare($sql_location);

    if ($stmt_location) {
        $stmt_location->bind_param('i', $buwana_id);
        if ($stmt_location->execute()) {
            $stmt_location->bind_result($location_full);
            $stmt_location->fetch();
            $stmt_location->close();
        }
    }

    // If $location_full is still empty or null, set a default value
    if (empty($location_full)) {
        $location_full = 'Unknown Location'; // Default value if no valid location is found
    }

    return $location_full;
}



function getUserContinent($buwana_conn, $buwana_id) {
    $continent_code = '';
    $country_icon = '';

    // Query to get the user's continent_code from users_tb
    $sql_continent = "SELECT continent_code FROM users_tb WHERE buwana_id = ?";
    $stmt_continent = $buwana_conn->prepare($sql_continent);

    if ($stmt_continent) {
        $stmt_continent->bind_param('i', $buwana_id);
        if ($stmt_continent->execute()) {
            $stmt_continent->bind_result($continent_code);
            $stmt_continent->fetch();
            $stmt_continent->close();
        }
    }

    // Determine the globe emoticon based on the continent_code
    switch (strtoupper($continent_code)) {
        case 'AF':
            $country_icon = '🌍'; // Africa
            break;
        case 'EU':
            $country_icon = '🌍'; // Europe
            break;
        case 'AS':
            $country_icon = '🌏'; // Asia
            break;
        case 'NA':
        case 'SA':
            $country_icon = '🌎'; // North America, South America
            break;
        case 'AU':
        case 'OC':
            $country_icon = '🌏'; // Australia, Oceania
            break;
        case 'AN':
            $country_icon = '❄️'; // Antarctica
            break;
        default:
            $country_icon = '🌐'; // Default icon if continent is not recognized
            break;
    }

    return $country_icon;
}



?>
