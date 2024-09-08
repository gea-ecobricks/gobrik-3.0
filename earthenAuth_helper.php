<?php


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
