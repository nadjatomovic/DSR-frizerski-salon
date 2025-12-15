<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_config.php';
session_start();

$user = $_SESSION['user'] ?? null;

require_once "email_sender.php";

$kontakt_msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ime = trim($_POST["ime"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $sporocilo = trim($_POST["sporocilo"] ?? "");

    if ($ime === "" || $email === "" || $sporocilo === "") {
        $kontakt_msg = "<p class='message' style='color:red;'>Prosimo, izpolnite vsa polja.</p>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $kontakt_msg = "<p class='message' style='color:red;'>Prosimo, vnesite veljaven email.</p>";
    } else {
        // Escape za sigurnost
        $imeSafe = htmlspecialchars($ime, ENT_QUOTES, 'UTF-8');
        $emailSafe = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
        $sporociloSafe = nl2br(htmlspecialchars($sporocilo, ENT_QUOTES, 'UTF-8'));

        $html = "
            <h2>Novo sporočilo iz kontakt forme</h2>
            <p><strong>Ime in priimek:</strong> {$imeSafe}</p>
            <p><strong>Email:</strong> {$emailSafe}</p>
            <p><strong>Sporočilo:</strong><br>{$sporociloSafe}</p>
        ";

        // Subject može i da uključi ime
        $subject = "Kontakt forma – {$imeSafe}";

        // šalješ na tvoj salon email, reply-to je korisnik
        $ok = posaljiEmail(
            "belladonasaloninfo@gmail.com",
            $subject,
            $html,
            $email // reply-to
        );

        if ($ok) {
            $kontakt_msg = "<p class='message' style='color:green;'>Sporočilo je bilo uspešno poslano!</p>";
        } else {
            $kontakt_msg = "<p class='message' style='color:red;'>Napaka pri pošiljanju. Poskusite znova.</p>";
        }
    }
}


?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Kontakt – Bella Donna</title>
    <link rel="stylesheet" href="style.css">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        .distance-info {
            background: linear-gradient(135deg, #8B4789 0%, #a855a8 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .distance-info h4 {
            margin: 0 0 15px 0;
            font-size: 18px;
        }
        .distance-value {
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0;
        }
        .travel-time {
            font-size: 16px;
            opacity: 0.9;
            margin-top: 5px;
        }
        .location-btn {
            background: #8B4789;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            cursor: pointer;
            margin: 10px 0;
            font-size: 16px;
            transition: background 0.3s;
        }
        .location-btn:hover {
            background: #6d3569;
        }
        .location-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .location-error {
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }
        .location-loading {
            color: #8B4789;
            font-size: 14px;
            margin-top: 10px;
        }
        #map {
            width: 100%;
            height: 400px;
            border-radius: 10px;
            margin-top: 15px;
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


<section class="services" style="padding-top: 2rem;">
    <h2>Kontaktirajte nas</h2>

    <div class="contact-container">

        <!-- LEVA STRAN -->
        <div class="contact-info">
            <h3>Bella Donna – Frizerski salon</h3>

            <p><strong>📍 Naslov:</strong><br>
               Slomškov trg 5, 2000 Maribor</p>

            <p><strong>📞 Telefon:</strong><br>
               +386 40 123 456</p>

            <p><strong>✉ Email:</strong><br>
               belladonasaloninfo@gmail.com</p>

            <p><strong>🕒 Delovni čas:</strong><br>
               Pon–Pet: 08:00–19:00<br>
               Sobota: 09:00–14:00<br>
               Nedelja & prazniki: zaprto</p>
        </div>


        <!-- DESNA STRAN -->
        <div class="contact-form">
            <h3>Pošljite nam sporočilo</h3>

             <?= $kontakt_msg ?>
             <form method="POST" action="">
                <label>Ime in priimek</label>
                <input type="text" name="ime" required value="<?= htmlspecialchars($_POST['ime'] ?? '') ?>">

                <label>Email</label>
                <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

                <label>Sporočilo</label>
                <textarea name="sporocilo" rows="5" required><?= htmlspecialchars($_POST['sporocilo'] ?? '') ?></textarea>

                <button type="submit">Pošlji</button>
            </form>
        </div>
    </div>

    <!-- UDALJENOST -->
    <div class="distance-info" id="distanceInfo" style="display:none;">
        <h4>🎯 Vaša udaljenost od salona</h4>
        <div class="distance-value" id="distanceValue">-</div>
        <div class="travel-time" id="travelTime"></div>
    </div>

    <!-- MAPA -->
    <div class="map-container">
        <h3>Najdete nas tukaj</h3>
        
        <button class="location-btn" id="getLocationBtn">📍 Prikaži me na mapi</button>
        <p class="location-loading" id="locationLoading" style="display:none;">Pridobivanje vaše lokacije...</p>
        <p class="location-error" id="locationError"></p>

        <div id="map"></div>
    </div>

</section>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    const salonLat = 46.5580917;
    const salonLng = 15.6464164;

    let map = L.map('map').setView([salonLat, salonLng], 15);
    let userMarker;

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    const salonIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });

    L.marker([salonLat, salonLng], { icon: salonIcon })
        .addTo(map)
        .bindPopup('<strong>Bella Donna</strong><br>Slomškov trg 5<br>2000 Maribor')
        .openPopup();

    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2)**2 +
                  Math.cos(lat1 * Math.PI/180) *
                  Math.cos(lat2 * Math.PI/180) *
                  Math.sin(dLon/2)**2;
        return R * (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)));
    }

    document.getElementById('getLocationBtn').addEventListener('click', function() {
        const btn = this;
        const loadingMsg = document.getElementById('locationLoading');
        const errorMsg = document.getElementById('locationError');

        if (navigator.geolocation) {
            btn.disabled = true;
            loadingMsg.style.display = 'block';
            errorMsg.textContent = '';

            navigator.geolocation.getCurrentPosition(function(position) {

                const userLat = position.coords.latitude;
                const userLng = position.coords.longitude;

                const distance = calculateDistance(userLat, userLng, salonLat, salonLng);

                document.getElementById('distanceInfo').style.display = 'block';
                
                if (distance < 1) {
                    document.getElementById('distanceValue').textContent = 
                        Math.round(distance * 1000) + ' metara';
                } else {
                    document.getElementById('distanceValue').textContent = 
                        distance.toFixed(2) + ' km';
                }

                let travelText = '';
                if (distance < 1) {
                    travelText = `🚶 Približno ${Math.round((distance / 5) * 60)} min hoda`;
                } else if (distance < 5) {
                    travelText = `🚴 ${Math.round((distance / 15) * 60)} min biciklom | 
                                  🚗 ${Math.round((distance / 40) * 60)} min autom`;
                } else {
                    travelText = `🚗 Približno ${Math.round((distance / 40) * 60)} min vožnje`;
                }
                document.getElementById('travelTime').textContent = travelText;

                const userIcon = L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                });

                if (userMarker) map.removeLayer(userMarker);

                userMarker = L.marker([userLat, userLng], { icon: userIcon })
                    .addTo(map)
                    .bindPopup('📍 Vi ste tukaj')
                    .openPopup();

                // *** NEMA CRTANJA LINIJE ***
                // map.setView centrira korisnika
                map.setView([userLat, userLng], 17);

                loadingMsg.style.display = 'none';
                btn.textContent = '✓ Prikazano na mapi';

            }, function(error) {
                let errorText = '';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorText = '❌ Morate omogućiti dostop do lokacije.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorText = '❌ Lokacija ni na voljo.';
                        break;
                    case error.TIMEOUT:
                        errorText = '❌ Zahteva je potekla.';
                        break;
                    default:
                        errorText = '❌ Napaka pri pridobivanju lokacije.';
                }
                errorMsg.textContent = errorText;
                loadingMsg.style.display = 'none';
                btn.disabled = false;
            });
        } else {
            errorMsg.textContent = '❌ Vaš brskalnik ne podpira geolokacije.';
        }
    });
</script>

</body>
</html>
