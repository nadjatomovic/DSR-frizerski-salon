<?php
include 'db_config.php';

// Danasnji timestamp
$now = date("Y-m-d H:i:s");

// 1) Potrjeno → Opravljeno (ako je proslo)
$q1 = $conn->prepare("
    UPDATE Rezervacija
    SET status = 'opravljeno'
    WHERE status = 'potrjeno'
      AND datum_cas < ?
");
$q1->bind_param("s", $now);
$q1->execute();

// 2) Cakajoce → Preklicano (ako je proslo)
$q2 = $conn->prepare("
    UPDATE Rezervacija
    SET status = 'preklicano'
    WHERE status = 'cakajoce'
      AND datum_cas < ?
");
$q2->bind_param("s", $now);
$q2->execute();

echo "OK";
