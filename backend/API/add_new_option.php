<?php

$logDir = __DIR__ . "/logs/add_new_option";
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/add_new_option/php_error.log');
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
    
    $stmt = $conn->prepare("INSERT INTO opzioni_domanda(domanda_id) VALUES (?)");
    $stmt->bind_param('i',$question_id);
    fwrite($f, "Adding new option to the database.\n");
    if($stmt->execute()){
        fwrite($f, "Executed.\n");
        $last_id = $conn->insert_id;
        fwrite($f, "Generate HTML.\n");
        $res = "<div class='form-check'>
        <input class='form-check-input' 
        type='radio' 
        name='question_".$question_id."' 
        id='option_$last_id' 
        disabled>
        
        <input type='text' class='border-0 border-bottom text-left' 
        id='titolo'
        name='question_".$question_id."'
        id='option_".$last_id."' value=''
        style='background: transparent; outline: none;'
        onblur=\"aggiornaOpzione('".$last_id."', this.value)\">
        <input type='checkbox' class='form-check-input' 
                            id='correct_option_$last_id' 
                            onclick='toggleCorrectOption(\"" . $last_id . "\", this.checked)'>
                            <label for='correct_option_$last_id'>Corretta</label>
                            <button type=\"button\" class='btn btn-light' onclick='eliminaOpzione(\"".$last_id."\")'><span class='text-danger'>x</span></button>
        </div>";
        fwrite($f, "Generated.\n");
        echo json_encode(['success' => true, 'error' => '', 'HTML_CODE' => $res]);
    }
    // echo json_encode(['success' => false, 'error' => 'Failed to execute', 'HTML_CODE' => null]);
    

} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method', 'HTML_CODE' => null]);
}
