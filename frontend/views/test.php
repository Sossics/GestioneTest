<?php

session_start();
var_export($_POST);
require("./../../backend/Include/db_connect.php");
$filter = isset($_GET['filter']) ? trim($_GET['filter']) : '';
switch($_SESSION['user']['ruolo']){
    case "STUDENTE":
            $SQL_query = "SELECT
                                t.id AS test_id,
                                t.titolo AS test_titolo,
                                d.nome AS nome_docente,
                                d.cognome AS cognome_docente,
                                COUNT(dm.id) AS numero_domande
                            FROM
                                utente AS stu
                            JOIN
                                classe_studente as c_s
                            ON
                                c_s.cf_studente=stu.codice_fiscale
                            JOIN
                                classe as c
                            ON
                                c_s.id_classe=c.id
                            JOIN
                                sessione as s
                            ON
                                s.classe_id=c.id
                            JOIN
                                test as t
                            ON
                                s.test_id=t.id
                            JOIN
                                utente as d
                            ON
                                d.codice_fiscale=t.cf_docente
                            LEFT JOIN
                                domanda as dm
                            ON
                                dm.test_id=t.id";
            break;
        case "DOCENTE":
            $SQL_query = "SELECT
                                t.id AS test_id,
                                t.titolo AS test_titolo,
                                d.nome AS nome_docente,
                                d.cognome AS cognome_docente,
                                COUNT(dm.id) AS numero_domande
                            FROM 
                                test AS t
                            JOIN 
                                utente AS d
                            ON 
                                d.codice_fiscale=t.cf_docente
                            LEFT JOIN 
                                domanda AS dm 
                            ON 
                                dm.test_id = t.id";
            
            break;
        }

            if (isset($_POST['id'])) {
                $SQL_query .= " WHERE t.id=?";
            }
            
            if ($filter !== '') {
                $SQL_query .= " " . (isset($_POST['id']) ? "AND" : "WHERE") . " t.titolo LIKE ? OR d.nome LIKE ? OR d.cognome LIKE ?";
            }
            
            $SQL_query .= " GROUP BY 
                                t.id, t.titolo, d.nome, d.cognome";
            
            // echo "Processing: $SQL_query";
            $stmt = $conn->prepare($SQL_query);
            if ($filter !== '') {
                $like_filter = '%' . $filter . '%';
                if (isset($_POST['id'])) {
                    $stmt->bind_param("isss", $_POST['id'], $like_filter, $like_filter, $like_filter);
                } else {
                    $stmt->bind_param("sss", $like_filter, $like_filter, $like_filter);
                }
            } else if (isset($_POST['id'])) {
                $stmt->bind_param("i", $_POST['id']);
            }
            // echo "Executing: $SQL_query";
            $stmt->execute();
            $result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php include("components/head.php"); ?>
    <style>
        tr {
            text-align: center;
        }

        .custom-card {
            width: 18rem;
            height: 350px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            margin: 20px;
        }

        .card-img-top {
            object-fit: cover;
            height: 150px;
        }

        .mb-3 {
            margin-bottom: 20px;
        }
    </style>
    <?php if(isset($_POST['attempt_code'])):?>
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
    <?php endif;?>
</head>

