<?php
$servername = "localhost";
$username = "root";
$password = "";
$schema = "gestione_test";

$conn = new mysqli($servername, $username, $password, $schema);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>