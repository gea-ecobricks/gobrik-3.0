<?php
require_once '../gobrikconn_env.php'; // Include database connection

// Get the request parameters sent by DataTables
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 12; // Number of records to fetch per page

// Search term (if any)
$searchValue = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';

// Prepare the base SQL query
$sql = "SELECT ecobrick_thumb_photo_url, ecobrick_full_photo_url, weight_g, volume_ml, density, date_logged_ts, location_full, location_watershed, ecobricker_maker, serial_no, status
        FROM tb_ecobricks
        WHERE status != 'not ready'";

// Add search filter if there is a search term
if (!empty($searchValue)) {
    $sql .= " AND (serial_no LIKE ? OR location_full LIKE ? OR ecobricker_maker LIKE ?)";
}

// Count total records before filtering
$totalRecordsResult = $gobrik_conn->query("SELECT COUNT(*) as total FROM tb_ecobricks WHERE status != 'not ready'");
$totalRecords = $totalRecordsResult->fetch_assoc()['total'];

// Prepare the statement for the main query
$stmt = $gobrik_conn->prepare($sql . " ORDER BY date_logged_ts DESC LIMIT ?, ?");
if (!empty($searchValue)) {
    $searchTerm = "%" . $searchValue . "%";
    $stmt->bind_param("sssii", $searchTerm, $searchTerm, $searchTerm, $start, $length);
} else {
    $stmt->bind_param("ii", $start, $length);
}

$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    // Process the location into $location_brik
    $location_parts = explode(',', $row['location_full']);
    $location_parts = array_map('trim', $location_parts);

    $location_last = $location_parts[count($location_parts) - 1] ?? '';
    $location_third_last = $location_parts[count($location_parts) - 3] ?? '';
    $location_brik = $location_third_last . ', ' . $location_last;

    if (!empty($row['location_watershed'])) {
        $location_brik = $row['location_watershed'] . ', ' . $location_brik;
    }

    $data[] = [
        'ecobrick_thumb_photo_url' => '<img src="' . htmlspecialchars($row['ecobrick_thumb_photo_url']) . '" alt="Ecobrick ' . htmlspecialchars($row['serial_no']) . ' Thumbnail" title="Ecobrick ' . htmlspecialchars($row['serial_no']) . '" class="table-thumbnail">',
        'weight_g' => number_format($row['weight_g']) . ' g',
        'volume_ml' => number_format($row['volume_ml']) . ' ml',
        'density' => number_format($row['density'], 2) . ' g/ml',
        'date_logged_ts' => date("Y-m-d", strtotime($row['date_logged_ts'])),
        'location_brik' => htmlspecialchars($location_brik),
        'status' => htmlspecialchars($row['status']),
        'serial_no' => '<button class="serial-button" data-text="' . htmlspecialchars($row['serial_no']) . '"><a href="brik.php?serial_no=' . htmlspecialchars($row['serial_no']) . '">' . htmlspecialchars($row['serial_no']) . '</a></button>'
    ];
}

// Get total filtered records
$totalFilteredRecords = $gobrik_conn->query("SELECT COUNT(*) as total FROM tb_ecobricks WHERE status != 'not ready'" . (!empty($searchValue) ? " AND (serial_no LIKE '%$searchValue%' OR location_full LIKE '%$searchValue%' OR ecobricker_maker LIKE '%$searchValue%')" : ""))->fetch_assoc()['total'];

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