<?php
include 'db_config.php';
session_start();

$user = $_SESSION['user'] ?? null;

// povlečemo vse izdelke
$search = trim($_GET['search'] ?? "");

// Ako postoji pretraga
if ($search !== "") {
    $stmt = $conn->prepare("
        SELECT i.izdelek_id, i.naziv, i.opis, i.cena, i.zaloga, i.slika,
               t.naziv AS tip
        FROM Izdelek i
        LEFT JOIN TipIzdelka t ON i.tip_id = t.tip_id
        WHERE i.naziv LIKE ?
        ORDER BY i.izdelek_id DESC
    ");
    $like = "%" . $search . "%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Ako nema pretrage – svi proizvodi
    $result = $conn->query("
        SELECT i.izdelek_id, i.naziv, i.opis, i.cena, i.zaloga, i.slika,
               t.naziv AS tip
        FROM Izdelek i
        LEFT JOIN TipIzdelka t ON i.tip_id = t.tip_id
        ORDER BY i.izdelek_id DESC
    ");
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Vsi izdelki</title>
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
    <h2>Vsi izdelki</h2>

    <form method="GET" action="izdelki.php" class="search-bar">
    <input 
        type="text" 
        name="search" 
        placeholder="Išči izdelek po imenu..."
        value="<?= htmlspecialchars($search ?? '') ?>"
        class="search-input"
    >

    <button type="submit" class="search-btn">
        Išči
    </button>

    <?php if (!empty($search)): ?>
        <a href="izdelki.php" class="search-clear">
            Počisti
        </a>
    <?php endif; ?>
</form>



    <div class="service-grid">
        <?php if ($result->num_rows === 0): ?>
           <p style="text-align:center; color:#777; margin-top:20px;">
              Ni izdelkov, ki ustrezajo iskanju.
           </p>
        <?php endif; ?>

        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="service-card">

                <!-- ✔️ PRAVA SLIKA IZ BAZE -->
                <img src="images/<?= htmlspecialchars($row['slika']) ?>" 
         alt="izdelek" 
         loading="lazy">

                <h3>
    <a href="izdelek_podrobnosti.php?id=<?= $row['izdelek_id'] ?>" 
       style="color:#3c2a21; text-decoration:none;">
        <?= htmlspecialchars($row['naziv']) ?>
    </a>
</h3>
                <p><strong>Cena:</strong> <?= number_format($row['cena'],2) ?> €</p>
                <p><strong>Zaloga:</strong> <?= $row['zaloga'] ?></p>
                <p><strong>Tip:</strong> <?= $row['tip'] ?></p>

                <!-- Frizer lahko ureja in briše -->
                <?php if ($user && $user['tip'] == 1): ?>
                    <div class="card-actions">
                        <a href="uredi_izdelek.php?id=<?= $row['izdelek_id'] ?>" class="btn-action btn-edit">
                            Uredi
                        </a>

                        <a href="brisi_izdelek.php?id=<?= $row['izdelek_id'] ?>" class="btn-action btn-delete"
                            onclick="return confirm('Ali ste prepričani, da želite izbrisati ta izdelek?');">
                             Izbriši
                        </a>
                    </div>
                <?php endif; ?>


            </div>
        <?php endwhile; ?>
    </div>
</section>

</body>
</html>
