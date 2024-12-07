<?php

$logDir = __DIR__ . "/logs/delete_option";
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/delete_option/php_error.log');
header('Content-Type: application/json');
$f = fopen($logDir . "/log.txt", "a+");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include './../include/db_connect.php';
    fwrite($f, "----------------NEW OPERATION----------------\n");

    $input = json_decode(file_get_contents('php://input'), true);

    $option_id = intval($input['id']);

    fwrite($f, "Deleting option with ID: $option_id\n");
    fwrite($f, "    Checking if option exists in DB, if yes delete.\n");
    
    $stmt = $conn->prepare('DELETE FROM opzioni_domanda WHERE id=?');
    $stmt->bind_param('i', $option_id);
    if($stmt->execute()){
        fwrite($f, "Deleted.\n");
        echo json_encode(['success' => true, 'error' => null]);
    }else{
        echo json_encode(['success' => false, 'error' => 'SQL error']);
    }

} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
