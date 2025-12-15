<?php
include 'db_config.php';

$id = $_POST['id'] ?? null;

if (!$id) {
    echo "napaka";
    exit;
}

$q = $conn->prepare("
    UPDATE Rezervacija
    SET status = 'cakajoce'
    WHERE rezervacija_id = ?
");
$q->bind_param("i", $id);
$q->execute();

echo "ok";
