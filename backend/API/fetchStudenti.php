<?php
    include './../include/db_connect.php';

    $sql = "SELECT u.codice_fiscale AS studente_cf, u.nome AS studente_nome, u.cognome AS studente_cognome, c.nome AS classe FROM utente AS u LEFT JOIN classe_studente AS cs ON cs.cf_studente=u.codice_fiscale LEFT JOIN classe AS c ON c.id=cs.id_classe WHERE ruolo = 'STUDENTE'";
    $result = $conn->query($sql);

    $docenti = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $docenti[] = $row;
        }
    }

    echo json_encode($docenti);

    $conn->close();
?>