<?php
include 'db_config.php';
session_start();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $geslo = $_POST['geslo'];

    $stmt = $conn->prepare("SELECT uporabnik_id, ime, priimek, geslo, tip_id 
                            FROM Uporabnik 
                            WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $ime, $priimek, $hash, $tip);
        $stmt->fetch();
        $stmt->close();

        $login_ok = false;

        // ✅ PROVERI DA LI JE HASH ILI PLAIN TEXT
        if (password_get_info($hash)['algo'] !== null) {
            // JE HASH → koristi password_verify
            if (password_verify($geslo, $hash)) {
                $login_ok = true;
            }
        } else {
            // JE PLAIN TEXT → direktna provera
            if ($geslo === $hash) {
                $login_ok = true;

                // ✅ AUTOMATSKI HASHIRAJ I AŽURIRAJ U BAZI
                $novi_hash = password_hash($geslo, PASSWORD_DEFAULT);
                $update = $conn->prepare("UPDATE Uporabnik SET geslo = ? WHERE uporabnik_id = ?");
                $update->bind_param("si", $novi_hash, $id);
                $update->execute();
                $update->close();
            }
        }

        if ($login_ok) {
            $_SESSION['user'] = [
                'id' => $id,
                'ime' => $ime,
                'priimek' => $priimek,
                'tip' => $tip
            ];

            echo "
            <script>
                localStorage.setItem('user_logged', '1');
                localStorage.setItem('user_id', '$id');
                localStorage.setItem('user_name', '$ime');
                localStorage.setItem('user_type', '$tip');
                window.location.href = 'index.php';
            </script>";
            exit;
        } else {
            $message = "Napačno geslo.";
        }

    } else {
        $message = "Uporabnik s tem emailom ne obstaja.";
    }
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Prijava – Bella Donna</title>
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
            <a href="register.php" class="btn-nav">Registracija</a>
        </nav>
    </div>
</header>

<section class="form-section">
    <div class="form-container">
        <h2>Prijava</h2>

        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="post">
            <label>Email</label>
            <input type="email" name="email" required>

            <label>Geslo</label>
            <input type="password" name="geslo" required>

            <button type="submit" class="btn-primary">Prijavi se</button>
        </form>
        <div style="margin-top: 15px;">
           <a href="oidc_login.php" class="btn-google">
              <!-- Google G icon (SVG) -->
             <svg viewBox="0 0 48 48" aria-hidden="true">
              <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303C33.659 32.659 29.234 36 24 36c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.954 4 4 12.954 4 24s8.954 20 20 20 20-8.954 20-20c0-1.341-.138-2.651-.389-3.917z"/>
              <path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 16.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z"/>
              <path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.197l-6.19-5.238C29.211 35.091 26.715 36 24 36c-5.213 0-9.623-3.319-11.291-7.946l-6.521 5.025C9.505 39.556 16.227 44 24 44z"/>
              <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-0.793 2.244-2.231 4.141-4.084 5.565l.003-.002 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.651-.389-3.917z"/>
             </svg>
                Prijava z Google računom
            </a>
        </div>


        <p class="alt-link">Še nimaš računa? <a href="register.php">Registriraj se</a></p>

    </div>
</section>

<script>
document.addEventListener("DOMContentLoaded", () => {
    if (localStorage.getItem("user_logged") === "1") {
        const registerBtn = document.querySelector("a[href='register.php']");
        if (registerBtn) registerBtn.style.display = "none";
    }
});
</script>

</body>
</html>