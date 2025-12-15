<?php
include 'db_config.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['tip'] != 1) {
    die("Dostop zavrnjen.");
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("DELETE FROM Izdelek WHERE izdelek_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: izdelki.php");
exit;
