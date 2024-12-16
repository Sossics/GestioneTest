<?php

session_start();
require("./../../backend/Include/db_connect.php");

if (!isset($_GET['id'])) {
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
    $row = $result->fetch_assoc();
    $nome_classe = $row["nome"];
} else {
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
        tr {
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
                    <li class="breadcrumb-item active"><?php echo $nome_classe ?> </li>
                </ol>
            </nav>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2><?php echo $nome_classe ?></h2>
            <button type="button" class="btn btn-success" onclick="openModal(<?=$_GET['id']?>)">Assegna Studenti</button>
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
                    echo "<tr><td colspan='3' class='text-center'>Nessun studente trovato</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="modal fade" id="assignModal" tabindex="-1" aria-labelledby="assignModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignModalLabel">Assegna gli studenti alla classe <?=$nome_classe?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                <form id="assignModalForm">
                    <input type="hidden" name="classe" value="<?=$_GET['id']?>">
                        <div class="mb-3">
                            <h6>Seleziona Studenti</h6>
                            <p class="info">Seleziona gli studenti a cui verrà assegnata la classe <?=$nome_classe?></p>
                            <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr class="text-white">
                                            <th>Seleziona</th>
                                            <th>Nome</th>
                                            <th>Cognome</th>
                                            <th>Assegnato a</th>
                                        </tr>
                                    </thead>
                                    <tbody id="studenteTableBody">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">Chiudi</button>
                            <button type="submit" class="btn btn-primary border-0">Assegna</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>

        document.addEventListener("DOMContentLoaded", function () {

            const studenteTableBody = document.getElementById("studenteTableBody");
            const form = document.getElementById('assignModalForm');
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const selectedCheckboxes = document.querySelectorAll('input[name="studenti[]"]:checked');
                const selectedValues = Array.from(selectedCheckboxes).map(checkbox => checkbox.value);
                fetch("../../backend/API/fetchStudenti.php", {
                    method: "POST",
                    body: JSON.stringify({studenti: selectedValues, classe: <?=$_GET['id']?>})
                })
                .then(response => response.json())
                .then(data => {
                    if(data.msg == 'success'){
                        closeModal();
                    }
                })
            });
            
        });

        function loadStudenti() {
            fetch("../../backend/API/fetchStudenti.php")
                .then(response => response.json())
                .then(data => {
                    studenteTableBody.innerHTML = "";
                    data.forEach(obj => {
                        const row = document.createElement("tr");
                        row.innerHTML = `
                            <td><input type="checkbox" name="studenti[]" value="${obj.studente_cf}"></td>
                            <td>${obj.studente_nome}</td>
                            <td>${obj.studente_cognome}</td>
                            <td>${obj.classe}</td>
                        `;
                        studenteTableBody.appendChild(row);
                    });
                });
        }

        function openModal(classID){
            $('#assignModal').modal('show');
            loadStudenti();
        }

        function closeModal() {
            $('#assignModal').modal('hide');
        }
    </script>
</body>

</html>