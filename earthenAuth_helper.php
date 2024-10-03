<?php



/**
 * Fetch the GEA status of a user from the tb_ecobrickers table using buwana_id.
 *
 * @param int $buwana_id The ID of the user.
 * @return string|null The GEA status of the user or null if not found.
 */

function getGEA_status($buwana_id) {
    // Include the database connection if not already included
    global $gobrik_conn; // Use the existing connection variable

    // Prepare the SQL statement to fetch the gea_status
    $sql = "SELECT gea_status FROM tb_ecobrickers WHERE buwana_id = ?";
    $stmt = $gobrik_conn->prepare($sql);

    // Check if the statement was prepared successfully
    if ($stmt) {
        $stmt->bind_param("i", $buwana_id);
        $stmt->execute();
        $stmt->bind_result($gea_status);
        $stmt->fetch();
        $stmt->close();

        // Return the fetched gea_status
        return $gea_status;
    } else {
        // Log error or handle it appropriately
        error_log("Database error: " . $gobrik_conn->error);
        return null;
    }
}





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


function getWatershedName($buwana_conn, $buwana_id) {
    $watershed_name = '';


    // Query to get the user's watershed name from users_tb
    $sql_watershed = "SELECT location_watershed FROM users_tb WHERE buwana_id = ?";
    $stmt_watershed = $buwana_conn->prepare($sql_watershed);

    if ($stmt_watershed) {
        $stmt_watershed->bind_param('s', $buwana_id); // Assuming buwana_id is a string
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



function getCommunityName($buwana_conn, $buwana_id) {
    $community_name = ''; // Initialize the community name variable

    // Step 1: Query to get the user's community_id from users_tb
    $sql_community_id = "SELECT community_id FROM users_tb WHERE buwana_id = ?";
    $stmt_community_id = $buwana_conn->prepare($sql_community_id);

    if ($stmt_community_id) {
        $stmt_community_id->bind_param('i', $buwana_id); // Assuming buwana_id is an integer now
        if ($stmt_community_id->execute()) {
            $stmt_community_id->bind_result($community_id);
            $stmt_community_id->fetch();
            $stmt_community_id->close();

            // Step 2: Use the retrieved community_id to get the com_name from communities_tb
            if (!empty($community_id)) {
                $sql_community_name = "SELECT com_name FROM communities_tb WHERE com_id = ?";
                $stmt_community_name = $buwana_conn->prepare($sql_community_name);

                if ($stmt_community_name) {
                    $stmt_community_name->bind_param('i', $community_id); // Assuming community_id is an integer
                    if ($stmt_community_name->execute()) {
                        $stmt_community_name->bind_result($community_name);
                        $stmt_community_name->fetch();
                        $stmt_community_name->close();
                    }
                }
            }
        }
    }

    // If $community_name is still empty or null, set a default value
    if (empty($community_name)) {
        $community_name = 'Unknown Community'; // Default value if no valid community name is found
    }

    return $community_name; // Return the correct variable
}

startSecureSession();
error_reporting(E_ALL);
ini_set('display_errors', 1);


// Initialize user variables
$first_name = '';
$buwana_id = '';
$watershed_id = '';
$location_watershed = '';
$is_logged_in = isLoggedIn(); // Check if the user is logged in using the helper function



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
