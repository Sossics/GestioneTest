<?php

    require("./../../backend/include/db_connect.php");

    session_start();

    if(isset($_SESSION['user'])){
        header("Location: index.php");
    }

    $error_msg = "";

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $login = $_POST['username'];
        $password = $_POST['password'];
        
        $SQL_query = "SELECT * 
                        FROM utente
                        WHERE 
                            login = ?
                        AND
                            password = ?";
        $stmt = $conn->prepare($SQL_query);
        $stmt->bind_param("ss", $login, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows == 1){
            $_SESSION['user'] = $result->fetch_assoc();
            $_SESSION['logged_in'] = true;
            header("Location: index.php");
            exit();
        }else{
            $error_msg = "Credenziali non valide. Riprova.";
        }
    }

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body style="background-color: rgb(240, 235, 248);">
        <div class="container">
            <div class="row justify-content-center mt-5">
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-header text-center">
                            <h3>Login</h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($error_msg)): ?>
                                <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                            <?php endif; ?>
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="login" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Accedi</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>