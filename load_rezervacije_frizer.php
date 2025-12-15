<?php
include 'db_config.php';
session_start();

if (isset($_GET['debug'])) {
    header("Content-Type: text/plain; charset=utf-8");
    print_r($_SESSION);
    exit;
}

if (!isset($_SESSION['user']) || $_SESSION['user']['tip'] != 1) {
    echo "[]";
    exit;
}


$frizer = $_SESSION['user']['id'];

$q = $conn->prepare("
    SELECT 
        r.rezervacija_id,
        r.datum_cas,
        r.trajanje_min,
        r.status,
        u.ime, u.priimek,
        s.naziv
    FROM Rezervacija r
    JOIN Uporabnik u ON u.uporabnik_id = r.uporabnik_stranka_id
    JOIN Rezervacija_Storitev rs ON rs.rezervacija_id = r.rezervacija_id
    JOIN Storitev s ON s.storitev_id = rs.storitev_id
    WHERE r.uporabnik_frizer_id = ?
");
$q->bind_param("i", $frizer);
$q->execute();
$res = $q->get_result();

$events = [];

while ($row = $res->fetch_assoc()) {

    // Dodeli boju po statusu
    $barva = "#3788d8"; // default FullCalendar plava

    switch ($row['status']) {
       case 'cakajoce':  $barva = "#f7d774"; break;  // žuta
       case 'potrjeno':  $barva = "#8cd47e"; break;  // zelena
       case 'opravljeno': $barva = "#bcbcbc"; break; // siva
       case 'preklicano': $barva = "#e57373"; break; // crvena
    }


    $start = $row['datum_cas'];
    $end = date("Y-m-d H:i:s", strtotime($start) + $row['trajanje_min'] * 60);

    $events[] = [
        "title" => $row['ime'] . " — " . $row['naziv'],
        "start" => $start,
        "end"   => $end,
        "rezervacija_id" => $row['rezervacija_id'],
        "stranka" => $row['ime'] . " " . $row['priimek'],
        "storitev" => $row['naziv'],
        "status" => $row['status'],
        "color"  => $barva
    ];
}

echo json_encode($events);
