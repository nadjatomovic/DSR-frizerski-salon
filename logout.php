<?php
session_start();

// Pobriši sve podatke iz sesije
session_unset();
session_destroy();

// Pobriši sve iz localStorage i vrati korisnika na početnu
echo "
<script>
    localStorage.removeItem('user_logged');
    localStorage.removeItem('user_id');
    localStorage.removeItem('user_name');
    localStorage.removeItem('user_type');

    window.location.href = 'index.php';
</script>";
exit;
?>
