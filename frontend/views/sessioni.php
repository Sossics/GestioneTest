<?php

session_start();
require("./../../backend/Include/db_connect.php");

$filter = isset($_GET['filter']) ? trim($_GET['filter']) : '';

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
        tr{
            text-align: center;
        }
    </style>
</head>

<body style="background-color: rgb(240, 235, 248);">
    <?php include("components/navbar.php") ?>
    <div class="container mt-5">
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
            <form method="GET" class="d-flex">
                <input type="text" name="filter" class="form-control me-2" placeholder="Cerca sessione..."
                    value="<?php echo htmlspecialchars($filter); ?>">
                <button type="submit" class="btn btn-primary">Cerca</button>
            </form>
        </div>
        <table class="table table-bordered table-striped">
            <thead class="table-primary">
                <tr>
                    <th colspan="5">SESSIONE</th>
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
                    <th>N°</th>
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
                        echo "<td>" . htmlspecialchars($row['sessione_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['classe_nome']) . "</td>";
                        echo "<td>" . htmlspecialchars(ucfirst(strtolower($row['sessione_docente_nome'])) . " " . ucfirst(strtolower($row['sessione_docente_cognome']))) . "</td>";
                        echo "<td>" . htmlspecialchars($row['sessione_inizio']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['sessione_fine']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['test_titolo']) . "</td>";
                        echo "<td>" . htmlspecialchars(ucfirst(strtolower($row['test_docente_nome'])) . " " . ucfirst(strtolower($row['test_docente_cognome']))) . "</td>";
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
</body>

</html>