<?php
session_start();
require("./../../backend/Include/db_connect.php");

$codice_fiscale = $_SESSION['user']['codice_fiscale'];

if(isset($_POST["nome"]) || isset($_POST["cognome"])){

    $new_nome = htmlspecialchars($_POST['nome']);
    $new_cognome = htmlspecialchars($_POST['cognome']);

    $SQL_query_test = "UPDATE utente SET nome = ? , cognome = ? WHERE codice_fiscale = ?";
    $stmt_test = $conn->prepare($SQL_query_test);
    $stmt_test -> bind_param('sss', $new_nome, $new_cognome, $codice_fiscale);
    $stmt_test->execute();

    $_SESSION['user']['nome'] = $new_nome;
    $_SESSION['user']['cognome'] = $new_cognome;

}

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
                        <!-- <form method="POST" action="profilo.php"> -->

                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($user['nome']); ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label for="cognome" class="form-label">Cognome</label>
                                <input type="text" class="form-control" id="cognome" name="cognome" value="<?php echo htmlspecialchars($user['cognome']); ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label for="login" class="form-label">Login</label>
                                <input type="text" class="form-control" id="login" name="login" value="<?php echo htmlspecialchars($user['login']); ?>" readonly disabled>
                            </div>
                            <div class="mb-3">
                                <label for="ruolo" class="form-label">Ruolo</label>
                                <input type="text" class="form-control" id="ruolo" name="ruolo" value="<?php echo htmlspecialchars($user['ruolo']); ?>" readonly disabled>
                            </div>

                            <button class="btn btn-primary" id="edit_save_button" data-action="edit">Modifica</button>

                        <!-- </form> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>

    document.addEventListener("DOMContentLoaded", (event) => {

            function updateUser(name, surname){
                fetch('../../backend/API/change_profile_info.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({new_name: name, new_surname: surname})
                })
                .then(response => response.json())
                .then(data => {
                    if(!data.success){
                        alert(data.error)
                    }else{
                        alert('Dati aggiornati con successo.');
                    }
                })
            }

            var edit_save_button = document.getElementById('edit_save_button');
            var name_input = document.getElementById("nome");
            var surname_input = document.getElementById("cognome");

            edit_save_button.addEventListener('click', (event) => {
                console.log(edit_save_button.attributes['data-action']);
                if(edit_save_button.attributes['data-action'].value == "edit"){
                    name_input.disabled = false;
                    surname_input.disabled = false;
                    edit_save_button.innerHTML = "Salva"
                    edit_save_button.attributes['data-action'].value = "save";
                    edit_save_button.classList.remove('btn-primary');
                    edit_save_button.classList.add('btn-success');
                }else if(edit_save_button.attributes['data-action'].value == "save"){
                    updateUser(name_input.value, surname_input.value);
                    name_input.disabled = true;
                    surname_input.disabled = true;
                    edit_save_button.innerHTML = "Modifica"
                    edit_save_button.attributes['data-action'].value = "edit";
                    edit_save_button.classList.remove('btn-success');
                    edit_save_button.classList.add('btn-primary');
                }
            })

        });

    </script>
</body>

</html>
