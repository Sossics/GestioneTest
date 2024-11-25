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
                dm.test_id = t.id
            GROUP BY 
                t.id, t.titolo, d.nome, d.cognome";

if ($filter !== '') {
    $SQL_query .= " AND ( t.titolo LIKE ? OR d.nome LIKE ? OR d.cognome LIKE ?)";
}

$stmt = $conn->prepare($SQL_query);
if ($filter !== '') {
    $like_filter = '%' . $filter . '%';
    $stmt->bind_param("sss", $like_filter, $like_filter);
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
        tr{
            text-align: center;
        }
    </style>
</head>

<body style="background-color: rgb(240, 235, 248);">
    <?php include("components/navbar.php") ?>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Lista delle Sessioni</h2>
            <form method="GET" class="d-flex">
                <input type="text" name="filter" class="form-control me-2" placeholder="Cerca test..."
                    value="<?php echo htmlspecialchars($filter); ?>">
                <button type="submit" class="btn btn-primary">Cerca</button>
            </form>
        </div>
        <table class="table table-bordered table-striped">
            <thead class="table-primary">
                <tr>
                    <th>N°</th>
                    <th>Titolo</th>
                    <th>Creato Da</th>
                    <th>N° di Domande</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['test_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['test_titolo']) . "</td>";
                        echo "<td>" . htmlspecialchars(ucfirst(strtolower($row['nome_docente'])) . " " . ucfirst(strtolower($row['cognome_docente']))) . "</td>";
                        echo "<td>" . htmlspecialchars($row['numero_domande']) . "</td>";
                        echo "<td class='action-btn'>
                                <a href='modifica_test.php?id=" . htmlspecialchars($row['test_id']) . "' class='btn btn-warning btn-sm'>
                                    <i data-feather='edit'></i>
                                </a>
                                <a href='visualizza_test.php?id=" . htmlspecialchars($row['test_id']) . "' class='btn btn-info btn-sm'>
                                    <i data-feather='eye'></i>
                                </a>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3' class='text-center'>Nessun studente trovato</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php include("components/footer.php")?>
    <script>
        feather.replace();
    </script>
</body>

</html>