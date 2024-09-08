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
function getUserContinent($buwana_conn, $buwana_id, $lang = '') {
    $continent_code = '';
    $country_icon = '';
    $watershed_name = '';

    // Determine which column to use for watershed name based on the $lang variable
    $lang_column = 'watershed_name'; // Default column if $lang is empty or not set
    if (!empty($lang)) {
        switch (strtolower($lang)) {
            case 'en':
                $lang_column = 'watershed_name_en';
                break;
            case 'fr':
                $lang_column = 'watershed_name_fr';
                break;
            case 'es':
                $lang_column = 'watershed_name_es';
                break;
            case 'id':
                $lang_column = 'watershed_name_id';
                break;
        }
    }

    // Query to get the user's continent_code and watershed_id from users_tb
    $sql_user = "SELECT continent_code, watershed_id FROM users_tb WHERE buwana_id = ?";
    $stmt_user = $buwana_conn->prepare($sql_user);

    if ($stmt_user) {
        $stmt_user->bind_param('i', $buwana_id);
        if ($stmt_user->execute()) {
            $stmt_user->bind_result($continent_code, $watershed_id);
            $stmt_user->fetch();
            $stmt_user->close();

            // If watershed_id is not null, query the watershed name from watersheds_tb
            if (!empty($watershed_id)) {
                $sql_watershed = "SELECT $lang_column FROM watersheds_tb WHERE watershed_id = ?";
                $stmt_watershed = $buwana_conn->prepare($sql_watershed);

                if ($stmt_watershed) {
                    $stmt_watershed->bind_param('i', $watershed_id);
                    if ($stmt_watershed->execute()) {
                        $stmt_watershed->bind_result($watershed_name);
                        $stmt_watershed->fetch();
                        $stmt_watershed->close();
                    }
                }
            }
        }
    }

    // Determine the globe emoticon based on the continent code
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

    return ['continent_icon' => $country_icon, 'watershed_name' => $watershed_name];
}

?>
