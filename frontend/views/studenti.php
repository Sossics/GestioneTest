<?php

session_start();
require("./../../backend/Include/db_connect.php");

$filter = isset($_GET['filter']) ? trim($_GET['filter']) : '';


if (isset($_POST["elimina"])) {
    
    $SQL_query = "  DELETE FROM utente WHERE utente.codice_fiscale = ?";

    $stmt = $conn->prepare($SQL_query);
    $stmt->bind_param("s", $_POST["elimina"]);
    $stmt->execute();

}

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
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Studenti</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Lista degli Studenti</h2>
            <form method="GET" class="d-flex">
                <input type="text" name="filter" class="form-control me-2" placeholder="Cerca studente..."
                    value="<?php echo htmlspecialchars($filter); ?>">
                <button type="submit" class="btn btn-primary me-2">Cerca</button>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#nuovoStudente">Aggiungi</button>
            </form>
        </div>
        <table class="table table-bordered table-striped">
        <form action="studenti.php" method="post">
            <thead class="table-primary">
                <tr>
                    <th>Elimina</th>
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
                        echo "<td> <button type=\"submit\" value=\"" . $row['codice_fiscale'] . "\" name=\"elimina\" class=\"btn btn-danger\">X</button></td>";
                        echo "<td>" . htmlspecialchars($row['nome']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['cognome']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['codice_fiscale']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' class='text-center'>Nessun studente trovato</td></tr>";
                }
                ?>
            </tbody>
            </form>
        </table>
    </div>

    <div class="modal fade" id="nuovoStudente" tabindex="-1" aria-labelledby="titoloModal" aria-hidden="true">
    <div class="modal-dialog">
        <form action="classi.php" method="post">
        <div class="modal-content">
        <div class="modal-header">
            <h1 class="modal-title fs-5" id="titoloModal">Aggiunta Studente</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            
            <label for="nome_studente" class="col-form-label">Nome: </label>
            <input type="text" class="form-control" id="nome_studente" name="nome_studente" required>

            <label for="cognome_studente" class="col-form-label">Cognome: </label>
            <input type="text" class="form-control" id="cognome_studente" name="cognome_studente" required>

            <label for="codice_studente" class="col-form-label">Codice fiscale: </label>
            <input type="text" class="form-control" id="codice_studente" name="codice_studente" required>

            
            <label for="login_studente" class="col-form-label">Login: </label>
            <input type="text" class="form-control" id="login_studente" name="login_studente" required>

            <label for="password_studente" class="col-form-label">Password: </label>
            <input type="text" class="form-control" id="password_studente" name="password_studente" value="password" required>
            
            <input type="hidden" name="ruolo_studente" id="ruolo_studente" value="STUDENTE">

        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
            <button type="button" class="btn btn-primary" onclick='aggiungiStudente(this);' >Aggiungi</button>
        </div>
        </div>
        </form>
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>

            function aggiungiStudente(context){

                const nome = document.getElementById("nome_studente").value;
                const cognome = document.getElementById("cognome_studente").value;
                const codice = document.getElementById("codice_studente").value;
                const login = document.getElementById("login_studente").value;
                const password = document.getElementById("password_studente").value;
                const ruolo = document.getElementById("ruolo_studente").value;
                
                fetch('../../backend/API/add_new_user.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            nome: nome,
                            cognome: cognome,
                            codice: codice,
                            login: login,
                            password: password,
                            ruolo: ruolo,
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