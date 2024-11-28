<?php
session_start();
require("./../../backend/Include/db_connect.php");

// ID
$test_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Test fetch
$SQL_query_test = "SELECT t.titolo, d.nome AS nome_docente, d.cognome AS cognome_docente
                   FROM test AS t
                   JOIN utente AS d ON d.codice_fiscale = t.cf_docente
                   WHERE t.id = ?";
$stmt_test = $conn->prepare($SQL_query_test);
$stmt_test->bind_param("i", $test_id);
$stmt_test->execute();
$result_test = $stmt_test->get_result();

if ($result_test->num_rows == 0) {
    echo "Test non trovato!";
    exit();
}

$row_test = $result_test->fetch_assoc();

// Domande fetch SQL
$SQL_query_domande = "SELECT id, testo, tipo
                      FROM domanda
                      WHERE test_id = ?";
$stmt_domande = $conn->prepare($SQL_query_domande);
$stmt_domande->bind_param("i", $test_id);
$stmt_domande->execute();
$result_domande = $stmt_domande->get_result();

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
        <h2 class="text-center"><?php echo htmlspecialchars($row_test['titolo']); ?></h2>
        <p class="text-center">Creato da: <?php echo ucfirst(strtolower($row_test['nome_docente'])) . " " . ucfirst(strtolower($row_test['cognome_docente'])); ?></p>

        <form id="test-form">
            <?php
            if ($result_domande->num_rows > 0) {
                while ($row_domanda = $result_domande->fetch_assoc()) {
                    echo "<div class='question-container'>";
                    
                    // Testo
                    echo "<p class='question-title'>" . htmlspecialchars($row_domanda['testo']) . "</p>";

                    // tipo "APERTA"
                    if ($row_domanda['tipo'] == 'APERTA') {
                        echo "<div class='question-text'>
                                <label for='answer_{$row_domanda['id']}'>Risposta:</label>
                                <textarea id='answer_{$row_domanda['id']}' class='form-control' rows='4' placeholder='Scrivi la tua risposta'></textarea>
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
                            echo "<div class='form-check'>
                                    <input class='form-check-input' type='radio' name='question_{$row_domanda['id']}' id='option_{$row_opzione['id']}'>
                                    <label class='form-check-label' for='option_{$row_opzione['id']}'>
                                        " . htmlspecialchars($row_opzione['testo_opzione']) . "
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