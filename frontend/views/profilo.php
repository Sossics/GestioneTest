<?php
session_start();
require("./../../backend/Include/db_connect.php");

$codice_fiscale = $_SESSION['user']['codice_fiscale'];

$SQL_query = "SELECT nome, cognome, login, ruolo FROM utente WHERE codice_fiscale = ?";
$stmt = $conn->prepare($SQL_query);
$stmt->bind_param("s", $codice_fiscale);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Utente non trovato.";
    exit();
}

$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilo Utente - <?php echo htmlspecialchars($user['nome'] . ' ' . $user['cognome']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php include("components/head.php"); ?>
</head>

<body style="background-color: rgb(240, 235, 248);">
    <?php include("components/navbar.php"); ?>

    <div class="container mt-5">
        <h2 class="text-center">Profilo di <?php echo htmlspecialchars($user['nome']) . ' ' . htmlspecialchars($user['cognome']); ?></h2>
        
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="profilo.php">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($user['nome']); ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="cognome" class="form-label">Cognome</label>
                                <input type="text" class="form-control" id="cognome" name="cognome" value="<?php echo htmlspecialchars($user['cognome']); ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="login" class="form-label">Login</label>
                                <input type="text" class="form-control" id="login" name="login" value="<?php echo htmlspecialchars($user['login']); ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="ruolo" class="form-label">Ruolo</label>
                                <input type="text" class="form-control" id="ruolo" name="ruolo" value="<?php echo htmlspecialchars($user['ruolo']); ?>" readonly>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
