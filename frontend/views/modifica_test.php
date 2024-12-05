<?php
session_start();
require("./../../backend/Include/db_connect.php");

// ID
$test_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (isset($_GET['titolo'])) {

    $new_title = htmlspecialchars($_GET['titolo']);

    $SQL_query_test = "UPDATE test SET titolo = ? WHERE test.id = ?";
    $stmt_test = $conn->prepare($SQL_query_test);
    $stmt_test->bind_param('si', $new_title, $test_id);
    $stmt_test->execute();

}

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

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica Test - <?php echo htmlspecialchars($row_test['titolo']); ?></title>
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
                    <li class="breadcrumb-item"><a href="test.php">Modifica</a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($row_test['titolo']); ?></li>
                </ol>
            </nav>
        </div>

        <div class="text-center">
            <div class="input-group mb-1 w-100">
                <input type="text" class="form-control fs-1 border-0 border-bottom text-center" id="titolo"
                    name="titolo" value="<?php echo htmlspecialchars($row_test['titolo']); ?>"
                    style="background: transparent; outline: none;"
                    onblur="aggiornaTitolo('<?php echo $test_id; ?>', this.value)">
            </div>
            <p class="text-center">
                Creato da:
                <?php echo ucfirst(strtolower($row_test['nome_docente'])) . " " . ucfirst(strtolower($row_test['cognome_docente'])); ?>
            </p>
        </div>



        <form id="test-form">
            <?php
            if ($result_domande->num_rows > 0) {
                while ($row_domanda = $result_domande->fetch_assoc()) {
                    // print_r($row_domanda);
                    echo "<div class='question-container' id='".$row_domanda['id']."_question-container'>";

                    // Testo
                    // echo "<p class='question-title'>" . htmlspecialchars($row_domanda['testo']) . "</p>";
                    echo '  <div class="d-flex justify-content-between align-items-center">
                                <input type="text" class="form-control fs-4 border-0 border-bottom text-left" id="titolo"
                                    name="titolo" 
                                    value="'.htmlspecialchars($row_domanda['testo']).'"
                                    style="background: transparent; outline: none;"
                                    onblur="aggiornaDomanda(\''.$row_domanda['id'].'\', this.value)"
                                >
                                <div class="ms-3">
                                    <label>Tipo di domanda:</label>
                                    <select id="question_type" name="question_type" onchange="aggiornaTipoDomanda('.$row_domanda['id'].', this.value)">
                                        <option value="APERTA" '. (($row_domanda['tipo'] == "APERTA") ? "selected": "") .' >Domanda Aperta</option>
                                        <option value="MULTIPLA" '. (($row_domanda['tipo'] == "MULTIPLA") ? "selected": "") .' >Scelta Multipla</option>
                                    </select>
                                </div>
                            </div>';

                    // tipo "APERTA"
                    if ($row_domanda['tipo'] == 'APERTA') {
                        echo "<div class='question-text'>
                                <label for='answer_{$row_domanda['id']}'>Risposta:</label>
                                <textarea id='answer_{$row_domanda['id']}' class='form-control' rows='4' placeholder='Scrivi la tua risposta' disabled></textarea>
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
                            // print_r($row_opzione);
                            echo "<div class='form-check'>
                                    <input class='form-check-input' type='radio' name='question_{$row_domanda['id']}' id='option_{$row_opzione['id']}' disabled>
                                    <input type='text' class='border-0 border-bottom text-left' id='titolo'
                            name='question_".$row_domanda['id']."'
                            id='option_".$row_opzione['id']."' 
                            value='" . $row_opzione['testo_opzione'] . "'
                            style='background: transparent; outline: none;'
                            onblur='aggiornaOpzione('".$row_opzione['id']."', this.value)'>
                                  </div>";
                        }
                        echo '  <div class="form-check">
                                    <div class="text-left mt-3">
                                        <button class="btn btn-success rounded-pill d-flex align-items-center justify-content-center" 
                                                style="width: 10vh; height: 4vh;" onclick="aggiungiOpzione()">
                                            <span style="color: white; font-size: 24px;">+</span>
                                        </button>
                                    </div>
                                </div>';
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
    <?php include("components/footer.php") ?>
    <script>
        feather.replace();
        
                function aggiornaTitolo(testID, titolo) {
                    fetch('../../backend/API/change_test_title.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ id: testID, titolo: titolo })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                alert('Errore durante l\'aggiornamento del titolo.');
                            }
                        })
                        .catch(error => {
                            console.error('Errore nella richiesta:', error);
                            alert('Errore durante la connessione al server.');
                        });
                }
        
                function aggiornaTipoDomanda(questionID, tipo) {
                    fetch('../../backend/API/change_test_question_type.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ id: questionID, tipo: tipo })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                alert('Errore durante l\'aggiornamento del titolo.');
                            }else{
                                
                                //Fetch new Question HTML code from renderer
                                fetch('../../backend/API/render_question.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({id: questionID})
                                })
                                .then(render_response => render_response.json())
                                .then(data => {
                                    if(!data.success){
                                        alert('Errore durante il cambiamento del tipo di domanda');
                                        location.reload();
                                    }else{
                                        var container_div = document.getElementById(questionID+"_question-container");
                                        container_div.innerHTML = "";                                        
                                        }
                                    });

                            }
                        })
                        .catch(error => {
                            console.error('Errore nella richiesta:', error);
                            alert('Errore durante la connessione al server.');
                        });
                }
        
                function aggiornaDomanda(questionID, domanda) {
                    fetch('../../backend/API/change_test_question.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ id: questionID, domanda: domanda })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                alert('Errore durante l\'aggiornamento del titolo.');
                            }
                        })
                        .catch(error => {
                            console.error('Errore nella richiesta:', error);
                            alert('Errore durante la connessione al server.');
                        });
                }
        
                function aggiornaOpzione(optionID, opzione) {
                    fetch('../../backend/API/change_test_option.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ id: optionID, opzione: opzione })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                alert('Errore durante l\'aggiornamento del titolo.');
                            }
                        })
                        .catch(error => {
                            console.error('Errore nella richiesta:', error);
                            alert('Errore durante la connessione al server.');
                        });
                }
        </script>

</body>

</html>