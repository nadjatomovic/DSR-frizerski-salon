<?php
include 'db_config.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['tip'] != 1) {
    die("Dostop zavrnjen.");
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM Storitev WHERE storitev_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$storitev = $stmt->get_result()->fetch_assoc();

if (!$storitev) {
    die("Storitev ne obstaja.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $naziv = $_POST['naziv'];
    $cena = $_POST['cena'];
    $trajanje = $_POST['trajanje_min'];

    $stmt2 = $conn->prepare("
        UPDATE Storitev 
        SET naziv=?, cena=?, trajanje_min=?
        WHERE storitev_id=?
    ");
    $stmt2->bind_param("sdii", $naziv, $cena, $trajanje, $id);

    if ($stmt2->execute()) {
        header("Location: storitve.php");
        exit;
    } else {
        $message = "Napaka: " . $stmt2->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Uredi storitev</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="form-container">
    <h2>Uredi storitev</h2>

    <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>

    <form method="POST">

        <label>Naziv</label>
        <input type="text" name="naziv" value="<?= htmlspecialchars($storitev['naziv']) ?>" required>

        <label>Cena (€)</label>
        <input type="number" name="cena" step="0.01" value="<?= $storitev['cena'] ?>" required>

        <label>Trajanje (min)</label>
        <input type="number" name="trajanje_min" value="<?= $storitev['trajanje_min'] ?>" required>

        <button type="submit">Shrani spremembe</button>
    </form>
</div>

</body>
</html>
