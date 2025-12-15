<?php
include 'db_config.php';
session_start();

$user = $_SESSION['user'] ?? null;

// Proveri ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: izdelki.php");
    exit;
}

$izdelek_id = (int)$_GET['id'];

// Učitaj sve podatke o proizvodu
$stmt = $conn->prepare("
    SELECT i.izdelek_id, i.naziv, i.opis, i.cena, i.zaloga, i.slika,
           t.naziv AS tip_naziv, t.opis AS tip_opis,
           d.naziv AS dobavitelj_naziv, d.telefon AS dobavitelj_telefon, d.email AS dobavitelj_email,
           d.website AS dobavitelj_website
    FROM Izdelek i
    LEFT JOIN TipIzdelka t ON i.tip_id = t.tip_id
    LEFT JOIN Dobavitelj d ON i.dobavitelj_id = d.dobavitelj_id
    WHERE i.izdelek_id = ?
");
$stmt->bind_param("i", $izdelek_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: izdelki.php");
    exit;
}

$izdelek = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($izdelek['naziv']) ?> – Bella Donna</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .product-detail {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .product-detail img {
            max-width: 100%;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .product-detail h2 {
            color: #3c2a21;
            margin-bottom: 15px;
        }
        .product-detail .info-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .product-detail .info-row:last-child {
            border-bottom: none;
        }
        .product-detail .label {
            font-weight: bold;
            color: #3c2a21;
        }
        .product-detail .value {
            color: #666;
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

<section class="product-detail">
    <?php if ($izdelek['slika']): ?>
       <img src="images/<?= htmlspecialchars($izdelek['slika']) ?>" 
         alt="<?= htmlspecialchars($izdelek['naziv']) ?>"
         loading="lazy">
    <?php endif; ?>

    <h2><?= htmlspecialchars($izdelek['naziv']) ?></h2>

    <div class="info-row">
        <span class="label">Opis:</span>
        <span class="value"><?= nl2br(htmlspecialchars($izdelek['opis'])) ?></span>
    </div>
    <?php if (!empty($izdelek['dobavitelj_website'])): ?>
  <div style="margin-top:15px; padding: 12px 0;">
    <div style="font-weight:bold; color:#3c2a21; margin-bottom:8px;">
      Obiščite spletno stran dobavitelja
    </div>

    <img
      src="qr_dobavitelj.php?url=<?= urlencode($izdelek['dobavitelj_website']) ?>"
      alt="QR dobavitelja"
      width="150"
      height="150"
      style="border:1px solid #eee; padding:8px; border-radius:8px; background:white;"
    >

    <div style="font-size:12px; color:#666; margin-top:6px;">
      Skeniraj QR kodo ali klikni:
      <a href="<?= htmlspecialchars($izdelek['dobavitelj_website']) ?>" target="_blank" rel="noopener">
        <?= htmlspecialchars($izdelek['dobavitelj_website']) ?>
      </a>
    </div>
  </div>
<?php endif; ?>


    <div class="info-row">
        <span class="label">Cena:</span>
        <span class="value"><?= number_format($izdelek['cena'], 2) ?> €</span>
    </div>

    <div class="info-row">
        <span class="label">Zaloga:</span>
        <span class="value"><?= $izdelek['zaloga'] ?> kom</span>
    </div>

    <?php if ($izdelek['tip_naziv']): ?>
        <div class="info-row">
            <span class="label">Tip izdelka:</span>
            <span class="value"><?= htmlspecialchars($izdelek['tip_naziv']) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($izdelek['tip_opis']): ?>
        <div class="info-row">
            <span class="label">Opis tipa:</span>
            <span class="value"><?= htmlspecialchars($izdelek['tip_opis']) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($izdelek['dobavitelj_naziv']): ?>
        <div class="info-row">
            <span class="label">Dobavitelj:</span>
            <span class="value"><?= htmlspecialchars($izdelek['dobavitelj_naziv']) ?></span>
        </div>

        <?php if ($izdelek['dobavitelj_telefon']): ?>
            <div class="info-row">
                <span class="label">Telefon dobavitelja:</span>
                <span class="value"><?= htmlspecialchars($izdelek['dobavitelj_telefon']) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($izdelek['dobavitelj_email']): ?>
            <div class="info-row">
                <span class="label">Email dobavitelja:</span>
                <span class="value"><?= htmlspecialchars($izdelek['dobavitelj_email']) ?></span>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div style="margin-top:30px;">
        <?php if ($user && $user['tip'] == 1): ?>
            <a href="uredi_izdelek.php?id=<?= $izdelek['izdelek_id'] ?>" class="btn-primary">Uredi izdelek</a>
        <?php endif; ?>
        <a href="izdelki.php" class="btn-nav">← Nazaj na vse izdelke</a>
    </div>
</section>

</body>
</html>