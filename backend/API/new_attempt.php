<?php

define('SECRET_KEY', 'g78gh789f4328h79g890aafzxvssfga72gfas');

function encryptId($id) {
    $method = 'AES-256-CBC';
    $key = hash('sha256', SECRET_KEY, true);
    $iv = openssl_random_pseudo_bytes(16);
    $encryptedId = openssl_encrypt($id, $method, $key, 0, $iv);
    return base64_encode($encryptedId . '::' . base64_encode($iv)); 
}

$logDir = __DIR__ . "/logs/new_attempt";
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__.'/logs/new_attempt/php_error.log');
header('Content-Type: application/json');
$f = fopen($logDir . "/log.txt", "a+");

header('Content-Type: application/json');

fwrite($f, "Checking request method...\n");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    fwrite($f, "    Request: POST. Continuing...\n");
    fwrite($f, "----------------NEW OPERATION----------------\n");
    $input = json_decode(file_get_contents('php://input'), true);
    $student_code = $input['student_code'] ?? "";
    $session_code= $input['session_code'] ?? "";
    fwrite($f, "Requesting to create a new attempt for student with ID: '$student_code' in session with ID: '$session_code'\n");
    fwrite($f, "Checking if requirements are met and set...\n");

    // Verifica che i dati siano validi
    if (!empty($student_code) && !empty($session_code)) {
        fwrite($f, "    Checked requirements.\n");
        include './../include/db_connect.php';
        
        $stmt = $conn->prepare("INSERT INTO tentativo(cf_studente, sessione_id, data_tentativo) VALUES (?, ?, current_timestamp())");
        $stmt->bind_param('si', $student_code, $session_code);

        fwrite($f, "Executing query...\n");

        if ($stmt->execute()) {
            fwrite($f, "    Executed.\n");
            $attempt_id = $conn->insert_id;

           $encrypted_attempt_id = encryptId($attempt_id);

            echo json_encode(['success' => true, 'attempt_code' => $encrypted_attempt_id]);
        } else {
            fwrite($f, "    Error in query.\n");
            echo json_encode(['success' => false, 'error' => 'DB insert failed']);
        }
    } else {
        fwrite($f, "    Requirements not met.\n");
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}