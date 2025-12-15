<?php
require_once 'db_config.php'; // učitava tvoju postojeću konekciju

if ($conn->connect_error) {
    die("❌ Povezivanje na bazu nije uspelo: " . $conn->connect_error);
}

echo "✅ Uspesno povezano sa bazom FrizerskiSalon!";
$conn->close();
?>
