<?php

$logDir = __DIR__ . "/debug";
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug/log.txt');

session_start();
require("./../../backend/Include/db_connect.php");

$filter = isset($_GET['filter']) ? trim($_GET['filter']) : '';

if (isset($_POST["elimina"])) {

    $SQL_query = "  DELETE FROM tentativo
                    WHERE sessione_id = ? ";

    $stmt = $conn->prepare($SQL_query);
    $stmt->bind_param("i", $_POST["elimina"]);
    if ($stmt->execute()) {

        $SQL_query = "  DELETE FROM sessione
                        WHERE id = ? ";

        $stmt = $conn->prepare($SQL_query);
        $stmt->bind_param("i", $_POST["elimina"]);
        $stmt->execute();
    }

}

$SQL_query = "SELECT 
                s.id AS sessione_id,
                d_s.nome AS sessione_docente_nome,
                d_s.cognome AS sessione_docente_cognome,
                s.data_inizio AS sessione_inizio,
                s.data_fine AS sessione_fine,
                t.titolo AS test_titolo,
                d_t.nome AS test_docente_nome,
                d_t.cognome AS test_docente_cognome,
                c.nome AS classe_nome
            FROM 
                sessione AS s
            JOIN
                test AS t 
            ON 
                t.id=s.test_id
            JOIN 
                utente AS d_s
            ON 
                d_s.codice_fiscale=s.cf_docente
            JOIN 
                utente AS d_t
            ON 
                d_t.codice_fiscale=t.cf_docente
            JOIN 
                classe AS c
            ON 
                c.id=s.classe_id";

if ($filter !== '') {
    $SQL_query .= " AND ( nome LIKE ? OR cognome LIKE ? )";
}

