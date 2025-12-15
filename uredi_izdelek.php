<?php
include 'db_config.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['tip'] != 1) {
    die("Dostop zavrnjen.");
}

$id = intval($_GET['id']);

// povlečemo trenutne podatke
$stmt = $conn->prepare("SELECT * FROM Izdelek WHERE izdelek_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$izdelek = $stmt->get_result()->fetch_assoc();

if (!$izdelek) {
    die("Izdelek ne obstaja.");
}

// ako je posodobi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $naziv = $_POST['naziv'];
    $cena = $_POST['cena'];
    $zaloga = $_POST['zaloga'];
    $tip_id = $_POST['tip_id'];
    $dobavitelj_id = $_POST['dobavitelj_id'];

    $stmt2 = $conn->prepare("
        UPDATE Izdelek 
        SET naziv=?, cena=?, zaloga=?, tip_id=?, dobavitelj_id=?
        WHERE izdelek_id=?
    ");
    $stmt2->bind_param("sdiiii", $naziv, $cena, $zaloga, $tip_id, $dobavitelj_id, $id);

    if ($stmt2->execute()) {
        header("Location: izdelki.php");
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
    <title>Uredi izdelek</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="form-container">
    <h2>Uredi izdelek</h2>

    <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>

    <form method="POST">

        <label>Naziv</label>
        <input type="text" name="naziv" value="<?= htmlspecialchars($izdelek['naziv']) ?>" required>

        <label>Cena (€)</label>
        <input type="number" name="cena" step="0.01" value="<?= $izdelek['cena'] ?>" required>

        <label>Zaloga</label>
        <input type="number" name="zaloga" value="<?= $izdelek['zaloga'] ?>" required>

        <label>Tip ID</label>
        <input type="number" name="tip_id" value="<?= $izdelek['tip_id'] ?>" required>

        <label>Dobavitelj ID</label>
        <input type="number" name="dobavitelj_id" value="<?= $izdelek['dobavitelj_id'] ?>" required>

        <button type="submit">Shrani spremembe</button>
    </form>
</div>

</body>
</html>
