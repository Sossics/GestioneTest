<?php

$logDir = __DIR__ . "/logs/change_test_question_type";
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__.'/logs/change_test_question_type/php_error.log');
header('Content-Type: application/json');
$f = fopen($logDir . "/log.txt", "a+");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    fwrite($f, "----------------NEW OPERATION----------------\n");
    $input = json_decode(file_get_contents('php://input'), true);

    $id = intval($input['id']);
    $tipo = trim($input['tipo']);

    fwrite($f, "Changing type of question with id=$id to: $tipo\n");

    if ($id > 0 && !empty($tipo)) {
        include './../include/db_connect.php';
        fwrite($f, "    Checking if row exists. If yes, update.\n");
        $stmt = $conn->prepare("UPDATE domanda SET tipo = ? WHERE id = ?");
        $stmt->bind_param('si', $tipo, $id);
        
        if ($stmt->execute()) {
            fwrite($f, "    Row detected. Updating...\n");
            fwrite($f, "Updated.\n");
            fwrite($f, "Checking new question type for next executions...\n");
            switch($tipo){
                case "MULTIPLA":
                    fwrite($f, "    Type is MULTIPLA, no other executions needed.\n");
                    break;
                    //Lo esegue anche se il cambiamento e' MULTIPLA > MULTIPLA (cercare ottimizzazioni: ricavarsi il tipo precedente)
                case "APERTA":
                    fwrite($f, "    Type is APERTA, removing previously associated question options...\n");
                    $stmt = $conn->prepare("DELETE FROM opzioni_domanda WHERE domanda_id=?");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $rows_deleted = $stmt->affected_rows;
                    fwrite($f, "    $rows_deleted question options have been eliminated for question with id: $id\n");
                break;
            }
            fwrite($f, " Ending...\n");
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
