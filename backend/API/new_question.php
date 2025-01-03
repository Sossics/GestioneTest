<?php

$logDir = __DIR__ . "/logs/new_question";
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/new_question/php_error.log');
header('Content-Type: application/json');
$f = fopen($logDir . "/log.txt", "a+");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include './../include/db_connect.php';
    fwrite($f, "----------------NEW OPERATION----------------\n");
    $res = "";
    $input = json_decode(file_get_contents('php://input'), true);

    $test_id = intval($input['id']);

    fwrite($f, "Adding new question to Test with ID: $test_id\n");

    $SQL_QUERY = "INSERT INTO domanda(testo, tipo, test_id, punti) VALUES('Inserisci la domanda...', 'APERTA', ?, '0.0')";
    $stmt = $conn->prepare($SQL_QUERY);
    $stmt->bind_param('i', $test_id);
    if($stmt->execute()){
        $last_id = $conn->insert_id;
        $res = <<<HTML
                <div class="d-flex justify-content-between align-items-center">
                    <input type="text" class="form-control fs-4 border-0 border-bottom text-left" id="titolo"
                        name="titolo" 
                        value="Inserisci la domanda..."
                        style="background: transparent; outline: none;"
                        onblur="aggiornaDomanda('{$last_id}', this.value)"
                    >
                    <div class="ms-3">
                        <label>Tipo di domanda:</label>
                        <select id="question_type" name="question_type" onchange="aggiornaTipoDomanda('{$last_id}', this.value)">
                            <option value="APERTA" selected >Domanda Aperta</option>
                            <option value="MULTIPLA"  >Scelta Multipla</option>
                        </select>
                    </div>
                </div>
                <div class='question-text'>
                    <label for='answer_{$last_id}'>Risposta:</label>
                    <textarea id='answer_{$last_id}' class='form-control' rows='4' placeholder='Scrivi la tua risposta' disabled></textarea>
                </div>
            HTML;
    }

    echo json_encode(['success' => true, 'error' => '', 'HTML_CODE' => $res, 'newQuestionID' => $last_id]);
    

} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method', 'HTML_CODE' => null]);
}
