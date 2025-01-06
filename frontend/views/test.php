<?php

// var_export($_POST);

define('SECRET_KEY', 'g78gh789f4328h79g890aafzxvssfga72gfas');

function decryptId($encrypted) {
    $method = 'AES-256-CBC';
    $key = hash('sha256', SECRET_KEY, true);
    list($encryptedData, $iv) = explode('::', base64_decode($encrypted), 2);
    $iv = base64_decode($iv);
    $id = openssl_decrypt($encryptedData, $method, $key, 0, $iv);
    return $id;
}

function encryptId($id) {
    $method = 'AES-256-CBC';
    $key = hash('sha256', SECRET_KEY, true);
    $iv = openssl_random_pseudo_bytes(16);
    $encryptedId = openssl_encrypt($id, $method, $key, 0, $iv);
    return base64_encode($encryptedId . '::' . base64_encode($iv)); 
}


session_start();
require("./../../backend/Include/db_connect.php");
$filter = isset($_GET['filter']) ? trim($_GET['filter']) : '';

if (isset($_POST["elimina"])) {
    
    $SQL_query = " DELETE FROM test WHERE test.id = ?";

    $stmt = $conn->prepare($SQL_query);
    $stmt->bind_param("s", $_POST["elimina"]);
    $stmt->execute();

}

