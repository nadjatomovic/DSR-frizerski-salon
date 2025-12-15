<?php
include 'db_config.php';
session_start();

$user = $_SESSION['user'] ?? null;

// Provera: mora biti prijavljen i biti stranka (tip_id = 2)
if (!$user || $user['tip'] != 2) {
    header("Location: login.php");
    exit;
}

$message = '';
$stranka_id = $user['id'];

// Učitaj trenutne podatke stranke
$stmt = $conn->prepare("
    SELECT ime, priimek, telefon, email, slika 
    FROM Uporabnik 
    WHERE uporabnik_id = ?
");
$stmt->bind_param("i", $stranka_id);
$stmt->execute();
$result = $stmt->get_result();
$stranka = $result->fetch_assoc();
$stmt->close();

// Ako je POST, ažuriraj podatke
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ime = trim($_POST['ime']);
    $priimek = trim($_POST['priimek']);
    $telefon = trim($_POST['telefon']);
    $email = trim($_POST['email']);
    $staro_geslo = $_POST['staro_geslo'] ?? '';
    $novo_geslo = $_POST['novo_geslo'] ?? '';

    // Upload slike
    $slika = $stranka['slika'];
    if (isset($_FILES['slika']) && $_FILES['slika']['error'] == 0) {
        $upload_dir = 'images/';
        $file_name = time() . '_' . basename($_FILES['slika']['name']);
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['slika']['tmp_name'], $target_file)) {
            $slika = $file_name;
        }
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Neveljaven email.";
    } else {
        if (!empty($novo_geslo)) {
            $check = $conn->prepare("SELECT geslo FROM Uporabnik WHERE uporabnik_id = ?");
            $check->bind_param("i", $stranka_id);
            $check->execute();
            $check->bind_result($hash);
            $check->fetch();
            $check->close();

            if (!password_verify($staro_geslo, $hash)) {
                $message = "Staro geslo je napačno.";
            } elseif (strlen($novo_geslo) < 6) {
                $message = "Novo geslo mora imeti vsaj 6 znakov.";
            } else {
                $novo_hash = password_hash($novo_geslo, PASSWORD_DEFAULT);
                $update = $conn->prepare("
                    UPDATE Uporabnik 
                    SET ime=?, priimek=?, telefon=?, email=?, geslo=?, slika=? 
                    WHERE uporabnik_id=?
                ");
                $update->bind_param("ssssssi", $ime, $priimek, $telefon, $email, $novo_hash, $slika, $stranka_id);
                
                if ($update->execute()) {
                    $message = "Profil uspešno posodobljen!";
                    $_SESSION['user']['ime'] = $ime;
                    $_SESSION['user']['priimek'] = $priimek;
                    $stranka['slika'] = $slika;
                } else {
                    $message = "Napaka: " . $update->error;
                }
                $update->close();
            }
        } else {
            $update = $conn->prepare("
                UPDATE Uporabnik 
                SET ime=?, priimek=?, telefon=?, email=?, slika=? 
                WHERE uporabnik_id=?
            ");
            $update->bind_param("sssssi", $ime, $priimek, $telefon, $email, $slika, $stranka_id);
            
            if ($update->execute()) {
                $message = "Profil uspešno posodobljen!";
                $_SESSION['user']['ime'] = $ime;
                $_SESSION['user']['priimek'] = $priimek;
                $stranka['slika'] = $slika;
            } else {
                $message = "Napaka: " . $update->error;
            }
            $update->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Moj profil – Bella Donna</title>
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
            <a href="kontakt.php">Kontakt</a>
            <span class="nav-user">Pozdrav, <?= htmlspecialchars($user['ime']) ?>!</span>
            <a href="logout.php" class="btn-nav">Odjavi se</a>
        </nav>
    </div>
</header>

<section class="form-section">
    <div class="form-container">
        <h2>Moj profil</h2>

        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <?php if ($stranka['slika']): ?>
            <div style="text-align:center; margin-bottom:20px;">
                <img src="images/<?= htmlspecialchars($stranka['slika']) ?>" 
                     alt="Profilna slika" 
                     style="max-width:150px; border-radius:50%;">
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <label>Ime</label>
            <input type="text" name="ime" value="<?= htmlspecialchars($stranka['ime']) ?>" required>

            <label>Priimek</label>
            <input type="text" name="priimek" value="<?= htmlspecialchars($stranka['priimek']) ?>" required>

            <label>Telefon</label>
            <input type="text" name="telefon" value="<?= htmlspecialchars($stranka['telefon']) ?>" required>

            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($stranka['email']) ?>" required>

            <label>Profilna slika</label>
            <input type="file" name="slika" accept="image/*">

            <hr style="margin:20px 0;">

            <h3>Spremeni geslo (opcijsko)</h3>

            <label>Staro geslo</label>
            <input type="password" name="staro_geslo" placeholder="Pustite prazno, če ne želite spremeniti">

            <label>Novo geslo (min 6 znakov)</label>
            <input type="password" name="novo_geslo" placeholder="Pustite prazno, če ne želite spremeniti">

            <button type="submit" class="btn-primary">Shrani spremembe</button>
        </form>

        <p class="alt-link">
            <a href="index.php">← Nazaj na domov</a>
        </p>
    </div>
</section>

</body>
</html>