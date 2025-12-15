<?php
include 'db_config.php';
session_start();

$user = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frizerski salon Bella Donna</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <div class="top-banner">
        <img src="images/monaLiza2.jpg" alt="Bella Donna Logo" class="banner-logo">
        <div class="banner-text">
            <h1>Bella Donna</h1>
            <p class="slogan">Ko še Mona Liza rabi malo volumna.</p>
        </div>
    </div>
    <div class="nav-container">
        <nav>
            <a href="index.php">Domov</a>
            <a href="storitve.php">Poglej storitve</a>
            <a href="izdelki.php">Poglej izdelke</a>
            <a href="kontakt.php">Kontakt</a>

            <?php if ($user && $user['tip'] == 2): ?>
                <a href="stranka_rezervacije.php">Moje rezervacije</a>
                <a href="profil_stranka.php">Moj profil</a>

            <?php elseif ($user && $user['tip'] == 1): ?>
                <a href="profil_frizer.php">Moj profil</a>
                <a href="frizer_rezervacije.php">Rezervacije</a>
                <a href="frizer_kalendar.php">Koledar</a>
                <a href="dodaj_storitev.php">Dodaj storitev</a>
                <a href="dodaj_izdelek.php">Dodaj izdelek</a>
            <?php endif; ?>

            <?php if ($user): ?>
                <span class="nav-user">Pozdrav, <?= htmlspecialchars($user['ime']) ?>!</span>
                <a href="logout.php" class="btn-nav">Odjavi se</a>
            <?php else: ?>
                <a href="login.php" class="btn-nav">Prijava</a>
                <a href="register.php" class="btn-nav">Registracija</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<section class="hero">
    <div class="hero-text">
        <h2>Lepota. Stil. Zaupanje.</h2>
        <p>Dobrodošli v frizerskem salonu <strong>Bella Donna</strong>, kjer ustvarjamo popolne frizure in lepe trenutke.</p>

        <?php if (!$user): ?>
            <a href="login.php" class="btn-primary">Rezerviraj termin</a>
        <?php elseif ($user['tip'] == 2): ?>
            <a href="stranka_rezervacije.php" class="btn-primary">Rezerviraj termin</a>
        <?php elseif ($user['tip'] == 1): ?>
            <a href="frizer_rezervacije.php" class="btn-primary">Preglej termine</a>
        <?php endif; ?>
    </div>

    <div class="hero-image">
        <img src="images/salon1.jpg" alt="Frizerski salon">
    </div>
</section>


<section class="services">
    <h2>Naše storitve</h2>
    <div class="service-grid">

        <div class="service-card">
            <img src="images/strizenje.jpg" alt="Striženje">
            <h3>Striženje</h3>
            <p>Klasično, moderno ali kreativno – izberite slog, ki odraža vašo osebnost.</p>
        </div>

        <div class="service-card">
            <img src="images/barvanje.jpg" alt="Barvanje las">
            <h3>Barvanje</h3>
            <p>Osvetlite svoj videz z vrhunskimi barvami in strokovno nego las.</p>
        </div>

        <div class="service-card">
            <img src="images/nega.jpg" alt="Nega las">
            <h3>Nega las</h3>
            <p>Globinska nega in tretmaji, ki povrnejo sijaj in zdravje vašim lasem.</p>
        </div>

    </div>
</section>


<section class="services">
    <h2>Lahko pri nas najdete izdelke za VSE kaj rabite!</h2>
    <div class="service-grid">

        <div class="service-card">
            <img src="images/izdelek1.jpg" alt="Izdelek 1">
            <h3>Nega las</h3>
            <p>Kakovostna profesionalna nega za lase.</p>
        </div>

        <div class="service-card">
            <img src="images/izdelek2.jpg" alt="Izdelek 2">
            <h3>Sjaj las</h3>
            <p>Izdelki za sijaj in obnovo strukture las.</p>
        </div>

        <div class="service-card">
            <img src="images/izdelek3.jpg" alt="Izdelek 3">
            <h3>Pranje las</h3>
            <p>Profesionalna nega za popoln videz.</p>
        </div>

    </div>
</section>

<footer>
    <div class="footer-container">
        <p>© 2025 Bella Donna | Ustvarjeno z ljubeznijo v Mariboru 💇‍♀️</p>
        <div class="socials">
            <a href="#">Instagram</a>
            <a href="#">Facebook</a>
            <a href="#">Kontakt</a>
        </div>
    </div>
</footer>

</body>
</html>
