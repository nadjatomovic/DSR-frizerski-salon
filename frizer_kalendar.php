<?php
session_start();
include 'db_config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['tip'] != 1) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Koledar – Bella Donna</title>
    <link rel="stylesheet" href="style.css">

    <!-- FULLCALENDAR -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

    <style>
        #koledar {
            max-width: 900px;
            margin: 40px auto;
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.15);
            min-height: 600px;
        }
    </style>
</head>
<body>

<header>
    <div class="nav-container">
        <div class="logo-box">
            <img src="images/monaLiza2.jpg" class="logo-small">
            <h1>Bella Donna</h1>
        </div>
        <nav>
            <a href="index.php">Domov</a>
            <!-- uklonila sam gumb Koledar jer kažeš da je suvišan -->
            <a href="logout.php" class="btn-nav">Odjava</a>
        </nav>
    </div>
</header>
<div style="max-width:900px; margin:20px auto; text-align:center;">
    <a href="export_kalendar.php?format=pdf" class="btn-primary" style="margin-right:10px;">
        📄 Preuzmi PDF
    </a>
    <a href="export_kalendar_csv.php" class="btn-primary">
        📊 Preuzmi Excel (CSV)
    </a>
</div>

<div id="koledar"></div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    let calendarEl = document.getElementById('koledar');

    let calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        locale: 'sl',
        slotMinTime: "08:00:00",
        slotMaxTime: "20:00:00",

        events: "load_rezervacije_frizer.php",

        eventClick: function(info) {

    document.getElementById("m_title").textContent = info.event.title;
    document.getElementById("m_stranka").textContent = info.event.extendedProps.stranka;
    document.getElementById("m_storitev").textContent = info.event.extendedProps.storitev;
    document.getElementById("m_status").textContent = info.event.extendedProps.status;
    document.getElementById("m_start").textContent = info.event.start.toLocaleString();
    document.getElementById("m_end").textContent = info.event.end.toLocaleString();

    document.getElementById("eventModal").style.display = "flex";
},

    });

    calendar.render();
});

function closeEventModal() {
    document.getElementById("eventModal").style.display = "none";
}

</script>
<script>
// auto update statusov vsakokrat ko frizer odpre koledar
fetch("update_statusi.php");
</script>

<div class="modal-bg" id="eventModal">
    <div class="modal-box">
        <h3 id="m_title"></h3>

        <p><strong>Stranka:</strong> <span id="m_stranka"></span></p>
        <p><strong>Storitev:</strong> <span id="m_storitev"></span></p>
        <p><strong>Status:</strong> <span id="m_status"></span></p>
        <p><strong>Začetek:</strong> <span id="m_start"></span></p>
        <p><strong>Konec:</strong> <span id="m_end"></span></p>

        <button onclick="closeEventModal()" class="btn-primary">Zapri</button>
    </div>
</div>


</body>
</html>
