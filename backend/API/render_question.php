<?php

$logDir = __DIR__ . "/logs/render_question";
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/render_question/php_error.log');
header('Content-Type: application/json');
$f = fopen($logDir . "/log.txt", "a+");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include './../include/db_connect.php';
    fwrite($f, "----------------NEW OPERATION----------------\n");
    $res = "";
    $input = json_decode(file_get_contents('php://input'), true);

    $question_id = intval($input['id']);

    fwrite($f, "Rendering HTML for question with ID: $question_id\n");

    // Domande fetch SQL
    $SQL_query_domande = "SELECT id, testo, tipo
                        FROM domanda
                        WHERE id = ?";
    $stmt_domande = $conn->prepare($SQL_query_domande);
    $stmt_domande->bind_param("i", $question_id);
    $stmt_domande->execute();
    $result_domande = $stmt_domande->get_result();

    if ($result_domande->num_rows > 0) {
        $row_domanda = $result_domande->fetch_assoc();

        $res .= '  <div class="d-flex justify-content-between align-items-center">
                <input type="text" class="form-control fs-4 border-0 border-bottom text-left" id="titolo"
                    name="titolo" 
                    value="' . htmlspecialchars($row_domanda['testo']) . '"
                    style="background: transparent; outline: none;"
                    onblur="aggiornaDomanda(\'' . $row_domanda['id'] . '\', this.value)"
                >
                <div class="ms-3">
                    <label>Tipo di domanda:</label>
                    <select id="question_type" name="question_type" onchange="aggiornaTipoDomanda(' . $row_domanda['id'] . ', this.value)">
                        <option value="APERTA" ' . (($row_domanda['tipo'] == "APERTA") ? "selected" : "") . ' >Domanda Aperta</option>
                        <option value="MULTIPLA" ' . (($row_domanda['tipo'] == "MULTIPLA") ? "selected" : "") . ' >Scelta Multipla</option>
                    </select>
                </div>
                <button class="m-3 custom-btn" onclick="eliminaDomanda(event, \''.$row_domanda['id'].'\', this)">X</button>
            </div>';

        // tipo "APERTA"
        if ($row_domanda['tipo'] == 'APERTA') {
            $res .= "<div class='question-text'>
                <label for='answer_{$row_domanda['id']}'>Risposta:</label>
                <textarea id='answer_{$row_domanda['id']}' class='form-control' rows='4' placeholder='Scrivi la tua risposta' disabled></textarea>
                </div>";
        }

        // tipo "SCELTA_MULTIPLA"
        if ($row_domanda['tipo'] == 'MULTIPLA') {
            // Opzioni
            $SQL_query_opzioni = "SELECT * FROM opzioni_domanda WHERE domanda_id = ?";
            $stmt_opzioni = $conn->prepare($SQL_query_opzioni);
            $stmt_opzioni->bind_param("i", $question_id);
            $stmt_opzioni->execute();
            $result_opzioni = $stmt_opzioni->get_result();

            $res .= "<div class='options-list'>";
            while ($row_opzione = $result_opzioni->fetch_assoc()) {
                $res .= "<div class='form-check'>
                    <input class='form-check-input' type='radio' name='question_{$row_domanda['id']}' id='option_{$row_opzione['id']}' disabled>
                    <input type='text' class='border-0 border-bottom text-left' id='titolo'
            name='question_" . $row_domanda['id'] . "'
            id='option_" . $row_opzione['id'] . "' 
            value='" . $row_opzione['testo_opzione'] . "'
            style='background: transparent; outline: none;'
            onblur='aggiornaOpzione('" . $row_opzione['id'] . "', this.value)'>
                    </div>";
            }
            $res .= '  <div class="form-check">
                    <div class="text-left mt-3">
                        <button class="btn btn-success rounded-pill d-flex align-items-center justify-content-center" 
                                style="width: 10vh; height: 4vh;" onclick="aggiungiOpzione(\''.$row_domanda['id'].'\', this)">
                            <span style="color: white; font-size: 24px;">+</span>
                        </button>
                    </div>
                </div>';
            $res .= "</div>";
        }

        echo json_encode(['success' => true, 'error' => '', 'HTML_CODE' => $res]);

    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method', 'HTML_CODE' => '']);
}
