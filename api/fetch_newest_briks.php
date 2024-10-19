<?php
require_once '../gobrikconn_env.php'; // Include database connection

// Get the request parameters sent by DataTables
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 12;
$ecobricker_id = isset($_POST['ecobricker_id']) ? $_POST['ecobricker_id'] : ''; // Get the ecobricker_id from the request
$searchValue = isset($_POST['search']['value']) ? $_POST['search']['value'] : ''; // Search term, if any

// Prepare the base SQL query
$sql = "SELECT ecobrick_thumb_photo_url, ecobrick_full_photo_url, weight_g, volume_ml, density, date_logged_ts, location_full, location_watershed, ecobricker_maker, serial_no, status
        FROM tb_ecobricks
        WHERE status != 'not ready'";

// Add filtering for ecobricker_id and search term
$bindTypes = '';
$bindValues = [];

// If ecobricker_id is provided, add it to the filter
if (!empty($ecobricker_id)) {
    $sql .= " AND maker_id = ?";
    $bindTypes .= 's';
    $bindValues[] = $ecobricker_id;
}

// Add search filter if there is a search term
if (!empty($searchValue)) {
    $sql .= " AND (serial_no LIKE ? OR location_full LIKE ? OR ecobricker_maker LIKE ?)";
    $bindTypes .= 'sss';
    $searchTerm = '%' . $searchValue . '%';
    $bindValues[] = $searchTerm;
    $bindValues[] = $searchTerm;
    $bindValues[] = $searchTerm;
}

// Count total records before filtering
$totalRecordsQuery = "SELECT COUNT(*) as total FROM tb_ecobricks WHERE status != 'not ready'";
if (!empty($ecobricker_id)) {
    $totalRecordsQuery .= " AND maker_id = ?";
    $totalRecordsStmt = $gobrik_conn->prepare($totalRecordsQuery);
    $totalRecordsStmt->bind_param('s', $ecobricker_id);
} else {
    $totalRecordsStmt = $gobrik_conn->prepare($totalRecordsQuery);
}
$totalRecordsStmt->execute();
$totalRecordsResult = $totalRecordsStmt->get_result();
$totalRecords = $totalRecordsResult->fetch_assoc()['total'] ?? 0;

// Prepare the main query with pagination
$sql .= " ORDER BY date_logged_ts DESC LIMIT ?, ?";
$bindTypes .= 'ii';
$bindValues[] = $start;
$bindValues[] = $length;

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

// Bind the parameters dynamically
$stmt->bind_param($bindTypes, ...$bindValues);
$stmt->execute();
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

// Fetch the results into the data array
$data = [];
while ($stmt->fetch()) {
    // Process location data
    $location_parts = array_map('trim', explode(',', $location_full));
    $location_last = $location_parts[count($location_parts) - 1] ?? '';
    $location_third_last = $location_parts[count($location_parts) - 3] ?? '';
    $location_brik = $location_third_last . ', ' . $location_last;

    if (!empty($location_watershed)) {
        $location_brik = $location_watershed . ', ' . $location_brik;
    }

    $serial_url = "brik.php?serial_no=" . urlencode($serial_no);

    $data[] = [
        'ecobrick_thumb_photo_url' => '<img src="' . htmlspecialchars($ecobrick_thumb_photo_url) . '"
            alt="Ecobrick ' . htmlspecialchars($serial_no) . ' Thumbnail"
            title="Ecobrick ' . htmlspecialchars($serial_no) . '"
            class="table-thumbnail"
            onclick="ecobrickPreview(\'' . htmlspecialchars($ecobrick_full_photo_url) . '\', \'' . htmlspecialchars($serial_no) . '\', \'' . htmlspecialchars($weight_g) . ' g\', \'' . htmlspecialchars($ecobricker_maker) . '\', \'' . htmlspecialchars($location_brik) . '\')">',
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

// Count filtered records for pagination purposes
$filteredSql = "SELECT COUNT(*) as total FROM tb_ecobricks WHERE status != 'not ready'";
if (!empty($ecobricker_id)) {
    $filteredSql .= " AND maker_id = ?";
}
if (!empty($searchValue)) {
    $filteredSql .= " AND (serial_no LIKE ? OR location_full LIKE ? OR ecobricker_maker LIKE ?)";
}
$filteredStmt = $gobrik_conn->prepare($filteredSql);
if (!empty($ecobricker_id) && !empty($searchValue)) {
    $filteredStmt->bind_param('ssss', $ecobricker_id, $searchTerm, $searchTerm, $searchTerm);
} elseif (!empty($ecobricker_id)) {
    $filteredStmt->bind_param('s', $ecobricker_id);
} elseif (!empty($searchValue)) {
    $filteredStmt->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);
}
$filteredStmt->execute();
$filteredResult = $filteredStmt->get_result();
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
