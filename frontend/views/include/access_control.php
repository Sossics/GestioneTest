<?php

$file = fopen("debug.txt", "a+");
fwrite($file, var_export($_SESSION, true) . "\n");


function isURLAvailableForRole($current_url, $availableURLs)
{
    foreach ($availableURLs as $url) {
        if ($url === "*" || strpos($current_url, $url) !== false) {
            return true;
        }
    }
    return false;
}

function redirectToLogin()
{
    echo "<center>
                <br><br>
                <h1><b>Questa sezione è accessibile al solo personale autorizzato</b></h1>
                <h3>reindirizzamento in corso</h3>
            </center>";
    header("refresh:3;url=login.php");
}

$redirectURLsIfLoggedIN = [
    'STUDENTE' => ['url' => "index.php"],
    'DOCENTE' => ['url' => "index.php"]
];

$availableURLsForRole = [
    'STUDENTE' => [
        "index.php",
        "test.php",
        "profilo.php",
    ],
    'DOCENTE' => [
        "index.php",
        "test.php",
        "profilo.php",
        "classi.php",
        "sessioni.php",
        "studenti.php",
        "aggiungi_classe.php",
        "aggiungi_sessione.php",
        "aggiungi_test.php",
        "aggiungi_utente.php",
    ]


];
$current_url = $_SERVER['REQUEST_URI'];

if (!isset($_SESSION['user'])) {

    header("Location:login.php");

} else {
    switch ($_SESSION['user']['ruolo']) {
        case "STUDENTE":
            if (isset($availableURLsForRole['STUDENTE'])) {
                if (!isURLAvailableForRole($current_url, $availableURLsForRole['STUDENTE'])) {
                    fwrite($file, "Redirecting to Login, Failed test line: 150 ($current_url NOT AVAILABLE FOR STUDENTE) with ". $_SERVER['REQUEST_URI'] . "\n");
                    redirectToLogin();
                    exit();
                } else {
                    fwrite($file, "Access Authorized for STUDENTE with  ". $_SERVER['REQUEST_URI'] . "\n");
                }
            } else {
                fwrite($file, "Redirecting to Login, Failed test line: 148 ($current_url NOT AVAILABLE FOR STUDENTE) with ". $_SERVER['REQUEST_URI'] . "\n");
                redirectToLogin();
                exit();
            }
            break;
        case "DOCENTE":
            if (isset($availableURLsForRole['DOCENTE'])) {
                if (!isURLAvailableForRole($current_url, $availableURLsForRole['DOCENTE'])) {
                    fwrite($file, "Redirecting to Login, Failed test line: 150 ($current_url NOT AVAILABLE FOR DOCENTE) with ". $_SERVER['REQUEST_URI'] . "\n");
                    redirectToLogin();
                    exit();
                } else {
                    fwrite($file, "Access Authorized for DOCENTE with  ". $_SERVER['REQUEST_URI'] . "\n");
                }
            } else {
                fwrite($file, "Redirecting to Login, Failed test line: 148 ($current_url NOT AVAILABLE FOR DOCENTE) with ". $_SERVER['REQUEST_URI'] . "\n");
                redirectToLogin();
                exit();
            }
            break;
        default:
            fwrite($file, "Undefined and Unauthorized user tried to access:  ". $_SERVER['REQUEST_URI'] . "\n");
            redirectToLogin();
            exit();
    }
}

// exit();
?>