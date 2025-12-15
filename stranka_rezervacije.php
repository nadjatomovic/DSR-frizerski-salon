<?php
include 'db_config.php';
session_start();

// samo stranka može videti to
if (!isset($_SESSION['user']) || $_SESSION['user']['tip'] != 2) {
    header("Location: login.php");
    exit;
}

$stranka_id = $_SESSION['user']['id'];

// ================================
//   POVLECI REZERVACIJE IZ BAZE
// ================================
$q = $conn->prepare("
    SELECT 
        r.rezervacija_id,
        r.datum_cas,
        r.status,
        r.trajanje_min,
        r.opomba,
        f.ime AS frizer_ime,
        f.priimek AS frizer_priimek,
        f.email AS frizer_email,
        f.slika AS frizer_slika,
        s.naziv AS storitev_naziv
    FROM Rezervacija r
    JOIN Uporabnik f ON r.uporabnik_frizer_id = f.uporabnik_id
    JOIN Rezervacija_Storitev rs ON rs.rezervacija_id = r.rezervacija_id
    JOIN Storitev s ON s.storitev_id = rs.storitev_id
    WHERE r.uporabnik_stranka_id = ?
    ORDER BY r.datum_cas DESC
");
$q->bind_param("i", $stranka_id);
$q->execute();
$rez = $q->get_result();

// ===================================
//   POVLECI FRIZERJE I STORITVE ZA FORMU
// ===================================
$frizerji = $conn->query("SELECT uporabnik_id, ime, priimek FROM Uporabnik WHERE tip_id = 1 ORDER BY ime");
$stor     = $conn->query("SELECT storitev_id, naziv, trajanje_min FROM Storitev ORDER BY naziv");
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Moje rezervacije — Bella Donna</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<!-- GLOBALNI MODAL ZA PORUKE -->
<div id="msgModal" class="modal-bg" style="display:none;">
    <div class="modal-box" style="text-align:center;">
        <p id="msgText" style="margin-bottom:20px; font-size:1rem;"></p>
        <button onclick="closeMsg()" class="btn-primary" style="width:120px;">OK</button>
    </div>
</div>

<header>
    <div class="nav-container">
        <div class="logo-box">
            <img src="images/monaLiza2.jpg" class="logo-small" alt="logo">
            <h1>Bella Donna</h1>
        </div>
        <nav>
            <a href="index.php">Domov</a>
            <a href="stranka_rezervacije.php" class="btn-nav">Moje rezervacije</a>
            <a href="logout.php" class="btn-nav">Odjava</a>
        </nav>
    </div>
</header>

<div class="rez-wrapper">

<?php if (!empty($_SESSION['rez_error'])): ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    showMsg(<?= json_encode($_SESSION['rez_error']) ?>);
});
</script>
<?php unset($_SESSION['rez_error']); endif; ?>

