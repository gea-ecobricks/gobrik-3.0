<?php
// Replace with your GitHub personal access token
define('GITHUB_TOKEN', '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');

// Set the owner and repository name
define('REPO_OWNER', 'gea-ecobricks');
define('REPO_NAME', 'gobrik-3.0');

// Function to make API requests to GitHub
function githubApiRequest($endpoint) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/repos/' . REPO_OWNER . '/' . REPO_NAME . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: token ' . GITHUB_TOKEN,
        'User-Agent: GoBrik-3.0-Middleware' // GitHub requires a user agent
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

// Endpoint to fetch repository contents
if (isset($_GET['path'])) {
    $path = $_GET['path'];
    $contents = githubApiRequest('/contents/' . $path);

    if (!empty($contents)) {
        header('Content-Type: application/json');
        echo json_encode($contents);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'File or directory not found.']);
    }
} else {
    // Default action: Fetch the root structure of the repo
    $structure = githubApiRequest('/contents/');

    if (!empty($structure)) {
        header('Content-Type: application/json');
        echo json_encode($structure);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Repository structure not found.']);
    }
}
?>
