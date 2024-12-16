<?php
    $logDir = __DIR__ . "/logs/assignToClass";
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/logs/assignToClass/php_error.log');
    header('Content-Type: application/json');
    $f = fopen($logDir . "/log.txt", "a+");

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        fwrite($f, "---------------- NEW OPERATION ----------------\n");
        include './../include/db_connect.php';
        $input = json_decode(file_get_contents('php://input'), true);
        // fwrite($f, var_export($input,true));
        $classe_id = isset($input['classe']) ? $input['classe'] : null;
        $studenti = isset($input['studenti']) ? $input['studenti'] : [];
        // fwrite($f, $classe_id."\n");
        fwrite($f, "Requested to assign ".sizeof($studenti)." students to class with ID: $classe_id\n");
        if ($classe_id === null) {
            fwrite($f, "Class ID not given, returning error...");
            echo json_encode(['msg' => 'Error: Classe ID is missing']);
            exit;
        }
        
        $count = 0;
        
        fwrite($f, "Assigning students...\n");
        foreach ($studenti as $studente_id) {
            fwrite($f, "    Assigning student with ID: $studente_id...\n");
            $studente_value = $studente_id ? "$studente_id" : null;
            
            if ($studente_value === null) {
                fwrite($f, "        ID given is null, continuing...\n");
                continue;
            }
            
            fwrite($f, "        Checking if already associated with query (SELECT COUNT(*) as count FROM classe_studente WHERE cf_studente=$studente_value)\n");
            $check_sql = "SELECT COUNT(*) as count FROM classe_studente WHERE cf_studente=?";
            $stmt_check_sql = $conn->prepare($check_sql);
            $stmt_check_sql->bind_param("s", $studente_value);
            $stmt_check_sql->execute();
            $result = $stmt_check_sql->get_result();
            
            if ($result) {
                $row = $result->fetch_assoc();
                
                if ($row['count'] > 0) {
                    fwrite($f, "        It does. Updating row with query (UPDATE classe_studente SET id_classe=$classe_id WHERE cf_studente=$studente_value)\n");
                    $update_sql = "UPDATE classe_studente SET id_classe=? WHERE cf_studente=?";
                    $stmt_update_sql = $conn->prepare($update_sql);
                    $stmt_update_sql->bind_param("is", $classe_id,$studente_value);
                    if ($stmt_update_sql->execute()) {
                        if ($stmt_update_sql->affected_rows > 0) {
                            $count++;
                        } else {
                            fwrite($f, "    Update affected 0 rows.\n");
                        }
                    } else {
                        fwrite($f, "  SQL error.  Returning with error...\n");
                        echo json_encode(['msg' => 'Error updating: ' . $stmt_update_sql->error, 'count' => $count]);
                        exit;
                    }
                } else {
                    fwrite($f, "        It doesn't. Inserting row with query (INSERT INTO classe_studente(id_classe, cf_studente) VALUES ($classe_id, $studente_value))\n");
                    $insert_sql = "INSERT INTO classe_studente(id_classe, cf_studente) VALUES (?, ?)";
                    $stmt_insert_sql = $conn->prepare($insert_sql);
                    $stmt_insert_sql->bind_param("is", $classe_id, $studente_value);
                    if ($stmt_insert_sql->execute()) {
                        if ($stmt_insert_sql->affected_rows > 0) {
                            $count++;
                        } else {
                            fwrite($f, "    Insert affected 0 rows.\n");
                        }
                    } else {
                        fwrite($f, "  SQL error.  Returning with error...\n");
                        echo json_encode(['msg' => 'Error inserting: ' . $stmt_insert_sql->error, 'count' => $count]);
                        exit;
                    }
                }
            } else {
                fwrite($f, "  SQL error.  Returning with error...\n");
                echo json_encode(['msg' => 'Error checking: ' . $stmt_check_sql->error, 'count' => $count]);
                exit;
            }
        }
        
        fwrite($f, "Finished, successfully assigned $count students\n");
        echo json_encode(['msg' => 'success', 'count' => $count]);
    }
?>
