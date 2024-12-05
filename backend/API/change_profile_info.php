<?php

$logDir = __DIR__ . "/logs/change_profile_info";
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/change_profile_info/php_error.log');
header('Content-Type: application/json');
$f = fopen($logDir . "/log.txt", "a+");

header('Content-Type: application/json');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    fwrite($f, "----------------NEW OPERATION----------------\n");
    $input = json_decode(file_get_contents('php://input'), true);

    $user_cf = (isset($_SESSION['user']['codice_fiscale']) ? $_SESSION['user']['codice_fiscale'] : "");
    $new_name = trim($input['new_name']);
    $new_surname = trim($input['new_surname']);

    fwrite($f, "Checking given parameters...\n");
    if (empty($user_cf) || (empty($new_cognome) && empty($new_name))) {
        fwrite($f, "Required parameters are missing. Aborting operation...\n");
        echo json_encode(['success' => false, 'error' => 'Missing fields']);
    }
    fwrite($f, "Parameters are valid.\n");

    fwrite($f, "Changing info for user with ID(cf): '$user_cf':\n");
    fwrite($f, "    New Name: $new_name\n");
    fwrite($f, "    New Surname: $new_surname\n");
    fwrite($f, "Processing...\n");
    fwrite($f, "    Checking if user with ID(cf) [$user_cf] exists...\n");

    include './../include/db_connect.php';
    $SQL_query = "SELECT
                    nome
                  FROM 
                    utente
                  WHERE 
                    codice_fiscale=?
                  LIMIT 1";

    $stmt = $conn->prepare($SQL_query);
    $stmt->bind_param("s", $user_cf);
    $stmt->execute();
    $result = $stmt->get_result();
    $count_rows = $result->num_rows;

    if ($count_rows > 0) {
        fwrite($f, "    User exists.\n");
        fwrite($f, "    Setting up DataBase connection...\n");
        fwrite($f, "    Connection set.\n");
        fwrite($f, "    Updating row...\n");
        $stmt = $conn->prepare("UPDATE utente SET nome = ?, cognome = ? WHERE codice_fiscale = ?");
        $stmt->bind_param('sss', $new_name, $new_surname, $user_cf);

        if ($stmt->execute()) {
            $_SESSION['user']['nome'] = $new_name;
            $_SESSION['user']['cognome'] = $new_surname;
            fwrite($f, "Updated.\n");
            echo json_encode(['success' => true]);
        } else {
            fwrite($f, "Update operation has failed (DB error).\n");
            echo json_encode(['success' => false, 'error' => 'DB update failed']);
        }
    } else {
        fwrite($f, "Update operation has failed, Invalid inputs.\n");
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
    }
} else {
    fwrite($f, "Update operation has failed, Invalid request method.\n");
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>