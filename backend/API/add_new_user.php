<?php

$logDir = __DIR__ . "/logs/add_new_user";
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__.'/logs/add_new_user/php_error.log');
header('Content-Type: application/json');
$f = fopen($logDir . "/log.txt", "a+");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    fwrite($f, "----------------NEW OPERATION----------------\n");
    $input = json_decode(file_get_contents('php://input'), true);
    fwrite($f, var_export($input, true));
    $nome = $input['nome']??"";
    $cognome = $input['cognome']??"";
    $codice = $input['codice']??"";
    $login_s = $input['login']??"";
    $password_s = $input['password']??"";
    $ruolo = $input['ruolo']??"STUDENTE";

   //    fwrite($f, "Adding a new class with nome=".$nome." and anno=".$anno);

    if (!empty($nome) && !empty($cognome) && !empty($codice) && !empty($login_s) && !empty($password_s) ) {
        include './../include/db_connect.php';
        fwrite($f, "    Checking if row exists. If yes, update.\n");
        $stmt = $conn->prepare("INSERT INTO utente (codice_fiscale, nome, cognome, login, password, ruolo) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssss', $codice, $nome, $cognome, $login_s, $password_s, $ruolo);
        
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
