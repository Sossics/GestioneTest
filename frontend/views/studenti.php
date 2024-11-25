<?php

session_start();
require("./../../backend/Include/db_connect.php");

$filter = isset($_GET['filter']) ? trim($_GET['filter']) : '';

$SQL_query = "SELECT 
                nome, cognome, codice_fiscale
            FROM 
                utente
            WHERE
                ruolo = 'STUDENTE'";

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
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Lista degli Studenti</h2>
            <form method="GET" class="d-flex">
                <input type="text" name="filter" class="form-control me-2" placeholder="Cerca studente..."
                    value="<?php echo htmlspecialchars($filter); ?>">
                <button type="submit" class="btn btn-primary">Cerca</button>
            </form>
        </div>
        <table class="table table-bordered table-striped">
            <thead class="table-primary">
                <tr>
                    <th>Nome</th>
                    <th>Cognome</th>
                    <th>Codice Fiscale</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['nome']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['cognome']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['codice_fiscale']) . "</td>";
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
</body>

</html>