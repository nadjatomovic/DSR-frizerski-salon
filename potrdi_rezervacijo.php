<?php
include 'db_config.php';
session_start();

// samo frizer lahko potrjuje
if (!isset($_SESSION['user']) || $_SESSION['user']['tip'] != 1) {
    http_response_code(403);
    exit("Dostop zavrnjen");
}

$id = $_POST['id'] ?? null;

if (!$id) {
    http_response_code(400);
    exit("Manjka ID");
}

// Potrdi rezervacijo
$q = $conn->prepare("UPDATE Rezervacija SET status='potrjeno' WHERE rezervacija_id=?");
$q->bind_param("i", $id);
$q->execute();

echo "ok";
?>
