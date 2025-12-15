<?php
include 'db_config.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['tip'] != 2) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Stranka – Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <div class="nav-container">
        <h1>Stranka Dashboard</h1>
        <nav>
            <a href="index.php" class="btn-nav">Domov</a>
            <a href="logout.php" class="btn-nav">Odjava</a>
        </nav>
    </div>
</header>

<section class="services">
    <h2>Storitve</h2>
    <div class="service-grid">
        <?php
        $result = $conn->query("SELECT * FROM Storitev");
        while ($row = $result->fetch_assoc()):
        ?>
            <div class="service-card">
                <img src="images/<?= $row['slika'] ?>">
                <h3><?= $row['naziv'] ?></h3>
                <p><?= $row['opis'] ?></p>
                <p><strong><?= $row['cena'] ?> €</strong></p>
            </div>
        <?php endwhile; ?>
    </div>
</section>


<section class="services">
    <h2>Izdelki</h2>
    <div class="service-grid">
        <?php
        $result = $conn->query("SELECT * FROM Izdelek");
        while ($row = $result->fetch_assoc()):
        ?>
            <div class="service-card">
                <img src="images/<?= $row['slika'] ?>">
                <h3><?= $row['naziv'] ?></h3>
                <p><?= $row['opis'] ?></p>
                <p><strong><?= $row['cena'] ?> €</strong></p>
            </div>
        <?php endwhile; ?>
    </div>
</section>

</body>
</html>
