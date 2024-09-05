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
    $country_continent = '';
    $country_icon = '';

    // Query to get the user's country_id from users_tb
    $sql_country = "SELECT country_id FROM users_tb WHERE buwana_id = ?";
    $stmt_country = $buwana_conn->prepare($sql_country);

    if ($stmt_country) {
        $stmt_country->bind_param('i', $buwana_id);
        if ($stmt_country->execute()) {
            $stmt_country->bind_result($country_id);
            $stmt_country->fetch();
            $stmt_country->close();

            // Now query the country_tb to get the country_continent using country_id
            $sql_continent = "SELECT country_continent FROM country_tb WHERE country_id = ?";
            $stmt_continent = $buwana_conn->prepare($sql_continent);

            if ($stmt_continent) {
                $stmt_continent->bind_param('i', $country_id);
                if ($stmt_continent->execute()) {
                    $stmt_continent->bind_result($country_continent);
                    $stmt_continent->fetch();
                    $stmt_continent->close();
                }
            }
        }
    }

    // Determine the globe emoticon based on the continent
    switch (strtolower($country_continent)) {
        case 'africa':
            $country_icon = '🌍';
            break;
        case 'europe':
            $country_icon = '🌍';
            break;
        case 'asia':
            $country_icon = '🌏';
            break;
        case 'north america':
        case 'south america':
            $country_icon = '🌎';
            break;
        case 'australia':
        case 'oceania':
            $country_icon = '🌏';
            break;
        case 'antarctica':
            $country_icon = '❄️';
            break;
        default:
            $country_icon = '🌐'; // Default icon if continent is not recognized
            break;
    }

    return $country_icon;
}
?>
