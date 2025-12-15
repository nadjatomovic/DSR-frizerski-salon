<?php
include 'db_config.php';
session_start();

// 1) Samo stranka sme ustvarjati
if (!isset($_SESSION['user']) || $_SESSION['user']['tip'] != 2) {
    header("Location: login.php");
    exit;
}

$stranka = $_SESSION['user']['id'];

// 2) Preberi POST podatke
$frizer   = $_POST['frizer_id']   ?? null;
$storitev = $_POST['storitev_id'] ?? null;
$datetime = $_POST['datum_cas']   ?? null;

// ------------------------------
// VALIDACIJA
// ------------------------------
if (!$frizer || !$storitev || !$datetime) {
    $_SESSION['rez_error'] = "Manjkajo podatki za ustvarjanje rezervacije.";
    header("Location: stranka_rezervacije.php");
    exit;
}

// Datum mora biti u budućnosti
$now = new DateTime();
$izabran = new DateTime($datetime);
if ($izabran <= $now) {
    $_SESSION['rez_error'] = "Ne morete rezervirati termin v preteklosti.";
    header("Location: stranka_rezervacije.php");
    exit;
}

// ------------------------------
// 3) Povleci trajanje storitve
// ------------------------------
$stmtT = $conn->prepare("SELECT trajanje_min FROM Storitev WHERE storitev_id = ?");
$stmtT->bind_param("i", $storitev);
$stmtT->execute();
$stmtT->bind_result($trajanje);
$stmtT->fetch();
$stmtT->close();

if (!$trajanje) $trajanje = 30;

// ------------------------------
// 4) Preveri prekrivanje terminov
// ------------------------------
$zacetek = date("Y-m-d H:i:s", strtotime($datetime));
$konec   = date("Y-m-d H:i:s", strtotime("$datetime +{$trajanje} minutes"));

$check = $conn->prepare("
    SELECT COUNT(*)
    FROM Rezervacija
    WHERE uporabnik_frizer_id = ?
      AND status IN ('cakajoce', 'potrjeno')
      AND (
            (datum_cas < ? AND DATE_ADD(datum_cas, INTERVAL COALESCE(trajanje_min,30) MINUTE) > ?)
         OR (datum_cas >= ? AND datum_cas < ?)
      )
");
$check->bind_param("issss", 
    $frizer,
    $konec,      
    $zacetek,    
    $zacetek,    
    $konec       
);
$check->execute();
$check->bind_result($count);
$check->fetch();
$check->close();

// Če se termin prekriva
if ($count > 0) {
    $fr = $conn->query("SELECT ime, priimek FROM Uporabnik WHERE uporabnik_id = $frizer")->fetch_assoc();
    $ime = htmlspecialchars($fr['ime'] . " " . $fr['priimek']);

    $_SESSION['rez_error'] =
        "Oprostite, frizer <strong>$ime</strong> je zaseden v izbranem terminu.<br>
         Prosimo, izberite drug dan ali uro.";

    header("Location: stranka_rezervacije.php");
    exit;
}

// ------------------------------
// 5) Vstavi rezervacijo
// ------------------------------
$stmt = $conn->prepare("
    INSERT INTO Rezervacija (datum_cas, status, uporabnik_stranka_id, uporabnik_frizer_id, trajanje_min)
    VALUES (?, 'cakajoce', ?, ?, ?)
");
$stmt->bind_param("siii", $datetime, $stranka, $frizer, $trajanje);
$stmt->execute();
$rezervacija_id = $stmt->insert_id;
$stmt->close();

// ------------------------------
// 6) Poveži storitev
// ------------------------------
$stmt2 = $conn->prepare("
    INSERT INTO Rezervacija_Storitev (rezervacija_id, storitev_id, kolicina)
    VALUES (?, ?, 1)
");
$stmt2->bind_param("ii", $rezervacija_id, $storitev);
$stmt2->execute();
$stmt2->close();

// ------------------------------
// 7) Success
// ------------------------------
$_SESSION['rez_ok'] = "Vaša rezervacija je bila uspešno poslana. Čaka na potrditev frizerja.";
header("Location: stranka_rezervacije.php");
exit;

?>
