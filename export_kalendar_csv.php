<?php
session_start();
include 'db_config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['tip'] != 1) {
    die("Niste prijavljeni kao frizer.");
}

$frizer_id = $_SESSION['user']['id'];

$stmt = $conn->prepare("
    SELECT 
        r.datum_cas,
        r.status,
        r.trajanje_min,
        CONCAT(s.ime, ' ', s.priimek) AS stranka,
        st.naziv AS storitev
    FROM Rezervacija r
    JOIN Uporabnik s ON r.uporabnik_stranka_id = s.uporabnik_id
    JOIN Rezervacija_Storitev rs ON rs.rezervacija_id = r.rezervacija_id
    JOIN Storitev st ON st.storitev_id = rs.storitev_id
    WHERE r.uporabnik_frizer_id = ?
    ORDER BY r.datum_cas DESC
");
$stmt->bind_param("i", $frizer_id);
$stmt->execute();
$result = $stmt->get_result();

// Postavi CSV headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=koledar_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');

// Header reda
fputcsv($output, ['Datum & Čas', 'Stranka', 'Storitev', 'Status', 'Trajanje (min)']);

// Podaci
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        date('d.m.Y H:i', strtotime($row['datum_cas'])),
        $row['stranka'],
        $row['storitev'],
        ucfirst($row['status']),
        (int)$row['trajanje_min']
    ]);
}

fclose($output);
$stmt->close();
exit;
?>