<?php

session_start();
require("./../../backend/Include/db_connect.php");

$filter = isset($_GET['filter']) ? trim($_GET['filter']) : '';

if (isset($_POST["elimina"])) {
    
    $SQL_query = "  DELETE FROM classe WHERE classe.id = ?";

    $stmt = $conn->prepare($SQL_query);
    $stmt->bind_param("i", $_POST["elimina"]);
    $stmt->execute();

}

$SQL_query = "SELECT 
                nome, anno_scolastico, id
            FROM 
                classe";

if ($filter !== '') {
    $SQL_query .= " WHERE nome LIKE ?";
}

$stmt = $conn->prepare($SQL_query);
if ($filter !== '') {
    $like_filter = '%' . $filter . '%';
    $stmt->bind_param("s", $like_filter);
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
                    <li class="breadcrumb-item active">Classi</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Lista delle Classi</h2>
            <form method="GET" class="d-flex">
                <input type="text" name="filter" class="form-control me-2" placeholder="Cerca classe..."
                    value="<?php echo htmlspecialchars($filter); ?>">
                <button type="submit" class="btn btn-primary me-2">Cerca</button>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#nuovaClasse">Nuova</button>
            </form>
        </div>
  
        
        <table class="table table-bordered table-striped">
        <form action="classi.php" method="post">
            <thead class="table-primary">
                <tr>
                    <th>Elimina</th>
                    <th>Nome Classe</th>
                    <th>Anno Scolastico</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td> <button type=\"submit\" value=\"" . $row['id'] . "\" name=\"elimina\" class=\"btn btn-danger\">X</button></td>";
                        echo "<td> <a href=\" ./visualizza_classe.php?id=".$row['id']."\">" . htmlspecialchars($row['nome']) . "</a></td>";
                        echo "<td>" . htmlspecialchars($row['anno_scolastico']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3' class='text-center'>Nessuna classe trovata</td></tr>";
                }
                ?>
            </tbody>
            </form>
        </table>
    </div>
    
    
    <div class="modal fade" id="nuovaClasse" tabindex="-1" aria-labelledby="titoloModal" aria-hidden="true">
    <div class="modal-dialog">
        <form action="classi.php" method="post">
        <div class="modal-content">
        <div class="modal-header">
            <h1 class="modal-title fs-5" id="titoloModal">Aggiunta Classe</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            
            <label for="nome_classe" class="col-form-label">Nome: </label>
            <input type="text" class="form-control" id="nome_classe" name="nome_classe" required>

            <label for="anno_classe" class="col-form-label">Anno: </label>
            <!-- <input type="text" id="anno_classe" class="form-control" name="anno_classe" maxlength="9" minlength="9" placeholder="20../20.." required> -->
            <select name="anno_classe" id="anno_classe" class="form-control">
                <?php for ($current = (int) date('Y') - 10, $i = $current, $until = $i + 20; $i <= $until; $i++): ?>
                    <option value="<?=$i;?>/<?=$i+1;?>"<?=($current === $i ? ' selected="selected"' : NULL);?>><?=$i;?>/<?=$i+1?></option>
                <?php endfor; ?>
            </select>

        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
            <button type="button" class="btn btn-primary" onclick='aggiungiClasse(this);' >Aggiungi</button>
        </div>
        </div>
        </form>
    </div>
    </div>
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>

            function aggiungiClasse(context){

                const nome = document.getElementById("nome_classe").value;
                const anno = document.getElementById("anno_classe").value;
                console.log(nome);
                console.log(anno);
                

                fetch('../../backend/API/add_new_class.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            nome: nome,
                            anno: anno,
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