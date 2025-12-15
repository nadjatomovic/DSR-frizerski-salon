<?php
include 'db_config.php';
session_start();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $ime = trim($_POST['ime']);
    $priimek = trim($_POST['priimek']);
    $email = trim($_POST['email']);
    $geslo_raw = $_POST['geslo'];

    // BASIC VALIDACIJA
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Neveljaven email.";
    } 
    elseif (strlen($geslo_raw) < 6) {
        $message = "Geslo mora imeti vsaj 6 znakov.";
    } 
    else {
        // PREVERI ALI EMAIL ŽE OBSTAJA
        $check = $conn->prepare("SELECT uporabnik_id FROM Uporabnik WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "Ta email je že registriran.";
        } else {
            // HASHIRANJE GESLA ZA NOVE KORISNIKE
            $geslo = password_hash($geslo_raw, PASSWORD_DEFAULT);

            // VSTAVI NOVEGA UPORABNIKA
            $stmt = $conn->prepare("
                INSERT INTO Uporabnik (ime, priimek, telefon, email, geslo, tip_id)
                VALUES (?, ?, '', ?, ?, 2)
            ");
            $stmt->bind_param("ssss", $ime, $priimek, $email, $geslo);

             if ($stmt->execute()) {
                
                // EMAIL SLANJE
                include 'email_sender.php';
                
                $htmlEmail = "
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset='UTF-8'>
                </head>
                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <div style='max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9;'>
                        <div style='background-color: white; padding: 30px; border-radius: 10px;'>
                            <h2 style='color: #8b4789; margin-top: 0;'>Dobrodošli, {$ime}!</h2>
                            <p>Hvala, ker ste se registrirali na <strong>Bella Donna</strong>.</p>
                            <p>Vaš račun je uspešno kreiran z naslovom: <strong>{$email}</strong></p>
                            <div style='margin: 30px 0; padding: 20px; background-color: #f4f4f4; border-left: 4px solid #8b4789;'>
                                <p style='margin: 0;'><strong>Naslednji koraki:</strong></p>
                                <ul style='margin: 10px 0;'>
                                    <li>Prijavite se na svoj račun</li>
                                    <li>Oglejte si našo ponudbo</li>
                                    <li>Rezervirajte termin</li>
                                </ul>
                            </div>
                            <p style='color: #666; font-size: 14px; margin-bottom: 0;'>
                                Lep pozdrav,<br>
                                <strong>Ekipa Bella Donna</strong>
                            </p>
                        </div>
                    </div>
                </body>
                </html>
                ";
                
                $emailSent = posaljiEmail($email, $ime, "Dobrodošli na Bella Donna!", $htmlEmail);
                
                if ($emailSent) {
                    $message = "Registracija uspešna! Potrditveni email je bil poslan na {$email}.";
                } else {
                    $message = "Registracija uspešna! Zdaj se lahko prijavite.";
                }
                
            } else {
                $message = "Napaka pri registraciji: " . $stmt->error;
            }

            $stmt->close();
        }

        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Registracija – Bella Donna</title>
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
            <a href="login.php" class="btn-nav">Prijava</a>
        </nav>
    </div>
</header>

<section class="form-section">
    <div class="form-container">
        <h2>Ustvari račun</h2>

        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="post">
            <label>Ime</label>
            <input type="text" name="ime" required>

            <label>Priimek</label>
            <input type="text" name="priimek" required>

            <label>Email</label>
            <input type="email" name="email" required>

            <label>Geslo (min 6 znakov)</label>
            <input type="password" name="geslo" required>

            <button type="submit" class="btn-primary">Registriraj se</button>
        </form>

        <p class="alt-link">
            Že imaš račun? <a href="login.php">Prijavi se</a>
        </p>
    </div>
</section>

</body>
</html>