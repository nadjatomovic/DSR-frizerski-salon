<?php
session_start();
include 'db_config.php';

// samo frizer može izvoziti kalendar
if (!isset($_SESSION['user']) || $_SESSION['user']['tip'] != 1) {
    exit("Dostop zavrnjen");
}

$frizer_id = $_SESSION['user']['id'];

require("fpdf/fpdf.php");

// Povuci rezervacije frizera
$q = $conn->prepare("
    SELECT 
        r.rezervacija_id,
        r.datum_cas,
        r.trajanje_min,
        r.status,
        u.ime AS stranka_ime,
        u.priimek AS stranka_priimek,
        s.naziv AS storitev
    FROM Rezervacija r
    JOIN Rezervacija_Storitev rs ON r.rezervacija_id = rs.rezervacija_id
    JOIN Storitev s ON s.storitev_id = rs.storitev_id
    JOIN Uporabnik u ON u.uporabnik_id = r.uporabnik_stranka_id
    WHERE r.uporabnik_frizer_id = ?
    ORDER BY r.datum_cas ASC
");
$q->bind_param("i", $frizer_id);
$q->execute();
$rez = $q->get_result();

// --- PDF GENERISANJE ---
$pdf = new FPDF();
$pdf->AddPage();

$pdf->SetFont('Helvetica','B',16);
$pdf->Cell(0,10,'Kalendar rezervacij',0,1,'C');

$pdf->SetFont('Helvetica','',12);
$pdf->Ln(5);

// header tabele
$pdf->SetFont('Helvetica','B',11);
$pdf->Cell(40,8,'Datum',1);
$pdf->Cell(40,8,'Stranka',1);
$pdf->Cell(55,8,'Storitev',1);
$pdf->Cell(25,8,'Trajanje',1);
$pdf->Cell(30,8,'Status',1);
$pdf->Ln();

// telo tabele
$pdf->SetFont('Helvetica','',10);

while ($r = $rez->fetch_assoc()) {
    $pdf->Cell(40,8, date("d.m.Y H:i", strtotime($r['datum_cas'])),1);
    $pdf->Cell(40,8, $r['stranka_ime']." ".$r['stranka_priimek'],1);
    $pdf->Cell(55,8, $r['storitev'],1);
    $pdf->Cell(25,8, $r['trajanje_min'].' min',1);
    $pdf->Cell(30,8, ucfirst($r['status']),1);
    $pdf->Ln();
}

$pdf->Output('D', 'kalendar_'.$frizer_id.'.pdf');
exit;
?>
