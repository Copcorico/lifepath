<?php
/**
 * GitHub Webhook Handler
 * Automatically pulls changes when a push event is received from GitHub
 */

// Configuration
define('SECRET_TOKEN', getenv('WEBHOOK_SECRET') ?: 'your-secret-token-here');
define('REPO_PATH', __DIR__);
define('LOG_FILE', __DIR__ . '/webhook.log');

// Function to log messages
function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message\n";
    file_put_contents(LOG_FILE, $logEntry, FILE_APPEND);
}

// Function to execute shell commands safely
function executeCommand($command) {
    $output = [];
    $returnCode = 0;
    exec($command . ' 2>&1', $output, $returnCode);
    return [
        'output' => implode("\n", $output),
        'code' => $returnCode
    ];
}

// Get the request payload
$payload = file_get_contents('php://input');
$headers = getallheaders();

// Verify the request is from GitHub
if (!isset($headers['X-Hub-Signature-256']) && !isset($headers['X-Hub-Signature'])) {
    http_response_code(403);
    logMessage('ERROR: Missing signature header');
    die('Forbidden: Missing signature');
}

// Verify the signature
$signature = isset($headers['X-Hub-Signature-256']) 
    ? $headers['X-Hub-Signature-256'] 
    : $headers['X-Hub-Signature'];

$algo = isset($headers['X-Hub-Signature-256']) ? 'sha256' : 'sha1';
$expectedSignature = $algo . '=' . hash_hmac($algo, $payload, SECRET_TOKEN);

if (!hash_equals($expectedSignature, $signature)) {
    http_response_code(403);
    logMessage('ERROR: Invalid signature');
    die('Forbidden: Invalid signature');
}

// Parse the payload
$data = json_decode($payload, true);

// Check if this is a push event
if (!isset($headers['X-GitHub-Event']) || $headers['X-GitHub-Event'] !== 'push') {
    http_response_code(200);
    logMessage('INFO: Received non-push event: ' . ($headers['X-GitHub-Event'] ?? 'unknown'));
    echo json_encode(['status' => 'ignored', 'message' => 'Not a push event']);
    exit;
}

logMessage('INFO: Received push event from GitHub');
logMessage('INFO: Repository: ' . ($data['repository']['full_name'] ?? 'unknown'));
logMessage('INFO: Ref: ' . ($data['ref'] ?? 'unknown'));
logMessage('INFO: Pusher: ' . ($data['pusher']['name'] ?? 'unknown'));

// Change to repository directory
chdir(REPO_PATH);

// Execute git fetch
logMessage('INFO: Starting git fetch...');
$fetchResult = executeCommand('git fetch origin 2>&1');
logMessage('FETCH OUTPUT: ' . $fetchResult['output']);

if ($fetchResult['code'] !== 0) {
    http_response_code(500);
    logMessage('ERROR: Git fetch failed with code ' . $fetchResult['code']);
    echo json_encode(['status' => 'error', 'message' => 'Git fetch failed', 'details' => $fetchResult['output']]);
    exit;
}

// Execute git pull
logMessage('INFO: Starting git pull...');
$pullResult = executeCommand('git pull origin $(git rev-parse --abbrev-ref HEAD) 2>&1');
logMessage('PULL OUTPUT: ' . $pullResult['output']);

if ($pullResult['code'] !== 0) {
    http_response_code(500);
    logMessage('ERROR: Git pull failed with code ' . $pullResult['code']);
    echo json_encode(['status' => 'error', 'message' => 'Git pull failed', 'details' => $pullResult['output']]);
    exit;
}

// Success
logMessage('SUCCESS: Repository updated successfully');
http_response_code(200);
echo json_encode([
    'status' => 'success',
    'message' => 'Repository updated successfully',
    'fetch' => $fetchResult['output'],
    'pull' => $pullResult['output']
]);
