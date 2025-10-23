<?php

date_default_timezone_set('Europe/Berlin');

// Load .env
if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

function find_max_stop($data) {
    $max_stop = 0;
    if (isset($data['time']['stop'])) {
        $max_stop = max($max_stop, $data['time']['stop'] / 1000);
    }
    if (isset($data['children'])) {
        foreach ($data['children'] as $child) {
            $max_stop = max($max_stop, find_max_stop($child));
        }
    }
    return $max_stop;
}

// Get parameters
$project = $_GET['project'] ?? '';
$state = $_GET['state'] ?? '';
$api_key = $_GET['api_key'] ?? '';

if (!$project || !$state) {
    http_response_code(400);
    echo "Missing parameters: project, state\n";
    exit;
}

if (!isset($_ENV['API_KEY']) || $api_key !== $_ENV['API_KEY']) {
    http_response_code(401);
    echo "Invalid API key\n";
    exit;
}

// Directories
$zip_dir = "zip/{$project}-{$state}";
$reports_dir = "reports/{$project}-{$state}";

// Create directories if not exist
if (!is_dir($zip_dir)) {
    mkdir($zip_dir, 0755, true);
}
if (!is_dir($reports_dir)) {
    mkdir($reports_dir, 0755, true);
}

// Handle file upload
if (!isset($_FILES['zip_file'])) {
    http_response_code(400);
    echo "No zip file uploaded\n";
    exit;
}

$file = $_FILES['zip_file'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo "File upload error\n";
    exit;
}

// Move uploaded file to temp
$temp_zip = "{$zip_dir}/temp.zip";
if (!move_uploaded_file($file['tmp_name'], $temp_zip)) {
    http_response_code(500);
    echo "Failed to save zip file\n";
    exit;
}

// Open zip and get timestamp from timeline.json
$zip = new ZipArchive;
if ($zip->open($temp_zip) === TRUE) {
    $timeline_content = $zip->getFromName('data/timeline.json');
    if ($timeline_content === false) {
        http_response_code(400);
        echo "timeline.json not found in zip\n";
        $zip->close();
        exit;
    }
    $timeline_data = json_decode($timeline_content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo "Invalid timeline.json: " . json_last_error_msg() . "\n";
        $zip->close();
        exit;
    }
    $timestamp = find_max_stop($timeline_data);
    if ($timestamp == 0) {
        http_response_code(400);
        echo "No stop time found in timeline.json\n";
        $zip->close();
        exit;
    }
    $human_date = date('Y-m-d_H-i-s', (int)$timestamp);

    $zip_file_path = "{$zip_dir}/{$human_date}.zip";
    $extract_dir = "{$reports_dir}/{$human_date}";

    // Rename temp to final name
    if (!rename($temp_zip, $zip_file_path)) {
        http_response_code(500);
        echo "Failed to rename zip file\n";
        $zip->close();
        exit;
    }

    // Create extract dir
    if (!is_dir($extract_dir)) {
        mkdir($extract_dir, 0755, true);
    }

    // Extract
    $zip->extractTo($extract_dir);
    $zip->close();
    echo "Upload and extraction successful\n";
} else {
    http_response_code(500);
    echo "Failed to open zip\n";
}

?>