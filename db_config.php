<?php
$servername = $_ENV['DB_HOST'];
$username   = $_ENV['DB_USER'];
$password   = $_ENV['DB_PASS'];
$database   = $_ENV['DB_NAME'];


$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Greška pri povezivanju s bazom: " . $conn->connect_error);
}
?>
