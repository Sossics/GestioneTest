<?php

$logDir = __DIR__ . "/logs/change_answer_points";
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__.'/logs/change_answer_points/php_error.log');
header('Content-Type: application/json');
$f = fopen($logDir . "/log.txt", "a+");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    fwrite($f, "----------------NEW OPERATION----------------\n");
    $input = json_decode(file_get_contents('php://input'), true);

    $attempt_id = intval($input['attempt_id']);
    $question_id = intval($input['question_id']);
    $points = trim($input['punteggio']);

    fwrite($f, "Changing points of answer with attemptID=$attempt_id and questionID=$question_id to: $points\n");

    if ($attempt_id > 0 && $question_id > 0 && !empty($points)) {
        include './../include/db_connect.php';
        fwrite($f, "    Checking if row exists. If yes, update.\n");
        $stmt = $conn->prepare("UPDATE risposta SET punteggio = ? WHERE tentativo_id = ? AND domanda_id = ?");
        $stmt->bind_param('sii', $points, $attempt_id, $question_id);
        
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
