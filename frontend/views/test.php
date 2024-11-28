<?php

session_start();
require("./../../backend/Include/db_connect.php");
$filter = isset($_GET['filter']) ? trim($_GET['filter']) : '';

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
</head>

<body style="background-color: rgb(240, 235, 248);">
    <?php include("components/navbar.php") ?>
    <div class="container mt-5">
        <div>
            <?php if (isset($_POST['id'])): ?>
                <?php

                $row = $result->fetch_assoc();

                ?>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="test.php">Test</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?= $row['test_titolo'] ?></li>
                    </ol>
                </nav>
            <?php else: ?>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active">Test</a></li>
                    </ol>
                </nav>
            <?php endif; ?>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <?php if (isset($_POST['id'])): ?>
                <h3>Stai visualizzando il Test "<?= $row['test_titolo'] ?>" del docente "<?= htmlspecialchars(ucfirst(strtolower($row['nome_docente'])) . " " . ucfirst(strtolower($row['cognome_docente']))) ?>"</h3>
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

            $SQL_query_fetch_tries = "SELECT
                                        COUNT(*) AS tentativi_usati
                                      FROM 
                                        tentativo AS t
                                      JOIN
                                        sessione AS s 
                                      ON 
                                        t.sessione_id=s.id
                                      WHERE
                                        s.test_id=?
                                      ";
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
                    stu.codice_fiscale=?";

            $stmt_attempt_details = $conn->prepare($SQL_query_attempt_details);
            $stmt_attempt_details->bind_param("is", $_POST['id'], $_SESSION['user']['codice_fiscale']);
            $stmt_attempt_details->execute();
            $result_attempt_details = $stmt_attempt_details->get_result();

            echo "<table class='table table-bordered w-50 mt-4'>";
            echo "<thead>
                    <tr>
                        <th colspan='3' class='bg-light'>Tentativi</th>
                    </tr>
                    <tr>
                        <th class='text-start'>Inviato il</th>
                        <th class='text-start'>Punteggio</th>
                        <th class='text-start'>Valutazione</th>
                    </tr>
                 </thead>";
            echo "<tbody>";

            if ($result_attempt_details->num_rows > 0) {
                while ($row_attempt = $result_attempt_details->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row_attempt['inviato_il']) . "</td>";
                    // echo "<td>" . htmlspecialchars($row_attempt['punteggio']) . "</td>";
                    echo "<td>" . "N/D" . "</td>";
                    // echo "<td>" . htmlspecialchars($row_attempt['valutazione']) . "</td>";
                    echo "<td>" . "N/D" . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='3' class='text-center'>Nessun tentativo trovato</td></tr>";
            }

            echo "</tbody>";
            echo "</table>";
        } else {

            if ($result && $result->num_rows > 0) {
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
                        echo "<a href='visualizza_test.php?id=" . htmlspecialchars($row['test_id']) . "&modalita=mod' class='btn btn-warning btn-sm'>
                                        <i data-feather='edit'></i>
                                     </a>";
                        echo "<a href='visualizza_test.php?id=" . htmlspecialchars($row['test_id']) . "&modalita=vis' class='btn btn-info btn-sm'>
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
    </script>
</body>

</html>