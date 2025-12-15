<?php
include 'db_config.php';
session_start();

// frizer ali stranka lahko prekliče
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    exit("Dostop zavrnjen");
}

$id = $_POST['id'] ?? ($_GET['id'] ?? null);

if (!$id) {
    http_response_code(400);
    exit("Manjka ID");
}

// Prekliči rezervacijo
$q = $conn->prepare("UPDATE Rezervacija SET status='preklicano' WHERE rezervacija_id=?");
$q->bind_param("i", $id);
$q->execute();

echo "ok";
?>
