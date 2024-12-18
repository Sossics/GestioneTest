<?php

define('SECRET_KEY', 'g78gh789f4328h79g890aafzxvssfga72gfas');

function decryptId($encrypted) {
    $method = 'AES-256-CBC';
    $key = hash('sha256', SECRET_KEY, true);
    list($encryptedData, $iv) = explode('::', base64_decode($encrypted), 2);
    $iv = base64_decode($iv);
    $id = openssl_decrypt($encryptedData, $method, $key, 0, $iv);
    return $id;
}

$logDir = __DIR__ . "/logs/save_answers";
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/save_answers/php_error.log');
header('Content-Type: application/json');
$f = fopen($logDir . "/log.txt", "a+");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include './../include/db_connect.php';
    fwrite($f, "----------------NEW OPERATION----------------\n");
    $res = "";
    $input = json_decode(file_get_contents('php://input'), true);

    //ID del test
    $testID = $input['testID'];

    //ID dello studente (Codice Fiscale)
    $studentID = $input['studentID'];

    //ID del tentativo
    $attemptID = (isset($input['attemptID']) ? decryptId($input['attemptID']) : null);

    //Data del form (Per le multiple viene restituito `'question_13' => 'on'`,
    //per le domande aperte invece `'answer_1' => 'a'`)
    //dove, ad esempio, il 13 sta per l'ID della domanda e cosi anche per l'1
    $data = $input['data'];

    fwrite($f, "Gotten data: ".var_export($input, true)."\n");

    fwrite($f, "Saving results of test ID: $testID attempted (ID: $attemptID) by the student ID: $studentID\n");

    foreach ($data as $key => $answer) {
        $parts = explode('_', $key);
        
        if ($parts[0] === 'question') {
            $questionID = $parts[1];
            $optionID = $parts[2];

            $stmt = $conn->prepare("SELECT id FROM opzioni_domanda WHERE domanda_id = ? AND corretta = 1");
            $stmt->bind_param('i', $questionID);
            $stmt->execute();
            $result = $stmt->get_result();
            $correctOptions = $result->fetch_all(MYSQLI_ASSOC);

            $correctOptionIDs = array_column($correctOptions, 'id');
            $totalCorrectOptions = count($correctOptionIDs);

            $selectedCorrectOptions = in_array($optionID, $correctOptionIDs) ? 1 : 0;

            $stmt = $conn->prepare("SELECT punti FROM domanda WHERE id = ?");
            $stmt->bind_param('i', $questionID);
            $stmt->execute();
            $result = $stmt->get_result();
            $question = $result->fetch_assoc();
            $maxPoints = $question['punti'];

            $score = 0;
            if ($totalCorrectOptions > 0) {
                $score = ($selectedCorrectOptions / $totalCorrectOptions) * $maxPoints;
            }

            $stmt = $conn->prepare("INSERT INTO risposta (tentativo_id, domanda_id, risposta_multipla_id, punteggio) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('iisi', $attemptID, $questionID, $optionID, $score);
            if ($stmt->execute()) {
                fwrite($f, "Saved multiple choice answer for question $questionID with partial score $score\n");
            } else {
                fwrite($f, "Error saving multiple choice answer for question $questionID\n");
            }
        } elseif ($parts[0] === 'answer') {
            $questionID = $parts[1];

            $score = 0;

            $stmt = $conn->prepare("INSERT INTO risposta (tentativo_id, domanda_id, risposta_aperta, punteggio) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('iisi', $attemptID, $questionID, $answer, $score);
            if ($stmt->execute()) {
                fwrite($f, "Saved open answer for question $questionID with score $score\n");
            } else {
                fwrite($f, "Error saving open answer for question $questionID\n");
            }
        }
    }

    echo json_encode(['success' => true, 'message' => 'Answers saved successfully']);

} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