<?php if (!empty($_SESSION['rez_ok'])): ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    showMsg(<?= json_encode($_SESSION['rez_ok']) ?>);
});
</script>
<?php unset($_SESSION['rez_ok']); endif; ?>

    <!-- TABS -->
    <div class="tab-buttons">
        <div class="tab-btn active" onclick="openTab('moje')">Moje rezervacije</div>
        <div class="tab-btn" onclick="openTab('nova')">Nova rezervacija</div>
    </div>

    <!-- ============================== -->
    <!--          MOJE REZERVACIJE      -->
    <!-- ============================== -->
    <div id="moje" class="tab-content active">

        <div class="filter-box">
            <select id="filter" onchange="filterRez()">
                <option value="vse">Vse rezervacije</option>
                <option value="cakajoce">Čakajoče</option>
                <option value="potrjeno">Potrjene</option>
                <option value="opravljeno">Opravljene</option>
                <option value="preklicano">Preklicane</option>
            </select>
        </div>

        <?php while ($r = $rez->fetch_assoc()):
            $status_class = "status-" . $r['status'];
            $slika = $r['frizer_slika'] ? $r['frizer_slika'] : 'default.png';
        ?>
        <div class="rez-card rez-<?= htmlspecialchars($r['status']) ?>">

            <img src="images/<?= htmlspecialchars($slika) ?>" class="rez-img" alt="Frizer">

            <div class="rez-info">
                
                <span class="status-badge <?= htmlspecialchars($status_class) ?>">
                    <?= htmlspecialchars(ucfirst($r['status'])) ?>
                </span>

                <h3><?= htmlspecialchars($r['storitev_naziv']) ?></h3>

                <p>
                    <strong>Frizer:</strong> <?= htmlspecialchars($r['frizer_ime'] . " " . $r['frizer_priimek']) ?><br>
                    <strong>Email:</strong> <?= htmlspecialchars($r['frizer_email']) ?><br>
                    <strong>Datum:</strong> <?= date("d.m.Y H:i", strtotime($r['datum_cas'])) ?><br>
                    <strong>Trajanje:</strong> <?= (int)$r['trajanje_min'] ?> min
                </p>

                <?php if ($r['status'] === 'cakajoce' || $r['status'] === 'potrjeno'): ?>
                    <a href="#" class="btn-small" onclick="openCancel(<?= (int)$r['rezervacija_id'] ?>)">Prekliči</a>
                <?php endif; ?>

            </div>
        </div>
        <?php endwhile; ?>

    </div>

    <!-- ============================== -->
    <!--        NOVA REZERVACIJA        -->
    <!-- ============================== -->
    <div id="nova" class="tab-content">
        <div class="new-rez-box">
            <h2>Nova rezervacija</h2>

            <form action="ustvari_rezervacijo.php" method="POST" id="formRez">
                <label>Izberi frizerja</label>
                <select name="frizer_id" required>
                    <?php while ($f = $frizerji->fetch_assoc()): ?>
                        <option value="<?= (int)$f['uporabnik_id'] ?>">
                            <?= htmlspecialchars($f['ime'] . " " . $f['priimek']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label>Izberi storitev</label>
                <select name="storitev_id" required>
                    <?php while ($s = $stor->fetch_assoc()): ?>
                        <option value="<?= (int)$s['storitev_id'] ?>">
                            <?= htmlspecialchars($s['naziv']) ?> (<?= (int)$s['trajanje_min'] ?> min)
                        </option>
                    <?php endwhile; ?>
                </select>

                <label>Datum & čas</label>
                <input type="datetime-local" name="datum_cas" required value="<?= date('Y-m-d\TH:i') ?>">

                <button type="submit" class="btn-primary">Rezerviraj</button>
            </form>
        </div>
    </div>

</div>

<!-- MODAL ZA PREKLIC -->
<div class="modal-bg" id="modal">
    <div class="modal-box">
        <h3>Ali želite preklicati rezervacijo?</h3>
        <button class="btn-primary" onclick="confirmCancel()">Da, prekliči</button>
        <br><br>
        <button class="btn-small" onclick="closeModal()" style="background:#555;">Ne</button>
    </div>
</div>

<script>
let cancelID = null;

function openTab(tab) {
    document.querySelectorAll(".tab-content").forEach(t => t.classList.remove("active"));
    document.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("active"));
    document.getElementById(tab).classList.add("active");
    event.target.classList.add("active");
}

function filterRez() {
    let f = document.getElementById("filter").value;
    document.querySelectorAll(".rez-card").forEach(c => {
        c.style.display = (f === "vse" || c.classList.contains("rez-" + f)) ? "flex" : "none";
    });
}

function openCancel(id) {
    cancelID = id;
    document.getElementById("modal").style.display = "flex";
}

function closeModal() {
    document.getElementById("modal").style.display = "none";
}

function confirmCancel() {
    window.location.href = "preklici_rezervacijo.php?id=" + encodeURIComponent(cancelID);
}

// MODAL PORUKE
function showMsg(txt) {
    document.getElementById("msgText").innerHTML = txt;
    document.getElementById("msgModal").style.display = "flex";
}

function closeMsg() {
    document.getElementById("msgModal").style.display = "none";
}
</script>

</body>
</html>
