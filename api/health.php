<?php
// api/health.php — POST endpoint for anonymous health pings
// Deploy to: yourserver.com/api/health.php
// Access: POST only

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error' => 'POST only']); exit; }

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) { http_response_code(400); echo json_encode(['error' => 'Invalid JSON']); exit; }

// Append to log file — simple flat file, no database needed
$logFile = __DIR__ . '/health.log';
$entry = [
    'ts'     => date('c'),
    'app'    => $data['app_version'] ?? '?',
    'game'   => $data['game_version'] ?? '?',
    'ok'     => $data['offsets_working'] ?? false,
    'error'  => $data['error'] ?? null,
    'ip'     => $_SERVER['REMOTE_ADDR'] ?? '?',
];
file_put_contents($logFile, json_encode($entry) . "\n", FILE_APPEND | LOCK_EX);

// Return stats for dashboard
$lines = file_exists($logFile) ? file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
$total = count($lines);
$working = 0;
foreach ($lines as $line) {
    $e = json_decode($line, true);
    if ($e && ($e['ok'] ?? false)) $working++;
}

echo json_encode([
    'status' => 'ok',
    'stats'  => ['total_pings' => $total, 'offsets_working' => $working, 'health_pct' => $total > 0 ? round($working/$total*100, 1) : 0],
]);
