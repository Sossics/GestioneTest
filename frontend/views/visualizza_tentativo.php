
<?php

if($_SERVER['REQUEST_METHOD'] !== "POST") header("Location: test.php");

session_start();
require("./../../backend/Include/db_connect.php");

define('SECRET_KEY', 'g78gh789f4328h79g890aafzxvssfga72gfas');

function decryptId($encrypted) {
    $method = 'AES-256-CBC';
    $key = hash('sha256', SECRET_KEY, true);
    list($encryptedData, $iv) = explode('::', base64_decode($encrypted), 2);
    $iv = base64_decode($iv);
    $id = openssl_decrypt($encryptedData, $method, $key, 0, $iv);
    return $id;
}


$attempt_id_encryted = $_POST['attempt_id'];
$attempt_id = decryptId($attempt_id_encryted);

// Test fetch
$SQL_query_test = "SELECT t.titolo, d.nome AS nome_docente, d.cognome AS cognome_docente, t.id AS test_id
                    FROM
                        tentativo AS tent
                    JOIN
                        sessione AS s
                    ON
                        tent.sessione_id=s.id
                    JOIN
                        test AS t
                    ON
                        s.test_id=t.id
                    JOIN
                        utente AS d
                    ON
                        t.cf_docente=d.codice_fiscale
                    WHERE
                        tent.id = ?";
$stmt_test = $conn->prepare($SQL_query_test);
$stmt_test->bind_param("i", $attempt_id);
$stmt_test->execute();
$result_test = $stmt_test->get_result();

if ($result_test->num_rows == 0) {
    echo "Test non trovato!";
    exit();
}

$row_test = $result_test->fetch_assoc();
$test_id = $row_test['test_id'];

// Domande fetch SQL
$SQL_query_domande = "SELECT id, testo, tipo
                      FROM domanda
                      WHERE test_id = ?";
$stmt_domande = $conn->prepare($SQL_query_domande);
$stmt_domande->bind_param("i", $test_id);
$stmt_domande->execute();
$result_domande = $stmt_domande->get_result();

// Risposte fetch SQL
$SQL_query_risposte = "SELECT domanda_id, risposta_multipla_id, risposta_aperta, punteggio
                      FROM risposta
                      WHERE tentativo_id = ?";
$stmt_risposte = $conn->prepare($SQL_query_risposte);
$stmt_risposte->bind_param("i", $attempt_id);
$stmt_risposte->execute();
$result_risposte = $stmt_risposte->get_result();
$risposte = $result_risposte->fetch_all(MYSQLI_ASSOC);

var_export($risposte);

$risposte_assoc = [];
foreach ($risposte as $risposta) {
    if (!isset($risposte_assoc[$risposta['domanda_id']])) {
        $risposte_assoc[$risposta['domanda_id']] = [];
    }
    if ($risposta['risposta_multipla_id'] !== null) {
        $risposte_assoc[$risposta['domanda_id']][] = $risposta['risposta_multipla_id'];
    } else {
        $risposte_assoc[$risposta['domanda_id']]['risposta_aperta'] = $risposta['risposta_aperta'];
    }
}

