<?php

$logDir = __DIR__ . "/logs/toggle_correct_option";
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/toggle_correct_option/php_error.log');
header('Content-Type: application/json');
$f = fopen($logDir . "/log.txt", "a+");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include './../include/db_connect.php';
    fwrite($f, "----------------NEW OPERATION----------------\n");
    
    
    $input = json_decode(file_get_contents('php://input'), true);
    $option_id = intval($input['id']);
    $is_checked_js = intval($input['isChecked']);
    $is_checked = (isset($is_checked_js) ? (($is_checked_js == "true" || $is_checked_js == true || $is_checked_js == 1) ? 1 : 0) : 0);
    
    fwrite($f, "Setting value is correct of option with ID: $option_id to: ".($is_checked ? 'TRUE' : 'FALSE')."\n");
    
    fwrite($f, "    Checking if it exists in DB, if yes update.\n");
    $stmt = $conn->prepare('UPDATE opzioni_domanda SET corretta=? WHERE id=?');
    $stmt->bind_param('ii', $is_checked,$option_id);
    if($stmt->execute()){
        fwrite($f, "Updated.\n");
        echo json_encode(['success' => true, 'is_checked' => ($is_checked ? true : false), 'error' => null]);
    }else{
        echo json_encode(['success' => false, 'error' => 'SQL error']);
    }

} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