if(isset($_POST['attempt_code'])){
    $SQL_check_attempt_existance = "SELECT
                                        id AS answer
                                    FROM
                                        risposta
                                    WHERE
                                        tentativo_id=?";
    $stmt_check_attempt_existance = $conn->prepare($SQL_check_attempt_existance);
    $decrypted_attempt_id = decryptId($_POST['attempt_code']);
    $stmt_check_attempt_existance->bind_param("i", $decrypted_attempt_id);
    $stmt_check_attempt_existance->execute();
    $result_check_attempt_existance = $stmt_check_attempt_existance->get_result();
    if($result_check_attempt_existance && $result_check_attempt_existance->num_rows > 0){
        // echo "AIAAAA";
        unset($_POST['attempt_code']);
    }
}

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
                                dm.test_id = t.id 
                            WHERE 
                                d.codice_fiscale = ? ";
            
            break;
        case "ADMIN":
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
            if($_SESSION['user']['ruolo'] == "DOCENTE"){
                if ($filter !== '') {
                    $like_filter = '%' . $filter . '%';
                    if (isset($_POST['id'])) {
                        $stmt->bind_param("sisss", $_SESSION['user']["codice_fiscale"], $_POST['id'], $like_filter, $like_filter, $like_filter);
                    } else {
                        $stmt->bind_param("ssss", $_SESSION['user']["codice_fiscale"], $like_filter, $like_filter, $like_filter);
                    }
                } else if (isset($_POST['id'])) {
                    $stmt->bind_param("si", $_SESSION['user']["codice_fiscale"], $_POST['id']);
                }else{
                    $stmt->bind_param("s", $_SESSION['user']["codice_fiscale"]);
                }
            }else{
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
            }

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
        <div class="d-flex flex-column <?= isset($_POST['attempt_code']) ? "justify-content-center" : "justify-content-between" ?> align-items-center mb-3">
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
            <h3 class="text-center m-0">
                <div class="spinner-border" role="status" style="display: none;"></div>
            </h3>
            <p id="saving_message" style="display: none;"></p>
        </div>
        <?php
        if (isset($_POST['id'])) {
            $SQL_query_fetch_tries = "SELECT
                                            COUNT(t.id) AS tentativi_usati,
                                            s.svolgibile AS test_status,
                                            s.id AS session_id,
                                            s.max_tentativi_ammessi AS max_attempts,
                                            s.visibilita_tentativi AS attempts_visibility
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
            if(isset($_POST['attempt_code'])){
                if(isset($_POST['data_attempt'])){
                    // var_export($_POST['data_attempt']);
                }else{
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
    
                    echo "<h3 class='text-center'>
                            <div class='spinner-border' role='status' style='display: none;'></div>
                        </h3>";
                    echo '<form id="test-form" method="POST">';
                    echo '<input type="hidden" name="id" value="'.$_POST['id'].'">';
                    echo '<input type="hidden" name="attempt_code" value="'.$_POST['attempt_code'].'">';
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
                                            <textarea id='answer_{$row_domanda['id']}' name='answer_{$row_domanda['id']}' class='form-control' rows='4' placeholder='Scrivi la tua risposta'></textarea>
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
                                                <input class='form-check-input' type='checkbox' name='question_{$row_domanda['id']}_{$row_opzione['id']}' id='option_{$row_opzione['id']}'>
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
                    echo "<input type=\"submit\" value=\"Invia Tentativo\" name=\"data_attempt\">";
                    echo '</form>';
                }
            }else{
    
                $max_attempts = $row_fetch_tries['max_attempts'];
                $num_attempts = $row_fetch_tries['tentativi_usati'];
                $visibile = $row_fetch_tries['attempts_visibility'];

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
                            <td>" . $row_fetch_tries['tentativi_usati'] . " su " . $max_attempts . "</td>
                        </tr>
                        
                        ";
    
                $SQL_query_attempt_details = "SELECT 
                                                    s.id AS session_id,
													t.data_tentativo AS inviato_il,
                                                    t.id AS attempt_id,
                                                    (
                                                        SELECT 
                                                            SUM(d.punti)
                                                        FROM
                                                            test AS td
                                                        JOIN
                                                            domanda AS d
                                                        ON
                                                            td.id=d.test_id
                                                        WHERE
                                                            td.id=?
                                                    ) AS punteggio_totale,
                                                    (
                                                        SELECT 
                                                            SUM(ris.punteggio)
                                                        FROM 
                                                            tentativo as tent
                                                        JOIN
                                                            risposta AS ris
                                                        ON
                                                            tent.id=ris.tentativo_id
                                                        WHERE
                                                            tent.id=t.id
                                                    ) AS punteggio_finale
                                                FROM 
                                                    sessione AS s
                                                LEFT JOIN 
                                                    tentativo AS t
                                                ON 
                                                    t.sessione_id = s.id
                                                LEFT JOIN
                                                    utente AS stu
                                                ON
                                                    t.cf_studente = stu.codice_fiscale
                                                WHERE 
                                                    s.test_id = ?
                                                AND
                                                    stu.codice_fiscale = ?";
    
                $stmt_attempt_details = $conn->prepare($SQL_query_attempt_details);
                $stmt_attempt_details->bind_param("iis", $_POST['id'],$_POST['id'], $_SESSION['user']['codice_fiscale']);
                $stmt_attempt_details->execute();
                $result_attempt_details = $stmt_attempt_details->get_result();
                
                echo "<table class='table table-bordered w-50 mt-4'>";
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

                // var_export($row_fetch_tries);
            
    
                if ($result_attempt_details->num_rows > 0) {
                    $row_attempt = $result_attempt_details->fetch_assoc();
                
                    if ($num_attempts > 0) {
                        do {
                            // var_export($row_attempt);
                            echo "<tr class='text-center'>";
                            echo "<td>" . htmlspecialchars($row_attempt['inviato_il']) . "</td>";
                            echo "<td>" . ($visibile ? ( ($row_attempt['punteggio_finale'] ?? "...") . '/' . ($row_attempt['punteggio_totale'])) : "Non Visibile") . "</td>";
                            echo "<td>" . ($visibile ? ((is_null($row_attempt['punteggio_finale']) ? "..." : number_format((float)($row_attempt['punteggio_finale'] / $row_attempt['punteggio_totale'])*10, 2, '.', '')). ' / 10') : "Non Visibile") . "</td>";
                   // echo "<td>" . $row_attempt['attempt_id'] . "</td>";
                            echo "<td>
                                    <form action='visualizza_tentativo.php' method='POST'>
                                    <input type='hidden' name='attempt_id' value='" . encryptId($row_attempt['attempt_id']) . "'>
                                    " . (($visibile) ? "<button class='btn btn-link show-answers' type='submit'>
                                        Visualizza
                                    </button>" : "Non Visibile") . "
                                    </form>
                                 </td>";
                            echo "</tr>";
                        } while ($row_attempt = $result_attempt_details->fetch_assoc());
                    } else {
                        echo "<tr><td colspan='4' class='text-center'>Tentativi nascosti o non trovati</td></tr>";
                    }
                } else {
                    // $max_attempts = null;
                    // $num_attempts = 0;
                    // $visibile = null;
                    echo "<tr><td colspan='4' class='text-center'>Tentativi nascosti o non trovati</td></tr>";
                }
    
                echo "</tbody>";
                echo "</table></form>";
                // var_export($row_fetch_tries);
                // echo "N Tentativi trovati:".$num_attempts." Tentativi Massimi:".$max_attempts;
                if($row_fetch_tries['test_status'] == 1 && ($num_attempts < $max_attempts || $max_attempts == 0)){
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
                    echo "<form method='POST' action='test.php'>";
                    echo "<thead>
                                    <tr>
                                        <th>Elimina</th>
                                        <th>Titolo</th>
                                        <th>Docente</th>
                                        <th>Domande</th>
                                        <th>Azioni</th>
                                    </tr>
                                 </thead>";
                    echo "<tbody>";
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td> <button type=\"submit\" value=\"" . $row['test_id'] . "\" name=\"elimina\" class=\"btn btn-danger\">X</button></td>";
                       // echo "<td>" . htmlspecialchars($row['test_id']) . "</td>";
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
                    echo "</form>";
                    echo "</table>";
                    echo '<div class="d-flex justify-content-center"><button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#nuovoTest">Crea nuovo test</button></div>';
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
                        student_code: "<?=$_SESSION['user']['codice_fiscale'] ?? "null"?>",
                        session_code: "<?=$row_fetch_tries['session_id'] ?? "null"?>"
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
                    id: <?= $_POST['id'] ?? 'null'?>,
                    attempt_code: data.attempt_code ?? null
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

        document.addEventListener('DOMContentLoaded', () => {
            const student_form = document.getElementById('test-form');
            const spinner = document.querySelector('.spinner-border');
            const message = document.getElementById('saving_message');

            if (student_form) {
                student_form.addEventListener('submit', async (e) => {
                    e.preventDefault();

                    spinner.style.display = 'block';
                    message.style.display = 'block';
                    message.innerHTML = 'Salvataggio...';
                    student_form.style.display = 'none';

                    const formData = new FormData(student_form);
                    const jsonData = Object.fromEntries(formData.entries());

                    try {
                        const response = await fetch('../../backend/API/save_answers.php', {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json"
                            },
                            body: JSON.stringify({
                                studentID: "<?= $_SESSION['user']['codice_fiscale'] ?? "null"?>",
                                testID: "<?= $_POST['id'] ?? "null"?>",
                                attemptID: "<?= $_POST['attempt_code'] ?? "null"?>",
                                data: jsonData ?? '-1'
                            })
                        });

                        if (response.ok) {
                            const result = await response.json();
                            console.log('Success:', result);

                            message.innerHTML = `<p class="text-success">Risposte inviate con successo!</p>`;
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            console.error('Errore durante l\'invio:', response.statusText);
                            message.innerHTML = `<p class="text-danger">Errore durante l'invio delle risposte.</p>`;
                        }
                    } catch (error) {
                        console.error('Fetch error:', error);
                        message.innerHTML = `<p class="text-danger">Errore di rete. Controlla la connessione.</p>`;
                    }
                });
            }
        });

    </script>

    <div class="modal fade" id="nuovoTest" tabindex="-1" aria-labelledby="titoloModal" aria-hidden="true">
    <div class="modal-dialog">
        <form action="test.php" method="post">
        <div class="modal-content">
        <div class="modal-header">
            <h1 class="modal-title fs-5" id="titoloModal">Crea nuovo test</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            
            <label for="nome_test" class="col-form-label">Nome del test: </label>
            <input type="text" class="form-control" id="nome_test" name="nome_test" required>
            
            <input type="hidden" name="ruolo_studente" id="ruolo_studente" value="STUDENTE">

        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
            <button type="button" class="btn btn-primary" onclick='aggiungiTest(this);' >Crea</button>
        </div>
        </div>
        </form>
    </div>
    </div>

    <script>

        function aggiungiTest(context){

            const nome = document.getElementById("nome_test").value;
            const IDdoc = "<?php echo $_SESSION['user']['codice_fiscale'];?>";

            fetch('../../backend/API/add_new_test.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        nome: nome,
                        IDdoc: IDdoc,
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        alert('Errore durante l\'aggiornamento del titolo.');
                    }
                    location.reload();
                })
                .catch(error => {   
                    console.error('Errore nella richiesta:', error);
                    alert('Errore durante la connessione al server.');
                });


        }

    </script>


</body>

</html>