$stmt = $conn->prepare($SQL_query);
if ($filter !== '') {
    $like_filter = '%' . $filter . '%';
    $stmt->bind_param("ss", $like_filter, $like_filter);
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
    </style>
</head>

<body style="background-color: rgb(240, 235, 248);">
    <?php include("components/navbar.php") ?>
    <div class="container mt-5">
        <?php if (isset($_POST['modifica'])): ?>

            <?php

            $SQL_query_test = "SELECT 
                                        t.titolo AS test_title,
                                        s.svolgibile,
                                        s.visibilita_tentativi,
                                        s.max_tentativi_ammessi
                                    FROM 
                                        sessione AS s
                                    JOIN 
                                        test AS t
                                    ON 
                                        t.id=s.test_id
                                    WHERE
                                        s.id=?";
            $stmt_test = $conn->prepare($SQL_query_test);
            $stmt_test->bind_param("i", $_POST['modifica']);
            $stmt_test->execute();
            // var_export($stmt_test->error_list);
            $result_test = $stmt_test->get_result();
            $row_test = $result_test->fetch_assoc();
            // var_export($row_test);
            $test_title = $row_test['test_title'];

            ?>

            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="sessioni.php">Sessioni</a></li>
                        <li class="breadcrumb-item"><a href="sessioni.php"><?= $test_title ?></a></li>
                        <li class="breadcrumb-item active">Modifica</li>
                    </ol>
                </nav>
            </div>

            <h2 class="text-center">Stai modificando le impostazioni di Sessione</h2>

            <form id="session-config-form" class="mt-5" onsubmit="return false;">
                <div class="mb-3">

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span>Visibilità dei tentativi inviati da parte degli studenti</span>
                        <button type="button" id="toggle-visibilita"
                            class="btn <?= $row_test['visibilita_tentativi'] ? 'btn-primary' : 'btn-outline-primary' ?>"
                            onclick="toggleSetting('visibilita_tentativi', this)"
                            data-status="<?= $row_test['visibilita_tentativi'] ? '1' : '0' ?>">
                            <?= $row_test['visibilita_tentativi'] ? 'Nascondi' : 'Rendi Visibile' ?>
                        </button>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span>Possibilità di svolgere il test <?= htmlspecialchars($test_title) ?></span>
                        <button type="button" id="toggle-possibilita"
                            class="btn <?= $row_test['svolgibile'] ? 'btn-primary' : 'btn-outline-primary' ?>"
                            onclick="toggleSetting('possibilita_test', this)"
                            data-status="<?= $row_test['svolgibile'] ? '1' : '0' ?>">
                            <?= $row_test['svolgibile'] ? 'Disabilita Test' : 'Abilita Test' ?>
                        </button>
                    </div>

                    <div class="mb-3">
                        <label for="numero-tentativi" class="form-label">Numero massimo di tentativi ammessi:</label>
                        <select class="form-select" id="numero-tentativi" name="numero_tentativi">
                            <option value="0" <?= $row_test['max_tentativi_ammessi'] == 0 ? 'selected' : '' ?>>Non definito
                                (tentativi infiniti)</option>
                            <?php for ($i = 1; $i <= 20; $i++): ?>
                                <option value="<?= $i ?>" <?= $row_test['max_tentativi_ammessi'] == $i ? 'selected' : '' ?>>
                                    <?= $i ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <button type="button" class="btn btn-primary" onclick="submitConfig()">Salva impostazioni</button>
            </form>
        </div>

        <div class="toast-container position-fixed bottom-0 end-0 p-3" id="toast-container"></div>
    <?php else: ?>
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Sessioni</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Lista delle Sessioni</h2>
            <form method="GET" class="d-flex justify-content-between">
                <input type="text" name="filter" class="form-control me-2" placeholder="Cerca sessione..."
                    value="<?php echo htmlspecialchars($filter); ?>">
                <button type="submit" class="btn btn-primary me-2">Cerca</button>
                <button type="submit" class="btn btn-success">Nuova</button>
            </form>
        </div>
        <form action="sessioni.php" method="post">
            <table class="table table-bordered table-striped">
                <thead class="table-primary">
                    <tr>
                        <th colspan="6">SESSIONE</th>
                        <th colspan="2">TEST</th>
                    </tr>
                    <tr>
                        <!-- s.id AS sessione_id,
                    d_s.nome AS sessione_docente_nome,
                    d_s.cognome AS sessione_docente_cognome,
                    s.data_inizio AS sessione_inizio,
                    s.data_fine AS sessione_fine,
                    t.titolo AS test_titolo,
                    d_t.nome AS test_docente_nome,
                    d_t.cognome AS test_docente_cognome,
                    c.nome AS classe_nome -->
                        <th>Modifica</th>
                        <th>Elimina</th>
                        <th>Assegnata A</th>
                        <th>Creato Da</th>
                        <th>Inizio</th>
                        <th>Fine</th>
                        <th>Titolo</th>
                        <th>Creato Da</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td> <button type=\"submit\" value=\"" . $row['sessione_id'] . "\" name=\"modifica\" class=\"btn btn-primary\">Modifica</button></td>";
                            echo "<td> <button type=\"submit\" value=\"" . $row['sessione_id'] . "\" name=\"elimina\" class=\"btn btn-danger\">X</button></td>";
                            echo "<td>" . htmlspecialchars($row['classe_nome']) . "</td>";
                            echo "<td>" . htmlspecialchars(ucfirst(strtolower($row['sessione_docente_nome'])) . " " . ucfirst(strtolower($row['sessione_docente_cognome']))) . "</td>";
                            echo "<td>" . htmlspecialchars($row['sessione_inizio']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['sessione_fine']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['test_titolo']) . "</td>";
                            echo "<td>" . htmlspecialchars(ucfirst(strtolower($row['test_docente_nome'])) . " " . ucfirst(strtolower($row['test_docente_cognome']))) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center'>Nessuna sessione trovata</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </form>
        </div>
    <?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSetting(setting, button) {
            const currentStatus = button.getAttribute("data-status");
            const newStatus = currentStatus === "1" ? "0" : "1";

            if (newStatus === "1") {
                button.textContent = setting === "visibilita_tentativi" ? "Nascondi" : "Disabilita Test";
                button.classList.remove("btn-outline-primary");
                button.classList.add("btn-primary");
            } else {
                button.textContent = setting === "visibilita_tentativi" ? "Rendi Visibile" : "Abilita Test";
                button.classList.remove("btn-primary");
                button.classList.add("btn-outline-primary");
            }

            button.setAttribute("data-status", newStatus);
        }

        function submitConfig() {
            const attempt_visibility = document.getElementById("toggle-visibilita").getAttribute("data-status");
            const test_status = document.getElementById("toggle-possibilita").getAttribute("data-status");
            const max_attempts = document.getElementById("numero-tentativi").value;

            const formData = {
                attempt_visibility: attempt_visibility,
                test_status: test_status,
                max_attempts: max_attempts,
                session_id: <?= $_POST['modifica'] ?? 'null' ?>
            };

            fetch("../../backend/API/session_config.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ data: formData })
            })
                .then(response => response.json())
                .then(data => {
                    showToast(data.message || "Impostazioni salvate!", "success");
                })
                .catch(error => {
                    console.error("Errore:", error);
                    showToast("Errore durante il salvataggio delle impostazioni.", "danger");
                });
        }

        function showToast(message, type) {
            const toastContainer = document.getElementById("toast-container");
            const toast = document.createElement("div");
            toast.className = `toast align-items-center text-bg-${type} border-0 show`;
            toast.role = "alert";
            toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>`;
            toastContainer.appendChild(toast);
            setTimeout(() => {
                toast.remove();
            }, 5000);
        }
    </script>
    <?php include("components/footer.php") ?>
</body>

</html>