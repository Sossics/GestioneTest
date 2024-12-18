<?php

$logDir = __DIR__ . "/logs/save_config";
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/save_config/php_error.log');
header('Content-Type: application/json');
$f = fopen($logDir . "/log.txt", "a+");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include './../include/db_connect.php';
    fwrite($f, "----------------NEW OPERATION----------------\n");

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['data'])) {
        fwrite($f, "Invalid payload format\n");
        echo json_encode(['success' => false, 'error' => 'Invalid payload']);
        exit;
    }

    $data = $input['data'];
    $sessionID = isset($data['session_id']) ? $data['session_id'] : null;
    $attemptVisibility = isset($data['attempt_visibility']) ? (int)$data['attempt_visibility'] : null;
    $testStatus = isset($data['test_status']) ? (int)$data['test_status'] : null;
    $maxAttempts = isset($data['max_attempts']) ? (int)$data['max_attempts'] : null;

    if (!$sessionID || is_null($attemptVisibility) || is_null($testStatus) || is_null($maxAttempts)) {
        fwrite($f, "Invalid data: ".var_export($data, true)."\n");
        echo json_encode(['success' => false, 'error' => 'Missing or invalid parameters']);
        exit;
    }

    fwrite($f, "Received data: " . var_export($data, true) . "\n");

    $SQL_query = "UPDATE sessione 
                  SET visibilita_tentativi = ?, svolgibile = ?, max_tentativi_ammessi = ?
                  WHERE id = ?";
    $stmt = $conn->prepare($SQL_query);
    if (!$stmt) {
        fwrite($f, "SQL Error: " . $conn->error . "\n");
        echo json_encode(['success' => false, 'error' => 'Database error']);
        exit;
    }

    $stmt->bind_param("iiii", $attemptVisibility, $testStatus, $maxAttempts, $sessionID);
    if ($stmt->execute()) {
        fwrite($f, "Configuration updated successfully for session ID: $sessionID\n");
        echo json_encode(['success' => true, 'message' => 'Configurazione salvata con successo']);
    } else {
        fwrite($f, "Error updating configuration: " . $stmt->error . "\n");
        echo json_encode(['success' => false, 'error' => 'Error updating configuration']);
    }
    $stmt->close();

} else {
    fwrite($f, "Invalid request method\n");
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
