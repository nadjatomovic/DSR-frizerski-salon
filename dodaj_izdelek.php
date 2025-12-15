<?php
include 'db_config.php';
session_start();

// --- Pridobimo tipove izdelkov ---
$tipi = $conn->query("SELECT tip_id, naziv FROM TipIzdelka ORDER BY naziv ASC");

// --- Pridobimo dobavitelje ---
$dobavitelji = $conn->query("SELECT dobavitelj_id, naziv FROM Dobavitelj ORDER BY naziv ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $naziv = $_POST['naziv'];
    $opis = $_POST['opis'];
    $cena = $_POST['cena'];
    $zaloga = $_POST['zaloga'];
    $tip_id = $_POST['tip_id'];
    $dobavitelj_id = $_POST['dobavitelj_id'];

    // upload slike
    $slika = $_FILES['slika']['name'];
    $target = "images/" . basename($slika);

    if (!empty($slika)) {
        move_uploaded_file($_FILES['slika']['tmp_name'], $target);
    }

    $stmt = $conn->prepare("
        INSERT INTO Izdelek (naziv, opis, cena, zaloga, slika, tip_id, dobavitelj_id)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("ssdisii",
        $naziv,
        $opis,
        $cena,
        $zaloga,
        $slika,
        $tip_id,
        $dobavitelj_id
    );

    if ($stmt->execute()) {
        $message = "Izdelek uspešno dodan!";
    } else {
        $message = "Napaka: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Dodaj izdelek</title>
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
    <h2>Dodaj izdelek</h2>

    <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>

    <form method="POST" enctype="multipart/form-data">

        <label>Naziv</label>
        <input type="text" name="naziv" required>

        <label>Opis</label>
        <textarea name="opis" rows="4" required></textarea>

        <label>Cena (€)</label>
        <input type="number" step="0.01" name="cena" required>

        <label>Zaloga</label>
        <input type="number" name="zaloga" required>

        <label>Tip izdelka</label>
        <select name="tip_id" required>
            <option value="">-- izberi tip --</option>
            <?php while($t = $tipi->fetch_assoc()): ?>
                <option value="<?= $t['tip_id'] ?>"><?= $t['naziv'] ?></option>
            <?php endwhile; ?>
        </select>

        <label>Dobavitelj</label>
        <select name="dobavitelj_id" required>
            <option value="">-- izberi dobavitelja --</option>
            <?php while($d = $dobavitelji->fetch_assoc()): ?>
                <option value="<?= $d['dobavitelj_id'] ?>"><?= $d['naziv'] ?></option>
            <?php endwhile; ?>
        </select>

        <label>Slika (opcijsko)</label>
        <input type="file" name="slika">

        <button type="submit">Dodaj izdelek</button>

        <a href="izdelki.php"
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
