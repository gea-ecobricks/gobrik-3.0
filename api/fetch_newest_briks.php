<?php
require_once '../gobrikconn_env.php'; // Include database connection

// Get the request parameters sent by DataTables
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 12;
$ecobriker_id = isset($_POST['ecobriker_id']) ? $_POST['ecobriker_id'] : ''; // Get the ecobriker_id from the request

// Search term (if any)
$searchValue = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';

// Prepare the base SQL query
$sql = "SELECT ecobrick_thumb_photo_url, ecobrick_full_photo_url, weight_g, volume_ml, density, date_logged_ts, location_full, location_watershed, ecobricker_maker, serial_no, status
        FROM tb_ecobricks
        WHERE status != 'not ready'";

// If ecobriker_id is provided, add it to the SQL query to filter results
if (!empty($ecobriker_id)) {
    $sql .= " AND ecobricker_id = ?";
}

// Add search filter if there is a search term
if (!empty($searchValue)) {
    $sql .= " AND (serial_no LIKE ? OR location_full LIKE ? OR ecobricker_maker LIKE ?)";
}

// Count total records before filtering
$totalRecordsQuery = "SELECT COUNT(*) as total FROM tb_ecobricks WHERE status != 'not ready'";
if (!empty($ecobriker_id)) {
    $totalRecordsQuery .= " AND ecobricker_id = '$ecobriker_id'";
}
$totalRecordsResult = $gobrik_conn->query($totalRecordsQuery);
$totalRecords = $totalRecordsResult->fetch_assoc()['total'] ?? 0;

// Prepare the statement for the main query
$sql .= " ORDER BY date_logged_ts DESC LIMIT ?, ?";
$stmt = $gobrik_conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "draw" => $draw,
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => [],
        "error" => "Failed to prepare SQL statement: " . $gobrik_conn->error
    ]);
    exit;
}

// Bind parameters and execute the statement
if (!empty($ecobriker_id) && !empty($searchValue)) {
    $searchTerm = "%" . $searchValue . "%";
    $stmt->bind_param("ssssii", $ecobriker_id, $searchTerm, $searchTerm, $searchTerm, $start, $length);
} elseif (!empty($ecobriker_id)) {
    $stmt->bind_param("sii", $ecobriker_id, $start, $length);
} elseif (!empty($searchValue)) {
    $searchTerm = "%" . $searchValue . "%";
    $stmt->bind_param("sssii", $searchTerm, $searchTerm, $searchTerm, $start, $length);
} else {
    $stmt->bind_param("ii", $start, $length);
}

$stmt->execute();

// Bind the results to variables
$stmt->bind_result(
    $ecobrick_thumb_photo_url,
    $ecobrick_full_photo_url,
    $weight_g,
    $volume_ml,
    $density,
    $date_logged_ts,
    $location_full,
    $location_watershed,
    $ecobricker_maker,
    $serial_no,
    $status
);

$data = [];
while ($stmt->fetch()) {
    // Process the location into $location_brik
    $location_parts = explode(',', $location_full);
    $location_parts = array_map('trim', $location_parts);

    $location_last = $location_parts[count($location_parts) - 1] ?? '';
    $location_third_last = $location_parts[count($location_parts) - 3] ?? '';
    $location_brik = $location_third_last . ', ' . $location_last;

    if (!empty($location_watershed)) {
        $location_brik = $location_watershed . ', ' . $location_brik;
    }

    $serial_url = "brik.php?serial_no=" . urlencode($serial_no);
    $data[] = [
        'ecobrick_thumb_photo_url' => '<img src="' . htmlspecialchars($ecobrick_thumb_photo_url) . '" alt="Ecobrick ' . htmlspecialchars($serial_no) . ' Thumbnail" title="Ecobrick ' . htmlspecialchars($serial_no) . '" class="table-thumbnail">',
        'weight_g' => number_format($weight_g) . ' g',
        'volume_ml' => number_format($volume_ml) . ' ml',
        'density' => number_format($density, 2) . ' g/ml',
        'date_logged_ts' => date("Y-m-d", strtotime($date_logged_ts)),
        'location_brik' => htmlspecialchars($location_brik),
        'status' => htmlspecialchars($status),
        'serial_no' => '<a href="' . htmlspecialchars($serial_url) . '" class="serial-button" data-text="' . htmlspecialchars($serial_no) . '">
                            <span>' . htmlspecialchars($serial_no) . '</span>
                        </a>'
    ];
}

// Get total filtered records
$filteredSql = "SELECT COUNT(*) as total FROM tb_ecobricks WHERE status != 'not ready'";
if (!empty($ecobriker_id)) {
    $filteredSql .= " AND ecobricker_id = '$ecobriker_id'";
}
if (!empty($searchValue)) {
    $filteredSql .= " AND (serial_no LIKE '%$searchValue%' OR location_full LIKE '%$searchValue%' OR ecobricker_maker LIKE '%$searchValue%')";
}
$filteredResult = $gobrik_conn->query($filteredSql);
$totalFilteredRecords = $filteredResult->fetch_assoc()['total'] ?? 0;

// Prepare the JSON response
$response = [
    "draw" => $draw,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalFilteredRecords,
    "data" => $data
];

// Close database connection
$gobrik_conn->close();

// Send the response in JSON format
echo json_encode($response);
?>
