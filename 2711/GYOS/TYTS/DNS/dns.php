<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'false',
        'message' => 'POST required',
    ], JSON_UNESCAPED_SLASHES);
    exit;
}

$configFile = __DIR__ . DIRECTORY_SEPARATOR . 'providers.php';
if (!is_file($configFile)) {
    http_response_code(503);
    echo json_encode([
        'status' => 'false',
        'message' => 'Provider configuration unavailable',
    ], JSON_UNESCAPED_SLASHES);
    exit;
}

$config = require $configFile;
if (!is_array($config)) {
    http_response_code(500);
    echo json_encode([
        'status' => 'false',
        'message' => 'Invalid provider configuration',
    ], JSON_UNESCAPED_SLASHES);
    exit;
}

$username = trim((string) ($_POST['u'] ?? ''));
if ($username === '') {
    http_response_code(400);
    echo json_encode([
        'status' => 'false',
        'message' => 'Username required',
    ], JSON_UNESCAPED_SLASHES);
    exit;
}

$serverUrl = null;

// Exact username mapping has highest priority.
foreach (($config['users'] ?? []) as $configuredUser => $url) {
    if (strcasecmp((string) $configuredUser, $username) === 0) {
        $serverUrl = (string) $url;
        break;
    }
}

// Optional prefix mapping supports groups of provider usernames.
if ($serverUrl === null) {
    foreach (($config['prefixes'] ?? []) as $prefix => $url) {
        if ($prefix !== '' && stripos($username, (string) $prefix) === 0) {
            $serverUrl = (string) $url;
            break;
        }
    }
}

if ($serverUrl === null && isset($config['default'])) {
    $serverUrl = (string) $config['default'];
}

$serverUrl = rtrim(trim((string) $serverUrl), '/');
if (!filter_var($serverUrl, FILTER_VALIDATE_URL)
    || !in_array(strtolower((string) parse_url($serverUrl, PHP_URL_SCHEME)), ['http', 'https'], true)) {
    http_response_code(404);
    echo json_encode([
        'status' => 'false',
        'message' => 'No provider found',
    ], JSON_UNESCAPED_SLASHES);
    exit;
}

// XOLO requires status, su, ndd, and sc to exist. In this APK, su is the
// server URL; ndd is read but unused; sc comparison result is not enforced.
echo json_encode([
    'status' => 'true',
    'su' => $serverUrl,
    'ndd' => '',
    'sc' => '',
], JSON_UNESCAPED_SLASHES);

