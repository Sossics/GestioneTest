<?php

session_start();
require("./../../backend/Include/db_connect.php");

if(!isset($_GET['id'])){
    header("Location: classi.php");
}

$SQL_query = "  SELECT c.nome as nome
            FROM classe
            AS c 
           WHERE c.id=? LIMIT 1";

$stmt = $conn->prepare($SQL_query);
$stmt->bind_param("i", $_GET["id"]);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row=$result->fetch_assoc();
    $nome_classe=$row["nome"];
}else{
    header("Location: classi.php");
}



$SQL_query = "  SELECT s.nome AS nome_studente, s.cognome AS cognome_studente, s.codice_fiscale AS cf_studente
            FROM classe 
            AS c
            JOIN classe_studente
            AS c_s
            ON c.id = c_s.id_classe
            JOIN utente
            AS s
            ON c_s.cf_studente = s.codice_fiscale
            WHERE s.ruolo='STUDENTE' AND c.id=?";

$stmt = $conn->prepare($SQL_query);
$stmt->bind_param("i", $_GET["id"]);
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
                    <li class="breadcrumb-item"><a href="classi.php">Classi</a></li>
                    <li class="breadcrumb-item active"><?php echo $nome_classe?> </li>
                </ol>
            </nav>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2><?php echo $nome_classe?></h2>
        </div>
        <table class="table table-bordered table-striped">
            <thead class="table-primary">
                <tr>
                    <th>Nome</th>
                    <th>Cognome</th>
                    <th>Codice fiscale</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['nome_studente']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['cognome_studente']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['cf_studente']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='2' class='text-center'>Nessuna classe trovata</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>