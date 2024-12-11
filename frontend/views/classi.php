<?php

session_start();
require("./../../backend/Include/db_connect.php");

$filter = isset($_GET['filter']) ? trim($_GET['filter']) : '';

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
                <button type="submit" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exampleModal">Nuova</button>
            </form>
        </div>

            


        
        
        
        
        <table class="table table-bordered table-striped">
            <thead class="table-primary">
                <tr>
                    <th>Nome Classe</th>
                    <th>Anno Scolastico</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td> <a href=\" ./visualizza_classe.php?id=".$row['id']."\">" . htmlspecialchars($row['nome']) . "</a></td>";
                        echo "<td>" . htmlspecialchars($row['anno_scolastico']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='2' class='text-center'>Nessuna classe trovata</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    
    
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            ...
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary">Save changes</button>
        </div>
        </div>
    </div>
    </div>
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
</body>

</html>