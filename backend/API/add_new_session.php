<?php

$logDir = __DIR__ . "/logs/add_new_session";
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__.'/logs/add_new_session/php_error.log');
header('Content-Type: application/json');
$f = fopen($logDir . "/log.txt", "a+");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    fwrite($f, "----------------NEW OPERATION----------------\n");
    $input = json_decode(file_get_contents('php://input'), true);
    fwrite($f, var_export($input, true));
    $test_id = $input['test_id']??"";
    $classe_id = $input['classe_id']??"";
    $doc_id = $input['doc_id']??"";
    $ora_inizio = $input['ora_inizio']??"";
    $ora_fine = $input['ora_fine']??"";

   //    fwrite($f, "Adding a new class with nome=".$nome." and anno=".$anno);

    if (!empty($test_id) && !empty($classe_id) && !empty($doc_id) && !empty($ora_inizio) && !empty($ora_fine) ) {
        include './../include/db_connect.php';
        fwrite($f, "    Checking if row exists. If yes, update.\n");
        $stmt = $conn->prepare("INSERT INTO sessione (id, test_id, classe_id, cf_docente, data_inizio, data_fine) VALUES (NULL, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sssss', $test_id, $classe_id, $doc_id, $ora_inizio, $ora_fine);
        
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
