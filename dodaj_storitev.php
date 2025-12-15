<?php
include 'db_config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $naziv = $_POST['naziv'];
    $opis = $_POST['opis'];
    $cena = $_POST['cena'];
    $trajanje = $_POST['trajanje'];

    // upload slike
    $slika = $_FILES['slika']['name'];
    $target = "images/" . basename($slika);

    if (!empty($slika)) {
        move_uploaded_file($_FILES['slika']['tmp_name'], $target);
    }

    $stmt = $conn->prepare("
        INSERT INTO Storitev (naziv, opis, cena, trajanje_min, slika)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ssdis", $naziv, $opis, $cena, $trajanje, $slika);

    if ($stmt->execute()) {
        $message = "Storitev uspešno dodana!";
    } else {
        $message = "Napaka: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Dodaj storitev</title>
    <link rel="stylesheet" href="style.css">

    <script>
        function potrdiPreklic() {
            return confirm("Ali res želite zapustiti stran? Vnešeni podatki ne bodo shranjeni.");
        }
    </script>
</head>
<body>

<div class="form-wrapper">
  <div class="form-container" style="max-width: 480px; margin-top: 40px;">
    <h2>Dodaj novo storitev</h2>

    <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>

    <form method="POST" enctype="multipart/form-data">

        <label>Naziv</label>
        <input type="text" name="naziv" required>

        <label>Opis</label>
        <textarea name="opis" rows="4" required></textarea>

        <label>Cena (€)</label>
        <input type="number" step="0.01" name="cena" required>

        <label>Trajanje (min)</label>
        <input type="number" name="trajanje" required>

        <label>Slika (opcijsko)</label>
        <input type="file" name="slika">

        <button type="submit">Dodaj storitev</button>

        <a href="storitve.php" 
           class="btn-nav" 
           style="display:block;text-align:center;margin-top:10px;background:#8a6d5a;color:white;"
           onclick="return potrdiPreklic();">
            Prekliči / Vrni se nazaj
        </a>

    </form>
  </div>
</div>

</body>
</html>
