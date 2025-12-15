<?php
include 'db_config.php';
session_start();

$user = $_SESSION['user'] ?? null;

// Proveri ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: storitve.php");
    exit;
}

$storitev_id = (int)$_GET['id'];

// Učitaj podatke o storitvi
$stmt = $conn->prepare("
    SELECT storitev_id, naziv, opis, cena, trajanje_min, slika
    FROM Storitev
    WHERE storitev_id = ?
");
$stmt->bind_param("i", $storitev_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: storitve.php");
    exit;
}

$storitev = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($storitev['naziv']) ?> – Bella Donna</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .service-detail {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .service-detail img {
            max-width: 100%;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .service-detail h2 {
            color: #3c2a21;
            margin-bottom: 15px;
        }
        .service-detail .info-row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin: 10px 0;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .service-detail .info-row:last-child {
            border-bottom: none;
        }
        .service-detail .label {
            font-weight: bold;
            color: #3c2a21;
            min-width: 140px;
        }
        .service-detail .value {
            color: #666;
            text-align: right;
            flex: 1;
        }
        .service-detail .value.long {
            text-align: left;
        }
    </style>
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
            <a href="kontakt.php">Kontakt</a>

            <?php if ($user): ?>
                <span class="nav-user">Pozdrav, <?= htmlspecialchars($user['ime']) ?>!</span>
                <a href="logout.php" class="btn-nav">Odjavi se</a>
            <?php else: ?>
                <a href="login.php" class="btn-nav">Prijava</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<section class="service-detail">

    <?php if (!empty($storitev['slika'])): ?>
        <img src="images/<?= htmlspecialchars($storitev['slika']) ?>"
             alt="<?= htmlspecialchars($storitev['naziv']) ?>"
             loading="lazy">
    <?php endif; ?>

    <h2><?= htmlspecialchars($storitev['naziv']) ?></h2>

    <div class="info-row">
        <span class="label">Opis:</span>
        <span class="value long"><?= nl2br(htmlspecialchars($storitev['opis'])) ?></span>
    </div>

    <div class="info-row">
        <span class="label">Cena:</span>
        <span class="value"><?= number_format((float)$storitev['cena'], 2) ?> €</span>
    </div>

    <div class="info-row">
        <span class="label">Trajanje:</span>
        <span class="value"><?= (int)$storitev['trajanje_min'] ?> min</span>
    </div>

    <div style="margin-top:30px;">
        <?php if ($user && $user['tip'] == 1): ?>
            <a href="uredi_storitev.php?id=<?= (int)$storitev['storitev_id'] ?>" class="btn-primary">
                Uredi storitev
            </a>
        <?php endif; ?>
        <a href="storitve.php" class="btn-nav">← Nazaj na vse storitve</a>
    </div>

</section>

</body>
</html>
