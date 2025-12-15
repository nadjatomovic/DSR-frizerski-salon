<?php
include 'db_config.php';
session_start();

$user = $_SESSION['user'] ?? null;

$result = $conn->query("SELECT * FROM Storitev ORDER BY storitev_id DESC");
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Vse storitve</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <div class="nav-container">
        <div class="logo-box">
            <img src="images/monaLiza2.jpg" class="logo-small" alt="logo">
            <h1>Bella Donna</h1>
        </div>

        <nav>
            <a href="index.php">Domov</a>
            <a href="storitve.php">Storitve</a>
            <a href="izdelki.php">Izdelki</a>
            <a href="kontakt.php" class="active">Kontakt</a>

            <?php if ($user): ?>
                <span class="nav-user">Pozdrav, <?= htmlspecialchars($user['ime']) ?>!</span>
                <a href="logout.php" class="btn-nav">Odjavi se</a>
            <?php else: ?>
                <a href="login.php" class="btn-nav">Prijava</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<section class="services">
    <h2>Vse storitve</h2>

    <div class="service-grid">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="service-card">

                <img src="images/<?= htmlspecialchars($row['slika']) ?>" alt="storitev">

                <h3>
                    <a class="name-link" 
                       href="storitev_podrobnosti.php?id=<?= $row['storitev_id'] ?>">
                       <?= htmlspecialchars($row['naziv']) ?>
                    </a>
                </h3>

                <p>Cena: <?= number_format($row['cena'],2) ?> €</p>
                <p>Trajanje: <?= $row['trajanje_min'] ?> min</p>

                <?php if ($user && $user['tip'] == 1): ?>
                    <div class="card-actions">
                        <a href="uredi_storitev.php?id=<?= $row['storitev_id'] ?>" class="btn-action btn-edit">
                            <span class="icon">✏️</span> Uredi
                        </a>

                        <a href="brisi_storitev.php?id=<?= $row['storitev_id'] ?>" class="btn-action btn-delete"
                            onclick="return confirm('Ali ste prepričani, da želite izbrisati to storitev?');">
                            <span class="icon">🗑️</span> Izbriši
                        </a>
                    </div>
                <?php endif; ?>

            </div>
        <?php endwhile; ?>
    </div>
</section>

</body>
</html>