<body style="background-color: rgb(240, 235, 248);">
    <?php include("components/navbar.php") ?>
    <div class="container mt-5">
        <div>
            <?php if (isset($_POST['id'])): ?>
                <?php

                $row = $result->fetch_assoc();

                ?>
                <?php if (isset($_POST['attempt_code'])): ?>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="test.php">Test</a></li>
                            <li class="breadcrumb-item"><a href="test.php"><?= $row['test_titolo'] ?></a></li>
                            <li class="breadcrumb-item active" aria-current="page">Tentativo</li>
                        </ol>
                    </nav>
                <?php else: ?>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                                <li class="breadcrumb-item"><a href="test.php">Test</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><?= $row['test_titolo'] ?></li>
                            </ol>
                        </nav>
                <?php endif; ?>
            <?php else: ?>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active">Test</a></li>
                    </ol>
                </nav>
            <?php endif; ?>
        </div>
        <div class="d-flex <?= isset($_POST['attempt_code']) ? "justify-content-center" : "justify-content-between" ?> align-items-center mb-3">
            <?php if (isset($_POST['id'])): ?>
                <?php if (isset($_POST['attempt_code'])): ?>
                    <h2 class="text-center"><?= $row['test_titolo'] ?></h2>
                <?php else: ?>
                    <h3>Stai visualizzando il Test "<?= $row['test_titolo'] ?>" del docente "<?= htmlspecialchars(ucfirst(strtolower($row['nome_docente'])) . " " . ucfirst(strtolower($row['cognome_docente']))) ?>"</h3>
                <?php endif; ?>
            <?php else: ?>
                <h2>Lista dei Test</h2>
                <form method="GET" class="d-flex">
                    <input type="text" name="filter" class="form-control me-2" placeholder="Cerca test..."
                        value="<?php echo htmlspecialchars($filter); ?>">
                    <button type="submit" class="btn btn-primary">Cerca</button>
                </form>
            <?php endif; ?>
        </div>
        <?php
        if (isset($_POST['id'])) {
            if(isset($_POST['attempt_code'])){

                $test_id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

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

                echo '<form id="test-form">';
                    if ($result_domande->num_rows > 0) {
                        while ($row_domanda = $result_domande->fetch_assoc()) {
                            // print_r($row_domanda);
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
                                    // print_r($row_opzione);
                                    echo "<div class='form-check'>
                                            <input class='form-check-input' type='radio' name='question_{$row_domanda['id']}' id='option_{$row_opzione['id']}'>
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
                echo '</form>';
            }else{
                $SQL_query_fetch_tries = "SELECT
                                            COUNT(t.id) AS tentativi_usati,
                                            s.svolgibile AS test_status,
                                            s.id AS session_id
                                        FROM 
                                            sessione AS s
                                        LEFT JOIN
                                            tentativo AS t
                                        ON 
                                            s.id = t.sessione_id
                                        WHERE
                                            s.test_id =?";
                $stmt_fetch_tries = $conn->prepare($SQL_query_fetch_tries);
                $stmt_fetch_tries->bind_param("i", $_POST['id']);
                $stmt_fetch_tries->execute();
                $result_fetch_tries = $stmt_fetch_tries->get_result();
                $row_fetch_tries = $result_fetch_tries->fetch_assoc();
                // print_r($row_fetch_tries);
    
                echo "<table class='table table-bordered w-50'>";
                echo "<tbody>
                        <tr>
                            <th class='text-start'>Titolo:</th>
                            <td>" . $row['test_titolo'] . "</td>
                        </tr>
                        <tr>
                            <th class='text-start'>Creato Da:</th>
                            <td>" . htmlspecialchars(ucfirst(strtolower($row['nome_docente'])) . " " . ucfirst(strtolower($row['cognome_docente']))) . "</td>
                        </tr>
                        <tr>
                            <th class='text-start'>Tentativi usati:</th>
                            <td>" . $row_fetch_tries['tentativi_usati'] . "</td>
                        </tr>
                        
                        ";
    
                $SQL_query_attempt_details = "SELECT 
                        t.data_tentativo AS inviato_il
                      FROM 
                        tentativo AS t
                      JOIN 
                        sessione AS s
                      ON 
                        t.sessione_id = s.id
                      JOIN
                        utente AS stu
                      ON
                        t.cf_studente = stu.codice_fiscale
                      WHERE 
                        s.test_id = ?
                      AND
                        stu.codice_fiscale=?
                      AND
                        s.visibilita_tentativi=1";
    
                $stmt_attempt_details = $conn->prepare($SQL_query_attempt_details);
                $stmt_attempt_details->bind_param("is", $_POST['id'], $_SESSION['user']['codice_fiscale']);
                $stmt_attempt_details->execute();
                $result_attempt_details = $stmt_attempt_details->get_result();
    
                echo "<form action='visualizza_tentativo.php' method='POST'><table class='table table-bordered w-50 mt-4'>";
                echo "<thead>
                        <tr>
                            <th colspan='4' class='bg-light'>Tentativi</th>
                        </tr>
                        <tr>
                            <th class='text-start'>Inviato il</th>
                            <th class='text-start'>Punteggio</th>
                            <th class='text-start'>Valutazione</th>
                            <th class='text-start'>Azioni</th>
                        </tr>
                     </thead>";
                echo "<tbody>";
    
                if ($result_attempt_details->num_rows > 0) {
                    while ($row_attempt = $result_attempt_details->fetch_assoc()) {
                        echo "<tr class='text-center'>";
                        echo "<td>" . htmlspecialchars($row_attempt['inviato_il']) . "</td>";
                        // echo "<td>" . htmlspecialchars($row_attempt['punteggio']) . "</td>";
                        echo "<td>" . "N/D" . "</td>";
                        // echo "<td>" . htmlspecialchars($row_attempt['valutazione']) . "</td>";
                        echo "<td>" . "N/D" . "</td>";
                        echo "<td>
                                <input type='hidden' name='attempt_id' value='" . $row_attempt['inviato_il'] . "'>
                                <input type='hidden' name='session_id' value='" . $row_fetch_tries['session_id'] . "'>
                
                                <button class='btn btn-link show-answers' type='submit'>
                                    Visualizza Risposte
                                </button>
                             </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' class='text-center'>Tentativi nascosti o non trovati</td></tr>";
                }
    
                echo "</tbody>";
                echo "</table></form>";
                // var_export($row_fetch_tries);
                if($row_fetch_tries['test_status'] == 1){
                    echo "<button class='btn btn-success fs-6' onclick='startAttempt();'>Avvia un Tentativo</button>";
                }
            }
        } else {

            if (isset($result) && $result->num_rows > 0) {
                if ($_SESSION['user']['ruolo'] == "STUDENTE") {
                    while ($row = $result->fetch_assoc()) {
                        echo "<form method='POST' action='test.php' class='mb-3' style='display: inline-block;'>";
                        echo "<input type='hidden' name='id' value='" . htmlspecialchars($row['test_id']) . "'>";
                        echo "<div class='card custom-card' style='cursor: pointer;' onclick='this.closest(\"form\").submit();'>";
                        echo "<img src='src/images/test.jpg' class='card-img-top' alt='Immagine test'>";
                        echo "<div class='card-body'>";
                        echo "<h5 class='card-title font-weight-bold'>" . htmlspecialchars($row['test_titolo']) . "</h5>";
                        echo "<p class='card-text'>Creato da: " . htmlspecialchars(ucfirst(strtolower($row['nome_docente'])) . " " . ucfirst(strtolower($row['cognome_docente']))) . "</p>";
                        echo "</div>";
                        echo "</div>";
                        echo "</form>";
                    }
                } else {
                    echo "<table class='table'>";
                    echo "<thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Titolo</th>
                                        <th>Docente</th>
                                        <th>Domande</th>
                                        <th>Azioni</th>
                                    </tr>
                                 </thead>";
                    echo "<tbody>";
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['test_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['test_titolo']) . "</td>";
                        echo "<td>" . htmlspecialchars(ucfirst(strtolower($row['nome_docente'])) . " " . ucfirst(strtolower($row['cognome_docente']))) . "</td>";
                        echo "<td>" . htmlspecialchars($row['numero_domande']) . "</td>";
                        echo "<td class='action-btn'>";
                        echo "<a href='modifica_test.php?id=" . htmlspecialchars($row['test_id']) . "' class='btn btn-warning btn-sm me-1'>
                                        <i data-feather='edit'></i>
                                     </a>";
                        echo "<a href='visualizza_test.php?id=" . htmlspecialchars($row['test_id']) . "' class='btn btn-info btn-sm'>
                                        <i data-feather='eye'></i>
                                     </a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    echo "</tbody>";
                    echo "</table>";
                }
            } else {
                if ($_SESSION['user']['ruolo'] == "STUDENTE") {
                    echo "<p class='text-center'>Nessun test disponibile</p>";
                } else {
                    echo "<tr><td colspan='5' class='text-center'>Nessun test trovato</td></tr>";
                }
            }
        }
        ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php include("components/footer.php") ?>
    <script>
        feather.replace();

        function startAttempt(){
            fetch('../../backend/API/new_attempt.php', {
                method: "POST",
                headers: {
                    'Content-Type' : "application/json"
                },
                body: JSON.stringify({
                        student_code: "<?=$_SESSION['user']['codice_fiscale']?>",
                        session_code: "<?=$row_fetch_tries['session_id'] ?? ""?>"
                    })
            })
            .then(response => {
                if(!response.ok){
                    throw new Error("Errore nella risposta della API");
                }
                return response.json();
            })
            .then(data => {
                submitPostData('test.php', {
                    id: <?= $_POST['id'] ?>,
                    attempt_code: data.attempt_code
                });
            })
            .catch(error => {
                console.error("Errore: " + error);
                alert("Si e' verificato un errore. Riprova piu' tardi");
            })
        }

        function submitPostData(url, data){
            const form = document.createElement("form");
            form.method = "POST";
            form.action = url;

            for(const key in data){
                const input = document.createElement("input");
                input.type = "hidden";
                input.name = key;
                input.value = data[key];
                form.appendChild(input);
            }

            document.body.append(form);
            form.submit();
        }

    </script>
</body>

</html>