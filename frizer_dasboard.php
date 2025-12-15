<?php
include 'db_config.php';
session_start();

// samo frizer lahko gleda
if (!isset($_SESSION['user']) || $_SESSION['user']['tip'] != 1) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Frizer – Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <div class="nav-container">
        <h1>Frizer Dashboard</h1>
        <nav>
            <a href="index.php" class="btn-nav">Domov</a>
            <a href="logout.php" class="btn-nav">Odjava</a>
        </nav>
    </div>
</header>

<section class="services">
    <h2>Urejanje storitev</h2>

    <a href="dodaj_storitev.php" class="btn-primary">Dodaj novo storitev</a>

    <div class="service-grid">
        <?php
        $result = $conn->query("SELECT * FROM Storitev");
        while ($row = $result->fetch_assoc()):
        ?>
            <div class="service-card">
                <img src="images/<?= $row['slika'] ?>" alt="">
                <h3><?= $row['naziv'] ?></h3>
                <p><?= $row['opis'] ?></p>
                <p><strong><?= $row['cena'] ?> €</strong></p>

                <a class="btn-nav" href="brisi_storitev.php?id=<?= $row['storitev_id'] ?>"
                   onclick="return confirm('Res želiš izbrisati?')">Izbriši</a>
            </div>
        <?php endwhile; ?>
    </div>
</section>


<section class="services">
    <h2>Urejanje izdelkov</h2>

    <a href="dodaj_izdelek.php" class="btn-primary">Dodaj nov izdelek</a>

    <div class="service-grid">
        <?php
        $result = $conn->query("SELECT * FROM Izdelek");
        while ($row = $result->fetch_assoc()):
        ?>
            <div class="service-card">
                <img src="images/<?= $row['slika'] ?>" alt="">
                <h3><?= $row['naziv'] ?></h3>
                <p><?= $row['opis'] ?></p>
                <p><strong><?= $row['cena'] ?> €</strong></p>

                <a class="btn-nav" href="brisi_izdelek.php?id=<?= $row['izdelek_id'] ?>"
                   onclick="return confirm('Res želiš izbrisati?')">Izbriši</a>
            </div>
        <?php endwhile; ?>
    </div>
</section>

</body>
</html>