?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizza Test - <?php echo htmlspecialchars($row_test['titolo']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/feather-icons@4.30.0/dist/feather.min.css" rel="stylesheet">
    <style>
        .question-container {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            background-color: #f9f9f9;
        }

        .question-container .question-title {
            font-weight: bold;
            font-size: 1.2rem;
        }

        .question-container .question-text {
            margin-top: 10px;
        }

        .question-container .options-list {
            margin-top: 10px;
        }

        .question-container input[type="radio"],
        .question-container input[type="checkbox"] {
            margin-right: 10px;
        }

        .form-check-label {
            font-size: 1rem;
        }

        .form-control {
            font-size: 1rem;
        }
    </style>
</head>

<body style="background-color: rgb(240, 235, 248);">
    <?php include("components/navbar.php"); ?>
    <div class="container mt-5">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="test.php">Test</a></li>
                    <li class="breadcrumb-item"><a href="test.php">Visualizza</a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($row_test['titolo']); ?></li>
                </ol>
            </nav>
        </div>
        <h2 class="text-center"><?php echo htmlspecialchars($row_test['titolo']); ?></h2>
        <p class="text-center">Creato da: <?php echo ucfirst(strtolower($row_test['nome_docente'])) . " " . ucfirst(strtolower($row_test['cognome_docente'])); ?></p>

        <form id="test-form">
            <?php
            if ($result_domande->num_rows > 0) {
                while ($row_domanda = $result_domande->fetch_assoc()) {
                    // print_r($row_domanda);

                    $current_answer = isset($risposte_assoc[$row_domanda['id']]) ? $risposte_assoc[$row_domanda['id']] : null;
                    $punteggio_tot = 0;

                    if ($row_domanda['tipo'] == 'MULTIPLA' && is_array($current_answer)) {
                        foreach ($current_answer as $risposta_multipla_id) {
                            foreach ($risposte as $risposta) {
                                if ($risposta['domanda_id'] == $row_domanda['id'] && $risposta['risposta_multipla_id'] == $risposta_multipla_id) {
                                    $punteggio_totale += $risposta['punteggio'];
                                }
                            }
                        }
                    }
            
                    if ($row_domanda['tipo'] == 'APERTA') {
                        $punteggio_totale = 0;
                    }

                    echo "<div class='question-container'>";
                    
                    // Testo
                    echo "<p class='question-title d-flex justify-content-between align-items-center'>";
                    echo htmlspecialchars($row_domanda['testo']);
                    echo "<span class='badge bg-secondary'>
                            <input type='text' class='form-control d-inline-block text-center' 
                                style='width: 60px; padding: 2px; font-size: 0.9rem;' 
                                value='{$punteggio_totale}' " . (($_SESSION['user']['ruolo'] == "STUDENTE") ? "disabled" : "") . ">
                         </span>";    
                    echo "</p>";

                    // tipo "APERTA"
                    if ($row_domanda['tipo'] == 'APERTA') {
                        $risposta_aperta = $current_answer['risposta_aperta'] ?? "";
                        echo "<div class='question-text'>
                                <label for='answer_{$row_domanda['id']}'>Risposta:</label>
                                <textarea id='answer_{$row_domanda['id']}' class='form-control' rows='4' placeholder='Scrivi la tua risposta' " . (($_SESSION['user']['ruolo'] == "STUDENTE") ? "disabled" : "") . ">{$risposta_aperta}</textarea>
                              </div>";
                    }

                    // tipo "SCELTA_MULTIPLA"
                    if ($row_domanda['tipo'] == 'MULTIPLA') {
                        // Opzioni
                        $SQL_query_opzioni = "SELECT * FROM opzioni_domanda WHERE domanda_id = ?";
                        $stmt_opzioni = $conn->prepare($SQL_query_opzioni);
                        $stmt_opzioni->bind_param("i", $row_domanda['id']);
                        $stmt_opzioni->execute();
                        $result_opzioni = $stmt_opzioni->get_result();
                        
                        echo "<div class='options-list'>";
                        while ($row_opzione = $result_opzioni->fetch_assoc()) {
                            $is_checked = (is_array($current_answer) && in_array($row_opzione['id'], $current_answer)) ? "checked" : "";
                            // print_r($row_opzione);
                            echo "<div class='form-check'>
                                    <input class='form-check-input' type='radio' name='question_{$row_domanda['id']}' id='option_{$row_opzione['id']}' {$is_checked}>
                                    <label class='form-check-label' for='option_{$row_opzione['id']}'>
                                        " . $row_opzione['testo_opzione'] . "
                                    </label>
                                  </div>";
                        }
                        echo "</div>";
                    }
                    echo "</div>";
                }
            } else {
                echo "<p>Non ci sono domande per questo test.</p>";
            }
            ?>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php include("components/footer.php")?>
    <script>
        feather.replace();
    </script>
</body>

</html>