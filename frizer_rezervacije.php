<?php
include 'db_config.php';
session_start();

// samo frizer može pristupiti
if (!isset($_SESSION['user']) || $_SESSION['user']['tip'] != 1) {
    header("Location: login.php");
    exit;
}

$frizer_id = $_SESSION['user']['id'];

// ===============================
//   POVLECI REZERVACIJE
// ===============================
$q = $conn->prepare("
    SELECT 
        r.rezervacija_id,
        r.datum_cas,
        r.status,
        r.trajanje_min,
        r.opomba,
        s.naziv AS storitev_naziv,
        u.ime AS stranka_ime,
        u.priimek AS stranka_priimek,
        u.email AS stranka_email,
        u.slika AS stranka_slika,
        (SELECT COUNT(*) FROM Rezervacija rr 
            WHERE rr.uporabnik_stranka_id = u.uporabnik_id 
              AND rr.uporabnik_frizer_id = ?) AS stevilo_obiskov
    FROM Rezervacija r
    JOIN Rezervacija_Storitev rs ON r.rezervacija_id = rs.rezervacija_id
    JOIN Storitev s ON rs.storitev_id = s.storitev_id
    JOIN Uporabnik u ON u.uporabnik_id = r.uporabnik_stranka_id
    WHERE r.uporabnik_frizer_id = ?
    ORDER BY r.datum_cas DESC
");
$q->bind_param("ii", $frizer_id, $frizer_id);
$q->execute();
$rez = $q->get_result();

// ===============================
//   STATISTIKA ZA GRAF
// ===============================
$stat = $conn->prepare("
    SELECT status, COUNT(*) AS cnt
    FROM Rezervacija
    WHERE uporabnik_frizer_id = ?
    GROUP BY status
");
$stat->bind_param("i", $frizer_id);
$stat->execute();
$resStat = $stat->get_result();

$labels = [];
$values = [];

while ($s = $resStat->fetch_assoc()) {
    $labels[] = $s['status'];
    $values[] = (int)$s['cnt'];
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Rezervacije – Frizer</title>
    <link rel="stylesheet" href="style.css">

    <!-- 🎨 Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

<header>
    <div class="nav-container">
        <div class="logo-box">
            <img src="images/monaLiza2.jpg" class="logo-small" alt="">
            <h1>Bella Donna</h1>
        </div>
        <nav>
            <a href="index.php">Domov</a>
            <a href="frizer_rezervacije.php" class="btn-nav">Rezervacije</a>
            <a href="logout.php" class="btn-nav">Odjava</a>
        </nav>
    </div>
</header>

<div class="rez-wrapper">

    <!-- LEVI PANEL – FILTER -->
    <div class="left-panel">
        <div class="filter-box">
            <label><strong>Filter rezervacij</strong></label>
            <select id="filter" onchange="filterRez()">
                <option value="vse">Vse</option>
                <option value="cakajoce">Čakajoče</option>
                <option value="potrjeno">Potrjene</option>
                <option value="opravljeno">Opravljene</option>
                <option value="preklicano">Preklicane</option>
            </select>
        </div>
    </div>

    <!-- DESNI PANEL -->
    <div class="rez-list">

        <!-- 📊 STATISTIKA GRAF -->
        <div class="chart-box" style="width:420px; margin:20px auto;">
            <h3 style="text-align:center;">Statistika rezervacij</h3>
            <canvas id="statusChart"></canvas>
        </div>

        <!-- LISTA REZERVACIJ -->
        <?php while ($r = $rez->fetch_assoc()):
            $status_class = "status-" . $r['status'];
            $slika = $r['stranka_slika'] ?: "default.png";
        ?>
        <div class="rez-card rez-<?= htmlspecialchars($r['status']) ?>">

            <img src="images/<?= htmlspecialchars($slika) ?>" class="card-img" alt="Stranka">

            <div style="flex:1;">

                <span class="status-badge <?= htmlspecialchars($status_class) ?>">
                    <?= htmlspecialchars(ucfirst($r['status'])) ?>
                </span>

                <h3><?= htmlspecialchars($r['storitev_naziv']) ?></h3>

                <p>
                    <strong>Stranka:</strong> <?= htmlspecialchars($r['stranka_ime'] . " " . $r['stranka_priimek']) ?><br>
                    <strong>Email:</strong> <?= htmlspecialchars($r['stranka_email']) ?><br>
                    <strong>Datum:</strong> <?= date("d.m.Y H:i", strtotime($r['datum_cas'])) ?><br>
                    <strong>Trajanje:</strong> <?= (int)$r['trajanje_min'] ?> min<br>
                    <strong>Obiski skupaj:</strong> <?= (int)$r['stevilo_obiskov'] ?>
                </p>

                <?php if (!empty($r['opomba'])): ?>
                    <p class="opomba-box">
                        <strong>Sporočilo stranke:</strong><br>
                        <?= nl2br(htmlspecialchars($r['opomba'])) ?>
                    </p>
                <?php endif; ?>

            </div>

            <!-- DUGMAD -->
            <?php if ($r['status'] === 'cakajoce' || $r['status'] === 'potrjeno'): ?>
                <button class="btn-action btn-green" onclick="potrdi(<?= (int)$r['rezervacija_id'] ?>)">Potrdi</button>
                <button class="btn-action btn-red" onclick="preklici(<?= (int)$r['rezervacija_id'] ?>)">Prekliči</button>
            <?php endif; ?>

        </div>
        <?php endwhile; ?>

    </div>
</div>

<!-- JS: FILTER + AKCIJE -->
<script>
function filterRez() {
    let f = document.getElementById("filter").value;
    document.querySelectorAll(".rez-card").forEach(c => {
        c.style.display = (f === "vse" || c.classList.contains("rez-" + f)) ? "flex" : "none";
    });
}

function potrdi(id) {
    fetch("potrdi_rezervacijo.php", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "id=" + encodeURIComponent(id)
    })
    .then(r => r.text())
    .then(t => {
        if (t.trim() === "ok") location.reload();
        else alert(t);
    });
}

function preklici(id) {
    fetch("preklici_rezervacijo.php", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "id=" + encodeURIComponent(id)
    })
    .then(r => r.text())
    .then(t => {
        if (t.trim() === "ok") location.reload();
        else alert(t);
    });
}
</script>

<!-- 🎨 GRAF KODA -->
<script>
const ctx = document.getElementById('statusChart');

new Chart(ctx, {
    type: 'pie',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            data: <?= json_encode($values) ?>,
            backgroundColor: [
                '#f4c542', // cakajoce
                '#4caf50', // potrjeno
                '#2196f3', // opravljeno
                '#e53935'  // preklicano
            ]
        }]
    }
});
</script>

</body>
</html>
