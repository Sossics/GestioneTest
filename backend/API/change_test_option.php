<?php

$logDir = __DIR__ . "/logs/change_test_option";
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__.'/logs/change_test_option/php_error.log');
header('Content-Type: application/json');
$f = fopen($logDir . "/log.txt", "a+");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    fwrite($f, "----------------NEW OPERATION----------------\n");
    $input = json_decode(file_get_contents('php://input'), true);

    $id = intval($input['id']);
    $opzione = trim($input['opzione']);

    fwrite($f, "Changing text of option with id=$id to: $opzione\n");

    if ($id > 0 && !empty($opzione)) {
        include './../include/db_connect.php';
        fwrite($f, "    Checking if row exists. If yes, update.\n");
        $stmt = $conn->prepare("UPDATE opzioni_domanda SET testo_opzione = ? WHERE id = ?");
        $stmt->bind_param('si', $opzione, $id);
        
        if ($stmt->execute()) {
            fwrite($f, "    Row detected. Updating...\n");
            fwrite($f, "Updated.\n");
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'DB update failed']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
